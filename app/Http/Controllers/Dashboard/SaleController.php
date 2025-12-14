<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleEditNotification;
use App\Models\User;
use App\Services\SerialNumberGenerator;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class SaleController extends Controller
{
    public function __construct(
        private readonly SerialNumberGenerator $serials
    ) {
    }

    private ?array $variationExpiryLookup = null;

    public function index(Request $request): View
    {
        $perPage = (int) $request->query('per_page', 50);
        $perPage = in_array($perPage, [25, 50, 100, 200], true) ? $perPage : 50;

        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'created_by' => trim((string) $request->query('created_by', '')),
            'product_name' => trim((string) $request->query('product_name', '')),
            'status' => trim((string) $request->query('status', '')),
        ];

        $filters['date_from'] = $this->normalizeDateInput($request->query('date_from'));
        $filters['date_to'] = $this->normalizeDateInput($request->query('date_to'));

        if ($filters['date_from'] && $filters['date_to'] && $filters['date_from'] > $filters['date_to']) {
            [$filters['date_from'], $filters['date_to']] = [$filters['date_to'], $filters['date_from']];
        }

        $salesQuery = Sale::with(['createdBy']);

        if ($filters['search'] !== '') {
            $rawSearch = $filters['search'];
            $startsWithTm = Str::startsWith(mb_strtolower(trim($rawSearch)), 'tm');
            $searchTerm = '%' . $rawSearch . '%';
            $numericSearch = preg_replace('/\D+/', '', $rawSearch);
            $normalizedPhoneTerm = $numericSearch !== '' ? '%' . $numericSearch . '%' : null;
            $normalizedSerial = mb_strtolower(trim($rawSearch));

            $salesQuery->where(function ($query) use ($searchTerm, $normalizedPhoneTerm, $normalizedSerial, $startsWithTm) {
                // If the query looks like an order id (starts with TM), search only by serial_number (exact).
                if ($startsWithTm) {
                    $query->whereRaw('LOWER(serial_number) = ?', [$normalizedSerial]);
                    return;
                }

                // Otherwise, search across serial (exact) and other fields.
                $query->whereRaw('LOWER(serial_number) = ?', [$normalizedSerial])
                    ->orWhere('phone', 'like', $searchTerm)
                    ->orWhere('email', 'like', $searchTerm)
                    ->orWhere('remarks', 'like', $searchTerm);

                if ($normalizedPhoneTerm !== null) {
                    $query->orWhereRaw(
                        "REGEXP_REPLACE(phone, '[^0-9]+', '') like ?",
                        [$normalizedPhoneTerm]
                    );
                }
            });
        }

        if ($filters['product_name'] !== '') {
            $salesQuery->where('product_name', 'like', '%' . $filters['product_name'] . '%');
        }

        if ($filters['status'] !== '' && in_array($filters['status'], ['pending', 'completed', 'refunded'], true)) {
            $salesQuery->where('status', $filters['status']);
        }

        if ($filters['created_by'] !== '') {
            if ($filters['created_by'] === 'admin') {
                $salesQuery->whereHas('createdBy', function ($query) {
                    $query->where('role', 'admin');
                });
            } else {
                $creatorId = (int) $filters['created_by'];
                if ($creatorId > 0) {
                    $salesQuery->where('created_by', $creatorId);
                }
            }
        }

        if ($filters['date_from']) {
            $salesQuery->whereDate('purchase_date', '>=', $filters['date_from']);
        }

        if ($filters['date_to']) {
            $salesQuery->whereDate('purchase_date', '<=', $filters['date_to']);
        }

        $sales = $salesQuery
            ->orderByDesc(DB::raw('CAST(SUBSTRING(serial_number, 3) AS UNSIGNED)'))
            ->paginate($perPage)
            ->withQueryString();

        $productOptions = Product::query()
            ->where('is_in_stock', true)
            ->with([
                'variations' => fn ($query) => $query
                    ->where('is_in_stock', true)
                    ->orderBy('name'),
            ])
            ->orderBy('name')
            ->get()
            ->flatMap(function (Product $product) {
                $name = trim($product->name);
                if ($name === '') {
                    return collect();
                }

                return $product->variations
                    ->map(function ($variation) use ($name) {
                        $variationName = trim((string) $variation->name);
                        if ($variationName === '') {
                            return null;
                        }

                        return [
                            'label' => sprintf('%s - %s', $name, $variationName),
                            'expiry_days' => $variation->expiry_days,
                        ];
                    })
                    ->filter();
            })
            ->unique('label')
            ->values()
            ->all();

        $saleToEdit = null;
        if ($request->filled('edit')) {
            $saleToEdit = Sale::find($request->input('edit'));
        }

        $admins = User::query()
            ->where('role', 'admin')
            ->orderBy('name')
            ->get(['id', 'name']);
        $employees = User::query()
            ->where('role', 'employee')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('sales.index', [
            'sales' => $sales,
            'productOptions' => $productOptions,
            'saleToEdit' => $saleToEdit,
            'perPage' => $perPage,
            'filters' => $filters,
            'admins' => $admins,
            'employees' => $employees,
        ]);
    }

    public function expiredOrders(Request $request): View
    {
        $perPage = (int) $request->query('per_page', 25);
        $perPage = in_array($perPage, [25, 50, 100, 200], true) ? $perPage : 25;
        $mode = $request->query('remaining_filter', 'today');
        $mode = in_array($mode, ['today', 'all'], true) ? $mode : 'today';

        $page = LengthAwarePaginator::resolveCurrentPage();
        $expiredSalesCollection = Sale::query()
            ->with(['createdBy'])
            ->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->get();

        $variationExpiryLookup = $this->getVariationExpiryLookup();
        $productStockLookup = Product::query()
            ->select('name', 'is_in_stock')
            ->get()
            ->mapWithKeys(function (Product $product) {
                $baseName = trim(mb_strtolower($product->name));
                if ($baseName === '') {
                    return [];
                }

                return [$baseName => (bool) $product->is_in_stock];
            })
            ->all();
        $today = Carbon::now('Asia/Kathmandu')->startOfDay();

        $transformedSales = $expiredSalesCollection->map(function (Sale $sale) use ($variationExpiryLookup, $productStockLookup, $today) {
            $expiryDays = $sale->product_expiry_days;
            if ($expiryDays === null && isset($variationExpiryLookup[$sale->product_name])) {
                $expiryDays = $variationExpiryLookup[$sale->product_name];
            }

            $sale->calculated_expiry_days = $expiryDays;
            if ($sale->purchase_date && $expiryDays !== null) {
                $sale->calculated_expiry_date = $sale->purchase_date->copy()->addDays((int) $expiryDays);
            } else {
                $sale->calculated_expiry_date = null;
            }

            if ($sale->calculated_expiry_date) {
                $diff = $today->diffInDays($sale->calculated_expiry_date->copy()->startOfDay(), false);
                $sale->calculated_remaining_days = (int) $diff;
            } else {
                $sale->calculated_remaining_days = null;
            }

            $baseProductName = trim((string) $sale->product_name);
            $token = strtok($baseProductName, '-');
            $normalizedBase = mb_strtolower(trim($token !== false ? $token : $baseProductName));
            $sale->product_is_in_stock = $normalizedBase !== '' && array_key_exists($normalizedBase, $productStockLookup)
                ? $productStockLookup[$normalizedBase]
                : null;

            return $sale;
        });

        $filteredSales = $transformedSales->filter(function (Sale $sale) use ($mode) {
            $remaining = $sale->calculated_remaining_days;

            if ($mode === 'today') {
                return $remaining === -1;
            }

            if ($remaining === null) {
                return true;
            }

            return $remaining >= -7;
        });

        $sortedSales = $filteredSales
            ->sortBy(function (Sale $sale) {
                $remaining = $sale->calculated_remaining_days;
                if ($remaining === null) {
                    return [3, PHP_INT_MAX];
                }

                if ($remaining < 0) {
                    return [0, $remaining];
                }

                if ($remaining === 0) {
                    return [1, 0];
                }

                return [2, $remaining];
            })
            ->values();

        $total = $sortedSales->count();
        $items = $sortedSales->slice(($page - 1) * $perPage, $perPage)->values();

        $expiredSales = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
        $expiredSales->appends($request->query());

        return view('sales.expired', [
            'sales' => $expiredSales,
            'perPage' => $perPage,
            'remainingFilter' => $mode,
        ]);
    }

    public function checkDuplicate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:50'],
            'product_name' => ['required', 'string', 'max:255'],
            'purchase_date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $cutoff = Carbon::now('Asia/Kathmandu')->subHours(48);
        $normalizedPhone = preg_replace('/\D+/', '', $data['phone']);
        $normalizedProduct = mb_strtolower(trim($data['product_name']));

        $isDuplicate = false;

        $purchaseDateInput = $data['purchase_date'] ?? null;
        if ($purchaseDateInput) {
            try {
                $purchaseDate = Carbon::createFromFormat('Y-m-d', $purchaseDateInput, 'Asia/Kathmandu')->endOfDay();
                if ($purchaseDate->lt($cutoff)) {
                    return response()->json([
                        'duplicate' => false,
                    ]);
                }
            } catch (\Throwable $e) {
                // Ignore parse errors and fall back to checking.
            }
        }

        if ($normalizedPhone !== '' && $normalizedProduct !== '') {
            $isDuplicate = Sale::query()
                ->whereRaw("REGEXP_REPLACE(phone, '[^0-9]+', '') = ?", [$normalizedPhone])
                ->whereRaw('LOWER(TRIM(product_name)) = ?', [$normalizedProduct])
                ->where('created_at', '>=', $cutoff)
                ->exists();
        }

        return response()->json([
            'duplicate' => $isDuplicate,
        ]);
    }

    public function store(Request $request)
    {
        $nowKathmandu = Carbon::now('Asia/Kathmandu');

        if (!$request->filled('purchase_date')) {
            $request->merge([
                'purchase_date' => $nowKathmandu->toDateString(),
            ]);
        }

        $data = $this->validatePayload($request);

        $hasAmount = $data['sales_amount'] !== null;

        if ($data['product_expiry_days'] === null && $data['product_name']) {
            $data['product_expiry_days'] = $this->inferExpiryDaysFromProductName($data['product_name']);
        }
        $data['status'] = $data['status']
            ?? ($hasAmount ? 'completed' : 'pending');

        $createdBy = $request->user()?->id;

        $createdSale = null;

        DB::transaction(function () use ($data, $nowKathmandu, $createdBy, &$createdSale) {
            $purchaseDate = Carbon::createFromFormat('Y-m-d', $data['purchase_date'])->startOfDay();

            $sale = Sale::create([
                'serial_number' => $this->serials->next(),
                'purchase_date' => $purchaseDate,
                'product_name' => $data['product_name'],
                'product_expiry_days' => $data['product_expiry_days'],
                'remarks' => $data['remarks'],
                'phone' => $data['phone'],
                'email' => $data['email'] ? trim($data['email']) : null,
                'sales_amount' => $data['sales_amount'],
                'created_by' => $createdBy,
                'status' => $data['status'],
            ]);

            $createdSale = $sale;
        });

        $message = 'Sale saved successfully.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
            ], 201);
        }

        return redirect()
            ->route('orders.index')
            ->with('saleConfirmation', $createdSale ? [
                'serial_number' => $createdSale->serial_number,
                'product_name' => $createdSale->product_name,
                'phone' => $createdSale->phone,
                'email' => $createdSale->email,
                'sales_amount' => $createdSale->sales_amount,
            ] : null);
    }

    public function update(Request $request, Sale $sale)
    {
        $originalSnapshot = [
            'status' => $sale->status,
            'sales_amount' => $sale->sales_amount,
            'phone' => $sale->phone,
            'email' => $sale->email,
            'product_name' => $sale->product_name,
        ];

        $data = $this->validatePayload($request, $sale);

        $hasAmount = $data['sales_amount'] !== null;

        if ($data['product_expiry_days'] === null && $data['product_name']) {
            $data['product_expiry_days'] = $this->inferExpiryDaysFromProductName($data['product_name']);
        }
        $data['status'] = $data['status']
            ?? ($hasAmount ? 'completed' : ($sale->status ?? 'pending'));

        if ($sale->status === 'pending' && ($data['status'] === null || $data['status'] === 'pending')) {
            $data['status'] = 'completed';
        }

        $actorId = $request->user()?->id;

        DB::transaction(function () use ($sale, $data) {
            $purchaseDate = Carbon::createFromFormat('Y-m-d', $data['purchase_date'])->startOfDay();

            $sale->update([
                'purchase_date' => $purchaseDate,
                'product_name' => $data['product_name'],
                'product_expiry_days' => $data['product_expiry_days'],
                'remarks' => $data['remarks'],
                'phone' => $data['phone'],
                'email' => $data['email'] ? trim($data['email']) : null,
                'sales_amount' => $data['sales_amount'],
                'status' => $data['status'],
            ]);
        });

        $sale->refresh();

        $changeMessages = [];

        $formatAmount = static fn ($value) => $value === null
            ? 'N/A'
            : number_format((float) $value, 2);

        $originalAmount = $originalSnapshot['sales_amount'];
        $newAmount = $sale->sales_amount;
        if ($originalAmount != $newAmount) {
            $changeMessages[] = 'Changed Amount from ' . $formatAmount($originalAmount) . ' to ' . $formatAmount($newAmount);
        }

        $originalPhone = trim((string) ($originalSnapshot['phone'] ?? ''));
        $newPhone = trim((string) ($sale->phone ?? ''));
        if ($originalPhone !== $newPhone) {
            $changeMessages[] = 'Changed Phone from ' . ($originalPhone !== '' ? $originalPhone : 'N/A') . ' to ' . ($newPhone !== '' ? $newPhone : 'N/A');
        }

        $originalEmail = trim((string) ($originalSnapshot['email'] ?? ''));
        $newEmail = trim((string) ($sale->email ?? ''));
        if ($originalEmail !== $newEmail) {
            $changeMessages[] = 'Changed Email from ' . ($originalEmail !== '' ? $originalEmail : 'N/A') . ' to ' . ($newEmail !== '' ? $newEmail : 'N/A');
        }

        $originalProduct = trim((string) ($originalSnapshot['product_name'] ?? ''));
        $newProduct = trim((string) ($sale->product_name ?? ''));
        if ($originalProduct !== $newProduct) {
            $changeMessages[] = 'Changed Product from ' . ($originalProduct !== '' ? $originalProduct : 'N/A') . ' to ' . ($newProduct !== '' ? $newProduct : 'N/A');
        }

        $originalStatus = $originalSnapshot['status'] ?? null;
        $newStatus = $sale->status ?? null;
        if ($originalStatus !== $newStatus) {
            $changeMessages[] = 'Changed Status from ' . ($originalStatus ?? 'N/A') . ' to ' . ($newStatus ?? 'N/A');
        }

        if (count($changeMessages) > 0 && Schema::hasTable('sale_edit_notifications')) {
            $actorName = $request->user()?->name ?? 'Employee';
            $message = $actorName . ' edited the ' . ($sale->serial_number ?? 'order') . ': ' . implode('; ', $changeMessages);

            SaleEditNotification::create([
                'sale_id' => $sale->id,
                'actor_id' => $actorId,
                'message' => $message,
            ]);
        }

        $message = 'Sale updated successfully.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
            ]);
        }

        return redirect()
            ->route('orders.index')
            ->with('status', $message);
    }

    public function logs(Request $request): View
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'admin', 403);

        $pageSize = 50;
        $hasTable = Schema::hasTable('sale_edit_notifications');
        $search = trim((string) $request->query('search', ''));

        $logs = collect();

        if ($hasTable) {
            $logsQuery = SaleEditNotification::query()
                ->with(['sale:id,serial_number', 'actor:id,name'])
                ->latest();

            if ($search !== '') {
                $logsQuery->whereHas('sale', function ($query) use ($search) {
                    $query->where('serial_number', 'like', '%' . $search . '%');
                });
            }

            $logs = $logsQuery
                ->paginate($pageSize)
                ->appends($request->query());
        }

        $timezone = 'Asia/Kathmandu';

        return view('sales.logs', [
            'logs' => $logs,
            'timezone' => $timezone,
            'hasTable' => $hasTable,
            'search' => $search,
        ]);
    }

    public function destroy(Request $request, Sale $sale)
    {
        if ($request->user()?->role === 'employee') {
            $message = 'Employees are not allowed to delete orders.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], Response::HTTP_FORBIDDEN);
            }

            return redirect()
                ->route('orders.index')
                ->with('status', $message);
        }

        DB::transaction(function () use ($sale) {
            $sale->transaction()->delete();
            $sale->delete();
        });

        $message = 'Sale deleted successfully.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
            ]);
        }

        return redirect()
            ->route('orders.index')
            ->with('status', $message);
    }

    public function customerProfile(Request $request, string $phone)
    {
        $decodedPhone = preg_replace('/\D+/', '', trim(urldecode($phone)));

        $perPage = (int) $request->query('per_page', 25);
        $perPage = in_array($perPage, [25, 50, 100], true) ? $perPage : 25;

        $salesQuery = Sale::query()
            ->with(['createdBy'])
            ->whereRaw(
                "REGEXP_REPLACE(phone, '[^0-9]+', '') = ?",
                [$decodedPhone]
            );

        $totalPurchases = (clone $salesQuery)->count();
        $startingDate = (clone $salesQuery)->min('purchase_date');
        $totalSpent = (clone $salesQuery)->sum('sales_amount');

        $customerSales = $salesQuery
            ->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $customerSales->appends(['per_page' => $perPage]);

        return view('sales.customer', [
            'phone' => $decodedPhone,
            'sales' => $customerSales,
            'totalPurchases' => $totalPurchases,
            'startingDate' => $startingDate,
            'totalSpent' => $totalSpent,
            'perPage' => $perPage,
        ]);
    }

    /**
     * @return array{
     *     product_name: string,
     *     phone: string,
     *     email?: string|null,
     *     sales_amount: float,
     *     purchase_date: string,
     *     remarks: string,
     *     product_expiry_days: ?int,
     *     status: ?string
     * }
     */
    private function validatePayload(Request $request, ?Sale $sale = null): array
    {
        $isUpdate = $sale !== null;

        $data = $request->validate([
            'product_name' => $isUpdate
                ? ['sometimes', 'nullable', 'string', 'max:255']
                : ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'sales_amount' => ['nullable', 'numeric', 'min:0'],
            'purchase_date' => ['required', 'date_format:Y-m-d'],
            'remarks' => ['nullable', 'string', 'max:255'],
            'product_expiry_days' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'string', Rule::in(['completed', 'refunded', 'pending'])],
        ]);

        $hasAmount = array_key_exists('sales_amount', $data) && $data['sales_amount'] !== null && $data['sales_amount'] !== '';

        $expiryInput = $data['product_expiry_days'] ?? null;
        $expiryDays = isset($expiryInput) && $expiryInput !== '' ? max(0, (int) $expiryInput) : null;
        $productName = array_key_exists('product_name', $data)
            ? trim((string) $data['product_name'])
            : ($isUpdate ? trim((string) $sale->product_name) : '');

        return [
            ...$data,
            'sales_amount' => $hasAmount ? (float) $data['sales_amount'] : null,
            'product_expiry_days' => $expiryDays,
            'product_name' => $productName,
            'status' => $data['status'] ?? null,
        ];
    }

    private function normalizeDateInput(?string $value): ?string
    {
        $trimmed = is_string($value) ? trim($value) : '';
        if ($trimmed === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $trimmed)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function inferExpiryDaysFromProductName(?string $productName): ?int
    {
        $normalized = trim((string) $productName);
        if ($normalized === '') {
            return null;
        }

        $lookup = $this->getVariationExpiryLookup();

        return array_key_exists($normalized, $lookup) ? (int) $lookup[$normalized] : null;
    }

    private function getVariationExpiryLookup(): array
    {
        if ($this->variationExpiryLookup !== null) {
            return $this->variationExpiryLookup;
        }

        $this->variationExpiryLookup = Product::query()
            ->with(['variations' => fn ($query) => $query->select('id', 'product_id', 'name', 'expiry_days')])
            ->orderBy('name')
            ->get()
            ->flatMap(function (Product $product) {
                $baseName = trim($product->name);
                if ($baseName === '') {
                    return collect();
                }

                return $product->variations
                    ->filter(fn ($variation) => trim((string) $variation->name) !== '')
                    ->mapWithKeys(function ($variation) use ($baseName) {
                        if ($variation->expiry_days === null) {
                            return [];
                        }

                        $variationName = trim((string) $variation->name);
                        $key = sprintf('%s - %s', $baseName, $variationName);

                        return [$key => (int) $variation->expiry_days];
                    });
            })
            ->all();

        return $this->variationExpiryLookup;
    }
}
