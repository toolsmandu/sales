@extends('layouts.app')

@push('styles')
    @include('stock.partials.styles')
    @include('partials.product-combobox-styles')
@endpush

@section('content')
    <div class="dashboard-grid" id="stock-root">
        @include('partials.dashboard-sidebar')

        <section class="dashboard-content">
            <div class="stock-layout">
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

                @php
                    $productOptions = $productOptions ?? [];
                @endphp

                <section class="card stock-card stock-form">
                    <header>
                        <h2>Add Activation Keys</h2>
                    </header>

                    @php
                        $selectedProductName = old('product_search', '');
                        if ($selectedProductName === '' && $productOptions ?? false) {
                            $match = collect($productOptions)->firstWhere('id', (int) old('product_id'));
                            $selectedProductName = $match['label'] ?? '';
                        }
                    @endphp

                    <form method="POST" action="{{ route('stock.keys.store') }}" class="form-grid">
                        @csrf

                        <div class="product-combobox" data-product-combobox>
                            <label for="stock-product-input">
                                Product
                                <input
                                    type="text"
                                    id="stock-product-input"
                                    class="product-combobox__input"
                                    name="product_search"
                                    value="{{ $selectedProductName }}"
                                    placeholder="Choose product..."
                                    autocomplete="off"
                                    data-selected-name="{{ $selectedProductName }}"
                                    {{ empty($productOptions) ? 'disabled' : '' }}
                                    required
                                >
                            </label>
                            <input type="hidden" name="product_id" value="{{ old('product_id') }}" data-product-selected>

                            <div class="product-combobox__dropdown" role="listbox" aria-label="Product options">
                                @if (empty($productOptions))
                                    <p class="product-combobox__empty">No products available yet.</p>
                                @else
                                    <p class="product-combobox__empty" data-empty-message hidden>No matching products found.</p>
                                    @foreach ($productOptions as $option)
                                        @php
                                            $isActive = $selectedProductName !== '' && $selectedProductName === $option['label'];
                                        @endphp
                                        <button
                                            type="button"
                                            class="product-combobox__option {{ $isActive ? 'is-active' : '' }}"
                                            data-product-option
                                            data-product-id="{{ $option['id'] }}"
                                            data-product-name="{{ $option['label'] }}"
                                            role="option"
                                            aria-selected="{{ $isActive ? 'true' : 'false' }}"
                                        >
                                            {{ $option['label'] }}
                                        </button>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <label for="stock-keys">
                            Activation keys <small>(one per line)</small>
                            <textarea
                                id="stock-keys"
                                name="keys"
                                rows="5"
                                placeholder="LICENSE-KEY-123&#10;LICENSE-KEY-456"
                                required>{{ old('keys') }}</textarea>
                        </label>

                        <label for="stock-view-limit">
                            View limit per key
                            <input
                                type="number"
                                min="1"
                                max="1000"
                                id="stock-view-limit"
                                name="view_limit"
                                value="{{ old('view_limit', 1) }}"
                            >
                            <small>How many times a key can be viewed before it moves to Viewed. Default: 1.</small>
                        </label>

                        <div class="form-actions">
                            <button type="submit">Save</button>
                        </div>
                    </form>
                </section>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    @include('partials.product-combobox-scripts')
@endpush
