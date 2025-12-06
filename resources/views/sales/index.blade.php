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
            gap: 0.85rem;
            overflow-x: auto;
            padding-bottom: 0.25rem;
        }

        .sales-filter-row > label,
        .sales-filter-row > .sales-filter-actions {
            flex: 0 0 auto;
            min-width: 200px;
        }

        .sales-filter-row > label {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }

        .sales-filter-row > .product-combobox {
            flex: 0 0 auto;
            min-width: 210px;
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
            grid-template-columns: repeat(4, minmax(150px, 1fr));
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
            max-width: 160px;
            justify-self: start;
        }

        .paymentmethodchoose {
            min-width: 170px;
            max-width: 200px;
        }

        .orders-field--remarks input {
            min-width: 220px;
        }

        .orders-field--submit {
            align-self: end;
            justify-self: start;
        }

        .orders-submit-button {
            white-space: nowrap;
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


/* === NEW FIXED LAYOUT (FULL COPY–PASTE) === */

/* 4 equal columns per row on desktop */
.orders-row--primary,
.orders-row--secondary {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 0.75rem;
}

/* Make every field stretch to fill its column */
.orders-field--purchase-date,
.orders-field--phone,
.orders-field--email,
.orders-field--product,
.orders-field--payment,
.orders-field--amount,
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
.orders-field--product,
.orders-field--amount {
    max-width: none !important;
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

            @if (session('status'))
                <article role="alert">
                    {{ session('status') }}
                </article>
            @endif

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
                <section class="card stack orders-card">
                    <div class="orders-card__section">
                    <h2>Add Order</h2><br>
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
                                            placeholder="Choose one (Required)"
                                            autocomplete="off"
                                            data-selected-name="{{ $createProductValue }}"
                                            required
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
    
    <div class="orders-field orders-field--payment">
        <label for="sales-payment-method">
            Payment Method
            <select id="sales-payment-method" class="paymentmethodchoose" name="payment_method">
                <option value="" {{ old('payment_method') ? '' : 'selected' }}>Choose one</option>
                @foreach ($paymentMethods as $method)
                    <option value="{{ $method->slug }}" @selected(old('payment_method') === $method->slug)>
                        {{ $method->label }}
                    </option>
                @endforeach
            </select>
        </label>
    </div>

    <!-- Amount MOVED HERE -->
    <div class="orders-field orders-field--amount">
        <label for="sales-amount">
            Amount
            <input
                type="number"
                step="0.01"
                min="0"
                id="sales-amount"
                name="sales_amount"
                value="{{ old('sales_amount') }}"
                placeholder="Rs."
            >
        </label>
    </div>

    <div class="orders-field orders-field--remarks">
        <label for="sales-remarks">
            Remarks
            <input
                type="text"
                id="sales-remarks"
                name="remarks"
                value="{{ old('remarks') }}"
                placeholder="Remarks"
                maxlength="255">
        </label>
    </div>

    <div class="createorderbutton">
        <button type="submit" class="createorderbutton">Create Order</button>
    </div>
</div>

                    </form>
                    </div>
                    <div class="orders-card__section">
                        <h2>All Orders List</h2><br>
                        @include('sales.partials.orders-list', [
                            'filters' => $filters ?? [],
                            'productChoices' => $productChoices,
                            'sales' => $sales,
                            'perPage' => $perPage,
                            'paymentMethods' => $paymentMethods,
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
                        <label for="edit-sales-product-name">
                            Product Name
                            <input
                                type="text"
                                id="edit-sales-product-name"
                                value="{{ $editProductValue }}"
                                disabled
                            >
                            <input type="hidden" name="product_name" value="{{ $editProductValue }}">
                            <input type="hidden" name="product_expiry_days" value="{{ $editExpiryValue }}">
                            <span class="helper-text">Update the name from the Products page if it needs to change.</span>
                        </label>

                        <label for="edit-sales-remarks">
                            Remarks
                            <input
                                type="text"
                                id="edit-sales-remarks"
                                name="remarks"
                                value="{{ old('remarks', $saleToEdit->remarks) }}"
                                placeholder="Remarks"
                                maxlength="255"
                            >
                        </label>

                        <label for="edit-sales-payment-method">
                            Payment Method
                            @php
                                $editPaymentMethod = old('payment_method', $saleToEdit->paymentMethod?->slug);
                            @endphp
                            <select
                                id="edit-sales-payment-method"
                                name="payment_method"
                                class="paymentmethodchoose"
                            >
                                @foreach ($paymentMethods as $method)
                                    <option value="{{ $method->slug }}" @selected($editPaymentMethod === $method->slug)>
                                        {{ $method->label }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label for="edit-sales-status">
                            Status
                            @php
                                $editStatusValue = old('status', $saleToEdit->status ?? 'completed');
                            @endphp
                            <select id="edit-sales-status" name="status">
                                <option value="completed" @selected($editStatusValue === 'completed')>Completed</option>
                                <option value="refunded" @selected($editStatusValue === 'refunded')>Refunded</option>
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
                                step="0.01"
                                min="0"
                                id="edit-sales-amount"
                                name="sales_amount"
                                value="{{ old('sales_amount', $saleToEdit->sales_amount) }}"
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
                        'paymentMethods' => $paymentMethods,
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

    @php
        $saleConfirmation = session('saleConfirmation');
        $orderId = $saleConfirmation['serial_number'] ?? null;
        $productName = $saleConfirmation['product_name'] ?? null;
        $productDisplay = $productName ?: 'N/A';
        $orderCopy = $orderId
            ? "𝐘𝐨𝐮𝐫 𝐎𝐫𝐝𝐞𝐫 𝐈𝐃: {$orderId}\n\n"
                ."𝐏𝐫𝐨𝐝𝐮𝐜𝐭 𝐍𝐚𝐦𝐞: {$productDisplay}\n\n"
                ."We are processing your order now. Please wait patiently until we deliver your order.\n\n"
                ."𝐈𝐦𝐩𝐨𝐫𝐭𝐚𝐧𝐭 𝐍𝐨𝐭𝐞: Please keep your Order ID safe to get support in the future."
            : null;
    @endphp

    @if ($saleConfirmation && $orderId && $orderCopy)
        <section
            class="modal"
            id="sale-confirmation-modal"
            role="dialog"
            aria-modal="true"
            aria-labelledby="sale-confirmation-title">
            <div class="modal__content sale-confirmation">
                <div class="modal__header">
                    <div class="modal__header-group">
                        <center><h3 id="sale-confirmation-title">Order received</h3></center>
                    </div>
                    <button type="button" class="ghost-button button-with-icon sale-confirmation-close" data-sale-confirmation-close aria-label="Close confirmation">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M18 6L6 18M6 6l12 12" />
                        </svg>
                        <span>Close</span>
                    </button>
                </div>
                <div class="sale-confirmation-message">
                    <div class="sale-confirmation-hero">
                     
                        <p>We are processing your order now. Please wait patiently until we deliver your order.</p>
                    </div>
                    <dl class="sale-confirmation-details">
                        <div>
                            <dt>Your Order ID</dt>
                            <dd>{{ $orderId }}</dd>
                        </div>
                        <div>
                            <dt>Product Name</dt>
                            <dd>{{ $productDisplay }}</dd>
                        </div>
                    </dl>
                    <div class="sale-confirmation-note">
                        <strong>Important note:</strong> Please keep your Order ID safe to get support in the future.
                    </div>
                </div>
                <div class="sale-confirmation-actions">
                    <Center><button
                        type="button"
                        class="ghost-button button-with-icon sale-confirmation-copy"
                        data-sale-confirmation-copy
                        data-copy-text="{{ e($orderCopy) }}"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="8" y="8" width="12" height="12" rx="2" ry="2" />
                            <path d="M16 8V6a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2h2" />
                        </svg>
                        <span data-copy-label>Copy Credentials</span>
                    </button></center>
                </div>
            </div>
        </section>
    @endif
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const addPhoneInput = document.getElementById('sales-phone');
            const addEmailInput = document.getElementById('sales-email');
            const addEmailDefaultPlaceholder = addEmailInput?.getAttribute('placeholder') || 'Email address';
            const collectPhoneEmailMap = () => {
                const map = new Map();
                document.querySelectorAll('#orders-table tbody tr').forEach((row) => {
                    const phone = (row.dataset.phone || '').replace(/\D+/g, '');
                    const email = (row.dataset.email || '').trim();
                    if (phone && email && !map.has(phone)) {
                        map.set(phone, email);
                    }
                });
                return map;
            };
            const phoneEmailMap = collectPhoneEmailMap();
            const autofillEmail = () => {
                if (!addPhoneInput || !addEmailInput) return;
                const digits = (addPhoneInput.value || '').replace(/\D+/g, '');
                addEmailInput.setAttribute('placeholder', addEmailDefaultPlaceholder);
                if (!digits) return;
                const isEmailEmpty = addEmailInput.value.trim() === '';
                const foundEmail = phoneEmailMap.get(digits);
                if (foundEmail && isEmailEmpty) {
                    addEmailInput.value = foundEmail;
                    addEmailInput.dispatchEvent(new Event('input', { bubbles: true }));
                    addEmailInput.setAttribute('placeholder', addEmailDefaultPlaceholder);
                } else if (isEmailEmpty) {
                    addEmailInput.setAttribute('placeholder', 'No Matching Records');
                }
            };
            addPhoneInput?.addEventListener('blur', autofillEmail);
            addPhoneInput?.addEventListener('change', autofillEmail);
            addPhoneInput?.addEventListener('keyup', (event) => {
                if (event.key === 'Enter') {
                    autofillEmail();
                }
            });
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

            const confirmationModal = document.getElementById('sale-confirmation-modal');
            if (confirmationModal) {
                const closeButtons = confirmationModal.querySelectorAll('[data-sale-confirmation-close]');
                const copyButton = confirmationModal.querySelector('[data-sale-confirmation-copy]');
                const copyText = copyButton?.dataset.copyText ?? '';
                const copyLabel = copyButton?.querySelector('[data-copy-label]');
                const defaultCopyLabel = copyLabel?.textContent?.trim()
                    ?? copyButton?.textContent?.trim()
                    ?? 'Copy Credentials';

                const hideConfirmation = () => {
                    confirmationModal.classList.add('is-hidden');
                };

                closeButtons.forEach((button) => button.addEventListener('click', hideConfirmation));

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && !confirmationModal.classList.contains('is-hidden')) {
                        hideConfirmation();
                    }
                });

                if (copyButton && copyText) {
                    copyButton.addEventListener('click', async () => {
                        try {
                            await navigator.clipboard.writeText(copyText);
                            if (copyLabel) {
                                copyLabel.textContent = 'Copied!';
                            } else {
                                copyButton.textContent = 'Copied!';
                            }
                        } catch (error) {
                            if (copyLabel) {
                                copyLabel.textContent = 'Copy failed';
                            } else {
                                copyButton.textContent = 'Copy failed';
                            }
                        }

                        window.setTimeout(() => {
                            if (copyLabel) {
                                copyLabel.textContent = defaultCopyLabel;
                            } else {
                                copyButton.textContent = defaultCopyLabel;
                            }
                        }, 2000);
                    });
                }
            }
        });
    </script>
    @include('partials.product-combobox-scripts')
@endpush
