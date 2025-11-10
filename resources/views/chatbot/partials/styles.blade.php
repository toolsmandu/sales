<style>
    .chatbot-grid {
        display: grid;
        gap: 1.5rem;
    }

    .chatbot-simulator {
        display: grid;
        gap: 1.25rem;
        min-height: 70vh;
    }

    .chatbot-window {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 1rem;
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.85) 0%, rgba(248, 250, 252, 0.55) 100%);
        padding: 1.25rem;
        overflow-y: auto;
        max-height: min(60vh, 520px);
    }

    .chatbot-placeholder {
        display: grid;
        place-items: center;
        gap: 0.75rem;
        text-align: center;
        color: rgba(15, 23, 42, 0.6);
        padding: 2rem 1.5rem;
    }

    .chatbot-message {
        display: flex;
        gap: 0.75rem;
        align-items: flex-start;
        max-width: min(720px, 100%);
    }

    .chatbot-message__avatar {
        width: 36px;
        height: 36px;
        border-radius: 999px;
        display: grid;
        place-items: center;
        font-weight: 600;
        font-size: 0.9rem;
        background: rgba(79, 70, 229, 0.18);
        color: rgba(79, 70, 229, 0.85);
        flex-shrink: 0;
    }

    .chatbot-message--user {
        margin-left: auto;
        flex-direction: row-reverse;
    }

    .chatbot-message--user .chatbot-message__avatar {
        background: rgba(37, 99, 235, 0.18);
        color: rgba(37, 99, 235, 0.9);
    }

    .chatbot-message__bubble {
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid rgba(148, 163, 184, 0.35);
        border-radius: 1rem;
        padding: 0.9rem 1.1rem;
        display: grid;
        gap: 0.35rem;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
    }

    .chatbot-setup {
        display: grid;
        gap: 0.55rem;
        border: 1px solid rgba(148, 163, 184, 0.25);
        border-radius: 1rem;
        padding: 0.9rem 1rem 1.15rem;
        background: rgba(255, 255, 255, 0.95);
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
    }

    .chatbot-setup label {
        display: grid;
        gap: -150px -150px;
        font-weight: 600;
        color: rgba(15, 23, 42, 0.85);
    }

    .chatbot-setup select {
        border-radius: 0.8rem;
        border: 1px solid rgba(148, 163, 184, 0.35);
        padding: 0.65rem 0.85rem;
        font-size: 0.95rem;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
        background: #fff;
        cursor: pointer;
    }

    .chatbot-setup select:focus-visible {
        outline: none;
        border-color: rgba(79, 70, 229, 0.45);
        box-shadow: 0 0 0 3px rgba(129, 140, 248, 0.22);
    }

    .chatbot-setup .product-combobox {
        border-radius: 1rem;
        padding: 0.85rem 1rem 1rem;
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.95) 0%, rgba(248, 250, 252, 0.6) 100%);
        border: 1px solid rgba(148, 163, 184, 0.35);
        box-shadow: 0 20px 36px rgba(15, 23, 42, 0.08);
        gap: 0.5rem;
    }

    .chatbot-setup .product-combobox__input {
        border-radius: 0.85rem;
        border: 1px solid rgba(99, 102, 241, 0.35);
        background: #ffffff;
        padding: 0.75rem 1rem;
        font-size: 0.96rem;
        box-shadow: 0 12px 24px rgba(99, 102, 241, 0.12);
    }

    .chatbot-setup .product-combobox__input:focus-visible {
        outline: none;
        border-color: rgba(79, 70, 229, 0.55);
        box-shadow: 0 0 0 3px rgba(129, 140, 248, 0.25);
    }

    .chatbot-setup .product-combobox__dropdown {
        top: calc(100% + 0.35rem);
        border-radius: 1rem;
        border: 1px solid rgba(148, 163, 184, 0.35);
        box-shadow: 0 28px 50px rgba(15, 23, 42, 0.2);
        padding: 0.5rem 0;
    }

    .chatbot-setup .product-combobox__option {
        border-radius: 0.7rem;
        margin: 0 0.45rem;
    }

    .chatbot-setup .product-combobox__option + .product-combobox__option {
        margin-top: 0.25rem;
    }

    .chatbot-setup .product-combobox__option:hover,
    .chatbot-setup .product-combobox__option:focus-visible {
        background: rgba(79, 70, 229, 0.1);
    }

    .chatbot-setup .product-combobox__option.is-active {
        background: rgba(79, 70, 229, 0.18);
        color: rgba(30, 41, 59, 0.95);
    }

    .chatbot-setup__hint {
        font-size: 0.9rem;
        color: rgba(15, 23, 42, 0.6);
    }

    .chatbot-interface[hidden] {
        display: none;
    }

    .chatbot-message--user .chatbot-message__bubble {
        background: rgba(79, 70, 229, 0.12);
        border-color: rgba(79, 70, 229, 0.2);
        color: rgba(30, 64, 175, 0.95);
    }

    .chatbot-message__meta {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.85rem;
        color: rgba(15, 23, 42, 0.55);
    }

    .chatbot-message__content {
        font-size: 0.96rem;
        line-height: 1.55;
        color: rgba(30, 41, 59, 0.95);
        white-space: pre-line;
    }

    .chatbot-message__options {
        display: grid;
        gap: 0.5rem;
        margin-top: 0.5rem;
    }

    .chatbot-message__option {
        border-radius: 0.9rem;
        border: 1px solid rgba(79, 70, 229, 0.18);
        background: rgba(79, 70, 229, 0.1);
        color: rgba(30, 41, 59, 0.92);
        padding: 0.55rem 0.95rem;
        font-size: 0.92rem;
        cursor: pointer;
        text-align: left;
        width: 100%;
        transition: background 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
    }

    .chatbot-message__option:hover,
    .chatbot-message__option:focus-visible {
        background: rgba(79, 70, 229, 0.18);
        box-shadow: 0 10px 18px rgba(79, 70, 229, 0.18);
        outline: none;
        transform: translateY(-1px);
    }

    .chatbot-message__list {
        display: grid;
        gap: 0.4rem;
        padding-left: 1.1rem;
        margin: 0.4rem 0 0 0;
    }

    .chatbot-message__tag {
        background: rgba(37, 99, 235, 0.12);
        color: rgba(30, 64, 175, 0.9);
        font-size: 0.8rem;
        padding: 0.2rem 0.55rem;
        border-radius: 999px;
        border: 1px solid rgba(37, 99, 235, 0.18);
    }

    .chatbot-input {
        display: grid;
        gap: 0.75rem;
        border: 1px solid rgba(148, 163, 184, 0.25);
        border-radius: 1rem;
        padding: 0.9rem 1rem;
        background: rgba(255, 255, 255, 0.95);
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
    }

    .chatbot-input__toolbar {
        display: grid;
        gap: 0.65rem;
    }

    .chatbot-input__row {
        display: grid;
        gap: 0.65rem;
        grid-template-columns: minmax(220px, 280px) minmax(200px, 1fr);
    }

    .chatbot-input__field input[type='text'] {
        width: 100%;
        border-radius: 0.8rem;
        border: 1px solid rgba(148, 163, 184, 0.35);
        padding: 0.65rem 0.85rem;
        font-size: 0.95rem;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .chatbot-input__field input[type='text']:focus {
        outline: none;
        border-color: rgba(79, 70, 229, 0.45);
        box-shadow: 0 0 0 3px rgba(129, 140, 248, 0.22);
    }

    .chatbot-input textarea {
        width: 100%;
        border-radius: 0.8rem;
        border: 1px solid rgba(148, 163, 184, 0.35);
        padding: 0.7rem 0.85rem;
        font-size: 0.95rem;
        resize: vertical;
        min-height: 90px;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .chatbot-input textarea:focus-visible {
        outline: none;
        border-color: rgba(79, 70, 229, 0.45);
        box-shadow: 0 0 0 3px rgba(129, 140, 248, 0.22);
    }

    .chatbot-input__actions {
        display: flex;
        justify-content: flex-end;
    }

    .chatbot-input__actions button {
        min-width: 120px;
        border-radius: 999px;
        background: rgba(79, 70, 229, 0.92);
        border: none;
        color: #fff;
        padding: 0.6rem 1.25rem;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
    }

    .chatbot-input__actions button:hover,
    .chatbot-input__actions button:focus-visible {
        transform: translateY(-1px);
        box-shadow: 0 12px 18px rgba(79, 70, 229, 0.2);
        background: rgba(67, 56, 202, 0.95);
        outline: none;
    }

    @media (max-width: 768px) {
        .chatbot-input__row {
            grid-template-columns: 1fr;
        }

        .chatbot-window {
            max-height: 55vh;
        }
    }
</style>
