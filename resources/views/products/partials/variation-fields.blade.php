@php
    $fieldLabelId = $fieldLabelId ?? uniqid('product_variations_');
    $fieldName = $fieldName ?? 'variations[]';
    $values = collect($values ?? [])
        ->map(fn ($value) => is_string($value) ? $value : '')
        ->filter(fn ($value) => $value !== '')
        ->values()
        ->all();
    $initialValues = count($values) > 0 ? $values : [''];
@endphp

@once('product-variation-assets')
    @push('styles')
        <style>
            .variation-block {
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
            }

            .variation-header {
                display: flex;
                flex-direction: column;
                gap: 0.25rem;
            }

            .variation-title {
                font-weight: 600;
                font-size: 1rem;
            }

            .variation-collection {
                display: flex;
                flex-direction: column;
                gap: 0.65rem;
            }

            .variation-field {
                display: grid;
                grid-template-columns: 1fr auto;
                gap: 0.5rem;
                align-items: start;
            }

            @media (max-width: 540px) {
                .variation-field {
                    grid-template-columns: 1fr;
                }

                .variation-field button {
                    width: 100%;
                }
            }

            .variation-field input[type="text"] {
                width: 100%;
            }

            .add-variation-btn {
                width: fit-content;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('[data-variation-block]').forEach((block) => {
                    const collection = block.querySelector('[data-variation-collection]');
                    const template = block.querySelector('template[data-variation-template]');
                    const addButton = block.querySelector('[data-add-variation]');

                    if (!collection || !template) {
                        return;
                    }

                    const fieldName = collection.dataset.variationName || 'variations[]';
                    const labelId = block.dataset.variationLabel || '';
                    const templateElement = template.content.firstElementChild;

                    const createField = (value = '') => {
                        if (!templateElement) {
                            return null;
                        }

                        const field = templateElement.cloneNode(true);
                        const input = field.querySelector('input');

                        if (input) {
                            input.name = fieldName;
                            input.value = value;
                            if (labelId) {
                                input.setAttribute('aria-labelledby', labelId);
                            }
                        }

                        collection.appendChild(field);
                        return field;
                    };

                    const addField = (value = '', { focus = true } = {}) => {
                        const field = createField(value);
                        if (focus) {
                            field?.querySelector('input')?.focus();
                        }
                    };

                    addButton?.addEventListener('click', (event) => {
                        event.preventDefault();
                        addField('');
                    });

                    collection.addEventListener('click', (event) => {
                        const trigger = event.target.closest('[data-remove-variation]');
                        if (!trigger) {
                            return;
                        }

                        event.preventDefault();
                        const row = trigger.closest('.variation-field');
                        row?.remove();

                        if (!collection.querySelector('.variation-field')) {
                            addField('', { focus: false });
                        }
                    });

                    collection.querySelectorAll('input').forEach((input) => {
                        input.name = fieldName;
                        if (labelId) {
                            input.setAttribute('aria-labelledby', labelId);
                        }
                    });

                    if (!collection.querySelector('.variation-field')) {
                        addField('', { focus: false });
                    }
                });
            });
        </script>
    @endpush
@endonce

<div
    class="variation-block"
    data-variation-block
    data-variation-label="{{ $fieldLabelId }}">
    <div class="variation-header">
        <p id="{{ $fieldLabelId }}" class="variation-title">Variations</p>
    </div>

    <div
        class="variation-collection"
        data-variation-collection
        data-variation-name="{{ $fieldName }}">
        @foreach ($initialValues as $value)
            <div class="variation-field">
                <input
                    type="text"
                    name="{{ $fieldName }}"
                    value="{{ $value }}"
                    maxlength="255"
                    aria-labelledby="{{ $fieldLabelId }}"
                    placeholder="Variation"
                >
                <button type="button" class="ghost-button" data-remove-variation>Remove</button>
            </div>
        @endforeach
    </div>

    <button type="button" class="ghost-button add-variation-btn" data-add-variation>
        + Add variation
    </button>

    <template data-variation-template>
        <div class="variation-field">
            <input
                type="text"
                name="{{ $fieldName }}"
                maxlength="255"
                placeholder="Variation"
            >
            <button type="button" class="ghost-button" data-remove-variation>Remove</button>
        </div>
    </template>
</div>
