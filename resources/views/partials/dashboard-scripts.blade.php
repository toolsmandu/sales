<script>
            const initializeDashboard = async () => {
                const navButtons = document.querySelectorAll('[data-panel-target]');
                const panels = document.querySelectorAll('.dashboard-panel');
                const productToggle = document.getElementById('add-product-toggle');
                const productFormWrapper = document.getElementById('product-form-wrapper');
                const productForm = document.getElementById('product-form');
                const productNameInput = document.getElementById('product-name');
                const variationsWrapper = document.getElementById('product-variations');
                const addVariationButton = document.getElementById('add-variation');
                const productSubmitButton = document.getElementById('product-submit');
                const cancelEditButton = document.getElementById('cancel-edit');
                const productList = document.getElementById('product-list');
                const productEmptyState = document.getElementById('product-empty');
                const paymentForm = document.getElementById('payment-method-form');
                const paymentInput = document.getElementById('payment-method-name');
                const paymentHelper = document.getElementById('payment-helper');
                const paymentList = document.getElementById('payment-method-list');
                const paymentOverviewList = document.getElementById('payment-overview-list');
                const paymentOverviewEmpty = document.getElementById('payment-overview-empty');
                const paymentSummaryItems = document.getElementById('payment-summary-items');
                const paymentSummaryMonthSelect = document.getElementById('payment-summary-month');
                const paymentSummaryYearSelect = document.getElementById('payment-summary-year');
                const paymentSummaryTableBody = document.getElementById('payment-summary-table');
                const paymentSummaryEmpty = document.getElementById('payment-summary-empty');
                const paymentLedgerFilter = document.getElementById('payment-ledger-filter');
                const paymentLedgerList = document.getElementById('payment-ledger-list');
                const paymentLedgerEmpty = document.getElementById('payment-ledger-empty');
                const salesPageSizeSelect = document.getElementById('sales-page-size');
                const salesPaginationInfo = document.getElementById('sales-pagination-info');
                const salesPrevPageButton = document.getElementById('sales-prev-page');
                const salesNextPageButton = document.getElementById('sales-next-page');
                const paymentPageSizeSelect = document.getElementById('payment-page-size');
                const paymentPaginationInfo = document.getElementById('payment-pagination-info');
                const paymentPrevPageButton = document.getElementById('payment-prev-page');
                const paymentNextPageButton = document.getElementById('payment-next-page');
                const paymentSalesTableBody = document.getElementById('payment-sales-table');
                const paymentSalesEmpty = document.getElementById('payment-sales-empty');
                const salesModal = document.getElementById('sales-modal');
                const openSalesModalButton = document.getElementById('open-sales-modal');
                const closeSalesModalButton = document.getElementById('close-sales-modal');
                const cancelSalesModalButton = document.getElementById('cancel-sales-modal');
                const salesForm = document.getElementById('sales-form');
                const salesDateInput = document.getElementById('sales-date');
                const salesProductInput = document.getElementById('sales-product-name');
                const salesProductOptionsList = document.getElementById('sales-product-options');
                const salesPhoneInput = document.getElementById('sales-phone');
                const salesEmailInput = document.getElementById('sales-email');
                const salesRemarksInput = document.getElementById('sales-remarks');
                const salesAmountInput = document.getElementById('sales-amount');
                const salesPaymentSelect = document.getElementById('sales-payment-method');
                const salesSubmitButton = salesForm?.querySelector('button[type="submit"]');
                const salesModalTitle = document.getElementById('sales-modal-title');
                const salesTableBody = document.getElementById('sales-record-list');
                const salesEmptyState = document.getElementById('sales-empty');
                const salesSummary = document.getElementById('sales-summary');
                const salesFilterButtons = document.querySelectorAll('[data-sales-filter]');
                const salesFilterCustomWrapper = document.getElementById('sales-filter-custom');
                const salesFilterFromInput = document.getElementById('sales-filter-from');
                const salesFilterToInput = document.getElementById('sales-filter-to');
                const applySalesFilterButton = document.getElementById('apply-sales-filter');
                const exportRangeButtons = document.querySelectorAll('[data-export-range]');
                const exportCustomWrapper = document.getElementById('sales-export-custom');
                const exportFromInput = document.getElementById('sales-export-from');
                const exportToInput = document.getElementById('sales-export-to');
                const exportButton = document.getElementById('sales-export');
                const filterToggleButton = document.getElementById('sales-filter-toggle');
                const exportToggleButton = document.getElementById('sales-export-toggle');
                const dropdowns = document.querySelectorAll('[data-dropdown]');
                const notificationToast = document.createElement('div');
                notificationToast.className = 'copy-toast is-hidden';
                document.body.appendChild(notificationToast);

                let editingIndex = null;
                let editingProductId = null;
                let editingSalesId = null;
                let activeSalesFilter = 'all';
                let customSalesFilter = { start: null, end: null };
                let activeExportRange = 'today';
                let openDropdown = null;
                let salesPageSize = 100;
                let salesCurrentPage = 1;
                let paymentPageSize = 100;
                let paymentCurrentPage = 1;
                let showCopyToastTimeoutId;

                const amountFormatter = new Intl.NumberFormat(undefined, {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0,
                });

                const monthFormatter = new Intl.DateTimeFormat('en-US', { month: 'short' });

                const defaultPaymentHelperText = paymentHelper?.textContent ?? '';

                if (salesPageSizeSelect) {
                    salesPageSizeSelect.value = String(salesPageSize);
                }

                salesPhoneInput?.addEventListener('paste', (event) => {
                    event.preventDefault();
                    const clipboardData = event.clipboardData?.getData('text') ?? '';
                    const sanitized = clipboardData
                        .replace(/[()\-\s]/g, '')
                        .replace(/^(\+?)/, (_, plus) => plus === '+' ? '+' : '');

                    const currentValue = salesPhoneInput.value;
                    const selectionStart = salesPhoneInput.selectionStart ?? currentValue.length;
                    const selectionEnd = salesPhoneInput.selectionEnd ?? currentValue.length;

                    salesPhoneInput.value = currentValue.slice(0, selectionStart)
                        + sanitized
                        + currentValue.slice(selectionEnd);
                    const cursor = selectionStart + sanitized.length;
                    salesPhoneInput.setSelectionRange(cursor, cursor);
                });

                if (paymentPageSizeSelect) {
                    paymentPageSizeSelect.value = String(paymentPageSize);
                }

                function createRecordId() {
                    return Date.now().toString(36) + '-' + Math.random().toString(16).slice(2);
                }

                function normalizePaymentMethod(value) {
                    return value.trim().toLowerCase();
                }

                function startOfDay(date) {
                    return new Date(date.getFullYear(), date.getMonth(), date.getDate());
                }

                function endOfDay(date) {
                    return new Date(date.getFullYear(), date.getMonth(), date.getDate(), 23, 59, 59, 999);
                }

                function formatDateForInput(date) {
                    const year = date.getFullYear();
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const day = String(date.getDate()).padStart(2, '0');
                    return `${year}-${month}-${day}`;
                }

                function formatDateForDisplay(date) {
                    if (!(date instanceof Date) || Number.isNaN(date.getTime())) {
                        return '';
                    }

                    const month = monthFormatter.format(date);
                    const day = String(date.getDate()).padStart(2, '0');
                    return `${month}-${day}`;
                }

                function parseDateInput(value) {
                    if (!value) {
                        return null;
                    }

                    const parts = value.split('-').map(Number);
                    if (parts.length !== 3 || parts.some((part) => Number.isNaN(part))) {
                        return null;
                    }

                    const [year, month, day] = parts;
                    return new Date(year, month - 1, day);
                }

                function formatAmount(value) {
                    return amountFormatter.format(Number(value ?? 0));
                }

                function parseSerialNumber(serial) {
                    if (typeof serial !== 'string') {
                        return null;
                    }

                    const match = /^TM(\d+)$/i.exec(serial.trim());
                    if (!match) {
                        return null;
                    }

                    const numeric = Number(match[1]);
                    return Number.isFinite(numeric) && numeric > 0 ? numeric : null;
                }

                function getSerialForTransaction(transaction) {
                    if (!transaction || transaction.type !== 'income') {
                        return '';
                    }

                    const refId = transaction.refId;
                    if (!refId) {
                        return '';
                    }

                    const sale = salesRecords.find((record) => record.id === refId);
                    return sale?.serialNumber ?? '';
                }

                let products = [];
                let paymentMethods = new Map();
                let salesRecords = [];
                let activePaymentFilter = null;

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
                const routes = {
                    bootstrap: '{{ route('dashboard.bootstrap') }}',
                    products: '{{ route('dashboard.products.store') }}',
                    paymentMethods: '{{ route('dashboard.payment-methods.store') }}',
                    sales: '{{ route('dashboard.orders.store') }}',
                };

                async function apiRequest(url, options = {}) {
                    const config = {
                        method: options.method ?? 'GET',
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        credentials: 'same-origin',
                        ...options,
                    };

                    if (config.body && !(config.body instanceof FormData)) {
                        config.headers['Content-Type'] = 'application/json';
                        config.body = JSON.stringify(config.body);
                    }

                    const response = await fetch(url, config);
                    if (!response.ok) {
                        let message = 'Request failed.';
                        try {
                            const errorBody = await response.json();
                            message = errorBody?.message
                                ?? Object.values(errorBody?.errors ?? {})[0]?.[0]
                                ?? message;
                        } catch (_) {
                            // Ignore JSON parse errors.
                        }
                        throw new Error(message);
                    }

                    if (response.status === 204) {
                        return null;
                    }

                    return response.json();
                }

                function hydrateDashboardState(payload) {
                    products = (payload?.products ?? []).map((product) => ({
                        id: product.id ?? createRecordId(),
                        name: product.name ?? '',
                        variations: Array.isArray(product.variations)
                            ? product.variations.filter((variation) => typeof variation === 'string' && variation.trim() !== '')
                            : [],
                    }));

                    const methodEntries = (payload?.payment_methods ?? []).map((method) => {
                        const slug = method.slug ?? normalizePaymentMethod(method.label ?? '');
                        const transactions = Array.isArray(method.transactions)
                            ? method.transactions.map((transaction) => ({
                                id: String(transaction.id ?? createRecordId()),
                                refId: transaction.sale_id ? String(transaction.sale_id) : transaction.refId ?? null,
                                type: transaction.type === 'expense' ? 'expense' : 'income',
                                amount: Number(transaction.amount) || 0,
                                phone: typeof transaction.phone === 'string' ? transaction.phone : '-',
                                timestamp: transaction.occurred_at
                                    ? Date.parse(transaction.occurred_at)
                                    : (typeof transaction.timestamp === 'number' ? transaction.timestamp : Date.now()),
                                balanceAfter: Number(transaction.balance_after ?? transaction.balanceAfter ?? 0),
                            }))
                            : [];

                        return [slug, {
                            id: method.id ?? createRecordId(),
                            label: method.label ?? slug,
                            slug,
                            balance: Number(method.balance) || 0,
                            transactions,
                        }];
                    });

                    paymentMethods = new Map(methodEntries);

                    salesRecords = (payload?.sales ?? []).map((record) => {
                        const dateValue = record.purchase_date
                            ? new Date(record.purchase_date)
                            : (record.purchaseDate ? new Date(record.purchaseDate) : new Date());
                        const validDate = Number.isNaN(dateValue.getTime()) ? new Date() : dateValue;

                        const productName = record.product_name ?? record.productName ?? '';
                        const remarks = record.remarks ?? record.remark ?? '';

                        return {
                            id: String(record.id ?? createRecordId()),
                            serialNumber: typeof record.serial_number === 'string'
                                ? record.serial_number
                                : (record.serialNumber ?? ''),
                            purchaseDate: startOfDay(validDate),
                            productName,
                            remarks,
                            productDisplay: formatProductLabel(productName, remarks),
                            phone: record.phone ?? '',
                            email: record.email ?? '',
                            salesAmount: Number(record.sales_amount ?? record.salesAmount ?? 0),
                            paymentMethod: record.payment_method_slug
                                ?? normalizePaymentMethod(record.paymentMethod ?? ''),
                            paymentMethodLabel: record.payment_method_label ?? record.paymentMethodLabel ?? '',
                            __createdAt: record.created_at
                                ? Date.parse(record.created_at)
                                : (record.__createdAt ?? Date.now()),
                        };
                    });
                }

                async function refreshDashboardState(options = {}) {
                    try {
                        const payload = await apiRequest(routes.bootstrap);
                        hydrateDashboardState(payload);

                        if (!options.keepPaymentFilter || !paymentMethods.has(activePaymentFilter)) {
                            activePaymentFilter = paymentMethods.size > 0 ? Array.from(paymentMethods.keys())[0] : null;
                        }

                        renderProducts();
                        renderPaymentMethods();
                        renderSalesRecords();
                    } catch (error) {
                        console.error('Failed to load dashboard data.', error);
                        window.alert(error.message ?? 'Unable to load dashboard data.');
                    } finally {
                    }
                }

                function getPaymentMethodLabel(value, fallback = '') {
                    if (!value) {
                        return fallback || '';
                    }

                    const normalized = normalizePaymentMethod(value);
                    return paymentMethods.get(normalized)?.label ?? fallback || value;
                }

                function formatProductLabel(name = '', remarks = '') {
                    const trimmedName = String(name ?? '').trim();
                    const trimmedRemarks = String(remarks ?? '').trim();

                    if (trimmedName === '' && trimmedRemarks === '') {
                        return '';
                    }

                    return trimmedRemarks ? `${trimmedName} - ${trimmedRemarks}` : trimmedName;
                }

                function buildProductOptions() {
                    const options = [];
                    const seen = new Set();

                    const pushOption = (label) => {
                        const value = (label ?? '').trim();
                        if (value === '' || seen.has(value)) {
                            return;
                        }

                        seen.add(value);
                        options.push({ label: value, value });
                    };

                    products.forEach(({ name, variations }) => {
                        const baseName = typeof name === 'string' ? name.trim() : '';
                        if (baseName === '') {
                            return;
                        }

                        pushOption(baseName);

                        const formattedVariations = Array.isArray(variations)
                            ? variations
                                .map((variation) => (typeof variation === 'string' ? variation.trim() : ''))
                                .filter((variation) => variation !== '')
                            : [];

                        formattedVariations.forEach((variation) => {
                            pushOption(`${baseName} - ${variation}`);
                        });
                    });

                    return options;
                }

                function setProductFormVisibility(visible) {
                    if (!productFormWrapper) {
                        return;
                    }

                    productFormWrapper.classList.toggle('is-hidden', !visible);
                    productFormWrapper.setAttribute('aria-hidden', visible ? 'false' : 'true');

                    if (visible) {
                        productNameInput?.focus();
                    }
                }

                function updateVariationRemoveButtons() {
                    if (!variationsWrapper) {
                        return;
                    }

                    const rows = Array.from(variationsWrapper.querySelectorAll('.variation-field'));

                    rows.forEach((row, index) => {
                        const removeButton = row.querySelector('.ghost-button');
                        if (!removeButton) {
                            return;
                        }

                        const hide = rows.length === 1 && index === 0;
                        removeButton.classList.toggle('is-hidden', hide);
                        removeButton.disabled = hide;
                    });
                }

                function createVariationField(value = '') {
                    if (!variationsWrapper) {
                        return null;
                    }

                    const row = document.createElement('div');
                    row.className = 'variation-field';

                    const input = document.createElement('input');
                    input.type = 'text';
                    input.name = 'product_variations[]';
                    input.placeholder = 'Eg. Size M / Blue';
                    input.value = value;
                    input.setAttribute('aria-labelledby', 'product-variation-label');
                    input.addEventListener('input', () => {
                        input.setCustomValidity('');
                    });

                    const removeButton = document.createElement('button');
                    removeButton.type = 'button';
                    removeButton.className = 'ghost-button';
                    removeButton.textContent = 'Remove';
                    removeButton.addEventListener('click', () => {
                        row.remove();
                        updateVariationRemoveButtons();
                    });

                    row.append(input, removeButton);
                    variationsWrapper.appendChild(row);

                    updateVariationRemoveButtons();
                    return row;
                }

                function resetVariationFields() {
                    if (!variationsWrapper) {
                        return;
                    }

                    variationsWrapper.innerHTML = '';
                    createVariationField();
                }

                function ensureVariationField() {
                    if (!variationsWrapper) {
                        return;
                    }

                    if (variationsWrapper.children.length === 0) {
                        createVariationField();
                    }
                }

                function closeAllDropdowns() {
                    dropdowns.forEach((dropdown) => {
                        const menu = dropdown.querySelector('.dropdown__menu');
                        const toggle = dropdown.querySelector('button');
                        menu?.classList.add('is-hidden');
                        toggle?.classList.remove('is-active');
                    });
                    openDropdown = null;
                }

                function toggleDropdown(element) {
                    if (!element) {
                        return;
                    }

                    const menu = element.querySelector('.dropdown__menu');
                    const toggle = element.querySelector('button');

                    if (!menu || !toggle) {
                        return;
                    }

                    const isHidden = menu.classList.contains('is-hidden');
                    closeAllDropdowns();

                    if (isHidden) {
                        menu.classList.remove('is-hidden');
                        toggle.classList.add('is-active');
                        openDropdown = element;
                    }
                }

                function enterCreateMode() {
                    editingIndex = null;
                    editingProductId = null;

                    if (productSubmitButton) {
                        productSubmitButton.textContent = 'Save product';
                    }

                    cancelEditButton?.classList.add('is-hidden');

                    productForm?.reset();
                    resetVariationFields();
                }

                function enterEditMode(index) {
                    const product = products[index];
                    if (!product || !productForm) {
                        return;
                    }

                    editingIndex = index;
                    editingProductId = product.id;

                    if (productSubmitButton) {
                        productSubmitButton.textContent = 'Update product';
                    }

                    cancelEditButton?.classList.remove('is-hidden');

                    productForm.reset();
                    productNameInput.value = product.name;

                    if (variationsWrapper) {
                        variationsWrapper.innerHTML = '';
                        if (product.variations.length === 0) {
                            createVariationField();
                        } else {
                            product.variations.forEach((variation) => createVariationField(variation));
                        }
                    }

                    updateVariationRemoveButtons();
                    setProductFormVisibility(true);
                }

                async function deleteProduct(index) {
                    const product = products[index];
                    if (!product) {
                        return;
                    }

                    const confirmation = window.confirm(`Delete "${product.name}"? This action cannot be undone.`);
                    if (!confirmation) {
                        return;
                    }

                    try {
                        await apiRequest(`${routes.products}/${product.id}`, { method: 'DELETE' });
                        await refreshDashboardState({ keepPaymentFilter: true });
                        enterCreateMode();
                        setProductFormVisibility(false);
                    } catch (error) {
                        window.alert(error.message ?? 'Unable to delete product.');
                    }
                }

                function switchPanel(panelKey) {
                    panels.forEach((panel) => {
                        panel.classList.toggle('is-active', panel.dataset.panel === panelKey);
                    });

                    navButtons.forEach((button) => {
                        button.classList.toggle('is-active', button.dataset.panelTarget === panelKey);
                    });
                }

                navButtons.forEach((button) => {
                    button.addEventListener('click', () => {
                        switchPanel(button.dataset.panelTarget);
                    });
                });

                filterToggleButton?.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    toggleDropdown(filterToggleButton.closest('[data-dropdown]'));
                });

                exportToggleButton?.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    toggleDropdown(exportToggleButton.closest('[data-dropdown]'));
                });

                document.addEventListener('click', (event) => {
                    if (!event.target.closest('[data-dropdown]')) {
                        closeAllDropdowns();
                    }
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        closeAllDropdowns();
                    }
                });

                paymentLedgerFilter?.addEventListener('change', (event) => {
                    activePaymentFilter = event.target.value;
                    paymentCurrentPage = 1;
                    renderPaymentLedger();
                });

                paymentSummaryMonthSelect?.addEventListener('change', renderMonthlySummaryTable);
                paymentSummaryYearSelect?.addEventListener('change', renderMonthlySummaryTable);

                                
                function renderProducts() {
                    if (!productList) {
                        return;
                    }

                    const preservedProductSelection = salesProductInput?.value ?? '';
                    productList.innerHTML = '';

                    if (products.length === 0) {
                        productEmptyState?.classList.remove('is-hidden');
                        renderSalesProductOptions('');
                        return;
                    }

                    productEmptyState?.classList.add('is-hidden');

                    products.forEach(({ name, variations }, index) => {
                        const row = document.createElement('tr');

                        const variationSlug = variations
                            .map((variation) => variation.toLowerCase().replace(/\s+/g, '-'))
                            .join('-');
                        row.dataset.product = `${name.toLowerCase().replace(/\s+/g, '-')}-${variationSlug}`;

                        const indexCell = document.createElement('td');
                        indexCell.textContent = String(index + 1);

                        const nameCell = document.createElement('td');
                        const variationLabel = variations.length > 0 ? variations.join(', ') : '';
                        nameCell.textContent = variationLabel ? `${name} - ${variationLabel}` : name;

                        const actionsCell = document.createElement('td');
                        actionsCell.className = 'table-actions';

                        const editButton = document.createElement('button');
                        editButton.type = 'button';
                        editButton.className = 'ghost-button';
                        editButton.textContent = 'Edit';
                        editButton.addEventListener('click', () => enterEditMode(index));

                        const deleteButton = document.createElement('button');
                        deleteButton.type = 'button';
                        deleteButton.className = 'ghost-button button-danger';
                        deleteButton.textContent = 'Delete';
                        deleteButton.addEventListener('click', () => deleteProduct(index));

                        actionsCell.append(editButton, deleteButton);
                        row.append(indexCell, nameCell, actionsCell);
                        productList.appendChild(row);
                    });

                    renderSalesProductOptions(preservedProductSelection);
                }

                addVariationButton?.addEventListener('click', () => {
                    const newField = createVariationField();
                    const lastInput = newField?.querySelector('input');
                    lastInput?.focus();
                });

                productForm?.addEventListener('submit', async (event) => {
                    event.preventDefault();

                    if (!productForm.reportValidity()) {
                        return;
                    }

                    const productName = productNameInput.value.trim();

                    if (productName === '') {
                        productNameInput.setCustomValidity('Product name is required.');
                        productNameInput.reportValidity();
                        return;
                    }

                    const variationValues = variationsWrapper
                        ? Array.from(variationsWrapper.querySelectorAll('input[name="product_variations[]"]'))
                            .map((input) => input.value.trim())
                            .filter((value, index, list) => value !== '' && list.indexOf(value) === index)
                        : [];

                    const productPayload = { name: productName };
                    if (variationValues.length > 0) {
                        productPayload.variations = variationValues;
                    }
                    const endpoint = editingProductId
                        ? `${routes.products}/${editingProductId}`
                        : routes.products;
                    const method = editingProductId ? 'PUT' : 'POST';

                    try {
                        productSubmitButton?.setAttribute('disabled', 'disabled');
                        await apiRequest(endpoint, { method, body: productPayload });
                        await refreshDashboardState({ keepPaymentFilter: true });
                        enterCreateMode();
                        setProductFormVisibility(false);
                    } catch (error) {
                        window.alert(error.message ?? 'Unable to save product.');
                    } finally {
                        productSubmitButton?.removeAttribute('disabled');
                    }
                });

                productToggle?.addEventListener('click', () => {
                    const isVisible = productFormWrapper && !productFormWrapper.classList.contains('is-hidden');
                    if (isVisible) {
                        enterCreateMode();
                        setProductFormVisibility(false);
                    } else {
                        enterCreateMode();
                        ensureVariationField();
                        setProductFormVisibility(true);
                    }
                });

                cancelEditButton?.addEventListener('click', () => {
                    enterCreateMode();
                    setProductFormVisibility(false);
                });

                function clearPaymentInputValidity() {
                    if (!paymentInput) {
                        return;
                    }

                    paymentInput.setCustomValidity('');
                    if (paymentHelper) {
                        paymentHelper.textContent = defaultPaymentHelperText;
                    }
                }

                paymentInput?.addEventListener('input', clearPaymentInputValidity);

                function renderPaymentMethodOptions(preservedValue = '') {
                    if (!salesPaymentSelect) {
                        return;
                    }

                    const currentValue = preservedValue || salesPaymentSelect.value;
                    salesPaymentSelect.innerHTML = '';

                    if (paymentMethods.size === 0) {
                        const option = document.createElement('option');
                        option.value = '';
                        option.textContent = 'No payment methods available';
                        option.disabled = true;
                        option.selected = true;
                        salesPaymentSelect.appendChild(option);
                        salesPaymentSelect.disabled = true;
                        return;
                    }

                    salesPaymentSelect.disabled = false;

                    paymentMethods.forEach((method, key) => {
                        const option = document.createElement('option');
                        option.value = method.slug ?? key;
                        option.textContent = method.label;
                        if (option.value === currentValue) {
                            option.selected = true;
                        }
                        salesPaymentSelect.appendChild(option);
                    });

                    if (!salesPaymentSelect.value && salesPaymentSelect.options.length > 0) {
                        salesPaymentSelect.selectedIndex = 0;
                    }
                }

                function recalculatePaymentMethod(method) {
                    if (!method) {
                        return;
                    }

                    const transactions = Array.isArray(method.transactions)
                        ? method.transactions
                        : [];

                    if (transactions.length === 0) {
                        method.balance = Number(method.balance) || 0;
                        return;
                    }

                    transactions.sort((a, b) => (a.timestamp ?? 0) - (b.timestamp ?? 0));

                    let balance = 0;
                    transactions.forEach((transaction) => {
                        if (transaction.type === 'income') {
                            balance += transaction.amount;
                        } else if (transaction.type === 'expense') {
                            balance -= transaction.amount;
                        }

                        transaction.balanceAfter = balance;
                    });

                    method.balance = balance;
                }

                function renderPaymentSummary() {
                    if (!paymentSummaryItems) {
                        return;
                    }

                    paymentSummaryItems.innerHTML = '';

                    const totalElement = document.getElementById('payments-balance-total');
                    const entries = Array.from(paymentMethods.entries());

                    if (entries.length === 0) {
                        const emptyLine = document.createElement('p');
                        emptyLine.className = 'helper-text';
                        emptyLine.textContent = 'Add a payment method to begin tracking balances.';
                        paymentSummaryItems.appendChild(emptyLine);
                        if (totalElement) {
                            totalElement.querySelector('strong').textContent = 'Rs 0';
                        }
                        updateMonthlySummaryYearOptions();
                        renderMonthlySummaryTable();
                        return;
                    }

                    let runningTotal = 0;

                    entries.forEach(([, method]) => {
                        recalculatePaymentMethod(method);
                        runningTotal += Number(method.balance) || 0;

                        const item = document.createElement('div');
                        item.className = 'payments-balance-item';
                        item.innerHTML = `
                            <div class="payments-balance-item__info">
                                <strong>${method.label}</strong>
                                <span class="payments-balance-item__meta">${method.transactions?.length ?? 0} transactions</span>
                            </div>
                            <span class="payments-balance-amount">Rs ${formatAmount(method.balance)}</span>
                        `;
                        paymentSummaryItems.appendChild(item);
                    });

                    if (totalElement) {
                        const strong = totalElement.querySelector('strong');
                        if (strong) {
                            strong.textContent = `Rs ${formatAmount(runningTotal)}`;
                        }
                    }

                    updateMonthlySummaryYearOptions();
                    renderMonthlySummaryTable();
                }

                function getMonthlySummaryMonthFilter() {
                    if (!paymentSummaryMonthSelect || paymentSummaryMonthSelect.disabled) {
                        return null;
                    }

                    const value = paymentSummaryMonthSelect.value ?? 'all';
                    return value === 'all' ? null : Number(value);
                }

                function getMonthlySummaryYearFilter() {
                    if (!paymentSummaryYearSelect || paymentSummaryYearSelect.disabled) {
                        return null;
                    }

                    const value = paymentSummaryYearSelect.value ?? 'all';
                    return value === 'all' ? null : Number(value);
                }

                function updateMonthlySummaryYearOptions() {
                    if (!paymentSummaryYearSelect) {
                        return;
                    }

                    const currentValue = paymentSummaryYearSelect.value || 'all';
                    const years = new Set();

                    paymentMethods.forEach((method) => {
                        (method.transactions ?? []).forEach((transaction) => {
                            if (typeof transaction?.timestamp !== 'number') {
                                return;
                            }

                            const date = new Date(transaction.timestamp);
                            if (!Number.isNaN(date.getTime())) {
                                years.add(date.getFullYear());
                            }
                        });
                    });

                    const sortedYears = Array.from(years.values()).sort((a, b) => b - a);

                    paymentSummaryYearSelect.innerHTML = '';

                    const addOption = (value, label) => {
                        const option = document.createElement('option');
                        option.value = String(value);
                        option.textContent = label;
                        paymentSummaryYearSelect.appendChild(option);
                    };

                    addOption('all', 'All');
                    sortedYears.forEach((year) => addOption(year, String(year)));

                    if (sortedYears.some((year) => String(year) === currentValue)) {
                        paymentSummaryYearSelect.value = currentValue;
                    } else {
                        paymentSummaryYearSelect.value = 'all';
                    }
                }

                function renderMonthlySummaryTable() {
                    if (!paymentSummaryTableBody) {
                        return;
                    }

                    paymentSummaryTableBody.innerHTML = '';

                    const entries = Array.from(paymentMethods.entries());

                    if (entries.length === 0) {
                        if (paymentSummaryEmpty) {
                            paymentSummaryEmpty.textContent = 'Add a payment method to begin tracking balances.';
                            paymentSummaryEmpty.classList.remove('is-hidden');
                        }
                        return;
                    }

                    const monthFilter = getMonthlySummaryMonthFilter();
                    const yearFilter = getMonthlySummaryYearFilter();

                    let hasMatchingTransactions = false;

                    entries.forEach(([, method]) => {
                        const transactions = Array.isArray(method.transactions) ? method.transactions : [];
                        let incomeTotal = 0;
                        let expenseTotal = 0;
                        let matched = false;

                        transactions.forEach((transaction) => {
                            if (typeof transaction?.timestamp !== 'number') {
                                return;
                            }

                            const date = new Date(transaction.timestamp);
                            if (Number.isNaN(date.getTime())) {
                                return;
                            }

                            if (monthFilter !== null && date.getMonth() + 1 !== monthFilter) {
                                return;
                            }

                            if (yearFilter !== null && date.getFullYear() !== yearFilter) {
                                return;
                            }

                            matched = true;
                            if (transaction.type === 'income') {
                                incomeTotal += Number(transaction.amount) || 0;
                            } else if (transaction.type === 'expense') {
                                expenseTotal += Number(transaction.amount) || 0;
                            }
                        });

                        if (matched) {
                            hasMatchingTransactions = true;
                        }

                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${method.label}</td>
                            <td>Rs ${formatAmount(incomeTotal)}</td>
                            <td>Rs ${formatAmount(expenseTotal)}</td>
                        `;
                        paymentSummaryTableBody.appendChild(row);
                    });

                    if (paymentSummaryEmpty) {
                        if (hasMatchingTransactions) {
                            paymentSummaryEmpty.classList.add('is-hidden');
                        } else {
                            paymentSummaryEmpty.textContent = 'No transactions match the selected month/year.';
                            paymentSummaryEmpty.classList.remove('is-hidden');
                        }
                    }
                }

                function renderPaymentSalesRecords(methodKey) {
                    if (!paymentSalesTableBody || !paymentSalesEmpty) {
                        return;
                    }

                    paymentSalesTableBody.innerHTML = '';

                    const normalizedKey = methodKey ? normalizePaymentMethod(methodKey) : null;

                    if (!normalizedKey) {
                        paymentSalesEmpty.textContent = 'Select a payment method to view related sales records.';
                        paymentSalesEmpty.classList.remove('is-hidden');
                        return;
                    }

                    const matchingSales = salesRecords
                        .filter((sale) => {
                            const saleKey = sale.paymentMethod
                                ?? normalizePaymentMethod(sale.paymentMethodLabel ?? '');
                            return saleKey && saleKey === normalizedKey;
                        })
                        .sort((a, b) => {
                            const aTime = a.__createdAt ?? (a.purchaseDate instanceof Date ? a.purchaseDate.getTime() : 0);
                            const bTime = b.__createdAt ?? (b.purchaseDate instanceof Date ? b.purchaseDate.getTime() : 0);
                            return (bTime ?? 0) - (aTime ?? 0);
                        });

                    if (matchingSales.length === 0) {
                        paymentSalesEmpty.textContent = 'No sales recorded for this payment method yet.';
                        paymentSalesEmpty.classList.remove('is-hidden');
                        return;
                    }

                    paymentSalesEmpty.classList.add('is-hidden');

                    matchingSales.forEach((sale) => {
                        const recordedAt = sale.__createdAt
                            ? new Date(sale.__createdAt)
                            : (sale.purchaseDate instanceof Date ? sale.purchaseDate : new Date());
                        const formattedDate = formatDateForDisplay(recordedAt);
                        const phone = sale.phone && sale.phone.trim() !== '' ? sale.phone : '-';
                        const email = sale.email && sale.email.trim() !== '' ? sale.email : '-';
                        const amountValue = Number(sale.salesAmount) || 0;

                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${sale.serialNumber || '-'}</td>
                            <td>${formattedDate}</td>
                            <td>${sale.productDisplay ?? '-'}</td>
                            <td>
                                <span class="cell-with-action">
                                    <span>${phone}</span>
                                    ${phone !== '-' ? `<button type="button" class="cell-action-button" data-copy="${phone}" aria-label="Copy phone ${sale.serialNumber ?? ''}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 7V5a2 2 0 012-2h9a2 2 0 012 2v11a2 2 0 01-2 2h-2" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><rect x="4" y="7" width="12" height="12" rx="2" ry="2" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></button>` : ''}
                                </span>
                            </td>
                            <td>
                                <span class="cell-with-action">
                                    <span>${email}</span>
                                    ${email !== '-' ? `<button type="button" class="cell-action-button" data-copy="${email}" aria-label="Copy email ${sale.serialNumber ?? ''}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 7V5a2 2 0 012-2h9a2 2 0 012 2v11a2 2 0 01-2 2h-2" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><rect x="4" y="7" width="12" height="12" rx="2" ry="2" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></button>` : ''}
                                </span>
                            </td>
                            <td>Rs ${formatAmount(amountValue)}</td>
                            <td>${sale.paymentMethodLabel || sale.paymentMethod || '-'}</td>
                        `;
                        paymentSalesTableBody.appendChild(row);
                    });
                }

                function renderPaymentLedger() {
                    if (!paymentLedgerList || !paymentLedgerEmpty || !paymentLedgerFilter) {
                        return;
                    }

                    const entries = Array.from(paymentMethods.entries());

                    paymentLedgerFilter.innerHTML = '';

                    if (entries.length === 0) {
                        activePaymentFilter = null;
                        paymentLedgerList.innerHTML = '';
                        paymentLedgerEmpty.textContent = 'Add a payment method to begin tracking transactions.';
                        paymentLedgerEmpty.classList.remove('is-hidden');

                        paymentCurrentPage = 1;
                        if (paymentPaginationInfo) {
                            paymentPaginationInfo.textContent = 'No transactions to display';
                        }
                        if (paymentPrevPageButton) {
                            paymentPrevPageButton.disabled = true;
                        }
                        if (paymentNextPageButton) {
                            paymentNextPageButton.disabled = true;
                        }
                        renderPaymentSalesRecords(null);
                        return;
                    }

                    if (!activePaymentFilter || !paymentMethods.has(activePaymentFilter)) {
                        activePaymentFilter = entries[0][0];
                    }

                    entries.forEach(([key, method]) => {
                        const option = document.createElement('option');
                        option.value = key;
                        option.textContent = method.label;
                        if (key === activePaymentFilter) {
                            option.selected = true;
                        }
                        paymentLedgerFilter.appendChild(option);
                    });

                    const activeMethod = paymentMethods.get(activePaymentFilter);
                    if (!activeMethod) {
                        paymentLedgerList.innerHTML = '';
                        paymentLedgerEmpty.textContent = 'Select a payment method to view its transactions.';
                        paymentLedgerEmpty.classList.remove('is-hidden');

                        paymentCurrentPage = 1;
                        if (paymentPaginationInfo) {
                            paymentPaginationInfo.textContent = 'No transactions to display';
                        }
                        if (paymentPrevPageButton) {
                            paymentPrevPageButton.disabled = true;
                        }
                        if (paymentNextPageButton) {
                            paymentNextPageButton.disabled = true;
                        }
                        renderPaymentSalesRecords(null);
                        return;
                    }

                    paymentLedgerList.innerHTML = '';

                    const methodKey = activeMethod.slug ?? normalizePaymentMethod(activeMethod.label ?? '');
                    renderPaymentSalesRecords(methodKey);
                    recalculatePaymentMethod(activeMethod);

                    const baseTransactions = Array.isArray(activeMethod.transactions)
                        ? activeMethod.transactions.slice()
                        : [];

                    const seenSaleIds = new Set(
                        baseTransactions
                            .map((transaction) => transaction?.refId)
                            .filter((refId) => typeof refId === 'string' && refId.trim() !== '')
                            .map((refId) => refId.trim()),
                    );

                    const saleLinkedTransactions = salesRecords
                        .filter((sale) => {
                            const saleMethodKey = sale.paymentMethod
                                ?? normalizePaymentMethod(sale.paymentMethodLabel ?? '');
                            return saleMethodKey && methodKey && saleMethodKey === methodKey;
                        })
                        .filter((sale) => !seenSaleIds.has(String(sale.id)))
                        .map((sale) => ({
                            id: `sale-${sale.id}`,
                            refId: String(sale.id),
                            type: 'income',
                            amount: Number(sale.salesAmount) || 0,
                            phone: sale.phone ?? '-',
                            timestamp: sale.__createdAt
                                ?? (sale.purchaseDate instanceof Date ? sale.purchaseDate.getTime() : Date.now()),
                            source: 'sale',
                        }));

                    const transactions = baseTransactions
                        .concat(saleLinkedTransactions)
                        .map((transaction) => ({ ...transaction }))
                        .sort((a, b) => {
                            const aSerial = parseSerialNumber(getSerialForTransaction(a)) ?? 0;
                            const bSerial = parseSerialNumber(getSerialForTransaction(b)) ?? 0;
                            if (aSerial !== bSerial) {
                                return bSerial - aSerial;
                            }
                            const aTimestamp = a.timestamp ?? 0;
                            const bTimestamp = b.timestamp ?? 0;
                            return bTimestamp - aTimestamp;
                        });

                    const chronologicalTransactions = transactions
                        .slice()
                        .sort((a, b) => (a.timestamp ?? 0) - (b.timestamp ?? 0));

                    let runningBalance = 0;
                    chronologicalTransactions.forEach((transaction) => {
                        if (transaction.type === 'income') {
                            runningBalance += Number(transaction.amount) || 0;
                        } else if (transaction.type === 'expense') {
                            runningBalance -= Number(transaction.amount) || 0;
                        }
                        transaction.__computedBalance = runningBalance;
                    });
                    activeMethod.balance = runningBalance;

                    const totalTransactions = transactions.length;
                    const totalPages = totalTransactions === 0 ? 1 : Math.ceil(totalTransactions / paymentPageSize);

                    if (totalTransactions === 0) {
                        paymentCurrentPage = 1;
                        paymentLedgerEmpty.textContent = 'No transactions recorded for this payment method yet.';
                        paymentLedgerEmpty.classList.remove('is-hidden');
                        if (paymentPaginationInfo) {
                            paymentPaginationInfo.textContent = 'No transactions to display';
                        }
                        if (paymentPrevPageButton) {
                            paymentPrevPageButton.disabled = true;
                        }
                        if (paymentNextPageButton) {
                            paymentNextPageButton.disabled = true;
                        }
                        return;
                    }

                    paymentLedgerEmpty.classList.add('is-hidden');

                    paymentCurrentPage = Math.min(Math.max(paymentCurrentPage, 1), totalPages);

                    if (paymentPageSizeSelect && Number(paymentPageSizeSelect.value) !== paymentPageSize) {
                        paymentPageSizeSelect.value = String(paymentPageSize);
                    }

                    const startIndex = (paymentCurrentPage - 1) * paymentPageSize;
                    const paginatedTransactions = transactions.slice(startIndex, startIndex + paymentPageSize);

                    paginatedTransactions.forEach((transaction) => {
                        const date = formatDateForDisplay(new Date(transaction.timestamp));
                        const income = transaction.type === 'income' ? `Rs ${formatAmount(transaction.amount)}` : '-';
                        const expense = transaction.type === 'expense' ? `Rs ${formatAmount(transaction.amount)}` : '-';
                        const phone = transaction.phone && transaction.phone.trim() !== '' ? transaction.phone : '-';
                        const serial = getSerialForTransaction(transaction) || '-';

                        const row = document.createElement('tr');
                        if (transaction.type === 'expense') {
                            row.classList.add('ledger-expense');
                        }
                        row.innerHTML = `
                            <td>${serial}</td>
                            <td>${date}</td>
                            <td>
                                <span class="cell-with-action">
                                    <span>${phone}</span>
                                    ${transaction.type === 'income' && phone !== '-'
                                        ? `<button type="button" class="cell-action-button" data-copy="${phone}" aria-label="Copy phone"><svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path d="M8 7V5a2 2 0 012-2h9a2 2 0 012 2v11a2 2 0 01-2 2h-2" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><rect x="4" y="7" width="12" height="12" rx="2" ry="2" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></button>`
                                        : ''}
                                </span>
                            </td>
                            <td>${income}</td>
                            <td>${expense}</td>
                            <td>Rs ${formatAmount(transaction.__computedBalance ?? transaction.balanceAfter ?? 0)}</td>
                        `;
                        paymentLedgerList.appendChild(row);
                    });

                    if (paymentPaginationInfo) {
                        const pages = Array.from({ length: totalPages }, (_, i) => i + 1).join(', ');
                        paymentPaginationInfo.textContent = totalTransactions === 0 ? 'No transactions to display' : pages;
                    }

                    if (paymentPrevPageButton) {
                        paymentPrevPageButton.disabled = paymentCurrentPage <= 1;
                    }

                    if (paymentNextPageButton) {
                        paymentNextPageButton.disabled = paymentCurrentPage >= totalPages;
                    }

                    renderMonthlySummaryTable();
                }

                async function renamePaymentMethod(methodKey) {
                    const method = paymentMethods.get(methodKey);
                    if (!method) {
                        return;
                    }

                    const newLabel = window.prompt(`Rename "${method.label}" to:`, method.label);
                    if (newLabel === null) {
                        return;
                    }

                    const trimmedLabel = newLabel.trim();
                    if (trimmedLabel === '') {
                        window.alert('Payment method name cannot be empty.');
                        return;
                    }

                    try {
                        await apiRequest(`${routes.paymentMethods}/${method.slug}`, {
                            method: 'PUT',
                            body: { label: trimmedLabel },
                        });
                        await refreshDashboardState({ keepPaymentFilter: true });
                    } catch (error) {
                        window.alert(error.message ?? 'Unable to rename payment method.');
                    }
                }

                function renderPaymentMethods() {
                    const entries = Array.from(paymentMethods.entries());

                    if (paymentOverviewList) {
                        paymentOverviewList.innerHTML = '';
                    }

                    if (entries.length === 0) {
                        paymentOverviewEmpty?.classList.remove('is-hidden');
                    } else {
                        paymentOverviewEmpty?.classList.add('is-hidden');

                        if (paymentOverviewList) {
                            entries.forEach(([, method]) => {
                                const item = document.createElement('li');
                                const label = document.createElement('strong');
                                label.textContent = method.label;
                                item.appendChild(label);
                                paymentOverviewList.appendChild(item);
                            });
                        }
                    }

                    if (paymentList) {
                        paymentList.innerHTML = '';

                        entries.forEach(([key, method]) => {
                            const item = document.createElement('li');
                            item.dataset.method = key;
                            const label = document.createElement('span');
                            label.textContent = method.label;

                            const editButton = document.createElement('button');
                            editButton.type = 'button';
                            editButton.className = 'ghost-button pill-action';
                            editButton.textContent = 'Edit';
                            editButton.addEventListener('click', () => renamePaymentMethod(key));

                            item.append(label, editButton);
                            paymentList.appendChild(item);
                        });
                    }

                    renderPaymentMethodOptions();
                    renderPaymentSummary();
                    renderPaymentLedger();
                }

                function renderSalesProductOptions(preservedValue = '') {
                    if (!salesProductOptionsList) {
                        return;
                    }

                    const allOptions = buildProductOptions();
                    salesProductOptionsList.innerHTML = '';

                    if (!salesProductInput) {
                        return;
                    }

                    if (allOptions.length === 0) {
                        salesProductInput.value = '';
                        salesProductInput.placeholder = 'No products available';
                        salesProductInput.disabled = true;
                        return;
                    }

                    salesProductInput.disabled = false;
                    salesProductInput.placeholder = 'Select product...';

                    allOptions.forEach(({ value, label }) => {
                        const option = document.createElement('option');
                        option.value = value;
                        option.label = label;
                        salesProductOptionsList.appendChild(option);
                    });

                    if (preservedValue) {
                        salesProductInput.value = preservedValue;
                    } else if (!salesProductInput.value && allOptions.length === 1) {
                        salesProductInput.value = allOptions[0].value;
                    }
                }

                function isKnownProduct(value) {
                    if (!value) {
                        return false;
                    }

                    const normalizedValue = value.trim();
                    if (normalizedValue === '') {
                        return false;
                    }

                    return buildProductOptions().some(({ value: entryValue }) => entryValue === normalizedValue);
                }

                salesProductInput?.addEventListener('input', () => {
                    salesProductInput.setCustomValidity('');
                });

                salesPageSizeSelect?.addEventListener('change', (event) => {
                    const value = Number(event.target.value);
                    if (Number.isFinite(value) && value > 0) {
                        salesPageSize = value;
                        salesCurrentPage = 1;
                        renderSalesRecords();
                    }
                });

                salesPrevPageButton?.addEventListener('click', () => {
                    if (salesCurrentPage > 1) {
                        salesCurrentPage -= 1;
                        renderSalesRecords();
                    }
                });

                salesNextPageButton?.addEventListener('click', () => {
                    const totalRecords = getRecordsForRange(activeSalesFilter, customSalesFilter).length;
                    const totalPages = totalRecords === 0 ? 1 : Math.ceil(totalRecords / salesPageSize);
                    if (salesCurrentPage < totalPages) {
                        salesCurrentPage += 1;
                        renderSalesRecords();
                    }
                });

                paymentPageSizeSelect?.addEventListener('change', (event) => {
                    const value = Number(event.target.value);
                    if (Number.isFinite(value) && value > 0) {
                        paymentPageSize = value;
                        paymentCurrentPage = 1;
                        renderPaymentLedger();
                    }
                });

                paymentPrevPageButton?.addEventListener('click', () => {
                    if (paymentCurrentPage > 1) {
                        paymentCurrentPage -= 1;
                        renderPaymentLedger();
                    }
                });

                paymentNextPageButton?.addEventListener('click', () => {
                    const activeMethod = paymentMethods.get(activePaymentFilter);
                    const totalTransactions = activeMethod ? activeMethod.transactions.length : 0;
                    const totalPages = totalTransactions === 0 ? 1 : Math.ceil(totalTransactions / paymentPageSize);
                    if (paymentCurrentPage < totalPages) {
                        paymentCurrentPage += 1;
                        renderPaymentLedger();
                    }
                });

                paymentForm?.addEventListener('submit', async (event) => {
                    event.preventDefault();

                    const methodName = paymentInput.value.trim();

                    if (methodName === '') {
                        paymentInput.reportValidity();
                        return;
                    }

                    try {
                        paymentInput.setAttribute('disabled', 'disabled');
                        await apiRequest(routes.paymentMethods, {
                            method: 'POST',
                            body: { label: methodName },
                        });
                        await refreshDashboardState({ keepPaymentFilter: true });
                        paymentForm.reset();
                        clearPaymentInputValidity();
                    } catch (error) {
                        paymentInput.setCustomValidity(error.message ?? 'Unable to add payment method.');
                        paymentInput.reportValidity();
                    } finally {
                        paymentInput.removeAttribute('disabled');
                        if (paymentHelper) {
                            paymentHelper.textContent = defaultPaymentHelperText;
                        }
                    }
                });

                function openSalesModal(existingRecord = null) {
                    if (existingRecord && typeof existingRecord.preventDefault === 'function') {
                        existingRecord.preventDefault();
                        existingRecord = null;
                    }

                    if (!salesModal) {
                        return;
                    }

                    salesForm?.reset();
                    if (salesProductInput) {
                        salesProductInput.value = '';
                        salesProductInput.setCustomValidity('');
                    }

                    if (existingRecord) {
                        editingSalesId = existingRecord.id;
                    } else {
                        editingSalesId = null;
                    }

                    const baseDate = existingRecord ? existingRecord.purchaseDate : startOfDay(new Date());
                    if (salesDateInput) {
                        salesDateInput.value = formatDateForInput(baseDate);
                    }

                    renderPaymentMethodOptions(existingRecord?.paymentMethod ?? '');
                    renderSalesProductOptions(existingRecord?.productName ?? '');

                    if (existingRecord) {
                        if (salesProductInput) {
                            salesProductInput.value = existingRecord.productName;
                        }
                        if (salesPhoneInput) {
                            salesPhoneInput.value = existingRecord.phone;
                        }
                        if (salesEmailInput) {
                            salesEmailInput.value = existingRecord.email ?? '';
                        }
                        if (salesRemarksInput) {
                            salesRemarksInput.value = existingRecord.remarks ?? '';
                        }
                        if (salesAmountInput) {
                            salesAmountInput.value = existingRecord.salesAmount;
                        }
                        if (salesPaymentSelect) {
                            salesPaymentSelect.value = existingRecord.paymentMethod;
                        }
                        if (salesSubmitButton) {
                            salesSubmitButton.textContent = 'Update record';
                        }
                        if (salesModalTitle) {
                            salesModalTitle.textContent = 'Edit Sales Record';
                        }
                    } else {
                        if (salesSubmitButton) {
                            salesSubmitButton.textContent = 'Save record';
                        }
                        if (salesModalTitle) {
                            salesModalTitle.textContent = 'Add Sales Record';
                        }
                    }

                    salesModal.classList.remove('is-hidden');
                    salesModal.setAttribute('aria-hidden', 'false');
                }

                function closeSalesModal() {
                    if (!salesModal) {
                        return;
                    }

                    if (salesProductInput) {
                        salesProductInput.value = '';
                        salesProductInput.setCustomValidity('');
                    }
                    if (salesRemarksInput) {
                        salesRemarksInput.value = '';
                    }
                    renderSalesProductOptions();

                    salesModal.classList.add('is-hidden');
                    salesModal.setAttribute('aria-hidden', 'true');
                    editingSalesId = null;
                    salesForm?.reset();
                    if (salesSubmitButton) {
                        salesSubmitButton.textContent = 'Save record';
                    }
                    if (salesModalTitle) {
                        salesModalTitle.textContent = 'Add Sales Record';
                    }
                }

                openSalesModalButton?.addEventListener('click', openSalesModal);
                closeSalesModalButton?.addEventListener('click', closeSalesModal);
                cancelSalesModalButton?.addEventListener('click', closeSalesModal);

                salesModal?.addEventListener('click', (event) => {
                    if (event.target === salesModal) {
                        closeSalesModal();
                    }
                });

                function showInlineCopyFeedback(target, message) {
                    if (!target) return;
                    let indicator = target._copyIndicator;
                    if (!indicator) {
                        indicator = document.createElement('span');
                        indicator.className = 'copy-inline-feedback';
                        target.insertAdjacentElement('afterend', indicator);
                        target._copyIndicator = indicator;
                    }
                    indicator.textContent = message;
                    window.clearTimeout(target._copyIndicatorTimeout);
                    target._copyIndicatorTimeout = window.setTimeout(() => {
                        indicator.textContent = '';
                    }, 1500);
                }

                function showCopyToast(message) {
                    if (!notificationToast) {
                        return;
                    }

                    notificationToast.textContent = message;
                    notificationToast.classList.remove('is-hidden');
                    notificationToast.classList.add('is-visible');

                    window.clearTimeout(showCopyToastTimeoutId);
                    showCopyToastTimeoutId = window.setTimeout(() => {
                        notificationToast.classList.remove('is-visible');
                        notificationToast.classList.add('is-hidden');
                    }, 1500);
                }

                async function handleCopyClick(event) {
                    const target = event.target.closest('[data-copy]');
                    if (!target) {
                        return;
                    }

                    const feedbackTarget = target.closest('.cell-action-button, .cell-with-action, .data-copy-field, button, [role="button"]') || target;
                    const feedbackMessage = target.getAttribute('data-copy-feedback') || 'Copied';

                    const value = target.getAttribute('data-copy') ?? '';
                    if (value === '') {
                        return;
                    }

                    try {
                        await navigator.clipboard.writeText(value);
                        showCopyToast(feedbackMessage);
                        showInlineCopyFeedback(feedbackTarget, feedbackMessage);
                    } catch (error) {
                        console.warn('Clipboard copy failed, attempting fallback.', error);
                        const textarea = document.createElement('textarea');
                        textarea.value = value;
                        textarea.setAttribute('readonly', 'readonly');
                        textarea.style.position = 'absolute';
                        textarea.style.left = '-9999px';
                        document.body.appendChild(textarea);
                        textarea.select();
                        try {
                            document.execCommand('copy');
                            showCopyToast(feedbackMessage);
                            showInlineCopyFeedback(feedbackTarget, feedbackMessage);
                        } catch (fallbackError) {
                            console.warn('Fallback copy failed.', fallbackError);
                            window.alert('Unable to copy to clipboard.');
                        } finally {
                            document.body.removeChild(textarea);
                        }
                    }
                }

                document.addEventListener('click', handleCopyClick);

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && salesModal && !salesModal.classList.contains('is-hidden')) {
                        closeSalesModal();
                    }
                });

                function getRangeForFilter(filterKey, overrides = {}) {
                    if (filterKey === 'all') {
                        return { start: null, end: null };
                    }

                    const now = new Date();
                    const range = {
                        start: startOfDay(now),
                        end: endOfDay(now),
                    };

                    switch (filterKey) {
                        case 'yesterday': {
                            const yesterday = new Date(now);
                            yesterday.setDate(now.getDate() - 1);
                            range.start = startOfDay(yesterday);
                            range.end = endOfDay(yesterday);
                            break;
                        }
                        case 'last7': {
                            const start = new Date(now);
                            start.setDate(now.getDate() - 6);
                            range.start = startOfDay(start);
                            range.end = endOfDay(now);
                            break;
                        }
                        case 'month': {
                            const monthStart = new Date(now.getFullYear(), now.getMonth(), 1);
                            range.start = startOfDay(monthStart);
                            range.end = endOfDay(now);
                            break;
                        }
                        case 'custom': {
                            if (overrides.start instanceof Date && overrides.end instanceof Date) {
                                range.start = startOfDay(overrides.start);
                                range.end = endOfDay(overrides.end);
                            }
                            break;
                        }
                        default:
                            break;
                    }

                    return range;
                }

                function getRecordsForRange(filterKey, overrides = {}) {
                    const { start, end } = getRangeForFilter(filterKey, overrides);

                    const filtered = start === null || end === null
                        ? salesRecords.slice()
                        : salesRecords.filter((record) => record.purchaseDate >= start && record.purchaseDate <= end);

                    filtered.sort((a, b) => {
                        const aSerial = parseSerialNumber(a.serialNumber) ?? 0;
                        const bSerial = parseSerialNumber(b.serialNumber) ?? 0;
                        if (aSerial !== bSerial) {
                            return bSerial - aSerial;
                        }
                        const aCreated = a.__createdAt ?? a.purchaseDate?.getTime?.() ?? 0;
                        const bCreated = b.__createdAt ?? b.purchaseDate?.getTime?.() ?? 0;
                        return bCreated - aCreated;
                    });

                    return filtered;
                }

                function updateSalesFilterUI() {
                    salesFilterButtons.forEach((button) => {
                        button.classList.toggle('is-active', button.dataset.salesFilter === activeSalesFilter);
                    });

                    const showCustom = activeSalesFilter === 'custom';
                    salesFilterCustomWrapper?.classList.toggle('is-hidden', !showCustom);
                }

                function updateExportUI() {
                    exportRangeButtons.forEach((button) => {
                        button.classList.toggle('is-active', button.dataset.exportRange === activeExportRange);
                    });

                    const showCustom = activeExportRange === 'custom';
                    exportCustomWrapper?.classList.toggle('is-hidden', !showCustom);

                    if (showCustom) {
                        const defaultDate = startOfDay(new Date());
                        if (exportFromInput && !exportFromInput.value) {
                            exportFromInput.value = formatDateForInput(defaultDate);
                        }
                        if (exportToInput && !exportToInput.value) {
                            exportToInput.value = formatDateForInput(defaultDate);
                        }
                    }
                }

                function renderSalesRecords() {
                    if (!salesTableBody) {
                        return;
                    }

                    const records = getRecordsForRange(activeSalesFilter, customSalesFilter);

                    const totalRecords = records.length;
                    const totalPages = totalRecords === 0 ? 1 : Math.ceil(totalRecords / salesPageSize);

                    if (totalRecords === 0) {
                        salesCurrentPage = 1;
                    } else {
                        salesCurrentPage = Math.min(Math.max(salesCurrentPage, 1), totalPages);
                    }

                    if (salesPageSizeSelect && Number(salesPageSizeSelect.value) !== salesPageSize) {
                        salesPageSizeSelect.value = String(salesPageSize);
                    }

                    const startIndex = totalRecords === 0 ? 0 : (salesCurrentPage - 1) * salesPageSize;
                    const paginatedRecords = totalRecords === 0
                        ? []
                        : records.slice(startIndex, startIndex + salesPageSize);

                    salesTableBody.innerHTML = '';

                    paginatedRecords.forEach((record) => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${record.serialNumber ?? ''}</td>
                            <td>${formatDateForDisplay(record.purchaseDate)}</td>
                            <td>${formatProductLabel(record.productName, record.remarks)}</td>
                            <td>
                                <span class="cell-with-action">
                                    <span>${record.phone}</span>
                                    <button type="button" class="cell-action-button" data-copy="${record.phone}" aria-label="Copy phone">
                                        <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path d="M8 7V5a2 2 0 012-2h9a2 2 0 012 2v11a2 2 0 01-2 2h-2" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><rect x="4" y="7" width="12" height="12" rx="2" ry="2" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                </span>
                            </td>
                            <td>
                                <span class="cell-with-action">
                                    <span>${record.email}</span>
                                    <button type="button" class="cell-action-button" data-copy="${record.email}" aria-label="Copy email">
                                        <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true"><path d="M8 7V5a2 2 0 012-2h9a2 2 0 012 2v11a2 2 0 01-2 2h-2" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><rect x="4" y="7" width="12" height="12" rx="2" ry="2" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                </span>
                            </td>
                            <td>Rs ${formatAmount(record.salesAmount)}</td>
                            <td>${getPaymentMethodLabel(record.paymentMethod, record.paymentMethodLabel)}</td>
                            <td>
                                <div class="table-actions">
                                    <button type="button" class="icon-button" data-action="edit" data-id="${record.id}" aria-label="Edit sales record">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 15.5V20h4.5L19 9.5l-4.5-4.5L4 15.5z" fill="currentColor"/><path d="M14.5 5.5l4 4" stroke="currentColor" stroke-width="1.2"/></svg>
                                    </button>
                                    <button type="button" class="icon-button icon-button--danger" data-action="delete" data-id="${record.id}" aria-label="Delete sales record">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 7h12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M10 11v6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M14 11v6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M19 7l-.6 10.2A2 2 0 0116.41 19H7.59a2 2 0 01-1.99-1.8L5 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                    </button>
                                </div>
                            </td>
                        `;
                        salesTableBody.appendChild(row);
                    });

                    const salesActionButtons = salesTableBody.querySelectorAll('.icon-button[data-action]');
                    salesActionButtons.forEach((button) => {
                        button.addEventListener('click', handleSalesRowAction);
                    });

                    if (salesPaginationInfo) {
                        if (totalRecords === 0) {
                            salesPaginationInfo.textContent = 'No records to display';
                        } else {
                            const pages = Array.from({ length: totalPages }, (_, i) => i + 1).join(', ');
                            salesPaginationInfo.textContent = pages;
                        }
                    }

                    if (salesPrevPageButton) {
                        salesPrevPageButton.disabled = totalRecords === 0 || salesCurrentPage <= 1;
                    }

                    if (salesNextPageButton) {
                        salesNextPageButton.disabled = totalRecords === 0 || salesCurrentPage >= totalPages;
                    }

                    salesEmptyState?.classList.toggle('is-hidden', totalRecords > 0);

                    if (salesSummary) {
                        if (totalRecords > 0) {
                            salesSummary.textContent = `Showing ${totalRecords} record(s).`;
                            salesSummary.classList.remove('is-hidden');
                        } else {
                            salesSummary.textContent = '';
                            salesSummary.classList.add('is-hidden');
                        }
                    }

                    renderPaymentSummary();
                    renderPaymentLedger();
                }

                async function handleSalesRowAction(event) {
                    const button = event.currentTarget;
                    const action = button.dataset.action;
                    const recordId = button.dataset.id;

                    if (!action || !recordId) {
                        return;
                    }

                    const record = salesRecords.find((entry) => entry.id === recordId);
                    if (!record) {
                        return;
                    }

                    if (action === 'edit') {
                        openSalesModal(record);
                        return;
                    }

                    if (action === 'delete') {
                        const confirmation = window.confirm(`Delete sale for "${formatProductLabel(record.productName, record.remarks)}" on ${formatDateForDisplay(record.purchaseDate)}?`);
                        if (!confirmation) {
                            return;
                        }

                        try {
                            salesCurrentPage = 1;
                            paymentCurrentPage = 1;
                            await apiRequest(`${routes.sales}/${record.id}`, { method: 'DELETE' });
                            await refreshDashboardState({ keepPaymentFilter: true });
                        } catch (error) {
                            window.alert(error.message ?? 'Unable to delete sales record.');
                        }
                    }
                }

                exportRangeButtons.forEach((button) => {
                    button.addEventListener('click', () => {
                        const range = button.dataset.exportRange;
                        if (!range) {
                            return;
                        }

                        activeExportRange = range;
                        updateExportUI();

                        if (range !== 'custom') {
                            closeAllDropdowns();
                        }
                    });
                });

                salesFilterButtons.forEach((button) => {
                    button.addEventListener('click', () => {
                        const filterKey = button.dataset.salesFilter;
                        if (!filterKey) {
                            return;
                        }

                        if (filterKey === 'custom') {
                            if (!customSalesFilter.start || !customSalesFilter.end) {
                                const defaultDate = startOfDay(new Date());
                                customSalesFilter = { start: defaultDate, end: defaultDate };
                                if (salesFilterFromInput) {
                                    salesFilterFromInput.value = formatDateForInput(defaultDate);
                                }
                                if (salesFilterToInput) {
                                    salesFilterToInput.value = formatDateForInput(defaultDate);
                                }
                            }
                            activeSalesFilter = 'custom';
                        } else {
                            activeSalesFilter = filterKey;
                        }

                        salesCurrentPage = 1;
                        updateSalesFilterUI();
                        renderSalesRecords();

                        if (filterKey !== 'custom') {
                            closeAllDropdowns();
                        }
                    });
                });

                applySalesFilterButton?.addEventListener('click', () => {
                    const from = parseDateInput(salesFilterFromInput?.value ?? '');
                    const to = parseDateInput(salesFilterToInput?.value ?? '');

                    if (!from || !to) {
                        window.alert('Please select both start and end dates for the custom filter.');
                        return;
                    }

                    if (from > to) {
                        window.alert('The start date cannot be later than the end date.');
                        return;
                    }

                salesCurrentPage = 1;
                customSalesFilter = { start: from, end: to };
                activeSalesFilter = 'custom';
                updateSalesFilterUI();
                renderSalesRecords();
                closeAllDropdowns();
            });

                salesForm?.addEventListener('submit', async (event) => {
                    event.preventDefault();

                    const purchaseDateValue = salesDateInput?.value ?? '';
                    const productName = (salesProductInput?.value ?? '').trim();
                    const phone = (salesPhoneInput?.value ?? '').trim();
                    const email = (salesEmailInput?.value ?? '').trim();
                    const remarks = (salesRemarksInput?.value ?? '').trim();
                    const amountValue = parseFloat(salesAmountInput?.value ?? '0');
                    const paymentMethodValue = salesPaymentSelect?.value ?? '';

                    const purchaseDate = parseDateInput(purchaseDateValue);
                    if (!purchaseDate || productName === '' || phone === '' || remarks === '' || Number.isNaN(amountValue) || amountValue < 0 || paymentMethodValue === '') {
                        return;
                    }

                    if (!isKnownProduct(productName)) {
                        if (salesProductInput) {
                            salesProductInput.setCustomValidity('Select a product from the suggestions list.');
                            salesProductInput.reportValidity();
                        }
                        return;
                    }

                    const payload = {
                        product_name: productName,
                        phone,
                        sales_amount: amountValue,
                        payment_method: paymentMethodValue,
                        purchase_date: formatDateForInput(startOfDay(purchaseDate)),
                        remarks,
                    };

                    if (email !== '') {
                        payload.email = email;
                    }

                    const endpoint = editingSalesId
                        ? `${routes.sales}/${editingSalesId}`
                        : routes.sales;
                    const method = editingSalesId ? 'PUT' : 'POST';

                    try {
                        salesSubmitButton?.setAttribute('disabled', 'disabled');
                        salesCurrentPage = 1;
                        paymentCurrentPage = 1;
                        await apiRequest(endpoint, { method, body: payload });
                        await refreshDashboardState({ keepPaymentFilter: true });
                        closeSalesModal();
                    } catch (error) {
                        window.alert(error.message ?? 'Unable to save sales record.');
                    } finally {
                        salesSubmitButton?.removeAttribute('disabled');
                    }
                });

                exportButton?.addEventListener('click', () => {
                    const rangeKey = activeExportRange;
                    let customRange = {};

                    if (rangeKey === 'custom') {
                        const from = parseDateInput(exportFromInput?.value ?? '');
                        const to = parseDateInput(exportToInput?.value ?? '');

                        if (!from || !to) {
                            window.alert('Please choose both start and end dates to export a custom range.');
                            return;
                        }

                        if (from > to) {
                            window.alert('The export start date cannot be later than the end date.');
                            return;
                        }

                        customRange = { start: from, end: to };
                    }

                    const recordsToExport = getRecordsForRange(rangeKey, customRange);

                    if (recordsToExport.length === 0) {
                        window.alert('No sales records available for the selected export range.');
                        return;
                    }

                    const header = [
                        'Order ID',
                        'Purchase Date',
                        'Product Name',
                        'Phone',
                        'Email',
                        'Sales Amount',
                        'Payment Method',
                    ];

                    const rows = recordsToExport.map((record) => [
                        record.serialNumber ?? '',
                        formatDateForDisplay(record.purchaseDate),
                        formatProductLabel(record.productName, record.remarks),
                        record.phone,
                        record.email,
                        formatAmount(record.salesAmount),
                        getPaymentMethodLabel(record.paymentMethod, record.paymentMethodLabel),
                    ]);

                    const csvContent = [header, ...rows]
                        .map((row) => row
                            .map((value) => '"' + String(value).replace(/"/g, '""') + '"')
                            .join(','))
                        .join('\n');

                    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = `sales-records-${rangeKey}-${formatDateForDisplay(new Date())}.csv`;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    URL.revokeObjectURL(url);
                    closeAllDropdowns();
                });

                await refreshDashboardState();

                enterCreateMode();
                setProductFormVisibility(false);

                const defaultCustomDate = startOfDay(new Date());
                customSalesFilter = { start: defaultCustomDate, end: defaultCustomDate };
                if (salesFilterFromInput) {
                    salesFilterFromInput.value = formatDateForInput(defaultCustomDate);
                }
                if (salesFilterToInput) {
                    salesFilterToInput.value = formatDateForInput(defaultCustomDate);
                }

                if (exportFromInput) {
                    exportFromInput.value = formatDateForInput(defaultCustomDate);
                }
                if (exportToInput) {
                    exportToInput.value = formatDateForInput(defaultCustomDate);
                }

                updateSalesFilterUI();
                renderSalesRecords();
                updateExportUI();
                switchPanel('products');
            };

            const runDashboard = () => {
                initializeDashboard().catch((error) => {
                    console.error('Failed to initialize dashboard.', error);
                });
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', runDashboard);
            } else {
                runDashboard();
            }
        </script>
