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
            min-width: 170px;
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

        .sales-filter-actions {
            display: flex;
            align-items: flex-end;
            gap: 0.5rem;
            margin: 20px;
        }

     

        .sale-confirmation-message {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .sale-confirmation-actions {
            display: flex;
            justify-content: flex-start;
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

        .orders-card__section + .orders-card__section {
            border-top: 1px solid rgba(148, 163, 184, 0.35);
            padding-top: 1.25rem;
            margin-top: 0.5rem;
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
                    <form method="POST" action="{{ route('dashboard.orders.store') }}" class="form-grid form-grid--compact">
                        @csrf
                        <label for="sales-phone">
                            Phone Number
                            <input
                                type="tel"
                                id="sales-phone"
                                name="phone"
                                value="{{ old('phone') }}"
                                placeholder="Phone"
                                required>
                        </label>

                        <label for="sales-payment-method">
                            Payment
                            <select id="sales-payment-method" name="payment_method" required>
                                <option value="" disabled {{ old('payment_method') ? '' : 'selected' }}>Choose one</option>
                                @foreach ($paymentMethods as $method)
                                    <option value="{{ $method->slug }}" @selected(old('payment_method') === $method->slug)>
                                        {{ $method->label }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        @php
                            $createProductValue = old('product_name');
                            $createExpiryValue = old('product_expiry_days');
                            $createOptions = $ensureOptionPresent($normalizedProductOptions, $createProductValue, $createExpiryValue);
                        @endphp
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
                                    placeholder="Choose one"
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
                                required>
                        </label>

                          <label for="sales-remarks">
                            Remarks
                            <input
                                type="text"
                                id="sales-remarks"
                                name="remarks"
                                value="{{ old('remarks') }}"
                                placeholder="Remarks"
                                maxlength="255"
                                required
                            >
                        </label>


            
                            <div class="sales-filter-actions">
                                <button type="submit" class="button">Create</button>
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
                        <div
                            class="product-combobox"
                            data-product-combobox
                            data-allow-free-entry="true"
                            data-expiry-input="edit-product-expiry">
                            <label for="edit-sales-product-name">
                                Product Name
                                <input
                                    type="text"
                                    id="edit-sales-product-name"
                                    class="product-combobox__input"
                                    name="product_name"
                                    value="{{ $editProductValue }}"
                                    placeholder="Select product..."
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

                        <label for="edit-sales-remarks">
                            Remarks
                            <input
                                type="text"
                                id="edit-sales-remarks"
                                name="remarks"
                                value="{{ old('remarks', $saleToEdit->remarks) }}"
                                placeholder="Remarks"
                                maxlength="255"
                                required
                            >
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
                                required>
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
                ."𝐈𝐦𝐩𝐨𝐫𝐭𝐚𝐧𝐭 𝐍𝐨𝐭𝐞: Please keep your Order ID safe to get support in future. Our team will ask you for your order ID to provide support."
            : null;
    @endphp

    @if ($saleConfirmation && $orderId && $orderCopy)
        <section
            class="modal"
            id="sale-confirmation-modal"
            role="dialog"
            aria-modal="true"
            aria-labelledby="sale-confirmation-title">
            <div class="modal__content">
                <div class="modal__header">
                    <h3 id="sale-confirmation-title">Order received</h3>
                    <button type="button" class="ghost-button" data-sale-confirmation-close aria-label="Close confirmation">Close</button>
                </div>
                <div class="sale-confirmation-message">
                    <p>𝐘𝐨𝐮𝐫 𝐎𝐫𝐝𝐞𝐫 𝐈𝐃: <strong>{{ $orderId }}</strong></p>
                    <p>𝐏𝐫𝐨𝐝𝐮𝐜𝐭 𝐍𝐚𝐦𝐞: <strong>{{ $productDisplay }}</strong></p>
                    <p>We are processing your order now. Please wait patiently until we deliver your order.</p>
                    <p><strong>𝐈𝐦𝐩𝐨𝐫𝐭𝐚𝐧𝐭 𝐍𝐨𝐭𝐞:</strong> Please keep your Order ID safe to get support in future. Our team will ask you for your order ID to provide support.</p>
                </div>
                <div class="sale-confirmation-actions">
                    <button
                        type="button"
                        class="ghost-button"
                        data-sale-confirmation-copy
                        data-copy-text="{{ e($orderCopy) }}"
                    >
                        Copy Credentials
                    </button>
                </div>
            </div>
        </section>
    @endif
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
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
                const defaultCopyLabel = copyButton?.textContent?.trim() ?? 'Copy Credentials';

                const hideConfirmation = () => {
                    confirmationModal.classList.add('is-hidden');
                };

                closeButtons.forEach((button) => button.addEventListener('click', hideConfirmation));

                confirmationModal.addEventListener('click', (event) => {
                    if (event.target === confirmationModal) {
                        hideConfirmation();
                    }
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && !confirmationModal.classList.contains('is-hidden')) {
                        hideConfirmation();
                    }
                });

                if (copyButton && copyText) {
                    copyButton.addEventListener('click', async () => {
                        try {
                            await navigator.clipboard.writeText(copyText);
                            copyButton.textContent = 'Copied!';
                        } catch (error) {
                            copyButton.textContent = 'Copy failed';
                        }

                        window.setTimeout(() => {
                            copyButton.textContent = defaultCopyLabel;
                        }, 2000);
                    });
                }
            }
        });
    </script>
    @include('partials.product-combobox-scripts')
@endpush
