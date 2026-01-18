@php
    $filters = $filters ?? [
        'search' => '',
        'created_by' => '',
        'product_name' => '',
        'status' => '',
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

    $isEmployee = auth()->user()?->role === 'employee';
@endphp

<form method="GET" action="{{ route('orders.index') }}" class="sales-filter-row" autocomplete="off" id="orders-search-form">
    @if (request()->filled('per_page'))
        <input type="hidden" name="per_page" value="{{ request()->query('per_page') }}">
    @endif

    <label for="filter-search">
        Search
        <input
            type="text"
            id="filter-search"
            name="search"
            value="{{ $filters['search'] }}"
            placeholder="Search Order"
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
            class="date-compact"
        >
    </label>

    <label for="filter-date-to">
        To
        <input
            type="date"
            id="filter-date-to"
            name="date_to"
            value="{{ $filters['date_to'] }}"
            class="date-compact"
        >
    </label>

    <label for="filter-status">
        Status
        <select id="filter-status" name="status">
            <option value="">Any</option>
            <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>Pending</option>
            <option value="completed" @selected(($filters['status'] ?? '') === 'completed')>Completed</option>
            <option value="refunded" @selected(($filters['status'] ?? '') === 'refunded')>Refunded</option>
            <option value="cancelled" @selected(($filters['status'] ?? '') === 'cancelled')>Cancelled</option>
        </select>
    </label>

    <label for="filter-created-role">
        Created By
        <select id="filter-created-role" name="created_by">
            <option value="">Any</option>
            @foreach (($admins ?? []) as $admin)
                <option value="{{ $admin->id }}" @selected(($filters['created_by'] ?? '') == $admin->id)>{{ $admin->name }}</option>
            @endforeach
            @foreach (($employees ?? []) as $employee)
                <option value="{{ $employee->id }}" @selected(($filters['created_by'] ?? '') == $employee->id)>{{ $employee->name }}</option>
            @endforeach
        </select>
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
        .sales-filter-row {
            gap: 0.85rem;
        }

        .table-wrapper {
            overflow: auto;
            border: 1px solid rgba(15, 23, 42, 0.1);
            border-radius: 0.75rem;
            width: 100%;
            max-width: 100%;
        }

        .sales-filter-row .date-compact {
            width: 2.8rem;
            min-width: 2.8rem;
            padding: 0.25rem;
        }

        #orders-table {
            width: 100%;
            min-width: 960px;
            border-collapse: collapse;
            table-layout: fixed;
            background: linear-gradient(180deg, #fff, #f8fafc 18%, #fff 100%);
        }

        #orders-table th,
        #orders-table td {
            border: 1px solid rgba(148, 163, 184, 0.35);
            padding: 0.55rem 0.65rem;
            background: #fff;
            text-align: center;
            min-width: 0;
            word-break: break-word;
        }

        #orders-table thead th {
            background: linear-gradient(180deg, rgba(226, 232, 240, 0.9), rgba(241, 245, 249, 0.9));
            font-weight: 800;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            position: sticky;
            top: 0;
            z-index: 2;
        }

        #orders-table tbody tr:nth-child(even) td {
            background: #f8fafc;
        }

        #orders-table tbody tr:hover td {
            background: #eef2ff;
            border-color: rgba(79, 70, 229, 0.35);
            transition: background 0.2s ease, border-color 0.2s ease;
        }

        .sales-product-text {
            display: inline-block;
            white-space: normal;
            word-break: break-word;
            line-height: 1.35;
        }

        .sales-table th {
            position: relative;
        }

        .sales-col-resizer {
            position: absolute;
            top: 0;
            right: -6px;
            width: 12px;
            height: 100%;
            cursor: col-resize;
            user-select: none;
            z-index: 3;
        }

        .sales-product-input {
            width: 100%;
            border: 1px solid rgba(148, 163, 184, 0.45);
            border-radius: 0.35rem;
            padding: 0.3rem 0.4rem;
            font: inherit;
            background: #fff;
            text-align: center;
        }

        #orders-table td input,
        #orders-table td a,
        #orders-table td span {
            text-align: center;
        }

        .cell-with-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            width: 100%;
        }

        .table-actions {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            width: 100%;
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
                <th scope="col" data-col-id="status">Status<span class="sales-col-resizer" data-col-id="status"></span></th>
                <th scope="col" data-col-id="sync">Sync<span class="sales-col-resizer" data-col-id="sync"></span></th>
                <th scope="col" data-col-id="sold_by">Sold By<span class="sales-col-resizer" data-col-id="sold_by"></span></th>
                <th scope="col" data-col-id="actions">Actions<span class="sales-col-resizer" data-col-id="actions"></span></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($sales as $sale)
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
                    $phoneDigits = preg_replace('/\D+/', '', $rawPhone);
                @endphp
                @php
                    $formId = 'sale-inline-' . $sale->id;
                    $purchaseAt = $sale->purchase_date?->timezone('Asia/Kathmandu')
                        ?? $sale->created_at?->timezone('Asia/Kathmandu');
                @endphp
                <tr data-phone="{{ $phoneDigits }}" data-email="{{ trim((string) $sale->email) }}">
                    <td>
                        {{ $sale->serial_number }}
                        <form id="{{ $formId }}" method="POST" action="{{ route('dashboard.orders.update', $sale) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="purchase_date" value="{{ optional($sale->purchase_date ?? $sale->created_at)->format('Y-m-d') }}">
                            <input type="hidden" name="phone" value="{{ $sale->phone }}">
                            <input type="hidden" name="product_expiry_days" value="{{ $sale->product_expiry_days }}">
                        </form>
                    </td>
                    <td>
                        @if ($purchaseAt)
                            {{ $purchaseAt->format('M d, Y') }}
                        @else
                            <span class="muted">—</span>
                        @endif
                    </td>
                    @php
                        $productDisplayRaw = trim($sale->product_name ?? '');
                        $productDisplay = $productDisplayRaw !== '' ? $productDisplayRaw : '—';
                    @endphp
                    <td>
                        <input
                            type="text"
                            name="product_name"
                            form="{{ $formId }}"
                            class="sales-product-text sales-product-input"
                            value="{{ $sale->product_name }}"
                            placeholder="Product"
                            list="sales-product-options"
                            autocomplete="off"
                        >
                    </td>
                    @php
                        $emailDisplay = trim((string) $sale->email);
                    @endphp
                    <td>
                        <input
                            type="email"
                            name="email"
                            form="{{ $formId }}"
                            value="{{ $emailDisplay }}"
                            placeholder="Add email"
                        >
                    </td>
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
                    <td>
                        <input
                            type="number"
                            step="1"
                            min="0"
                            inputmode="numeric"
                            name="sales_amount"
                            form="{{ $formId }}"
                            value="{{ $sale->sales_amount !== null ? (int) $sale->sales_amount : '' }}"
                            placeholder="Amount"
                        >
                    </td>
                    <input type="hidden" name="remarks" form="{{ $formId }}" value="{{ $sale->remarks }}">
                    @php
                        $status = strtolower((string) ($sale->status ?? 'pending'));
                        $statusLabel = match ($status) {
                            'completed' => 'Completed',
                            'refunded' => 'Refunded',
                            'cancelled' => 'Cancelled',
                            default => 'Pending',
                        };
                    @endphp
                    <td>
                        <input type="hidden" name="remarks" form="{{ $formId }}" value="{{ $sale->remarks }}">
                        <input type="hidden" name="status" form="{{ $formId }}" value="{{ $status }}">
                        @php
                            $statusIcon = match ($status) {
                                'completed' => '☑️',
                                'refunded' => '♻',
                                'cancelled' => '✖️',
                                default => '⌛',
                            };
                        @endphp
                        <span aria-label="{{ $statusLabel }}" title="{{ $statusLabel }}" style="font-size: 1.1rem;">{{ $statusIcon }}</span>
                    </td>
                    @php
                        $sheetSyncState = $sale->sheet_sync_state ?? 'unlinked';
                        $familySyncState = $sale->family_sync_state ?? 'unlinked';
                        if ($status === 'refunded') {
                            $syncLabel = 'Refunded';
                        } elseif ($status === 'cancelled') {
                            $syncLabel = 'Cancelled';
                        } elseif ($sheetSyncState === 'active') {
                            $syncLabel = 'Sheet';
                        } elseif ($familySyncState === 'active') {
                            $syncLabel = 'Family';
                        } elseif ($sheetSyncState === 'error' || $familySyncState === 'error') {
                            $syncLabel = 'Error';
                        } else {
                            $syncLabel = '-';
                        }
                    @endphp
                    <td aria-label="Sync Status">{{ $syncLabel }}</td>
                    <td style="white-space: nowrap;">{{ $sale->createdBy?->name ?? 'Unknown employee' }}</td>
                    <td>
                        <div class="table-actions">
                            <button type="submit" form="{{ $formId }}" class="icon-button" aria-label="Update {{ $sale->serial_number }}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M345 273c9.4-9.4 9.4-24.6 0-33.9L201 95c-6.9-6.9-17.2-8.9-26.2-5.2S160 102.3 160 112l0 80-112 0c-26.5 0-48 21.5-48 48l0 32c0 26.5 21.5 48 48 48l112 0 0 80c0 9.7 5.8 18.5 14.8 22.2s19.3 1.7 26.2-5.2L345 273zm7 143c-17.7 0-32 14.3-32 32s14.3 32 32 32l64 0c53 0 96-43 96-96l0-256c0-53-43-96-96-96l-64 0c-17.7 0-32 14.3-32 32s14.3 32 32 32l64 0c17.7 0 32 14.3 32 32l0 256c0 17.7-14.3 32-32 32l-64 0z"/></svg>
                            </button>
                            <a
                                class="icon-button"
                                href="{{ route('orders.index', array_merge(request()->query(), ['edit' => $sale->id])) }}"
                                aria-label="Edit {{ $sale->serial_number }}"
                            >
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M16.86 4.14a3 3 0 114.24 4.24L8.96 20.52 3 21l.48-5.96L16.86 4.14z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M15 6l3 3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                            <button
                                type="button"
                                class="icon-button"
                                aria-label="View remarks for {{ $sale->serial_number }}"
                                data-sale-remarks="{{ trim((string) $sale->remarks) }}"
                                data-sale-number="{{ $sale->serial_number }}"
                                data-sale-product="{{ $sale->product_name }}"
                                data-sale-poster="{{ $sale->createdBy?->name ?? '' }}"
                            >
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M12 5c-5 0-9 7-9 7s4 7 9 7 9-7 9-7-4-7-9-7z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle cx="12" cy="12" r="2.5" fill="none" stroke="currentColor" stroke-width="1.5" />
                                </svg>
                            </button>
                            @unless ($isEmployee)
                                <form
                                    method="POST"
                                    action="{{ route('dashboard.orders.destroy', $sale) }}"
                                    onsubmit="return confirm('Delete sale {{ $sale->serial_number }}? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="icon-button" aria-label="Delete sale {{ $sale->serial_number }}">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M6 7h12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                            <path d="M10 11v6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                            <path d="M14 11v6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                            <path d="M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                            <path d="M19 7l-.6 10.2A2 2 0 0116.41 19H7.59a2 2 0 01-1.99-1.8L5 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                        </svg>
                                    </button>
                                </form>
                            @endunless
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10">
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
        <div class="pagination-numbers" style="display: inline-flex; gap: 0.25rem; align-items: center;">
            @for ($i = 1; $i <= $lastPage; $i++)
                <a
                    class="ghost-button {{ $i === $currentPage ? 'is-active' : '' }}"
                    href="{{ route('orders.index', array_merge(request()->except('page'), ['page' => $i])) }}"
                    aria-current="{{ $i === $currentPage ? 'page' : 'false' }}">
                    {{ $i }}
                </a>
            @endfor
        </div>
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
            const columnIds = ['serial', 'purchase_date', 'product', 'email', 'phone', 'amount', 'status', 'sync', 'sold_by', 'actions'];
            const table = document.getElementById('orders-table');
        const colgroup = document.getElementById('orders-colgroup');
            const searchForm = document.getElementById('orders-search-form');
            const searchInput = document.getElementById('filter-search');
            if (searchForm && searchInput) {
                let debounceId;
                searchInput.addEventListener('input', () => {
                    const value = searchInput.value || '';
                    const shouldSanitize = /[0-9]/.test(value) && !value.includes('@') && !value.toLowerCase().startsWith('tm');
                    if (shouldSanitize) {
                        const cleaned = value.replace(/[()\s-]+/g, '');
                        if (cleaned !== value) {
                            searchInput.value = cleaned;
                        }
                    }
                    window.clearTimeout(debounceId);
                    debounceId = window.setTimeout(() => {
                        searchForm.requestSubmit ? searchForm.requestSubmit() : searchForm.submit();
                    }, 300);
                });
            }

            if (!table || !colgroup) return;

            const showInlineCopyFeedback = (button, message = 'Copied') => {
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

            document.querySelectorAll('#orders-table button[data-copy]').forEach((button) => {
                const value = button.dataset.copy ?? '';
                const originalLabel = button.getAttribute('aria-label') || 'Copy';
                button.addEventListener('click', async () => {
                    if (!value) return;
                    try {
                        await navigator.clipboard.writeText(value);
                        button.setAttribute('aria-label', 'Copied');
                        showInlineCopyFeedback(button, 'Copied');
                    } catch (error) {
                        console.warn('Clipboard API failed, using fallback.', error);
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
                            showInlineCopyFeedback(button, 'Copied');
                        } catch (fallbackError) {
                            console.error('Fallback copy failed', fallbackError);
                            button.setAttribute('aria-label', 'Copy failed');
                            showInlineCopyFeedback(button, 'Copy failed');
                        } finally {
                            document.body.removeChild(textarea);
                        }
                    }
                    setTimeout(() => {
                        button.setAttribute('aria-label', originalLabel);
                    }, 1500);
                });
            });

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

            const ensureColgroup = () => {
                if (!colgroup.children.length) {
                    columnIds.forEach((id) => {
                        const col = document.createElement('col');
                        col.dataset.colId = id;
                        if (widths[id]) {
                            col.style.width = `${widths[id]}px`;
                        }
                        colgroup.appendChild(col);
                    });
                } else {
                    Array.from(colgroup.children).forEach((col, idx) => {
                        const id = columnIds[idx];
                        if (!id) return;
                        const width = widths[id];
                        col.dataset.colId = id;
                        col.style.width = width ? `${width}px` : '';
                    });
                }
            };

            const persist = () => {
                try {
                    localStorage.setItem(storageKey, JSON.stringify(widths));
                } catch (error) {
                    console.warn('Unable to save column widths', error);
                }
            };

            const applyWidths = () => {
                columnIds.forEach((id, idx) => {
                    const width = widths[id];
                    const col = colgroup.children[idx];
                    if (col) {
                        col.style.width = width ? `${width}px` : '';
                    }
                    const header = table.querySelector(`th[data-col-id="${id}"]`);
                    if (header) {
                        header.style.width = width ? `${width}px` : '';
                    }
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
                const colEl = colgroup.querySelector(`col[data-col-id="${colId}"]`);
                const startWidth = widths[colId]
                    ?? colEl?.getBoundingClientRect().width
                    ?? header?.getBoundingClientRect().width
                    ?? 140;

                const onMove = (moveEvent) => {
                    moveEvent.preventDefault();
                    const delta = moveEvent.pageX - startX;
                    const nextWidth = Math.max(80, startWidth + delta);
                    widths[colId] = nextWidth;
                    applyWidths();
                };

                const onUp = () => {
                    document.removeEventListener('mousemove', onMove);
                    document.removeEventListener('mouseup', onUp);
                    persist();
                };

                document.addEventListener('mousemove', onMove);
                document.addEventListener('mouseup', onUp);
            };

            ensureColgroup();
            applyWidths();
            setupResizers();
        })();
    </script>
@endpush
