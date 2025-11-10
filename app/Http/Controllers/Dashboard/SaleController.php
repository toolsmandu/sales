<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Sale;
use App\Models\PaymentTransaction;
use App\Services\SerialNumberGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function __construct(
        private readonly SerialNumberGenerator $serials
    ) {
    }

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
            $salesQuery->where('email', 'like', '%' . $filters['email'] . '%');
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
            ->with(['variations' => fn ($query) => $query->orderBy('name')])
            ->orderBy('name')
            ->get()
            ->flatMap(function (Product $product) {
                $name = trim($product->name);
                if ($name === '') {
                    return [];
                }

                $options = [];

                foreach ($product->variations as $variation) {
                    $variationName = trim((string) $variation->name);
                    if ($variationName === '') {
                        continue;
                    }

                    $options[] = sprintf('%s - %s', $name, $variationName);
                }

                return $options;
            })
            ->unique()
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

    public function store(Request $request)
    {
        $nowKathmandu = Carbon::now('Asia/Kathmandu');

        $request->merge([
            'purchase_date' => $nowKathmandu->toDateString(),
        ]);

        $data = $this->validatePayload($request);

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
                'remarks' => $data['remarks'] ?? null,
                'phone' => $data['phone'],
                'email' => $data['email'],
                'sales_amount' => $data['sales_amount'],
                'payment_method_id' => $method->id,
                'created_by' => $createdBy,
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
            ->route('sales.index')
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

        DB::transaction(function () use ($sale, $data) {
            $newMethod = PaymentMethod::where('slug', $data['payment_method'])->firstOrFail();
            $previousMethod = $sale->paymentMethod;
            $purchaseDate = Carbon::createFromFormat('Y-m-d', $data['purchase_date'])->startOfDay();
            $occurredAt = Carbon::now('Asia/Kathmandu');

            $sale->update([
                'purchase_date' => $purchaseDate,
                'product_name' => $data['product_name'],
                'remarks' => $data['remarks'] ?? null,
                'phone' => $data['phone'],
                'email' => $data['email'],
                'sales_amount' => $data['sales_amount'],
                'payment_method_id' => $newMethod->id,
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
            ->route('sales.index')
            ->with('status', $message);
    }

    public function destroy(Request $request, Sale $sale)
    {
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
            ->route('sales.index')
            ->with('status', $message);
    }

    /**
     * @return array{
     *     product_name: string,
     *     phone: string,
     *     email: string,
     *     sales_amount: float,
     *     payment_method: string,
     *     purchase_date: string,
     *     remarks?: string
     * }
     */
    private function validatePayload(Request $request, ?Sale $sale = null): array
    {
        return $request->validate([
            'product_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'sales_amount' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'string', 'exists:payment_methods,slug'],
            'purchase_date' => ['required', 'date_format:Y-m-d'],
            'remarks' => ['nullable', 'string', 'max:255'],
        ]);
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
}
