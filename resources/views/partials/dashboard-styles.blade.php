<style>
    .dashboard-grid {
        display: flex;
        gap: 1.4rem;
        align-items: flex-start;
        padding-block: 0.75rem;
    }

    .dashboard-nav {
        flex: 0 0 240px;
        padding: 1.1rem 1rem;
        border-radius: 1rem;
        background: linear-gradient(180deg, rgba(37, 99, 235, 0.12) 0%, rgba(79, 70, 229, 0.08) 100%);
        border: 1px solid rgba(79, 70, 229, 0.16);
        box-shadow: 0 16px 32px rgba(79, 70, 229, 0.18);
        position: sticky;
        top: 1rem;
        display: grid;
        gap: 1rem;
    }

    .dashboard-nav ul {
        list-style: none;
        margin: 0;
        padding: 0;
        display: grid;
        gap: 0.6rem;
    }

    .dashboard-nav__heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.6rem;
        padding-bottom: 0.45rem;
        border-bottom: 1px dashed rgba(79, 70, 229, 0.35);
    }

    .dashboard-nav__heading span {
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: rgba(30, 41, 59, 0.62);
        font-weight: 600;
    }

    .dashboard-nav__heading svg {
        width: 1.1rem;
        height: 1.1rem;
        color: rgba(79, 70, 229, 0.7);
    }

    .dashboard-nav button,
    .dashboard-nav a {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.6rem 0.95rem;
        border-radius: 0.65rem;
        background: rgba(255, 255, 255, 0.92);
        color: #0f172a;
        border: 1px solid rgba(15, 23, 42, 0.06);
        transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease, background 0.15s ease;
        cursor: pointer;
        text-align: left;
        font-size: 0.95rem;
        text-decoration: none;
    }

    .dashboard-nav button span,
    .dashboard-nav a span {
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
    }

    .dashboard-nav button svg,
    .dashboard-nav a svg {
        width: 0.95rem;
        height: 0.95rem;
        color: rgba(79, 70, 229, 0.55);
        transition: transform 0.2s ease;
    }

    .dashboard-nav button:hover svg,
    .dashboard-nav button:focus-visible svg,
    .dashboard-nav a:hover svg,
    .dashboard-nav a:focus-visible svg {
        transform: scale(1.05);
    }

    .dashboard-nav button:hover,
    .dashboard-nav button:focus-visible,
    .dashboard-nav a:hover,
    .dashboard-nav a:focus-visible {
        transform: translateY(-2px);
        box-shadow: 0 14px 26px rgba(79, 70, 229, 0.2);
        outline: none;
        border-color: rgba(79, 70, 229, 0.35);
        background: rgba(255, 255, 255, 0.98);
    }

    .dashboard-nav button.is-active,
    .dashboard-nav a.is-active {
        border-color: rgba(79, 70, 229, 0.45);
        box-shadow: 0 18px 28px rgba(79, 70, 229, 0.24);
        background: linear-gradient(135deg, rgba(79, 70, 229, 0.16), rgba(37, 99, 235, 0.14));
    }

    .dashboard-nav__accordion {
        border: 1px solid rgba(79, 70, 229, 0.16);
        background: rgba(255, 255, 255, 0.92);
        color: #0f172a;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.6rem;
        padding: 0.6rem 0.95rem;
        border-radius: 0.75rem;
        cursor: pointer;
        transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease, background 0.15s ease, color 0.15s ease;
    }

    .dashboard-nav__accordion:hover,
    .dashboard-nav__accordion:focus-visible {
        transform: translateY(-2px);
        box-shadow: 0 14px 26px rgba(79, 70, 229, 0.2);
        outline: none;
        border-color: rgba(79, 70, 229, 0.35);
        background: rgba(255, 255, 255, 0.98);
        color: #1e1b4b;
    }

    .dashboard-nav__accordion svg {
        transition: transform 0.2s ease;
    }

    .dashboard-nav li.is-expanded > .dashboard-nav__accordion svg:last-child {
        transform: rotate(90deg);
    }

    .dashboard-nav .dashboard-nav__sublist {
        list-style: none;
        margin: 0.35rem 0 0;
        padding: 0 0 0 1.5rem;
        display: none;
        gap: 0.3rem;
    }

    .dashboard-nav li.is-expanded > .dashboard-nav__sublist {
        display: grid;
    }

    .dashboard-nav__sublink {
        display: block;
        padding: 0.4rem 0.6rem;
        border-radius: 0.5rem;
        background: rgba(255, 255, 255, 0.75);
        color: #312e81;
        border: 1px solid rgba(79, 70, 229, 0.12);
        font-size: 0.88rem;
        transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
    }

    .dashboard-nav__sublink:hover,
    .dashboard-nav__sublink:focus-visible {
        background: rgba(79, 70, 229, 0.12);
        color: #1e1b4b;
        border-color: rgba(79, 70, 229, 0.3);
    }

    .dashboard-nav__sublink.is-active {
        background: rgba(79, 70, 229, 0.2);
        color: #1d1b50;
        border-color: rgba(79, 70, 229, 0.4);
        font-weight: 600;
    }

    .dashboard-content {
        flex: 1;
        padding: 1.2rem;
        border-radius: 0.75rem;
        background: #fff;
        border: 1px solid rgba(15, 23, 42, 0.08);
    }

    .dashboard-content.stack {
        padding: 0;
    }

    .card {
        background: #fff;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 0.75rem;
        padding: 1rem;
        display: grid;
        gap: 0.9rem;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
    }

    .card--accent {
        border-color: rgba(79, 70, 229, 0.22);
        background: linear-gradient(180deg, rgba(79, 70, 229, 0.08) 0%, rgba(59, 130, 246, 0.08) 100%);
        box-shadow: 0 18px 34px rgba(79, 70, 229, 0.18);
    }

    .card--accent h2 {
        color: #312e81;
    }

    .card header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    .card h2 {
        margin: 0;
        font-size: 1.35rem;
    }

    .card p {
        margin: 0;
        color: rgba(15, 23, 42, 0.72);
    }

    .card button {
        margin: 0;
    }

    .dashboard-panel {
        display: grid;
        gap: 1.4rem;
    }

    .dashboard-panel.is-active {
        display: grid;
    }

    .is-hidden {
        display: none;
    }

    .copy-toast {
        position: fixed;
        bottom: 1.5rem;
        right: 1.5rem;
        padding: 0.6rem 1rem;
        background: rgba(15, 23, 42, 0.92);
        color: #fff;
        border-radius: 0.6rem;
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.2);
        font-size: 0.9rem;
        transition: opacity 0.2s ease, transform 0.2s ease;
        opacity: 0;
        transform: translateY(10px);
        z-index: 200;
    }

    .copy-toast.is-visible {
        opacity: 1;
        transform: translateY(0);
    }

    .form-grid {
        display: grid;
        gap: 0.85rem;
    }

    .form-grid--compact {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.75rem;
    }

    @media (max-width: 960px) {
        .form-grid--compact {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 640px) {
        .form-grid--compact {
            grid-template-columns: 1fr;
        }
    }

    .form-grid--compact > label {
        display: grid;
        gap: 0.3rem;
    }

    .pill-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
        padding: 0;
        margin: 0;
        list-style: none;
    }

    .pill-list li {
        background: rgba(79, 70, 229, 0.1);
        color: #4338ca;
        border: 1px solid rgba(79, 70, 229, 0.2);
        border-radius: 999px;
        padding: 0.35rem 0.75rem;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }

    .pill-action {
        padding: 0.2rem 0.55rem;
        font-size: 0.75rem;
        line-height: 1.2;
        white-space: nowrap;
    }

    .pill-list li strong {
        font-weight: 600;
    }

    .muted {
        color: rgba(15, 23, 42, 0.6);
        font-size: 0.95rem;
    }

    .helper-text {
        font-size: 0.85rem;
        color: rgba(15, 23, 42, 0.6);
    }

    .form-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    .form-actions__buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .form-actions--row {
        grid-column: 1 / -1;
        align-items: center;
        margin-top: 0.25rem;
    }

    .form-actions--row .helper-text {
        margin: 0;
    }

    .form-label {
        display: block;
        font-weight: 600;
        margin-bottom: 0.35rem;
    }

    .variation-collection {
        display: grid;
        gap: 0.5rem;
    }

    .variation-field {
        display: flex;
        gap: 0.6rem;
        align-items: center;
    }

    .variation-field input {
        flex: 1;
    }

    .add-variation-btn {
        width: fit-content;
    }

    .ghost-button {
        padding: 0.4rem 0.8rem;
        border-radius: 0.5rem;
        border: 1px solid rgba(79, 70, 229, 0.2);
        background: rgba(79, 70, 229, 0.08);
        color: #4338ca;
        cursor: pointer;
        transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
    }

    .ghost-button:hover,
    .ghost-button:focus-visible {
        background: rgba(79, 70, 229, 0.18);
        border-color: rgba(79, 70, 229, 0.35);
        color: #312e81;
        outline: none;
    }

    .ghost-button.is-hidden {
        display: none;
    }

    .ghost-button.is-disabled {
        pointer-events: none;
        opacity: 0.45;
    }

    .ghost-button--slim {
        padding: 0.3rem 0.6rem;
    }

    .table-wrapper {
        border: 1px solid rgba(148, 163, 184, 0.4);
        border-radius: 0.75rem;
        overflow: hidden;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    table thead {
        background: rgba(79, 70, 229, 0.08);
    }

    .table-striped tbody tr:nth-child(odd) {
        background: rgba(79, 70, 229, 0.035);
    }

    .table-striped tbody tr:hover {
        background: rgba(79, 70, 229, 0.12);
    }

    table th,
    table td {
        padding: 0.75rem 1rem;
        text-align: left;
        border-bottom: 1px solid rgba(148, 163, 184, 0.3);
        word-break: break-word;
    }

    /* S.N. ko width badhaune*/
.sales-table th:first-child,
.sales-table td:first-child {
    min-width: 5rem;
    white-space: nowrap;
}

.sales-table th:nth-child(2),
.sales-table td:nth-child(2) {
    min-width: 10rem;
    white-space: nowrap;
}

.sales-table th:nth-child(7),
.sales-table td:nth-child(7) {
    min-width:9rem;
    white-space: nowrap;
}

.sales-table th:nth-child(4),
.sales-table td:nth-child(4) {
    min-width: 13rem;
}

.sales-table th:nth-child(6),
.sales-table td:nth-child(6) {
    min-width: 6rem;
    white-space: nowrap;
}

.sales-table th:nth-child(5),
.sales-table td:nth-child(5) {
    min-width: 8rem;
}

.sales-table th:nth-child(8),
.sales-table td:nth-child(8) {
    min-width: 4.5rem;
}

    .amount-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.25rem 0.65rem;
        border-radius: 999px;
        font-weight: 600;
        font-size: 0.9rem;
        letter-spacing: 0.01em;
    }

    .amount-chip svg {
        width: 0.9rem;
        height: 0.9rem;
    }

    .amount-chip--expense {
        background: rgba(239, 68, 68, 0.12);
        color: #b91c1c;
    }

    .payments-hub {
        display: grid;
        gap: 1.5rem;
    }

    .payments-layout {
        display: grid;
        gap: 1.5rem;
    }

    .payments-layout__summary {
        display: grid;
        gap: 1.25rem;
    }

    .payments-subsection {
        display: grid;
        gap: 1rem;
    }

    .payments-subsection__header {
        display: grid;
        gap: 0.35rem;
    }

    .payments-subsection__header h2 {
        margin: 0;
        font-size: 1.35rem;
        color: #0f172a;
    }

    .payment-balances-section {
        display: grid;
        gap: 0.75rem;
    }

    .payment-balances-section__header {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .payment-balances-section__header h3 {
        margin: 0;
        font-size: 1.15rem;
        color: #0f172a;
    }

    @media (min-width: 640px) {
        .payment-balances-section__header {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
        }
    }

    .payment-balances-table table {
        width: 100%;
    }

    .payment-balances-table table thead th {
        background: linear-gradient(120deg, rgba(79, 70, 229, 0.12), rgba(56, 189, 248, 0.12));
        color: #1e293b;
        border-bottom: 1px solid rgba(79, 70, 229, 0.25);
        font-weight: 600;
    }

    .payment-balances-table table tbody tr:hover {
        background: rgba(79, 70, 229, 0.06);
    }

    .payment-balances__method {
        display: flex;
        flex-direction: column;
        gap: 0.1rem;
    }

    .payment-balances__label {
        font-weight: 600;
        color: #0f172a;
    }

    .payment-balances__slug {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: rgba(15, 23, 42, 0.5);
    }

    .payment-balances__count,
    .payment-balances__amount,
    .payment-balances__income,
    .payment-balances__withdrawal {
        text-align: center;
        font-weight: 600;
        color: #1f2937;
    }

    .payment-balances__income {
        color: #047857;
    }

    .payment-balances__withdrawal {
        color: #b91c1c;
    }

    .payment-monthly-section {
        display: grid;
        gap: 0.85rem;
        margin-top: 0.5rem;
    }

    .payment-monthly-section__header {
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
    }

    @media (min-width: 768px) {
        .payment-monthly-section__header {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
        }
    }

    .payment-monthly-filter {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    @media (min-width: 768px) {
        .payment-monthly-filter {
            flex-direction: row;
            align-items: flex-end;
            gap: 1rem;
        }
    }

    .payment-monthly-filter__inputs {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        align-items: flex-end;
    }

    .payment-monthly-actions {
        display: flex;
        gap: 0.5rem;
        align-items: flex-end;
    }

    .payment-ledger-actions, .ghost-button-statement {
        margin: 18px;
    }

        .payment-monthly-actions, .filter-apply {
        margin: 18px;
    }

    .payment-monthly-table table thead th {
        background: linear-gradient(120deg, rgba(79, 70, 229, 0.1), rgba(45, 212, 191, 0.12));
        color: #111827;
        border-bottom: 1px solid rgba(79, 70, 229, 0.2);
        font-weight: 600;
    }

    .payment-balances-table table th,
    .payment-balances-table table td,
    .payment-monthly-table table th,
    .payment-monthly-table table td {
        text-align: center;
        vertical-align: middle;
    }

    .payment-monthly-table table tbody tr:hover {
        background: rgba(45, 212, 191, 0.08);
    }

    .payment-monthly__period {
        font-weight: 600;
        color: #0f172a;
    }

    .payment-ledger-section {
        display: grid;
        gap: 0.85rem;
        margin-top: 1rem;
    }

    .payment-ledger-section__header {
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
    }

    @media (min-width: 768px) {
        .payment-ledger-section__header {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
        }
    }

    .payment-ledger-filter {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    @media (min-width: 768px) {
        .payment-ledger-filter {
            flex-direction: row;
            align-items: flex-end;
            gap: 1rem;
        }
    }

    .payment-ledger-filter__inputs {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        align-items: flex-end;
    }

    .payment-ledger-actions {
        display: flex;
        gap: 0.5rem;
        align-items: flex-end;
    }

    .payment-ledger-table table th,
    .payment-ledger-table table td {
        text-align: center;
        vertical-align: middle;
    }

    .payment-ledger-table table tbody tr:hover {
        background: rgba(79, 70, 229, 0.06);
    }

    .payment-ledger-controls {
        display: flex;
        justify-content: flex-end;
    }

    .statements-page-size {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        white-space: nowrap;
    }

    .statements-page-size label {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        white-space: nowrap;
    }

    .statements-page-size select {
        min-width: 4.5rem;
    }

    @media (min-width: 960px) {
        .payments-layout__summary {
            grid-template-columns: minmax(280px, 0.75fr) minmax(0, 1fr);
            align-items: stretch;
        }
    }

    .payments-tile {
        border-radius: 1rem;
        border: 1px solid rgba(148, 163, 184, 0.35);
        background: #fff;
        box-shadow: 0 20px 36px rgba(15, 23, 42, 0.08);
        padding: 1.2rem 1.3rem;
        display: grid;
        gap: 1rem;
    }

    .payments-tile--balances {
        background: linear-gradient(135deg, rgba(79, 70, 229, 0.12), rgba(56, 189, 248, 0.12));
        border: 1px solid rgba(79, 70, 229, 0.25);
    }

    .payments-tile__header {
        display: grid;
        gap: 0.4rem;
    }

    .payments-tile__header--split {
        gap: 0.9rem;
    }

    @media (min-width: 768px) {
        .payments-tile__header--split {
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: end;
        }
    }

    .payments-tile__header h2 {
        margin: 0;
        font-size: 1.2rem;
        color: #0f172a;
    }

    .payments-tile__header h3 {
        margin: 0;
        font-size: 1.05rem;
        color: #1f2937;
    }

    .payments-tile__header p {
        margin: 0;
        color: rgba(15, 23, 42, 0.62);
        font-size: 0.92rem;
    }

    .payments-filters {
        display: flex;
        gap: 0.7rem;
        flex-wrap: wrap;
    }

    .payments-filter {
        display: grid;
        gap: 0.3rem;
        font-size: 0.85rem;
        color: rgba(15, 23, 42, 0.72);
    }

    .payments-filter--wide {
        min-width: 220px;
    }

    .payments-filter select {
        border-radius: 0.65rem;
        border: 1px solid rgba(148, 163, 184, 0.5);
        padding: 0.45rem 0.75rem;
        background: rgba(255, 255, 255, 0.97);
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .payments-filter select:focus-visible {
        border-color: rgba(79, 70, 229, 0.55);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.18);
        outline: none;
    }

    .payments-balance-stack {
        display: grid;
        gap: 0.7rem;
    }

    .payments-ledger-divider {
        height: 1px;
        background: rgba(148, 163, 184, 0.4);
        margin: 0.75rem 0;
    }
    .payments-balance-total {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.6rem;
        padding: 0.5rem 0.75rem;
        border-radius: 0.75rem;
        background: rgba(255, 255, 255, 0.55);
        border: 1px solid rgba(255, 255, 255, 0.4);
        font-weight: 600;
        color: #0f172a;
    }

    .payments-balance-total strong {
        font-size: 1.05rem;
        color: #111827;
    }

    .payments-balance-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.8rem;
        padding: 0.75rem 0.95rem;
        border-radius: 0.85rem;
        background: rgba(255, 255, 255, 0.55);
        border: 1px solid rgba(255, 255, 255, 0.4);
        box-shadow: 0 12px 24px rgba(79, 70, 229, 0.12);
    }

    .payments-balance-item__info {
        display: grid;
        gap: 0.15rem;
    }

    .payments-balance-item__info strong {
        font-size: 1.05rem;
        color: #0b1120;
    }

    .payments-balance-item__meta {
        font-size: 0.8rem;
        color: rgba(15, 23, 42, 0.55);
    }

    .payments-balance-amount {
        font-weight: 700;
        font-size: 1rem;
        color: #312e81;
    }

    .table-wrapper--elevated {
        border-radius: 0.9rem;
        border: 1px solid rgba(148, 163, 184, 0.35);
        background: rgba(255, 255, 255, 0.98);
        overflow: hidden;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.6);
    }

    .table-controls--compact {
        align-items: center;
    }

    .table-controls--compact .table-controls__page-size {
        gap: 0.35rem;
    }

    .method-card-list {
        display: grid;
        gap: 1rem;
    }

    .method-card {
        border: 1px solid rgba(148, 163, 184, 0.3);
        border-radius: 1rem;
        padding: 1.1rem;
        background: linear-gradient(135deg, rgba(255, 255, 255, 1) 0%, rgba(229, 237, 255, 0.6) 100%);
        box-shadow: 0 18px 32px rgba(79, 70, 229, 0.12);
        display: grid;
        gap: 0.9rem;
    }

    .method-card__header {
        display: flex;
        align-items: flex-start;
        gap: 0.85rem;
    }

    .method-card__index {
        width: 2rem;
        height: 2rem;
        border-radius: 0.75rem;
        background: rgba(79, 70, 229, 0.18);
        color: #312e81;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
    }

    .method-card__title {
        margin: 0;
        font-size: 1.1rem;
        color: #0f172a;
    }

    .method-card__subtitle {
        margin: 0.15rem 0 0;
        color: rgba(15, 23, 42, 0.62);
        font-size: 0.9rem;
    }

    .method-card__body {
        display: flex;
        flex-wrap: wrap;
        gap: 0.8rem;
        align-items: flex-end;
        justify-content: space-between;
    }

    .method-card__form {
        display: grid;
        gap: 0.4rem;
        min-width: 240px;
        flex: 1 1 320px;
    }

    .method-card__label {
        display: grid;
        gap: 0.3rem;
        font-weight: 600;
        color: #0f172a;
    }

    .method-card__label input {
        width: 100%;
        border: 1px solid rgba(79, 70, 229, 0.35);
        border-radius: 0.65rem;
        padding: 0.55rem 0.8rem;
        background: rgba(255, 255, 255, 0.92);
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .method-card__label input:focus-visible {
        border-color: rgba(79, 70, 229, 0.55);
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.18);
        outline: none;
    }

    .method-card__delete {
        flex: 0 0 auto;
    }

    .pill-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        padding: 0.45rem 1.15rem;
        border-radius: 0.75rem;
        font-weight: 600;
        font-size: 0.95rem;
        border: 1px solid transparent;
        cursor: pointer;
        transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
    }

    .pill-button:hover,
    .pill-button:focus-visible {
        transform: translateY(-1px);
        outline: none;
    }

    .pill-button--primary {
        background: rgba(79, 70, 229, 0.14);
        color: #312e81;
        border-color: rgba(79, 70, 229, 0.3);
    }

    .pill-button--primary:hover,
    .pill-button--primary:focus-visible {
        background: rgba(79, 70, 229, 0.22);
        border-color: rgba(79, 70, 229, 0.45);
        color: #1e1b4b;
        box-shadow: 0 12px 26px rgba(79, 70, 229, 0.18);
    }

    .pill-button--danger {
        background: rgba(239, 68, 68, 0.13);
        color: #b91c1c;
        border-color: rgba(239, 68, 68, 0.32);
    }

    .pill-button--danger:hover,
    .pill-button--danger:focus-visible {
        background: rgba(239, 68, 68, 0.2);
        border-color: rgba(239, 68, 68, 0.48);
        color: #7f1d1d;
        box-shadow: 0 12px 26px rgba(239, 68, 68, 0.16);
    }

    .badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.25rem 0.7rem;
        border-radius: 999px;
        font-size: 0.85rem;
        font-weight: 600;
        letter-spacing: 0.01em;
        border: 1px solid transparent;
    }

    .badge--method {
        background: rgba(59, 130, 246, 0.12);
        color: #1d4ed8;
        border-color: rgba(59, 130, 246, 0.3);
    }

    .cell-with-action {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
    }

    .cell-action-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.25rem;
        border-radius: 0.4rem;
        border: 1px solid transparent;
        background: rgba(79, 70, 229, 0.1);
        color: rgba(79, 70, 229, 0.8);
        cursor: pointer;
        transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
        line-height: 0;
    }

    .cell-action-button:hover,
    .cell-action-button:focus-visible {
        border-color: rgba(79, 70, 229, 0.35);
        background: rgba(79, 70, 229, 0.16);
        color: rgba(49, 46, 129, 0.9);
        outline: none;
    }

    .cell-action-button svg {
        width: 1rem;
        height: 1rem;
    }

    .copy-toast {
        position: fixed;
        bottom: 1.5rem;
        right: 1.5rem;
        padding: 0.6rem 1rem;
        background: rgba(15, 23, 42, 0.92);
        color: #fff;
        border-radius: 0.6rem;
        box-shadow: 0 12px 24px rgba(15, 23, 42, 0.2);
        font-size: 0.9rem;
        transition: opacity 0.2s ease, transform 0.2s ease;
        opacity: 0;
        transform: translateY(10px);
        z-index: 200;
    }

    .copy-toast.is-visible {
        opacity: 1;
        transform: translateY(0);
    }

    table tbody tr:last-child td {
        border-bottom: none;
    }

    table:not(.table-striped) tbody tr:hover {
        background: rgba(15, 23, 42, 0.04);
    }

   
    .table-actions {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        flex-wrap: nowrap;
    }

    .table-actions form {
        margin: 0;
    }

    .table-actions > * {
        flex: 0 0 auto;
    }

    .sales-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1.1rem;
    }

    .sales-controls {
        display: flex;
        gap: 0.65rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .table-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.6rem;
        margin: 0.6rem 0;
    }

    .table-controls__page-size {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.9rem;
    }

    .table-controls__page-size label {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        white-space: nowrap;
    }

    .table-controls__page-size select,
    .table-controls select {
        min-width: 4.5rem;
    }

    .table-controls select {
        min-width: 4.5rem;
    }

    .table-controls__pagination {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .ledger-expense {
        color: #b91c1c;
    }

    .dropdown {
        position: relative;
    }

    .dropdown button {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.5rem 0.9rem;
        border-radius: 999px;
        border: 1px solid rgba(148, 163, 184, 0.6);
        background: #fff;
        color: #0f172a;
        cursor: pointer;
        transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
    }

    .dropdown button:hover,
    .dropdown button:focus-visible,
    .dropdown button.is-active {
        border-color: rgba(79, 70, 229, 0.5);
        background: rgba(79, 70, 229, 0.08);
        color: #0f172a;
        outline: none;
        box-shadow: 0 8px 18px rgba(79, 70, 229, 0.12);
    }

    .dropdown__menu {
        position: absolute;
        top: calc(100% + 0.5rem);
        right: 0;
        display: grid;
        background: #fff;
        border-radius: 0.75rem;
        border: 1px solid rgba(148, 163, 184, 0.3);
        min-width: 500px;
        box-shadow: 0 18px 38px rgba(15, 23, 42, 0.18);
        padding: 0.6rem 0.7rem;
        gap: 0.55rem;
        z-index: 10;
    }

    .dropdown__menu.is-hidden {
        display: none;
    }

    .dropdown__menu .stack {
        display: grid;
        gap: 0.45rem;
    }

    .pill-group {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
    }

    .pill-group button {
        padding: 0.4rem 0.75rem;
        border-radius: 999px;
        border: 1px solid rgba(148, 163, 184, 0.5);
        background: rgba(248, 250, 252, 0.8);
        cursor: pointer;
        font-size: 0.9rem;
        transition: background 0.15s ease, border-color 0.15s ease, color 0.15s ease;
    }

    .pill-group button:hover,
    .pill-group button:focus-visible {
        border-color: rgba(79, 70, 229, 0.55);
        background: rgba(79, 70, 229, 0.12);
        color: #312e81;
        outline: none;
    }

    .pill-group button.is-active {
        border-color: rgba(79, 70, 229, 0.7);
        background: rgba(79, 70, 229, 0.26);
        color: #312e81;
    }

    .stack label {
        display: grid;
        gap: 0.25rem;
    }

    #sales-filter-custom,
    #sales-export-custom {
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        align-items: end;
        padding-top: 0.15rem;
    }

    #sales-filter-custom strong,
    #sales-export-custom strong {
        grid-column: 1 / -1;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: rgba(15, 23, 42, 0.6);
    }

    #sales-filter-custom footer {
        grid-column: 1 / -1;
        display: flex;
        justify-content: flex-end;
        gap: 0.4rem;
    }

    .modal {
        position: fixed;
        inset: 0;
        display: flex;
        align-items: flex-start;
        justify-content: center;
        background: rgba(15, 23, 42, 0.45);
        padding: 1.5rem 1.25rem;
        z-index: 50;
        overflow-y: auto;
    }

    .modal.is-hidden {
        display: none;
    }

    .modal__content {
        background: #fff;
        border-radius: 0.9rem;
        padding: 1.35rem;
        width: min(560px, calc(100vw - 2.5rem));
        max-height: min(88vh, 600px);
        box-shadow: 0 18px 36px rgba(15, 23, 42, 0.18);
        display: grid;
        gap: 1rem;
        overflow-y: auto;
    }

    .modal__header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
    }

    .button-danger {
        border-color: rgba(220, 38, 38, 0.4);
        background: rgba(248, 113, 113, 0.16);
        color: #b91c1c;
    }

    .button-danger:hover,
    .button-danger:focus-visible {
        border-color: rgba(220, 38, 38, 0.55);
        background: rgba(248, 113, 113, 0.28);
        color: #7f1d1d;
    }

    .icon-button {
        width: 2rem;
        height: 2rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
        border: 1px solid rgba(15, 23, 42, 0.08);
        background: #fff;
        color: rgba(15, 23, 42, 0.72);
        cursor: pointer;
        transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease, color 0.15s ease, background 0.15s ease;
        padding: 0;
    }

    .icon-button svg {
        width: 1rem;
        height: 1rem;
    }

    .icon-button:hover,
    .icon-button:focus-visible {
        transform: translateY(-1px);
        border-color: rgba(79, 70, 229, 0.35);
        color: rgba(79, 70, 229, 0.8);
        box-shadow: 0 8px 16px rgba(79, 70, 229, 0.18);
        outline: none;
    }

    .icon-button--danger {
        border-color: rgba(220, 38, 38, 0.25);
        color: rgba(220, 38, 38, 0.72);
    }

    .icon-button--danger:hover,
    .icon-button--danger:focus-visible {
        border-color: rgba(220, 38, 38, 0.5);
        color: rgba(185, 28, 28, 0.85);
        box-shadow: 0 8px 16px rgba(220, 38, 38, 0.18);
    }

    @media (max-width: 960px) {
        .dashboard-grid {
            flex-direction: column;
        }

        .dashboard-nav {
            width: 100%;
        }

        .dashboard-content {
            padding: 1rem;
        }

        .modal {
            padding: 1.5rem 1rem;
        }

        .modal__content {
            width: 100%;
            max-height: calc(100vh - 4rem);
            padding: 1.5rem;
        }
    }
</style>
