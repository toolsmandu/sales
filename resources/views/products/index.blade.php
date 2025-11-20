@extends('layouts.app')

@push('styles')
    @include('partials.dashboard-styles')
    @include('partials.product-combobox-styles')
    <style>
        .stack {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .stock-toggle {
            border: 1px solid rgba(15, 23, 42, 0.12);
            border-radius: 0.65rem;
            padding: 0.85rem 1rem;
        }

        .stock-toggle legend {
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 0.35rem;
        }

        .stock-toggle__options {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .stock-toggle__option {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.95rem;
        }

        .products-table tbody tr + tr {
            border-top: 1px solid rgba(148, 163, 184, 0.25);
        }

        .products-table th:first-child,
        .products-table td:first-child {
            width: 6rem;
            min-width: 6rem;
        }

        .product-group-row td {
            background: rgba(79, 70, 229, 0.05);
            font-weight: 600;
            letter-spacing: 0.02em;
        }

        .product-group-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1rem;
        }

        .stock-pill {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 0.15rem 0.45rem;
            border-radius: 999px;
            background: rgba(15, 23, 42, 0.08);
            color: rgba(15, 23, 42, 0.65);
        }

        .stock-pill--in {
            background: rgba(16, 185, 129, 0.15);
            color: #047857;
        }

        .stock-pill--out {
            background: rgba(239, 68, 68, 0.15);
            color: #b91c1c;
        }

        .variation-row td:first-child {
            width: 6rem;
        }

        .variation-name {
            font-weight: 600;
        }

        .variation-empty {
            font-style: italic;
            color: rgba(15, 23, 42, 0.6);
        }

        .products-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .product-list-controls {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            align-items: center;
        }

        .product-stock-filter,
        .product-search {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .product-search__input {
            border-radius: 0.5rem;
            border: 1px solid rgba(148, 163, 184, 0.6);
            padding: 0.35rem 0.65rem;
            background: #fff;
            min-width: 220px;
        }

        .product-search__button {
            border: 1px solid rgba(148, 163, 184, 0.4);
            padding: 0.35rem 0.9rem;
        }

        .product-stock-filter__select {
            border-radius: 0.5rem;
            border: 1px solid rgba(148, 163, 184, 0.6);
            padding: 0.35rem 0.65rem;
            background: #fff;
        }
    </style>
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

            @unless ($productToEdit)
                <section class="card stack">
                    <h2>Add Product</h2>
                    <form method="POST" action="{{ route('dashboard.products.store') }}" class="form-grid">
                        @csrf
                        <label for="product-name">
                            Product name
                            <input
                                id="product-name"
                                type="text"
                                name="name"
                                value="{{ old('name') }}"
                                placeholder="Enter Product Name"
                                required>
                        </label>

                        @php
                            $createStockValue = old('is_in_stock', '1');
                        @endphp
                        <fieldset class="stock-toggle">
                            <legend>Product stock status</legend>
                            <div class="stock-toggle__options">
                                <label class="stock-toggle__option">
                                    <input
                                        type="radio"
                                        name="is_in_stock"
                                        value="1"
                                        @checked($createStockValue !== '0')>
                                    In stock
                                </label>
                                <label class="stock-toggle__option">
                                    <input
                                        type="radio"
                                        name="is_in_stock"
                                        value="0"
                                        @checked($createStockValue === '0')>
                                    Out of stock
                                </label>
                            </div>
                        </fieldset>

                        @include('products.partials.variation-fields', [
                            'fieldLabelId' => 'product-create-variation-label',
                            'values' => old('variations', []),
                        ])

                        <div class="form-actions">
                            <button type="submit">Save product</button>
                        </div>
                    </form>
                </section>
            @endunless

            @if ($productToEdit)
                <section class="card stack">
                    <h2>Edit Product</h2>

                    <form method="POST" action="{{ route('dashboard.products.update', $productToEdit) }}" class="form-grid">
                        @csrf
                        @method('PUT')
                        <label for="edit-product-name">
                            Product name
                            <input
                                id="edit-product-name"
                                type="text"
                                name="name"
                                value="{{ old('name', $productToEdit->name) }}"
                                required>
                        </label>

                        @php
                            $editStockValue = old('is_in_stock', $productToEdit->is_in_stock ? '1' : '0');
                        @endphp
                        <fieldset class="stock-toggle">
                            <legend>Product stock status</legend>
                            <div class="stock-toggle__options">
                                <label class="stock-toggle__option">
                                    <input
                                        type="radio"
                                        name="is_in_stock"
                                        value="1"
                                        @checked($editStockValue !== '0')>
                                    In stock
                                </label>
                                <label class="stock-toggle__option">
                                    <input
                                        type="radio"
                                        name="is_in_stock"
                                        value="0"
                                        @checked($editStockValue === '0')>
                                    Out of stock
                                </label>
                            </div>
                        </fieldset>

                        @include('products.partials.variation-fields', [
                            'fieldLabelId' => 'product-edit-variation-label',
                            'values' => old('variations', $productToEdit->variations->map(fn ($variation) => [
                                'name' => $variation->name,
                                'expiry_days' => $variation->expiry_days,
                                'is_in_stock' => $variation->is_in_stock,
                            ])->toArray()),
                        ])

                        <div class="form-actions">
                            <button type="submit">Update product</button>
                            <a href="{{ route('products.index') }}" class="ghost-button">Cancel</a>
                        </div>
                    </form>
                </section>
            @endif

            <section class="card">
                <div class="products-card-header">
                    <h2>Existing Products</h2>
                    <div class="product-list-controls">
                        <form method="GET" class="product-search" data-product-search-form>
                            @foreach (request()->except('search', 'page') as $param => $value)
                                @if (!is_array($value))
                                    <input type="hidden" name="{{ $param }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            <input
                                type="search"
                                id="product-search-input"
                                name="search"
                                class="product-search__input"
                                placeholder="Search products..."
                                value="{{ $search ?? '' }}"
                            >
                            <button type="submit" class="ghost-button product-search__button">Search</button>
                        </form>
                        <form method="GET" class="product-stock-filter">
                            @foreach (request()->except('stock_status', 'page') as $param => $value)
                                @if (!is_array($value))
                                    <input type="hidden" name="{{ $param }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            <label for="product-stock-filter" class="helper-text">Filter:</label>
                            <select
                                id="product-stock-filter"
                                name="stock_status"
                                class="product-stock-filter__select"
                                aria-label="Filter products by stock status"
                                onchange="this.form.submit()">
                                <option value="all" @selected(($stockStatus ?? 'all') === 'all')>All products</option>
                                <option value="in" @selected(($stockStatus ?? 'all') === 'in')>In stock</option>
                                <option value="out" @selected(($stockStatus ?? 'all') === 'out')>Out of stock</option>
                            </select>
                        </form>
                    </div>
                </div>

                @if (!empty($productOptions))
                    <div class="product-preview-list">
                        <div>
                        </div>
                        <div class="product-preview-list__dropdown product-combobox__dropdown">
                            @foreach ($productOptions as $option)
                                <div class="product-combobox__option" tabindex="-1">
                                    <span>{{ $option['label'] }}</span>
                                    <small>
                                        @if (!is_null($option['expiry_days']))
                                            {{ $option['expiry_days'] }} days
                                        @else
                                            No expiry
                                        @endif
                                    </small>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="table-wrapper">
                    <table class="products-table">
                        <thead>
                            <tr>
                                <th scope="col">S.N.</th>
                                <th scope="col">Product</th>
                                <th scope="col">Stock Status</th>
                                <th scope="col">Expiry</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($products as $product)
                                @php
                                    $rowNumber = ($products->firstItem() ?? 0) + $loop->index;
                                    $variations = $product->variations;
                                @endphp
                                <tr class="product-group-row">
                                    <td>{{ $rowNumber }}</td>
                                    <td colspan="4">
                                        <div class="product-group-title">
                                            <span>{{ $product->name }}</span>
                                        </div>
                                    </td>
                                </tr>
                                @forelse ($variations as $variation)
                                    @php
                                        $variationStockClass = $variation->is_in_stock ? 'stock-pill--in' : 'stock-pill--out';
                                        $variationStockLabel = $variation->is_in_stock ? 'In Stock' : 'Out of Stock';
                                        $expiryLabel = $variation->expiry_days ? "{$variation->expiry_days} Days" : '—';
                                    @endphp
                                    <tr class="variation-row">
                                        <td aria-hidden="true"></td>
                                        <td>
                                            <span class="variation-name">{{ $variation->name }}</span>
                                        </td>
                                        <td>
                                            <span class="stock-pill {{ $variationStockClass }}">{{ $variationStockLabel }}</span>
                                        </td>
                                        <td>{{ $expiryLabel }}</td>
                                        <td>
                                            <div class="table-actions">
                                                <a class="ghost-button" href="{{ route('products.index', ['edit' => $product->id]) }}">Edit</a>
                                                <form method="POST" action="{{ route('dashboard.products.destroy', $product) }}" onsubmit="return confirm('Delete {{ $product->name }}? This cannot be undone.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="ghost-button button-danger">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="variation-row">
                                        <td aria-hidden="true"></td>
                                        <td colspan="4" class="variation-empty">No variations added for this product.</td>
                                    </tr>
                                @endforelse
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <p class="helper-text">No products found. Add your first one above.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @php
                    $totalProducts = $products->total();
                    $currentPage = $products->currentPage();
                    $lastPage = $products->lastPage();
                    $start = $products->firstItem() ?? 0;
                    $end = $products->lastItem() ?? 0;
                @endphp

                <div class="table-controls">
                    <form method="GET" class="table-controls__page-size">
                        @foreach (request()->except('per_page', 'page', 'edit') as $param => $value)
                            @if (!is_array($value))
                                <input type="hidden" name="{{ $param }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        <label for="products-per-page">
        
                            <select
                                id="products-per-page"
                                name="per_page"
                                onchange="this.form.submit()">
                                @foreach ([25, 50, 100, 200] as $option)
                                    <option value="{{ $option }}" @selected(($perPage ?? $products->perPage()) === $option)>
                                        {{ $option }}
                                    </option>
                                @endforeach
                            </select>
                        
                        </label>
                    </form>
                    <div class="table-controls__pagination">
                        <a
                            class="ghost-button"
                            href="{{ $products->previousPageUrl() ? route('products.index', array_merge(request()->except('page'), ['page' => $currentPage - 1])) : '#' }}"
                            @class(['is-disabled' => !$products->previousPageUrl()])
                            aria-disabled="{{ $products->previousPageUrl() ? 'false' : 'true' }}">
                            Previous
                        </a>
                        <span class="helper-text">
                            @if ($totalProducts === 0)
                                No products to display
                            @else
                                Showing {{ $start }}-{{ $end }} of {{ $totalProducts }} (Page {{ $currentPage }} of {{ $lastPage }})
                            @endif
                        </span>
                        <a
                            class="ghost-button"
                            href="{{ $products->nextPageUrl() ? route('products.index', array_merge(request()->except('page'), ['page' => $currentPage + 1])) : '#' }}"
                            @class(['is-disabled' => !$products->nextPageUrl()])
                            aria-disabled="{{ $products->nextPageUrl() ? 'false' : 'true' }}">
                            Next
                        </a>
                    </div>
                </div>
            </section>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchForm = document.querySelector('[data-product-search-form]');
            const searchInput = searchForm?.querySelector('input[name="search"]');
            let debounceId;

            if (searchForm && searchInput) {
                const submitSearch = () => {
                    if (typeof searchForm.requestSubmit === 'function') {
                        searchForm.requestSubmit();
                    } else {
                        searchForm.submit();
                    }
                };

                searchInput.addEventListener('input', () => {
                    window.clearTimeout(debounceId);
                    debounceId = window.setTimeout(() => {
                        submitSearch();
                    }, 400);
                });
            }
        });
    </script>
@endpush
       
