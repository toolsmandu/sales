@extends('layouts.app')

@push('styles')
    @include('partials.dashboard-styles')
    <style>
        .stack {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .sales-filter-row {
            display: flex;
            flex-wrap: nowrap;
            gap: 1.5 rem;
            overflow-x: auto;
            padding-bottom: 0.25rem;
        }

        .sales-filter-row > label,
        .sales-filter-row > .sales-filter-actions {
            flex: 0 0 auto;
            min-width: 140px;
        }

        .sales-filter-row > label {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }

        .sales-filter-row > .product-combobox {
            flex: 0 0 auto;
            min-width: 210px;
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
            align-self: flex-start;
            margin-top: 0;
        }

        .sales-filter-actions  {
            align-items: center;
            gap: 0.5rem;
            margin: 30px;
        }


        .createorderbutton  {
            margin: 20px;
        }

        .sale-confirmation {
            gap: 1.5rem;
        }

        .modal__header-group {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .sale-confirmation-eyebrow {
            font-size: 0.78rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: rgba(79, 70, 229, 0.85);
            font-weight: 600;
        }

        .sale-confirmation-message {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            padding: 1.25rem;
            border-radius: 1rem;
            border: 1px solid rgba(79, 70, 229, 0.2);
            background: radial-gradient(circle at top, rgba(79, 70, 229, 0.12), rgba(79, 70, 229, 0.02)) #fff;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.6), 0 18px 32px rgba(15, 23, 42, 0.12);
        }

        .sale-confirmation-hero {
            display: flex;
            gap: 0.9rem;
            align-items: flex-start;
        }

        .sale-confirmation-hero p {
            margin: 0;
            color: rgba(15, 23, 42, 0.8);
            line-height: 1.5;
        }

        .sale-confirmation-hero__icon {
            width: 3rem;
            height: 3rem;
            border-radius: 1rem;
            background: rgba(16, 185, 129, 0.14);
            color: #059669;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: inset 0 0 0 1px rgba(16, 185, 129, 0.35);
        }

        .sale-confirmation-hero__icon svg {
            width: 1.25rem;
            height: 1.25rem;
        }

        .sale-confirmation-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
        }

        .sale-confirmation-details dt {
            font-size: 0.75rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: rgba(15, 23, 42, 0.55);
            margin-bottom: 0.2rem;
        }

        .sale-confirmation-details dd {
            font-size: 1.1rem;
            font-weight: 600;
            color: rgba(15, 23, 42, 0.9);
            margin: 0;
        }

        .sale-confirmation-note {
            background: rgba(248, 250, 252, 0.9);
            border: 1px dashed rgba(79, 70, 229, 0.35);
            border-radius: 0.85rem;
            padding: 0.85rem 1rem;
            font-size: 0.95rem;
            color: rgba(15, 23, 42, 0.75);
            line-height: 1.5;
        }

        .sale-confirmation-actions {
            display: flex;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .button-with-icon {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-weight: 600;
        }

        .button-with-icon svg {
            width: 1rem;
            height: 1rem;
        }

        .sale-confirmation-close,
        .sale-confirmation-copy {
            border-color: transparent;
            color: #fff;
            text-shadow: 0 1px 1px rgba(15, 23, 42, 0.25);
        }

        .sale-confirmation-close {
            background: linear-gradient(135deg, #ef4444, #b91c1c);
            box-shadow: 0 10px 18px rgba(185, 28, 28, 0.35);
        }

        .sale-confirmation-close:hover,
        .sale-confirmation-close:focus-visible {
            background: linear-gradient(135deg, #f87171, #dc2626);
        }

        .sale-confirmation-copy {
            background: linear-gradient(135deg, #38bdf8, #0284c7);
            box-shadow: 0 10px 18px rgba(14, 165, 233, 0.35);
        }

        .sale-confirmation-copy:hover,
        .sale-confirmation-copy:focus-visible {
            background: linear-gradient(135deg, #7dd3fc, #0ea5e9);
        }

        @media (max-width: 640px) {
            .sale-confirmation {
                gap: 1.1rem;
            }

            .sale-confirmation-hero {
                flex-direction: column;
            }
        }

        .sale-remarks-text {
            background: rgba(15, 23, 42, 0.04);
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 0.65rem;
            padding: 0.9rem 1rem;
            line-height: 1.5;
            white-space: pre-wrap;
        }

        .sale-remarks-meta {
            font-size: 0.9rem;
            color: rgba(15, 23, 42, 0.65);
        }

        .cell-with-action {
            align-items: center;
        }

        .cell-action-button {
            margin-top: 0;
            background: transparent;
        }

        .modal.modal--center {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal.modal--center .modal__content {
            margin: auto;
        }

        .phone-link--clean,
        .whatsapp-link {
            text-decoration: none;
        }

        .phone-link--clean:hover,
        .whatsapp-link:hover {
            text-decoration: underline;
        }

        .orders-card {
            gap: 0;
        }

        .orders-card--create{
            width: 800px;
        }

        .orders-form {
            display: grid;
            gap: 1rem;
        }

        .orders-row {
            display: grid;
            gap: 0.75rem;
            align-items: end;
        }

        .orders-row--primary {
            grid-template-columns: repeat(5, minmax(140px, 1fr));
        }

        .orders-row--secondary {
            grid-template-columns: repeat(3, minmax(200px, 1fr));
        }

        .orders-field label {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }

        .orders-field--product {
            max-width: 260px;
            justify-self: start;
        }

        .orders-field--product .product-combobox {
            width: 100%;
        }

        .orders-field--purchase-date {
            max-width: 180px;
            justify-self: start;
        }

        .orders-field--amount {
            max-width: 125px;
            justify-self: start;
        }

        .paymentmethodchoose {
            min-width: 170px;
            max-width: 200px;
        }

        .orders-field--remarks {
            align-self: center;
        }

        .orders-field--submit {
            align-self: end;
            justify-self: stretch;
        }

        /* Hide number input spinners for amount */
        .orders-field--amount input::-webkit-outer-spin-button,
        .orders-field--amount input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .orders-field--amount input[type="number"] {
            -moz-appearance: textfield;
        }

        .orders-submit-button {
            white-space: nowrap;
        }

        .remarks-toggle {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            margin-left: 8px;
        }

        .remarks-toggle input[type="checkbox"] {
            width: 12px;
            height: 16px;
            min-width: 10px;
        }

        .remarks-toggle-checkbox {
            width: 12px;
            height: 16px;
            min-width: 10px;
        }

        .orders-actions {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            justify-content: flex-end;
        }


        @media (max-width: 960px) {
            .orders-row--primary,
            .orders-row--secondary {
                grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            }

            .orders-field--submit {
                justify-self: stretch;
            }

            .orders-submit-button {
                width: 100%;
            }
        }

        .orders-card__section + .orders-card__section {
            border-top: 1px solid rgba(148, 163, 184, 0.35);
            padding-top: 1.25rem;
            margin-top: 0.5rem;
        }

        .orders-card__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            border-bottom: 1px solid rgba(148, 163, 184, 0.25);
            padding-bottom: 0.75rem;
        }

        .orders-card__eyebrow {
            margin: 0;
            font-size: 0.78rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: rgba(79, 70, 229, 0.8);
            font-weight: 700;
        }

        .orders-card__badge {
            padding: 0.4rem 0.8rem;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.12);
            color: #1d4ed8;
            font-weight: 700;
            border: 1px solid rgba(37, 99, 235, 0.2);
        }

        .orders-card--create {
            --orders-primary-col1: 1fr;
            --orders-primary-col2: 1fr;
            --orders-primary-col3: 1fr;
            --orders-primary-gap: 0;
            --orders-secondary-col1: 1fr;
            --orders-secondary-col2: 0.5fr;
            --orders-secondary-col3: 0.7fr;
            --orders-secondary-gap: 0.5rem;
            background: linear-gradient(180deg, rgba(226, 232, 240, 0.6), rgba(248, 250, 252, 0.95));
            border: 1px solid rgba(148, 163, 184, 0.35);
            box-shadow: 0 14px 32px rgba(15, 23, 42, 0.08);
        }

        .orders-card--list {
            border: 1px solid rgba(148, 163, 184, 0.35);
            background: #fff;
        }

        .orders-card__list-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.35rem;
        }

        .orders-card__actions {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
        }

        .orders-layout-toggle {
            border: 1px solid rgba(79, 70, 229, 0.35);
            background: #fff;
            color: #1d4ed8;
            border-radius: 999px;
            padding: 0.45rem 0.9rem;
            font-weight: 700;
            box-shadow: 0 6px 16px rgba(59, 130, 246, 0.15);
            cursor: pointer;
        }

        .orders-layout-panel {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0.5rem;
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            border: 1px dashed rgba(59, 130, 246, 0.3);
            border-radius: 0.75rem;
            background: rgba(59, 130, 246, 0.04);
        }

        .orders-layout-panel label {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .orders-layout-panel input[type="range"] {
            accent-color: #2563eb;
        }

        .orders-card__actions {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
        }

        .orders-layout-toggle {
            border: 1px solid rgba(79, 70, 229, 0.35);
            background: #fff;
            color: #1d4ed8;
            border-radius: 999px;
            padding: 0.45rem 0.9rem;
            font-weight: 700;
            box-shadow: 0 6px 16px rgba(59, 130, 246, 0.15);
            cursor: pointer;
        }

        .orders-layout-panel {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0.5rem;
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            border: 1px dashed rgba(59, 130, 246, 0.3);
            border-radius: 0.75rem;
            background: rgba(59, 130, 246, 0.04);
        }

        .orders-layout-panel label {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .orders-layout-panel input[type="range"] {
            accent-color: #2563eb;
        }


/* === NEW FIXED LAYOUT (FULL COPY–PASTE) === */

/* 4 equal columns per row on desktop */
.orders-row--primary,
.orders-row--secondary {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 0.75rem;
}

.orders-row--primary {
    grid-template-columns: var(--orders-primary-col1, 1fr) var(--orders-primary-col2, 1fr) var(--orders-primary-col3, 1fr);
    gap: var(--orders-primary-gap, 0);
}

.orders-row--secondary {
    grid-template-columns: var(--orders-secondary-col1, 1fr) var(--orders-secondary-col2, 0.5fr) var(--orders-secondary-col3, 0.7fr);
    gap: var(--orders-secondary-gap, 0.5rem);
}

/* Make every field stretch to fill its column */
.orders-field--purchase-date,
.orders-field--phone,
.orders-field--email,
.orders-field--product,
.orders-field--payment,
.orders-field--remarks,
.orders-field--submit {
    max-width: none !important;
    justify-self: stretch !important;
}

/* Ensure all inputs/selects fill 100% */
.orders-field label > input,
.orders-field label > select,
.orders-field .product-combobox {
    width: 100% !important;
}

/* Remove restrictive widths from earlier code */
.orders-field--purchase-date,
.orders-field--product {
    max-width: none !important;
}

@media (min-width: 961px) {
    .orders-field--purchase-date {
        max-width: 150px !important;
        width: 200px !important;
        justify-self: start !important;
    }

    .orders-field--phone {
        max-width: 250px !important;
        width: 250px !important;
        justify-self: start !important;
    }
    

    .orders-field--amount {
        max-width: 125px !important;
        justify-self: start !important;
    }
}

.paymentmethodchoose {
    min-width: 0 !important;
    max-width: none !important;
    width: 100% !important;
}

/* Make submit button width match others */
.orders-submit-button {
    width: 100% !important;
     
}


/* Responsive layout below 960px */
@media (max-width: 960px) {
    .orders-row--primary,
    .orders-row--secondary {
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    }

    .orders-field--amount {
        max-width: none !important;
        justify-self: stretch !important;
    }

    .orders-field--submit {
        justify-self: stretch !important;
        
    }

    .button orders-submit-button {
        width: 100% !important;
    
    }
}


    </style>
    @include('partials.product-combobox-styles')
@endpush

@section('content')
    <div class="dashboard-grid">
        @include('partials.dashboard-sidebar')

        <section class="dashboard-content stack">

            @if ($errors->any())
                <article role="alert">
                    <strong>There was a problem:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </article>
            @endif

            @php
                $normalizedProductOptions = collect($productOptions ?? [])
                    ->map(function ($option) {
                        if (is_array($option)) {
                            $label = trim((string) ($option['label'] ?? ''));
                            if ($label === '') {
                                return null;
                            }

                            return [
                                'label' => $label,
                                'expiry_days' => isset($option['expiry_days']) && $option['expiry_days'] !== ''
                                    ? (int) $option['expiry_days']
                                    : null,
                            ];
                        }

                        if (is_string($option)) {
                            $label = trim($option);
                            if ($label === '') {
                                return null;
                            }

                            return [
                                'label' => $label,
                                'expiry_days' => null,
                            ];
                        }

                        return null;
                    })
                    ->filter()
                    ->values();

                $productChoices = $normalizedProductOptions
                    ->pluck('label')
                    ->filter(fn ($value) => $value !== '')
                    ->unique()
                    ->values()
                    ->all();

                $ensureOptionPresent = static function ($options, $value, $expiry = null) {
                    $options = collect($options);
                    if ($value && !$options->contains(fn ($option) => $option['label'] === $value)) {
                        $options->prepend([
                            'label' => $value,
                            'expiry_days' => $expiry !== null && $expiry !== '' ? (int) $expiry : null,
                        ]);
                    }

                    return $options->values();
                };
            @endphp

            @if (!$saleToEdit)
                <section class="card stack orders-card orders-card--create">
                    <header class="orders-card__header">
                        <div>
                            <h1>Add Order</h1>
                        </div>
                        <div class="orders-card__actions">
                            <button class="orders-layout-toggle" id="orders-layout-toggle">✍️</button>
                        </div>
                    </header>
                    <div class="orders-layout-panel" id="orders-layout-panel" hidden>
                        <label>
                            Purchase Date Width (%)
                            <input type="range" min="20" max="60" value="33" data-layout-input data-row="primary" data-col="1">
                        </label>
                        <label>
                            Phone Number Width (%)
                            <input type="range" min="20" max="60" value="33" data-layout-input data-row="primary" data-col="2">
                        </label>
                        <label>
                            Email Width (%)
                            <input type="range" min="20" max="60" value="33" data-layout-input data-row="primary" data-col="3">
                        </label>
                        <label>
                            Row Gap (px)
                            <input type="range" min="0" max="24" value="0" data-layout-input data-row="primary" data-gap>
                        </label>
                        <label>
                            Product Width (%)
                            <input type="range" min="20" max="70" value="45" data-layout-input data-row="secondary" data-col="1">
                        </label>
                        <label>
                            Amount Width (%)
                            <input type="range" min="15" max="50" value="25" data-layout-input data-row="secondary" data-col="2">
                        </label>
                        <label>
                            Submit Width (%)
                            <input type="range" min="15" max="50" value="30" data-layout-input data-row="secondary" data-col="3">
                        </label>
                        <label>
                            Second Row Gap (px)
                            <input type="range" min="0" max="24" value="8" data-layout-input data-row="secondary" data-gap>
                        </label>
                    </div>
                    <div class="orders-card__section">
                    <form method="POST" action="{{ route('dashboard.orders.store') }}" class="orders-form">
                        @csrf
                        @php
                            $createPurchaseDate = old('purchase_date', now('Asia/Kathmandu')->toDateString());
                            $createProductValue = old('product_name');
                            $createExpiryValue = old('product_expiry_days');
                            $createOptions = $ensureOptionPresent($normalizedProductOptions, $createProductValue, $createExpiryValue);
                        @endphp
                        <div class="orders-row orders-row--primary">
                            <div class="orders-field orders-field--purchase-date">
                                <label for="sales-purchase-date">
                                    Purchase Date
                                    <input
                                        type="date"
                                        id="sales-purchase-date"
                                        name="purchase_date"
                                        value="{{ $createPurchaseDate }}"
                                        required>
                                </label>
                            </div>

                            <div class="orders-field orders-field--phone">
                                <label for="sales-phone">
                                    Phone Number
                                    <input
                                        type="tel"
                                        id="sales-phone"
                                        name="phone"
                                        value="{{ old('phone') }}"
                                        placeholder="Phone (Required)"
                                        required>
                                </label>
                            </div>

                            <div class="orders-field orders-field--email">
                                <label for="sales-email">
                                    Email
                                    <input
                                        type="email"
                                        id="sales-email"
                                        name="email"
                                        value="{{ old('email') }}"
                                        placeholder="Email address">
                                </label>
                            </div>
                        </div>

                        <div class="orders-row orders-row--secondary">
                            <div class="orders-field orders-field--product">
                                <div
                                    class="product-combobox"
                                    data-product-combobox
                                    data-allow-free-entry="true"
                                    data-expiry-input="create-product-expiry">
                                    <label for="sales-product-name">
                                        Product
                                    <input
                                        type="text"
                                        id="sales-product-name"
                                        class="product-combobox__input"
                                        name="product_name"
                                        value="{{ $createProductValue }}"
                                        placeholder="Choose one (optional)"
                                        autocomplete="off"
                                        data-selected-name="{{ $createProductValue }}"
                                    >
                                        <input
                                            type="hidden"
                                            name="product_expiry_days"
                                            id="create-product-expiry"
                                            value="{{ $createExpiryValue }}"
                                        >
                                    </label>

                                    <div class="product-combobox__dropdown" role="listbox" aria-label="Product options">
                                        @if ($createOptions->isEmpty())
                                            <p class="product-combobox__empty">No products available yet.</p>
                                        @else
                                            <p class="product-combobox__empty" data-empty-message hidden>No matching products found.</p>
                                            @foreach ($createOptions as $option)
                                                @php
                                                    $label = $option['label'];
                                                    $expiryDays = $option['expiry_days'];
                                                    $isSelectedOption = $createProductValue === $label;
                                                @endphp
                                                <button
                                                    type="button"
                                                    class="product-combobox__option {{ $isSelectedOption ? 'is-active' : '' }}"
                                                    data-product-option
                                                    data-product-name="{{ $label }}"
                                                    data-product-id="{{ $label }}"
                                                    data-product-expiry-days="{{ $expiryDays ?? '' }}"
                                                    role="option"
                                                    aria-selected="{{ $isSelectedOption ? 'true' : 'false' }}"
                                                >
                                                    {{ $label }}
                                                </button>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="orders-field orders-field--amount">
                                <label for="sales-amount">
                                    Amount
                                    <input
                                        type="number"
                                        step="1"
                                        min="0"
                                        id="sales-amount"
                                        name="sales_amount"
                                        value="{{ old('sales_amount') !== null ? (int) old('sales_amount') : '' }}"
                                        placeholder="Rs."
                                    >
                                </label>
                            </div>

                            <div class="orders-field orders-field--submit">
                                <div class="orders-actions">
                                    <label class="remarks-toggle" aria-label="Add remarks">
                                        <input
                                            type="checkbox"
                                            class="remarks-toggle-checkbox"
                                            id="sales-remarks-toggle"
                                            {{ old('remarks') ? 'checked' : '' }}
                                            title="Add remarks">
                                    </label>
                                    <input
                                        type="hidden"
                                        id="sales-remarks"
                                        name="remarks"
                                        value="{{ old('remarks') }}"
                                    >
                                    <button type="submit" class="createorder" style="margin:20px;">
                                        Create Order
                                    </button>
                                </div>
                                
                            </div>
                            
                        </div>

                    </form>
                    </div>
                    
                </section>
                <section class="card stack orders-card orders-card--list">
                    <div class="orders-card__section">
                        <div class="orders-card__list-header">
                            <div>
                                <h2>All Orders List</h2>
                            </div>
                        </div>
                        @include('sales.partials.orders-list', [
                            'filters' => $filters ?? [],
                            'productChoices' => $productChoices,
                            'sales' => $sales,
                            'perPage' => $perPage,
                            'admins' => $admins ?? collect(),
                            'employees' => $employees ?? collect(),
                        ])
                    </div>
                </section>
            @else
                <section class="card stack">
                    <div class="flex items-center gap-1">
                        <h2>Edit Sales Record #{{ $saleToEdit->serial_number }}</h2>
                    </div>

                    <form method="POST" action="{{ route('dashboard.orders.update', $saleToEdit) }}" class="form-grid form-grid--compact">
                        @csrf
                        @method('PUT')

                        <label for="edit-sales-purchase-date">
                            Purchase date
                            <input
                                type="date"
                                id="edit-sales-purchase-date"
                                name="purchase_date"
                                value="{{ old('purchase_date', optional($saleToEdit->purchase_date)->format('Y-m-d')) }}"
                                required>
                        </label>

                        @php
                            $editProductValue = old('product_name', $saleToEdit->product_name);
                            $editExpiryValue = old('product_expiry_days', $saleToEdit->product_expiry_days);
                            $editOptions = $ensureOptionPresent($normalizedProductOptions, $editProductValue, $editExpiryValue);
                        @endphp
                        <div class="orders-field orders-field--product">
                            <div
                                class="product-combobox"
                                data-product-combobox
                                data-allow-free-entry="true"
                                data-expiry-input="edit-product-expiry">
                                <label for="edit-sales-product-name">
                                    Product
                                    <input
                                        type="text"
                                        id="edit-sales-product-name"
                                        class="product-combobox__input"
                                        name="product_name"
                                        value="{{ $editProductValue }}"
                                        placeholder="Choose one (Required)"
                                        autocomplete="off"
                                        data-selected-name="{{ $editProductValue }}"
                                        required
                                    >
                                    <input
                                        type="hidden"
                                        name="product_expiry_days"
                                        id="edit-product-expiry"
                                        value="{{ $editExpiryValue }}"
                                    >
                                </label>

                                <div class="product-combobox__dropdown" role="listbox" aria-label="Product options">
                                    @if ($editOptions->isEmpty())
                                        <p class="product-combobox__empty">No products available yet.</p>
                                    @else
                                        <p class="product-combobox__empty" data-empty-message hidden>No matching products found.</p>
                                        @foreach ($editOptions as $option)
                                            @php
                                                $label = $option['label'];
                                                $expiryDays = $option['expiry_days'];
                                                $isSelectedOption = $editProductValue === $label;
                                            @endphp
                                            <button
                                                type="button"
                                                class="product-combobox__option {{ $isSelectedOption ? 'is-active' : '' }}"
                                                data-product-option
                                                data-product-name="{{ $label }}"
                                                data-product-id="{{ $label }}"
                                                data-product-expiry-days="{{ $expiryDays ?? '' }}"
                                                role="option"
                                                aria-selected="{{ $isSelectedOption ? 'true' : 'false' }}"
                                            >
                                                {{ $label }}
                                            </button>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>

                        <label for="edit-sales-remarks">
                            Remarks
                            <input
                                type="text"
                                id="edit-sales-remarks"
                                name="remarks"
                                value="{{ old('remarks', $saleToEdit->remarks) }}"
                                placeholder="Remarks"
                            >
                        </label>

                        <label for="edit-sales-status">
                            Status
                            @php
                                $editStatusValue = old('status', $saleToEdit->status ?? 'completed');
                            @endphp
                            <select id="edit-sales-status" name="status">
                                <option value="completed" @selected($editStatusValue === 'completed')>Completed</option>
                                <option value="refunded" @selected($editStatusValue === 'refunded')>Refunded</option>
                                <option value="cancelled" @selected($editStatusValue === 'cancelled')>Cancelled</option>
                            </select>
                        </label>

                        <label for="edit-sales-phone">
                            Phone
                            <input
                                type="tel"
                                id="edit-sales-phone"
                                name="phone"
                                value="{{ old('phone', $saleToEdit->phone) }}"
                                required>
                        </label>

                        <label for="edit-sales-email">
                            Email
                            <input
                                type="email"
                                id="edit-sales-email"
                                name="email"
                                value="{{ old('email', $saleToEdit->email) }}">
                        </label>

                        

                        <label for="edit-sales-amount">
                            Amount
                            <input
                                type="number"
                                step="1"
                                min="0"
                                id="edit-sales-amount"
                                name="sales_amount"
                                value="{{ old('sales_amount', $saleToEdit->sales_amount !== null ? (int) $saleToEdit->sales_amount : null) }}"
                            >
                        </label>

                        
                        <div class="form-actions form-actions--row">
                            <button type="submit">Update record</button>
                        </div>
                    </form>
                </section>
            @endif

            @if ($saleToEdit)
                <section class="card stack">
                    <h2>All Orders List</h2>
                    @include('sales.partials.orders-list', [
                        'filters' => $filters ?? [],
                        'productChoices' => $productChoices,
                        'sales' => $sales,
                        'perPage' => $perPage,
                        'admins' => $admins ?? collect(),
                        'employees' => $employees ?? collect(),
                    ])
                </section>
            @endif
        </section>
    </div>

    <section class="modal is-hidden" id="sale-remarks-modal" role="dialog" aria-modal="true" aria-labelledby="sale-remarks-title">
        <div class="modal__content">
            <div class="modal__header">
                <div>
                    <h3 id="sale-remarks-title">Sale remarks</h3>
                    <p class="sale-remarks-meta" data-sale-remarks-meta>—</p>
                </div>
                <button type="button" class="ghost-button" data-sale-remarks-close aria-label="Close remarks">
                    Close
                </button>
            </div>
            <div class="sale-remarks-text" data-sale-remarks-body>—</div>
        </div>
    </section>

    <section
        class="modal is-hidden"
        id="duplicate-order-modal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="duplicate-order-title">
        <div class="modal__content">
            <div class="modal__header">
                <div>
                    <h3 id="duplicate-order-title">Duplicate order?</h3>
                    <p class="text-subtle" data-duplicate-message>This will be a duplicate order. Do you want to proceed?</p>
                </div>
                <button type="button" class="ghost-button" data-duplicate-close aria-label="Close duplicate warning">
                    Close
                </button>
            </div>
            <div class="form-actions form-actions--row" style="justify-content: flex-end; gap: 12px;">
                <button type="button" class="ghost-button" data-duplicate-no> No </button>
                <button type="button" class="button-with-icon" data-duplicate-yes>
            
                    <span>Create Duplicate Order</span>
                </button>
            </div>
        </div>
    </section>

    @php
        $saleConfirmation = session('saleConfirmation');
        $orderId = $saleConfirmation['serial_number'] ?? null;
        $productName = $saleConfirmation['product_name'] ?? null;
        $productDisplay = $productName ?: 'N/A';
        $orderPhone = $saleConfirmation['phone'] ?? 'N/A';
        $orderAmount = $saleConfirmation['sales_amount'] ?? null;
        $formattedAmount = $orderAmount !== null ? number_format((float) $orderAmount, 2) : 'N/A';
        $familyStatus = $saleConfirmation['family_status'] ?? null;
        $familyStatusDisplay = match ($familyStatus) {
            'sync_active' => ['text' => 'Sync Active', 'icon' => '✔', 'color' => '#15803d'],
            'error' => ['text' => 'ERROR', 'icon' => '✖', 'color' => '#b91c1c'],
            default => null,
        };
    @endphp

    @if ($saleConfirmation && $orderId)
        <section
            class="modal modal--center"
            id="sale-confirmation-modal"
            role="dialog"
            aria-modal="true"
            aria-labelledby="sale-confirmation-title">
            <div class="modal__content sale-confirmation">
                <div class="modal__header">
                    <div class="modal__header-group">
                        <h3 id="sale-confirmation-title">Order Created Successfully.</h3>
                    </div>
                    <button type="button" class="ghost-button button-with-icon sale-confirmation-close" data-sale-confirmation-close aria-label="Close confirmation">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M18 6L6 18M6 6l12 12" />
                        </svg>
                        <span data-sale-confirmation-close-label>Close</span>
                    </button>
                </div>
                <div class="sale-confirmation-message">
                    <dl class="sale-confirmation-details">
                        <div>
                            <dt>Order ID</dt>
                            <dd>{{ $orderId }}</dd>
                        </div>
                        <div>
                            <dt>User</dt>
                            <dd>{{ $orderPhone }}</dd>
                        </div>
                        <div>
                            <dt>Product</dt>
                            <dd>{{ $productDisplay }}</dd>
                        </div>
                        <div>
                            <dt>Amount</dt>
                            <dd>Rs. {{ $formattedAmount }}</dd>
                        </div>
                        @if ($familyStatusDisplay)
                            <div>
                                <dt>Family Status</dt>
                                <dd>
                                    <span style="color: {{ $familyStatusDisplay['color'] }};">
                                        {{ $familyStatusDisplay['icon'] }} {{ $familyStatusDisplay['text'] }}
                                    </span>
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        </section>
    @endif
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const layoutCard = document.querySelector('.orders-card--create');
            const layoutToggle = document.getElementById('orders-layout-toggle');
            const layoutPanel = document.getElementById('orders-layout-panel');
            const layoutInputs = layoutPanel?.querySelectorAll('[data-layout-input]') || [];
            const primaryRow = document.querySelector('.orders-row--primary');
            const secondaryRow = document.querySelector('.orders-row--secondary');
            const layoutStorageKey = 'ordersLayoutConfig';

            const defaultLayout = {
                primary: { col1: 33, col2: 33, col3: 33, gap: 0 },
                secondary: { col1: 45, col2: 25, col3: 30, gap: 8 },
            };

            const loadLayoutConfig = () => {
                try {
                    const raw = localStorage.getItem(layoutStorageKey);
                    if (!raw) return { ...defaultLayout };
                    const parsed = JSON.parse(raw);
                    return {
                        primary: { ...defaultLayout.primary, ...(parsed.primary || {}) },
                        secondary: { ...defaultLayout.secondary, ...(parsed.secondary || {}) },
                    };
                } catch (_err) {
                    return { ...defaultLayout };
                }
            };

            const persistLayoutConfig = (config) => {
                try {
                    localStorage.setItem(layoutStorageKey, JSON.stringify(config));
                } catch (_err) {
                    /* ignore */
                }
            };

            const applyLayout = () => {
                const rowValues = loadLayoutConfig();

                layoutInputs.forEach((input) => {
                    const rowKey = input.dataset.row || 'primary';
                    const parsed = parseFloat(input.value || '0');
                    if (input.dataset.gap !== undefined) {
                        const gapValue = `${parsed}${input.type === 'range' ? 'px' : ''}`;
                        layoutCard?.style.setProperty(`--orders-${rowKey}-gap`, gapValue);
                        rowValues[rowKey].gap = parsed;
                    } else if (input.dataset.col) {
                        const track = `${parsed}%`;
                        layoutCard?.style.setProperty(`--orders-${rowKey}-col${input.dataset.col}`, track);
                        rowValues[rowKey][`col${input.dataset.col}`] = parsed;
                    }
                });

                if (primaryRow) {
                    primaryRow.style.gridTemplateColumns = `${rowValues.primary.col1}% ${rowValues.primary.col2}% ${rowValues.primary.col3}%`;
                    primaryRow.style.gap = `${rowValues.primary.gap}px`;
                }

                if (secondaryRow) {
                    secondaryRow.style.gridTemplateColumns = `${rowValues.secondary.col1}% ${rowValues.secondary.col2}% ${rowValues.secondary.col3}%`;
                    secondaryRow.style.gap = `${rowValues.secondary.gap}px`;
                }

                persistLayoutConfig(rowValues);
            };

            const hydrateInputs = () => {
                const config = loadLayoutConfig();
                layoutInputs.forEach((input) => {
                    const rowKey = input.dataset.row || 'primary';
                    if (input.dataset.gap !== undefined) {
                        input.value = config[rowKey]?.gap ?? 0;
                    } else if (input.dataset.col) {
                        input.value = config[rowKey]?.[`col${input.dataset.col}`] ?? 0;
                    }
                });
            };

            if (layoutToggle && layoutPanel && layoutCard) {
                hydrateInputs();
                applyLayout();

                layoutToggle.addEventListener('click', () => {
                    const editing = layoutCard.classList.toggle('orders-card--editing');
                    layoutPanel.hidden = !editing;
                    layoutToggle.textContent = editing ? 'Close Edit' : 'Edit Layout';
                    if (editing) {
                        applyLayout();
                    }
                });

                layoutInputs.forEach((input) => {
                    input.addEventListener('input', applyLayout);
                });
            }

            const addPhoneInput = document.getElementById('sales-phone');
            const addEmailInput = document.getElementById('sales-email');
            const addEmailDefaultPlaceholder = addEmailInput?.getAttribute('placeholder') || 'Email address';
            const emailLookupUrl = '{{ route('orders.lookup-email') }}';
            const remarksToggle = document.getElementById('sales-remarks-toggle');
            const remarksInput = document.getElementById('sales-remarks');
            const normalizePhone = (value) => (value || '').replace(/\D+/g, '');
            const collectPhoneEmailMap = () => {
                const map = new Map();
                document.querySelectorAll('#orders-table tbody tr').forEach((row) => {
                    const phone = normalizePhone(row.dataset.phone || '');
                    const email = (row.dataset.email || '').trim();
                    if (phone && email && !map.has(phone)) {
                        map.set(phone, email);
                    }
                });
                return map;
            };
            const phoneEmailMap = collectPhoneEmailMap();
            let emailLookupTimer = null;
            let emailLookupToken = 0;
            const autofillEmail = async () => {
                if (!addPhoneInput || !addEmailInput) return;
                const digits = normalizePhone(addPhoneInput.value || '');
                addEmailInput.setAttribute('placeholder', addEmailDefaultPlaceholder);
                if (!digits) return;
                const isEmailEmpty = addEmailInput.value.trim() === '';
                if (!isEmailEmpty) return;
                const foundEmail = phoneEmailMap.get(digits);
                if (foundEmail && isEmailEmpty) {
                    addEmailInput.value = foundEmail;
                    addEmailInput.dispatchEvent(new Event('input', { bubbles: true }));
                    addEmailInput.setAttribute('placeholder', addEmailDefaultPlaceholder);
                    return;
                }
                const lookupToken = ++emailLookupToken;
                try {
                    const response = await fetch(`${emailLookupUrl}?phone=${encodeURIComponent(digits)}`, {
                        headers: {
                            'Accept': 'application/json',
                        },
                    });
                    if (!response.ok) {
                        throw new Error('Lookup failed');
                    }
                    const payload = await response.json();
                    if (lookupToken !== emailLookupToken) return;
                    if (normalizePhone(addPhoneInput.value || '') !== digits) return;
                    const email = typeof payload?.email === 'string' ? payload.email.trim() : '';
                    if (email && addEmailInput.value.trim() === '') {
                        addEmailInput.value = email;
                        addEmailInput.dispatchEvent(new Event('input', { bubbles: true }));
                        addEmailInput.setAttribute('placeholder', addEmailDefaultPlaceholder);
                    } else if (addEmailInput.value.trim() === '') {
                        addEmailInput.setAttribute('placeholder', 'No Matching Records');
                    }
                } catch (error) {
                    if (lookupToken !== emailLookupToken) return;
                    if (normalizePhone(addPhoneInput.value || '') !== digits) return;
                    if (addEmailInput.value.trim() === '') {
                        addEmailInput.setAttribute('placeholder', 'No Matching Records');
                    }
                }
            };
            const scheduleAutofill = () => {
                if (emailLookupTimer) {
                    window.clearTimeout(emailLookupTimer);
                }
                emailLookupTimer = window.setTimeout(() => {
                    void autofillEmail();
                }, 250);
            };
            addPhoneInput?.addEventListener('input', scheduleAutofill);
            addPhoneInput?.addEventListener('blur', () => {
                void autofillEmail();
            });
            addPhoneInput?.addEventListener('change', () => {
                void autofillEmail();
            });
            addPhoneInput?.addEventListener('keyup', (event) => {
                if (event.key === 'Enter') {
                    void autofillEmail();
                }
            });

            const handleRemarksToggle = () => {
                if (!remarksToggle || !remarksInput) return;
                if (remarksToggle.checked) {
                    const value = window.prompt('Enter remarks') ?? '';
                    const trimmed = value.trim();
                    if (trimmed === '') {
                        remarksToggle.checked = false;
                        remarksInput.value = '';
                    } else {
                        remarksInput.value = trimmed;
                    }
                } else {
                    remarksInput.value = '';
                }
            };
            remarksToggle?.addEventListener('change', handleRemarksToggle);
            const remarksModal = document.getElementById('sale-remarks-modal');

            if (remarksModal) {
                const modalBody = remarksModal.querySelector('[data-sale-remarks-body]');
                const modalMeta = remarksModal.querySelector('[data-sale-remarks-meta]');
                const closeButton = remarksModal.querySelector('[data-sale-remarks-close]');
                let lastFocusedElement = null;

                const closeModal = () => {
                    if (remarksModal.classList.contains('is-hidden')) {
                        return;
                    }

                    remarksModal.classList.add('is-hidden');
                    if (lastFocusedElement) {
                        lastFocusedElement.focus({ preventScroll: true });
                    }
                };

                const openModal = ({ remarks, saleNumber, product, poster }) => {
                    const cleanRemarks = (remarks ?? '').trim();
                    const saleLabel = saleNumber ? `Sale #${saleNumber}` : 'Remarks for this Sale';
                    const productLabel = (product ?? '').trim();
                    const posterLabel = (poster ?? '').trim();

                    if (modalBody) {
                        modalBody.textContent = cleanRemarks === ''
                            ? 'No remarks provided for this sale.'
                            : cleanRemarks;
                    }

                    if (modalMeta) {
                        const metaParts = [saleLabel];
                        if (productLabel !== '') {
                            metaParts.push(productLabel);
                        }
                        if (posterLabel !== '') {
                            metaParts.push(`Posted by: ${posterLabel}`);
                        }
                        modalMeta.textContent = metaParts.join(' · ');
                    }

                    lastFocusedElement = document.activeElement instanceof HTMLElement
                        ? document.activeElement
                        : null;

                    remarksModal.classList.remove('is-hidden');
                    closeButton?.focus({ preventScroll: true });
                };

                document.querySelectorAll('[data-sale-remarks]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const remarks = button.dataset.saleRemarks ?? '';
                        const saleNumber = button.dataset.saleNumber ?? '';
                        const product = button.dataset.saleProduct ?? '';
                        const poster = button.dataset.salePoster ?? '';
                        openModal({ remarks, saleNumber, product, poster });
                    });
                });

                closeButton?.addEventListener('click', closeModal);

                remarksModal.addEventListener('click', (event) => {
                    if (event.target === remarksModal) {
                        closeModal();
                    }
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && !remarksModal.classList.contains('is-hidden')) {
                        closeModal();
                    }
                });
            }

            const duplicateModal = document.getElementById('duplicate-order-modal');
            const duplicateMessage = duplicateModal?.querySelector('[data-duplicate-message]');
            const duplicateYesButton = duplicateModal?.querySelector('[data-duplicate-yes]');
            const duplicateNoButton = duplicateModal?.querySelector('[data-duplicate-no]');
            const duplicateCloseButton = duplicateModal?.querySelector('[data-duplicate-close]');
            const createOrderForm = document.querySelector('.orders-form');
            const createProductInput = document.getElementById('sales-product-name');
            const createPurchaseDateInput = document.getElementById('sales-purchase-date');
            const duplicateCheckUrl = '{{ route('dashboard.orders.check-duplicate') }}';
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            let pendingDuplicateSubmit = null;

            const closeDuplicateModal = () => {
                if (!duplicateModal) return;
                duplicateModal.classList.add('is-hidden');
                pendingDuplicateSubmit = null;
            };

            const openDuplicateModal = ({ phone, product }) => {
                if (!duplicateModal) return;
                const detailParts = [];
                const cleanProduct = (product ?? '').trim();
                const cleanPhone = (phone ?? '').trim();
                if (cleanProduct !== '') detailParts.push(cleanProduct);
                if (cleanPhone !== '') detailParts.push(cleanPhone);
                if (duplicateMessage) {
                    const details = detailParts.length > 0 ? ` for ${detailParts.join(' · ')}` : '';
                    duplicateMessage.textContent = `A similar order was placed within the last 48 hours${details}. Do you want to proceed?`;
                }
                duplicateModal.classList.remove('is-hidden');
                duplicateYesButton?.focus({ preventScroll: true });
            };

            duplicateYesButton?.addEventListener('click', () => {
                if (pendingDuplicateSubmit) {
                    pendingDuplicateSubmit();
                }
                closeDuplicateModal();
            });
            duplicateNoButton?.addEventListener('click', closeDuplicateModal);
            duplicateCloseButton?.addEventListener('click', closeDuplicateModal);
            duplicateModal?.addEventListener('click', (event) => {
                if (event.target === duplicateModal) {
                    closeDuplicateModal();
                }
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && duplicateModal && !duplicateModal.classList.contains('is-hidden')) {
                    closeDuplicateModal();
                }
            });

            createOrderForm?.addEventListener('submit', async (event) => {
                if (!duplicateModal || !addPhoneInput || !createProductInput || !duplicateCheckUrl) {
                    return;
                }

                const phoneValue = addPhoneInput.value.trim();
                const productValue = createProductInput.value.trim();

                if (phoneValue === '' || productValue === '') {
                    return;
                }

                event.preventDefault();

                try {
                    const response = await fetch(duplicateCheckUrl, {
                        method: 'POST',
                        headers: {
                            Accept: 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({
                            phone: phoneValue,
                            product_name: productValue,
                            purchase_date: createPurchaseDateInput?.value ?? null,
                        }),
                    });

                    if (!response.ok) {
                        createOrderForm.submit();
                        return;
                    }

                    const payload = await response.json();
                    const isDuplicate = Boolean(payload?.duplicate);

                    if (!isDuplicate) {
                        createOrderForm.submit();
                        return;
                    }

                    pendingDuplicateSubmit = () => createOrderForm.submit();
                    openDuplicateModal({ phone: phoneValue, product: productValue });
                } catch (_error) {
                    createOrderForm.submit();
                }
            });

            const confirmationModal = document.getElementById('sale-confirmation-modal');
            if (confirmationModal) {
                const closeButtons = confirmationModal.querySelectorAll('[data-sale-confirmation-close]');
                const closeLabel = confirmationModal.querySelector('[data-sale-confirmation-close-label]');
                let confirmationCountdownId = null;
                let confirmationTimeoutId = null;

                const resetCloseLabel = () => {
                    if (closeLabel) {
                        closeLabel.textContent = 'Close';
                    }
                };

                const startAutoClose = () => {
                    if (confirmationCountdownId) {
                        window.clearInterval(confirmationCountdownId);
                        confirmationCountdownId = null;
                    }
                    if (confirmationTimeoutId) {
                        window.clearTimeout(confirmationTimeoutId);
                        confirmationTimeoutId = null;
                    }

                    let remaining = 5;
                    if (closeLabel) {
                        closeLabel.textContent = `Close (${remaining}s)`;
                    }

                    confirmationCountdownId = window.setInterval(() => {
                        remaining -= 1;
                        if (remaining <= 0) {
                            hideConfirmation();
                        } else if (closeLabel) {
                            closeLabel.textContent = `Close (${remaining}s)`;
                        }
                    }, 1000);

                    confirmationTimeoutId = window.setTimeout(() => {
                        hideConfirmation();
                    }, remaining * 1000);
                };

                const hideConfirmation = () => {
                    if (confirmationCountdownId) {
                        window.clearInterval(confirmationCountdownId);
                        confirmationCountdownId = null;
                    }
                    if (confirmationTimeoutId) {
                        window.clearTimeout(confirmationTimeoutId);
                        confirmationTimeoutId = null;
                    }
                    confirmationModal.classList.add('is-hidden');
                    confirmationModal.style.display = 'none';
                    confirmationModal.setAttribute('aria-hidden', 'true');
                    resetCloseLabel();
                };

                closeButtons.forEach((button) => button.addEventListener('click', hideConfirmation));

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && !confirmationModal.classList.contains('is-hidden')) {
                        hideConfirmation();
                    }
                });

                confirmationModal.classList.remove('is-hidden');
                confirmationModal.style.display = '';
                confirmationModal.setAttribute('aria-hidden', 'false');
                startAutoClose();
            }
        });
    </script>
    @include('partials.product-combobox-scripts')
@endpush
