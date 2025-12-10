@extends('layouts.app')

@push('styles')
    @include('partials.dashboard-styles')
    @include('partials.product-combobox-styles')
    <style>
        .reports-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .reports-filters {
            display: flex;
            gap: 0.65rem;
            align-items: flex-end;
            flex-wrap: wrap;
            justify-content: flex-end;
            margin-left: auto;
        }

        .reports-filters label {
            display: inline-flex;
            flex-direction: column;
            gap: 0.2rem;
            font-size: 0.9rem;
            color: #0f172a;
        }

        .reports-filters select {
            min-width: 140px;
        }

        .reports-table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.06);
        }

        .reports-table th,
        .reports-table td {
            border: 1px solid rgba(148, 163, 184, 0.35);
            padding: 0.6rem 0.75rem;
            text-align: center;
            background: #fff;
        }

        .reports-table thead th {
            background: linear-gradient(180deg, rgba(226, 232, 240, 0.9), rgba(241, 245, 249, 0.9));
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.03em;
            text-align: center;
        }

        .reports-table tbody tr:nth-child(even) td {
            background: #f8fafc;
        }

        .reports-table tbody tr:hover td {
            background: #eef2ff;
            border-color: rgba(79, 70, 229, 0.35);
            transition: background 0.2s ease, border-color 0.2s ease;
        }

        .reports-meta {
            font-size: 0.9rem;
            color: rgba(15, 23, 42, 0.7);
        }

        /* Pagination sizing fix */
        nav[aria-label="Pagination Navigation"] svg {
            width: 14px;
            height: 14px;
        }

        nav[aria-label="Pagination Navigation"] a,
        nav[aria-label="Pagination Navigation"] span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2rem;
            min-height: 2rem;
        }

        @media (max-width: 900px) {
            .reports-filters {
                flex-wrap: wrap;
                justify-content: flex-start;
            }
        }

        .reports-subsection {
            display: grid;
            gap: 0.85rem;
        }

        .reports-subsection h2 {
            margin: 0;
        }

        .reports-subsection form {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            align-items: center;
        }

        .reports-subsection table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.06);
        }

        .reports-contact-filters {
            display: flex;
            align-items: flex-end;
            gap: 0.65rem;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .reports-contact-filters > * {
            flex: 0 0 auto;
        }

        .reports-contact-filters .product-combobox {
            min-width: 260px;
        }

        .reports-contact-actions {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: nowrap;
        }

        .reports-subsection th,
        .reports-subsection td {
            border: 1px solid rgba(148, 163, 184, 0.35);
            padding: 0.55rem 0.65rem;
            text-align: center;
            background: #fff;
        }

        .reports-subsection thead th {
            background: linear-gradient(180deg, rgba(226, 232, 240, 0.9), rgba(241, 245, 249, 0.9));
            text-transform: uppercase;
            font-size: 0.82rem;
            letter-spacing: 0.03em;
            text-align: center;
        }

        .reports-subsection tbody tr:nth-child(even) td {
            background: #f8fafc;
        }

        .reports-subsection tbody tr:hover td {
            background: #eef2ff;
            border-color: rgba(79, 70, 229, 0.35);
            transition: background 0.2s ease, border-color 0.2s ease;
        }

        .reports-subsection .product-combobox {
            min-width: 220px;
        }

        .reports-table-toolbar {
            display: flex;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 0.65rem;
            align-items: center;
            padding: 0.25rem 0;
        }

        .reports-table-toolbar label {
            display: inline-flex;
            flex-direction: column;
            gap: 0.2rem;
            font-size: 0.9rem;
            color: #0f172a;
        }

        .reports-table-toolbar select {
            min-width: 140px;
        }
    </style>
@endpush

@push('scripts')
    @include('partials.product-combobox-scripts')
@endpush

@section('content')
    <div class="dashboard-grid">
        @include('partials.dashboard-sidebar')

        <section class="dashboard-content stack">
            <section class="card stack">
                <header class="reports-header">
                    <div>
                        <h2 style="margin: 0;">Sales Statement</h2>
                        <p class="reports-meta" style="margin: 0;">{{ $monthlyStatement['month_label'] }} {{ $monthlyStatement['year'] }}</p>
                    </div>
                    <form method="GET" action="{{ route('reports') }}" style="display: flex; align-items: flex-end; gap: 0.5rem; flex-wrap: wrap; justify-content: flex-end; margin-left: auto; width: 100%; max-width: 520px;">
                        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                            <label style="display: inline-flex; flex-direction: column; gap: 0.2rem; font-size: 0.9rem; color: #0f172a;">
                                <span>Month</span>
                                <select name="month">
                                    @foreach ($months as $month)
                                        <option value="{{ $month['value'] }}" @selected($selectedMonth === $month['value'])>{{ $month['label'] }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label style="display: inline-flex; flex-direction: column; gap: 0.2rem; font-size: 0.9rem; color: #0f172a;">
                                <span>Year</span>
                                <select name="year">
                                    @foreach ($years as $year)
                                        <option value="{{ $year }}" @selected($selectedYear === $year)>{{ $year }}</option>
                                    @endforeach
                                </select>
                            </label>
                        </div>
                        <input type="hidden" name="range" value="custom_month">
                        <button type="submit" style="width: auto;">Apply</button>
                    </form>
                </header>
                <div class="table-wrapper">
                    <table class="reports-table">
                        <thead>
                            <tr>
                                <th style="text-align: left;">Date</th>
                                <th>Total Sales</th>
                                <th>Running Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($monthlyStatement['days'] as $day)
                                <tr>
                                    <td style="text-align: left;">{{ $day['date'] }}</td>
                                    <td>Rs {{ number_format((float) $day['amount'], 0) }}</td>
                                    <td>Rs {{ number_format((float) $day['running_total'], 0) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th style="text-align: left;">Monthly Total</th>
                                <th colspan="2" style="text-align: right;">Rs {{ number_format($monthlyStatement['total'], 0) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>

            <section class="card stack">
                <header class="reports-header">
                    <div>
                        <h2 style="margin: 0;">Top Selling Reports</h2>
                    </div>
                </header>
                <div class="reports-meta">
                    @if ($selectedRange === 'custom_month')
                        Showing results for {{ $months->firstWhere('value', $selectedMonth)['label'] ?? 'selected month' }} {{ $selectedYear ?: '' }}
                    @else
                        Showing {{ $rangeOptions[$selectedRange] ?? 'selected range' }}
                    @endif
                </div>
                <div class="reports-table-toolbar">
                    <form class="reports-filters" method="GET" action="{{ route('reports') }}">
                        <label>
                            <span>Month</span>
                            <select name="month">
                                @foreach ($months as $month)
                                    <option value="{{ $month['value'] }}" @selected($selectedMonth === $month['value'])>
                                        {{ $month['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            <span>Year</span>
                            <select name="year">
                                @foreach ($years as $year)
                                    <option value="{{ $year }}" @selected($selectedYear === $year)>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            <span>Range</span>
                            <select name="range">
                                @foreach ($rangeOptions as $value => $label)
                                    <option value="{{ $value }}" @selected($selectedRange === $value)>{{ $label }}</option>
                                @endforeach
                                @if ($selectedRange === 'custom_month')
                                    <option value="custom_month" selected>Custom Month</option>
                                @endif
                            </select>
                        </label>
                        <label style="display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.9rem; margin: 0;">
                            <span>Rows:</span>
                            <select name="top_per_page" onchange="this.form.submit()">
                                @foreach ([10, 25, 50, 100, 250, 500] as $option)
                                    <option value="{{ $option }}" @selected($topPerPage === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                        </label>
                        <button type="submit" style="margin: 10px 0;">Apply</button>
                    </form>
                </div>
                <div class="table-wrapper">
                    <table class="reports-table">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Sales Count</th>
                                <th>Total Sales</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($topProducts as $product)
                                <tr>
                                    <td>{{ $product->product_name }}</td>
                                    <td>{{ $product->sales_count }}</td>
                                    <td>Rs. {{ number_format((float) $product->total_sales, 0) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">No sales found for the selected filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div style="padding-top: 0.5rem;">
                        {{ $topProducts->onEachSide(1)->links() }}
                    </div>
                </div>
<br>
                <div class="reports-subsection">
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
                        <div>
                            <h2 style="margin: 0;">Customer List</h2>
                        </div>
                        <form method="GET" action="{{ route('reports') }}" class="reports-contact-filters">
                            @foreach (['month', 'year', 'range', 'top_per_page', 'top_page'] as $key)
                                @if (request()->has($key))
                                    <input type="hidden" name="{{ $key }}" value="{{ request($key) }}">
                                @endif
                            @endforeach
                            <div class="product-combobox" data-product-combobox style="min-width: 240px; margin: 0;">
                                <input
                                    type="text"
                                    id="contact-product-input"
                                    class="product-combobox__input"
                                    placeholder="Enter product"
                                    autocomplete="off"
                                    data-selected-name="{{ $selectedProduct ?? '' }}"
                                    value="{{ $selectedProduct ?? '' }}"
                                    name="product_name"
                                    data-allow-free-entry="true"
                                >
                                <div class="product-combobox__dropdown" role="listbox" aria-label="Product options">
                                    @if ($products->isEmpty())
                                        <p class="product-combobox__empty">No products available yet.</p>
                                    @else
                                        <p class="product-combobox__empty" data-empty-message hidden>No matching products found.</p>
                                        @foreach ($products as $product)
                                            @php
                                                $isSelectedOption = $selectedProduct === $product->name;
                                            @endphp
                                            <button
                                                type="button"
                                                class="product-combobox__option {{ $isSelectedOption ? 'is-active' : '' }}"
                                                data-product-option
                                                data-product-name="{{ $product->name }}"
                                                data-product-id="{{ $product->id }}"
                                                role="option"
                                                aria-selected="{{ $isSelectedOption ? 'true' : 'false' }}"
                                            >
                                                {{ $product->name }}
                                            </button>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                            <div class="reports-contact-actions">
                                <button type="submit" style="margin: 15px;">Filter</button>
                                <a
                                    class="secondary outline"
                                    href="{{ route('reports', array_merge(request()->all(), ['export' => 'contacts'])) }}"
                                    style="display: inline-flex; align-items: center; gap: 0.4rem;"
                                >
                                    Export
                                </a>
                                <label style="display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.9rem; margin: 0;">
                                    <span>Rows:</span>
                                    <select name="contact_per_page" onchange="this.form.submit()">
                                        @foreach ([10, 25, 50, 100, 250, 500] as $option)
                                            <option value="{{ $option }}" @selected($contactPerPage === $option)>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            </div>
                        </form>
                    </div>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Serial</th>
                                    <th>Product</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($customerRows as $row)
                                    <tr>
                                        <td>{{ ($customerRows->firstItem() ?? 0) + $loop->index }}</td>
                                        <td>{{ $row->product_name ?? 'Unknown' }}</td>
                                        <td>{{ $row->email ?? '—' }}</td>
                                        <td>{{ $row->phone ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3">No customers found for the selected filters.</td>
                                    </tr>
                                    @endforelse
                            </tbody>
                        </table>
                        <div style="padding-top: 0.5rem;">
                            {{ $customerRows->onEachSide(1)->links() }}
                        </div>
                    </div>
                </div>
            </section>
        </section>
    </div>
@endsection
