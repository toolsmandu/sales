<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Sale;
use App\Models\PaymentTransaction;
use App\Services\SerialNumberGenerator;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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
            'serial_number' => trim((string) $request->query('serial_number', '')),
            'phone' => trim((string) $request->query('phone', '')),
            'email' => trim((string) $request->query('email', '')),
            'product_name' => trim((string) $request->query('product_name', '')),
        ];

        $filters['date_from'] = $this->normalizeDateInput($request->query('date_from'));
        $filters['date_to'] = $this->normalizeDateInput($request->query('date_to'));

        if ($filters['date_from'] && $filters['date_to'] && $filters['date_from'] > $filters['date_to']) {
            [$filters['date_from'], $filters['date_to']] = [$filters['date_to'], $filters['date_from']];
        }

        $salesQuery = Sale::with(['paymentMethod', 'createdBy']);

        if ($filters['serial_number'] !== '') {
            $salesQuery->where('serial_number', 'like', '%' . $filters['serial_number'] . '%');
        }

        if ($filters['phone'] !== '') {
            $salesQuery->where('phone', 'like', '%' . $filters['phone'] . '%');
        }

        if ($filters['email'] !== '') {
            $emailTerm = '%' . $filters['email'] . '%';
            $salesQuery->where(function ($query) use ($emailTerm) {
                $query->where('email', 'like', $emailTerm)
                    ->orWhere('remarks', 'like', $emailTerm);
            });
        }

        if ($filters['product_name'] !== '') {
            $salesQuery->where('product_name', 'like', '%' . $filters['product_name'] . '%');
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

        $paymentMethods = PaymentMethod::orderBy('label')->get();
        $salesByMethod = Sale::query()
            ->selectRaw('payment_method_id, COUNT(*) as sale_count, COALESCE(SUM(sales_amount), 0) as total_sales')
            ->groupBy('payment_method_id')
            ->get()
            ->keyBy('payment_method_id');

        $paymentMethodSummaries = $paymentMethods->map(function (PaymentMethod $method) use ($salesByMethod) {
            $summary = $salesByMethod->get($method->id);
            $total = (float) ($summary->total_sales ?? 0);
            $count = (int) ($summary->sale_count ?? 0);

            return [
                'label' => $method->label,
                'slug' => $method->slug,
                'available_balance' => $total,
                'sale_count' => $count,
            ];
        });

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
            $saleToEdit = Sale::with('paymentMethod')->find($request->input('edit'));
        }

        return view('sales.index', [
            'sales' => $sales,
            'paymentMethods' => $paymentMethods,
            'paymentMethodSummaries' => $paymentMethodSummaries,
            'productOptions' => $productOptions,
            'saleToEdit' => $saleToEdit,
            'perPage' => $perPage,
            'filters' => $filters,
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
            ->with(['paymentMethod', 'createdBy'])
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

    public function store(Request $request)
    {
        $nowKathmandu = Carbon::now('Asia/Kathmandu');

        if (!$request->filled('purchase_date')) {
            $request->merge([
                'purchase_date' => $nowKathmandu->toDateString(),
            ]);
        }

        $data = $this->validatePayload($request);

        if ($data['product_expiry_days'] === null) {
            $data['product_expiry_days'] = $this->inferExpiryDaysFromProductName($data['product_name']);
        }
        $data['status'] = $data['status'] ?? 'completed';

        $createdBy = $request->user()?->id;

        $createdSale = null;

        DB::transaction(function () use ($data, $nowKathmandu, $createdBy, &$createdSale) {
            $method = PaymentMethod::where('slug', $data['payment_method'])->firstOrFail();
            $purchaseDate = Carbon::createFromFormat('Y-m-d', $data['purchase_date'])->startOfDay();
            $occurredAt = $nowKathmandu->copy();

            $sale = Sale::create([
                'serial_number' => $this->serials->next(),
                'purchase_date' => $purchaseDate,
                'product_name' => $data['product_name'],
                'product_expiry_days' => $data['product_expiry_days'],
                'remarks' => $data['remarks'],
                'phone' => $data['phone'],
                'email' => trim($data['email'] ?? ''),
                'sales_amount' => $data['sales_amount'],
                'payment_method_id' => $method->id,
                'created_by' => $createdBy,
                'status' => $data['status'],
            ]);

            PaymentTransaction::create([
                'payment_method_id' => $method->id,
                'sale_id' => $sale->id,
                'type' => 'income',
                'amount' => $sale->sales_amount,
                'phone' => $sale->phone,
                'occurred_at' => $occurredAt,
            ]);

            $method->recalculateBalance();

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
            ->with('status', $message)
            ->with('saleConfirmation', $createdSale ? [
                'serial_number' => $createdSale->serial_number,
                'product_name' => $createdSale->product_name,
                'phone' => $createdSale->phone,
                'email' => $createdSale->email,
            ] : null);
    }

    public function update(Request $request, Sale $sale)
    {
        $data = $this->validatePayload($request, $sale);

        if ($data['product_expiry_days'] === null) {
            $data['product_expiry_days'] = $this->inferExpiryDaysFromProductName($data['product_name']);
        }
        $data['status'] = $data['status'] ?? $sale->status ?? 'completed';

        DB::transaction(function () use ($sale, $data) {
            $newMethod = PaymentMethod::where('slug', $data['payment_method'])->firstOrFail();
            $previousMethod = $sale->paymentMethod;
            $purchaseDate = Carbon::createFromFormat('Y-m-d', $data['purchase_date'])->startOfDay();
            $occurredAt = Carbon::now('Asia/Kathmandu');

            $sale->update([
                'purchase_date' => $purchaseDate,
                'product_name' => $data['product_name'],
                'product_expiry_days' => $data['product_expiry_days'],
                'remarks' => $data['remarks'],
                'phone' => $data['phone'],
                'email' => trim($data['email'] ?? ''),
                'sales_amount' => $data['sales_amount'],
                'payment_method_id' => $newMethod->id,
                'status' => $data['status'],
            ]);

            $transaction = $sale->transaction;

            if (!$transaction) {
                $transaction = new PaymentTransaction([
                    'sale_id' => $sale->id,
                    'type' => 'income',
                ]);
            }

            $transaction->fill([
                'payment_method_id' => $newMethod->id,
                'amount' => $sale->sales_amount,
                'phone' => $sale->phone,
                'occurred_at' => $occurredAt,
            ]);

            $transaction->save();

            if ($previousMethod && $previousMethod->isNot($newMethod)) {
                $previousMethod->recalculateBalance();
            }

            $newMethod->recalculateBalance();
        });

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
            $method = $sale->paymentMethod;

            $sale->transaction()->delete();
            $sale->delete();

            $method?->recalculateBalance();
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
            ->with(['paymentMethod', 'createdBy'])
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
     *     payment_method: string,
     *     purchase_date: string,
     *     remarks: string,
     *     product_expiry_days: ?int,
     *     status: ?string
     * }
     */
    private function validatePayload(Request $request, ?Sale $sale = null): array
    {
        $data = $request->validate([
            'product_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'sales_amount' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'string', 'exists:payment_methods,slug'],
            'purchase_date' => ['required', 'date_format:Y-m-d'],
            'remarks' => ['required', 'string', 'max:255'],
            'product_expiry_days' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'string', Rule::in(['completed', 'refunded'])],
        ]);

        $expiryInput = $data['product_expiry_days'] ?? null;
        $expiryDays = isset($expiryInput) && $expiryInput !== '' ? max(0, (int) $expiryInput) : null;

        return [
            ...$data,
            'product_expiry_days' => $expiryDays,
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
