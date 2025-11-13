@extends('layouts.app')

@push('styles')
    @include('partials.dashboard-styles')
    <style>
        .smart-card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
        }

        .smart-card {
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 0.8rem;
            padding: 1rem 1.2rem;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }

        .smart-card__label {
            font-size: 0.9rem;
            color: rgba(15, 23, 42, 0.6);
            margin: 0;
        }

        .smart-card__value {
            font-size: 1.4rem;
            font-weight: 600;
            margin: 0;
            color: #111827;
        }
    </style>
@endpush

@section('content')
    <div class="dashboard-grid">
        @include('partials.dashboard-sidebar')

        <section class="dashboard-content stack">
            <div class="flex items-center gap-1">
                <a href="{{ route('orders.index') }}" class="ghost-button">← Back to Orders</a>
            </div>

            @php
                $rawPhone = (string) ($phone ?? '');
                $leadingSymbol = ltrim($rawPhone, " ()-\t\n\r\0\x0B");
                $hasLeadingPlus = str_starts_with($leadingSymbol, '+');
                $sanitizedPhone = preg_replace('/[()\s-]+/', '', $rawPhone);
                $sanitizedPhone = ltrim($sanitizedPhone ?? '', '+');
                if ($sanitizedPhone !== '' && $hasLeadingPlus) {
                    $sanitizedPhone = '+' . $sanitizedPhone;
                }
                $displayPhone = $sanitizedPhone !== '' ? $sanitizedPhone : $rawPhone;
            @endphp

            <section class="card stack">
                <h2>Customer Profile</h2>
                <p class="helper-text">Phone: <strong>{{ $displayPhone ?: 'Unknown' }}</strong></p>

                <div class="smart-card-grid">
                    <article class="smart-card">
                        <p class="smart-card__label">Number of Purchases</p>
                        <p class="smart-card__value">{{ $totalPurchases }}</p>
                    </article>
                    <article class="smart-card">
                        <p class="smart-card__label">Starting Date</p>
                        <p class="smart-card__value">
                            @if ($startingDate)
                                {{ \Illuminate\Support\Carbon::parse($startingDate)->format('M d, Y') }}
                            @else
                                —
                            @endif
                        </p>
                    </article>
                    <article class="smart-card">
                        <p class="smart-card__label">Total Spent</p>
                        <p class="smart-card__value">Rs {{ number_format($totalSpent, 0) }}</p>
                    </article>
                </div>
            </section>

            <section class="card stack">
                <h2>Sales History</h2>

                <div class="table-wrapper">
                    <table class="sales-table">
                        <thead>
                            <tr>
                                <th scope="col">Order ID</th>
                                <th scope="col">Purchase Date</th>
                                <th scope="col">Product</th>
                                <th scope="col">Phone</th>
                                <th scope="col">Remarks</th>
                                <th scope="col">Amount</th>
                                <th scope="col">Payment Method</th>
                                <th scope="col">Sold By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($sales as $sale)
                                @php
                                    $saleRecordedAt = $sale->created_at?->timezone('Asia/Kathmandu');
                                    $productDisplay = trim($sale->product_name ?? '');
                                    $rawSalePhone = (string) ($sale->phone ?? '');
                                    $saleLeadingSymbol = ltrim($rawSalePhone, " ()-\t\n\r\0\x0B");
                                    $saleHasPlus = str_starts_with($saleLeadingSymbol, '+');
                                    $normalizedSalePhone = preg_replace('/[()\s-]+/', '', $rawSalePhone);
                                    $normalizedSalePhone = ltrim($normalizedSalePhone ?? '', '+');
                                    if ($normalizedSalePhone !== '' && $saleHasPlus) {
                                        $normalizedSalePhone = '+' . $normalizedSalePhone;
                                    }
                                    $phoneDisplay = $normalizedSalePhone !== '' ? $normalizedSalePhone : '—';
                                @endphp
                                <tr>
                                    <td>{{ $sale->serial_number }}</td>
                                    <td>
                                        @if ($saleRecordedAt)
                                            {{ $saleRecordedAt->format('M d h:i A') }}
                                        @else
                                            <span class="muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $productDisplay !== '' ? $productDisplay : '—' }}</td>
                                    <td>{{ $phoneDisplay }}</td>
                                    <td>{{ $sale->remarks ?? '—' }}</td>
                                    <td>Rs {{ number_format($sale->sales_amount, 0) }}</td>
                                    <td>{{ $sale->paymentMethod?->label ?? '—' }}</td>
                                    <td>{{ $sale->createdBy?->name ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8">
                                        <p class="helper-text">No sales found for this customer.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @php
                    $totalSales = $sales->total();
                    $currentPage = $sales->currentPage();
                    $lastPage = $sales->lastPage();
                    $start = $sales->firstItem() ?? 0;
                    $end = $sales->lastItem() ?? 0;
                @endphp

                <div class="table-controls">
                    <form method="GET" class="table-controls__page-size" action="{{ route('orders.customer', ['phone' => preg_replace('/\D+/', '', $phone)]) }}">
                        <label for="customer-sales-per-page">
                            <select
                                id="customer-sales-per-page"
                                name="per_page"
                                onchange="this.form.submit()">
                                @foreach ([25, 50, 100] as $option)
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
                            @if ($totalSales === 0)
                                No sales to display
                            @else
                                Showing {{ $start }}-{{ $end }} of {{ $totalSales }} (Page {{ $currentPage }} of {{ $lastPage }})
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
        </section>
    </div>
@endsection
