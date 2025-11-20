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

    $filterProductValue = $filters['product_name'];
    $filterProductOptions = collect($productChoices);
    if ($filterProductValue && !$filterProductOptions->contains($filterProductValue)) {
        $filterProductOptions->prepend($filterProductValue);
    }
@endphp

<form method="GET" action="{{ route('orders.index') }}" class="sales-filter-row" autocomplete="off">
    @if (request()->filled('per_page'))
        <input type="hidden" name="per_page" value="{{ request()->query('per_page') }}">
    @endif

    <label for="filter-serial-number">
        Order ID
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
            placeholder="Email"
        >
    </label>

    <div class="product-combobox" data-product-combobox data-allow-free-entry="true">
        <label for="filter-product">
            Product
            <input
                type="text"
                id="filter-product"
                class="product-combobox__input"
                name="product_name"
                value="{{ $filterProductValue }}"
                placeholder="Choose"
                autocomplete="off"
                list="sales-product-options"
                data-selected-name="{{ $filterProductValue }}"
            >
        </label>
        <div class="product-combobox__dropdown" role="listbox" aria-label="Product options">
            @if ($filterProductOptions->isEmpty())
                <p class="product-combobox__empty">No products available yet.</p>
            @else
                <p class="product-combobox__empty" data-empty-message hidden>No matching products found.</p>
                @foreach ($filterProductOptions as $option)
                    @php
                        $isSelectedOption = $filterProductValue === $option;
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

    <label for="filter-date-from">
       From
        <input
            type="date"
            id="filter-date-from"
            name="date_from"
            value="{{ $filters['date_from'] }}"
        >
    </label>

    <label for="filter-date-to">
        To
        <input
            type="date"
            id="filter-date-to"
            name="date_to"
            value="{{ $filters['date_to'] }}"
        >
    </label>

    <div class="sales-filter-actions">
        <button type="submit">Filter</button>
    </div>
</form>
@if (!empty($productChoices))
    <datalist id="sales-product-options">
        @foreach ($productChoices as $option)
            <option value="{{ $option }}"></option>
        @endforeach
    </datalist>
@endif

@push('styles')
    <style>
        .sales-table th {
            position: relative;
        }

        .sales-col-resizer {
            position: absolute;
            top: 0;
            right: -4px;
            width: 8px;
            height: 100%;
            cursor: col-resize;
            user-select: none;
            z-index: 2;
        }
    </style>
@endpush

<div class="table-wrapper">
    <table class="sales-table" id="orders-table">
        <colgroup id="orders-colgroup"></colgroup>
        <thead>
            <tr>
                <th scope="col" data-col-id="serial">Order ID<span class="sales-col-resizer" data-col-id="serial"></span></th>
                <th scope="col" data-col-id="purchase_date">Purchase Date<span class="sales-col-resizer" data-col-id="purchase_date"></span></th>
                <th scope="col" data-col-id="product">Product<span class="sales-col-resizer" data-col-id="product"></span></th>
                <th scope="col" data-col-id="email">Email<span class="sales-col-resizer" data-col-id="email"></span></th>
                <th scope="col" data-col-id="phone">Phone<span class="sales-col-resizer" data-col-id="phone"></span></th>
                <th scope="col" data-col-id="amount">Amount<span class="sales-col-resizer" data-col-id="amount"></span></th>
                <th scope="col" data-col-id="payment">Payment<span class="sales-col-resizer" data-col-id="payment"></span></th>
                <th scope="col" data-col-id="status">Status<span class="sales-col-resizer" data-col-id="status"></span></th>
                <th scope="col" data-col-id="sold_by">Sold By<span class="sales-col-resizer" data-col-id="sold_by"></span></th>
                <th scope="col" data-col-id="actions">Actions<span class="sales-col-resizer" data-col-id="actions"></span></th>
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
                    @php
                        $emailDisplay = trim((string) $sale->email);
                    @endphp
                    <td>{{ $emailDisplay !== '' ? $emailDisplay : '—' }}</td>
                    @php
                        $rawPhone = (string) ($sale->phone ?? '');
                        $leadingSymbol = ltrim($rawPhone, " ()-\t\n\r\0\x0B");
                        $hasLeadingPlus = str_starts_with($leadingSymbol, '+');
                        $normalizedPhone = preg_replace('/[()\s-]+/', '', $rawPhone);
                        $normalizedPhone = ltrim($normalizedPhone ?? '', '+');
                        if ($normalizedPhone !== '' && $hasLeadingPlus) {
                            $normalizedPhone = '+' . $normalizedPhone;
                        }
                        $phoneDisplay = $normalizedPhone !== '' ? $normalizedPhone : '—';
                    @endphp
                    <td>
                        <div class="cell-with-action">
                            @php
                                $urlPhone = preg_replace('/\D+/', '', $sale->phone ?? '');
                            @endphp
                            @if ($phoneDisplay !== '—' && $urlPhone !== '')
                                <a
                                    href="{{ route('orders.customer', ['phone' => $urlPhone]) }}"
                                    class="phone-link phone-link--clean">
                                    {{ $phoneDisplay }}
                                </a>
                            @else
                                <span>{{ $phoneDisplay }}</span>
                            @endif
                            @if ($phoneDisplay !== '—')
                                <button
                                    type="button"
                                    class="cell-action-button"
                                    data-copy="{{ $phoneDisplay }}"
                                    aria-label="Copy phone {{ $sale->serial_number }}">
                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M8 7V5a2 2 0 012-2h9a2 2 0 012 2v11a2 2 0 01-2 2h-2" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        <rect x="4" y="7" width="12" height="12" rx="2" ry="2" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </td>
                    <td>Rs {{ number_format($sale->sales_amount, 0) }}</td>
                    <td>{{ $sale->paymentMethod?->label ?? '—' }}</td>
                    @php
                        $status = strtolower((string) ($sale->status ?? 'completed'));
                        $statusLabel = $status === 'refunded' ? 'Refunded' : 'Completed';
                    @endphp
                    <td>{{ $statusLabel }}</td>
                    <td>{{ $sale->createdBy?->name ?? 'Unknown employee' }}</td>
                    <td>
                        <div class="table-actions">
                            <button
                                type="button"
                                class="icon-button"
                                data-action="show-remarks"
                                data-remarks="{{ trim((string) $sale->remarks) }}"
                                aria-label="View remarks for {{ $sale->serial_number }}">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M12 5c-4.5 0-8.2 3-10 7 1.8 4 5.5 7 10 7s8.2-3 10-7c-1.8-4-5.5-7-10-7z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="1.5"/>
                                </svg>
                            </button>
                            <a
                                class="icon-button"
                                href="{{ route('orders.index', ['edit' => $sale->id] + request()->except('page')) }}"
                                aria-label="Edit sale {{ $sale->serial_number }}">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M4 15.5V20h4.5L19 9.5l-4.5-4.5L4 15.5z" fill="currentColor"/>
                                    <path d="M14.5 5.5l4 4" stroke="currentColor" stroke-width="1.2"/>
                                </svg>
                            </a>
                            <form
                                method="POST"
                                action="{{ route('dashboard.orders.destroy', $sale) }}"
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
                    <td colspan="9">
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
            <input type="hidden" name="{{ $param }}" value="{{ $value }}">
        @endforeach
        <label for="orders-per-page">
            <select
                id="orders-per-page"
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
            href="{{ $sales->previousPageUrl() ? route('orders.index', array_merge(request()->except('page'), ['page' => $currentPage - 1])) : '#' }}"
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
            href="{{ $sales->nextPageUrl() ? route('orders.index', array_merge(request()->except('page'), ['page' => $currentPage + 1])) : '#' }}"
            @class(['is-disabled' => !$sales->nextPageUrl()])
            aria-disabled="{{ $sales->nextPageUrl() ? 'false' : 'true' }}">
            Next
        </a>
    </div>
