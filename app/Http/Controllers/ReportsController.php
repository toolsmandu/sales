<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()?->isEmployee()) {
            abort(403);
        }

        $today = Carbon::today('Asia/Kathmandu');
        $defaultMonth = $today->month;
        $defaultYear = $today->year;

        $rangeOptions = [
            'last_7_days' => 'In Last 7 Days',
            'last_30_days' => 'In Last 30 Days',
            'last_90_days' => 'In Last 90 Days',
            'last_6_months' => 'In Last 6 Months',
            'last_12_months' => 'In Last 12 Months',
            'lifetime' => 'Lifetime',
        ];

        $selectedRange = $request->query('range', 'custom_month');
        $selectedMonth = $request->has('month') ? (int) $request->query('month', 0) : $defaultMonth;
        $selectedYear = $request->has('year') ? (int) $request->query('year', 0) : $defaultYear;
        $topPerPage = $this->perPage($request->query('top_per_page'));
        $contactPerPage = $this->perPage($request->query('contact_per_page'));

        $startDate = null;
        $endDate = $today;

        if ($selectedRange === 'custom_month') {
            $selectedMonth = $selectedMonth > 0 ? $selectedMonth : $defaultMonth;
            $selectedYear = $selectedYear > 0 ? $selectedYear : $defaultYear;

            $startDate = Carbon::create($selectedYear, $selectedMonth, 1, 0, 0, 0, 'Asia/Kathmandu')->startOfMonth();
            $endDate = (clone $startDate)->endOfMonth();
        } else {
            switch ($selectedRange) {
                case 'last_7_days':
                    $startDate = $today->copy()->subDays(6);
                    break;
                case 'last_30_days':
                    $startDate = $today->copy()->subDays(29);
                    break;
                case 'last_90_days':
                    $startDate = $today->copy()->subDays(89);
                    break;
                case 'last_6_months':
                    $startDate = $today->copy()->subMonths(6)->addDay();
                    break;
                case 'last_12_months':
                    $startDate = $today->copy()->subMonths(12)->addDay();
                    break;
                case 'lifetime':
                default:
                    $startDate = null;
                    break;
            }
        }

        if ($request->query('export') === 'contacts') {
            return $this->exportContactsCsv(
                $this->contactQuery($request, $startDate, $endDate)->get()
            );
        }

        $topProducts = Sale::query()
            ->when($startDate, fn ($query) => $query->whereDate('purchase_date', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->whereDate('purchase_date', '<=', $endDate))
            ->selectRaw('COALESCE(NULLIF(product_name, \'\'), \'Unknown Product\') as product_name')
            ->selectRaw('COUNT(*) as sales_count')
            ->selectRaw('COALESCE(SUM(sales_amount), 0) as total_sales')
            ->groupBy('product_name')
            ->orderByDesc('sales_count')
            ->orderByDesc('total_sales')
            ->paginate($topPerPage, ['*'], 'top_page')
            ->withQueryString();

        $months = collect(range(1, 12))->map(fn ($m) => [
            'value' => $m,
            'label' => Carbon::create(null, $m, 1)->format('F'),
        ]);

        $years = collect(range($today->year, $today->copy()->subYears(9)->year, -1))->values();

        return view('reports.index', [
            'topProducts' => $topProducts,
            'rangeOptions' => $rangeOptions,
            'selectedRange' => $selectedRange,
            'selectedMonth' => $selectedMonth,
            'selectedYear' => $selectedYear,
            'months' => $months,
            'years' => $years,
            'customerRows' => $this->customerRows($request, $startDate, $endDate, $contactPerPage),
            'selectedProduct' => $request->query('product_name'),
            'topPerPage' => $topPerPage,
            'contactPerPage' => $contactPerPage,
            'products' => Product::query()->select(['id', 'name'])->orderBy('name')->get(),
            'monthlyStatement' => $this->monthlyStatement($selectedMonth, $selectedYear),
        ]);
    }

    private function customerRows(Request $request, ?Carbon $startDate, ?Carbon $endDate, int $perPage)
    {
        return $this->contactQuery($request, $startDate, $endDate)
            ->paginate($perPage, ['*'], 'contact_page')
            ->withQueryString();
    }

    private function perPage($value): int
    {
        $allowed = [10, 25, 50, 100, 250, 500];
        $num = (int) $value;
        return in_array($num, $allowed, true) ? $num : 25;
    }

    private function contactQuery(Request $request, ?Carbon $startDate, ?Carbon $endDate)
    {
        $productName = trim((string) $request->query('product_name', ''));

        return Sale::query()
            ->when($startDate, fn ($query) => $query->whereDate('purchase_date', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->whereDate('purchase_date', '<=', $endDate))
            ->when($productName !== '', function ($query) use ($productName) {
                $query->where('product_name', 'like', '%' . $productName . '%');
            })
            ->where(function ($query) {
                $query->whereNotNull('email')
                    ->orWhereNotNull('phone');
            })
            ->select(['product_name', 'email', 'phone'])
            ->orderBy('product_name')
            ->orderBy('email');
    }

    private function exportContactsCsv($rows): StreamedResponse
    {
        $filename = 'customer-contacts-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Serial', 'Product', 'Email', 'Phone']);
            $serial = 1;

            /** @var \App\Models\Sale $row */
            foreach ($rows as $row) {
                fputcsv($handle, [
                    $serial++,
                    $row->product_name ?? 'Unknown',
                    $row->email ?? '',
                    $row->phone ?? '',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function monthlyStatement(int $month, int $year): array
    {
        $today = Carbon::today('Asia/Kathmandu');
        $month = $month > 0 ? $month : $today->month;
        $year = $year > 0 ? $year : $today->year;

        $start = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Kathmandu')->startOfMonth();
        $end = (clone $start)->endOfMonth();
        if ($end->gt($today)) {
            $end = $today;
        }

        $dailySales = Sale::query()
            ->whereBetween('purchase_date', [$start, $end])
            ->selectRaw('DATE(purchase_date) as sale_day')
            ->selectRaw('COALESCE(SUM(sales_amount), 0) as total_sales')
            ->groupBy('sale_day')
            ->orderBy('sale_day')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->sale_day => (float) $row->total_sales]);

        $days = [];
        $runningTotal = 0.0;
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $key = $cursor->toDateString();
            $amount = $dailySales[$key] ?? 0.0;
            $runningTotal += $amount;

            $days[] = [
                'date' => $cursor->toDateString(),
                'label' => $cursor->format('jS'),
                'amount' => $amount,
                'running_total' => $runningTotal,
            ];

            $cursor->addDay();
        }

        return [
            'month_label' => $start->format('F'),
            'year' => $year,
            'days' => $days,
            'total' => $runningTotal,
        ];
    }
}
