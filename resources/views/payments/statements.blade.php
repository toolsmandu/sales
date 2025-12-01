@extends('layouts.app')

@push('styles')
    @include('partials.dashboard-styles')
    <style>
        .stack {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .payment-balances-table tfoot td {
            background: #f1f5f9;
            font-weight: 600;
            border-top: 1px solid rgba(15, 23, 42, 0.12);
        }

        .payment-balances-table table th,
        .payment-balances-table table td,
        .payment-ledger-table table th,
        .payment-ledger-table table td {
            white-space: nowrap;
            word-break: normal;
        }

        .payment-balances-table table th:first-child,
        .payment-balances-table table td:first-child {
            min-width: 10rem;
        }

        .payment-ledger-table table th:nth-child(1),
        .payment-ledger-table table td:nth-child(1) {
            min-width: 6rem;
        }

        .payment-ledger-table table th:nth-child(2),
        .payment-ledger-table table td:nth-child(2) {
            min-width: 11rem;
        }

        .payment-ledger-table table th:nth-child(3),
        .payment-ledger-table table td:nth-child(3) {
            min-width: 8rem;
        }

        .payment-ledger-table table th:nth-child(4),
        .payment-ledger-table table td:nth-child(4) {
            min-width: 10rem;
        }

        .payment-ledger-table table th:nth-child(5),
        .payment-ledger-table table td:nth-child(5) {
            min-width: 12rem;
        }

        .payment-ledger-table table th:nth-child(6),
        .payment-ledger-table table td:nth-child(6),
        .payment-ledger-table table th:nth-child(7),
        .payment-ledger-table table td:nth-child(7),
        .payment-ledger-table table th:nth-child(8),
        .payment-ledger-table table td:nth-child(8) {
            min-width: 9rem;
        }

        .manual-deposit {
            display: flex;
            align-items: flex-end;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .manual-deposit label {
            display: grid;
            gap: 0.35rem;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .manual-deposit input,
        .manual-deposit select {
            min-width: 10rem;
        }

        .manual-deposit button {
            height: fit-content;
            padding: 0.55rem 1rem;
            margin: 27px
        }
    </style>
@endpush

@section('content')
    <div class="dashboard-grid">
        @include('partials.dashboard-sidebar')

        <section class="dashboard-content stack">
            <section class="card stack">
                @if ($paymentMethodSummaries->isNotEmpty())
                    @php
                        $monthlyByLabel = $monthlySummaries->keyBy(fn ($summary) => mb_strtolower($summary['label'] ?? ''));
                        $monthLabel = $selectedMonth ? \Illuminate\Support\Carbon::create()->month($selectedMonth)->format('F') : 'All Months';
                    @endphp
                    <div class="payment-monthly-section__header">
                        <div>
                            <h3>Summary by Payment Method</h3>
                        </div>
                        <form method="GET" action="{{ route('payments.statements') }}" class="payment-monthly-filter">
                            <input type="hidden" name="method" value="{{ optional($selectedMethod)->slug }}">
                            <input type="hidden" name="per_page" value="{{ $perPage }}">
                            <div class="payment-monthly-filter__inputs">
                                <label class="payments-filter" for="statement-balance-month">
                                    <select id="statement-balance-month" name="month">
                                                                                <option value="" @selected($selectedMonth === null)>Month</option>
                                        @foreach ($monthOptions as $monthOption)
                                            <option value="{{ $monthOption['value'] }}" @selected($selectedMonth === $monthOption['value'])>
                                                {{ $monthOption['label'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </label>
                                <label class="payments-filter" for="statement-balance-year">
                                    <select id="statement-balance-year" name="year">
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
                                <button type="submit" class="filter-apply">Filter</button>
                            </div>
                        </form>
                    </div>

                    <div class="table-wrapper table-wrapper--elevated payment-balances-table">
                        <table>
                            <thead>
                                <tr>
                                    <th scope="col">Payment Method</th>
                                    <th scope="col">No. of Sales ({{ $monthLabel }})</th>
                                    <th scope="col">Total Sales ({{ $monthLabel }})</th>
                                    <th scope="col">Total Withdraw ({{ $monthLabel }})</th>
                                    <th scope="col">Net Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($paymentMethodSummaries as $summary)
                                    @php
                                        $label = $summary['label'];
                                        $monthlySummary = $monthlyByLabel->get(mb_strtolower($label));
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="payment-balances__method">
                                                <span class="payment-balances__label">{{ $label }}</span>
                                            </div>
                                        </td>
                                        <td class="payment-balances__count">
                                            @if ($monthlySummary)
                                                {{ number_format($monthlySummary['sale_count']) }}
                                            @else
                                                <span class="muted">—</span>
                                            @endif
                                        </td>
                                        <td class="payment-balances__income">
                                            @if ($monthlySummary)
                                                Rs {{ number_format($monthlySummary['income_total'], 0) }}
                                            @else
                                                <span class="muted">—</span>
                                            @endif
                                        </td>
                                        <td class="payment-balances__withdrawal">
                                            @if ($monthlySummary)
                                                Rs {{ number_format($monthlySummary['withdrawal_total'], 0) }}
                                            @else
                                                <span class="muted">—</span>
                                            @endif
                                        </td>
                                        <td class="payment-balances__amount">Rs {{ number_format($summary['available_balance'], 0) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td>
                                        <div class="payment-balances__method">
                                            <span class="payment-balances__label">Totals</span>
                                        </div>
                                    </td>
                                    <td class="payment-balances__count">{{ number_format($monthlySummaryTotals['sale_count']) }}</td>
                                    <td class="payment-balances__income">Rs {{ number_format($monthlySummaryTotals['income_total'], 0) }}</td>
                                    <td class="payment-balances__withdrawal">Rs {{ number_format($monthlySummaryTotals['withdrawal_total'], 0) }}</td>
                                    <td class="payment-balances__amount">Rs {{ number_format($totalAvailableBalance, 0) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    @unless ($monthlySummaries->isNotEmpty())
                        <p class="helper-text">No payments recorded for the selected period.</p>
                    @endunless
                @else
                    <p class="helper-text">No sales data available yet. Record sales to see balances by payment method.</p>
                @endif
            </section>

            <section class="card stack">
                @if ($methods->isNotEmpty())
                    @php
                        $todayDate = now()->format('Y-m-d');
                    @endphp
                    <div class="payment-ledger-section">
                        <div class="payment-ledger-section__header">
                            <div>
                                <h3>All Payment Statement</h3>
                            </div>
                            <form method="GET" action="{{ route('payments.statements') }}" class="payment-ledger-filter">
                                <div class="payment-ledger-filter__inputs">
                                    <label class="payments-filter" for="statements-method">
                                        <span>Payment Method</span>
                                        <select id="statements-method" name="method">
                                            @foreach ($methods as $method)
                                                <option value="{{ $method->slug }}" @selected(optional($selectedMethod)->slug === $method->slug)>
                                                    {{ $method->label }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </label>
                                </div>
                                <input type="hidden" name="per_page" value="{{ $perPage }}">
                                <div class="payment-ledger-actions">
                                    <button type="submit" class="ghost-button-statement">Filter</button>
                                </div>
                            </form>
                        </div>

                        <form
                            method="POST"
                            action="{{ route('payments.statements.deposit') }}"
                            class="manual-deposit"
                        >
                            @csrf
                            <label>
                                <span>Amount</span>
                                <input
                                    type="number"
                                    name="amount"
                                    inputmode="decimal"
                                    step="0.01"
                                    min="0.01"
                                    placeholder="0.00"
                                    required
                                >
                            </label>
                            <label>
                                <span>Payment Method</span>
                                <select name="payment_method" required>
                                    @foreach ($methods as $method)
                                        <option value="{{ $method->slug }}" @selected(optional($selectedMethod)->slug === $method->slug)>
                                            {{ $method->label }}
                                        </option>
                                    @endforeach
                                </select>
                            </label>
                            <label>
                                <span>Date</span>
                                <input
                                    type="date"
                                    name="date"
                                    value="{{ $todayDate }}"
                                    required
                                >
                            </label>
                            <label>
                                <span>Remarks</span>
                                <input
                                    type="text"
                                    name="remarks"
                                    placeholder="Payment Remarks"
                                    maxlength="255"
                                >
                            </label>
                            <div>
                                <button type="submit" class="filter-apply">
                                    Add Fund
                                </button>
                            </div>
                        </form>

                        @if ($ledgerPaginator && $ledgerHasData)
                            <div class="table-wrapper table-wrapper--elevated payment-ledger-table">
                                <table>
                                    <thead>
                                        <tr>
                                            <th scope="col">Order ID</th>
                                            <th scope="col">Date</th>
                                            <th scope="col">Phone</th>
                                            <th scope="col">Product</th>
                                            <th scope="col">Remarks</th>
                                            <th scope="col">Sales</th>
                                            <th scope="col">Withdraw</th>
                                            <th scope="col">Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $displayTimezone = $displayTimezone ?? 'Asia/Kathmandu';
                                        @endphp
                                        @foreach ($ledgerPaginator as $index => $entry)
                                            @php
                                                $position = ($ledgerPaginator->firstItem() ?? 0) + $index;
                                                $timestamp = $entry['timestamp'];
                                                $formattedDate = $timestamp
                                                    ? $timestamp->copy()->setTimezone($displayTimezone)->format('Y-M-d h:i A')
                                                    : '—';
                                                $incomeAmount = $entry['income'] > 0 ? 'Rs ' . number_format($entry['income'], 0) : '—';
                                                $expenseAmount = $entry['expense'] > 0 ? 'Rs ' . number_format($entry['expense'], 0) : '—';
                                                $balanceAmount = 'Rs ' . number_format($entry['balance'], 0);
                                                $rawPhone = (string) ($entry['phone'] ?? '');
                                                $leadingSymbol = ltrim($rawPhone, " ()-\t\n\r\0\x0B");
                                                $hasLeadingPlus = str_starts_with($leadingSymbol, '+');
                                                $sanitizedPhone = preg_replace('/[()\s-]+/', '', $rawPhone);
                                                $sanitizedPhone = ltrim($sanitizedPhone ?? '', '+');
                                                if ($sanitizedPhone !== '' && $hasLeadingPlus) {
                                                    $sanitizedPhone = '+' . $sanitizedPhone;
                                                }
                                                $phoneValue = $sanitizedPhone !== '' ? $sanitizedPhone : '—';
                                                $productValue = trim($entry['product'] ?? '—');
                                                $remarksValue = trim($entry['remarks'] ?? '—');
                                                $isExpense = ($entry['type'] ?? null) === 'expense';
                                            @endphp
                                            <tr @class(['ledger-expense' => $isExpense])>
                                                <td>{{ $entry['serial'] ?? $position }}</td>
                                                <td>{{ $formattedDate }}</td>
                                                <td>
                                                    <div class="cell-with-action">
                                                        <span>{{ $phoneValue }}</span>
                                                        @if (!$isExpense && $phoneValue !== '—')
                                                            <button
                                                                type="button"
                                                                class="cell-action-button"
                                                                data-copy="{{ $phoneValue }}"
                                                                aria-label="Copy phone {{ $entry['serial'] ?? '' }}"
                                                            >
                                                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                                                    <path d="M8 7V5a2 2 0 012-2h9a2 2 0 012 2v11a2 2 0 01-2 2h-2" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                    <rect x="4" y="7" width="12" height="12" rx="2" ry="2" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                </svg>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>{{ $productValue !== '' ? $productValue : '—' }}</td>
                                                <td>
                                                    @if ($remarksValue === '' || $remarksValue === '—')
                                                        <span class="muted">—</span>
                                                    @else
                                                        {{ \Illuminate\Support\Str::limit($remarksValue, 80) }}
                                                    @endif
                                                </td>
                                                <td>{{ $incomeAmount }}</td>
                                                <td>{{ $expenseAmount }}</td>
                                                <td>{{ $balanceAmount }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="table-controls table-controls--compact payment-ledger-controls">
                                <form method="GET" class="table-controls__page-size statements-page-size">
                                    <input type="hidden" name="method" value="{{ optional($selectedMethod)->slug }}">
                                    <input type="hidden" name="month" value="{{ $selectedMonth }}">
                                    <input type="hidden" name="year" value="{{ $selectedYear }}">
                                    <label for="statements-page-size">
                                        <span>Show</span>
                                        <select
                                            id="statements-page-size"
                                            name="per_page"
                                            onchange="this.form.submit()"
                                        >
                                            @foreach ($perPageOptions as $option)
                                                <option value="{{ $option }}" @selected($perPage === $option)>
                                                    {{ $option }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </label>
                                </form>
                                <div class="table-controls__pagination">
                                    <a
                                        class="ghost-button {{ $ledgerPaginator->onFirstPage() ? 'is-disabled' : '' }}"
                                        href="{{ $ledgerPaginator->previousPageUrl() ?? '#' }}"
                                        aria-disabled="{{ $ledgerPaginator->onFirstPage() ? 'true' : 'false' }}"
                                    >
                                        Previous
                                    </a>
                                    <span class="helper-text">
                                        Showing {{ $ledgerPaginator->firstItem() }}-{{ $ledgerPaginator->lastItem() }} of {{ $ledgerPaginator->total() }} (Page {{ $ledgerPaginator->currentPage() }} of {{ $ledgerPaginator->lastPage() }})
                                    </span>
                                    <a
                                        class="ghost-button {{ $ledgerPaginator->hasMorePages() ? '' : 'is-disabled' }}"
                                        href="{{ $ledgerPaginator->hasMorePages() ? $ledgerPaginator->nextPageUrl() : '#' }}"
                                        aria-disabled="{{ $ledgerPaginator->hasMorePages() ? 'false' : 'true' }}"
                                    >
                                        Next
                                    </a>
                                </div>
                            </div>
                        @else
                            <p class="helper-text">No transactions recorded for the selected payment method yet.</p>
                        @endif
                    </div>
                @else
                    <p class="helper-text">No payment methods available yet. Add a payment method to view statements.</p>
                @endif
            </section>

            <section class="card stack">
                <div class="payment-ledger-section__header">
                    <div>
                        <h3>Daily Total Sales Statement</h3>
                    </div>
                    <form method="GET" action="{{ route('payments.statements') }}" class="payment-monthly-filter">
                        <input type="hidden" name="method" value="{{ optional($selectedMethod)->slug }}">
                        <input type="hidden" name="per_page" value="{{ $perPage }}">
                        <div class="payment-monthly-filter__inputs">
                            <label class="payments-filter" for="daily-sales-month">
                                <select id="daily-sales-month" name="month">
                                    <option value="" @selected($selectedMonth === null)>Month</option>
                                    @foreach ($monthOptions as $monthOption)
                                        <option value="{{ $monthOption['value'] }}" @selected($selectedMonth === $monthOption['value'])>
                                            {{ $monthOption['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="payments-filter" for="daily-sales-year">
                                <select id="daily-sales-year" name="year">
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

                @if ($dailySalesTotals->isNotEmpty())
                    <div class="table-wrapper table-wrapper--elevated payment-ledger-table">
                        <table>
                            <thead>
                                <tr>
                                    <th scope="col">Date</th>
                                    <th scope="col">Total Sales</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($dailySalesTotals as $row)
                                    @php
                                        $day = $row['day'];
                                        $label = $day ? $day->format('M d, Y') : '—';
                                    @endphp
                                    <tr>
                                        <td>{{ $label }}</td>
                                        <td>Rs {{ number_format($row['income_total'], 0) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td><strong>Total</strong></td>
                                    <td><strong>Rs {{ number_format($dailySalesTotalsSum, 0) }}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <p class="helper-text">No sales recorded for the selected period.</p>
                @endif
            </section>
        </section>
    </div>
@endsection
