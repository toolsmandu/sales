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
                    $variationNotesMap = collect($productOptions ?? [])
                        ->filter(fn ($option) => !empty($option['variation_id']))
                        ->mapWithKeys(fn ($option) => [(string) $option['variation_id'] => $option['notes'] ?? ''])
                        ->all();
                @endphp

                    <div class="stock-form-layout">
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
                            <input type="hidden" name="variation_id" value="{{ old('variation_id') }}" data-variation-selected>

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
                                                data-variation-id="{{ $option['variation_id'] ?? '' }}"
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

                        <aside class="stock-variation-notes" data-variation-notes>
                            <header>
                                <h3>Variation Notes</h3>
                                <p class="helper-text">Saved per product variation and reused for all new keys.</p>
                            </header>

                            <div class="stock-variation-notes__empty" data-variation-notes-empty>
                                Select a product variation to add or edit notes.
                            </div>

                            <div class="stock-variation-notes__form" data-variation-notes-form hidden>
                                <label for="stock-variation-notes-input">
                                    Notes
                                    <textarea
                                        id="stock-variation-notes-input"
                                        rows="6"
                                        placeholder="Add notes for this variation..."></textarea>
                                </label>

                                <div class="stock-variation-notes__actions">
                                    <button type="button" class="ghost-button" data-variation-notes-save>Save</button>
                                    <button type="button" class="ghost-button ghost-button--danger" data-variation-notes-delete>Delete</button>
                                    <span class="stock-variation-notes__status" data-variation-notes-status aria-live="polite"></span>
                                </div>
                            </div>
                        </aside>
                    </div>
                </section>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    @include('partials.product-combobox-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const panel = document.querySelector('[data-variation-notes]');
            if (!panel) {
                return;
            }

            const variationNotesMap = @json($variationNotesMap);
            const emptyState = panel.querySelector('[data-variation-notes-empty]');
            const form = panel.querySelector('[data-variation-notes-form]');
            const notesInput = panel.querySelector('#stock-variation-notes-input');
            const variationInput = document.querySelector('[data-variation-selected]');
            const saveButton = panel.querySelector('[data-variation-notes-save]');
            const deleteButton = panel.querySelector('[data-variation-notes-delete]');
            const status = panel.querySelector('[data-variation-notes-status]');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

            let currentVariationId = '';

            const setStatus = (message, isError = false) => {
                if (!status) {
                    return;
                }
                status.textContent = message;
                status.style.color = isError ? 'rgba(185, 28, 28, 0.9)' : '';
            };

            const setPanelState = ({ variationId, notes }) => {
                currentVariationId = variationId || '';

                if (!currentVariationId) {
                    if (variationInput) {
                        variationInput.value = '';
                    }
                    if (form) {
                        form.hidden = true;
                    }
                    if (emptyState) {
                        emptyState.hidden = false;
                    }
                    if (notesInput) {
                        notesInput.value = '';
                    }
                    saveButton?.setAttribute('disabled', 'disabled');
                    deleteButton?.setAttribute('disabled', 'disabled');
                    setStatus('');
                    return;
                }

                if (emptyState) {
                    emptyState.hidden = true;
                }
                if (form) {
                    form.hidden = false;
                }
                if (notesInput) {
                    notesInput.value = notes ?? '';
                }
                if (variationInput) {
                    variationInput.value = currentVariationId;
                }
                saveButton?.removeAttribute('disabled');
                deleteButton?.removeAttribute('disabled');
                setStatus('');
            };

            setPanelState({ variationId: '', notes: '' });

            const saveNotes = async (nextNotes) => {
                if (!currentVariationId) {
                    return;
                }

                setStatus('Saving...');
                saveButton?.setAttribute('disabled', 'disabled');
                deleteButton?.setAttribute('disabled', 'disabled');

                try {
                    const response = await fetch(`{{ url('/stock/variations') }}/${currentVariationId}/notes`, {
                        method: 'PUT',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ notes: nextNotes }),
                    });

                    if (response.status === 422) {
                        const data = await response.json();
                        const message = data?.errors?.notes?.[0] ?? 'Unable to validate notes.';
                        setStatus(message, true);
                        return;
                    }

                    if (!response.ok) {
                        throw new Error('Response not OK');
                    }

                    const data = await response.json();
                    const savedNotes = data?.notes ?? nextNotes ?? '';
                    if (notesInput) {
                        notesInput.value = savedNotes;
                    }
                    if (currentVariationId) {
                        variationNotesMap[currentVariationId] = savedNotes;
                    }
                    setStatus('Saved.');
                } catch (error) {
                    console.error(error);
                    setStatus('Unable to save notes. Please try again.', true);
                } finally {
                    saveButton?.removeAttribute('disabled');
                    deleteButton?.removeAttribute('disabled');
                }
            };

            saveButton?.addEventListener('click', () => {
                const nextNotes = notesInput?.value ?? '';
                saveNotes(nextNotes);
            });

            deleteButton?.addEventListener('click', () => {
                if (!currentVariationId) {
                    return;
                }
                const confirmed = window.confirm('Delete notes for this variation?');
                if (!confirmed) {
                    return;
                }
                saveNotes('');
            });

            const productInput = document.getElementById('stock-product-input');

            productInput?.addEventListener('input', () => {
                setPanelState({ variationId: '', notes: '' });
            });

            document.addEventListener('product-combobox:select', (event) => {
                const option = event?.detail?.option;
                const variationId = option?.dataset?.variationId ?? '';
                const notes = variationId ? (variationNotesMap[variationId] ?? '') : '';
                setPanelState({ variationId, notes });
            });

            const selectedName = productInput?.dataset?.selectedName ?? '';
            if (selectedName) {
                const options = Array.from(document.querySelectorAll('[data-product-option]'));
                const selectedOption = options.find((option) => option.dataset.productName === selectedName);
                if (selectedOption) {
                    setPanelState({
                        variationId: selectedOption.dataset.variationId ?? '',
                        notes: selectedOption.dataset.variationId ? (variationNotesMap[selectedOption.dataset.variationId] ?? '') : '',
                    });
                }
            }
        });
    </script>
@endpush