</div>

@push('scripts')
    <script>
        (() => {
            const storageKey = 'orders_table_widths';
            const columnIds = ['serial', 'purchase_date', 'product', 'email', 'phone', 'amount', 'payment', 'status', 'sold_by', 'actions'];
            const table = document.getElementById('orders-table');
            const colgroup = document.getElementById('orders-colgroup');

            if (!table || !colgroup) return;

            const loadWidths = () => {
                try {
                    const raw = localStorage.getItem(storageKey);
                    if (!raw) return {};
                    const parsed = JSON.parse(raw);
                    return parsed && typeof parsed === 'object' ? parsed : {};
                } catch (error) {
                    console.warn('Unable to read column widths', error);
                    return {};
                }
            };

            const widths = loadWidths();

            const renderColgroup = () => {
                colgroup.innerHTML = columnIds.map((id) => {
                    const width = widths[id];
                    return `<col data-col-id="${id}" style="${width ? `width:${width}px` : ''}">`;
                }).join('');
            };

            const persist = () => {
                try {
                    localStorage.setItem(storageKey, JSON.stringify(widths));
                } catch (error) {
                    console.warn('Unable to save column widths', error);
                }
            };

            const applyHeaderWidths = () => {
                columnIds.forEach((id) => {
                    const header = table.querySelector(`th[data-col-id="${id}"]`);
                    if (!header) return;
                    const width = widths[id];
                    header.style.width = width ? `${width}px` : '';
                });
            };

            const setupResizers = () => {
                const headers = Array.from(table.querySelectorAll('thead th'));
                headers.forEach((th) => {
                    const handle = th.querySelector('.sales-col-resizer');
                    const colId = handle?.dataset?.colId;
                    if (!handle || !colId) return;
                    handle.addEventListener('mousedown', (event) => startResize(event, colId));
                });
            };

            const startResize = (event, colId) => {
                event.preventDefault();
                const startX = event.pageX;
                const header = table.querySelector(`th[data-col-id="${colId}"]`);
                const startWidth = widths[colId]
                    ?? header?.getBoundingClientRect().width
                    ?? 140;

                const onMove = (moveEvent) => {
                    moveEvent.preventDefault();
                    const delta = moveEvent.pageX - startX;
                    const nextWidth = Math.max(80, startWidth + delta);
                    widths[colId] = nextWidth;
                    renderColgroup();
                    applyHeaderWidths();
                };

                const onUp = () => {
                    document.removeEventListener('mousemove', onMove);
                    document.removeEventListener('mouseup', onUp);
                    persist();
                };

                document.addEventListener('mousemove', onMove);
                document.addEventListener('mouseup', onUp);
            };

            const handleRemarksView = (event) => {
                const button = event.target.closest('button[data-action="show-remarks"]');
                if (!button) return;
                const remarks = button.dataset.remarks?.trim();
                alert(remarks && remarks !== '' ? remarks : 'No remarks available.');
            };

            renderColgroup();
            applyHeaderWidths();
            setupResizers();
            table.addEventListener('click', handleRemarksView);
        })();
    </script>
@endpush
