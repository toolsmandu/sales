<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Support\Carbon;

class PaymentMethodController extends Controller
{
    public function index(): View
    {
        $paymentMethods = PaymentMethod::query()
            ->orderBy('label')
            ->get();

        return view('payments.manage', [
            'paymentMethods' => $paymentMethods,
        ]);
    }

    public function balance(Request $request): View
    {
        $monthInput = $request->input('month');
        $selectedMonth = null;
        if ($request->has('month') && $monthInput !== '') {
            $candidateMonth = (int) $monthInput;
            if ($candidateMonth >= 1 && $candidateMonth <= 12) {
                $selectedMonth = $candidateMonth;
            }
        }

        $yearOptions = PaymentTransaction::query()
            ->whereNotNull('occurred_at')
            ->selectRaw('YEAR(occurred_at) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn ($year) => (int) $year);

        $selectedYear = null;
        if ($request->has('year') && $request->input('year') !== '') {
            $candidateYear = (int) $request->input('year');
            if ($yearOptions->contains($candidateYear)) {
                $selectedYear = $candidateYear;
            }
        }

        $methods = PaymentMethod::query()
            ->orderBy('label')
            ->get();

        $transactionTotals = PaymentTransaction::query()
            ->selectRaw('payment_method_id')
            ->selectRaw('SUM(CASE WHEN type = "income" THEN amount ELSE 0 END) as income_total')
            ->selectRaw('SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END) as expense_total')
            ->selectRaw('COUNT(CASE WHEN type = "income" THEN 1 END) as income_count')
            ->groupBy('payment_method_id')
            ->get()
            ->keyBy('payment_method_id');

        $paymentMethodSummaries = $methods->map(function (PaymentMethod $method) use ($transactionTotals) {
            $totals = $transactionTotals->get($method->id);
            $incomeTotal = (float) ($totals->income_total ?? 0);
            $expenseTotal = (float) ($totals->expense_total ?? 0);
            $incomeCount = (int) ($totals->income_count ?? 0);

            return [
                'label' => $method->label,
                'slug' => $method->slug,
                'sale_count' => $incomeCount,
                'payment_in_total' => $incomeTotal,
                'withdrawal_total' => $expenseTotal,
                'available_balance' => $incomeTotal - $expenseTotal,
            ];
        });

        $totalAvailableBalance = $paymentMethodSummaries->sum(fn (array $summary) => $summary['available_balance']);

        $monthlyTotalsQuery = PaymentTransaction::query()
            ->selectRaw('payment_methods.id as method_id')
            ->selectRaw('payment_methods.label as label')
            ->selectRaw('payment_methods.slug as slug')
            ->selectRaw('YEAR(payment_transactions.occurred_at) as year')
            ->selectRaw('MONTH(payment_transactions.occurred_at) as month')
            ->selectRaw('SUM(CASE WHEN payment_transactions.type = "income" THEN payment_transactions.amount ELSE 0 END) as income_total')
            ->selectRaw('SUM(CASE WHEN payment_transactions.type = "expense" THEN payment_transactions.amount ELSE 0 END) as expense_total')
            ->selectRaw('COUNT(CASE WHEN payment_transactions.type = "income" THEN 1 END) as income_count')
            ->join('payment_methods', 'payment_transactions.payment_method_id', '=', 'payment_methods.id')
            ->whereNotNull('payment_transactions.occurred_at');

        if ($selectedYear !== null) {
            $monthlyTotalsQuery->whereYear('payment_transactions.occurred_at', $selectedYear);
        }

        if ($selectedMonth !== null) {
            $monthlyTotalsQuery->whereMonth('payment_transactions.occurred_at', $selectedMonth);
        }

        $monthlySummaries = $monthlyTotalsQuery
            ->groupBy('method_id', 'payment_methods.label', 'payment_methods.slug', 'year', 'month')
            ->orderBy('payment_methods.label')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get()
            ->map(function ($row) {
                $monthNumber = (int) $row->month;
                $monthLabel = $monthNumber > 0
                    ? Carbon::create()->month($monthNumber)->format('F')
                    : '-';
                $incomeTotal = (float) ($row->income_total ?? 0);
                $expenseTotal = (float) ($row->expense_total ?? 0);

                return [
                    'label' => $row->label,
                    'slug' => $row->slug,
                    'year' => (int) $row->year,
                    'month' => $monthNumber,
                    'month_label' => $monthLabel,
                    'sale_count' => (int) ($row->income_count ?? 0),
                    'income_total' => $incomeTotal,
                    'withdrawal_total' => $expenseTotal,
                    'total_amount' => $incomeTotal - $expenseTotal,
                ];
            });

        $monthlySummaryTotals = [
            'income_total' => $monthlySummaries->sum(fn (array $summary) => $summary['income_total']),
            'withdrawal_total' => $monthlySummaries->sum(fn (array $summary) => $summary['withdrawal_total']),
            'sale_count' => $monthlySummaries->sum(fn (array $summary) => $summary['sale_count']),
        ];

        $monthOptions = collect(range(1, 12))->map(fn ($month) => [
            'value' => $month,
            'label' => Carbon::create()->month($month)->format('F'),
        ]);

        return view('payments.balance', [
            'paymentMethodSummaries' => $paymentMethodSummaries,
            'monthlySummaries' => $monthlySummaries,
            'monthOptions' => $monthOptions,
            'yearOptions' => $yearOptions,
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
            'totalAvailableBalance' => $totalAvailableBalance,
            'monthlySummaryTotals' => $monthlySummaryTotals,
        ]);
    }

    public function statements(Request $request): View
    {
        $monthOptions = collect(range(1, 12))->map(fn ($month) => [
            'value' => $month,
            'label' => Carbon::create()->month($month)->format('F'),
        ]);

        $selectedMonth = null;
        if ($request->filled('month')) {
            $candidateMonth = (int) $request->input('month');
            if ($candidateMonth >= 1 && $candidateMonth <= 12) {
                $selectedMonth = $candidateMonth;
            }
        }

        $yearOptions = PaymentTransaction::query()
            ->whereNotNull('occurred_at')
            ->selectRaw('YEAR(occurred_at) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn ($year) => (int) $year);

        $selectedYear = null;
        if ($request->filled('year')) {
            $candidateYear = (int) $request->input('year');
            if ($yearOptions->contains($candidateYear)) {
                $selectedYear = $candidateYear;
            }
        }

        $now = Carbon::now('Asia/Kathmandu');
        if ($selectedMonth === null) {
            $selectedMonth = $now->month;
        }

        if ($selectedYear === null) {
            $currentYear = (int) $now->year;
            if ($yearOptions->contains($currentYear)) {
                $selectedYear = $currentYear;
            } elseif ($yearOptions->isNotEmpty()) {
                $selectedYear = $yearOptions->first();
            }
        }

        $methods = PaymentMethod::query()
            ->orderBy('label')
            ->get();

        $transactionTotals = PaymentTransaction::query()
            ->selectRaw('payment_method_id')
            ->selectRaw('SUM(CASE WHEN type = "income" THEN amount ELSE 0 END) as income_total')
            ->selectRaw('SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END) as expense_total')
            ->selectRaw('COUNT(CASE WHEN type = "income" THEN 1 END) as income_count')
            ->groupBy('payment_method_id')
            ->get()
            ->keyBy('payment_method_id');

        $paymentMethodSummaries = $methods->map(function (PaymentMethod $method) use ($transactionTotals) {
            $totals = $transactionTotals->get($method->id);
            $incomeTotal = (float) ($totals->income_total ?? 0);
            $expenseTotal = (float) ($totals->expense_total ?? 0);
            $incomeCount = (int) ($totals->income_count ?? 0);

            return [
                'label' => $method->label,
                'slug' => $method->slug,
                'sale_count' => $incomeCount,
                'payment_in_total' => $incomeTotal,
                'withdrawal_total' => $expenseTotal,
                'available_balance' => $incomeTotal - $expenseTotal,
            ];
        });

        $totalAvailableBalance = $paymentMethodSummaries->sum(fn (array $summary) => $summary['available_balance']);

        $monthlyTotalsQuery = PaymentTransaction::query()
            ->selectRaw('payment_methods.id as method_id')
            ->selectRaw('payment_methods.label as label')
            ->selectRaw('payment_methods.slug as slug')
            ->selectRaw('YEAR(payment_transactions.occurred_at) as year')
            ->selectRaw('MONTH(payment_transactions.occurred_at) as month')
            ->selectRaw('SUM(CASE WHEN payment_transactions.type = "income" THEN payment_transactions.amount ELSE 0 END) as income_total')
            ->selectRaw('SUM(CASE WHEN payment_transactions.type = "expense" THEN payment_transactions.amount ELSE 0 END) as expense_total')
            ->selectRaw('COUNT(CASE WHEN payment_transactions.type = "income" THEN 1 END) as income_count')
            ->join('payment_methods', 'payment_transactions.payment_method_id', '=', 'payment_methods.id')
            ->whereNotNull('payment_transactions.occurred_at');

        if ($selectedYear !== null) {
            $monthlyTotalsQuery->whereYear('payment_transactions.occurred_at', $selectedYear);
        }

        if ($selectedMonth !== null) {
            $monthlyTotalsQuery->whereMonth('payment_transactions.occurred_at', $selectedMonth);
        }

        $monthlySummaries = $monthlyTotalsQuery
            ->groupBy('method_id', 'payment_methods.label', 'payment_methods.slug', 'year', 'month')
            ->orderBy('payment_methods.label')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get()
            ->map(function ($row) {
                $monthNumber = (int) $row->month;
                $monthLabel = $monthNumber > 0
                    ? Carbon::create()->month($monthNumber)->format('F')
                    : '-';
                $incomeTotal = (float) ($row->income_total ?? 0);
                $expenseTotal = (float) ($row->expense_total ?? 0);

                return [
                    'label' => $row->label,
                    'slug' => $row->slug,
                    'year' => (int) $row->year,
                    'month' => $monthNumber,
                    'month_label' => $monthLabel,
                    'sale_count' => (int) ($row->income_count ?? 0),
                    'income_total' => $incomeTotal,
                    'withdrawal_total' => $expenseTotal,
                    'total_amount' => $incomeTotal - $expenseTotal,
                ];
            });

        $monthlySummaryTotals = [
            'income_total' => $monthlySummaries->sum(fn (array $summary) => $summary['income_total']),
            'withdrawal_total' => $monthlySummaries->sum(fn (array $summary) => $summary['withdrawal_total']),
            'sale_count' => $monthlySummaries->sum(fn (array $summary) => $summary['sale_count']),
        ];

        $selectedMethod = null;
        if ($methods->isNotEmpty()) {
            $requestedMethod = $request->input('method');
            $selectedMethod = $methods->firstWhere('slug', $requestedMethod) ?? $methods->first();
        }

        $perPageOptions = [50, 100, 200];
        $perPage = (int) $request->input('per_page', 100);
        if (!in_array($perPage, $perPageOptions, true)) {
            $perPage = 100;
        }

        $ledgerPaginator = null;
        $ledgerHasData = false;
        $displayTimezone = config('app.display_timezone', 'Asia/Kathmandu');

        if ($selectedMethod) {
            $chronologicalTransactions = PaymentTransaction::query()
                ->with('sale:id,serial_number,created_at,product_name,phone,remarks')
                ->where('payment_method_id', $selectedMethod->id)
                ->orderBy('occurred_at')
                ->orderBy('id')
                ->get(['id', 'type', 'amount', 'sale_id', 'phone', 'occurred_at', 'created_at']);

            $timestampResolver = static function (PaymentTransaction $transaction, string $timezone) {
                $sale = $transaction->sale;

                if ($transaction->type === 'income' && $sale?->created_at) {
                    return $sale->created_at->copy()->timezone($timezone);
                }

                if ($transaction->type === 'expense' && $transaction->created_at) {
                    return $transaction->created_at->copy()->timezone($timezone);
                }

                if ($transaction->occurred_at) {
                    return $transaction->occurred_at->copy()->timezone($timezone);
                }

                return null;
            };

            $chronologicalTransactions = $chronologicalTransactions
                ->sortBy(function (PaymentTransaction $transaction) use ($timestampResolver, $displayTimezone) {
                    $timestamp = $timestampResolver($transaction, $displayTimezone);
                    $timestampValue = $timestamp?->getTimestamp() ?? 0;

                    return ($timestampValue * 10_000) + $transaction->id;
                })
                ->values();

            $serialNumbers = [];
            $serialLabels = [];
            $transactionTimestamps = [];
            $runningBalances = [];
            $currentSerialNumeric = 0;
            $runningBalance = 0.0;

            foreach ($chronologicalTransactions as $transaction) {
                $timestamp = $timestampResolver($transaction, $displayTimezone);
                $transactionTimestamps[$transaction->id] = $timestamp;

                $amount = (float) $transaction->amount;
                $runningBalance += $transaction->type === 'income' ? $amount : -$amount;
                $runningBalances[$transaction->id] = $runningBalance;

                $saleSerial = $transaction->sale?->serial_number;
                $serialNumeric = null;

                if ($transaction->type === 'income' && $saleSerial && preg_match('/(\d+)/', $saleSerial, $matches)) {
                    $serialNumeric = (int) $matches[1];
                    $currentSerialNumeric = max($currentSerialNumeric, $serialNumeric);
                } else {
                    $currentSerialNumeric++;
                    $serialNumeric = $currentSerialNumeric;
                    $saleSerial = sprintf('%s%d', $transaction->type === 'expense' ? 'WD' : 'TM', $serialNumeric);
                }

                $serialNumbers[$transaction->id] = $serialNumeric;
                $serialLabels[$transaction->id] = $saleSerial;
            }

            $ledgerQuery = PaymentTransaction::query()
                ->with('sale')
                ->where('payment_method_id', $selectedMethod->id)
                ->orderByDesc('occurred_at')
                ->orderByDesc('id');

            $ledgerPaginator = $ledgerQuery
                ->paginate($perPage)
                ->withQueryString();

            $ledgerPaginator->setCollection(
                $ledgerPaginator->getCollection()->map(function (PaymentTransaction $transaction) use ($displayTimezone, $serialNumbers, $serialLabels, $transactionTimestamps, $timestampResolver, $runningBalances) {
                    $sale = $transaction->sale;

                    $serialNumeric = $serialNumbers[$transaction->id] ?? (int) $transaction->id;
                    $serial = $serialLabels[$transaction->id] ?? sprintf('TM%d', $serialNumeric);

                    $incomeAmount = $transaction->type === 'income' ? (float) $transaction->amount : 0.0;
                    $expenseAmount = $transaction->type === 'expense' ? (float) $transaction->amount : 0.0;
                    $withdrawalNote = $transaction->phone ?? '—';

                    $timestamp = $transactionTimestamps[$transaction->id]
                        ?? $timestampResolver($transaction, $displayTimezone);

                    return [
                        'id' => $transaction->id,
                        'serial' => $serial,
                        'serial_numeric' => $serialNumeric,
                        'timestamp' => $timestamp,
                        'phone' => $transaction->type === 'income'
                            ? ($sale?->phone ?? '—')
                            : '—',
                        'product' => $transaction->type === 'income'
                            ? ($sale?->product_name ?? '—')
                            : '—',
                        'remarks' => $transaction->type === 'income'
                            ? ($sale?->remarks ?? '—')
                            : $withdrawalNote,
                        'income' => $incomeAmount,
                        'expense' => $expenseAmount,
                        'balance' => $runningBalances[$transaction->id] ?? 0.0,
                        'type' => $transaction->type,
                        'timestamp' => $timestamp,
                    ];
                })->sort(function (array $a, array $b) {
                    $aTimestamp = $a['timestamp']?->getTimestamp() ?? 0;
                    $bTimestamp = $b['timestamp']?->getTimestamp() ?? 0;

                    if ($aTimestamp !== $bTimestamp) {
                        return $bTimestamp <=> $aTimestamp;
                    }

                    return ($b['id'] ?? 0) <=> ($a['id'] ?? 0);
                })->values()
            );

            $ledgerHasData = $ledgerPaginator->total() > 0;
        }

        return view('payments.statements', [
            'methods' => $methods,
            'selectedMethod' => $selectedMethod,
            'ledgerPaginator' => $ledgerPaginator,
            'ledgerHasData' => $ledgerHasData,
            'perPageOptions' => $perPageOptions,
            'perPage' => $perPage,
            'displayTimezone' => $displayTimezone,
            'paymentMethodSummaries' => $paymentMethodSummaries,
            'monthlySummaries' => $monthlySummaries,
            'monthlySummaryTotals' => $monthlySummaryTotals,
            'totalAvailableBalance' => $totalAvailableBalance,
            'monthOptions' => $monthOptions,
            'yearOptions' => $yearOptions,
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);

        PaymentMethod::create($data);

        $message = 'Payment method created successfully.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
            ], 201);
        }

        return redirect()
            ->route('payments.manage')
            ->with('status', $message);
    }

    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $data = $this->validatePayload($request, $paymentMethod);

        $paymentMethod->update($data);

        $message = 'Payment method updated successfully.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
            ]);
        }

        return redirect()
            ->route('payments.manage')
            ->with('status', $message);
    }

    public function destroy(Request $request, PaymentMethod $paymentMethod)
    {
        if ($paymentMethod->sales()->exists()) {
            $message = 'Cannot delete a payment method that has related sales records.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                ], 422);
            }

            return redirect()
                ->route('payments.manage')
                ->withErrors(['label' => $message]);
        }

        $paymentMethod->delete();

        $message = 'Payment method deleted successfully.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
            ]);
        }

        return redirect()
            ->route('payments.manage')
            ->with('status', $message);
    }

    private function validatePayload(Request $request, ?PaymentMethod $paymentMethod = null): array
    {
        return $request->validate([
            'label' => [
                'required',
                'string',
                'max:255',
                Rule::unique('payment_methods', 'label')->ignore($paymentMethod?->id),
            ],
            'unique_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('payment_methods', 'unique_number')->ignore($paymentMethod?->id),
            ],
            'monthly_limit' => [
                'required',
                'numeric',
                'min:0',
            ],
        ]);
    }
}
