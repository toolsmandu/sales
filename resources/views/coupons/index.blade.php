@extends('layouts.app')

@php
    /** @var \Illuminate\Support\Collection|\App\Models\Product[] $products */
    /** @var \Illuminate\Pagination\LengthAwarePaginator|\App\Models\CouponCode[] $coupons */
    $initialEntries = collect(old('coupon_entries', [['code' => '', 'remarks' => '', 'instructions' => '']]))
        ->map(fn ($entry) => [
            'code' => $entry['code'] ?? '',
            'remarks' => $entry['remarks'] ?? '',
            'instructions' => $entry['instructions'] ?? '',
        ])
        ->values();

    $selectedProduct = $products->firstWhere('id', (int) old('product_id', $filters['product'] ?? null));
    $filterProduct = $products->firstWhere('id', (int) ($filters['product'] ?? null));
    $couponCodeError = $errors->first('coupon_entries.*.code');
    $couponRemarksError = $errors->first('coupon_entries.*.remarks');
    $couponInstructionsError = $errors->first('coupon_entries.*.instructions');
    $isEmployee = auth()->user()?->role === 'employee';
@endphp

@push('styles')
    @include('partials.dashboard-styles')
    @include('partials.product-combobox-styles')
    <style>
        .coupon-layout {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .coupon-form {
            display: grid;
            gap: 1.25rem;
        }

        .coupon-entries {
            display: grid;
            gap: 0.75rem;
        }

        .coupon-entry {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 0.85rem;
            padding: 0.75rem;
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 0.75rem;
            background: rgba(15, 23, 42, 0.02);
        }

        .coupon-entry__actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }

        .coupon-filters {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0.75rem;
            align-items: end;
        }

        .coupon-filters .product-combobox {
            max-width: 300px;
        }

        .coupon-filters__actions {
            display: flex;
            gap: 0.5rem;
            margin: 5px;
        }

 
        .coupon-table td {
            vertical-align: top;
        }

        .coupon-edit-row {
            background: rgba(15, 23, 42, 0.03);
        }

        .coupon-inline-form {
            display: grid;
            gap: 0.85rem;
        }

        .coupon-code {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .copy-instructions-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            border: 1px solid rgba(79, 70, 229, 0.25);
            background: linear-gradient(180deg, rgba(79, 70, 229, 0.07), rgba(79, 70, 229, 0.02));
            color: #4338ca;
            font-weight: 600;
            font-size: 0.92rem;
            text-decoration: none;
            transition: background 0.15s ease, border-color 0.15s ease, transform 0.12s ease, box-shadow 0.15s ease;
        }

        .copy-instructions-btn svg {
            width: 18px;
            height: 18px;
        }

        .copy-instructions-btn:hover,
        .copy-instructions-btn:focus-visible {
            background: linear-gradient(180deg, rgba(79, 70, 229, 0.12), rgba(79, 70, 229, 0.05));
            border-color: rgba(79, 70, 229, 0.45);
            transform: translateY(-1px);
            box-shadow: 0 8px 18px rgba(79, 70, 229, 0.15);
            outline: none;
        }

        .coupon-inline-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0.75rem;
        }

        .coupon-inline-grid .product-combobox {
            width: 100%;
        }

        .form-error {
            color: #b91c1c;
            font-size: 0.85rem;
        }

        .ghost-button--danger {
            border-color: rgba(239, 68, 68, 0.35);
            background: rgba(239, 68, 68, 0.08);
            color: #b91c1c;
        }

        .ghost-button--danger:hover,
        .ghost-button--danger:focus-visible {
            background: rgba(239, 68, 68, 0.18);
            border-color: rgba(239, 68, 68, 0.6);
            color: #7f1d1d;
            outline: none;
        }

        .is-hidden {
            display: none;
        }

        .coupon-code .cell-action-button {
            padding: 0.35rem;
            border-radius: 0.45rem;
            background: rgba(79, 70, 229, 0.06);
            color: #4338ca;
            border: 1px solid rgba(79, 70, 229, 0.2);
            transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease, border-color 0.15s ease;
        }

        .coupon-code .cell-action-button:hover,
        .coupon-code .cell-action-button:focus-visible {
            transform: translateY(-1px);
            background: rgba(79, 70, 229, 0.12);
            border-color: rgba(79, 70, 229, 0.4);
            box-shadow: 0 10px 18px rgba(15, 23, 42, 0.12);
            outline: none;
        }
    </style>
