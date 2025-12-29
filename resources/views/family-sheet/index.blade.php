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
        }



        .family-table-wrapper {
            overflow: auto;
            border: 1px solid rgba(15, 23, 42, 0.1);
            border-radius: 0.75rem;
            width: 100%;
        }
        table.family-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 960px;
        }
        table.family-table th,
        table.family-table td {
            border: 1px solid rgba(148, 163, 184, 0.35);
            padding: 0.55rem 0.65rem;
            text-align: center;
            background: #fff;
        }
        table.family-table th {
            position: relative;
        }
        table.family-table thead th {
            background: linear-gradient(180deg, rgba(226, 232, 240, 0.9), rgba(241, 245, 249, 0.9));
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.02em;
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
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1rem;
            align-items: start;
        }

        .create-column {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .create-card form:not(.family-form-inline--inline-action) button[type="submit"] {
            width: 100%;
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
                    <strong>Show Product Creating sections:</strong>
                    <label style="display: inline-flex; align-items: center; gap: 0.35rem; margin: 0;">
                        <input type="checkbox" id="toggle-create-sections">
                    </label>
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
                        <div class="family-card create-card">
                            <h3>Add Admin Account</h3>
                            <form method="POST" action="{{ route('family-sheet.accounts.store') }}">
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
                                Remarks
                                <input type="text" name="remarks" placeholder="Remarks">
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
            </div>
<br>

            <div class="family-card family-card--full">
                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        const toggle = document.getElementById('toggle-create-sections');
                        const wrapper = document.getElementById('create-sections-wrapper');
                        const linkCard = document.getElementById('family-link-card');
                        if (!toggle || !wrapper) return;
                        const apply = () => {
                            const visible = toggle.checked;
                            wrapper.style.display = visible ? '' : 'none';
                            if (linkCard) {
                                linkCard.style.display = visible ? '' : 'none';
                            }
                        };
                        toggle.addEventListener('change', apply);
                        apply();
                    });
                </script>
                @if ($products->count())
                    <div class="family-inline-row" style="align-items: center; justify-content: flex-start; margin-bottom: 0.5rem;">
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
                    </div>
                @endif

                <div class="family-inline-row" style="align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                    <h3 style="margin: 0;">Add Member</h3>
                    <div class="family-inline-row" style="gap: 0.75rem; align-items: center;">
                        <label style="display: inline-flex; gap: 0.35rem; align-items: center; margin: 0;">
                            <input type="checkbox" id="family-show-all-accounts">
                            <span>Show all accounts</span>
                        </label>
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
                                <option value="{{ $account->id }}">
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
                        <input type="date" name="purchase_date">
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
                    <label data-member-field="two_factor">
                        Two Factor
                        <input type="text" name="two_factor">
                    </label>
                    <button type="submit">Add Member</button>
                </form>

                <div class="family-table-wrapper" id="family-accounts-table-wrapper" data-default-open="{{ old('account_edit') ? 'true' : 'false' }}" style="margin-top: 1rem; display: none;">
                    @if ($accounts->count())
                        <table class="family-table" style="min-width: 720px;">
                            <thead>
                                <tr>
                                    <th>Main Account</th>
                                    <th>Product</th>
                                    <th>Index</th>
                                    <th>Max members</th>
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
                                            {{ $account->member_count }}/{{ $account->capacity ?? '∞' }} used
                                        </td>
                                        <td>
                                            <input type="text" name="remarks" form="family-account-update-{{ $account->id }}" value="{{ old('remarks', $account->remarks) }}" style="width: 100%;">
                                        </td>
                                        <td style="display: flex; gap: 0.35rem; align-items: center; justify-content: center;">
                                            <button type="submit" form="family-account-update-{{ $account->id }}" class="ghost-button ghost-button--compact">Save</button>
                                            <button type="submit" form="family-account-delete-{{ $account->id }}" class="ghost-button ghost-button--compact" style="color: #b91c1c;" onclick="return confirm('Delete this main account and all its members?');">Delete</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="helper-text" style="padding: 0.75rem;">No main accounts yet.</p>
                    @endif
                </div>
            </div>

