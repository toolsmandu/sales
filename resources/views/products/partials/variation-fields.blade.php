@php
    $fieldLabelId = $fieldLabelId ?? uniqid('product_variations_');
    $normalizeBoolean = static function ($value, bool $default = true): bool {
        if ($value === null || $value === '') {
            return $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        $normalized = strtolower((string) $value);

        return in_array($normalized, ['1', 'true', 'on', 'yes'], true);
    };

    $values = collect($values ?? [])
        ->map(function ($value) use ($normalizeBoolean) {
            if (is_array($value)) {
                return [
                    'id' => isset($value['id']) && is_numeric($value['id']) ? (int) $value['id'] : null,
                    'name' => trim((string) ($value['name'] ?? '')),
                    'expiry_days' => isset($value['expiry_days']) && $value['expiry_days'] !== ''
                        ? max(0, (int) $value['expiry_days'])
                        : null,
                    'is_in_stock' => $normalizeBoolean($value['is_in_stock'] ?? null),
                ];
            }

            return [
                'id' => null,
                'name' => is_string($value) ? trim($value) : '',
                'expiry_days' => null,
                'is_in_stock' => true,
            ];
        })
        ->filter(fn (array $value) => $value['name'] !== '')
        ->values()
        ->all();
    $initialValues = count($values) > 0 ? $values : [['name' => '', 'expiry_days' => null, 'is_in_stock' => true]];
    $nextIndex = count($initialValues);
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
                grid-template-columns: minmax(0, 1fr) 160px 170px auto;
                gap: 0.5rem;
                align-items: start;
            }

            .variation-field .variation-expiry {
                display: flex;
                flex-direction: column;
                gap: 0.2rem;
            }

            .variation-stock {
                display: flex;
                flex-direction: column;
                gap: 0.25rem;
            }

            .variation-stock__label {
                font-size: 0.8rem;
                color: rgba(15, 23, 42, 0.7);
            }

            .variation-stock__options {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
            }

            .variation-stock__option {
                display: inline-flex;
                align-items: center;
                gap: 0.3rem;
                font-size: 0.85rem;
                white-space: nowrap;
            }

            .variation-field small {
                color: rgba(15, 23, 42, 0.6);
                font-size: 0.75rem;
            }

            @media (max-width: 540px) {
                .variation-field {
                    grid-template-columns: 1fr;
                }

                .variation-field button {
                    width: 100%;
                }

                .variation-stock__options {
                    justify-content: flex-start;
                }

                .variation-dynamic {
                    display: inline-flex;
                    align-items: center;
                    gap: 0.35rem;
                    font-size: 0.9rem;
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
                const normalizeStockValue = (value) => {
                    if (value === undefined || value === null || value === '') {
                        return '1';
                    }

                    if (value === true || value === 'true') {
                        return '1';
                    }

                    if (value === false || value === 'false') {
                        return '0';
                    }

                    const normalized = String(value).toLowerCase();

                    if (['0', 'no', 'off'].includes(normalized)) {
                        return '0';
                    }

                    return '1';
                };

                document.querySelectorAll('[data-variation-block]').forEach((block) => {
                    const collection = block.querySelector('[data-variation-collection]');
                    const template = block.querySelector('template[data-variation-template]');
                    const addButton = block.querySelector('[data-add-variation]');
                    let nextIndex = Number(block.dataset.nextIndex ?? 0);

                    if (!collection || !template) {
                        return;
                    }

                    const labelId = block.dataset.variationLabel || '';
                    const templateElement = template.content.firstElementChild;

                    const attachRemoveHandler = (field) => {
                        const removeButton = field.querySelector('[data-remove-variation]');
                        if (!removeButton) {
                            return;
                        }
                        removeButton.addEventListener('click', () => {
                            field.remove();
                            updateRemoveButtons();
                            if (!collection.querySelector('.variation-field')) {
                                addField({}, { focus: false });
                            }
                        });
                    };

                    const createField = (value = { name: '', expiry_days: null }) => {
                        if (!templateElement) {
                            return null;
                        }

                        const field = templateElement.cloneNode(true);
                        const index = nextIndex;
                        nextIndex += 1;

                        const nameInput = field.querySelector('[data-variation-name-input]');
                        if (nameInput) {
                            nameInput.name = `variations[${index}][name]`;
                            nameInput.value = value.name ?? '';
                            if (labelId) {
                                nameInput.setAttribute('aria-labelledby', labelId);
                            }
                        }

                        const idInput = field.querySelector('[data-variation-id-input]');
                        if (idInput) {
                            idInput.name = `variations[${index}][id]`;
                            idInput.value = value.id ?? '';
                        }

                        const expiryInput = field.querySelector('[data-variation-expiry-input]');
                        if (expiryInput) {
                            expiryInput.name = `variations[${index}][expiry_days]`;
                            if (value.expiry_days !== null && value.expiry_days !== undefined && value.expiry_days !== '') {
                                expiryInput.value = value.expiry_days;
                            }
                        }

                    const stockInputs = field.querySelectorAll('[data-variation-stock-input]');
                    const desiredStockValue = normalizeStockValue(value?.is_in_stock);
                    stockInputs.forEach((stockInput) => {
                        stockInput.name = `variations[${index}][is_in_stock]`;
                        stockInput.checked = stockInput.value === desiredStockValue;
                    });

                    const dynamicInput = field.querySelector('[data-variation-dynamic-input]');
                    if (dynamicInput) {
                        dynamicInput.name = `variations[${index}][is_dynamic]`;
                        dynamicInput.checked = !!value?.is_dynamic;
                    }

                        attachRemoveHandler(field);

                        collection.appendChild(field);
                        updateRemoveButtons();
                        return field;
                    };

                    const addField = (value = {}, { focus = true } = {}) => {
                        const field = createField(value);
                        if (focus && field) {
                            field.querySelector('input[type="text"]')?.focus();
                        }
                    };

                    const updateRemoveButtons = () => {
                        const rows = collection.querySelectorAll('.variation-field');
                        rows.forEach((row) => {
                            const removeButton = row.querySelector('[data-remove-variation]');
                            if (!removeButton) {
                                return;
                            }
                            const shouldDisable = rows.length === 1;
                            removeButton.classList.toggle('is-hidden', shouldDisable);
                            removeButton.disabled = shouldDisable;
                        });
                    };

                    addButton?.addEventListener('click', (event) => {
                        event.preventDefault();
                        addField('');
                    });

                    if (!collection.querySelector('.variation-field')) {
                        addField('', { focus: false });
                    } else {
                        collection.querySelectorAll('.variation-field').forEach((row) => attachRemoveHandler(row));
                        updateRemoveButtons();
                    }
                });
            });
        </script>
    @endpush