@endpush

@section('content')
    <div class="dashboard-grid">
        @include('partials.dashboard-sidebar')

        <section class="dashboard-content coupon-layout">
            @if (session('status'))
                <article role="status">
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

            <section class="card">
                <header class="stack" style="gap: 0.35rem;">
                    <h2>Add Coupon Code</h2>
                </header>

                <form method="POST" action="{{ route('coupons.store') }}" class="coupon-form">
                    @csrf

                    <div class="product-combobox" data-product-combobox>
                        <label for="coupon-product-input">
                            Product
                            <input
                                type="text"
                                id="coupon-product-input"
                                class="product-combobox__input"
                                name="product_search"
                                value="{{ old('product_search', $selectedProduct->name ?? '') }}"
                                placeholder="Choose product..."
                                autocomplete="off"
                                data-selected-name="{{ $selectedProduct->name ?? '' }}"
                                {{ $products->isEmpty() ? 'disabled' : '' }}
                                required
                            >
                        </label>
                        <input type="hidden" name="product_id" value="{{ old('product_id', $selectedProduct->id ?? '') }}" data-product-selected>

                        <div class="product-combobox__dropdown" role="listbox" aria-label="Product options">
                            @if ($products->isEmpty())
                                <p class="product-combobox__empty">No products available yet.</p>
                            @else
                                <p class="product-combobox__empty" data-empty-message hidden>No matching products found.</p>
                                @foreach ($products as $product)
                                    <button
                                        type="button"
                                        class="product-combobox__option {{ $selectedProduct && $selectedProduct->id === $product->id ? 'is-active' : '' }}"
                                        data-product-option
                                        data-product-id="{{ $product->id }}"
                                        data-product-name="{{ $product->name }}"
                                        role="option"
                                        aria-selected="{{ $selectedProduct && $selectedProduct->id === $product->id ? 'true' : 'false' }}"
                                    >
                                        {{ $product->name }}
                                    </button>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <div class="coupon-entries" data-coupon-entries data-next-index="{{ $initialEntries->count() }}">
                        @foreach ($initialEntries as $index => $entry)
                            <div class="coupon-entry" data-coupon-entry>
                                <label>
                                    Coupon Code
                                    <input
                                        type="text"
                                        name="coupon_entries[{{ $index }}][code]"
                                        value="{{ $entry['code'] }}"
                                        placeholder="XXXXX"
                                        required
                                    >
                                </label>
                                <label>
                                    Remarks
                                    <input
                                        type="text"
                                        name="coupon_entries[{{ $index }}][remarks]"
                                        value="{{ $entry['remarks'] }}"
                                        placeholder="Remarks"
                                    >
                                </label>
                                <label style="grid-column: 1 / -1;">
                                    Instructions
                                    <textarea
                                        name="coupon_entries[{{ $index }}][instructions]"
                                        rows="3"
                                        placeholder="Instructions shown when copying this coupon">{{ $entry['instructions'] }}</textarea>
                                </label>
                                <div class="coupon-entry__actions">
                                    <button
                                        type="button"
                                        class="ghost-button ghost-button--danger"
                                        data-coupon-entry-remove
                                        {{ $initialEntries->count() === 1 ? 'hidden' : '' }}
                                    >
                                        Remove
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div>
                        <button type="button" class="ghost-button" data-coupon-entry-add>Add another coupon</button>
                        @error('coupon_entries')
                            <p class="form-error">{{ $message }}</p>
                        @enderror
                        @if ($couponCodeError)
                            <p class="form-error">{{ $couponCodeError }}</p>
                        @endif
                        @if ($couponRemarksError)
                            <p class="form-error">{{ $couponRemarksError }}</p>
                        @endif
                        @if ($couponInstructionsError)
                            <p class="form-error">{{ $couponInstructionsError }}</p>
                        @endif
                    </div>

                    <div class="form-actions">
                        <button type="submit" {{ $products->isEmpty() ? 'disabled' : '' }}>Save Coupon</button>
                    </div>
                </form>
            </section>

                <header class="stack" style="gap: 0.75rem;">
                    <div>
                        <h2>Coupon Code Library</h2>
                    </div>

                    <form method="GET" class="coupon-filters">
                        <div
                            class="product-combobox"
                            data-product-combobox
                            data-allow-empty="true"
                        >
                            <label for="coupon-filter-product">
                                Product
                                <input
                                    type="text"
                                    id="coupon-filter-product"
                                    class="product-combobox__input"
                                    name="filter_product_name"
                                    value="{{ $filterProduct->name ?? '' }}"
                                    placeholder="All products"
                                    autocomplete="off"
                                    data-selected-name="{{ $filterProduct->name ?? '' }}"
                                >
                            </label>
                            <input
                                type="hidden"
                                name="filter_product"
                                value="{{ $filterProduct->id ?? '' }}"
                                data-product-selected
                            >

                            <div class="product-combobox__dropdown" role="listbox" aria-label="Product options">
                                @if ($products->isEmpty())
                                    <p class="product-combobox__empty">No products available yet.</p>
                                @else
                                    <p class="product-combobox__empty" data-empty-message hidden>No matching products found.</p>
                                    @foreach ($products as $product)
                                        @php
                                            $isActiveFilterProduct = $filterProduct && $filterProduct->id === $product->id;
                                        @endphp
                                        <button
                                            type="button"
                                            class="product-combobox__option {{ $isActiveFilterProduct ? 'is-active' : '' }}"
                                            data-product-option
                                            data-product-id="{{ $product->id }}"
                                            data-product-name="{{ $product->name }}"
                                            role="option"
                                            aria-selected="{{ $isActiveFilterProduct ? 'true' : 'false' }}"
                                        >
                                            {{ $product->name }}
                                        </button>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <div class="coupon-filters__actions">
                            <button type="submit">Filter</button>
                        </div>
                    </form>
                </header>

                <div class="table-wrapper">
                    <table class="coupon-table">
                        <thead>
                            <tr>
                                <th scope="col">Coupon Code</th>
                                <th scope="col">Product</th>
                                <th scope="col">Remarks</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($coupons as $coupon)
                                <tr>
                                    <td>
                                        <div class="coupon-code">
                                            <span>{{ $coupon->code }}</span>
                                            <button
                                                type="button"
                                                class="cell-action-button"
                                                data-copy="{{ $coupon->code }}"
                                                aria-label="Copy coupon {{ $coupon->code }}"
                                            >
                                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                                    <path d="M8 7V5a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2h-2" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                    <rect x="4" y="7" width="12" height="12" rx="2" ry="2" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                            </button>
                                            @if ($coupon->instructions)
                                                <button
                                                    type="button"
                                                    class="copy-instructions-btn"
                                                    data-copy-template="{{ $coupon->instructions }}"
                                                    data-copy-code="{{ $coupon->code }}"
                                                    title="Copy Instructions"
                                                    aria-label="Copy instructions for {{ $coupon->code }}"
                                                >
                                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                                        <path d="M8 7V5a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2h-2" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                        <rect x="5" y="7" width="11" height="12" rx="2" ry="2" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                        <path d="M8.5 11H15M8.5 14H13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                    <span>Copy Instructions</span>
                                                </button>
                                            @endif
                                            <span class="copy-inline-feedback"></span>
                                        </div>
                                    </td>
                                    <td>{{ $coupon->product->name ?? 'N/A' }}</td>
                                    <td>{{ $coupon->remarks ?? '—' }}</td>
                                    <td>
                                        <div class="table-actions">
                                            <button
                                                type="button"
                                                class="icon-button"
                                                data-coupon-edit-toggle="{{ $coupon->id }}"
                                                aria-label="Edit coupon {{ $coupon->code }}"
                                            >
                                              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M345 273c9.4-9.4 9.4-24.6 0-33.9L201 95c-6.9-6.9-17.2-8.9-26.2-5.2S160 102.3 160 112l0 80-112 0c-26.5 0-48 21.5-48 48l0 32c0 26.5 21.5 48 48 48l112 0 0 80c0 9.7 5.8 18.5 14.8 22.2s19.3 1.7 26.2-5.2L345 273zm7 143c-17.7 0-32 14.3-32 32s14.3 32 32 32l64 0c53 0 96-43 96-96l0-256c0-53-43-96-96-96l-64 0c-17.7 0-32 14.3-32 32s14.3 32 32 32l64 0c17.7 0 32 14.3 32 32l0 256c0 17.7-14.3 32-32 32l-64 0z"/></svg>
                                            </button>
                                            @unless ($isEmployee)
                                                <form
                                                    method="POST"
                                                    action="{{ route('coupons.destroy', $coupon) }}"
                                                    data-coupon-delete-form
                                                    data-confirm="Delete coupon {{ $coupon->code }}?"
                                                >
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="icon-button icon-button--danger" aria-label="Delete coupon {{ $coupon->code }}">
                                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                                            <path d="M6 7h12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                                            <path d="M10 11v6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                                            <path d="M14 11v6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                                            <path d="M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                                            <path d="M19 7l-.6 10.2A2 2 0 0116.41 19H7.59a2 2 0 01-1.99-1.8L5 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endunless
                                        </div>
                                    </td>
                                </tr>
                                <tr class="coupon-edit-row is-hidden" data-coupon-edit-form="{{ $coupon->id }}">
                                    <td colspan="4">
                                        <form method="POST" action="{{ route('coupons.update', $coupon) }}" class="coupon-inline-form">
                                            @csrf
                                            @method('PUT')
                                            <div class="coupon-inline-grid">
                                                <div class="product-combobox" data-product-combobox>
                                                    <label for="coupon-edit-product-{{ $coupon->id }}">
                                                        Product
                                                        <input
                                                            type="text"
                                                            id="coupon-edit-product-{{ $coupon->id }}"
                                                            class="product-combobox__input"
                                                            name="product_search"
                                                            value="{{ $coupon->product->name ?? '' }}"
                                                            placeholder="Choose product..."
                                                            autocomplete="off"
                                                            data-selected-name="{{ $coupon->product->name ?? '' }}"
                                                            required
                                                        >
                                                    </label>
                                                    <input
                                                        type="hidden"
                                                        name="product_id"
                                                        value="{{ $coupon->product_id }}"
                                                        data-product-selected
                                                    >

                                                    <div class="product-combobox__dropdown" role="listbox" aria-label="Product options">
                                                        @if ($products->isEmpty())
                                                            <p class="product-combobox__empty">No products available yet.</p>
                                                        @else
                                                            <p class="product-combobox__empty" data-empty-message hidden>No matching products found.</p>
                                                            @foreach ($products as $product)
                                                                @php
                                                                    $isActiveProduct = $product->id === $coupon->product_id;
                                                                @endphp
                                                                <button
                                                                    type="button"
                                                                    class="product-combobox__option {{ $isActiveProduct ? 'is-active' : '' }}"
                                                                    data-product-option
                                                                    data-product-id="{{ $product->id }}"
                                                                    data-product-name="{{ $product->name }}"
                                                                    role="option"
                                                                    aria-selected="{{ $isActiveProduct ? 'true' : 'false' }}"
                                                                >
                                                                    {{ $product->name }}
                                                                </button>
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                </div>
                                                <label>
                                                    Coupon Code
                                                    <input type="text" name="code" value="{{ $coupon->code }}" required>
                                                </label>
                                                <label>
                                                    Remarks
                                                    <input type="text" name="remarks" value="{{ $coupon->remarks }}">
                                                </label>
                                                <label style="grid-column: 1 / -1;">
                                                    Instructions
                                                    <textarea name="instructions" rows="3" placeholder="Instructions shown when copying this coupon">{{ $coupon->instructions }}</textarea>
                                                </label>
                                            </div>
                                            <div class="form-actions form-actions--row">
                                                <button type="submit">Save changes</button>
                                                <button type="button" class="ghost-button" data-coupon-edit-cancel="{{ $coupon->id }}">Cancel</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">
                                        <p class="helper-text">No coupons found for the selected filters.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @php
                    $totalCoupons = $coupons->total();
                    $currentPage = $coupons->currentPage();
                    $lastPage = $coupons->lastPage();
                    $start = $coupons->firstItem() ?? 0;
                    $end = $coupons->lastItem() ?? 0;
                @endphp

                <div class="table-controls">
                    <div class="table-controls__pagination">
                        <a
                            class="ghost-button"
                            href="{{ $coupons->previousPageUrl() ? $coupons->previousPageUrl() : '#' }}"
                            @class(['is-disabled' => !$coupons->previousPageUrl()])
                            aria-disabled="{{ $coupons->previousPageUrl() ? 'false' : 'true' }}"
                        >
                            Previous
                        </a>
                        <div class="pagination-numbers" style="display: inline-flex; gap: 0.25rem; align-items: center;">
                            @for ($i = 1; $i <= $lastPage; $i++)
                                <a
                                    class="ghost-button {{ $i === $currentPage ? 'is-active' : '' }}"
                                    href="{{ route('coupons.index', array_merge(request()->except('page'), ['page' => $i])) }}"
                                    aria-current="{{ $i === $currentPage ? 'page' : 'false' }}">
                                    {{ $i }}
                                </a>
                            @endfor
                        </div>
                        <a
                            class="ghost-button"
                            href="{{ $coupons->nextPageUrl() ? $coupons->nextPageUrl() : '#' }}"
                            @class(['is-disabled' => !$coupons->nextPageUrl()])
                            aria-disabled="{{ $coupons->nextPageUrl() ? 'false' : 'true' }}"
                        >
                            Next
                        </a>
                    </div>
                </div>
            </section>
        </section>
    </div>

    <template id="coupon-entry-template">
        <div class="coupon-entry" data-coupon-entry>
            <label>
                Coupon Code
                <input
                    type="text"
                    data-name="coupon_entries[__INDEX__][code]"
                    placeholder="XXXXX"
                    required
                >
            </label>
            <label>
                Remarks
                <input
                    type="text"
                    data-name="coupon_entries[__INDEX__][remarks]"
                    placeholder="Remarks"
                >
            </label>
            <label style="grid-column: 1 / -1;">
                Instructions
                <textarea
                    data-name="coupon_entries[__INDEX__][instructions]"
                    rows="3"
                    placeholder="Instructions shown when copying this coupon"></textarea>
            </label>
            <div class="coupon-entry__actions">
                <button
                    type="button"
                    class="ghost-button ghost-button--danger"
                    data-coupon-entry-remove
                >
                    Remove
                </button>
            </div>
        </div>
    </template>
