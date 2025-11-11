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
                @if ($methods->isNotEmpty())
                    <div class="payment-ledger-section">
                        <div class="payment-ledger-section__header">
                            <div>
                                <h3>Payment Statement</h3>
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
                                    <button type="submit" class="ghost-button-statement">Apply</button>
                                </div>
                            </form>
                        </div>

                        @if ($ledgerPaginator && $ledgerHasData)
                            <div class="table-wrapper table-wrapper--elevated payment-ledger-table">
                                <table>
                                    <thead>
                                        <tr>
                                            <th scope="col">Order ID</th>
                                            <th scope="col">Date</th>
                                            <th scope="col">Phone</th>
                                            <th scope="col">Email</th>
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
                                                $phoneValue = $entry['phone'] ?? '—';
                                                $emailValue = $entry['email'] ?? '—';
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
                                                <td>
                                                    <div class="cell-with-action">
                                                        <span>{{ $emailValue }}</span>
                                                        @if (!$isExpense && $emailValue !== '—')
                                                            <button
                                                                type="button"
                                                                class="cell-action-button"
                                                                data-copy="{{ $emailValue }}"
                                                                aria-label="Copy email {{ $entry['serial'] ?? '' }}"
                                                            >
                                                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                                                    <path d="M8 7V5a2 2 0 012-2h9a2 2 0 012 2v11a2 2 0 01-2 2h-2" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                    <rect x="4" y="7" width="12" height="12" rx="2" ry="2" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                                </svg>
                                                            </button>
                                                        @endif
                                                    </div>
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
        </section>
    </div>
@endsection
