@extends('layouts.app')

@push('styles')
    @include('partials.dashboard-styles')
    <style>
        .stack {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
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

                        @include('products.partials.variation-fields', [
                            'fieldLabelId' => 'product-edit-variation-label',
                            'values' => old('variations', $productToEdit->variations->pluck('name')->all()),
                        ])

                        <div class="form-actions">
                            <button type="submit">Update product</button>
                            <a href="{{ route('products.index') }}" class="ghost-button">Cancel</a>
                        </div>
                    </form>
                </section>
            @endif

            <section class="card">
                <h2>Existing Products</h2>

                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Product</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($products as $product)
                                <tr>
                                    <td>{{ ($products->firstItem() ?? 0) + $loop->index }}</td>
                                    <td>
                                        <div>{{ $product->name }}</div>
                                        @if ($product->variations->isNotEmpty())
                                            <div class="helper-text">
                                                Variations: {{ $product->variations->pluck('name')->implode(', ') }}
                                            </div>
                                        @endif
                                    </td>
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
                                <tr>
                                    <td colspan="3">
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
