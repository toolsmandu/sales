<style>
    .product-combobox {
        position: relative;
        display: grid;
        gap: 0.4rem;
    }

    .product-combobox__input {
        width: 100%;
        padding: 0.75rem 0.9rem;
        border-radius: 0.7rem;
        border: 1px solid rgba(148, 163, 184, 0.6);
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        font-size: 0.98rem;
        color: #1e293b;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .product-combobox__input::placeholder {
        color: #94a3b8;
    }

    .product-combobox__input:focus {
        border-color: rgba(79, 70, 229, 0.6);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
        outline: none;
    }

    .product-combobox__input[disabled] {
        cursor: not-allowed;
        background: rgba(241, 245, 249, 0.65);
    }

    .product-combobox__dropdown {
        position: absolute;
        top: calc(100% + 0.4rem);
        left: 0;
        width: 100%;
        max-height: 18rem;
        padding: 0.5rem 0;
        background: #ffffff;
        border: 1px solid rgba(148, 163, 184, 0.4);
        border-radius: 0.75rem;
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.12);
        overflow-y: auto;
        display: none;
        z-index: 20;
    }

    .product-combobox.is-open .product-combobox__dropdown {
        display: block;
    }

    .product-combobox__option {
        display: block;
        width: 100%;
        border: none;
        background: transparent;
        padding: 0.6rem 1rem;
        text-align: left;
        font-size: 0.98rem;
        color: #1e293b;
        cursor: pointer;
        transition: background 0.12s ease, color 0.12s ease;
    }

    .product-combobox__option:hover,
    .product-combobox__option:focus-visible {
        background: rgba(79, 70, 229, 0.08);
        color: #1d1b50;
        outline: none;
    }

    .product-combobox__option.is-active {
        background: rgba(79, 70, 229, 0.16);
        color: #1d1b50;
        font-weight: 600;
    }

    .product-combobox__empty {
        padding: 0.75rem 1rem;
        color: #64748b;
        font-style: italic;
    }
</style>
