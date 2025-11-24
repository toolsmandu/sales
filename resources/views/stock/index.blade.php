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
                                                <div class="stock-item__value-row">
                                                    <span class="stock-item__value">{{ $key->masked_activation_key }}</span>
                                                    <button
                                                        type="button"
                                                        class="cell-action-button"
                                                        data-stock-copy
                                                        aria-label="Copy activation key"
                                                    >
                                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                                            <path d="M8 7V5a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2h-2" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                            <rect x="4" y="7" width="12" height="12" rx="2" ry="2" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                        </svg>
                                                    </button>
                                                    <span class="copy-inline-feedback"></span>
                                                </div>
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
                                                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M288 32c-80.8 0-145.5 36.8-192.6 80.6-46.8 43.5-78.1 95.4-93 131.1-3.3 7.9-3.3 16.7 0 24.6 14.9 35.7 46.2 87.7 93 131.1 47.1 43.7 111.8 80.6 192.6 80.6s145.5-36.8 192.6-80.6c46.8-43.5 78.1-95.4 93-131.1 3.3-7.9 3.3-16.7 0-24.6-14.9-35.7-46.2-87.7-93-131.1-47.1-43.7-111.8-80.6-192.6-80.6zM144 256a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm144-64c0 35.3-28.7 64-64 64-11.5 0-22.3-3-31.7-8.4-1 10.9-.1 22.1 2.9 33.2 13.7 51.2 66.4 81.6 117.6 67.9s81.6-66.4 67.9-117.6c-12.2-45.7-55.5-74.8-101.1-70.8 5.3 9.3 8.4 20.1 8.4 31.7z"/></svg>
                                                </button>
                                                @if (auth()->user()?->role === 'admin')
                                                    <button type="button" class="stock-action-button" data-stock-edit aria-label="Edit key {{ $key->product->name }}">
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M471.6 21.7c-21.9-21.9-57.3-21.9-79.2 0L368 46.1 465.9 144 490.3 119.6c21.9-21.9 21.9-57.3 0-79.2L471.6 21.7zm-299.2 220c-6.1 6.1-10.8 13.6-13.5 21.9l-29.6 88.8c-2.9 8.6-.6 18.1 5.8 24.6s15.9 8.7 24.6 5.8l88.8-29.6c8.2-2.7 15.7-7.4 21.9-13.5L432 177.9 334.1 80 172.4 241.7zM96 64C43 64 0 107 0 160L0 416c0 53 43 96 96 96l256 0c53 0 96-43 96-96l0-96c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 96c0 17.7-14.3 32-32 32L96 448c-17.7 0-32-14.3-32-32l0-256c0-17.7 14.3-32 32-32l96 0c17.7 0 32-14.3 32-32s-14.3-32-32-32L96 64z"/></svg>
                                                    </button>
                                                    <button type="button" class="stock-action-button stock-action-button--danger" data-stock-delete aria-label="Delete key {{ $key->product->name }}">
                                                   <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M136.7 5.9L128 32 32 32C14.3 32 0 46.3 0 64S14.3 96 32 96l384 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-96 0-8.7-26.1C306.9-7.2 294.7-16 280.9-16L167.1-16c-13.8 0-26 8.8-30.4 21.9zM416 144L32 144 53.1 467.1C54.7 492.4 75.7 512 101 512L347 512c25.3 0 46.3-19.6 47.9-44.9L416 144z"/></svg>
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
                                                <div class="stock-item__value-row">
                                                    <span class="stock-item__value">{{ $key->activation_key }}</span>
                                                    <button
                                                        type="button"
                                                        class="cell-action-button"
                                                        data-stock-copy
                                                        aria-label="Copy activation key"
                                                    >
                                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                                            <path d="M8 7V5a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2h-2" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                            <rect x="4" y="7" width="12" height="12" rx="2" ry="2" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                        </svg>
                                                    </button>
                                                    <span class="copy-inline-feedback"></span>
                                                </div>
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
                                                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M471.6 21.7c-21.9-21.9-57.3-21.9-79.2 0L368 46.1 465.9 144 490.3 119.6c21.9-21.9 21.9-57.3 0-79.2L471.6 21.7zm-299.2 220c-6.1 6.1-10.8 13.6-13.5 21.9l-29.6 88.8c-2.9 8.6-.6 18.1 5.8 24.6s15.9 8.7 24.6 5.8l88.8-29.6c8.2-2.7 15.7-7.4 21.9-13.5L432 177.9 334.1 80 172.4 241.7zM96 64C43 64 0 107 0 160L0 416c0 53 43 96 96 96l256 0c53 0 96-43 96-96l0-96c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 96c0 17.7-14.3 32-32 32L96 448c-17.7 0-32-14.3-32-32l0-256c0-17.7 14.3-32 32-32l96 0c17.7 0 32-14.3 32-32s-14.3-32-32-32L96 64z"/></svg>
                                                    </button>
                                                    <button type="button" class="stock-action-button stock-action-button--danger" data-stock-delete aria-label="Delete key {{ $key->product->name }}">
                                                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                                            <path d="M6 7h12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                           <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M136.7 5.9L128 32 32 32C14.3 32 0 46.3 0 64S14.3 96 32 96l384 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-96 0-8.7-26.1C306.9-7.2 294.7-16 280.9-16L167.1-16c-13.8 0-26 8.8-30.4 21.9zM416 144L32 144 53.1 467.1C54.7 492.4 75.7 512 101 512L347 512c25.3 0 46.3-19.6 47.9-44.9L416 144z"/></svg>
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
