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

        .sales-filter-actions {
            display: flex;
            align-items: flex-end;
            gap: 0.5rem;
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
                $productChoices = collect($productOptions ?? [])
                    ->filter(fn ($value) => is_string($value) && trim($value) !== '')
                    ->unique()
                    ->values()
                    ->all();
            @endphp

            @unless ($saleToEdit)
                <section class="card stack">
                    <h2>Add Sales Record</h2>
                    <form method="POST" action="{{ route('dashboard.sales.store') }}" class="form-grid form-grid--compact">
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

                        <label for="sales-email">
                            Email Address
                            <input
                                type="email"
                                id="sales-email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="Email"
                                required>
                        </label>

                        <label for="sales-payment-method">
                            Payment method
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
                            $createOptions = collect($productChoices);
                            if ($createProductValue && !$createOptions->contains($createProductValue)) {
                                $createOptions->prepend($createProductValue);
                            }
                        @endphp
                        <div class="product-combobox" data-product-combobox data-allow-free-entry="true">
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
                            </label>

                            <div class="product-combobox__dropdown" role="listbox" aria-label="Product options">
                                @if ($createOptions->isEmpty())
                                    <p class="product-combobox__empty">No products available yet.</p>
                                @else
                                    <p class="product-combobox__empty" data-empty-message hidden>No matching products found.</p>
                                    @foreach ($createOptions as $option)
                                        @php
                                            $isSelectedOption = $createProductValue === $option;
                                        @endphp
                                        <button
                                            type="button"
                                            class="product-combobox__option {{ $isSelectedOption ? 'is-active' : '' }}"
                                            data-product-option
                                            data-product-name="{{ $option }}"
                                            data-product-id="{{ $option }}"
                                            role="option"
                                            aria-selected="{{ $isSelectedOption ? 'true' : 'false' }}"
                                        >
                                            {{ $option }}
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
                                placeholder="Amount"
                                required>
                        </label>

                          <label for="sales-remarks">
                            Remarks
                            <input
                                type="text"
                                id="sales-remarks"
                                name="remarks"
                                value="{{ old('remarks') }}"
                                placeholder="Optional"
                                maxlength="255"
                            >
                        </label>


                        <div class="form-actions form-actions--row">
                            <button type="submit">Save record</button>
                        </div>
                    </form>
                </section>
            @endunless

            @if ($saleToEdit)
                <section class="card stack">
                    <div class="flex items-center gap-1">
                        <h2>Edit Sales Record #{{ $saleToEdit->serial_number }}</h2>
                    </div>

                    <form method="POST" action="{{ route('dashboard.sales.update', $saleToEdit) }}" class="form-grid form-grid--compact">
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
                            $editOptions = collect($productChoices);
                            if ($editProductValue && !$editOptions->contains($editProductValue)) {
                                $editOptions->prepend($editProductValue);
                            }
                        @endphp
                        <div class="product-combobox" data-product-combobox data-allow-free-entry="true">
                            <label for="edit-sales-product-name">
                                Product
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
                            </label>

                            <div class="product-combobox__dropdown" role="listbox" aria-label="Product options">
                                @if ($editOptions->isEmpty())
                                    <p class="product-combobox__empty">No products available yet.</p>
                                @else
                                    <p class="product-combobox__empty" data-empty-message hidden>No matching products found.</p>
                                    @foreach ($editOptions as $option)
                                        @php
                                            $isSelectedOption = $editProductValue === $option;
                                        @endphp
                                        <button
                                            type="button"
                                            class="product-combobox__option {{ $isSelectedOption ? 'is-active' : '' }}"
                                            data-product-option
                                            data-product-name="{{ $option }}"
                                            data-product-id="{{ $option }}"
                                            role="option"
                                            aria-selected="{{ $isSelectedOption ? 'true' : 'false' }}"
                                        >
                                            {{ $option }}
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
                                placeholder="Optional notes"
                                maxlength="255"
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
                                value="{{ old('email', $saleToEdit->email) }}"
                                required>
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

            @unless ($saleToEdit)
                <section class="card stack">
                    <h2>Sales Records</h2>

                    @php
                        $filters = $filters ?? [
                            'serial_number' => '',
                            'phone' => '',
                            'email' => '',
                            'product_name' => '',
                            'date_from' => null,
                            'date_to' => null,
                        ];
                        $resetParams = [];
                        if (request()->filled('per_page')) {
                            $resetParams['per_page'] = request()->query('per_page');
                        }
                    @endphp

                    <form method="GET" action="{{ route('sales.index') }}" class="sales-filter-row" autocomplete="off">
                        @if (request()->filled('per_page'))
                            <input type="hidden" name="per_page" value="{{ request()->query('per_page') }}">
                        @endif

                        <label for="filter-serial-number">
                            S.N.
                            <input
                                type="text"
                                id="filter-serial-number"
                                name="serial_number"
                                value="{{ $filters['serial_number'] }}"
                                placeholder="TM123"
                            >
                        </label>

                        <label for="filter-phone">
                            Phone
                            <input
                                type="text"
                                id="filter-phone"
                                name="phone"
                                value="{{ $filters['phone'] }}"
                                placeholder="98xxxxxxxx"
                            >
                        </label>

                        <label for="filter-email">
                            Email
                            <input
                                type="text"
                                id="filter-email"
                                name="email"
                                value="{{ $filters['email'] }}"
                                placeholder="customer@example.com"
                            >
                        </label>

                        <label for="filter-product">
                            Product name
                            <input
                                type="text"
                                id="filter-product"
                                name="product_name"
                                value="{{ $filters['product_name'] }}"
                                placeholder="Product or variation"
                            >
                        </label>

                        <label for="filter-date-from">
                            Date from
                            <input
                                type="date"
                                id="filter-date-from"
                                name="date_from"
                                value="{{ $filters['date_from'] }}"
                            >
                        </label>

                        <label for="filter-date-to">
                            Date to
                            <input
                                type="date"
                                id="filter-date-to"
                                name="date_to"
                                value="{{ $filters['date_to'] }}"
                            >
                        </label>

                        <div class="sales-filter-actions">
                            <button type="submit">Apply filters</button>
                            <a href="{{ route('sales.index', $resetParams) }}" class="ghost-button">Reset</a>
                        </div>
                    </form>


                    <div class="table-wrapper">
                        <table class="sales-table">
                            <thead>
                                <tr>
                                    <th scope="col">S.N.</th>
                                    <th scope="col">Purchase Date</th>
                                    <th scope="col">Product</th>
                                    <th scope="col">Phone</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Amount</th>
                                    <th scope="col">Payment Method</th>
                                    <th scope="col">Posted By</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($sales as $sale)
                                    <tr>
                                        <td>{{ $sale->serial_number }}</td>
                                        @php
                                            $saleRecordedAt = $sale->created_at?->timezone('Asia/Kathmandu');
                                        @endphp
                                        <td>
                                            @if ($saleRecordedAt)
                                                {{ $saleRecordedAt->format('M d h:i A') }}
                                            @else
                                                <span class="muted">—</span>
                                            @endif
                                        </td>
                                        @php
                                            $productDisplay = trim($sale->product_name ?? '');
                                        @endphp
                                        <td>{{ $productDisplay !== '' ? $productDisplay : '—' }}</td>
                                        <td>
                                            <div class="cell-with-action">
                                                <span>{{ $sale->phone }}</span>
                                                <button
                                                    type="button"
                                                    class="cell-action-button"
                                                    data-copy="{{ $sale->phone }}"
                                                    aria-label="Copy phone {{ $sale->serial_number }}">
                                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                                        <path d="M8 7V5a2 2 0 012-2h9a2 2 0 012 2v11a2 2 0 01-2 2h-2" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <rect x="4" y="7" width="12" height="12" rx="2" ry="2" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="cell-with-action">
                                                <span>{{ $sale->email }}</span>
                                                <button
                                                    type="button"
                                                    class="cell-action-button"
                                                    data-copy="{{ $sale->email }}"
                                                    aria-label="Copy email {{ $sale->serial_number }}">
                                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                                        <path d="M8 7V5a2 2 0 012-2h9a2 2 0 012 2v11a2 2 0 01-2 2h-2" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <rect x="4" y="7" width="12" height="12" rx="2" ry="2" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                        <td>Rs {{ number_format($sale->sales_amount, 0) }}</td>
                                        <td>{{ $sale->paymentMethod?->label ?? '—' }}</td>
                                        <td>{{ $sale->createdBy?->name ?? 'Unknown employee' }}</td>
                                        <td>
                                            <div class="table-actions">
                                                @if (!empty($sale->remarks))
                                                    <button
                                                        type="button"
                                                        class="icon-button"
                                                        data-sale-remarks="{{ e($sale->remarks) }}"
                                                        data-sale-product="{{ e($sale->product_name ?? '') }}"
                                                        data-sale-number="{{ e($sale->serial_number) }}"
                                                        data-sale-poster="{{ e($sale->createdBy?->name ?? 'Unknown employee') }}"
                                                        aria-label="View remarks for sale {{ $sale->serial_number }}">
                                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                                            <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                            <circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="1.5" />
                                                        </svg>
                                                    </button>
                                                @endif
                                                <a
                                                    class="icon-button"
                                                    href="{{ route('sales.index', ['edit' => $sale->id] + request()->except('page')) }}"
                                                    aria-label="Edit sale {{ $sale->serial_number }}">
                                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                                        <path d="M4 15.5V20h4.5L19 9.5l-4.5-4.5L4 15.5z" fill="currentColor"/>
                                                        <path d="M14.5 5.5l4 4" stroke="currentColor" stroke-width="1.2"/>
                                                    </svg>
                                                </a>
                                                <form
                                                    method="POST"
                                                    action="{{ route('dashboard.sales.destroy', $sale) }}"
                                                    onsubmit="return confirm('Delete sale {{ $sale->serial_number }}? This cannot be undone.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="icon-button icon-button--danger" aria-label="Delete sale {{ $sale->serial_number }}">
                                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                                            <path d="M6 7h12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                            <path d="M10 11v6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                            <path d="M14 11v6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                            <path d="M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                            <path d="M19 7l-.6 10.2A2 2 0 0116.41 19H7.59a2 2 0 01-1.99-1.8L5 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8">
                                            <p class="helper-text">No sales recorded yet.</p>
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
                    <form method="GET" class="table-controls__page-size">
                        @foreach (request()->except('per_page', 'page') as $param => $value)
                            @if (!is_array($value))
                                <input type="hidden" name="{{ $param }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        <label for="sales-per-page">
                            
                            <select
                                id="sales-per-page"
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
                            href="{{ $sales->previousPageUrl() ? route('sales.index', array_merge(request()->except('page'), ['page' => $currentPage - 1])) : '#' }}"
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
                            href="{{ $sales->nextPageUrl() ? route('sales.index', array_merge(request()->except('page'), ['page' => $currentPage + 1])) : '#' }}"
                            @class(['is-disabled' => !$sales->nextPageUrl()])
                            aria-disabled="{{ $sales->nextPageUrl() ? 'false' : 'true' }}">
                            Next
                        </a>
                    </div>
                </div>
                </section>

               
            @endunless
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
        $orderCopy = $orderId
            ? "Your Order ID: {$orderId}\n\nWe are processing your order now. Please wait patiently until we deliver your order.\n\nImportant Note: Please keep your Order ID Safe to get support in Future. Our team will ask your order id to provide you further support."
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
