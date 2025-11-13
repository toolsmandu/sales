@extends('layouts.app')

@push('styles')
    @include('partials.dashboard-styles')
    <style>
        .stack {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
    </style>
@endpush

@section('content')
    <div class="dashboard-grid">
        @include('partials.dashboard-sidebar')

        <section class="dashboard-content stack">


            <section class="card stack">
                @if ($paymentMethodSummaries->isNotEmpty())
                    <div class="payment-monthly-section">
                        <div class="payment-monthly-section__header">
                            <div>
                                <h3>Filter Total Sales by Month</h3>
                            </div>
                            <form method="GET" action="{{ route('payments.balance') }}" class="payment-monthly-filter">
                                <div class="payment-monthly-filter__inputs">
                                    <label class="payments-filter" for="balance-month">
                                        <select id="balance-month" name="month">
                                            <option value="" @selected($selectedMonth === null)>Month</option>
                                            @foreach ($monthOptions as $monthOption)
                                                <option value="{{ $monthOption['value'] }}" @selected($selectedMonth === $monthOption['value'])>
                                                    {{ $monthOption['label'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </label>
                                    <label class="payments-filter" for="balance-year">
                                        <select id="balance-year" name="year">
                                            <option value="" @selected($selectedYear === null)>Year</option>
                                            @forelse ($yearOptions as $yearOption)
                                                <option value="{{ $yearOption }}" @selected($selectedYear === $yearOption)>
                                                    {{ $yearOption }}
                                                </option>
                                            @empty
                                                <option value="" disabled>No data yet</option>
                                            @endforelse
                                        </select>
                                    </label>
                                </div>
                                <div class="payment-monthly-actions">
                                    <button type="submit" class="filter-apply">Apply</button>
                                </div>
                            </form>
                        </div>

                        @if ($monthlySummaries->isNotEmpty())
                            <div class="table-wrapper table-wrapper--elevated payment-monthly-table">
                                <table>
                                    <thead>
                                        <tr>
                                            <th scope="col">Payment Method</th>
                                            <th scope="col">Total Sales</th>
                                            <th scope="col">Total Withdraw</th>
                                            <th scope="col">Sales Records</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($monthlySummaries as $summary)
                                            <tr>
                                                <td>
                                                    <div class="payment-balances__method">
                                                        <span class="payment-balances__label">{{ $summary['label'] }}</span>
                                                    </div>
                                                </td>
                                                <td class="payment-balances__income">Rs {{ number_format($summary['income_total'], 0) }}</td>
                                                <td class="payment-balances__withdrawal">Rs {{ number_format($summary['withdrawal_total'], 0) }}</td>
                                                <td class="payment-balances__count">{{ number_format($summary['sale_count']) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td>
                                                <div class="payment-balances__method">
                                                    <span class="payment-balances__label">Total</span>
                                                </div>
                                            </td>
                                            <td class="payment-balances__income">Rs {{ number_format($monthlySummaryTotals['income_total'], 0) }}</td>
                                            <td class="payment-balances__withdrawal">Rs {{ number_format($monthlySummaryTotals['withdrawal_total'], 0) }}</td>
                                            <td class="payment-balances__count">{{ number_format($monthlySummaryTotals['sale_count']) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <p class="helper-text">No payments recorded for the selected period.</p>
                        @endif
                    </div>

                @else
                    <p class="helper-text">No sales data available yet. Record sales to see balances by payment method.</p>
                @endif
            </section>
        </section>
    </div>
@endsection