@endsection

@push('scripts')
    @include('partials.product-combobox-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const entriesWrapper = document.querySelector('[data-coupon-entries]');
            const template = document.getElementById('coupon-entry-template');
            const addEntryButton = document.querySelector('[data-coupon-entry-add]');

            const refreshRemoveButtons = () => {
                if (!entriesWrapper) {
                    return;
                }
                const rows = entriesWrapper.querySelectorAll('[data-coupon-entry]');
                rows.forEach((row) => {
                    const removeButton = row.querySelector('[data-coupon-entry-remove]');
                    if (removeButton) {
                        removeButton.hidden = rows.length === 1;
                    }
                });
            };

            addEntryButton?.addEventListener('click', () => {
                if (!entriesWrapper || !template?.content?.firstElementChild) {
                    return;
                }

                const nextIndex = Number(entriesWrapper.dataset.nextIndex || entriesWrapper.children.length);
                const clone = template.content.firstElementChild.cloneNode(true);
                clone.querySelectorAll('[data-name]').forEach((input) => {
                    const templateName = input.dataset.name;
                    if (templateName) {
                        input.setAttribute('name', templateName.replace(/__INDEX__/g, String(nextIndex)));
                    }
                });

                entriesWrapper.appendChild(clone);
                entriesWrapper.dataset.nextIndex = String(nextIndex + 1);
                refreshRemoveButtons();
            });

            entriesWrapper?.addEventListener('click', (event) => {
                const button = event.target.closest('[data-coupon-entry-remove]');
                if (!button || !entriesWrapper) {
                    return;
                }
                const row = button.closest('[data-coupon-entry]');
                if (!row) {
                    return;
                }
                row.remove();
                refreshRemoveButtons();
            });

            refreshRemoveButtons();

            document.querySelectorAll('[data-coupon-edit-toggle]').forEach((button) => {
                button.addEventListener('click', () => {
                    const id = button.dataset.couponEditToggle;
                    const formRow = document.querySelector(`[data-coupon-edit-form="${id}"]`);
                    formRow?.classList.toggle('is-hidden');
                });
            });

            document.querySelectorAll('[data-coupon-edit-cancel]').forEach((button) => {
                button.addEventListener('click', () => {
                    const id = button.dataset.couponEditCancel;
                    const formRow = document.querySelector(`[data-coupon-edit-form="${id}"]`);
                    formRow?.classList.add('is-hidden');
                });
            });

            document.querySelectorAll('[data-coupon-delete-form]').forEach((form) => {
                form.addEventListener('submit', (event) => {
                    const message = form.dataset.confirm || 'Delete this coupon?';
                    if (!window.confirm(message)) {
                        event.preventDefault();
                    }
                });
            });

            document.querySelectorAll('button[data-copy], button[data-copy-template]').forEach((button) => {
                const originalLabel = button.getAttribute('aria-label') || 'Copy';
                const feedback = button.nextElementSibling && button.nextElementSibling.classList.contains('copy-inline-feedback')
                    ? button.nextElementSibling
                    : null;

                const setFeedback = (text) => {
                    if (!feedback) return;
                    feedback.textContent = text;
                    window.clearTimeout(button._copyIndicatorTimeout);
                    button._copyIndicatorTimeout = window.setTimeout(() => {
                        feedback.textContent = '';
                    }, 1500);
                };

                button.addEventListener('click', async () => {
                    let text = button.dataset.copy || '';
                    const template = button.dataset.copyTemplate || null;
                    const code = button.dataset.copyCode || '';

                    if (template) {
                        text = template.replaceAll('{key}', code);
                    }

                    if (!text) {
                        return;
                    }
                    try {
                        await navigator.clipboard.writeText(text);
                        button.setAttribute('aria-label', 'Copied');
                        setFeedback('Copied');
                    } catch (error) {
                        console.error('Unable to copy coupon code', error);
                        button.setAttribute('aria-label', 'Copy failed');
                        setFeedback('Copy failed');
                    }
                    setTimeout(() => {
                        button.setAttribute('aria-label', originalLabel);
                    }, 1500);
                });
            });
        });
    </script>
@endpush
