@include('partials.dashboard-styles')
<style>
    .stock-layout {
        display: grid;
        gap: 1.5rem;
    }

    .stock-card {
        display: grid;
        gap: 1rem;
    }

    .stock-form .form-grid {
        max-width: 520px;
    }

    .input.date {
        margin-bottom: -15px;
    }

    .stock-search {
        display: grid;
        gap: 0.35rem;
    }

    .stock-search input[type="search"] {
        width: 100%;
        border-radius: 0.65rem;
        border: 1px solid rgba(15, 23, 42, 0.12);
        background: rgba(255, 255, 255, 0.95);
        padding: 0.55rem 0.75rem;
        font-size: 0.95rem;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .stock-search input[type="search"]:focus {
        outline: none;
        border-color: rgba(79, 70, 229, 0.45);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.18);
    }

    .stock-tabs {
        display: inline-flex;
        gap: 1rem;
        border-bottom: 1px solid rgba(15, 23, 42, 0.12);
        padding-bottom: 0.4rem;
    }

    .stock-tab {
        position: relative;
        border: none;
        background: transparent;
        color: rgba(15, 23, 42, 0.6);
        font-weight: 600;
        padding: 0.45rem 0;
        cursor: pointer;
        transition: color 0.2s ease;
    }

    .stock-tab::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: -0.35rem;
        width: 100%;
        height: 2px;
        border-radius: 999px;
        background: transparent;
        transition: background 0.2s ease;
    }

    .stock-tab.is-active {
        color: #1d1b50;
    }

    .stock-tab.is-active::after {
        background: rgba(79, 70, 229, 0.85);
    }

    .stock-tab__count {
        margin-left: 0.5rem;
        padding: 0 0.5rem;
        border-radius: 999px;
        background: rgba(79, 70, 229, 0.16);
        color: rgba(37, 99, 235, 0.95);
        font-size: 0.8rem;
    }

    .stock-panels {
        display: grid;
    }

    .stock-panel {
        display: none;
        gap: 1rem;
    }

    .stock-panel.is-active {
        display: grid;
    }

    .stock-list {
        list-style: none;
        margin: 0;
        padding: 0;
        display: grid;
        gap: 0.75rem;
    }

    .stock-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.9rem 1rem;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 0.75rem;
        background: rgba(248, 250, 252, 0.82);
    }

    .stock-item__details {
        display: grid;
        gap: 0.2rem;
    }

    .stock-item__product {
        font-weight: 600;
        color: rgba(15, 23, 42, 0.95);
    }

    .stock-item__value-row {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: nowrap;
    }

    .stock-item__value {
        font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, Courier, monospace;
        font-size: 0.95rem;
        color: rgba(15, 23, 42, 0.9);
        letter-spacing: 0.03em;
    }

    .stock-item__timestamp {
        font-size: 0.8rem;
        color: rgba(15, 23, 42, 0.55);
    }

    .stock-item__actions {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    .stock-action-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 999px;
        border: none;
        background: rgba(79, 70, 229, 0.12);
        color: rgba(79, 70, 229, 0.95);
        cursor: pointer;
        transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease;
    }

    .stock-action-button[aria-busy="true"]::after {
        content: '';
        width: 14px;
        height: 14px;
        border-radius: 999px;
        border: 2px solid transparent;
        border-top-color: currentColor;
        animation: spin 0.8s linear infinite;
    }

    .stock-action-button svg {
        width: 22px;
        height: 22px;
    }

    .stock-action-button:hover {
        background: rgba(79, 70, 229, 0.2);
        color: rgba(49, 46, 129, 0.95);
        transform: translateY(-1px);
    }

    .stock-action-button:disabled {
        opacity: 0.45;
        cursor: not-allowed;
    }

    .stock-action-button--danger {
        background: rgba(220, 38, 38, 0.12);
        color: rgba(220, 38, 38, 0.88);
    }

    .stock-action-button--danger:hover {
        background: rgba(220, 38, 38, 0.2);
        color: rgba(153, 27, 27, 0.95);
    }

    .stock-pin-modal {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.58);
        display: grid;
        place-items: center;
        z-index: 50;
        padding: 1.5rem;
    }

    .stock-pin-modal[hidden] {
        display: none;
    }

    .stock-pin-modal__dialog {
        width: min(420px, 100%);
        background: #ffffff;
        border-radius: 1rem;
        padding: 1.75rem;
        display: grid;
        gap: 1.25rem;
        box-shadow: 0 20px 45px rgba(15, 23, 42, 0.2);
    }

    .stock-pin-modal__field {
        display: grid;
        gap: 0.4rem;
    }

    .stock-pin-modal__field textarea {
        border-radius: 0.65rem;
        border: 1px solid rgba(15, 23, 42, 0.12);
        padding: 0.55rem 0.75rem;
        font-size: 0.95rem;
        resize: vertical;
    }

    .stock-pin-modal__field textarea:focus {
        outline: none;
        border-color: rgba(79, 70, 229, 0.45);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.18);
    }

    .stock-pin-modal__actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
    }

    .stock-pin-modal__actions button[type="button"] {
        background: transparent;
        border: 1px solid rgba(15, 23, 42, 0.15);
        color: rgba(15, 23, 42, 0.8);
        padding: 0.45rem 0.9rem;
        border-radius: 0.65rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .stock-pin-modal__actions button[type="button"]:hover {
        border-color: rgba(79, 70, 229, 0.45);
        color: rgba(79, 70, 229, 0.95);
    }

    .stock-pin-error {
        color: #dc2626;
        font-size: 0.9rem;
    }

    @keyframes spin {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }

    .stock-empty {
        margin: 0;
        padding: 2.5rem 1.75rem;
        border: 1px dashed rgba(15, 23, 42, 0.2);
        border-radius: 0.9rem;
        text-align: center;
        color: rgba(15, 23, 42, 0.6);
        font-size: 0.95rem;
    }

</style>
