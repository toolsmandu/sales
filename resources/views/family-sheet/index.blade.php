@extends('layouts.app')

@push('styles')
    @include('partials.dashboard-styles')
    <style>
        .family-column-controls {
            display: none;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin: 0.5rem 0 1rem 0;
            padding: 0.75rem;
            border: 1px dashed rgba(15, 23, 42, 0.15);
            border-radius: 0.75rem;
            background: #f8fafc;
        }
        .family-column-control {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.25rem 0.45rem;
            background: #fff;
            border: 1px solid rgba(15, 23, 42, 0.1);
            border-radius: 0.5rem;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
        }
        .family-column-control button {
            padding: 0.2rem 0.4rem;
            min-width: 0;
        }
        .family-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .family-card {
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 0.8rem;
            padding: 1rem;
            background: #fff;
            width: 100%;
            box-sizing: border-box;
        }



        .family-table-wrapper {
            overflow: auto;
            border: 1px solid rgba(15, 23, 42, 0.1);
            border-radius: 0.75rem;
            width: 100%;
            max-width: 100%;
            display: flex;
        }
        table.family-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 100%;
            table-layout: fixed;
            background: linear-gradient(180deg, #fff, #f8fafc 18%, #fff 100%);
            flex: 1 1 auto;
        }
        table.family-table th,
        table.family-table td {
            border: 1px solid rgba(148, 163, 184, 0.35);
            padding: 0.55rem 0.65rem;
            text-align: center;
            background: #fff;
            word-break: break-word;
            white-space: normal;
        }
        table.family-table th {
            position: relative;
        }
        table.family-table thead th {
            background: linear-gradient(180deg, rgba(226, 232, 240, 0.9), rgba(241, 245, 249, 0.9));
            font-weight: 800;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            position: sticky;
            top: 0;
            z-index: 1;
        }
        table.family-table tbody tr:nth-child(even) td {
            background: #f8fafc;
        }
        table.family-table tbody tr:hover td {
            background: #eef2ff;
            border-color: rgba(79, 70, 229, 0.35);
            transition: background 0.2s ease, border-color 0.2s ease;
        }
        .family-col-resizer {
            position: absolute;
            top: 0;
            right: -6px;
            width: 12px;
            height: 100%;
            cursor: col-resize;
            user-select: none;
            z-index: 2;
        }
        .chip {
            display: inline-flex;
            gap: 0.35rem;
            align-items: center;
            padding: 0.2rem 0.55rem;
            border-radius: 999px;
            background: #e0f2fe;
            color: #0f172a;
            font-size: 0.9rem;
        }

        .family-inline-filters {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .family-inline-filters input[type="search"] {
            min-width: 160px;
        }

        .family-inline-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .family-inline-row__spacer {
            margin-left: auto;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .family-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 0.75rem;
        }

        .family-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .family-actions__main {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .family-form-inline {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem 1rem;
            align-items: flex-end;
        }

        .family-form-inline label {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            margin: 0;
            min-width: 180px;
        }

        .family-form-inline textarea {
            min-height: 2.6rem;
        }

        .family-form-inline button[type="submit"] {
            align-self: flex-end;
            min-height: 2.85rem;
            padding: 0.55rem 1.1rem;
            line-height: 1.2;
        }

        .family-form-inline--inline-action {
            flex-wrap: nowrap;
            align-items: flex-end;
            gap: 0.5rem;
        }

        .family-form-inline--inline-action label {
            flex: 1 1 320px;
            min-width: 240px;
        }

        .family-form-inline--inline-action button[type="submit"] {
            height: 2.6rem;
            margin-left: 0.5rem;
            white-space: nowrap;
            width: auto;
            min-width: 120px;
            flex-shrink: 0;
        }

        .family-admin-form {
            flex-wrap: nowrap;
            align-items: flex-end;
        }

        .family-admin-form label {
            min-width: 180px;
        }

        .family-admin-form button[type="submit"] {
            height: 2.6rem;
            align-self: flex-end;
            min-width: 80px;
            flex-shrink: 0;
            width: auto;
            max-width: max-content;
            padding: 0.55rem 1rem;
        }

        .family-add-member-button {
            background: #16a34a;
            border-color: #15803d;
            color: #fff;
        }

        .family-add-member-button:hover,
        .family-add-member-button:focus-visible {
            background: #15803d;
            border-color: #166534;
            color: #fff;
        }

        .family-modal__content {
            width: min(1200px, calc(100vw - 2.5rem));
            max-height: 90vh;
        }

        .modal.modal--center {
            align-items: center;
            justify-content: center;
        }

        .modal.modal--center .modal__content {
            margin: auto;
        }

        .family-import-export {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: nowrap;
        }

        .family-import-export form {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0;
        }

        .family-file-input {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            border: 0;
        }

        .family-file-label {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.45rem 0.9rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(59, 130, 246, 0.5);
            color: #1d4ed8;
            background: #eef2ff;
            cursor: pointer;
            font-weight: 600;
            line-height: 1.2;
        }

        .family-file-name {
            max-width: 180px;
            font-size: 0.9rem;
            color: #475569;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .family-export-button {
            border-radius: 0.65rem;
            padding-left: 1rem;
            padding-right: 1rem;
        }

        table.family-table .ghost-button--compact {
            width: 2rem;
            height: 2rem;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        table.family-table .ghost-button--compact svg {
            width: 1.1rem;
            height: 1.1rem;
        }

        .family-card--full {
            grid-column: 1 / -1;
        }

        .family-compact-form {
            max-width: 350px;
        }

        .family-link-overview .family-link-input {
            display: none;
        }

        .family-link-overview.is-editing .family-link-input {
            display: inline-block;
        }

        .family-link-overview.is-editing .family-link-display {
            display: none;
        }

        .create-sections-grid {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            align-items: stretch;
        }

        .create-column {
            width: 100%;
        }

        .create-card form:not(.family-form-inline--inline-action) button[type="submit"] {
            width: 100%;
        }

        @media (max-width: 1200px) {
            .family-form-inline label,
            .family-admin-form label {
                min-width: 160px;
            }
            table.family-table {
                min-width: 100%;
            }
            table.family-table th,
            table.family-table td {
                padding: 0.45rem 0.5rem;
                font-size: 0.92rem;
            }
        }

        @media (max-width: 900px) {
            .family-toolbar {
                flex-direction: column;
                align-items: stretch;
            }
            .family-actions {
                width: 100%;
                justify-content: flex-start;
            }
            .family-inline-filters {
                width: 100%;
            }
            .family-inline-filters form,
            .family-inline-filters label {
                width: 100%;
            }
            .family-inline-filters input[type="search"] {
                width: 100%;
                min-width: 0;
            }
            .family-admin-form {
                flex-wrap: wrap;
            }
            .family-admin-form button[type="submit"] {
                width: auto;
            }
            table.family-table {
                min-width: 720px;
            }
        }

        @media (max-width: 640px) {
            .family-form-inline label {
                min-width: 100%;
            }
            .family-actions__main {
                flex-wrap: wrap;
            }
            .family-actions__main > * {
                margin-right: 0.25rem;
            }
            table.family-table th,
            table.family-table td {
                padding: 0.4rem 0.45rem;
                font-size: 0.88rem;
            }
        }

        .family-admin-card .family-admin-form button[type="submit"] {
            width: auto;
        }
    </style>
@endpush

@php
    $familyColumns = [
        ['id' => 'account', 'label' => 'Main Account'],
        ['id' => 'family_name', 'label' => 'Family Name'],
        ['id' => 'order', 'label' => 'Order ID'],
        ['id' => 'email', 'label' => 'Email'],
        ['id' => 'phone', 'label' => 'Phone'],
        ['id' => 'product', 'label' => 'Product'],
        ['id' => 'amount', 'label' => 'Amount'],
        ['id' => 'purchase', 'label' => 'Purchase Date'],
        ['id' => 'period', 'label' => 'Period'],
        ['id' => 'remaining', 'label' => 'Remaining Days'],
        ['id' => 'remarks', 'label' => 'Remarks'],
    ];
@endphp

@section('content')
    <div class="dashboard-grid">
        @include('partials.dashboard-sidebar')

        <section class="dashboard-content stack">
            <header class="flex items-center gap-2 justify-between">
                <h1 style="margin: 0;">Family Sheet</h1>
                @if (session('status'))
                    <span class="chip">{{ session('status') }}</span>
                @endif
            </header>
<br>
            <div class="family-card family-card--full">
                <div class="family-inline-row" style="align-items: center; justify-content: flex-start; gap: 1rem; margin-bottom: 0.75rem;">
                    <strong>Add/Link Family Products:</strong>
                    <label style="display: inline-flex; align-items: center; gap: 0.35rem; margin: 0;">
                        <input type="checkbox" id="toggle-create-sections">
                    </label>
                </div>
                <div class="family-card create-card family-admin-card" id="family-admin-card" style="margin-bottom: 1rem;">
                    <h3>Add Admin Account</h3>
                    <form method="POST" action="{{ route('family-sheet.accounts.store') }}" class="family-form-inline family-admin-form">
                        @csrf
                        <label>
                            Product
                            <select name="family_product_id" required>
                                @forelse ($products as $product)
                                    <option value="{{ $product->id }}" @selected(optional($selectedProduct)->id === $product->id)>{{ $product->name }}</option>
                                @empty
                                    <option value="">Create a product first</option>
                                @endforelse
                            </select>
                        </label>
                        <label>
                            Admin Account's Email
                            <input type="text" name="name" required placeholder="Login Email Here">
                        </label>
                        <label>
                            Account index
                            <input type="number" name="account_index" min="1" placeholder="Auto" value="{{ old('account_index', $nextAccountIndex ?? '') }}">
                        </label>
                        <label>
                            Max members
                            <input type="number" name="capacity" min="1" placeholder="e.g. 5" required>
                        </label>
                        <label>
                            Period (days)
                            <input type="number" name="period" min="0" placeholder="e.g. 30">
                        </label>
                        <label>
                            Remarks
                            <input type="text" name="remarks" placeholder="Remarks">
                        </label>
                        <button type="submit">Create</button>
                    </form>
                </div>
                <div class="create-sections-grid" id="create-sections-wrapper" style="margin: 0; display: none;">
                    <div class="create-column">
                        <div class="family-card create-card">
                            <h3>Create Family Product</h3>
                            <form method="POST" action="{{ route('family-sheet.products.store') }}" class="family-form-inline family-form-inline--inline-action" style="justify-content: flex-start;">
                                @csrf
                                <label>
                                    Name
                                    <input type="text" name="name" required placeholder="Product name">
                                </label>
                                <button type="submit">Create</button>
                            </form>
                        </div>
                    </div>
                    <div class="family-card create-card" id="family-link-card" style="display: none;">
                        <h3 style="margin-top: 0;">Link Family Product to Website Product</h3>
                        <form method="POST" action="{{ route('family-sheet.products.link') }}" class="family-form-inline">
                            @csrf
                            <label>
                                Family product
                                <select name="family_product_id" required>
                                    <option value="">Select family product</option>
                                    @foreach ($products as $familyProductOption)
                                        <option value="{{ $familyProductOption->id }}">{{ $familyProductOption->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label>
                                Website product
                                <select name="linked_product_id" id="family-link-site-product">
                                    <option value="">-- Optional: Link to website product --</option>
                                    @foreach ($siteProducts as $siteProduct)
                                        <option value="{{ $siteProduct->id }}">{{ $siteProduct->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label>
                                Website variations (optional)
                                <select name="linked_variation_ids[]" id="family-link-variations" multiple size="4">
                                    @foreach ($siteProducts as $siteProduct)
                                        @foreach (($variations[$siteProduct->id] ?? collect()) as $variation)
                                            <option value="{{ $variation->id }}" data-product-id="{{ $siteProduct->id }}">{{ $siteProduct->name }} - {{ $variation->name }}</option>
                                        @endforeach
                                    @endforeach
                                </select>
                            </label>
                            <button type="submit">Link</button>
                        </form>
                    </div>
                    @if ($products->count())
                        <div class="family-card family-link-overview" id="family-link-overview" style="margin-top: 1rem;">
                            <div class="family-inline-row" style="align-items: center; justify-content: space-between;">
                                <h4 style="margin: 0;">Linked Products Overview</h4>
                                <button type="button" id="family-link-edit-toggle" class="ghost-button">Edit linkage</button>
                            </div>
                            <div class="table-wrapper" style="overflow:auto;">
                                <table class="records-table" style="min-width: 640px;">
                                    <thead>
                                        <tr>
                                            <th style="text-align:left;">Family Product</th>
                                            <th style="text-align:left;">Linked Product</th>
                                            <th style="text-align:left;">Linked Variations</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($products as $familyProduct)
                                            @php
                                                $linkedProduct = $siteProducts->firstWhere('id', $familyProduct->linked_product_id);
                                                $linkedIds = $familyProduct->linked_variation_ids ? json_decode($familyProduct->linked_variation_ids, true) : [];
                                                $variationNames = collect($linkedIds)
                                                    ->map(function ($id) use ($variations) {
                                                        foreach ($variations as $list) {
                                                            $found = $list->firstWhere('id', $id);
                                                            if ($found) {
                                                                return $found->name;
                                                            }
                                                        }
                                                        return null;
                                                    })
                                                    ->filter()
                                                    ->values();
                                            @endphp
                                            <tr class="family-link-row">
                                                <td style="text-align:left; white-space: nowrap;">{{ $familyProduct->name }}</td>
                                                <td style="text-align:left; min-width: 220px;">
                                                    <span class="family-link-display">{{ $linkedProduct->name ?? '—' }}</span>
                                                    <select name="linked_product_id" class="family-link-site-product family-link-input" form="family-link-form-{{ $familyProduct->id }}">
                                                        <option value="">-- Optional: Link to website product --</option>
                                                        @foreach ($siteProducts as $siteProduct)
                                                            <option value="{{ $siteProduct->id }}" @selected($siteProduct->id == $familyProduct->linked_product_id)>{{ $siteProduct->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td style="text-align:left; min-width: 280px;">
                                                    <span class="family-link-display">{{ $variationNames->isEmpty() ? '—' : $variationNames->implode(', ') }}</span>
                                                    <select name="linked_variation_ids[]" class="family-link-variations family-link-input" multiple size="3" form="family-link-form-{{ $familyProduct->id }}">
                                                        @foreach ($siteProducts as $siteProduct)
                                                            @foreach (($variations[$siteProduct->id] ?? collect()) as $variation)
                                                                <option value="{{ $variation->id }}" data-product-id="{{ $siteProduct->id }}" @selected(in_array($variation->id, $linkedIds))>{{ $siteProduct->name }} - {{ $variation->name }}</option>
                                                            @endforeach
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td style="text-align:left; width: 80px;">
                                                    <button type="submit" form="family-link-form-{{ $familyProduct->id }}" class="ghost-button ghost-button--compact family-link-input">Save</button>
                                                </td>
                                            </tr>
                                            <form id="family-link-form-{{ $familyProduct->id }}" method="POST" action="{{ route('family-sheet.products.link') }}">
                                                @csrf
                                                <input type="hidden" name="family_product_id" value="{{ $familyProduct->id }}">
                                            </form>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
<br>

          

@if ($selectedProduct)
                <div class="family-toolbar" style="margin-top: 1rem;">
                    <div class="family-inline-filters">
                        @if ($products->count())
                            <form method="GET" action="{{ route('family-sheet.index') }}" style="display: inline-flex; gap: 0.5rem; align-items: center;">
                                <label style="display: inline-flex; gap: 0.35rem; align-items: center; margin: 0;">
                                    <span class="helper-text">Select product</span>
                                    <select name="product_id" onchange="this.form.submit()">
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}" @selected(optional($selectedProduct)->id === $product->id)>{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            </form>
                        @endif
                        <label style="display: inline-flex; gap: 0.35rem; align-items: center; margin: 0;">
                            <input type="search" id="family-filter-search" placeholder="Search phone or email">
                        </label>
                        <button type="button" id="family-open-add-member" class="secondary outline family-add-member-button">Add Member Manually</button>
                        <label style="display: inline-flex; gap: 0.35rem; align-items: center; margin: 0;">
                            <input type="checkbox" id="family-show-all-accounts">
                            <span>Show all accounts</span>
                        </label>
                    </div>
                    <div class="family-actions">
                        <div class="family-actions__main">
                            <form method="POST" action="{{ route('family-sheet.import') }}" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="family_product_id" value="{{ $selectedProduct->id }}">
                                <input type="file" name="csv_file" id="family-import-file" class="family-file-input" accept=".csv,text/csv" required>
                                <button type="button" id="family-import-trigger" class="secondary outline" aria-label="Import CSV">
                                    <i class="fa-solid fa-file-import" aria-hidden="true" style="color: #000;"></i>
                                </button>
                            </form>
                            <button
                                type="button"
                                id="family-export-trigger"
                                class="secondary"
                                style="background: transparent;"
                                data-export-url="{{ route('family-sheet.export', ['product_id' => $selectedProduct->id]) }}"
                                aria-label="Export CSV"
                            >
                                <i class="fa-solid fa-download" aria-hidden="true" style="color: #000;"></i>
                            </button>
                            <button type="button" id="family-toggle-column-controls" class="secondary" aria-label="Edit fields" style="background: transparent;">
                                <i class="fa-solid fa-pen-to-square" aria-hidden="true" style="color: #000;"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="family-table-wrapper" id="family-accounts-table-wrapper" data-default-open="{{ old('account_edit') ? 'true' : 'false' }}" style="margin-top: 0.75rem; display: none;">
                    @if ($accounts->count())
                        <table class="family-table">
                            <thead>
                                <tr>
                                    <th>Main Account</th>
                                    <th>Product</th>
                                    <th>Index</th>
                                    <th>Max members</th>
                                    <th>Period</th>
                                    <th>Usage</th>
                                    <th>Remarks</th>
                                    <th style="width: 180px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($accounts as $account)
                                    <tr>
                                        <form id="family-account-update-{{ $account->id }}" method="POST" action="{{ route('family-sheet.accounts.update', $account->id) }}">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="account_edit" value="1">
                                        </form>
                                        <form id="family-account-delete-{{ $account->id }}" method="POST" action="{{ route('family-sheet.accounts.destroy', $account->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="account_edit" value="1">
                                        </form>
                                        <td>
                                            <input type="text" name="name" form="family-account-update-{{ $account->id }}" value="{{ old('name', $account->name) }}" required style="width: 100%;">
                                        </td>
                                        <td>
                                            {{ $account->product_name ?? $account->family_product_name ?? '—' }}
                                        </td>
                                        <td>
                                            <input type="number" name="account_index" form="family-account-update-{{ $account->id }}" min="1" value="{{ old('account_index', $account->account_index) }}" style="width: 100%;">
                                        </td>
                                        <td>
                                            <input type="number" name="capacity" form="family-account-update-{{ $account->id }}" min="1" value="{{ old('capacity', $account->capacity) }}" required style="width: 100%;">
                                        </td>
                                        <td>
                                            <input type="number" name="period" form="family-account-update-{{ $account->id }}" min="0" value="{{ old('period', $account->period) }}" style="width: 100%;">
                                        </td>
                                        <td>
                                            {{ $account->member_count }}/{{ $account->capacity ?? '∞' }} used
                                        </td>
                                        <td>
                                            <input type="text" name="remarks" form="family-account-update-{{ $account->id }}" value="{{ old('remarks', $account->remarks) }}" style="width: 100%;">
                                        </td>
                                        <td style="display: flex; gap: 0.35rem; align-items: center; justify-content: center;">
                                            <button type="submit" form="family-account-update-{{ $account->id }}" class="ghost-button ghost-button--compact" aria-label="Save">
                                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                                    <path d="M5 4h11l3 3v13a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V4z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                                                    <path d="M7 4v6h10V4" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                                                    <path d="M8 20v-6h8v6" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                                                </svg>
                                            </button>
                                            <button type="submit" form="family-account-delete-{{ $account->id }}" class="ghost-button ghost-button--compact" style="color: #b91c1c;" aria-label="Delete" onclick="return confirm('Delete this main account and all its members?');">
                                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                                    <path d="M6 7h12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                                    <path d="M10 11v6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                                    <path d="M14 11v6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                                    <path d="M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                                    <path d="M19 7l-.6 10.2A2 2 0 0116.41 19H7.59a2 2 0 01-1.99-1.8L5 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="helper-text" style="padding: 0.75rem;">No main accounts yet.</p>
                    @endif
                </div>
                <div id="family-column-controls" class="family-column-controls" style="display: none; margin-top: 0; margin-bottom: 0.75rem;"></div>

                <div class="modal is-hidden modal--center" id="family-add-member-modal" role="dialog" aria-modal="true" aria-labelledby="family-add-member-title">
                    <div class="modal__content family-modal__content">
                        <div class="modal__header">
                            <h3 id="family-add-member-title" style="margin: 0;">Add Member Manually</h3>
                            <button type="button" class="ghost-button" id="family-add-member-close" aria-label="Close add member">Close</button>
                        </div>
                        <div class="family-inline-row" style="align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                            <h3 style="margin: 0;">Add Member</h3>
                            <div class="family-inline-row" style="gap: 0.75rem; align-items: center;">
                                <button type="button" id="family-member-toggle-fields" class="ghost-button">Edit fields</button>
                            </div>
                        </div>
                        <div id="family-member-field-controls" class="family-column-controls" style="display: none; margin-top: 0; margin-bottom: 0.75rem;"></div>
                        <form method="POST" action="{{ route('family-sheet.members.store') }}" class="family-form-inline" id="family-add-member-form">
                            @csrf
                            <label data-member-field="family_account_id">
                                Main Account
                                <select name="family_account_id" required>
                                    @forelse ($accounts as $account)
                                        <option value="{{ $account->id }}" data-period="{{ $account->period ?? '' }}">
                                            {{ $account->name }} @if(!empty($account->account_index))(Index: {{ $account->account_index }})@endif ({{ $account->member_count }}/{{ $account->capacity ?? '∞' }} used)
                                        </option>
                                    @empty
                                        <option value="">Create a main account first</option>
                                    @endforelse
                                </select>
                                @error('family_account_id')
                                    <p class="helper-text" style="color: #b91c1c;">{{ $message }}</p>
                                @enderror
                            </label>
                            <label data-member-field="purchase_date">
                                Purchase Date
                                <input type="date" name="purchase_date" value="{{ old('purchase_date', now()->format('Y-m-d')) }}">
                            </label>
                            <label data-member-field="order_id">
                                Order ID
                                <input type="text" name="order_id" placeholder="Order/serial">
                            </label>
                            <label data-member-field="email">
                                Email
                                <input type="email" name="email" placeholder="email">
                            </label>
                            <label data-member-field="phone">
                                Phone
                                <input type="text" name="phone" placeholder="phone">
                            </label>
                            <label data-member-field="product">
                                Product (member)
                                <input type="text" name="product" placeholder="Product name">
                            </label>
                            <label data-member-field="sales_amount">
                                Sales Amount
                                <input type="number" name="sales_amount" min="0" step="1">
                            </label>
                            <label data-member-field="expiry">
                                Expiry (days)
                                <input type="number" name="expiry" min="0" step="1">
                            </label>
                            <label data-member-field="remaining_days">
                                Remaining Days
                                <input type="number" name="remaining_days" step="1">
                            </label>
                            <label data-member-field="remarks">
                                Remarks
                                <textarea name="remarks" rows="2"></textarea>
                            </label>
                            <button type="submit">Add Member</button>
                        </form>
                    </div>
                </div>
                <div class="family-table-wrapper">
                    <table class="family-table" id="family-table" data-product-id="{{ $selectedProduct->id }}">
                        <colgroup id="family-colgroup">
                            @foreach ($familyColumns as $col)
                                <col data-col-id="{{ $col['id'] }}">
                            @endforeach
                        </colgroup>
                        <thead>
                            <tr>
                                @foreach ($familyColumns as $col)
                                    <th data-col-id="{{ $col['id'] }}">
                                        {{ $col['label'] }}
                                        <span class="family-col-resizer" data-col-id="{{ $col['id'] }}"></span>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @include('family-sheet.partials.data-rows', [
                                'dataAccounts' => $dataAccounts,
                                'membersByAccount' => $membersByAccount,
                                'familyColumns' => $familyColumns,
                            ])
                        </tbody>
                    </table>
                </div>
                <div style="display:flex; justify-content:center; margin-top:0.75rem;">
                    <button
                        type="button"
                        id="family-show-more"
                        class="ghost-button"
                        data-next-page="{{ $dataAccountsHasMore ? $dataAccountsPage + 1 : '' }}"
                        style="{{ $dataAccountsHasMore ? '' : 'display:none;' }}"
                    >
                        Load more
                    </button>
                </div>
            @else
                <p class="helper-text">Create a family product to start.</p>
            @endif
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const table = document.getElementById('family-table');
            const colgroup = document.getElementById('family-colgroup');
            const headerRow = table?.querySelector('thead tr');
            const tbody = table?.querySelector('tbody');
            const tableWrapper = table?.closest('.family-table-wrapper');
            const filterSearchInput = document.getElementById('family-filter-search');
            const columnControls = document.getElementById('family-column-controls');
            const toggleColumnsButton = document.getElementById('family-toggle-column-controls');
            const memberToggleFields = document.getElementById('family-member-toggle-fields');
            const memberFieldControls = document.getElementById('family-member-field-controls');
            const memberForm = document.getElementById('family-add-member-form');
            const createSectionsToggle = document.getElementById('toggle-create-sections');
            const createSectionsWrapper = document.getElementById('create-sections-wrapper');
            const showAllAccountsToggle = document.getElementById('family-show-all-accounts');
            const accountsTableWrapper = document.getElementById('family-accounts-table-wrapper');
            const adminCard = document.getElementById('family-admin-card');
            const linkCard = document.getElementById('family-link-card');
            const linkSiteProduct = document.getElementById('family-link-site-product');
            const linkVariations = document.getElementById('family-link-variations');
            const linkRows = Array.from(document.querySelectorAll('.family-link-row'));
            const linkOverview = document.getElementById('family-link-overview');
            const linkEditToggle = document.getElementById('family-link-edit-toggle');
            const hasTable = table && colgroup && headerRow && tbody;
            const memberShowMore = document.getElementById('family-show-more');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            const productId = Number(table?.dataset.productId || 0);
            const preferenceRoutes = {
                table: @json(route('family-sheet.preferences.table.update', ['familyProduct' => 'PRODUCT_ID'])).replace('PRODUCT_ID', String(productId || 0)),
                member: @json(route('family-sheet.preferences.member.update', ['familyProduct' => 'PRODUCT_ID'])).replace('PRODUCT_ID', String(productId || 0)),
            };
            const serverTablePreferences = @json($familyTablePreferences ?? null);
            const serverMemberPreferences = @json($familyMemberPreferences ?? null);

            if (createSectionsToggle) {
                const applyCreateVisibility = () => {
                    const visible = createSectionsToggle.checked;
                    if (createSectionsWrapper) {
                        createSectionsWrapper.style.display = visible ? '' : 'none';
                    }
                    if (linkCard) {
                        linkCard.style.display = visible ? '' : 'none';
                    }
                    if (adminCard) {
                        adminCard.style.display = visible ? 'none' : '';
                    }
                };
                createSectionsToggle.addEventListener('change', applyCreateVisibility);
                applyCreateVisibility();
            }

            if (showAllAccountsToggle && accountsTableWrapper) {
                const applyAccountsVisibility = () => {
                    const visible = showAllAccountsToggle.checked || accountsTableWrapper.dataset.defaultOpen === 'true';
                    accountsTableWrapper.style.display = visible ? '' : 'none';
                };
                showAllAccountsToggle.addEventListener('change', () => {
                    accountsTableWrapper.dataset.defaultOpen = showAllAccountsToggle.checked ? 'true' : 'false';
                    applyAccountsVisibility();
                });
                applyAccountsVisibility();
            }

            if (memberForm) {
                const accountSelect = memberForm.querySelector('select[name="family_account_id"]');
                const expiryInput = memberForm.querySelector('input[name="expiry"]');
                const applyAccountPeriod = () => {
                    if (!accountSelect || !expiryInput) return;
                    const selected = accountSelect.selectedOptions?.[0];
                    const period = selected?.dataset?.period ?? '';
                    if (!expiryInput.value && period !== '') {
                        expiryInput.value = period;
                    }
                };
                if (accountSelect && expiryInput) {
                    accountSelect.addEventListener('change', applyAccountPeriod);
                    applyAccountPeriod();
                }
            }

            const importFile = document.getElementById('family-import-file');
            const importTrigger = document.getElementById('family-import-trigger');
            const importForm = importFile?.closest('form');
            if (importFile && importTrigger && importForm) {
                importTrigger.addEventListener('click', () => {
                    importFile.click();
                });
                importFile.addEventListener('change', () => {
                    if (importFile.files?.length) {
                        importForm.submit();
                    }
                });
            }

            const exportTrigger = document.getElementById('family-export-trigger');
            if (exportTrigger) {
                exportTrigger.addEventListener('click', () => {
                    const url = exportTrigger.dataset.exportUrl;
                    if (url) {
                        window.location.href = url;
                    }
                });
            }

            const addMemberOpen = document.getElementById('family-open-add-member');
            const addMemberModal = document.getElementById('family-add-member-modal');
            const addMemberClose = document.getElementById('family-add-member-close');
            const openAddMemberModal = () => {
                if (addMemberModal) {
                    addMemberModal.classList.remove('is-hidden');
                }
            };
            const closeAddMemberModal = () => {
                if (addMemberModal) {
                    addMemberModal.classList.add('is-hidden');
                }
            };
            addMemberOpen?.addEventListener('click', openAddMemberModal);
            addMemberClose?.addEventListener('click', closeAddMemberModal);
            addMemberModal?.addEventListener('click', (event) => {
                if (event.target === addMemberModal) {
                    closeAddMemberModal();
                }
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeAddMemberModal();
                }
            });

            if (linkSiteProduct && linkVariations) {
                const filterVariations = () => {
                    const selectedProduct = linkSiteProduct.value;
                    Array.from(linkVariations.options).forEach((opt) => {
                        const matches = selectedProduct && opt.dataset.productId === selectedProduct;
                        opt.hidden = !matches;
                        if (!matches && opt.selected) {
                            opt.selected = false;
                        }
                    });
                };
                linkSiteProduct.addEventListener('change', filterVariations);
                filterVariations();
            }

            const bindRowFilters = () => {
                linkRows.forEach((row) => {
                    const siteSelect = row.querySelector('.family-link-site-product');
                    const variationSelect = row.querySelector('.family-link-variations');
                    if (!siteSelect || !variationSelect) return;
                    const filter = () => {
                        const selected = siteSelect.value;
                        Array.from(variationSelect.options).forEach((opt) => {
                            const matches = selected && opt.dataset.productId === selected;
                            opt.hidden = !matches;
                            if (!matches && opt.selected) {
                                opt.selected = false;
                            }
                        });
                    };
                    siteSelect.addEventListener('change', filter);
                    filter();
                });
            };
            bindRowFilters();

            if (linkOverview && linkEditToggle) {
                let editing = false;
                const apply = () => {
                    if (editing) {
                        linkOverview.classList.add('is-editing');
                        linkEditToggle.textContent = 'Done';
                    } else {
                        linkOverview.classList.remove('is-editing');
                        linkEditToggle.textContent = 'Edit linkage';
                    }
                };
                linkEditToggle.addEventListener('click', () => {
                    editing = !editing;
                    apply();
                });
                apply();
            }

            if (!hasTable) return;

            if (memberShowMore) {
                memberShowMore.addEventListener('click', () => {
                    const nextPage = Number(memberShowMore.dataset.nextPage || 0);
                    if (!nextPage || !productId) return;
                    memberShowMore.disabled = true;
                    memberShowMore.textContent = 'Loading...';
                    const url = new URL(window.location.href);
                    url.searchParams.set('accounts_partial', '1');
                    url.searchParams.set('account_page', String(nextPage));
                    url.searchParams.set('product_id', String(productId));
                    fetch(url.toString(), {
                        headers: { 'Accept': 'application/json' },
                    })
                        .then((response) => response.json())
                        .then((payload) => {
                            if (payload?.html && tbody) {
                                tbody.insertAdjacentHTML('beforeend', payload.html);
                            }
                            if (payload?.has_more) {
                                memberShowMore.dataset.nextPage = payload.next_page;
                                memberShowMore.disabled = false;
                                memberShowMore.textContent = 'Load more';
                            } else {
                                memberShowMore.remove();
                            }
                        })
                        .catch(() => {
                            memberShowMore.disabled = false;
                            memberShowMore.textContent = 'Load more';
                        });
                });
            }

            const columns = [
                { id: 'account', label: 'Main Account' },
                { id: 'order', label: 'Order ID' },
                { id: 'email', label: 'Email' },
                { id: 'phone', label: 'Phone' },
                { id: 'purchase', label: 'Purchase Date' },
                { id: 'period', label: 'Period' },
                { id: 'remaining', label: 'Remaining Days' },
                { id: 'remarks', label: 'Remarks' },
            ];

            const defaultOrder = columns.map((c) => c.id);

            const sanitizeOrder = (order) => {
                const base = Array.isArray(order) ? order.filter((id) => defaultOrder.includes(id)) : [];
                const missing = defaultOrder.filter((id) => !base.includes(id));
                return [...base, ...missing];
            };

            const prefs = serverTablePreferences || {};
            const state = {
                order: sanitizeOrder(prefs.columnOrder ?? prefs.order),
                hidden: Array.isArray(prefs.hiddenColumns ?? prefs.hidden)
                    ? (prefs.hiddenColumns ?? prefs.hidden)
                        .filter((id) => defaultOrder.includes(id) && id !== 'remarks')
                    : [],
                widths: prefs.columnWidths && typeof prefs.columnWidths === 'object' ? prefs.columnWidths : {},
            };

            let tablePrefTimeout = null;
            const persistTablePreferences = async () => {
                if (!productId) return;
                try {
                    await fetch(preferenceRoutes.table, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        keepalive: true,
                        body: JSON.stringify({
                            columnOrder: state.order,
                            hiddenColumns: state.hidden,
                            columnWidths: state.widths,
                        }),
                    });
                } catch (error) {
                    console.warn('Unable to save family prefs', error);
                }
            };
                const schedulePersistTablePreferences = () => {
                    if (tablePrefTimeout) {
                        clearTimeout(tablePrefTimeout);
                    }
                    tablePrefTimeout = setTimeout(persistTablePreferences, 400);
                };

            const getVisible = () => state.order.filter((id) => !state.hidden.includes(id));

            const reorderContainer = (container) => {
                const nodes = Array.from(container.children);
                const map = {};
                nodes.forEach((el) => {
                    const id = el.dataset.colId;
                    if (id) map[id] = el;
                });
                container.innerHTML = '';
                state.order.forEach((id) => {
                    if (map[id]) {
                        container.appendChild(map[id]);
                    }
                });
            };

            const reorderRows = () => {
                Array.from(tbody.querySelectorAll('tr')).forEach((row) => {
                    const cells = Array.from(row.children);
                    const map = {};
                    cells.forEach((cell) => {
                        const id = cell.dataset.colId;
                        if (id) {
                            map[id] = cell;
                        }
                    });
                    row.innerHTML = '';
                    state.order.forEach((id) => {
                        if (map[id]) {
                            row.appendChild(map[id]);
                        }
                    });
                });
            };

            const applyAccountColspans = () => {
                if (!tbody) return;
                const visibleCount = getVisible().length;
                Array.from(tbody.querySelectorAll('.family-account-row td')).forEach((cell) => {
                    cell.colSpan = visibleCount || 1;
                });
            };

            const applyVisibility = () => {
                const hiddenSet = new Set(state.hidden);
                const applyDisplay = (selectorFn) => {
                    selectorFn().forEach((el) => {
                        const id = el.dataset.colId;
                        if (!id) return;
                        el.style.display = hiddenSet.has(id) ? 'none' : '';
                    });
                };
                applyDisplay(() => Array.from(colgroup.querySelectorAll('col')));
                applyDisplay(() => Array.from(headerRow.querySelectorAll('th')));
                applyDisplay(() => Array.from(table.querySelectorAll('tbody td[data-col-id]')));
                applyAccountColspans();
            };

            const applyWidths = () => {
                const visibleIds = getVisible();
                if (!tableWrapper || !visibleIds.length) return;
                const wrapperWidth = tableWrapper.clientWidth;
                if (!wrapperWidth) return;

                // Force equal-width columns to eliminate right-side gaps.
                const equalWidth = Math.floor(wrapperWidth / visibleIds.length);
                table.style.width = `${wrapperWidth}px`;
                table.style.minWidth = `${wrapperWidth}px`;

                visibleIds.forEach((id) => {
                    const col = colgroup.querySelector(`col[data-col-id="${id}"]`);
                    const header = headerRow.querySelector(`th[data-col-id="${id}"]`);
                    if (col) {
                        col.style.width = `${equalWidth}px`;
                    }
                    if (header) {
                        header.style.width = `${equalWidth}px`;
                    }
                });
            };

            const startResize = (event, colId) => {
                event.preventDefault();
                const startX = event.pageX;
                const header = headerRow.querySelector(`th[data-col-id="${colId}"]`);
                const colEl = colgroup.querySelector(`col[data-col-id="${colId}"]`);
                const startWidth = state.widths[colId]
                    ?? colEl?.getBoundingClientRect().width
                    ?? header?.getBoundingClientRect().width
                    ?? 140;

                const onMove = (moveEvent) => {
                    moveEvent.preventDefault();
                    const delta = moveEvent.pageX - startX;
                    const nextWidth = Math.max(80, startWidth + delta);
                    state.widths[colId] = nextWidth;
                    applyWidths();
                };

                const onUp = () => {
                    document.removeEventListener('mousemove', onMove);
                    document.removeEventListener('mouseup', onUp);
                    persistTablePreferences();
                };

                document.addEventListener('mousemove', onMove);
                document.addEventListener('mouseup', onUp);
            };

            const setupResizers = () => {
                const headers = Array.from(headerRow.querySelectorAll('th'));
                headers.forEach((th) => {
                    const handle = th.querySelector('.family-col-resizer');
                    const colId = handle?.dataset?.colId;
                    if (!handle || !colId) return;
                    if (handle.dataset.bound === 'true') return;
                    handle.dataset.bound = 'true';
                    handle.addEventListener('mousedown', (event) => startResize(event, colId));
                });
            };

            const moveColumn = (columnId, direction) => {
                const idx = state.order.indexOf(columnId);
                if (idx === -1) return;
                const target = idx + direction;
                if (target < 0 || target >= state.order.length) return;
                const order = [...state.order];
                [order[idx], order[target]] = [order[target], order[idx]];
                state.order = order;
                render();
                schedulePersistTablePreferences();
            };

            const toggleColumn = (columnId, visible) => {
                if (columnId === 'remarks') return;
                if (visible) {
                    state.hidden = state.hidden.filter((id) => id !== columnId);
                } else if (!state.hidden.includes(columnId)) {
                    state.hidden.push(columnId);
                }
                render();
                schedulePersistTablePreferences();
            };

            const renderColumnControls = () => {
                if (!columnControls) return;
                columnControls.innerHTML = '';
                columns.forEach((col) => {
                    const control = document.createElement('div');
                    control.className = 'family-column-control';
                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.checked = !state.hidden.includes(col.id);
                    if (col.id === 'remarks') {
                        checkbox.disabled = true;
                    }
                    checkbox.addEventListener('change', () => {
                        toggleColumn(col.id, checkbox.checked);
                    });
                    const labelEl = document.createElement('span');
                    labelEl.textContent = col.id === 'remarks'
                        ? 'Remarks / Actions'
                        : (col.label || col.id);
                    control.appendChild(checkbox);
                    control.appendChild(labelEl);
                    columnControls.appendChild(control);
                });
            };

            if (toggleColumnsButton && columnControls) {
                toggleColumnsButton.addEventListener('click', () => {
                    const isOpen = columnControls.style.display === 'flex';
                    columnControls.style.display = isOpen ? 'none' : 'flex';
                    toggleColumnsButton.setAttribute('aria-label', isOpen ? 'Edit fields' : 'Done');
                    if (!isOpen) {
                        renderColumnControls();
                    }
                });
            }

            const placeAccountNotes = () => {
                const visible = getVisible();
                const targetId = visible.find((id) => id !== 'account');
                Array.from(tbody.querySelectorAll('.family-account-row')).forEach((row) => {
                    const noteCells = Array.from(row.querySelectorAll('td[data-account-note]'));
                    noteCells.forEach((cell) => {
                        cell.textContent = '';
                    });
                    if (!targetId) return;
                    const targetCell = row.querySelector(`td[data-col-id="${targetId}"]`);
                    if (targetCell) {
                        targetCell.textContent = 'Main account';
                    }
                });
            };

            const applyRowFilters = () => {
                const term = (filterSearchInput?.value || '').trim().toLowerCase();
                const hasTerm = term.length > 0;
                const phoneTerm = term.replace(/[()\s-]+/g, '');
                const filtersActive = hasTerm;
                const rows = Array.from(tbody.querySelectorAll('tr'));

                if (!filtersActive) {
                    rows.forEach((row) => {
                        row.style.display = '';
                    });
                    return;
                }

                let currentAccountRow = null;
                let hasVisibleMember = false;

                const finalizeAccountRow = () => {
                    if (!currentAccountRow) return;
                    currentAccountRow.style.display = hasVisibleMember ? '' : 'none';
                };

                rows.forEach((row) => {
                    if (row.classList.contains('family-account-row')) {
                        finalizeAccountRow();
                        currentAccountRow = row;
                        hasVisibleMember = false;
                        row.style.display = '';
                        return;
                    }
                    if (!row.classList.contains('family-member-row')) return;

                    const phoneInput = row.querySelector('td[data-col-id="phone"] input[name="phone"], td[data-col-id="phone"] input[type="text"]');
                    const emailInput = row.querySelector('td[data-col-id="email"] input[name="email"], td[data-col-id="email"] input[type="email"]');
                    const phoneValue = phoneInput?.value?.toLowerCase() ?? '';
                    const emailValue = emailInput?.value?.toLowerCase() ?? '';
                    const phoneValueClean = phoneValue.replace(/[()\s-]+/g, '');
                    const phoneMatch = phoneTerm && phoneValueClean.includes(phoneTerm);
                    const emailMatch = term && emailValue.includes(term);
                    const isMatch = phoneMatch || emailMatch;
                    row.style.display = isMatch ? '' : 'none';
                    if (isMatch) {
                        hasVisibleMember = true;
                    }
                });

                finalizeAccountRow();
            };

            const render = () => {
                reorderContainer(colgroup);
                reorderContainer(headerRow);
                reorderRows();
                applyVisibility();
                applyWidths();
                placeAccountNotes();
                setupResizers();
                applyRowFilters();
                requestAnimationFrame(applyWidths);
            };

            const bindFilterInput = (input) => {
                if (!input) return;
                input.addEventListener('input', () => {
                    applyRowFilters();
                });
            };

            bindFilterInput(filterSearchInput);
            window.addEventListener('beforeunload', () => {
                persistTablePreferences();
            });

            if (memberToggleFields && memberForm && memberFieldControls) {
                const memberFields = Array.from(memberForm.querySelectorAll('[data-member-field]'));
                let hiddenFields = Array.isArray(serverMemberPreferences?.hiddenFields)
                    ? serverMemberPreferences.hiddenFields
                    : [];
                let memberPrefTimeout = null;
                const persistMemberPreferences = async () => {
                    if (!productId) return;
                    try {
                    await fetch(preferenceRoutes.member, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        keepalive: true,
                        body: JSON.stringify({
                            hiddenFields,
                        }),
                    });
                    } catch (error) {
                        console.warn('Unable to save family member prefs', error);
                    }
                };
                const schedulePersistMemberPreferences = () => {
                    if (memberPrefTimeout) {
                        clearTimeout(memberPrefTimeout);
                    }
                    memberPrefTimeout = setTimeout(persistMemberPreferences, 400);
                };

                const applyHidden = () => {
                    memberFields.forEach((label) => {
                        const id = label.dataset.memberField;
                        label.style.display = hiddenFields.includes(id) ? 'none' : '';
                    });
                    const submit = memberForm.querySelector('button[type="submit"]');
                    if (submit) {
                        submit.style.display = hiddenFields.length === memberFields.length ? 'none' : '';
                    }
                };

                const renderMemberControls = () => {
                    memberFieldControls.innerHTML = '';
                    memberFields.forEach((label) => {
                        const id = label.dataset.memberField;
                        if (!id) return;
                        const control = document.createElement('div');
                        control.className = 'family-column-control';
                        const checkbox = document.createElement('input');
                        checkbox.type = 'checkbox';
                        checkbox.checked = !hiddenFields.includes(id);
                        checkbox.addEventListener('change', () => {
                            if (checkbox.checked) {
                                hiddenFields = hiddenFields.filter((val) => val !== id);
                            } else if (!hiddenFields.includes(id)) {
                                hiddenFields.push(id);
                            }
                            schedulePersistMemberPreferences();
                            applyHidden();
                        });
                        const labelEl = document.createElement('span');
                        labelEl.textContent = label.firstChild?.textContent?.trim() || id;
                        control.appendChild(checkbox);
                        control.appendChild(labelEl);
                        memberFieldControls.appendChild(control);
                    });
                };

                memberToggleFields.addEventListener('click', () => {
                    const isOpen = memberFieldControls.style.display === 'flex';
                    memberFieldControls.style.display = isOpen ? 'none' : 'flex';
                    memberToggleFields.textContent = isOpen ? 'Edit fields' : 'Done';
                    if (!isOpen) {
                        renderMemberControls();
                    }
                });

                applyHidden();
            }

            render();
        })();
    </script>
@endpush
