<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\RecordProduct;
use App\Models\Sale;
use App\Models\SaleEditNotification;
use App\Models\StockProduct;
use App\Models\User;
use App\Services\SerialNumberGenerator;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\Schema\Blueprint;

class SaleController extends Controller
{
    public function __construct(
        private readonly SerialNumberGenerator $serials
    ) {
    }

    private ?array $variationExpiryLookup = null;

    public function index(Request $request): View
    {
        $this->ensureSaleSyncColumns();
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
            $isEmailSearch = filter_var($rawSearch, FILTER_VALIDATE_EMAIL) !== false;
            $searchTerm = '%' . $rawSearch . '%';
            $numericSearch = preg_replace('/\D+/', '', $rawSearch);
            $normalizedPhoneTerm = $numericSearch !== '' ? '%' . $numericSearch . '%' : null;
            $normalizedSerial = mb_strtolower(trim($rawSearch));
            $normalizedEmail = mb_strtolower(trim($rawSearch));

            $salesQuery->where(function ($query) use ($searchTerm, $normalizedPhoneTerm, $normalizedSerial, $startsWithTm, $isEmailSearch, $normalizedEmail) {
                // If the query looks like an order id (starts with TM), search only by serial_number (exact).
                if ($startsWithTm) {
                    $query->whereRaw('LOWER(serial_number) = ?', [$normalizedSerial]);
                    return;
                }

                // If the search looks like an email, match exactly against email or remarks.
                if ($isEmailSearch) {
                    $query->whereRaw('LOWER(email) = ?', [$normalizedEmail])
                        ->orWhereRaw('LOWER(remarks) = ?', [$normalizedEmail]);
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

        if ($filters['status'] !== '' && in_array($filters['status'], ['pending', 'completed', 'refunded', 'cancelled'], true)) {
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

        $this->appendFamilySyncStatus($sales);
        $this->appendSheetSyncStatus($sales);

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
        $search = trim((string) $request->query('search', ''));
        $searchDigits = preg_replace('/\D+/', '', $search);
        $searchLast4 = strlen($searchDigits) >= 4 ? substr($searchDigits, -4) : null;
        $searchDigits = preg_replace('/\D+/', '', $search);
        $searchLast4 = strlen($searchDigits) >= 4 ? substr($searchDigits, -4) : null;
        $normalizedSearch = $search !== '' ? mb_strtolower($search) : null;
        $digitsSearch = $search !== '' ? preg_replace('/\D+/', '', $search) : '';
        $startsWithTm = $normalizedSearch !== null && Str::startsWith($normalizedSearch, 'tm');
        $isEmailSearch = $normalizedSearch !== null && filter_var($search, FILTER_VALIDATE_EMAIL);

        $page = LengthAwarePaginator::resolveCurrentPage();
        $expiredSalesCollection = Sale::query()
            ->with(['createdBy'])
            ->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->get();

        $variationExpiryLookup = $this->getVariationExpiryLookup();
        $dynamicVariationNames = $this->getDynamicVariationNames();
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

        if ($dynamicVariationNames->isNotEmpty()) {
            $seenDynamic = [];
            $transformedSales = $transformedSales->filter(function (Sale $sale) use ($dynamicVariationNames, &$seenDynamic) {
                $productName = mb_strtolower(trim((string) ($sale->product_name ?? '')));
                if ($productName === '' || !$dynamicVariationNames->has($productName)) {
                    return true;
                }

                $phone = preg_replace('/\D+/', '', (string) ($sale->phone ?? ''));
                $email = mb_strtolower(trim((string) ($sale->email ?? '')));
                if ($phone === '' && $email === '') {
                    return true;
                }

                $key = implode('|', [$productName, $phone, $email]);
                if (isset($seenDynamic[$key])) {
                    return false;
                }
                $seenDynamic[$key] = true;
                return true;
            })->values();
        }

        $filteredSales = $transformedSales->filter(function (Sale $sale) use ($mode, $normalizedSearch, $digitsSearch, $startsWithTm, $isEmailSearch) {
            $status = strtolower((string) ($sale->status ?? ''));
            if ($status === 'cancelled' || $status === 'refunded') {
                return false;
            }
            // Apply search across serial, email, and phone (normalized digits) before paging.
            if ($normalizedSearch !== null) {
                $serialMatch = mb_strtolower(trim((string) $sale->serial_number)) === $normalizedSearch;

                if ($startsWithTm) {
                    if (!$serialMatch) {
                        return false;
                    }
                } elseif ($isEmailSearch) {
                    $emailMatch = mb_strtolower(trim((string) $sale->email)) === $normalizedSearch;
                    $remarksMatch = mb_strtolower(trim((string) $sale->remarks)) === $normalizedSearch;

                    if (!($emailMatch || $remarksMatch)) {
                        return false;
                    }
                } else {
                    $emailMatch = mb_stripos((string) $sale->email, $normalizedSearch) !== false;
                    $phoneDigits = preg_replace('/\D+/', '', (string) $sale->phone);
                    $phoneMatch = $digitsSearch !== '' && $phoneDigits !== '' && str_contains($phoneDigits, $digitsSearch);

                    if (!($serialMatch || $emailMatch || $phoneMatch)) {
                        return false;
                    }
                }
            }

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

    public function lookupEmail(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:50'],
        ]);

        $normalizedPhone = preg_replace('/\D+/', '', $data['phone']);
        $email = null;

        if ($normalizedPhone !== '') {
            $email = Sale::query()
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->whereRaw("REGEXP_REPLACE(phone, '[^0-9]+', '') = ?", [$normalizedPhone])
                ->orderByDesc('created_at')
                ->value('email');
        }

        return response()->json([
            'email' => $email ? trim((string) $email) : null,
        ]);
    }

    public function phoneHistory(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:50'],
        ]);

        $normalizedPhone = preg_replace('/\D+/', '', $data['phone']);
        if ($normalizedPhone === '') {
            return response()->json([
                'purchases' => [],
            ]);
        }

        $sales = Sale::query()
            ->whereRaw("REGEXP_REPLACE(phone, '[^0-9]+', '') = ?", [$normalizedPhone])
            ->orderByDesc('purchase_date')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['product_name', 'purchase_date', 'created_at']);

        $purchases = $sales->map(function (Sale $sale): array {
            $date = $sale->purchase_date
                ? Carbon::parse($sale->purchase_date)->format('Y-m-d')
                : Carbon::parse($sale->created_at)->format('Y-m-d');

            return [
                'product' => $sale->product_name ?: 'Unknown product',
                'date' => $date,
            ];
        })->values();

        return response()->json([
            'purchases' => $purchases,
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureSaleSyncColumns();
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
        $familyContext = $this->findFamilyAccountForProduct($data['product_name'] ?? null);
        if ($familyContext && $familyContext['full']) {
            $variationContext = $this->getVariationContextForName($data['product_name'] ?? null);
            $isLinked = $this->matchesFamilyLink(
                $familyContext['family_product'],
                $variationContext['variation_id'] ? (int) $variationContext['variation_id'] : null,
                $variationContext['variation_product_id'] ? (int) $variationContext['variation_product_id'] : null
            );
            if (!$isLinked) {
                $familyContext = null;
            }
        }
        if ($familyContext && $familyContext['full']) {
            $message = 'Family is full for selected product. Please fix that.';
            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }
            return redirect()->back()->withErrors(['product_name' => $message])->withInput();
        }

        $createdSale = null;
        $familySyncStatus = null;
        $sheetSyncStatus = null;
        $stockMatch = null;

        if (!empty($data['product_name'])) {
            $stockMatch = $this->findStockMatchForSale($data['product_name']);
            if ($stockMatch && !$this->hasEmptyStockRow($stockMatch)) {
                $message = 'No Stocks Left, Please add sufficient accounts in Stock account.';
                if ($request->expectsJson()) {
                    return response()->json(['message' => $message], 422);
                }
                return redirect()->back()->withErrors(['product_name' => $message])->withInput();
            }
        }

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

        if (
            $familyContext
            && !$familyContext['full']
            && $familyContext['account']
            && ! $this->isCancelledOrRefunded($data['status'] ?? null)
        ) {
            try {
                $synced = $this->createFamilyMemberFromSale(
                    $familyContext['account'],
                    $familyContext['family_product'],
                    $createdSale,
                    $data
                );
                if ($synced === true) {
                    $familySyncStatus = 'sync_active';
                } elseif ($synced === false) {
                    $familySyncStatus = 'error';
                } // null means skipped (no linked variation/product match); leave status unset.
            } catch (\Throwable $e) {
                Log::warning('Family sync failed from sale', [
                    'sale_id' => $createdSale?->id,
                    'error' => $e->getMessage(),
                ]);
                $familySyncStatus = 'error';
            }
        }

        if ($createdSale && ! $this->isCancelledOrRefunded($createdSale->status ?? null)) {
            $recordLinked = false;
            try {
                $this->ensureRecordLinkColumns();
                $recordLinked = $this->findRecordProductForSale($createdSale->product_name) !== null;
                if ($recordLinked) {
                    $this->createRecordEntryFromSale($createdSale);
                    $sheetSyncStatus = 'sync_active';
                }
            } catch (\Throwable $e) {
                if ($recordLinked) {
                    $sheetSyncStatus = 'error';
                }
                Log::warning('Unable to sync record entry from sale', [
                    'sale_id' => $createdSale->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($createdSale) {
            $this->persistSyncStates($createdSale, $familySyncStatus, $sheetSyncStatus);
        }
        $stockAccountNote = null;
        if ($createdSale) {
            try {
                $assignedRow = $this->assignStockAccountRow($createdSale, $stockMatch);
                $stockAccountNote = $this->renderStockAccountNote(
                    $this->extractStockNote($stockMatch),
                    $assignedRow
                );
            } catch (\Throwable $e) {
                Log::warning('Unable to handle stock account assignment for sale', [
                    'sale_id' => $createdSale->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

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
                'family_status' => $familySyncStatus,
                'sheet_status' => $sheetSyncStatus,
                'stock_account_note' => $stockAccountNote,
            ] : null);
    }

    public function update(Request $request, Sale $sale)
    {
        $this->ensureSaleSyncColumns();
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

        if ($this->isCancelledOrRefunded($newStatus)) {
            $this->removeFamilyMemberForSale($sale);
            $this->removeRecordEntryForSale($sale);
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

        // Sync to sheet records when a linked product is present (mirrors creation flow).
        if (! $this->isCancelledOrRefunded($sale->status ?? null)) {
            try {
                $this->ensureRecordLinkColumns();
                $this->createRecordEntryFromSale($sale);
            } catch (\Throwable $e) {
                Log::warning('Unable to sync record entry from sale update', [
                    'sale_id' => $sale->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->refreshStoredSyncStates($sale);

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
        $hasSaleTable = Schema::hasTable('sale_edit_notifications');
        $hasStockTable = Schema::hasTable('stock_account_edit_logs');
        $hasTable = $hasSaleTable || $hasStockTable;
        $search = trim((string) $request->query('search', ''));
        $searchDigits = preg_replace('/\D+/', '', $search);
        $searchLast4 = strlen($searchDigits) >= 4 ? substr($searchDigits, -4) : null;

        $logs = collect();

        if ($hasTable) {
            $saleLogsQuery = null;
            if ($hasSaleTable) {
                $saleLogsQuery = DB::table('sale_edit_notifications')
                    ->leftJoin('sales', 'sale_edit_notifications.sale_id', '=', 'sales.id')
                    ->leftJoin('users', 'sale_edit_notifications.actor_id', '=', 'users.id')
                    ->select([
                        'sale_edit_notifications.id as id',
                        'sale_edit_notifications.created_at as created_at',
                        'sale_edit_notifications.message as message',
                        DB::raw('sales.serial_number as order_id'),
                        DB::raw('users.name as actor_name'),
                        DB::raw("'orders' as context"),
                    ]);

                if ($search !== '') {
                    $saleLogsQuery->where(function ($query) use ($search, $searchLast4) {
                        $query->where('sales.serial_number', 'like', '%' . $search . '%')
                            ->orWhere('sales.email', 'like', '%' . $search . '%');
                        if ($searchLast4) {
                            $query->orWhereRaw("RIGHT(REGEXP_REPLACE(sales.phone, '[^0-9]+', ''), 4) = ?", [$searchLast4]);
                        } else {
                            $query->orWhere('sales.phone', 'like', '%' . $search . '%');
                        }
                    });
                }
            }

            $stockLogsQuery = null;
            if ($hasStockTable) {
                $stockLogsQuery = DB::table('stock_account_edit_logs')
                    ->leftJoin('users', 'stock_account_edit_logs.actor_id', '=', 'users.id')
                    ->select([
                        'stock_account_edit_logs.id as id',
                        'stock_account_edit_logs.created_at as created_at',
                        'stock_account_edit_logs.message as message',
                        DB::raw('NULL as order_id'),
                        DB::raw('users.name as actor_name'),
                        DB::raw("COALESCE(stock_account_edit_logs.context, 'stock-account') as context"),
                    ]);
                if ($search !== '') {
                    $stockLogsQuery->where('stock_account_edit_logs.message', 'like', '%' . $search . '%');
                }
            }

            $logsQuery = null;
            if ($saleLogsQuery && $stockLogsQuery) {
                $logsQuery = DB::query()->fromSub($saleLogsQuery->unionAll($stockLogsQuery), 'logs');
            } elseif ($saleLogsQuery) {
                $logsQuery = $saleLogsQuery;
            } elseif ($stockLogsQuery) {
                $logsQuery = $stockLogsQuery;
            }

            if ($logsQuery) {
                $logs = $logsQuery
                    ->orderByDesc('created_at')
                    ->paginate($pageSize)
                    ->appends($request->query());
            } else {
                $logs = new LengthAwarePaginator(
                    [],
                    0,
                    $pageSize,
                    1,
                    ['path' => $request->url(), 'query' => $request->query()]
                );
            }
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
            'remarks' => [
                'nullable',
                'string',
                'max:2000',
                function (string $attribute, $value, $fail): void {
                    $wordCount = $this->countWords((string) $value);
                    if ($wordCount > 50) {
                        $fail('The remarks field must not exceed 50 words.');
                    }
                },
            ],
            'product_expiry_days' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'string', Rule::in(['completed', 'refunded', 'pending', 'cancelled'])],
        ]);

        $hasAmount = array_key_exists('sales_amount', $data) && $data['sales_amount'] !== null && $data['sales_amount'] !== '';

        $expiryInput = $data['product_expiry_days'] ?? null;
        $expiryDays = isset($expiryInput) && $expiryInput !== '' ? max(0, (int) $expiryInput) : null;
        $productName = array_key_exists('product_name', $data)
            ? trim((string) $data['product_name'])
            : ($isUpdate ? trim((string) $sale->product_name) : '');
        $remarksValue = array_key_exists('remarks', $data) ? trim((string) $data['remarks']) : null;
        if ($remarksValue !== null) {
            $remarksValue = preg_replace('/\s+/u', ' ', $remarksValue);
            $remarksValue = $remarksValue === '' ? null : $remarksValue;
        }

        return [
            ...$data,
            'sales_amount' => $hasAmount ? (float) $data['sales_amount'] : null,
            'product_expiry_days' => $expiryDays,
            'product_name' => $productName,
            'status' => $data['status'] ?? null,
            'remarks' => $remarksValue,
        ];
    }

    private function countWords(string $value): int
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return 0;
        }

        $parts = preg_split('/\s+/u', $trimmed);
        return is_array($parts) ? count($parts) : 0;
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

    private function getDynamicVariationNames(): \Illuminate\Support\Collection
    {
        if (!Schema::hasTable('product_variations') || !Schema::hasColumn('product_variations', 'is_dynamic')) {
            return collect();
        }

        return DB::table('product_variations')
            ->join('products', 'product_variations.product_id', '=', 'products.id')
            ->where('product_variations.is_dynamic', true)
            ->select(['products.name as product_name', 'product_variations.name as variation_name'])
            ->get()
            ->mapWithKeys(function ($row) {
                $product = trim((string) ($row->product_name ?? ''));
                $variation = trim((string) ($row->variation_name ?? ''));
                if ($product === '' || $variation === '') {
                    return [];
                }
                $full = mb_strtolower($product . ' - ' . $variation);
                return [$full => true];
            });
    }

    private function createRecordEntryFromSale(Sale $sale): void
    {
        $productName = trim((string) $sale->product_name);
        if ($productName === '') {
            return;
        }

        $match = $this->findRecordProductForSale($productName);
        if (!$match) {
            return;
        }

        /** @var RecordProduct $recordProduct */
        $recordProduct = $match['product'];
        $tableName = $recordProduct->table_name;
        if (!$tableName) {
            return;
        }

        $this->ensureRecordTableExists($tableName);

        $purchaseDate = $sale->purchase_date ? Carbon::parse($sale->purchase_date)->toDateString() : null;

        $now = Carbon::now();
        $payload = [
            'product' => $productName,
            'email' => $sale->email,
            'phone' => $sale->phone,
            'serial_number' => $sale->serial_number ?? null,
            'sales_amount' => $sale->sales_amount,
            'purchase_date' => $purchaseDate,
            'expiry' => $sale->product_expiry_days,
            'remaining_days' => $this->computeRecordRemainingDays($purchaseDate, $sale->product_expiry_days),
            'remarks' => $sale->remarks,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        // Upsert by serial number so every linked order is recorded and updates stay in sync.
        if (!empty($sale->serial_number)) {
            $existing = DB::table($tableName)->where('serial_number', $sale->serial_number)->first();
            if ($existing) {
                unset($payload['created_at']);
                DB::table($tableName)
                    ->where('id', $existing->id)
                    ->update($payload);
                return;
            }
        }

        DB::table($tableName)->insert($payload);
    }

    private function removeRecordEntryForSale(Sale $sale): void
    {
        $orderId = trim((string) ($sale->serial_number ?? ''));
        if ($orderId === '') {
            return;
        }

        $productName = trim((string) ($sale->product_name ?? ''));
        if ($productName === '') {
            return;
        }

        try {
            $match = $this->findRecordProductForSale($productName);
            if (!$match) {
                return;
            }

            $recordProduct = $match['product'];
            $tableName = $recordProduct?->table_name;
            if (!$tableName || !Schema::hasTable($tableName)) {
                return;
            }

            DB::table($tableName)->where('serial_number', $orderId)->delete();
        } catch (\Throwable $e) {
            Log::warning('Unable to remove record entry for cancelled/refunded sale', [
                'sale_id' => $sale->id,
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function ensureRecordTableExists(string $tableName): void
    {
        $tableExists = Schema::hasTable($tableName);

        if (!$tableExists) {
            Schema::create($tableName, function (Blueprint $table): void {
                $table->id();
                $table->string('serial_number')->nullable();
                $table->string('email')->nullable();
                $table->string('password')->nullable();
                $table->string('phone')->nullable();
                $table->string('product')->nullable();
                $table->integer('sales_amount')->nullable();
                $table->date('purchase_date')->nullable();
                $table->integer('expiry')->nullable();
                $table->integer('remaining_days')->nullable();
                $table->text('remarks')->nullable();
                $table->string('two_factor')->nullable();
                $table->string('email2')->nullable();
                $table->string('password2')->nullable();
                $table->timestamps();
            });
            return;
        }

        if (!Schema::hasColumn($tableName, 'sales_amount')) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->integer('sales_amount')->nullable()->after('product');
            });
        }

        if (!Schema::hasColumn($tableName, 'serial_number')) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->string('serial_number')->nullable()->after('id');
            });
        }
    }

    private function computeRecordRemainingDays(?string $purchaseDate, ?int $expiryDays): ?int
    {
        if (empty($purchaseDate) || $expiryDays === null) {
            return null;
        }

        try {
            $endDate = Carbon::parse($purchaseDate)->startOfDay()->addDays((int) $expiryDays);
            return Carbon::today()->startOfDay()->diffInDays($endDate, false);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function findRecordProductForSale(?string $productName): ?array
    {
        $normalized = $this->normalizeName($productName);
        if ($normalized === '') {
            return null;
        }

        if (!Schema::hasTable('record_products') || !Schema::hasColumn('record_products', 'linked_product_id')) {
            return null;
        }

        // Base name (text before " - ") for permissive matching.
        $baseName = $normalized;
        if (str_contains($normalized, ' - ')) {
            [$baseName] = array_map(static fn ($v) => trim($v), explode(' - ', $normalized, 2));
        }

        // Cache site products and variations.
        $siteProducts = DB::table('products')->select('id', 'name')->get()->keyBy('id');
        $siteVariations = DB::table('product_variations')
            ->select('id', 'product_id', 'name')
            ->get()
            ->keyBy('id');

        // Pull record products with linked site product names.
        $recordLinks = RecordProduct::query()
            ->whereNotNull('linked_product_id')
            ->get()
            ->map(function (RecordProduct $recordProduct) use ($siteProducts) {
                $recordProduct->site_product_name = $recordProduct->linked_product_id
                    ? ($siteProducts[$recordProduct->linked_product_id]->name ?? null)
                    : null;
                $raw = $recordProduct->linked_variation_ids;
                $recordProduct->linked_variation_ids = $raw
                ? (is_string($raw) ? json_decode($raw, true) : $raw)
                : [];
            return $recordProduct;
        });

        // Try to resolve the site product / variation id from the order name.
        $variationRow = DB::table('product_variations')
            ->join('products', 'product_variations.product_id', '=', 'products.id')
            ->whereRaw('LOWER(CONCAT(products.name, " - ", product_variations.name)) = ?', [$normalized])
            ->select([
                'product_variations.id as variation_id',
                'products.id as product_id',
                'products.name as product_name',
            ])
            ->first();
        $variationId = $variationRow->variation_id ?? null;
        $productIdFromName = $variationRow->product_id
            ?? $siteProducts->first(function ($p) use ($normalized, $baseName) {
                $name = $this->normalizeName($p->name);
                return $name === $normalized || $name === $baseName;
            })?->id;

        // Helper to evaluate a record product against the order name.
        $matchesOrder = function (RecordProduct $recordProduct) use ($normalized, $baseName, $variationId, $siteProducts, $siteVariations) {
            $linkedIds = array_map(static fn ($v) => (int) $v, $recordProduct->linked_variation_ids ?: []);

            // Require explicit site-product link.
            if (!$recordProduct->linked_product_id) return false;
            // If variations are selected, enforce them strictly.
            if (!empty($linkedIds)) {
                if (!$variationId) return false;
                if (!in_array((int) $variationId, $linkedIds, true)) return false;
            } else {
                // No variations selected: skip syncing entirely.
                return false;
            }

            $siteNameNorm = $recordProduct->site_product_name ? $this->normalizeName($recordProduct->site_product_name) : null;
            $recordNameNorm = $this->normalizeName($recordProduct->name);

            // With variation permitted, accept if names align.
            if ($recordNameNorm === $normalized || $recordNameNorm === $baseName) return true;
            if ($siteNameNorm && ($siteNameNorm === $normalized || $siteNameNorm === $baseName || str_contains($normalized, $siteNameNorm) || str_contains($siteNameNorm, $baseName))) return true;
            return false;
        };

        // Priority 1: linked by site product id (and variation if present).
        if ($productIdFromName) {
            $linkedForProduct = $recordLinks->filter(fn ($rp) => (int) $rp->linked_product_id === (int) $productIdFromName);

            if ($linkedForProduct->isNotEmpty()) {
                // Variation-aware filter.
                $matched = $linkedForProduct->first(function (RecordProduct $rp) use ($matchesOrder) {
                    return $matchesOrder($rp);
                });

                if ($matched) {
                    return ['product' => $matched, 'variation_id' => $variationId];
                }
            }
        }

        // Priority 2: any record product whose linked site product name matches the order name.
        $matchedBySiteName = $recordLinks->first(function (RecordProduct $rp) use ($matchesOrder) {
            return $matchesOrder($rp);
        });
        if ($matchedBySiteName) {
            return ['product' => $matchedBySiteName, 'variation_id' => $variationId];
        }

        // Final fallback: apply full match logic (including variation rules) to any linked record product.
        $fallback = $recordLinks->first(function (RecordProduct $rp) use ($matchesOrder) {
            return $matchesOrder($rp);
        });

        return $fallback ? ['product' => $fallback, 'variation_id' => $variationId] : null;
    }

    private function extractStockNote(?array $match): ?string
    {
        if (!$match) {
            return null;
        }

        $note = trim((string) ($match['product']->stock_account_note ?? ''));

        return $note !== '' ? $note : null;
    }

    private function findStockMatchForSale(?string $productName): ?array
    {
        $normalized = $this->normalizeName($productName);
        if ($normalized === '') {
            return null;
        }

        if (!Schema::hasTable('stock_products') || !Schema::hasColumn('stock_products', 'linked_product_id')) {
            return null;
        }

        $baseName = $normalized;
        if (str_contains($normalized, ' - ')) {
            [$baseName] = array_map(static fn ($v) => trim($v), explode(' - ', $normalized, 2));
        }

        $siteProducts = DB::table('products')->select('id', 'name')->get()->keyBy('id');

        $stockLinks = StockProduct::query()
            ->whereNotNull('linked_product_id')
            ->get()
            ->map(function (StockProduct $stockProduct) use ($siteProducts) {
                $stockProduct->site_product_name = $stockProduct->linked_product_id
                    ? ($siteProducts[$stockProduct->linked_product_id]->name ?? null)
                    : null;
                $raw = $stockProduct->linked_variation_ids;
                $stockProduct->linked_variation_ids = $raw
                    ? (is_string($raw) ? json_decode($raw, true) : $raw)
                    : [];
                return $stockProduct;
            });

        $variationRow = DB::table('product_variations')
            ->join('products', 'product_variations.product_id', '=', 'products.id')
            ->whereRaw('LOWER(CONCAT(products.name, " - ", product_variations.name)) = ?', [$normalized])
            ->select([
                'product_variations.id as variation_id',
                'products.id as product_id',
                'products.name as product_name',
            ])
            ->first();
        $variationId = $variationRow->variation_id ?? null;
        $productIdFromName = $variationRow->product_id
            ?? $siteProducts->first(function ($p) use ($normalized, $baseName) {
                $name = $this->normalizeName($p->name);
                return $name === $normalized || $name === $baseName;
            })?->id;

        $matchesOrder = function (StockProduct $stockProduct) use ($normalized, $baseName, $variationId) {
            $linkedIds = array_map(static fn ($v) => (int) $v, $stockProduct->linked_variation_ids ?: []);

            if (!$stockProduct->linked_product_id) {
                return false;
            }
            if (!empty($linkedIds)) {
                if (!$variationId) return false;
                if (!in_array((int) $variationId, $linkedIds, true)) return false;
            } else {
                return false;
            }

            $siteNameNorm = $stockProduct->site_product_name ? $this->normalizeName($stockProduct->site_product_name) : null;
            $stockNameNorm = $this->normalizeName($stockProduct->name);

            if ($stockNameNorm === $normalized || $stockNameNorm === $baseName) return true;
            if ($siteNameNorm && ($siteNameNorm === $normalized || $siteNameNorm === $baseName || str_contains($normalized, $siteNameNorm) || str_contains($siteNameNorm, $baseName))) return true;
            return false;
        };

        if ($productIdFromName) {
            $linkedForProduct = $stockLinks->filter(fn ($sp) => (int) $sp->linked_product_id === (int) $productIdFromName);

            if ($linkedForProduct->isNotEmpty()) {
                $matched = $linkedForProduct->first(function (StockProduct $sp) use ($matchesOrder) {
                    return $matchesOrder($sp);
                });

                if ($matched) {
                    return ['product' => $matched, 'variation_id' => $variationId];
                }
            }
        }

        $matchedBySiteName = $stockLinks->first(function (StockProduct $sp) use ($matchesOrder) {
            return $matchesOrder($sp);
        });
        if ($matchedBySiteName) {
            return ['product' => $matchedBySiteName, 'variation_id' => $variationId];
        }

        $fallback = $stockLinks->first(function (StockProduct $sp) use ($matchesOrder) {
            return $matchesOrder($sp);
        });

        return $fallback ? ['product' => $fallback, 'variation_id' => $variationId] : null;
    }

    private function hasEmptyStockRow(?array $match): bool
    {
        if (!$match) {
            return false;
        }

        /** @var StockProduct $stockProduct */
        $stockProduct = $match['product'];
        $tableName = $stockProduct->table_name ?? null;
        if (!$tableName || !Schema::hasTable($tableName)) {
            return false;
        }

        $rowQuery = DB::table($tableName)->whereRaw("COALESCE(TRIM(phone), '') = ''");
        if (Schema::hasColumn($tableName, 'stock_index')) {
            $rowQuery->orderBy('stock_index');
        }

        return $rowQuery->orderBy('id')->exists();
    }

    private function assignStockAccountRow(Sale $sale, ?array $match): ?object
    {
        if (!$match) {
            return null;
        }

        /** @var StockProduct $stockProduct */
        $stockProduct = $match['product'];
        $tableName = $stockProduct->table_name ?? null;
        if (!$tableName || !Schema::hasTable($tableName)) {
            return null;
        }

        if (!Schema::hasColumn($tableName, 'phone') || !Schema::hasColumn($tableName, 'serial_number')) {
            return null;
        }

        $expiryDays = null;
        $variationId = $match['variation_id'] ?? null;
        if ($variationId) {
            $expiryDays = DB::table('product_variations')
                ->where('id', $variationId)
                ->value('expiry_days');
        }

        $rowQuery = DB::table($tableName)
            ->whereRaw("COALESCE(TRIM(phone), '') = ''");
        if (Schema::hasColumn($tableName, 'stock_index')) {
            $rowQuery->orderBy('stock_index');
        }
        $row = $rowQuery
            ->orderBy('id')
            ->first(['id']);

        if (!$row) {
            return null;
        }

        $payload = [
            'serial_number' => $sale->serial_number ?? null,
            'phone' => $sale->phone,
        ];

        if (Schema::hasColumn($tableName, 'purchase_date')) {
            $payload['purchase_date'] = $sale->purchase_date
                ? Carbon::parse($sale->purchase_date)->toDateString()
                : null;
        }

        if (Schema::hasColumn($tableName, 'expiry')) {
            $payload['expiry'] = $expiryDays !== null ? (int) $expiryDays : null;
        }

        DB::table($tableName)
            ->where('id', $row->id)
            ->update($payload);

        $columns = [
            'id',
            'stock_index',
            'serial_number',
            'purchase_date',
            'product',
            'email',
            'password',
            'phone',
            'sales_amount',
            'expiry',
            'remaining_days',
            'remarks',
            'two_factor',
            'email2',
            'password2',
        ];

        $assigned = DB::table($tableName)->where('id', $row->id)->first($columns);

        return $assigned ? $this->decryptStockRow($assigned) : null;
    }

    private function decryptStockRow(object $row): object
    {
        foreach (['password', 'password2'] as $field) {
            if (!isset($row->{$field}) || $row->{$field} === null) {
                continue;
            }

            try {
                $row->{$field} = Crypt::decryptString($row->{$field});
            } catch (\Throwable $e) {
                Log::warning("Unable to decrypt {$field} for stock row", [
                    'row_id' => $row->id ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $row;
    }

    private function renderStockAccountNote(?string $template, ?object $row): ?string
    {
        if ($template === null) {
            return null;
        }

        if (!$row) {
            return $template;
        }

        $map = [
            'index' => 'stock_index',
            'order_id' => 'serial_number',
            'orderid' => 'serial_number',
            'purchase' => 'purchase_date',
            'purchase_date' => 'purchase_date',
            'product' => 'product',
            'email' => 'email',
            'password' => 'password',
            'phone' => 'phone',
            'price' => 'sales_amount',
            'sales_amount' => 'sales_amount',
            'period' => 'expiry',
            'expiry' => 'expiry',
            'remaining' => 'remaining_days',
            'remaining_days' => 'remaining_days',
            'remarks' => 'remarks',
            '2fa' => 'two_factor',
            'two_factor' => 'two_factor',
            'email2' => 'email2',
            'password2' => 'password2',
        ];

        return preg_replace_callback('/\{([^}]+)\}/', function ($matches) use ($row, $map) {
            $rawKey = trim($matches[1]);
            if ($rawKey === '') {
                return $matches[0];
            }

            $normalized = strtolower(preg_replace('/\s+/', '_', $rawKey));
            $column = $map[$normalized] ?? $normalized;
            if (!property_exists($row, $column)) {
                return $matches[0];
            }

            $value = $row->{$column};
            if ($value === null) {
                return '';
            }

            return (string) $value;
        }, $template);
    }

    private function normalizeName(?string $value): string
    {
        $trimmed = trim((string) $value);
        $squeezed = preg_replace('/\s+/', ' ', $trimmed);
        return mb_strtolower($squeezed ?? '');
    }

    private function ensureRecordLinkColumns(): void
    {
        if (!Schema::hasTable('record_products')) {
            return;
        }

        if (!Schema::hasColumn('record_products', 'linked_product_id')) {
            Schema::table('record_products', function (Blueprint $table) {
                $table->foreignId('linked_product_id')->nullable()->after('table_name')->constrained('products')->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('record_products', 'linked_variation_ids')) {
            Schema::table('record_products', function (Blueprint $table) {
                $table->json('linked_variation_ids')->nullable()->after('linked_product_id');
            });
        }
    }

    private function findFamilyAccountForProduct(?string $productName): ?array
    {
        if (!$productName) {
            return null;
        }

        $normalized = mb_strtolower(trim($productName));
        $baseName = $normalized;

        if (str_contains($normalized, ' - ')) {
            [$baseName] = array_map(static fn ($v) => trim($v), explode(' - ', $normalized, 2));
        }

        $variationRow = DB::table('product_variations')
            ->join('products', 'product_variations.product_id', '=', 'products.id')
            ->whereRaw('LOWER(CONCAT(products.name, " - ", product_variations.name)) = ?', [$normalized])
            ->select([
                'product_variations.id as variation_id',
                'products.id as product_id',
                'products.name as product_name',
            ])
            ->first();

        $product = $variationRow
            ? (object) ['id' => $variationRow->product_id, 'name' => $variationRow->product_name]
            : DB::table('products')->whereRaw('LOWER(name) = ?', [$baseName])->first();

        $candidates = collect();

        if ($product) {
            $candidates = $candidates->merge(
                DB::table('family_products')
                    ->where('linked_product_id', $product->id)
                    ->get()
            );
        }

        $candidates = $candidates->merge(
            DB::table('family_products')
                ->whereRaw('LOWER(name) = ?', [$baseName])
                ->get()
        );

        $familyProduct = $candidates->first();

        if (!$familyProduct) {
            return null;
        }

        $account = DB::table('family_accounts')
            ->where('family_product_id', $familyProduct->id)
            ->orderByDesc('account_index')
            ->orderByDesc('id')
            ->first();

        if (!$account) {
            return [
                'family_product' => $familyProduct,
                'account' => null,
                'member_count' => 0,
                'full' => false,
            ];
        }

        $memberCount = DB::table('family_members')->where('family_account_id', $account->id)->count();
        $capacity = $account->capacity;
        $full = $capacity !== null && $memberCount >= $capacity;

        return [
            'family_product' => $familyProduct,
            'account' => $account,
            'member_count' => $memberCount,
            'full' => $full,
        ];
    }

    /**
     * @return bool|null true = synced, false = failed, null = skipped (not a linked variation/product)
     */
    private function createFamilyMemberFromSale(object $account, object $familyProduct, Sale $sale, array $data): ?bool
    {
        $variationContext = $this->getSaleVariationContext($sale);
        $expiryDays = $variationContext['expiry_days'];
        $variationId = $variationContext['variation_id'];
        $variationProductId = $variationContext['variation_product_id'];

        if (!$this->matchesFamilyLink($familyProduct, $variationId, $variationProductId)) {
            return null; // not linked; skip silently
        }

        $payload = [
            'family_name' => $account->name ?? null,
            'email' => $sale->email,
            'order_id' => $sale->serial_number ?? null,
            'phone' => $sale->phone,
            'product' => $sale->product_name,
            'sales_amount' => $sale->sales_amount,
            'purchase_date' => $sale->purchase_date ? Carbon::parse($sale->purchase_date)->toDateString() : null,
            'expiry' => $expiryDays,
            'remarks' => $this->encryptFamilyRemark($sale->remarks),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('family_members', 'family_product_name')) {
            $payload['family_product_name'] = $familyProduct->name ?? null;
        }
        if (Schema::hasColumn('family_members', 'family_product_id')) {
            $payload['family_product_id'] = $familyProduct->id;
        }
        if (Schema::hasColumn('family_members', 'family_account_id')) {
            $payload['family_account_id'] = $account->id;
        }

        try {
            DB::table('family_members')->insert($payload);
            return true;
        } catch (\Throwable $e) {
            Log::warning('Unable to sync family member from sale', [
                'sale_id' => $sale->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    private function encryptFamilyRemark(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        try {
            return Crypt::encryptString($value);
        } catch (\Throwable $e) {
            Log::warning('Unable to encrypt family remark from sale', ['error' => $e->getMessage()]);
            return $value;
        }
    }

    private function getSaleVariationContext(Sale $sale): array
    {
        return $this->getVariationContextForName($sale->product_name);
    }

    private function getVariationContextForName(?string $productName): array
    {
        $expiryDays = null;
        $variationId = null;
        $variationProductId = null;

        if ($productName) {
            $normalized = mb_strtolower(trim($productName));
            $variationRow = DB::table('product_variations')
                ->join('products', 'product_variations.product_id', '=', 'products.id')
                ->whereRaw('LOWER(CONCAT(products.name, " - ", product_variations.name)) = ?', [$normalized])
                ->select([
                    'product_variations.id as variation_id',
                    'product_variations.product_id as variation_product_id',
                    'product_variations.expiry_days',
                ])
                ->first();
            $expiryDays = $variationRow?->expiry_days ?? null;
            $variationId = $variationRow?->variation_id;
            $variationProductId = $variationRow?->variation_product_id;
        }

        return [
            'expiry_days' => $expiryDays,
            'variation_id' => $variationId,
            'variation_product_id' => $variationProductId,
        ];
    }

    private function matchesFamilyLink(object $familyProduct, ?int $variationId, ?int $variationProductId): bool
    {
        $linkedIds = [];
        if (!empty($familyProduct->linked_variation_ids)) {
            $decoded = is_string($familyProduct->linked_variation_ids)
                ? json_decode($familyProduct->linked_variation_ids, true)
                : $familyProduct->linked_variation_ids;
            $linkedIds = is_array($decoded) ? array_map('intval', array_filter($decoded, fn ($v) => $v !== null)) : [];
        }

        if (!empty($linkedIds)) {
            return $variationId !== null && in_array((int) $variationId, $linkedIds, true);
        }

        if (!empty($familyProduct->linked_product_id) && $variationProductId !== null) {
            return (int) $variationProductId === (int) $familyProduct->linked_product_id;
        }

        return true; // no link constraints -> treat as linked
    }

    private function appendFamilySyncStatus(LengthAwarePaginator $sales): void
    {
        if ($sales->isEmpty()) {
            return;
        }

        $hasFamilyMembers = Schema::hasTable('family_members') && Schema::hasColumn('family_members', 'order_id');

        $sales->getCollection()->transform(function (Sale $sale) use ($hasFamilyMembers) {
            $sale->family_sync_state = $this->determineFamilySyncState($sale, $hasFamilyMembers);
            return $sale;
        });
    }

    private function appendSheetSyncStatus(LengthAwarePaginator $sales): void
    {
        if ($sales->isEmpty()) {
            return;
        }

        $this->ensureRecordLinkColumns();

        $sales->getCollection()->transform(function (Sale $sale) {
            $sale->sheet_sync_state = $this->determineSheetSyncState($sale);
            return $sale;
        });
    }

    private function determineFamilySyncState(Sale $sale, bool $hasFamilyMembers): string
    {
        $productName = trim((string) ($sale->product_name ?? ''));
        if ($productName === '') {
            return 'unlinked';
        }

        $familyContext = $this->findFamilyAccountForProduct($productName);
        if (!$familyContext || empty($familyContext['family_product'])) {
            return 'unlinked';
        }
        if (empty($familyContext['account'])) {
            return 'expired';
        }

        $variationContext = $this->getSaleVariationContext($sale);
        $isLinked = $this->matchesFamilyLink(
            $familyContext['family_product'],
            $variationContext['variation_id'] ? (int) $variationContext['variation_id'] : null,
            $variationContext['variation_product_id'] ? (int) $variationContext['variation_product_id'] : null
        );

        if (!$isLinked) {
            return 'unlinked';
        }

        if (!$hasFamilyMembers) {
            return 'error';
        }

        $hasRecord = DB::table('family_members')
            ->where('order_id', $sale->serial_number)
            ->exists();

        if ($hasRecord) {
            return 'active';
        }

        return $sale->family_sync_state === 'active' ? 'expired' : 'error';
    }

    private function determineSheetSyncState(Sale $sale): string
    {
        $productName = trim((string) ($sale->product_name ?? ''));
        if ($productName === '') {
            return 'unlinked';
        }

        $match = $this->findRecordProductForSale($productName);
        if (!$match || empty($match['product'])) {
            return 'unlinked';
        }

        $recordProduct = $match['product'];
        $tableName = $recordProduct->table_name ?? null;
        if (!$tableName || !Schema::hasTable($tableName)) {
            return 'error';
        }

        $serial = trim((string) ($sale->serial_number ?? ''));
        if ($serial === '') {
            return 'error';
        }

        $hasRecord = DB::table($tableName)
            ->where('serial_number', $serial)
            ->exists();

        if ($hasRecord) {
            return 'active';
        }

        return $sale->sheet_sync_state === 'active' ? 'expired' : 'error';
    }

    private function ensureSaleSyncColumns(): void
    {
        if (!Schema::hasTable('sales')) {
            return;
        }

        if (!Schema::hasColumn('sales', 'family_sync_state')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->string('family_sync_state')->nullable()->after('status');
            });
        }

        if (!Schema::hasColumn('sales', 'sheet_sync_state')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->string('sheet_sync_state')->nullable()->after('family_sync_state');
            });
        }
    }

    private function persistSyncStates(Sale $sale, ?string $familyStatus, ?string $sheetStatus): void
    {
        $payload = [];
        if (Schema::hasColumn('sales', 'family_sync_state')) {
            $mapped = $this->mapSyncStatus($familyStatus);
            if ($mapped !== null) {
                $payload['family_sync_state'] = $mapped;
            }
        }
        if (Schema::hasColumn('sales', 'sheet_sync_state')) {
            $mapped = $this->mapSyncStatus($sheetStatus);
            if ($mapped !== null) {
                $payload['sheet_sync_state'] = $mapped;
            }
        }

        if (!empty($payload)) {
            DB::table('sales')->where('id', $sale->id)->update($payload);
            $sale->fill($payload);
        }
    }

    private function refreshStoredSyncStates(Sale $sale): void
    {
        $payload = [];

        if (Schema::hasColumn('sales', 'family_sync_state') && Schema::hasTable('family_members')) {
            $orderId = trim((string) ($sale->serial_number ?? ''));
            if ($orderId !== '' && Schema::hasColumn('family_members', 'order_id')) {
                $hasMember = DB::table('family_members')
                    ->where('order_id', $orderId)
                    ->exists();
                if ($hasMember) {
                    $payload['family_sync_state'] = 'active';
                }
            }
        }

        if (Schema::hasColumn('sales', 'sheet_sync_state')) {
            $productName = trim((string) ($sale->product_name ?? ''));
            $serial = trim((string) ($sale->serial_number ?? ''));
            if ($productName !== '' && $serial !== '') {
                $match = $this->findRecordProductForSale($productName);
                if ($match && !empty($match['product'])) {
                    $tableName = $match['product']->table_name ?? null;
                    if ($tableName && Schema::hasTable($tableName)) {
                        $hasRecord = DB::table($tableName)
                            ->where('serial_number', $serial)
                            ->exists();
                        if ($hasRecord) {
                            $payload['sheet_sync_state'] = 'active';
                        }
                    }
                }
            }
        }

        if (!empty($payload)) {
            DB::table('sales')->where('id', $sale->id)->update($payload);
            $sale->fill($payload);
        }
    }

    private function mapSyncStatus(?string $status): ?string
    {
        return match ($status) {
            'sync_active' => 'active',
            'error' => 'error',
            default => null,
        };
    }

    private function removeFamilyMemberForSale(Sale $sale): void
    {
        if (!Schema::hasTable('family_members') || !Schema::hasColumn('family_members', 'order_id')) {
            return;
        }

        $orderId = trim((string) ($sale->serial_number ?? ''));
        if ($orderId === '') {
            return;
        }

        try {
            DB::table('family_members')->where('order_id', $orderId)->delete();
        } catch (\Throwable $e) {
            Log::warning('Unable to remove family member for cancelled/refunded sale', [
                'sale_id' => $sale->id,
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function isCancelledOrRefunded(?string $status): bool
    {
        $normalized = strtolower(trim((string) $status));
        return $normalized === 'cancelled' || $normalized === 'refunded';
    }
}
