@extends('layouts.app')

@push('styles')
    @include('stock.partials.styles')
    @include('partials.product-combobox-styles')
@endpush

@section('content')
    <div class="dashboard-grid" id="stock-root" data-is-admin="{{ auth()->user()?->role === 'admin' ? '1' : '0' }}">
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

                

                

                <section class="card stock-card">
                    <header>
                        <h2>Get Activation Key from Stock</h2>
                    </header>

                    @php
                        $searchableProducts = $products ?? collect();
                    @endphp

                    <div class="product-combobox stock-search" data-product-combobox data-stock-search data-allow-free-entry="true">
                        <label for="stock-search">
                            Search by product name
                            <input
                                type="text"
                                id="stock-search"
                                class="product-combobox__input"
                                placeholder="Start typing to filter keys"
                                autocomplete="off"
                                data-selected-name=""
                            >
                        </label>

                        <div class="product-combobox__dropdown" role="listbox" aria-label="Product options">
                            @if ($searchableProducts->isEmpty())
                                <p class="product-combobox__empty">No products available yet.</p>
                            @else
                                <p class="product-combobox__empty" data-empty-message hidden>No matching products found.</p>
                                @foreach ($searchableProducts as $product)
                                    <button
                                        type="button"
                                        class="product-combobox__option"
                                        data-product-option
                                        data-product-name="{{ $product->name }}"
                                        data-product-id="{{ $product->id }}"
                                        role="option"
                                        aria-selected="false"
                                    >
                                        {{ $product->name }}
                                    </button>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <div class="stock-tabs" role="tablist">
                        <button
                            type="button"
                            class="stock-tab is-active"
                            data-stock-tab="fresh"
                            role="tab"
                            aria-selected="true"
                            aria-controls="stock-panel-fresh"
                            id="stock-tab-fresh"
                            tabindex="0"
                        >
                            Fresh Keys
                            <span class="stock-tab__count" data-stock-count="fresh">{{ $freshKeys->count() }}</span>
                        </button>
                        <button
                            type="button"
                            class="stock-tab"
                            data-stock-tab="viewed"
                            role="tab"
                            aria-selected="false"
                            aria-controls="stock-panel-viewed"
                            id="stock-tab-viewed"
                            tabindex="-1"
                        >
                            Viewed Keys
                            <span class="stock-tab__count" data-stock-count="viewed">{{ $viewedKeys->count() }}</span>
                        </button>
                    </div>

                    <div class="stock-panels">
                        <section
                            class="stock-panel is-active"
                            data-stock-panel="fresh"
                            id="stock-panel-fresh"
                            role="tabpanel"
                            aria-labelledby="stock-tab-fresh"
                        >
                            <ul class="stock-list" id="fresh-key-list" data-stock-list>
                                @if ($freshKeys->isNotEmpty())
                                    @foreach ($freshKeys as $key)
                                        <li
                                            class="stock-item"
                                            data-stock-item
                                            data-panel="fresh"
                                            data-product="{{ $key->product->name }}"
                                            data-key="{{ $key->masked_activation_key }}"
                                            data-activation="{{ $key->original_activation_key }}"
                                            data-remarks=""
                                            data-edit-url="{{ route('stock.keys.update', $key) }}"
                                            data-delete-url="{{ route('stock.keys.destroy', $key) }}"
                                            data-stock-id="{{ $key->id }}"
                                        >
                                            <div class="stock-item__details">
                                                <span class="stock-item__product">{{ $key->product->name }}</span>
                                                <span class="stock-item__value">{{ $key->masked_activation_key }}</span>
                                                <span class="stock-item__timestamp">
                                                    Added {{ optional($key->created_at)?->setTimezone('Asia/Kathmandu')->format('M j, Y g:i A') }}
                                                </span>
                                            </div>
                                            <div class="stock-item__actions">
                                                <button
                                                    type="button"
                                                    class="stock-action-button"
                                                    data-stock-reveal
                                                    data-reveal-url="{{ route('stock.keys.reveal', $key) }}"
                                                    data-stock-id="{{ $key->id }}"
                                                    aria-label="Reveal key for {{ $key->product->name }}"
                                                >
                                                    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                                        <path
                                                            d="M12 5C7 5 2.73 8.11 1 12c1.73 3.89 6 7 11 7s9.27-3.11 11-7c-1.73-3.89-6-7-11-7Zm0 10a3 3 0 1 1 0-6 3 3 0 0 1 0 6Z"
                                                            fill="currentColor"
                                                        />
                                                    </svg>
                                                </button>
                                                @if (auth()->user()?->role === 'admin')
                                                    <button type="button" class="stock-action-button" data-stock-edit aria-label="Edit key {{ $key->product->name }}">
                                                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                                            <path d="M4 15.5V20h4.5L19 9.5l-4.5-4.5L4 15.5z" fill="currentColor"/>
                                                        </svg>
                                                    </button>
                                                    <button type="button" class="stock-action-button stock-action-button--danger" data-stock-delete aria-label="Delete key {{ $key->product->name }}">
                                                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                                            <path d="M6 7h12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                            <path d="M10 11v6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                            <path d="M14 11v6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                            <path d="M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                        </svg>
                                                    </button>
                                                @endif
                                            </div>
                                        </li>
                                    @endforeach
                                @endif
                            </ul>

                            <p
                                id="fresh-empty"
                                class="stock-empty"
                                data-stock-empty
                                @if ($freshKeys->isNotEmpty()) hidden @endif
                            >
                                No fresh keys available. Add new keys to see them here.
                            </p>
                        </section>

                        <section
                            class="stock-panel"
                            data-stock-panel="viewed"
                            id="stock-panel-viewed"
                            role="tabpanel"
                            aria-labelledby="stock-tab-viewed"
                        >
                            <ul class="stock-list" id="viewed-key-list" data-stock-list>
                                @if ($viewedKeys->isNotEmpty())
                                    @foreach ($viewedKeys as $key)
                                        <li
                                            class="stock-item"
                                            data-stock-item
                                            data-panel="viewed"
                                            data-product="{{ $key->product->name }}"
                                            data-key="{{ $key->activation_key }}"
                                            data-activation="{{ $key->activation_key }}"
                                            data-viewer="{{ $key->viewedBy?->name }}"
                                            data-remarks="{{ $key->viewed_remarks }}"
                                            data-edit-url="{{ route('stock.keys.update', $key) }}"
                                            data-delete-url="{{ route('stock.keys.destroy', $key) }}"
                                            data-stock-id="{{ $key->id }}"
                                        >
                                            <div class="stock-item__details">
                                                <span class="stock-item__product">{{ $key->product->name }}</span>
                                                <span class="stock-item__value">{{ $key->activation_key }}</span>
                                                @php
                                                    $viewedAt = optional($key->viewed_at)?->setTimezone('Asia/Kathmandu');
                                                    $viewerLabel = $key->viewedBy?->name ?? '—';
                                                    $remarks = $key->viewed_remarks ?: '—';
                                                @endphp
                                                <span class="stock-item__timestamp">
                                                    Viewed on: {{ $viewedAt ? $viewedAt->format('M j, Y g:i A') : '—' }} | Viewed by: {{ $viewerLabel }} | Remarks: {{ $remarks }}
                                                </span>
                                            </div>
                                            @if (auth()->user()?->role === 'admin')
                                                <div class="stock-item__actions">
                                                    <button type="button" class="stock-action-button" data-stock-edit aria-label="Edit key {{ $key->product->name }}">
                                                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                                            <path d="M4 15.5V20h4.5L19 9.5l-4.5-4.5L4 15.5z" fill="currentColor"/>
                                                        </svg>
                                                    </button>
                                                    <button type="button" class="stock-action-button stock-action-button--danger" data-stock-delete aria-label="Delete key {{ $key->product->name }}">
                                                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                                            <path d="M6 7h12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                            <path d="M10 11v6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                            <path d="M14 11v6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                            <path d="M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            @endif
                                        </li>
                                    @endforeach
                                @endif
                            </ul>

                            <p
                                id="viewed-empty"
                                class="stock-empty"
                                data-stock-empty
                                @if ($viewedKeys->isNotEmpty()) hidden @endif
                            >
                                No keys have been viewed yet.
                            </p>
                        </section>
                    </div>
                </section>
            </div>
        </section>
    </div>

    <section class="stock-pin-modal" id="stock-pin-modal" hidden aria-modal="true" role="dialog">
        <form class="stock-pin-modal__dialog" id="stock-pin-form">
            <header>
                <h3>Show Activation Key</h3>
            </header>

            <div class="stock-pin-modal__field">
                <label for="stock-pin-remarks">Remarks</label>
                <textarea
                    id="stock-pin-remarks"
                    name="remarks"
                    rows="3"
                    maxlength="255"
                    required
                    placeholder="Include order ID, customer contact, or a short note"
                ></textarea>
            </div>

            <p class="stock-pin-error" id="stock-pin-error" hidden></p>

            <div class="stock-pin-modal__actions">
                <button type="button" data-pin-cancel>Cancel</button>
                <button type="submit">Show Key</button>
            </div>
        </form>
    </section>

    
@endsection

@push('scripts')
    @include('stock.partials.scripts')
    @include('partials.product-combobox-scripts')
@endpush
