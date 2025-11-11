@extends('layouts.app')

@push('styles')
    @include('partials.dashboard-styles')
    @include('partials.product-combobox-styles')
    @include('chatbot.partials.styles')
    <style>
        .knowledge-faq {
            display: grid;
            gap: 1rem;
        }

        .knowledge-faq__item {
            border: 1px solid rgba(148, 163, 184, 0.25);
            border-radius: 1rem;
            padding: 1rem 1.25rem;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.05);
        }

        .knowledge-faq__summary {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            font-weight: 600;
            color: rgba(30, 41, 59, 0.95);
            cursor: pointer;
            list-style: none;
        }

        .knowledge-faq__item summary::-webkit-details-marker {
            display: none;
        }

        details summary::after {
            display: none;
        }

        .knowledge-faq__question {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.2rem;
            line-height: 1.4;
        }

        .knowledge-faq__toggle {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.85rem;
            color: rgba(79, 70, 229, 0.85);
            letter-spacing: 0.01em;
        }

        .knowledge-faq__toggle::before {
            content: '›';
            transition: transform 0.18s ease;
            display: inline-block;
        }

        .knowledge-faq__toggle::after {
            content: 'Show Answer';
        }

        details[open] .knowledge-faq__toggle::before {
            transform: rotate(90deg);
        }

        details[open] .knowledge-faq__toggle::after {
            content: 'Hide Answer';
        }

        .knowledge-faq__timestamp {
            font-size: 0.85rem;
            color: rgba(71, 85, 105, 0.75);
        }

        .knowledge-faq__meta {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-top: 0.75rem;
        }

        .knowledge-faq__actions {
            display: flex;
            gap: 0.5rem;
        }

        .knowledge-faq__actions a,
        .knowledge-faq__actions button {
            border-radius: 999px;
            padding: 0.35rem 0.85rem;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid rgba(79, 70, 229, 0.2);
            background: rgba(79, 70, 229, 0.08);
            color: rgba(67, 56, 202, 0.92);
            transition: background 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
        }

        .knowledge-faq__actions button {
            background: rgba(248, 113, 113, 0.1);
            border-color: rgba(248, 113, 113, 0.3);
            color: rgba(185, 28, 28, 0.92);
        }

        .knowledge-faq__actions a:hover,
        .knowledge-faq__actions a:focus-visible,
        .knowledge-faq__actions button:hover,
        .knowledge-faq__actions button:focus-visible {
            transform: translateY(-1px);
            box-shadow: 0 8px 16px rgba(79, 70, 229, 0.16);
            outline: none;
        }

        .knowledge-faq__actions button:hover,
        .knowledge-faq__actions button:focus-visible {
            box-shadow: 0 8px 16px rgba(248, 113, 113, 0.16);
        }

        .knowledge-faq__answer {
            margin-top: 0.85rem;
            font-size: 0.95rem;
            line-height: 1.6;
            color: rgba(30, 41, 59, 0.95);
        }

        .knowledge-faq__answer * {
            max-width: 100%;
        }

        .knowledge-faq__answer img {
            display: block;
            margin: 0.75rem 0;
            border-radius: 0.75rem;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.12);
        }

        .status-banner {
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            background: rgba(34, 197, 94, 0.12);
            border: 1px solid rgba(34, 197, 94, 0.28);
            color: rgba(22, 101, 52, 0.9);
            font-weight: 600;
        }
    </style>
@endpush

@section('content')
    <div class="dashboard-grid">
        @include('partials.dashboard-sidebar')

        <section class="dashboard-content">
            <div class="chatbot-grid">
                <section class="card">
                    <header>
                        <h2>Knowledgebase</h2>
                    </header>

                    @php
                        $selectedProduct = $products->firstWhere('id', $selectedProductId);
                    @endphp

                    <form method="GET" action="{{ route('chatbot.existing') }}" class="form-grid form-grid--compact">
                        <div class="product-combobox" data-product-combobox data-autosubmit="true">
                            <label for="existing-product-input">
                                Product
                                <input
                                    type="text"
                                    id="existing-product-input"
                                    class="product-combobox__input"
                                    name="product_search"
                                    value="{{ request('product_search', $selectedProduct->name ?? '') }}"
                                    placeholder="Choose product..."
                                    autocomplete="off"
                                    data-selected-name="{{ $selectedProduct->name ?? '' }}"
                                    {{ $products->isEmpty() ? 'disabled' : '' }}
                                >
                            </label>
                            <input type="hidden" name="product" value="{{ $selectedProduct->id ?? $selectedProductId }}" data-product-selected>

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
                    </form>

                    @if (session('status'))
                        <div class="status-banner" role="status">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($knowledgeEntries->isEmpty())
                        <p class="muted">No knowledge captured for this product yet.</p>
                    @else
                        <div class="knowledge-faq" role="list">
                            @foreach ($knowledgeEntries as $entry)
                                <details class="knowledge-faq__item" role="listitem">
                                    <summary class="knowledge-faq__summary">
                                        <span class="knowledge-faq__question">{{ $entry->question }}</span>
                                        <span class="knowledge-faq__toggle" aria-hidden="true"></span>
                                    </summary>
                                    <div class="knowledge-faq__meta">
                                        <span class="knowledge-faq__timestamp">Created {{ optional($entry->created_at)?->setTimezone('Asia/Kathmandu')->format('M j, Y g:i A') }}</span>
                                        <div class="knowledge-faq__actions">
                                            <a href="{{ route('chatbot.entries.edit', ['chatbotEntry' => $entry, 'product' => $selectedProductId]) }}">Edit</a>
                                            <form method="POST" action="{{ route('chatbot.entries.destroy', $entry) }}" onsubmit="return confirm('Delete this entry?');">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="redirect_product" value="{{ $selectedProductId }}">
                                                <button type="submit">Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="knowledge-faq__answer">{!! $entry->answer !!}</div>
                                </details>
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    @include('partials.dashboard-scripts')
    @include('partials.product-combobox-scripts')
@endpush