@endonce

<div
    class="variation-block"
    data-variation-block
    data-variation-label="{{ $fieldLabelId }}"
    data-next-index="{{ $nextIndex }}">
    <div class="variation-header">
        <p id="{{ $fieldLabelId }}" class="variation-title">Variations</p>
    </div>

    <div
        class="variation-collection"
        data-variation-collection>
        @foreach ($initialValues as $index => $value)
            <div class="variation-field" data-variation-row>
                <input
                    type="text"
                    name="variations[{{ $index }}][name]"
                    value="{{ $value['name'] }}"
                    maxlength="255"
                    aria-labelledby="{{ $fieldLabelId }}"
                    placeholder="Variation name"
                >
                <input
                    type="hidden"
                    name="variations[{{ $index }}][id]"
                    value="{{ $value['id'] ?? '' }}"
                    data-variation-id-input
                >
                <div class="variation-expiry">
                    <input
                        type="number"
                        name="variations[{{ $index }}][expiry_days]"
                        value="{{ $value['expiry_days'] }}"
                        min="0"
                        step="1"
                        placeholder="Expiry (days)">
                    <small>Expiry (days)</small>
                </div>
                @php
                    $variationInStock = $normalizeBoolean($value['is_in_stock'] ?? null);
                @endphp
                <div class="variation-stock">
                    <span class="variation-stock__label">Stock</span>
                    <div class="variation-stock__options">
                        <label class="variation-stock__option">
                            <input
                                type="radio"
                                name="variations[{{ $index }}][is_in_stock]"
                                value="1"
                                @checked($variationInStock)>
                            In stock
                        </label>
                        <label class="variation-stock__option">
                            <input
                                type="radio"
                                name="variations[{{ $index }}][is_in_stock]"
                                value="0"
                                @checked(!$variationInStock)>
                            Out of stock
                        </label>
                    </div>
                </div>
                @php
                    $variationDynamic = $normalizeBoolean($value['is_dynamic'] ?? null, false);
                @endphp
                <label class="variation-dynamic">
                    <input
                        type="checkbox"
                        name="variations[{{ $index }}][is_dynamic]"
                        value="1"
                        @checked($variationDynamic)>
                    Dynamic Product
                </label>
                <button type="button" class="ghost-button" data-remove-variation>Remove</button>
            </div>
        @endforeach
    </div>

    <button type="button" class="ghost-button add-variation-btn" data-add-variation>
        + Add variation
    </button>

    <template data-variation-template>
        <div class="variation-field" data-variation-row>
            <input
                type="text"
                data-variation-name-input
                maxlength="255"
                placeholder="Variation name"
            >
            <input
                type="hidden"
                data-variation-id-input
            >
            <div class="variation-expiry">
                <input
                    type="number"
                    data-variation-expiry-input
                    min="0"
                    step="1"
                    placeholder="Expiry (days)">
                <small>Expiry (days)</small>
            </div>
            <div class="variation-stock">
                <span class="variation-stock__label">Stock</span>
                <div class="variation-stock__options">
                    <label class="variation-stock__option">
                        <input
                            type="radio"
                            data-variation-stock-input
                            value="1"
                            checked>
                        In stock
                    </label>
                    <label class="variation-stock__option">
                        <input
                            type="radio"
                            data-variation-stock-input
                            value="0">
                        Out of stock
                    </label>
                </div>
            </div>
            <label class="variation-dynamic">
                <input
                    type="checkbox"
                    data-variation-dynamic-input
                    value="1">
                Dynamic Product
            </label>
            <button type="button" class="ghost-button" data-remove-variation>Remove</button>
        </div>
    </template>
</div>
