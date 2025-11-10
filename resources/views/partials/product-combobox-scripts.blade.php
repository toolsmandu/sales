<script>
    document.addEventListener('DOMContentLoaded', () => {
        const comboboxes = document.querySelectorAll('[data-product-combobox]');

        comboboxes.forEach((combobox) => {
            const input = combobox.querySelector('.product-combobox__input');
            const dropdown = combobox.querySelector('.product-combobox__dropdown');
            if (!input || !dropdown) {
                return;
            }

            const options = Array.from(dropdown.querySelectorAll('[data-product-option]'));
            const hiddenField = combobox.querySelector('[data-product-selected]');
            const emptyMessage = dropdown.querySelector('[data-empty-message]');
            const allowFreeEntry = combobox.dataset.allowFreeEntry === 'true';
            const autoSubmit = combobox.dataset.autosubmit === 'true';
            const form = combobox.closest('form');
            const selectedName = (input.dataset.selectedName ?? '').toLowerCase();

            let userModifiedInput = false;
            let suppressFocusOpen = false;

            const setHiddenFieldValue = (value) => {
                if (!hiddenField) {
                    return;
                }

                const nextValue = value ?? '';
                if (hiddenField.value === nextValue) {
                    return;
                }

                hiddenField.value = nextValue;
                hiddenField.dispatchEvent(new Event('input', { bubbles: true }));
                hiddenField.dispatchEvent(new Event('change', { bubbles: true }));
            };

            const initialValue = input.value.trim().toLowerCase();
            if (initialValue !== '') {
                if (!allowFreeEntry && selectedName !== '') {
                    userModifiedInput = initialValue !== selectedName;
                } else if (!allowFreeEntry) {
                    userModifiedInput = true;
                }
            }

            const openDropdown = () => {
                if (options.length === 0) {
                    return;
                }
                combobox.classList.add('is-open');
            };

            const closeDropdown = () => {
                combobox.classList.remove('is-open');
            };

            const updateActiveOption = (activeOption) => {
                options.forEach((option) => {
                    const isActive = option === activeOption;
                    option.classList.toggle('is-active', isActive);
                    option.setAttribute('aria-selected', isActive ? 'true' : 'false');
                });
            };

            const getVisibleOptions = () => options.filter((option) => !option.hidden);

            const focusOption = (option) => {
                option?.focus({ preventScroll: true });
            };

            const filterOptions = () => {
                const rawQuery = input.value.trim().toLowerCase();
                const shouldFilter = userModifiedInput || allowFreeEntry;
                const query = shouldFilter ? rawQuery : '';
                let visibleCount = 0;

                options.forEach((option) => {
                    const name = option.dataset.productName?.toLowerCase() ?? '';
                    const matches = name.includes(query);
                    option.hidden = !matches;
                    if (matches) {
                        visibleCount += 1;
                    }
                });

                if (emptyMessage) {
                    emptyMessage.hidden = visibleCount > 0;
                }

                if (!allowFreeEntry && userModifiedInput && hiddenField && hiddenField.value) {
                    const activeOption = options.find((option) => option.dataset.productId === hiddenField.value);
                    const activeName = activeOption?.dataset.productName?.toLowerCase() ?? '';
                    if (activeName !== rawQuery) {
                        setHiddenFieldValue('');
                        updateActiveOption(null);
                        input.dataset.selectedName = '';
                    }
                }
            };

            const selectOption = (option) => {
                if (!option) {
                    return;
                }

                const productId = option.dataset.productId ?? '';
                const productName = option.dataset.productName?.trim() ?? '';

                setHiddenFieldValue(productId);

                suppressFocusOpen = true;
                input.value = productName;
                input.dataset.selectedName = productName;
                userModifiedInput = false;
                input.setCustomValidity('');

                updateActiveOption(option);
                filterOptions();
                closeDropdown();

                combobox.dispatchEvent(new CustomEvent('product-combobox:select', {
                    bubbles: true,
                    detail: {
                        productId,
                        productName,
                        option,
                    },
                }));

                if (autoSubmit && form) {
                    if (typeof form.requestSubmit === 'function') {
                        form.requestSubmit();
                    } else {
                        form.submit();
                    }
                    return;
                }

                input.focus({ preventScroll: true });
            };

            input.addEventListener('focus', () => {
                if (suppressFocusOpen) {
                    suppressFocusOpen = false;
                    return;
                }
                openDropdown();
                filterOptions();
            });

            input.addEventListener('click', () => {
                openDropdown();
                filterOptions();
            });

            input.addEventListener('input', () => {
                userModifiedInput = true;
                suppressFocusOpen = false;
                input.setCustomValidity('');
                if (!allowFreeEntry) {
                    setHiddenFieldValue('');
                    input.dataset.selectedName = '';
                }
                openDropdown();
                filterOptions();
            });

            input.addEventListener('keydown', (event) => {
                if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    openDropdown();
                    const firstVisible = getVisibleOptions()[0];
                    focusOption(firstVisible);
                } else if (event.key === 'Escape') {
                    closeDropdown();
                }
            });

            options.forEach((option) => {
                option.addEventListener('click', () => selectOption(option));
                option.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        selectOption(option);
                    } else if (event.key === 'Escape') {
                        event.preventDefault();
                        closeDropdown();
                        input.focus({ preventScroll: true });
                    } else if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                        event.preventDefault();
                        const visible = getVisibleOptions();
                        const index = visible.indexOf(option);
                        if (index === -1) {
                            return;
                        }
                        const offset = event.key === 'ArrowDown' ? 1 : -1;
                        const nextIndex = (index + offset + visible.length) % visible.length;
                        focusOption(visible[nextIndex]);
                    }
                });
            });

            document.addEventListener('click', (event) => {
                if (!combobox.contains(event.target)) {
                    closeDropdown();
                }
            });

            form?.addEventListener('submit', (event) => {
                if (!allowFreeEntry && hiddenField && options.length > 0 && hiddenField.value === '') {
                    event.preventDefault();
                    openDropdown();
                    input.focus({ preventScroll: true });
                    input.setCustomValidity('Please select a product from the list.');
                    input.reportValidity();
                } else {
                    input.setCustomValidity('');
                }
            });

            filterOptions();
        });
    });
</script>
