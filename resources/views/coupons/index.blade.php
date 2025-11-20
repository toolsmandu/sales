@extends('layouts.app')

@php
    /** @var \Illuminate\Support\Collection|\App\Models\Product[] $products */
    /** @var \Illuminate\Pagination\LengthAwarePaginator|\App\Models\CouponCode[] $coupons */
    $initialEntries = collect(old('coupon_entries', [['code' => '', 'remarks' => '']]))
        ->map(fn ($entry) => [
            'code' => $entry['code'] ?? '',
            'remarks' => $entry['remarks'] ?? '',
        ])
        ->values();

    $selectedProduct = $products->firstWhere('id', (int) old('product_id', $filters['product'] ?? null));
    $filterProduct = $products->firstWhere('id', (int) ($filters['product'] ?? null));
    $couponCodeError = $errors->first('coupon_entries.*.code');
    $couponRemarksError = $errors->first('coupon_entries.*.remarks');
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
                                    <td>{{ $coupon->code }}</td>
                                    <td>{{ $coupon->product->name ?? 'N/A' }}</td>
                                    <td>{{ $coupon->remarks ?? '—' }}</td>
                                    <td>
                                        <div class="table-actions">
                                            <button
                                                type="button"
                                                class="ghost-button"
                                                data-coupon-edit-toggle="{{ $coupon->id }}"
                                            >
                                                Edit
                                            </button>
                                            <form
                                                method="POST"
                                                action="{{ route('coupons.destroy', $coupon) }}"
                                                data-coupon-delete-form
                                                data-confirm="Delete coupon {{ $coupon->code }}?"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="ghost-button button-danger">Delete</button>
                                            </form>
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
                        <span class="helper-text">
                            @if ($totalCoupons === 0)
                                No coupons to display
                            @else
                                Showing {{ $start }}-{{ $end }} of {{ $totalCoupons }} (Page {{ $currentPage }} of {{ $lastPage }})
                            @endif
                        </span>
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
        });
    </script>
@endpush
