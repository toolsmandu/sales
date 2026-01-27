@extends('layouts.app')

@php
    $isEmployee = auth()->user()?->role === 'employee';
    $canDeleteRecords = ! $isEmployee;
    $productLinks = $products->mapWithKeys(function ($product) {
        $decoded = [];
        if (!empty($product->linked_variation_ids)) {
            $decoded = is_string($product->linked_variation_ids)
                ? json_decode($product->linked_variation_ids, true) ?: []
                : (is_array($product->linked_variation_ids) ? $product->linked_variation_ids : []);
        }
        return [
            $product->id => [
                'linked_product_id' => $product->linked_product_id ?? null,
                'linked_variation_ids' => $decoded,
            ],
        ];
    });
@endphp

@push('styles')
    @include('partials.dashboard-styles')
    @include('partials.product-combobox-styles')
    <style>
        .records-layout {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .records-toolbar {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .records-toolbar select {
            min-width: 220px;
        }

        .records-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .records-table-wrapper {
            overflow: auto;
            border: 1px solid rgba(15, 23, 42, 0.1);
            border-radius: 0.75rem;
            width: 100%;
            max-width: 100%;
        }

        table.records-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 960px;
            table-layout: auto;
            background: linear-gradient(180deg, #fff, #f8fafc 18%, #fff 100%);
        }



        table.records-table th,
        table.records-table td {
            border: 1px solid rgba(148, 163, 184, 0.35);
            padding: 0.55rem 0.65rem;
            background: #fff;
            text-align: center;
        }

        table.records-table thead th {
            background: linear-gradient(180deg, rgba(226, 232, 240, 0.9), rgba(241, 245, 249, 0.9));
            font-weight: 800;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        table.records-table tbody tr:nth-child(even) td {
            background: #f8fafc;
        }

        table.records-table tbody tr:hover td {
            background: #eef2ff;
            border-color: rgba(79, 70, 229, 0.35);
            transition: background 0.2s ease, border-color 0.2s ease;
        }

        .records-row--expired td,
        .records-row--expired:hover td {
            color: #b91c1c;
            background: #ffe2e5;
            border-color: rgba(248, 113, 113, 0.45);
        }

        .records-row--expiring td,
        .records-row--expiring:hover td {
            color: #4c1d95;
            background: #767082ff;
            border-color: rgba(139, 92, 246, 0.45);
        }

        .records-row--highlight td,
        .records-row--highlight:hover td {
            background: #6b5f50ff;
            border-color: rgba(38, 18, 169, 0.45);
            box-shadow: inset 0 0 0 1px rgba(234, 88, 12, 0.35);
            animation: records-highlight 1.6s ease-in-out 20;
        }

        @keyframes records-highlight {
            0% { box-shadow: inset 0 0 0 1px rgba(234, 88, 12, 0.35); }
            50% { box-shadow: inset 0 0 0 3px rgba(234, 88, 12, 0.2); }
            100% { box-shadow: inset 0 0 0 1px rgba(234, 88, 12, 0.35); }
        }

        input:not([type="checkbox"], [type="radio"]),
        select,
        textarea {
            margin-bottom: 0;
        }

        .records-date-wrapper {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            justify-content: center;
            position: relative;
        }

        .records-date-input {
            flex: 1 1 auto;
            text-align: center;
        }

        .records-date-button {
            width: 3rem;
            height: 3rem;
            border: 1px dotted rgba(79, 70, 229, 0.35);
            border-radius: 0.45rem;
            background: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: rgba(79, 70, 229, 0.8);
            transition: border-color 0.15s ease, box-shadow 0.15s ease, background 0.15s ease, color 0.15s ease;
        }

        .records-date-button:hover,
        .records-date-button:focus-visible {
            border-color: rgba(79, 70, 229, 0.65);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.18);
            outline: none;
            background: rgba(79, 70, 229, 0.04);
        }

        .records-date-picker {
            position: absolute;
            right: 0;
            top: 0;
            width: 3rem;
            height: 3rem;
            opacity: 0;
            pointer-events: none;
        }

        .records-table input[type="date"],
        .records-table input[type="number"] {
            width: 100%;
            border: 1px dotted rgba(79, 70, 229, 0.35);
            background: rgba(79, 70, 229, 0.04);
            padding: 0.3rem 0.4rem;
            border-radius: 0.45rem;
            line-height: 1.3;
            font-size: 1rem;
            height: 2.6rem;
            display: flex;
            align-items: center;
            box-sizing: border-box;
            transition: border-color 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
        }

        .records-table input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(0.35);
            opacity: 1;
            cursor: pointer;
            margin-right: 0.15rem;
        }

        .records-table input[type="date"]:focus-visible,
        .records-table input[type="number"]:focus-visible {
            border-color: rgba(79, 70, 229, 0.65);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.18);
            background: #fff;
            outline: none;
        }

        .records-table input[type="text"],
        .records-table textarea {
            width: 100%;
            border: none;
            background: transparent;
            display: flex;
            align-items: center;
            height: 2.6rem;
            box-sizing: border-box;
            text-align: center;
        }

        .records-table textarea {
            resize: vertical;
            min-height: 2.6rem;
        }

        .records-table [contenteditable="true"] {
            min-width: 140px;
            padding: 0.3rem 0.4rem;
            outline: none;
            border: 1px dotted rgba(79, 70, 229, 0.35);
            border-radius: 0.45rem;
            background: rgba(79, 70, 229, 0.04);
            line-height: 1.3;
            font-size: 1rem;
            min-height: 2.6rem;
            display: flex;
            align-items: center;
            box-sizing: border-box;
            transition: border-color 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
            text-align: center;
            justify-content: center;
        }

        .records-table [contenteditable="true"]:focus-visible {
            border-color: rgba(79, 70, 229, 0.65);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.18);
            background: #fff;
        }

        .records-empty {
            padding: 1rem;
            color: rgba(15, 23, 42, 0.8);
        }

        .pill {
            padding: 0.15rem 0.5rem;
            border-radius: 999px;
            background: rgba(59, 130, 246, 0.12);
            color: #1d4ed8;
            font-weight: 700;
            font-size: 0.9rem;
        }

        .col-resizer {
            position: absolute;
            right: -4px;
            top: 0;
            width: 8px;
            cursor: col-resize;
            user-select: none;
            height: 100%;
            z-index: 3;
        }

        .column-controls {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            align-items: center;
            padding: 0.5rem;
            border: 1px dashed rgba(148, 163, 184, 0.6);
            border-radius: 0.65rem;
            background: #f8fafc;
        }

        .column-control {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            border: 1px solid rgba(148, 163, 184, 0.45);
            border-radius: 999px;
            padding: 0.25rem 0.6rem;
            background: #fff;
        }

        .column-control button {
            border: none;
            background: transparent;
            cursor: pointer;
            padding: 0.1rem 0.25rem;
            font-weight: 700;
            color: #0f172a;
        }

        .add-product-inline {
            display: inline-flex;
            align-items: flex-end;
            gap: 0.5rem;
        }

        .add-product-inline input {
            min-width: 220px;
        }

        .records-header-content {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
        }

        .records-inline-filters {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .records-actions {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .records-actions__main {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .sort-button {
            border: 1px solid rgba(15, 23, 42, 0.35);
            background: #fff;
            border-radius: 0.4rem;
            width: 1.85rem;
            height: 1.85rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #0f172a;
            transition: border-color 0.15s ease, box-shadow 0.15s ease, background 0.15s ease, color 0.15s ease, transform 0.15s ease;
        }

        .sort-button:hover,
        .sort-button:focus-visible {
            border-color: rgba(15, 23, 42, 0.65);
            box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.15);
            outline: none;
            background: rgba(15, 23, 42, 0.05);
        }

        .sort-button svg {
            width: 16px;
            height: 16px;
        }

        .sort-button.is-active {
            background: rgba(15, 23, 42, 0.1);
            border-color: rgba(15, 23, 42, 0.6);
            color: #0f172a;
        }

        .records-cell--actions {
            text-align: center;
            white-space: nowrap;
        }

        .records-table .icon-button {
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 8px 16px rgba(15, 23, 42, 0.05);
        }

        .records-copy {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }
    </style>
@endpush

@section('content')
    <div class="dashboard-grid">
        @include('partials.dashboard-sidebar')

        @php
            $firstProduct = $products->first();
        @endphp
        <section class="dashboard-content stack records-layout">
            <section class="card stack">
                <header class="records-toolbar" style="justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                    <div id="link-card-body" style="display: none; align-items: flex-end; gap: 1rem; flex-wrap: wrap;">
                        <div class="add-product-inline">
                            <label for="record-new-product" style="margin:0;">
                                <span class="muted" style="display:block;">Product name</span>
                                <input type="text" id="record-new-product" placeholder="New product name">
                            </label>
                            <button type="button" id="records-create-product" class="primary">Create</button>
                        </div>
                        <label style="min-width: 220px;">
                            <span class="muted" style="display:block;">Record product</span>
                            <select id="records-link-product">
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label style="min-width: 320px;">
                            <span class="muted" style="display:block;">Website product (choose product + variation)</span>
                            <select id="records-link-site-product">
                                <option value="">-- Select website product/variation --</option>
                                @foreach ($siteProducts as $siteProduct)
                                    @php
                                        $productVariations = $variations[$siteProduct->id] ?? collect();
                                    @endphp
                                    @if ($productVariations->count())
                                        @foreach ($productVariations as $variation)
                                            <option value="variation:{{ $siteProduct->id }}:{{ $variation->id }}" data-product-id="{{ $siteProduct->id }}" data-variation-id="{{ $variation->id }}">↳ {{ $siteProduct->name }} - {{ $variation->name }}</option>
                                        @endforeach
                                    @else
                                        <option value="product:{{ $siteProduct->id }}" data-product-id="{{ $siteProduct->id }}" data-variation-id="">📦 {{ $siteProduct->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </label>
                        <div style="display: inline-flex; gap: 0.75rem; align-items: center;">
                            <button type="button" id="records-link-save" class="primary">Save link</button>
                            <button type="button" id="records-link-clear" class="ghost-button">Clear</button>
                        </div>
                    </div>
                    <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                        <label style="display: inline-flex; align-items: center; gap: 0.35rem; margin: 0;">
                            <input type="checkbox" id="toggle-link-card">
                            <span class="muted">Add/Edit Product </span>
                        </label>
                        <div class="pill" id="records-link-status" style="min-width: 160px; text-align: center;">Not linked</div>
                    </div>
                </header>
            </section>

            <section class="card stack">
                <header class="records-toolbar" style="justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
                    <div style="display: flex; align-items: flex-end; gap: 1rem; flex-wrap: wrap;">
                        <h2 style="margin: 12px;"></h2>
                        <div class="product-combobox" data-product-combobox style="min-width: 320px;">
                            <input
                                type="text"
                                id="record-product-input"
                                class="product-combobox__input"
                                placeholder="Enter product"
                                autocomplete="off"
                                data-selected-name="{{ $firstProduct->name ?? '' }}"
                                value="{{ $firstProduct->name ?? '' }}"
                            >
                            <input type="hidden" id="record-product-select" data-product-selected value="{{ $firstProduct->id ?? '' }}">
                            <div class="product-combobox__dropdown" role="listbox" aria-label="Product options">
                                @if ($products->isEmpty())
                                    <p class="product-combobox__empty">No products available yet.</p>
                                @else
                                    <p class="product-combobox__empty" data-empty-message hidden>No matching products found.</p>
                                    @foreach ($products as $product)
                                        <button
                                            type="button"
                                            class="product-combobox__option {{ $firstProduct && $firstProduct->id === $product->id ? 'is-active' : '' }}"
                                            data-product-option
                                            data-product-id="{{ $product->id }}"
                                            data-product-name="{{ $product->name }}"
                                            aria-selected="{{ $firstProduct && $firstProduct->id === $product->id ? 'true' : 'false' }}"
                                        >
                                            {{ $product->name }}
                                        </button>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        <div class="records-inline-filters" style="margin: 0;">
                            <label style="display: inline-flex; align-items: center; gap: 0.35rem; margin: 0;">
                                <input type="search" id="records-filter-search" placeholder="Search phone or email" style="min-width: 220px;">
                            </label>
                        </div>
                    </div>
                    <div class="records-actions">
                        <div class="records-actions__main">
                            <div class="pill" id="records-status" style="min-width: 160px; text-align: center;">Ready</div>
                            <button type="button" id="records-add-row" class="secondary outline" aria-label="Add row">
                                <i class="fa-solid fa-plus" aria-hidden="true" style="color: #000;"></i>
                            </button>
                            <button type="button" id="records-import-trigger" class="secondary outline" aria-label="Import CSV">
                                <i class="fa-solid fa-file-import" aria-hidden="true" style="color: #000;"></i>
                            </button>
                            <button type="button" id="records-export-trigger" class="secondary outline" aria-label="Export CSV">
                                <i class="fa-solid fa-download" aria-hidden="true" style="color: #000;"></i>
                            </button>
                            <input type="file" id="records-import-file" accept=".csv" style="display: none;">
                        </div>
                        <button type="button" id="toggle-column-controls" class="secondary" aria-label="Edit fields" style="background: transparent;">
                            <i class="fa-solid fa-pen-to-square" aria-hidden="true" style="color: #000;"></i>
                        </button>
                    </div>
                </header>
                <div class="modal is-hidden" id="records-import-modal" role="dialog" aria-modal="true" aria-labelledby="records-import-title">
                    <div class="modal__content">
                        <div class="modal__header">
                            <div>
                                <h3 id="records-import-title">Import CSV</h3>
                                <p class="helper-text" style="margin: 0;">Download the sample, fill it, then upload your CSV.</p>
                            </div>
                            <div style="display: inline-flex; gap: 0.5rem; align-items: center;">
                                <button type="button" class="ghost-button" id="records-download-sample">Download sample</button>
                                <button type="button" class="ghost-button" id="records-import-close" aria-label="Close import modal">Close</button>
                            </div>
                        </div>
                        <div class="table-wrapper" style="max-height: 320px; overflow: auto;">
                            <table class="records-table">
                        <thead>
                            <tr>
                                <th style="text-align: left;">product_name</th>
                                <th style="text-align: left;">email</th>
                                <th style="text-align: left;">phone</th>
                                <th style="text-align: left;">price (sales_amount)</th>
                                <th style="text-align: left;">purchase_date (YYYY-MM-DD)</th>
                                <th style="text-align: left;">period</th>
                                <th style="text-align: left;">password</th>
                                <th style="text-align: left;">two_factor</th>
                                <th style="text-align: left;">email2</th>
                                <th style="text-align: left;">password2</th>
                                <th style="text-align: left;">remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="11" class="records-empty" style="text-align: center;">(Your CSV will contain headers only; add rows below them.)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                        <div class="form-actions" style="justify-content: flex-end;">
                            <button type="button" class="primary" id="records-import-choose">Choose CSV to upload</button>
                        </div>
                    </div>
                </div>
                <div class="column-controls" id="column-controls" style="display: none;"></div>
                <div class="records-table-wrapper">
                    <table class="records-table" id="records-table">
                        <colgroup id="records-colgroup"></colgroup>
                        <thead id="records-head"></thead>
                        <tbody id="records-table-body">
                            <tr id="records-empty">
                                <td colspan="13" class="records-empty">Pick a product and add the first row.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div style="display: flex; justify-content: center; margin-top: 0.75rem;">
                    <button type="button" id="records-show-more" class="ghost-button" style="display:none;">Show more</button>
                </div>
            </section>
        </section>
    </div>
@endsection

@push('scripts')
    @include('partials.product-combobox-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const canDeleteRecords = @json($canDeleteRecords);
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const routes = {
                products: @json(route('sheet.products')),
                createProduct: @json(route('sheet.products.store')),
                linkProduct: @json(route('sheet.products.link')),
                preferences: (productId) => @json(route('sheet.preferences.show', ['recordProduct' => 'PRODUCT_ID'])).replace('PRODUCT_ID', productId),
                updatePreferences: (productId) => @json(route('sheet.preferences.update', ['recordProduct' => 'PRODUCT_ID'])).replace('PRODUCT_ID', productId),
                lastProduct: @json(route('sheet.preferences.last.show')),
                updateLastProduct: @json(route('sheet.preferences.last.update')),
                entries: (productId) => @json(route('sheet.entries.index', ['recordProduct' => 'PRODUCT_ID'])).replace('PRODUCT_ID', productId),
                storeEntry: (productId) => @json(route('sheet.entries.store', ['recordProduct' => 'PRODUCT_ID'])).replace('PRODUCT_ID', productId),
                updateEntry: (productId, entryId) => @json(route('sheet.entries.update', ['recordProduct' => 'PRODUCT_ID', 'entryId' => 'ENTRY_ID'])).replace('PRODUCT_ID', productId).replace('ENTRY_ID', entryId),
                deleteEntry: (productId, entryId) => @json(route('sheet.entries.destroy', ['recordProduct' => 'PRODUCT_ID', 'entryId' => 'ENTRY_ID'])).replace('PRODUCT_ID', productId).replace('ENTRY_ID', entryId),
                importEntries: (productId) => @json(route('sheet.entries.import', ['recordProduct' => 'PRODUCT_ID'])).replace('PRODUCT_ID', productId),
                exportEntries: (productId) => @json(route('sheet.entries.export', ['recordProduct' => 'PRODUCT_ID'])).replace('PRODUCT_ID', productId),
            };
            const siteProducts = @json($siteProducts);
            const siteVariations = @json($variations);
            const initialProductLinks = @json($productLinks);

            const urlParams = new URLSearchParams(window.location.search);
            const parseHighlightParam = (raw) => {
                if (!raw) return null;
                const [table, recordId] = raw.split(':');
                if (!recordId) {
                    return { table: null, recordId: raw };
                }
                return { table, recordId };
            };
            const highlightFromUrl = parseHighlightParam(urlParams.get('highlight'));
            const requestedProductId = urlParams.get('product') ? Number(urlParams.get('product')) : null;

            const columns = [
                { id: 'serial_number', label: 'Order ID', type: 'text-editable' },
                { id: 'purchase_date', label: 'Purchase', type: 'date' },
                { id: 'product', label: 'Product', type: 'text-editable' },
                { id: 'email', label: 'Email', type: 'text-editable' },
                { id: 'password', label: 'Password', type: 'text-editable' },
                { id: 'phone', label: 'Phone', type: 'text-editable' },
                { id: 'sales_amount', label: 'Price', type: 'text-editable' },
                { id: 'expiry', label: 'Period', type: 'number' },
                { id: 'remaining', label: 'Remaining', type: 'computed' },
                { id: 'remarks', label: 'Remarks', type: 'textarea' },
                { id: 'two_factor', label: '2FA', type: 'text-editable' },
                { id: 'email2', label: 'Email2', type: 'text-editable' },
                { id: 'password2', label: 'Password2', type: 'text-editable' },
                { id: 'actions', label: 'Action', type: 'actions' },
            ];

            const baseOrder = columns.map((c) => c.id);

            const sanitizeOrder = (order = []) => {
                const valid = columns.map((c) => c.id);
                const seen = new Set();
                const result = [];
                order.forEach((id) => {
                    if (valid.includes(id) && !seen.has(id)) {
                        seen.add(id);
                        result.push(id);
                    }
                });
                valid.forEach((id) => {
                    if (!seen.has(id)) {
                        result.push(id);
                    }
                });
                return result;
            };

            const state = {
                products: @json($products),
                productLinks: initialProductLinks,
                selectedProductId: null,
                records: [],
                visibleLimit: 50,
                loading: false,
                columnOrder: baseOrder,
                hiddenColumns: [],
                columnWidths: {},
                newRow: null,
                showColumnControls: false,
                lastSelectedProductId: null,
                searchFilter: '',
                sort: {
                    column: null,
                    direction: 'asc',
                },
                highlight: {
                    recordId: highlightFromUrl?.recordId ?? null,
                    table: highlightFromUrl?.table ?? null,
                    productId: requestedProductId,
                    applied: false,
                    focused: false,
                },
            };
            const dirtyRecords = new Set();

            const productSelect = document.getElementById('record-product-select');
            const productInput = document.getElementById('record-product-input');
            const statusLabel = document.getElementById('records-status');
            const tableBody = document.getElementById('records-table-body');
            const emptyRow = document.getElementById('records-empty');
            const addRowButton = document.getElementById('records-add-row');
            const showMoreButton = document.getElementById('records-show-more');
            const filterSearchInput = document.getElementById('records-filter-search');
            const colgroup = document.getElementById('records-colgroup');
            const tableHead = document.getElementById('records-head');
            const columnControls = document.getElementById('column-controls');
            const toggleColumnsButton = document.getElementById('toggle-column-controls');
            const createProductButton = document.getElementById('records-create-product');
            const newProductInput = document.getElementById('record-new-product');
            const importFileInput = document.getElementById('records-import-file');
            const importTrigger = document.getElementById('records-import-trigger');
            const exportTrigger = document.getElementById('records-export-trigger');
            const importModal = document.getElementById('records-import-modal');
            const importClose = document.getElementById('records-import-close');
            const importChoose = document.getElementById('records-import-choose');
            const importDownload = document.getElementById('records-download-sample');
            const linkRecordProductSelect = document.getElementById('records-link-product');
            const linkSiteProductSelect = document.getElementById('records-link-site-product');
            const linkSaveButton = document.getElementById('records-link-save');
            const linkClearButton = document.getElementById('records-link-clear');
            const linkStatus = document.getElementById('records-link-status');
            const linkCardToggle = document.getElementById('toggle-link-card');
            const linkCardBody = document.getElementById('link-card-body');

            const sanitizePhoneSearch = (value) => (value || '').replace(/[()\s-]+/g, '');
            const handleFilterInput = (input, key) => {
                if (!input) return;
                input.addEventListener('input', (event) => {
                    const next = (event.target.value || '').toLowerCase();
                    state[key] = next;
                    renderRecords();
                });
            };

            const setLinkStatus = (text, success = false) => {
                if (!linkStatus) return;
                linkStatus.textContent = text;
                linkStatus.style.color = success ? '#15803d' : '#0f172a';
            };

            const setColumnControlsLabel = (isOpen) => {
                if (!toggleColumnsButton) return;
                const label = isOpen ? 'Close fields' : 'Edit fields';
                toggleColumnsButton.setAttribute('aria-label', label);
                toggleColumnsButton.innerHTML = '<i class="fa-solid fa-pen-to-square" aria-hidden="true" style="color: #000;"></i>';
            };

            const syncLinkCardVisibility = () => {
                if (!linkCardBody) return;
                const visible = !!linkCardToggle?.checked;
                linkCardBody.style.display = visible ? 'flex' : 'none';
            };

            const getLinkForProduct = (productId) => {
                return state.productLinks?.[productId] ?? { linked_product_id: null, linked_variation_ids: [] };
            };

            const applyLinkFormValues = (productId) => {
                if (!productId) {
                    if (linkRecordProductSelect) linkRecordProductSelect.value = '';
                    setLinkStatus('Not linked');
                    return;
                }
                const link = getLinkForProduct(productId);
                if (linkRecordProductSelect) {
                    linkRecordProductSelect.value = String(productId);
                }
                if (linkSiteProductSelect) {
                    let optionValue = '';
                    const variationId = Array.isArray(link.linked_variation_ids) && link.linked_variation_ids.length
                        ? String(link.linked_variation_ids[0])
                        : '';
                    if (link.linked_product_id) {
                        optionValue = variationId
                            ? `variation:${link.linked_product_id}:${variationId}`
                            : `product:${link.linked_product_id}`;
                    }
                    linkSiteProductSelect.value = optionValue;
                }
                const statusText = link.linked_product_id ? 'Linked' : 'Not linked';
                setLinkStatus(statusText, Boolean(link.linked_product_id));
            };

            const syncLinkProductOptions = () => {
                if (!linkRecordProductSelect) return;
                const existing = new Set(Array.from(linkRecordProductSelect.options).map((o) => o.value));
                state.products.forEach((product) => {
                    if (!existing.has(String(product.id))) {
                        const opt = document.createElement('option');
                        opt.value = product.id;
                        opt.textContent = product.name;
                        linkRecordProductSelect.appendChild(opt);
                    }
                });
            };

            const parseCsvLine = (line) => {
                const cells = [];
                let current = '';
                let inQuotes = false;
                for (let i = 0; i < line.length; i += 1) {
                    const char = line[i];
                    const next = line[i + 1];
                    if (char === '"') {
                        if (inQuotes && next === '"') {
                            current += '"';
                            i += 1;
                        } else {
                            inQuotes = !inQuotes;
                        }
                        continue;
                    }
                    if (char === ',' && !inQuotes) {
                        cells.push(current);
                        current = '';
                        continue;
                    }
                    current += char;
                }
                cells.push(current);
                return cells;
            };

            const parseCsvText = (text) => text
                .split(/\r?\n/)
                .map((line) => line.trim())
                .filter(Boolean)
                .map(parseCsvLine);

            handleFilterInput(filterSearchInput, 'searchFilter');

            if (showMoreButton) {
                showMoreButton.addEventListener('click', () => {
                    state.visibleLimit += 50;
                    renderRecords();
                });
            }

            const resolveHighlightProduct = () => {
                if (!state.products.length || !state.highlight) {
                    return null;
                }
                if (state.highlight.productId) {
                    const matchById = state.products.find((product) => Number(product.id) === Number(state.highlight.productId));
                    if (matchById) {
                        return matchById;
                    }
                }
                if (state.highlight.table) {
                    const matchByTable = state.products.find((product) => product.table_name === state.highlight.table);
                    if (matchByTable) {
                        state.highlight.productId = matchByTable.id;
                        return matchByTable;
                    }
                }
                return null;
            };

            const focusHighlightedRow = () => {
                if (!state.highlight?.recordId || state.highlight.focused) {
                    return;
                }
                if (state.highlight.productId && Number(state.highlight.productId) !== Number(state.selectedProductId)) {
                    return;
                }
                const highlightedRow = tableBody.querySelector('tr.records-row--highlight');
                if (highlightedRow) {
                    highlightedRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    state.highlight.focused = true;
                }
            };

            const getSelectedProductName = () => productInput?.value ?? '';

            const setStatus = (message, highlight = false) => {
                if (!statusLabel) return;
                statusLabel.textContent = message;
                statusLabel.style.color = highlight ? '#15803d' : '#075985';
            };

            const showInlineCopyFeedback = (button, message) => {
                if (!button) return;
                let indicator = button._copyIndicator;
                if (!indicator) {
                    indicator = document.createElement('span');
                    indicator.className = 'copy-inline-feedback';
                    button.insertAdjacentElement('afterend', indicator);
                    button._copyIndicator = indicator;
                }
                indicator.textContent = message;
                window.clearTimeout(button._copyIndicatorTimeout);
                button._copyIndicatorTimeout = window.setTimeout(() => {
                    indicator.textContent = '';
                }, 1500);
            };

            const selectProduct = (product) => {
                if (!productSelect || !productInput) return;
                productSelect.value = product.id ?? '';
                productInput.value = product.name ?? '';
                productInput.dataset.selectedName = product.name ?? '';
                state.selectedProductId = product.id ?? null;
                if (state.highlight?.recordId && !state.highlight.productId && product.id) {
                    state.highlight.productId = product.id;
                }
                syncDropdown();
            };

            const getColumns = () => state.columnOrder
                .map((id) => columns.find((col) => col.id === id))
                .filter(Boolean);

            const getVisibleColumns = () => getColumns()
                .filter((col) => !state.hiddenColumns.includes(col.id));

            const applyPreferences = (prefs = {}) => {
                state.columnOrder = sanitizeOrder(prefs.columnOrder ?? baseOrder);
                state.hiddenColumns = (prefs.hiddenColumns ?? []).filter((id) => baseOrder.includes(id));
                state.columnWidths = prefs.columnWidths ?? {};
            };

            const fetchPreferences = async (productId) => {
                if (!productId) return null;
                try {
                    const response = await fetch(routes.preferences(productId), {
                        headers: { 'Accept': 'application/json' },
                    });
                    if (!response.ok) {
                        throw new Error('Unable to load table prefs.');
                    }
                    const payload = await response.json();
                    return payload?.preferences ?? payload?.global ?? null;
                } catch (error) {
                    console.warn(error.message ?? 'Unable to load table prefs');
                    return null;
                }
            };

            const persistPreferences = async () => {
                if (!state.selectedProductId) return;
                try {
                    await fetch(routes.updatePreferences(state.selectedProductId), {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({
                            columnOrder: state.columnOrder,
                            hiddenColumns: state.hiddenColumns,
                            columnWidths: state.columnWidths,
                        }),
                    });
                } catch (error) {
                    console.warn('Unable to save table prefs', error);
                }
            };

            const persistLastProduct = async (productId) => {
                try {
                    await fetch(routes.updateLastProduct, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({
                            last_product_id: productId ?? null,
                        }),
                    });
                } catch (error) {
                    console.warn('Unable to save last product', error);
                }
            };

            const fetchLastProduct = async () => {
                try {
                    const response = await fetch(routes.lastProduct, {
                        headers: { 'Accept': 'application/json' },
                    });
                    if (!response.ok) {
                        throw new Error('Unable to load last product.');
                    }
                    const payload = await response.json();
                    return payload?.last_product_id ?? null;
                } catch (error) {
                    console.warn('Unable to load last product', error);
                    return null;
                }
            };

            const renderColumnControls = () => {
                if (!columnControls) return;
                columnControls.innerHTML = '';
                columnControls.style.display = state.showColumnControls ? 'flex' : 'none';
                if (!state.showColumnControls) {
                    return;
                }
                getColumns().forEach((col) => {
                    const control = document.createElement('div');
                    control.className = 'column-control';
                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.checked = !state.hiddenColumns.includes(col.id);
                    checkbox.dataset.columnId = col.id;
                    checkbox.addEventListener('change', () => toggleColumn(col.id, checkbox.checked));
                    const label = document.createElement('span');
                    label.textContent = col.label;
                    const left = document.createElement('button');
                    left.type = 'button';
                    left.textContent = '←';
                    left.addEventListener('click', () => moveColumn(col.id, -1));
                    const right = document.createElement('button');
                    right.type = 'button';
                    right.textContent = '→';
                    right.addEventListener('click', () => moveColumn(col.id, 1));
                    control.appendChild(checkbox);
                    control.appendChild(label);
                    control.appendChild(left);
                    control.appendChild(right);
                    columnControls.appendChild(control);
                });
            };

            const moveColumn = (columnId, direction) => {
                const idx = state.columnOrder.indexOf(columnId);
                if (idx === -1) return;
                const target = idx + direction;
                if (target < 0 || target >= state.columnOrder.length) return;
                const order = [...state.columnOrder];
                [order[idx], order[target]] = [order[target], order[idx]];
                state.columnOrder = order;
                renderTableStructure();
                renderRecords();
                persistPreferences();
            };

            const toggleColumn = (columnId, visible) => {
                if (visible) {
                    state.hiddenColumns = state.hiddenColumns.filter((id) => id !== columnId);
                } else if (!state.hiddenColumns.includes(columnId)) {
                    state.hiddenColumns.push(columnId);
                    if (state.sort.column === columnId) {
                        state.sort = { column: null, direction: 'asc' };
                    }
                }
                renderTableStructure();
                renderRecords();
                persistPreferences();
            };

            const renderTableStructure = () => {
                const visible = getVisibleColumns();
                if (colgroup) {
                    colgroup.innerHTML = visible.map((col) => {
                        const width = state.columnWidths[col.id];
                        return `<col style="${width ? `width:${width}px` : ''}">`;
                    }).join('');
                }
                if (tableHead) {
                    const tr = document.createElement('tr');
                    visible.forEach((col) => {
                        const th = document.createElement('th');
                        const headerContent = document.createElement('div');
                        headerContent.className = 'records-header-content';
                        const label = document.createElement('span');
                        label.textContent = col.label;
                        headerContent.appendChild(label);
                        if (col.id === 'remaining') {
                            const isSorted = state.sort.column === col.id;
                            const sortButton = document.createElement('button');
                            sortButton.type = 'button';
                            sortButton.className = `sort-button${isSorted ? ' is-active' : ''}`;
                            sortButton.setAttribute('aria-label', isSorted ? `Sorted ${state.sort.direction === 'asc' ? 'ascending' : 'descending'}` : 'Sort by remaining');
                            sortButton.title = 'Sort by remaining days';
                            sortButton.textContent = isSorted
                                ? (state.sort.direction === 'asc' ? '▲' : '▼')
                                : '⇅';
                            sortButton.addEventListener('click', () => setSort(col.id));
                            headerContent.appendChild(sortButton);
                        }
                        th.appendChild(headerContent);
                        const handle = document.createElement('span');
                        handle.className = 'col-resizer';
                        handle.dataset.colId = col.id;
                        th.appendChild(handle);
                        tr.appendChild(th);
                    });
                    tableHead.innerHTML = '';
                    tableHead.appendChild(tr);
                    setupResizers();
                }
            };

            const syncDropdown = async () => {
                const selectedValue = productSelect?.value;
                if (!selectedValue) {
                    state.selectedProductId = null;
                    state.records = [];
                    state.visibleLimit = 50;
                    applyPreferences({});
                    state.showColumnControls = false;
                    state.sort = { column: null, direction: 'asc' };
                    setColumnControlsLabel(false);
                    renderColumnControls();
                    renderTableStructure();
                    renderRecords();
                    setStatus('Waiting for a product...');
                    return;
                }

                state.selectedProductId = Number(selectedValue);
                state.visibleLimit = 50;
                persistLastProduct(state.selectedProductId);

                applyPreferences({});
                state.showColumnControls = false;
                state.sort = { column: null, direction: 'asc' };
                setColumnControlsLabel(false);
                renderColumnControls();
                renderTableStructure();
                renderRecords();
                const prefs = await fetchPreferences(state.selectedProductId);
                if (prefs) {
                    applyPreferences(prefs);
                    renderColumnControls();
                    renderTableStructure();
                    renderRecords();
                }
                fetchRecords();
                if (linkRecordProductSelect && state.selectedProductId) {
                    linkRecordProductSelect.value = String(state.selectedProductId);
                    applyLinkFormValues(state.selectedProductId);
                }
            };

            const fetchRecords = async () => {
                if (!state.selectedProductId) {
                    return;
                }
                setStatus('Loading records...');
                emptyRow?.remove();
                try {
                    const response = await fetch(routes.entries(state.selectedProductId), {
                        headers: {
                            'Accept': 'application/json',
                        },
                    });
                    if (!response.ok) {
                        throw new Error('Unable to fetch records.');
                    }
                    const payload = await response.json();
                    state.records = (payload.records ?? []).map(formatRecord);
                    state.visibleLimit = 50;
                    renderRecords();
                    setStatus('Records ready', true);
                } catch (error) {
                    setStatus(error.message ?? 'Unable to load records');
                }
            };

            const createProduct = async (nameOverride = null) => {
                const rawName = nameOverride ?? newProductInput?.value ?? '';
                const name = rawName.trim();
                if (!name) {
                    alert('Enter a product name first.');
                    if (newProductInput) newProductInput.focus();
                    return;
                }
                setStatus('Adding product...');
                try {
                    const response = await fetch(routes.createProduct, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({ name }),
                    });
                    if (!response.ok) {
                        const payload = await response.json().catch(() => null);
                        const message = payload?.message ?? 'Unable to add product.';
                        throw new Error(message);
                    }
                    const result = await response.json();
                    const product = result?.product;
                    if (product?.id) {
                        state.products = [product, ...state.products.filter((p) => p.id !== product.id)];
                        state.productLinks[product.id] = {
                            linked_product_id: product.linked_product_id ?? null,
                            linked_variation_ids: [],
                        };
                        if (newProductInput) newProductInput.value = '';
                        selectProduct(product);
                        syncLinkProductOptions();
                        applyLinkFormValues(product.id);
                        setStatus('Product added', true);
                    } else {
                        setStatus('Product added, but missing details', true);
                    }
                } catch (error) {
                    setStatus(error.message ?? 'Unable to add product');
                }
            };

            const formatRecord = (record) => {
                return {
                    ...record,
                    serial_number: record.serial_number ?? '',
                    purchase_date: record.purchase_date ?? '',
                    product: record.product ?? '',
                    email: record.email ?? '',
                    password: record.password ?? '',
                    phone: record.phone ?? '',
                    sales_amount: record.sales_amount ?? '',
                    expiry: record.expiry ?? '',
                    remaining_days: record.remaining_days ?? '',
                    remarks: record.remarks ?? '',
                    two_factor: record.two_factor ?? '',
                    email2: record.email2 ?? '',
                    password2: record.password2 ?? '',
                };
            };

            const saveLink = async () => {
                if (!linkRecordProductSelect) return;
                const recordProductId = linkRecordProductSelect.value;
                if (!recordProductId) {
                    alert('Select a record product to link.');
                    return;
                }
                const selectedOption = linkSiteProductSelect?.selectedOptions?.[0];
                const linkedProductId = selectedOption?.dataset?.productId || null;
                const linkedVariationId = selectedOption?.dataset?.variationId || null;
                const linkedVariationIds = linkedVariationId ? [linkedVariationId] : [];

                try {
                    setLinkStatus('Saving...');
                    const response = await fetch(routes.linkProduct, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({
                            record_product_id: recordProductId,
                            linked_product_id: linkedProductId || null,
                            linked_variation_ids: linkedVariationIds,
                        }),
                    });
                    if (!response.ok) {
                        const payload = await response.json().catch(() => null);
                        throw new Error(payload?.message ?? 'Unable to save link.');
                    }
                    const payload = await response.json();
                    const product = payload?.product;
                    if (product?.id) {
                        state.productLinks[product.id] = {
                            linked_product_id: product.linked_product_id,
                            linked_variation_ids: product.linked_variation_ids
                                ? (Array.isArray(product.linked_variation_ids) ? product.linked_variation_ids : JSON.parse(product.linked_variation_ids || '[]'))
                                : [],
                        };
                        setLinkStatus('Link saved', true);
                        applyLinkFormValues(product.id);
                    } else {
                        setLinkStatus('Link saved', true);
                    }
                } catch (error) {
                    setLinkStatus(error.message ?? 'Unable to save link');
                }
            };

            const renderRecords = () => {
                tableBody.innerHTML = '';
                const visible = getVisibleColumns();

                if (!state.records.length && !state.newRow) {
                    const row = document.createElement('tr');
                    const cell = document.createElement('td');
                    cell.colSpan = Math.max(visible.length, 1);
                    cell.className = 'records-empty';
                    cell.textContent = state.selectedProductId ? 'No rows yet. Add one above.' : 'Pick a product and add the first row.';
                    row.appendChild(cell);
                    tableBody.appendChild(row);
                    return;
                }

                const renderRowCells = (record, rowId, serial, isNew = false) => {
                    const row = document.createElement('tr');
                    row.dataset.id = rowId;
                    const remainingRaw = computeRemainingDays(record);
                    const remainingNum = Number(remainingRaw);
                    if (!Number.isNaN(remainingNum)) {
                        if (remainingNum === 0) {
                            row.classList.add('records-row--expiring');
                        } else if (remainingNum < 0) {
                            row.classList.add('records-row--expired');
                        }
                    }
                    const shouldHighlight = state.highlight?.recordId
                        && String(rowId) === String(state.highlight.recordId)
                        && (!state.highlight.productId || Number(state.highlight.productId) === Number(state.selectedProductId));
                    if (shouldHighlight) {
                        row.classList.add('records-row--highlight');
                        state.highlight.applied = true;
                    }
                    visible.forEach((col) => {
                        const cell = document.createElement('td');
                        if (col.id === 'actions') {
                            cell.classList.add('records-cell--actions');
                        }
                        const content = renderCellContent(col, record, serial, isNew);
                        if (content) {
                            cell.appendChild(content);
                        }
                        row.appendChild(cell);
                    });
                    tableBody.appendChild(row);
                };

                if (state.newRow) {
                    renderRowCells(state.newRow, 'new', 'New', true);
                }

                const filteredRecords = state.records.filter((record) => {
                    const phone = (record.phone ?? '').toLowerCase();
                    const phoneSanitized = sanitizePhoneSearch(phone);
                    const emailPrimary = (record.email ?? '').toLowerCase();
                    const emailSecondary = (record.email2 ?? '').toLowerCase();
                    const emailTarget = `${emailPrimary} ${emailSecondary}`.trim();
                    const term = (state.searchFilter ?? '').trim();
                    if (term === '') {
                        return true;
                    }
                    const sanitizedTerm = sanitizePhoneSearch(term);
                    const phoneMatch = sanitizedTerm !== '' && phoneSanitized.includes(sanitizedTerm);
                    const emailMatch = emailPrimary.includes(term) || emailSecondary.includes(term) || emailTarget.includes(term);
                    return phoneMatch || emailMatch;
                });

                const recordsForDisplay = state.sort.column ? getSortedRecords(filteredRecords) : filteredRecords;
                const limitedRecords = recordsForDisplay.slice(0, state.visibleLimit);
                limitedRecords.forEach((record, index) => {
                    renderRowCells(record, record.id, index + 1, false);
                });

                if (showMoreButton) {
                    showMoreButton.style.display = recordsForDisplay.length > state.visibleLimit ? '' : 'none';
                }
                applyColumnWidths();
                focusHighlightedRow();
            };

            const getSortedRecords = (records = state.records) => {
                if (state.sort.column !== 'remaining') {
                    return [...records];
                }
                const sorted = [...records];
                const toNumber = (record) => {
                    const raw = computeRemainingDays(record);
                    if (raw === '' || raw === null || raw === undefined) {
                        return null;
                    }
                    const value = Number(raw);
                    return Number.isNaN(value) ? null : value;
                };
                sorted.sort((a, b) => {
                    const left = toNumber(a);
                    const right = toNumber(b);
                    if (left === null && right === null) return 0;
                    if (left === null) return 1;
                    if (right === null) return -1;
                    return left - right;
                });
                if (state.sort.direction === 'desc') {
                    sorted.reverse();
                }
                return sorted;
            };

            const setSort = (columnId) => {
                if (columnId !== 'remaining') return;
                const isSame = state.sort.column === columnId;
                const nextDirection = isSame && state.sort.direction === 'asc' ? 'desc' : 'asc';
                state.sort = {
                    column: columnId,
                    direction: nextDirection,
                };
                renderTableStructure();
                state.visibleLimit = 50;
                renderRecords();
            };

            const renderCellContent = (col, record, serial, isNew) => {
                const value = record[col.id] ?? '';
                if (col.id === 'remaining') {
                    return document.createTextNode(computeRemainingDays(record));
                }
                if (col.id === 'expiry') {
                    const input = document.createElement('input');
                    input.type = 'text';
                    input.placeholder = '0';
                    input.className = 'records-field';
                    input.dataset.field = 'expiry';
                    input.value = formatExpiryDisplay(value);
                    return input;
                }
                if (col.id === 'sales_amount') {
                    const input = document.createElement('input');
                    input.type = 'number';
                    input.min = '0';
                    input.step = '1';
                    input.className = 'records-field';
                    input.dataset.field = 'sales_amount';
                    const numeric = Number(value);
                    input.value = Number.isFinite(numeric) ? Math.trunc(numeric) : '';
                    input.placeholder = '0';
                    return input;
                }
                if (col.id === 'purchase_date') {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'records-date-wrapper';

                    const input = document.createElement('input');
                    input.type = 'text';
                    input.placeholder = 'YYYY/Mon/DD';
                    input.className = 'records-field records-date-input';
                    input.dataset.field = 'purchase_date';
                    input.value = formatDisplayDate(value || formatDate(new Date()));

                    const picker = document.createElement('input');
                    picker.type = 'date';
                    picker.className = 'records-date-picker';

                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'records-date-button';
                    button.setAttribute('aria-label', 'Open date picker');
                    button.innerHTML = `
                        <svg viewBox="0 0 24 24" aria-hidden="true" width="20" height="20">
                            <rect x="4" y="5" width="16" height="15" rx="2" ry="2" fill="none" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M8 3v4M16 3v4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M4 10h16" stroke="currentColor" stroke-width="1.5"/>
                        </svg>
                    `;

                    button.addEventListener('click', () => {
                        if (picker.showPicker) {
                            picker.showPicker();
                        } else {
                            picker.click();
                        }
                    });

                    picker.addEventListener('change', () => {
                        const iso = picker.value;
                        if (!iso) return;
                        input.value = formatDisplayDate(iso);
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    });

                    wrapper.appendChild(input);
                    wrapper.appendChild(button);
                    wrapper.appendChild(picker);
                    return wrapper;
                }
                if (col.type === 'textarea') {
                    const textarea = document.createElement('textarea');
                    textarea.className = 'records-field';
                    textarea.dataset.field = col.id;
                    textarea.value = value ?? '';
                    textarea.textContent = value ?? '';
                    return textarea;
                }
                if (col.type === 'text-editable') {
                    const editable = document.createElement('div');
                    editable.className = 'records-field';
                    editable.contentEditable = 'true';
                    editable.dataset.field = col.id;
                    editable.innerHTML = escapeHtml(value ?? '');

                    if (['email', 'password', 'email2', 'password2'].includes(col.id)) {
                        const wrapper = document.createElement('div');
                        wrapper.className = 'records-copy';
                        wrapper.appendChild(editable);

                        const copyButton = document.createElement('button');
                        copyButton.type = 'button';
                        copyButton.className = 'cell-action-button';
                        copyButton.dataset.action = 'copy';
                        copyButton.dataset.copyField = col.id;
                        copyButton.setAttribute('aria-label', `Copy ${col.label}`);
                        copyButton.innerHTML = `
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M8 7V5a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2h-2" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                <rect x="4" y="7" width="12" height="12" rx="2" ry="2" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        `;
                        wrapper.appendChild(copyButton);

                        const feedback = document.createElement('span');
                        feedback.className = 'copy-inline-feedback';
                        wrapper.appendChild(feedback);

                        return wrapper;
                    }

                    return editable;
                }
                if (col.id === 'actions') {
                    const wrapper = document.createElement('div');
                    wrapper.style.display = 'flex';
                    wrapper.style.gap = '0.35rem';
                    if (isNew) {
                        const save = document.createElement('button');
                        save.type = 'button';
                        save.className = 'primary';
                        save.dataset.action = 'save-new';
                        save.textContent = 'Save';
                        const cancel = document.createElement('button');
                        cancel.type = 'button';
                        cancel.className = 'secondary outline';
                        cancel.dataset.action = 'cancel-new';
                        cancel.textContent = 'Cancel';
                        wrapper.appendChild(save);
                        wrapper.appendChild(cancel);
                    } else {
                        const save = document.createElement('button');
                        save.type = 'button';
                        save.className = 'icon-button';
                        save.setAttribute('aria-label', 'Save row');
                        save.dataset.action = 'save-row';
                        save.innerHTML = `
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M5 4h11l3 3v13a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V4z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                                <path d="M7 4v6h10V4" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                                <path d="M8 20v-6h8v6" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                            </svg>
                        `;
                        wrapper.appendChild(save);
                        if (!canDeleteRecords) {
                            return wrapper;
                        }
                        const del = document.createElement('button');
                        del.type = 'button';
                        del.className = 'icon-button';
                        del.setAttribute('aria-label', 'Delete row');
                        del.dataset.action = 'delete';
                        del.innerHTML = `
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M6 7h12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                <path d="M10 11v6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                <path d="M14 11v6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                <path d="M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                <path d="M19 7l-.6 10.2A2 2 0 0116.41 19H7.59a2 2 0 01-1.99-1.8L5 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                            </svg>
                        `;
                        wrapper.appendChild(del);
                    }
                    return wrapper;
                }
                return document.createTextNode(value ?? '');
            };

            const formatDate = (value) => {
                if (!value) return '';
                const date = new Date(value);
                if (Number.isNaN(date.getTime())) return '';
                return date.toISOString().slice(0, 10);
            };

            const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const monthLookup = monthNames.reduce((map, name, index) => {
                map[name.toLowerCase()] = index;
                return map;
            }, {});

            const formatDisplayDate = (value) => {
                const date = new Date(value);
                if (Number.isNaN(date.getTime())) return '';
                const year = date.getFullYear();
                const month = monthNames[date.getMonth()];
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}/${month}/${day}`;
            };

            const toIsoDateFromDisplay = (raw) => {
                const value = (raw ?? '').trim();
                if (!value) return '';
                const direct = new Date(value);
                if (!Number.isNaN(direct.getTime())) {
                    return formatDate(direct);
                }
                const match = value.match(/^(\d{4})\/([A-Za-z]{3})\/(\d{1,2})$/);
                if (!match) return '';
                const [, yearStr, monthStr, dayStr] = match;
                const monthIndex = monthLookup[monthStr.toLowerCase()];
                if (monthIndex === undefined) return '';
                const date = new Date(Number(yearStr), monthIndex, Number(dayStr));
                if (Number.isNaN(date.getTime())) return '';
                return formatDate(date);
            };

            const formatExpiryDisplay = (value) => {
                const num = Number(value);
                if (Number.isNaN(num) || value === '') return '';
                return `${num} Days`;
            };

            const parseExpiryValue = (raw) => {
                const match = String(raw ?? '').match(/\d+/);
                if (!match) return '';
                const num = Number(match[0]);
                return Number.isNaN(num) ? '' : num;
            };

            const computeExpiryDate = (record) => {
                const purchase = record.purchase_date;
                const expiryDays = Number(record.expiry ?? 0);
                if (!purchase || Number.isNaN(expiryDays)) {
                    return '';
                }
                const date = new Date(purchase);
                if (Number.isNaN(date.getTime())) {
                    return '';
                }
                date.setDate(date.getDate() + expiryDays);
                return formatDate(date);
            };

            const computeRemainingDays = (record) => {
                const expiryString = computeExpiryDate(record);
                if (!expiryString) {
                    return '';
                }
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                const expiry = new Date(expiryString);
                if (Number.isNaN(expiry.getTime())) {
                    return '';
                }
                const diffMs = expiry.getTime() - today.getTime();
                return Math.floor(diffMs / (1000 * 60 * 60 * 24));
            };

            const escapeHtml = (value) => {
                return String(value ?? '').replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            };

            const updateCell = async (recordId, field, value) => {
                if (!state.selectedProductId) {
                    return;
                }
                if (recordId === 'new') {
                    return;
                }
                setStatus(`Updating ${field}...`);
                try {
                    const response = await fetch(routes.updateEntry(state.selectedProductId, recordId), {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({
                            [field]: value,
                        }),
                    });

                    if (!response.ok) {
                        const payload = await response.json().catch(() => null);
                        const message = payload?.message ?? 'Unable to update value.';
                        throw new Error(message);
                    }

                    const payload = await response.json();
                    const updated = payload?.record;
                    if (updated) {
                        const index = state.records.findIndex((row) => String(row.id) === String(recordId));
                        if (index !== -1) {
                            state.records[index] = formatRecord(updated);
                            renderRecords();
                        }
                    }
                    dirtyRecords.delete(String(recordId));
                    setStatus('Saved', true);
                } catch (error) {
                    setStatus(error.message ?? 'Unable to update value');
                }
            };

            const collectRowPayload = (row) => {
                const payload = {};
                columns.forEach((col) => {
                    if (col.type === 'actions' || col.type === 'computed') {
                        return;
                    }
                    const fieldEl = row.querySelector(`[data-field="${col.id}"]`);
                    if (!fieldEl) {
                        return;
                    }
                    let value = '';
                    if (fieldEl.tagName === 'TEXTAREA') {
                        value = fieldEl.value;
                    } else if (fieldEl.tagName === 'INPUT') {
                        value = fieldEl.value;
                    } else {
                        value = fieldEl.textContent.trim();
                    }
                    if (col.id === 'expiry') {
                        value = parseExpiryValue(value);
                    }
                    if (col.id === 'purchase_date') {
                        value = toIsoDateFromDisplay(value);
                    }
                    payload[col.id] = value;
                });
                return payload;
            };

            const saveRow = async (recordId, rowEl) => {
                if (!state.selectedProductId) {
                    return;
                }
                if (!rowEl) {
                    return;
                }
                if (!dirtyRecords.has(String(recordId))) {
                    setStatus('Unchanged Data');
                    return;
                }
                const payload = collectRowPayload(rowEl);
                setStatus('Saving row...');
                try {
                    const response = await fetch(routes.updateEntry(state.selectedProductId, recordId), {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify(payload),
                    });
                    if (!response.ok) {
                        const data = await response.json().catch(() => null);
                        const message = data?.message ?? 'Unable to save row.';
                        throw new Error(message);
                    }
                    const result = await response.json();
                    if (result?.record) {
                        const index = state.records.findIndex((r) => String(r.id) === String(recordId));
                        if (index !== -1) {
                            state.records[index] = formatRecord(result.record);
                            renderRecords();
                        }
                    }
                    dirtyRecords.delete(String(recordId));
                    setStatus('Data Updated Successfully.', true);
                } catch (error) {
                    setStatus(error.message ?? 'Unable to save row');
                }
            };

            const handleTableChange = (event) => {
                const field = event.target.dataset.field;
                const row = event.target.closest('tr');
                if (!field || !row?.dataset?.id) {
                    return;
                }
                const recordId = row.dataset.id;
                let value = '';
                if (event.target.tagName === 'TEXTAREA') {
                    value = event.target.value;
                } else if (event.target.type === 'date' || event.target.type === 'number' || event.target.type === 'text') {
                    value = event.target.value;
                } else {
                    value = event.target.textContent.trim();
                }
                if (field === 'expiry') {
                    const numeric = parseExpiryValue(value);
                    value = numeric;
                    if (event.target.value !== undefined) {
                        event.target.value = formatExpiryDisplay(numeric);
                    }
                }
                if (field === 'purchase_date') {
                    const iso = toIsoDateFromDisplay(value);
                    value = iso;
                    if (event.target.value !== undefined) {
                        event.target.value = formatDisplayDate(iso);
                    }
                }
                if (recordId === 'new') {
                    state.newRow = {
                        ...state.newRow,
                        [field]: value,
                    };
                    renderRecords();
                    return;
                }
                dirtyRecords.add(String(recordId));
                const localIndex = state.records.findIndex((r) => String(r.id) === String(recordId));
                if (localIndex !== -1) {
                    state.records[localIndex] = {
                        ...state.records[localIndex],
                        [field]: value,
                    };
                    renderRecords();
                }
            };

            const handleTableBlur = (event) => {
                if (['INPUT', 'TEXTAREA', 'SELECT'].includes(event.target.tagName)) {
                    return;
                }
                const field = event.target.dataset.field;
                const row = event.target.closest('tr');
                if (!field || !row?.dataset?.id) {
                    return;
                }
                const recordId = row.dataset.id;
                const value = event.target.textContent.trim();
                if (recordId === 'new') {
                    state.newRow = {
                        ...state.newRow,
                        [field]: value,
                    };
                    renderRecords();
                    return;
                }
                dirtyRecords.add(String(recordId));
            };

            const startNewRow = () => {
                if (!state.selectedProductId) {
                    alert('Select a product first.');
                    return;
                }
                if (state.newRow) {
                    return;
                }
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                state.newRow = {
                    serial_number: '',
                    purchase_date: formatDate(today),
                    product: getSelectedProductName(),
                    email: '',
                    password: '',
                    phone: '',
                    sales_amount: '',
                    expiry: '',
                    remaining_days: '',
                    remarks: '',
                    two_factor: '',
                    email2: '',
                    password2: '',
                };
                renderRecords();
            };

            const saveNewRow = async () => {
                if (!state.selectedProductId || !state.newRow) {
                    return;
                }
                const payload = {
                    ...state.newRow,
                    product: state.newRow.product || getSelectedProductName(),
                };
                if (!payload.purchase_date) {
                    alert('Purchase date is required.');
                    return;
                }
                try {
                    setStatus('Saving row...');
                    const response = await fetch(routes.storeEntry(state.selectedProductId), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify(payload),
                    });
                    if (!response.ok) {
                        const data = await response.json().catch(() => null);
                        const message = data?.message ?? 'Unable to save row.';
                        throw new Error(message);
                    }
                    const result = await response.json();
                    if (result?.record) {
                        state.records.unshift(formatRecord(result.record));
                    }
                    state.newRow = null;
                    renderRecords();
                    setStatus('Row added', true);
                } catch (error) {
                    setStatus(error.message ?? 'Unable to save row');
                }
            };

            const applyColumnWidths = () => {
                if (!colgroup) return;
                const cols = colgroup.querySelectorAll('col');
                const visible = getVisibleColumns();
                cols.forEach((col, index) => {
                    const colId = visible[index]?.id;
                    const width = colId ? state.columnWidths[colId] : null;
                    col.style.width = width ? `${width}px` : '';
                });
            };

            const setupResizers = () => {
                const headers = Array.from(document.querySelectorAll('#records-table thead th'));
                headers.forEach((th) => {
                    const handle = th.querySelector('.col-resizer');
                    const colId = handle?.dataset?.colId;
                    if (!handle || !colId) {
                        return;
                    }
                    handle.addEventListener('mousedown', (event) => startResize(event, colId));
                });
            };

            const startResize = (event, colId) => {
                event.preventDefault();
                const startX = event.pageX;
                const headers = Array.from(document.querySelectorAll('#records-table thead th'));
                const headerIndex = getVisibleColumns().findIndex((col) => col.id === colId);
                const startWidth = state.columnWidths[colId]
                    ?? headers[headerIndex]?.getBoundingClientRect().width
                    ?? 120;

                const onMove = (moveEvent) => {
                    moveEvent.preventDefault();
                    const delta = moveEvent.pageX - startX;
                    const nextWidth = Math.max(80, startWidth + delta);
                    state.columnWidths = {
                        ...state.columnWidths,
                        [colId]: nextWidth,
                    };
                    applyColumnWidths();
                };

                const onUp = () => {
                    document.removeEventListener('mousemove', onMove);
                    document.removeEventListener('mouseup', onUp);
                    persistPreferences();
                };

                document.addEventListener('mousemove', onMove);
                document.addEventListener('mouseup', onUp);
            };

            productSelect.addEventListener('change', syncDropdown);
            tableBody.addEventListener('change', handleTableChange);
            tableBody.addEventListener('blur', handleTableBlur, true);
            tableBody.addEventListener('click', async (event) => {
                const button = event.target.closest('button[data-action]');
                if (!button) {
                    return;
                }
                const action = button.dataset.action;
                const row = button.closest('tr');
                const recordId = row?.dataset?.id;
                if (!recordId) {
                    return;
                }

                if (action === 'copy') {
                    const field = button.dataset.copyField;
                    if (!field) {
                        return;
                    }
                    const target = row.querySelector(`[data-field="${field}"]`);
                    const value = target?.textContent?.trim() ?? '';
                    if (value === '') {
                        return;
                    }
                    const feedback = button.nextElementSibling && button.nextElementSibling.classList.contains('copy-inline-feedback')
                        ? button.nextElementSibling
                        : null;

                    const setFeedback = (text) => {
                        if (feedback) {
                            feedback.textContent = text;
                            window.clearTimeout(button._copyIndicatorTimeout);
                            button._copyIndicatorTimeout = window.setTimeout(() => {
                                feedback.textContent = '';
                            }, 1500);
                        }
                    };

                    const writeClipboard = async () => {
                        try {
                            await navigator.clipboard.writeText(value);
                            button.setAttribute('aria-label', 'Copied');
                            setFeedback('Copied');
                        } catch (error) {
                            console.error('Unable to copy', error);
                            button.setAttribute('aria-label', 'Copy failed');
                            setFeedback('Copy failed');
                        }
                    };

                    if (navigator.clipboard?.writeText) {
                        writeClipboard();
                    } else {
                        const textarea = document.createElement('textarea');
                        textarea.value = value;
                        textarea.setAttribute('readonly', 'readonly');
                        textarea.style.position = 'absolute';
                        textarea.style.left = '-9999px';
                        document.body.appendChild(textarea);
                        textarea.select();
                        try {
                            document.execCommand('copy');
                            button.setAttribute('aria-label', 'Copied');
                            setFeedback('Copied');
                        } catch (fallbackError) {
                            console.error('Fallback copy failed', fallbackError);
                            button.setAttribute('aria-label', 'Copy failed');
                            setFeedback('Copy failed');
                        } finally {
                            document.body.removeChild(textarea);
                        }
                    }
                    return;
                }

                if (action === 'delete') {
                    if (!canDeleteRecords) {
                        return;
                    }
                    if (!confirm('Delete this row?')) {
                        return;
                    }
                    try {
                        setStatus('Deleting...');
                        const response = await fetch(routes.deleteEntry(state.selectedProductId, recordId), {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                        });
                        if (!response.ok) {
                            const payload = await response.json().catch(() => null);
                            const message = payload?.message ?? 'Unable to delete row.';
                            throw new Error(message);
                        }
                        state.records = state.records.filter((record) => String(record.id) !== String(recordId));
                        renderRecords();
                        setStatus('Row deleted', true);
                    } catch (error) {
                        setStatus(error.message ?? 'Unable to delete row');
                    }
                    return;
                }

                if (action === 'save-row') {
                    await saveRow(recordId, row);
                    return;
                }

                if (action === 'save-new') {
                    await saveNewRow();
                    return;
                }

                if (action === 'cancel-new') {
                    state.newRow = null;
                    renderRecords();
                }
            });
            addRowButton.addEventListener('click', startNewRow);
            toggleColumnsButton.addEventListener('click', () => {
                state.showColumnControls = !state.showColumnControls;
                setColumnControlsLabel(state.showColumnControls);
                renderColumnControls();
            });
            linkCardToggle?.addEventListener('change', syncLinkCardVisibility);
            linkRecordProductSelect?.addEventListener('change', (event) => {
                const productId = event.target.value ? Number(event.target.value) : null;
                applyLinkFormValues(productId);
            });
            linkSaveButton?.addEventListener('click', saveLink);
            linkClearButton?.addEventListener('click', () => {
                if (linkSiteProductSelect) linkSiteProductSelect.value = '';
                setLinkStatus('Not linked');
            });
            createProductButton?.addEventListener('click', () => createProduct());
            newProductInput?.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    createProduct();
                }
            });

            const openImportModal = () => {
                importModal?.classList.remove('is-hidden');
            };

            const closeImportModal = () => {
                importModal?.classList.add('is-hidden');
            };

            importTrigger?.addEventListener('click', openImportModal);
            importClose?.addEventListener('click', closeImportModal);
            importModal?.addEventListener('click', (event) => {
                if (event.target === importModal) {
                    closeImportModal();
                }
            });

            importDownload?.addEventListener('click', () => {
                const headers = ['product_name','email','phone','price','purchase_date','period','password','two_factor','email2','password2','remarks'];
                const csv = headers.join(',');
                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'import-sample.csv';
                a.click();
                URL.revokeObjectURL(url);
            });

            importChoose?.addEventListener('click', () => {
                importFileInput?.click();
            });

            exportTrigger?.addEventListener('click', () => {
                if (!state.selectedProductId) {
                    setStatus('Select a product to export.');
                    return;
                }
                window.location.href = routes.exportEntries(state.selectedProductId);
            });

            importFileInput?.addEventListener('change', async () => {
                if (!importFileInput.files.length || !state.selectedProductId) {
                    return;
                }
                const file = importFileInput.files[0];
                try {
                    const text = await file.text();
                    const rows = parseCsvText(text);
                    if (!rows.length) {
                        setStatus('CSV is empty');
                        return;
                    }

                    const expectedHeaders = ['product_name', 'email', 'phone', 'sales_amount', 'purchase_date', 'period', 'password', 'two_factor', 'email2', 'password2', 'remarks'];
                    const headerRow = rows.shift().map((h) => h.trim().toLowerCase());

                    const indexMap = {};
                    headerRow.forEach((header, idx) => {
                        const canonical = header === 'price' ? 'sales_amount' : header;
                        if (expectedHeaders.includes(canonical)) {
                            indexMap[canonical] = idx;
                        }
                    });

                    if (!Object.keys(indexMap).length) {
                        setStatus('CSV headers not recognized');
                        return;
                    }

                    const normalizedRows = rows
                        .map((row) => expectedHeaders.map((header) => {
                            const idx = indexMap[header];
                            return idx !== undefined && row[idx] !== undefined ? row[idx] : '';
                        }))
                        .filter((row) => row.some((cell) => String(cell).trim() !== ''));

                    if (!normalizedRows.length) {
                        setStatus('No data rows found');
                        return;
                    }

                    const normalizedCsv = [
                        expectedHeaders.join(','),
                        ...normalizedRows.map((r) => r.map((v) => `"${String(v).replace(/"/g, '""')}"`).join(',')),
                    ].join('\n');

                    const blob = new Blob([normalizedCsv], { type: 'text/csv;charset=utf-8;' });
                    const formData = new FormData();
                    formData.append('file', blob, file.name || 'import.csv');

                    setStatus('Importing CSV...');
                    const response = await fetch(routes.importEntries(state.selectedProductId), {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: formData,
                    });
                    if (!response.ok) {
                        const payload = await response.json().catch(() => null);
                        const message = payload?.message ?? 'Unable to import CSV.';
                        throw new Error(message);
                    }
                    const result = await response.json();
                    const inserted = result?.inserted ?? 0;
                    await fetchRecords();
                    setStatus(`Imported ${inserted} row${inserted === 1 ? '' : 's'}`, true);
                } catch (error) {
                    setStatus(error.message ?? 'Unable to import CSV');
                } finally {
                    importFileInput.value = '';
                }
            });

            renderColumnControls();
            renderTableStructure();
            renderRecords();

            if (state.products.length) {
                (async () => {
                    state.lastSelectedProductId = await fetchLastProduct();
                    const preferredProduct = resolveHighlightProduct()
                        ?? (() => {
                            const stored = state.lastSelectedProductId;
                            if (!stored) return null;
                            const id = Number(stored);
                            if (Number.isNaN(id)) return null;
                            return state.products.find((p) => Number(p.id) === id) ?? null;
                        })();
                    if (preferredProduct) {
                        selectProduct(preferredProduct);
                    } else if (!productSelect.value) {
                        productSelect.value = state.products[0].id;
                        if (productInput && !productInput.value) {
                            productInput.value = state.products[0].name ?? '';
                            productInput.dataset.selectedName = productInput.value;
                        }
                    }
                    syncDropdown();
                })();
            }
            syncLinkProductOptions();
            if (state.selectedProductId) {
                applyLinkFormValues(state.selectedProductId);
            } else {
                applyLinkFormValues(state.products[0]?.id ?? null);
            }
            syncLinkCardVisibility();
        });
    </script>
@endpush