@if ($selectedProduct)
                <div class="family-inline-row" style="margin-top: 1rem;">
                    <h3 style="margin: 0;">Data Records of: {{ $selectedProduct->name }}</h3>
                    <div class="family-inline-filters">
                        <label style="display: inline-flex; gap: 0.35rem; align-items: center; margin: 0;">
                            <input type="search" id="family-filter-phone" placeholder="Search phone">
                        </label>
                        <label style="display: inline-flex; gap: 0.35rem; align-items: center; margin: 0;">
                            <input type="search" id="family-filter-email" placeholder="Search email">
                        </label>
                    </div>
                </div>
                <div style="margin-bottom: 0.5rem;">
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
                            @forelse ($accounts as $account)
                                @php
                                    $members = $membersByAccount->get($account->id) ?? collect();
                                @endphp
                                <tr style="background: #f8fafc; font-weight: 700;" class="family-account-row">
                                    <td colspan="{{ count($familyColumns) }}" style="text-align: left; padding: 0.75rem 1rem;">
                                        <strong>{{ $account->name }}</strong>
                                        @if(!empty($account->account_index))
                                            <strong>(Index: {{ $account->account_index }})</strong>
                                        @endif
                                        <strong>({{ $account->member_count }}/{{ $account->capacity ?? '∞' }} used)</strong>
                                        @if(!empty($account->remarks))
                                            — <strong>{{ $account->remarks }}</strong>
                                        @endif
                                    </td>
                                </tr>
                                @forelse ($members as $member)
                                    @php
                                        $purchaseAt = $member->purchase_date ? \Illuminate\Support\Carbon::parse($member->purchase_date) : null;
                                    @endphp
                                    <tr class="family-member-row">
                                        <td data-col-id="account" style="text-align: left;">
                                            @if ($loop->first)
                                                <strong>{{ $account->name }}</strong>
                                                @if(!empty($account->account_index))
                                                    <strong>(Index: {{ $account->account_index }})</strong>
                                                @endif
                                                <strong>({{ $account->member_count }}/{{ $account->capacity ?? '∞' }} used)</strong>
                                                @if(!empty($account->remarks))
                                                    — <strong>{{ $account->remarks }}</strong>
                                                @endif
                                            @endif
                                        </td>
                                        <td data-col-id="family_name">
                                            <input type="text" name="family_name" form="family-member-{{ $member->id }}" value="{{ $member->family_name ?? $account->name }}" style="width: 100%;">
                                        </td>
                                        <td data-col-id="order">
                                            <input type="text" name="order_id" form="family-member-{{ $member->id }}" value="{{ $member->order_id }}" style="width: 100%;">
                                        </td>
                                        <td data-col-id="email">
                                            <form method="POST" action="{{ route('family-sheet.members.update', $member->id) }}" id="family-member-{{ $member->id }}">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="family_account_id" value="{{ $member->family_account_id }}">
                                                <input type="email" name="email" value="{{ $member->email }}" style="width: 100%;">
                                        </td>
                                        <td data-col-id="phone">
                                            <input type="text" name="phone" form="family-member-{{ $member->id }}" value="{{ $member->phone }}" style="width: 100%;">
                                        </td>
                                        <td data-col-id="product">
                                            <input type="text" name="product" form="family-member-{{ $member->id }}" value="{{ $member->product }}" style="width: 100%;">
                                        </td>
                                        <td data-col-id="amount">
                                            <input type="number" name="sales_amount" form="family-member-{{ $member->id }}" value="{{ $member->sales_amount }}" style="width: 100%;">
                                        </td>
                                        <td data-col-id="purchase">
                                            <input type="date" name="purchase_date" form="family-member-{{ $member->id }}" value="{{ $purchaseAt ? $purchaseAt->format('Y-m-d') : '' }}" style="width: 100%;">
                                        </td>
                                        <td data-col-id="period">
                                            <input type="number" name="expiry" form="family-member-{{ $member->id }}" value="{{ $member->expiry }}" style="width: 100%;">
                                        </td>
                                        <td data-col-id="remaining">
                                            <input type="number" name="remaining_days" form="family-member-{{ $member->id }}" value="{{ $member->remaining_days }}" style="width: 100%;">
                                        </td>
                                        <td data-col-id="remarks">
                                            <div style="display: flex; gap: 0.35rem; align-items: center;">
                                                <input type="text" name="remarks" form="family-member-{{ $member->id }}" value="{{ $member->remarks }}" style="width: 100%;">
                                                <button type="submit" form="family-member-{{ $member->id }}" class="ghost-button ghost-button--compact">Save</button>
                                            </div>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td data-col-id="account"></td>
                                        <td data-col-id="email" colspan="{{ count($familyColumns) - 1 }}"><p class="helper-text">No members in this main account.</p></td>
                                    </tr>
                                @endforelse
                            @empty
                                <tr>
                                    <td colspan="{{ count($familyColumns) }}"><p class="helper-text">No main accounts yet.</p></td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div style="display:flex; justify-content:center; margin-top:0.75rem;">
                    <button type="button" id="family-show-more" class="ghost-button" style="display:none;">Show more</button>
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
            const filterPhoneInput = document.getElementById('family-filter-phone');
            const filterEmailInput = document.getElementById('family-filter-email');
            const memberToggleFields = document.getElementById('family-member-toggle-fields');
            const memberFieldControls = document.getElementById('family-member-field-controls');
            const memberForm = document.getElementById('family-add-member-form');
            const createSectionsToggle = document.getElementById('toggle-create-sections');
            const createSectionsWrapper = document.getElementById('create-sections-wrapper');
            const showAllAccountsToggle = document.getElementById('family-show-all-accounts');
            const accountsTableWrapper = document.getElementById('family-accounts-table-wrapper');
            const linkCard = document.getElementById('family-link-card');
            const linkSiteProduct = document.getElementById('family-link-site-product');
            const linkVariations = document.getElementById('family-link-variations');
            const linkRows = Array.from(document.querySelectorAll('.family-link-row'));
            const linkOverview = document.getElementById('family-link-overview');
            const linkEditToggle = document.getElementById('family-link-edit-toggle');
            const hasTable = table && colgroup && headerRow && tbody;
            const memberShowMore = document.getElementById('family-show-more');

            if (createSectionsToggle) {
                const applyCreateVisibility = () => {
                    const visible = createSectionsToggle.checked;
                    if (createSectionsWrapper) {
                        createSectionsWrapper.style.display = visible ? '' : 'none';
                    }
                    if (linkCard) {
                        linkCard.style.display = visible ? '' : 'none';
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

            if (memberToggleFields && memberForm && memberFieldControls) {
                const memberFields = Array.from(memberForm.querySelectorAll('[data-member-field]'));
                const hiddenKey = 'family_member_hidden_fields';
                let controlsOpen = false;

                const loadHidden = () => {
                    try {
                        const raw = localStorage.getItem(hiddenKey);
                        if (!raw) return [];
                        const parsed = JSON.parse(raw);
                        return Array.isArray(parsed) ? parsed : [];
                    } catch {
                        return [];
                    }
                };

                const saveHidden = (list) => {
                    try {
                        localStorage.setItem(hiddenKey, JSON.stringify(list));
                    } catch {
                        /* ignore */
                    }
                };

                let hiddenFields = loadHidden();

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
                            saveHidden(hiddenFields);
                            applyHidden();
                        });
                        const labelEl = document.createElement('span');
                        const text = label.querySelector('input, select, textarea')?.closest('label')?.childNodes?.[0]?.textContent?.trim() || label.textContent?.trim() || id;
                        labelEl.textContent = text || id;
                        control.appendChild(checkbox);
                        control.appendChild(labelEl);
                        memberFieldControls.appendChild(control);
                    });
                };

                memberToggleFields.addEventListener('click', () => {
                    controlsOpen = !controlsOpen;
                    memberFieldControls.style.display = controlsOpen ? 'flex' : 'none';
                    memberToggleFields.textContent = controlsOpen ? 'Done' : 'Edit fields';
                    if (controlsOpen && !memberFieldControls.childElementCount) {
                        renderMemberControls();
                    }
                });

                applyHidden();
            }

            if (!hasTable) return;

            const memberRows = Array.from(document.querySelectorAll('.family-member-row'));
            let memberVisibleLimit = 50;
            const applyMemberVisibility = () => {
                memberRows.forEach((row, index) => {
                    row.style.display = index < memberVisibleLimit ? '' : 'none';
                });
                if (memberShowMore) {
                    memberShowMore.style.display = memberRows.length > memberVisibleLimit ? '' : 'none';
                }
            };
            if (memberShowMore) {
                memberShowMore.addEventListener('click', () => {
                    memberVisibleLimit += 50;
                    applyMemberVisibility();
                });
            }
            applyMemberVisibility();

            const columns = [
                { id: 'account', label: 'Main Account' },
                { id: 'order', label: 'Order ID' },
                { id: 'email', label: 'Email' },
                { id: 'phone', label: 'Phone' },
                { id: 'product', label: 'Product' },
                { id: 'amount', label: 'Amount' },
                { id: 'purchase', label: 'Purchase Date' },
                { id: 'period', label: 'Period' },
                { id: 'remaining', label: 'Remaining Days' },
                { id: 'remarks', label: 'Remarks' },
            ];

            const defaultOrder = columns.map((c) => c.id);
            const productId = table?.dataset.productId || 'default';
            const prefsVersion = 'v2';
            const prefsKey = `family_table_prefs_${prefsVersion}_${productId}`;
            const widthKey = `family_table_widths_${prefsVersion}_${productId}`;

            const loadPrefs = () => {
                try {
                    const raw = localStorage.getItem(prefsKey);
                    if (!raw) return {};
                    const parsed = JSON.parse(raw);
                    return parsed && typeof parsed === 'object' ? parsed : {};
                } catch (error) {
                    console.warn('Unable to read family prefs', error);
                    return {};
                }
            };

            const loadWidths = () => {
                try {
                    const raw = localStorage.getItem(widthKey);
                    if (!raw) return {};
                    const parsed = JSON.parse(raw);
                    return parsed && typeof parsed === 'object' ? parsed : {};
                } catch (error) {
                    console.warn('Unable to read family column widths', error);
                    return {};
                }
            };

            const sanitizeOrder = (order) => {
                const base = Array.isArray(order) ? order.filter((id) => defaultOrder.includes(id)) : [];
                const missing = defaultOrder.filter((id) => !base.includes(id));
                return [...base, ...missing];
            };

            const prefs = loadPrefs();
            const state = {
                order: sanitizeOrder(prefs.order),
                hidden: Array.isArray(prefs.hidden) ? prefs.hidden.filter((id) => defaultOrder.includes(id)) : [],
                widths: loadWidths(),
            };

            const persistPrefs = () => {
                try {
                    localStorage.setItem(prefsKey, JSON.stringify({
                        order: state.order,
                        hidden: state.hidden,
                    }));
                } catch (error) {
                    console.warn('Unable to save family prefs', error);
                }
            };

            const persistWidths = () => {
                try {
                    localStorage.setItem(widthKey, JSON.stringify(state.widths));
                } catch (error) {
                    console.warn('Unable to save family widths', error);
                }
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
                state.order.forEach((id) => {
                    const width = state.widths[id];
                    const col = colgroup.querySelector(`col[data-col-id="${id}"]`);
                    const header = headerRow.querySelector(`th[data-col-id="${id}"]`);
                    const visible = !state.hidden.includes(id);
                    if (col) {
                        col.style.width = width && visible ? `${width}px` : '';
                    }
                    if (header) {
                        header.style.width = width && visible ? `${width}px` : '';
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
                    persistWidths();
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
                persistPrefs();
            };

            const toggleColumn = (columnId, visible) => {
                if (visible) {
                    state.hidden = state.hidden.filter((id) => id !== columnId);
                } else if (!state.hidden.includes(columnId)) {
                    state.hidden.push(columnId);
                }
                render();
                persistPrefs();
            };

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
                const phoneTerm = (filterPhoneInput?.value || '').trim().toLowerCase();
                const emailTerm = (filterEmailInput?.value || '').trim().toLowerCase();
                const hasPhoneTerm = phoneTerm.length > 0;
                const hasEmailTerm = emailTerm.length > 0;
                const filtersActive = hasPhoneTerm || hasEmailTerm;
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
                    const phoneMatch = hasPhoneTerm && phoneValue.includes(phoneTerm);
                    const emailMatch = hasEmailTerm && emailValue.includes(emailTerm);
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
            };

            const bindFilterInput = (input) => {
                if (!input) return;
                input.addEventListener('input', applyRowFilters);
            };

            bindFilterInput(filterPhoneInput);
            bindFilterInput(filterEmailInput);

            if (memberToggleFields && memberForm && memberFieldControls) {
                const memberFields = Array.from(memberForm.querySelectorAll('[data-member-field]'));
                const hiddenKey = 'family_member_hidden_fields';

                const loadHidden = () => {
                    try {
                        const raw = localStorage.getItem(hiddenKey);
                        if (!raw) return [];
                        const parsed = JSON.parse(raw);
                        return Array.isArray(parsed) ? parsed : [];
                    } catch {
                        return [];
                    }
                };

                const saveHidden = (list) => {
                    try {
                        localStorage.setItem(hiddenKey, JSON.stringify(list));
                    } catch {
                        /* ignore */
                    }
                };

                let hiddenFields = loadHidden();

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
                            saveHidden(hiddenFields);
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
