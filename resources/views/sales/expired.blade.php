@extends('layouts.app')

@push('styles')
    @include('partials.dashboard-styles')
    <style>
        .remaining-chip {
            font-weight: 600;
        }

        .remaining-chip--positive {
            color: #15803d;
        }

        .remaining-chip--negative {
            color: #dc2626;
        }

        .remaining-chip--neutral {
            color: #2563eb;
        }

        .ghost-button--compact {
            padding: 0.4rem 0.9rem;
            font-size: 0.85rem;
        }

        .ghost-button.is-active {
            background-color: #2563eb;
            color: #fff;
            border-color: #1d4ed8;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.2);
            gap: 0.35rem;
        }

        .expired-filter-actions {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
            flex-wrap: wrap;
        }

        .expired-filter-form {
            display: inline-flex;
            align-items: center;
        }

        .expired-header {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .expired-header h2 {
            margin: 0;
        }

        .filter-button-check {
            display: inline-block;
            width: 1rem;
            text-align: center;
            font-weight: 700;
        }

        .whatsapp-link {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            color: #047857;
            font-weight: 600;
            text-decoration: none;
        }

        .whatsapp-link:hover {
            color: #065f46;
            text-decoration: underline;
        }

        .whatsapp-icon {
            width: 1rem;
            height: 1rem;
            display: inline-flex;
        }

        .whatsapp-icon svg {
            width: 100%;
            height: 100%;
            fill: currentColor;
        }

        .stock-status {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-weight: 600;
        }

        .stock-status--in {
            color: #15803d;
        }

        .stock-status--out {
            color: #b91c1c;
        }

        .stock-status__icon {
            font-size: 1rem;
            line-height: 1;
        }

        .expired-table-wrapper {
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 1rem;
            box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
            background: #fff;
            overflow-x: auto;
            overflow-y: hidden;
            -webkit-overflow-scrolling: touch;
            padding: 1rem;
            margin-top: 1rem;
        }

        .expired-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .expired-table thead th {
            background: #f1f5f9;
            color: #0f172a;
            font-weight: 600;
            padding: 0.9rem 1rem;
            border-bottom: 1px solid rgba(15, 23, 42, 0.08);
        }

        .expired-table tbody td {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid rgba(15, 23, 42, 0.07);
            vertical-align: middle;
        }

        .expired-table tbody tr:last-child td {
            border-bottom: none;
        }

        .expired-table tbody tr:hover {
            background: rgba(37, 99, 235, 0.05);
        }
    </style>
@endpush

@section('content')
    <div class="dashboard-grid">
        @include('partials.dashboard-sidebar')

        <section class="dashboard-content stack">
            @php
                $currentFilter = $remainingFilter ?? 'today';
            @endphp
            <div class="expired-header">
                <h2>Expired Orders</h2>
                <div class="expired-filter-actions">
                    <form method="GET" class="expired-filter-form">
                        @foreach (request()->except(['remaining_filter', 'page']) as $param => $value)
                            <input type="hidden" name="{{ $param }}" value="{{ $value }}">
                        @endforeach
                        <input type="hidden" name="remaining_filter" value="today">
                        <button
                            type="submit"
                            class="ghost-button ghost-button--compact {{ $currentFilter === 'today' ? 'is-active' : '' }}">
                            <span class="filter-button-check" aria-hidden="true">
                                {{ $currentFilter === 'today' ? '✓' : '' }}
                            </span>
                            <span>Expired Today</span>
                        </button>
                    </form>
                    <form method="GET" class="expired-filter-form">
                        @foreach (request()->except(['remaining_filter', 'page']) as $param => $value)
                            <input type="hidden" name="{{ $param }}" value="{{ $value }}">
                        @endforeach
                        <input type="hidden" name="remaining_filter" value="all">
                        <button
                            type="submit"
                            class="ghost-button ghost-button--compact {{ $currentFilter === 'all' ? 'is-active' : '' }}">
                            <span class="filter-button-check" aria-hidden="true">
                                {{ $currentFilter === 'all' ? '✓' : '' }}
                            </span>
                            <span>All Expired</span>
                        </button>
                    </form>
                </div>
            </div>

            <div class="table-wrapper expired-table-wrapper">
                <table class="sales-table expired-table">
                    <thead>
                        <tr>
                            <th scope="col">Order ID</th>
                            <th scope="col">Product</th>
                            <th scope="col">Email</th>
                            <th scope="col">Phone</th>
                            <th scope="col">Stock Status</th>
                            <th scope="col">Purchase</th>
                            <th scope="col">Expiry</th>
                            <th scope="col">Remaining</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sales as $sale)
                            @php
                                $productDisplay = trim($sale->product_name ?? '');
                                $rawPhone = (string) ($sale->phone ?? '');
                                $leadingSymbol = ltrim($rawPhone, " ()-\t\n\r\0\x0B");
                                $hasLeadingPlus = str_starts_with($leadingSymbol, '+');
                                $normalizedPhone = preg_replace('/[()\s-]+/', '', $rawPhone);
                                $normalizedPhone = ltrim($normalizedPhone ?? '', '+');
                                if ($normalizedPhone !== '' && $hasLeadingPlus) {
                                    $normalizedPhone = '+' . $normalizedPhone;
                                }
                                $phoneDisplay = $normalizedPhone !== '' ? $normalizedPhone : '—';
                                $plainPhone = preg_replace('/\D+/', '', $rawPhone);
                                $encodedProduct = rawurlencode($productDisplay !== '' ? $productDisplay : 'your product');
                                $stockStatus = $sale->product_is_in_stock;
                                $purchaseDate = $sale->purchase_date ? $sale->purchase_date->copy() : null;
                                $expiryDate = $sale->calculated_expiry_date ?? null;
                            @endphp
                            <tr>
                                <td>{{ $sale->serial_number }}</td>
                          
                                <td>{{ $productDisplay !== '' ? $productDisplay : '—' }}</td>
                                @php
                                    $emailDisplay = trim((string) $sale->email);
                                @endphp
                                <td>{{ $emailDisplay !== '' ? $emailDisplay : '—' }}</td>
                                <td>
                                    @if ($plainPhone !== '')
                                        @php
                                            $whatsAppLink = sprintf(
                                                'https://api.whatsapp.com/send?phone=%s&text=Hello%%2C%%20Your%%20subscription%%20for%%20%s%%20purchased%%20from%%20toolsmandu.com%%20is%%20Expired.%%20Would%%20you%%20like%%20to%%20renew%%20it%%20%%3F&_fb_noscript=1',
                                                $plainPhone,
                                                $encodedProduct
                                            );
                                        @endphp
                                        <a href="{{ $whatsAppLink }}" target="_blank" rel="noopener" class="whatsapp-link whatsapp-link--clean">
                                            <span class="whatsapp-icon" aria-hidden="true">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                                                    <path d="M380.9 97.1c-41.9-42-97.7-65.1-157-65.1-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480 117.7 449.1c32.4 17.7 68.9 27 106.1 27l.1 0c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3 18.6-68.1-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1s56.2 81.2 56.1 130.5c0 101.8-84.9 184.6-186.6 184.6zM325.1 300.5c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8s-14.3 18-17.6 21.8c-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7 .9-6.9-.5-9.7s-12.5-30.1-17.1-41.2c-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2s-9.7 1.4-14.8 6.9c-5.1 5.6-19.4 19-19.4 46.3s19.9 53.7 22.6 57.4c2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4s4.6-24.1 3.2-26.4c-1.3-2.5-5-3.9-10.5-6.6z"/>
                                                </svg>
                                            </span>
                                            {{ $phoneDisplay }}
                                        </a>
                                    @else
                                        <span class="muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($stockStatus === true)
                                        <span class="stock-status stock-status--in">
                                            <span class="stock-status__icon">✓</span>
                                            In Stock
                                        </span>
                                    @elseif ($stockStatus === false)
                                        <span class="stock-status stock-status--out">
                                            <span class="stock-status__icon">✗</span>
                                            Out of Stock
                                        </span>
                                    @else
                                        <span class="muted">Unknown</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($purchaseDate)
                                        {{ $purchaseDate->format('M d, Y') }}
                                    @else
                                        <span class="muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($expiryDate)
                                        {{ $expiryDate->format('M d, Y') }}
                                    @else
                                        <span class="muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $remainingDays = $sale->calculated_remaining_days;
                                        $remainingClass = null;
                                        if ($remainingDays !== null) {
                                            if ($remainingDays > 0) {
                                                $remainingClass = 'remaining-chip--positive';
                                            } elseif ($remainingDays < 0) {
                                                $remainingClass = 'remaining-chip--negative';
                                            } else {
                                                $remainingClass = 'remaining-chip--neutral';
                                            }
                                        }
                                    @endphp
                                    @if ($remainingDays !== null)
                                        <span class="remaining-chip {{ $remainingClass }}">
                                            {{ $remainingDays }}
                                        </span>
                                    @else
                                        <span class="muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <p class="helper-text">No expired orders available.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @php
                $total = $sales->total();
                $currentPage = $sales->currentPage();
                $lastPage = $sales->lastPage();
                $start = $sales->firstItem() ?? 0;
                $end = $sales->lastItem() ?? 0;
            @endphp

            <div class="table-controls">
                <form method="GET" class="table-controls__page-size">
                    <label for="expired-orders-per-page">
                        <select
                            id="expired-orders-per-page"
                            name="per_page"
                            onchange="this.form.submit()">
                            @foreach ([25, 50, 100, 200] as $option)
                                <option value="{{ $option }}" @selected($perPage === $option)>
                                    {{ $option }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                </form>
                <div class="table-controls__pagination">
                    <a
                        class="ghost-button"
                        href="{{ $sales->previousPageUrl() ?? '#' }}"
                        @class(['is-disabled' => !$sales->previousPageUrl()])
                        aria-disabled="{{ $sales->previousPageUrl() ? 'false' : 'true' }}">
                        Previous
                    </a>
                    <span class="helper-text">
                        @if ($total === 0)
                            No expired orders to display
                        @else
                            Showing {{ $start }}-{{ $end }} of {{ $total }} (Page {{ $currentPage }} of {{ $lastPage }})
                        @endif
                    </span>
                    <a
                        class="ghost-button"
                        href="{{ $sales->nextPageUrl() ?? '#' }}"
                        @class(['is-disabled' => !$sales->nextPageUrl()])
                        aria-disabled="{{ $sales->nextPageUrl() ? 'false' : 'true' }}">
                        Next
                    </a>
                </div>
            </div>
        </section>
    </div>
@endsection
