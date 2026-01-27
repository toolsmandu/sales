@include('partials.dashboard-scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const stockRoot = document.getElementById('stock-root');
        if (!stockRoot) {
            return;
        }

        const variationNotesMap = @json($variationNotesMap ?? []);
        const stockInstructionMap = @json($stockInstructionMap ?? []);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        const searchInput = document.getElementById('stock-search');
        const searchCombobox = document.querySelector('[data-stock-search]');
        const tabButtons = stockRoot.querySelectorAll('[data-stock-tab]');
        const panels = stockRoot.querySelectorAll('[data-stock-panel]');
        const freshList = document.getElementById('fresh-key-list');
        const viewedList = document.getElementById('viewed-key-list');
        const freshEmpty = document.getElementById('fresh-empty');
        const viewedEmpty = document.getElementById('viewed-empty');

        const modal = document.getElementById('stock-pin-modal');
        const modalForm = document.getElementById('stock-pin-form');
        const remarksInput = document.getElementById('stock-pin-remarks');
        const modalError = document.getElementById('stock-pin-error');
        const modalCancel = modal?.querySelector('[data-pin-cancel]');
        const modalSubmit = modalForm?.querySelector('button[type="submit"]');

        const countFresh = stockRoot.querySelector('[data-stock-count="fresh"]');
        const countViewed = stockRoot.querySelector('[data-stock-count="viewed"]');

        const isAdmin = stockRoot.dataset.isAdmin === '1';

        let pendingReveal = null;

        const maskKey = (value = '') => {
            const visible = value.slice(0, 5);
            const hidden = Math.max(value.length - 5, 0);
            return visible + '*'.repeat(hidden);
        };

        const formatDateTime = (value) => {
            if (!value) {
                return '';
            }

            const date = new Date(value);
            if (Number.isNaN(date.valueOf())) {
                return '';
            }

            return date.toLocaleString('en-US', {
                timeZone: 'Asia/Kathmandu',
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
                hour12: true,
            });
        };

        const updateCounts = () => {
            if (countFresh) {
                countFresh.textContent = freshList ? freshList.querySelectorAll('[data-stock-item]').length : 0;
            }

            if (countViewed) {
                countViewed.textContent = viewedList ? viewedList.querySelectorAll('[data-stock-item]').length : 0;
            }
        };

        const updateEmptyStates = () => {
            const hasFresh = !!(freshList && freshList.querySelector('[data-stock-item]'));
            const hasViewed = !!(viewedList && viewedList.querySelector('[data-stock-item]'));

            if (freshEmpty) {
                freshEmpty.hidden = hasFresh;
            }

            if (viewedEmpty) {
                viewedEmpty.hidden = hasViewed;
            }
        };

        const filterItems = () => {
            const query = (searchInput?.value ?? '').trim().toLowerCase();

            panels.forEach((panel) => {
                const list = panel.querySelector('[data-stock-list]');
                const empty = panel.querySelector('[data-stock-empty]');

                if (!list) {
                    return;
                }

                let visibleCount = 0;

                list.querySelectorAll('[data-stock-item]').forEach((item) => {
                    const haystack = `${item.dataset.product ?? ''} ${item.dataset.variation ?? ''} ${item.dataset.key ?? ''} ${item.dataset.activation ?? ''} ${item.dataset.viewer ?? ''} ${item.dataset.remarks ?? ''}`.toLowerCase();
                    const matches = query.length === 0 || haystack.includes(query);
                    item.hidden = !matches;
                    if (matches) {
                        visibleCount += 1;
                    }
                });

                if (empty) {
                    empty.hidden = visibleCount > 0;
                }
            });
        };

        const activateTab = (target) => {
            tabButtons.forEach((button) => {
                const isActive = button.dataset.stockTab === target;
                button.classList.toggle('is-active', isActive);
                button.setAttribute('aria-selected', isActive ? 'true' : 'false');
                button.tabIndex = isActive ? 0 : -1;
            });

            panels.forEach((panel) => {
                panel.classList.toggle('is-active', panel.dataset.stockPanel === target);
            });
        };

        const openRevealModal = (context) => {
            if (!modal || !modalForm) {
                return;
            }

            pendingReveal = context;
            modalForm.reset();
            if (remarksInput) {
                remarksInput.value = '';
            }
            if (modalError) {
                modalError.textContent = '';
                modalError.hidden = true;
            }

            modal.hidden = false;

            window.setTimeout(() => {
                remarksInput?.focus();
            }, 50);
        };

        const closeRevealModal = () => {
            if (!modal || !modalForm) {
                return;
            }

            modal.hidden = true;
            modalForm.reset();
            pendingReveal = null;

            if (modalError) {
                modalError.textContent = '';
                modalError.hidden = true;
            }
        };

        tabButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const target = button.dataset.stockTab;
                if (!target) {
                    return;
                }

                activateTab(target);
                filterItems();
            });
        });

        searchInput?.addEventListener('input', filterItems);
        searchCombobox?.addEventListener('product-combobox:select', filterItems);

        const handleCopy = async (button, text) => {
            const feedback = button.nextElementSibling && button.nextElementSibling.classList.contains('copy-inline-feedback')
                ? button.nextElementSibling
                : null;
            const setFeedback = (message) => {
                if (!feedback) return;
                feedback.textContent = message;
                window.clearTimeout(button._copyTimeout);
                button._copyTimeout = window.setTimeout(() => {
                    feedback.textContent = '';
                }, 1500);
            };

            try {
                await navigator.clipboard.writeText(text);
                setFeedback('Copied');
            } catch (error) {
                console.warn('Unable to copy text', error);
                setFeedback('Copy failed');
            }
        };

        stockRoot.addEventListener('click', (event) => {
            const copyButton = event.target.closest('[data-stock-copy]');
            if (copyButton) {
                const item = copyButton.closest('[data-stock-item]');
                const activation = item?.dataset.activation ?? '';
                if (!activation) {
                    return;
                }
                handleCopy(copyButton, activation);
                return;
            }

            const instructionButton = event.target.closest('[data-stock-copy-instruction]');
            if (instructionButton) {
                const item = instructionButton.closest('[data-stock-item]');
                const code = instructionButton.dataset.copyCode || item?.dataset.activation || '';
                if (!code) {
                    return;
                }
                const template = instructionButton.dataset.copyTemplate || '';
                const mapInstruction = item?.dataset.stockId
                    ? (stockInstructionMap[item.dataset.stockId] ?? '')
                    : '';
                const fallbackInstruction = item?.dataset.variationId
                    ? (variationNotesMap[item.dataset.variationId] ?? '')
                    : '';
                const activeInstruction = template.trim() !== ''
                    ? template
                    : (mapInstruction.trim() !== '' ? mapInstruction : fallbackInstruction);
                const text = activeInstruction.trim() === ''
                    ? code
                    : activeInstruction.replaceAll('{key}', code);
                handleCopy(instructionButton, text);
                return;
            }

            const editButton = event.target.closest('[data-stock-edit]');
            if (editButton) {
                if (isAdmin && !editButton.disabled) {
                    handleEdit(editButton);
                }
                return;
            }

            const deleteButton = event.target.closest('[data-stock-delete]');
            if (deleteButton) {
                if (isAdmin && !deleteButton.disabled) {
                    handleDelete(deleteButton);
                }
                return;
            }

            const revealButton = event.target.closest('[data-stock-reveal]');
            if (!revealButton || revealButton.disabled) {
                return;
            }

            const item = revealButton.closest('[data-stock-item]');
            const revealUrl = revealButton.getAttribute('data-reveal-url');

            if (!item || !revealUrl) {
                return;
            }

            openRevealModal({ button: revealButton, item, revealUrl });
        });

        modalCancel?.addEventListener('click', closeRevealModal);

        modal?.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeRevealModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && modal && !modal.hidden) {
                closeRevealModal();
            }
        });

        const handleEdit = async (button) => {
            const item = button.closest('[data-stock-item]');
            if (!item) {
                return;
            }

            const editUrl = item.dataset.editUrl;
            if (!editUrl) {
                return;
            }

            const currentKey = item.dataset.activation ?? '';
            const nextKey = window.prompt('Enter the updated activation key:', currentKey);

            if (nextKey === null) {
                return;
            }

            const trimmedKey = nextKey.trim();

            if (trimmedKey === '') {
                alert('Activation key cannot be empty.');
                return;
            }

            if (trimmedKey === currentKey) {
                return;
            }

            button.disabled = true;
            button.setAttribute('aria-busy', 'true');

            try {
                const response = await fetch(editUrl, {
                    method: 'PUT',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ activation_key: trimmedKey }),
                });

                if (response.status === 422) {
                    const data = await response.json();
                    const message = data?.errors?.activation_key?.[0] ?? 'Unable to validate the new activation key.';
                    alert(message);
                    return;
                }

                if (!response.ok) {
                    throw new Error('Response not OK');
                }

                const data = await response.json();
                const activationKey = data.activation_key ?? trimmedKey;
                const masked = data.masked_key ?? maskKey(activationKey);

                item.dataset.activation = activationKey;

                if (item.dataset.panel === 'fresh' && !item.classList.contains('stock-item--revealed')) {
                    item.dataset.key = masked;
                } else {
                    item.dataset.key = activationKey;
                }

                const valueElement = item.querySelector('.stock-item__value');
                if (valueElement) {
                    if (item.dataset.panel === 'fresh' && !item.classList.contains('stock-item--revealed')) {
                        valueElement.textContent = masked;
                    } else {
                        valueElement.textContent = activationKey;
                    }
                }

                if (data.product?.name) {
                    item.dataset.product = data.product.name;
                    const productElement = item.querySelector('.stock-item__product');
                    if (productElement) {
                        productElement.textContent = data.product.name;
                    }
                }

                alert(data.message ?? 'Stock key updated successfully.');
                updateEmptyStates();
                filterItems();
            } catch (error) {
                console.error(error);
                alert('Unable to update this activation key right now. Please try again.');
            } finally {
                button.removeAttribute('aria-busy');
                button.disabled = false;
            }
        };

        const handleDelete = async (button) => {
            const item = button.closest('[data-stock-item]');
            if (!item) {
                return;
            }

            const deleteUrl = item.dataset.deleteUrl;
            if (!deleteUrl) {
                return;
            }

            const confirmation = window.confirm('Delete this activation key? This action cannot be undone.');
            if (!confirmation) {
                return;
            }

            button.disabled = true;
            button.setAttribute('aria-busy', 'true');

            try {
                const response = await fetch(deleteUrl, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error('Response not OK');
                }

                const data = await response.json();

                item.remove();
                updateCounts();
                updateEmptyStates();
                filterItems();

                if (data?.message) {
                    alert(data.message);
                }
            } catch (error) {
                console.error(error);
                alert('Unable to delete this activation key right now. Please try again.');
            } finally {
                button.removeAttribute('aria-busy');
                button.disabled = false;
            }
        };

        modalForm?.addEventListener('submit', async (event) => {
            event.preventDefault();

            if (!pendingReveal) {
                return;
            }

            const { button, item, revealUrl } = pendingReveal;
            const remarksValue = remarksInput?.value.trim() ?? '';

            if (remarksValue === '') {
                if (modalError) {
                    modalError.textContent = 'Please add a remark before revealing the key.';
                    modalError.hidden = false;
                }
                remarksInput?.focus();
                return;
            }

            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
            modalSubmit?.setAttribute('disabled', 'true');

            try {
                const response = await fetch(revealUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ remarks: remarksValue }),
                });

                if (response.status === 422) {
                    const data = await response.json();
                    const message = data?.errors?.remarks?.[0] ?? 'Unable to validate your request.';
                    if (modalError) {
                        modalError.textContent = message;
                        modalError.hidden = false;
                    }
                    button.disabled = false;
                    button.removeAttribute('aria-busy');
                    modalSubmit?.removeAttribute('disabled');
                    return;
                }

                if (!response.ok) {
                    throw new Error('Response not OK');
                }

                const payload = await response.json();

                closeRevealModal();

                const revealedKey = payload.activation_key ?? '';
                const remarksResponse = payload.remarks ?? remarksValue;
                const viewCount = payload.view_count ?? null;
                const viewLimit = payload.view_limit ?? null;
                const viewLogs = Array.isArray(payload.view_logs) ? payload.view_logs : [];
                const reachedLimit = viewLimit !== null && viewCount !== null
                    ? Number(viewCount) >= Number(viewLimit)
                    : false;

                item.dataset.key = revealedKey;
                item.dataset.activation = revealedKey;
                item.dataset.viewer = payload.viewer?.name ?? '';
                item.dataset.remarks = remarksResponse;
                if (viewCount !== null) {
                    item.dataset.viewCount = viewCount;
                }
                if (viewLimit !== null) {
                    item.dataset.viewLimit = viewLimit;
                }

                const valueElement = item.querySelector('.stock-item__value');
                if (valueElement) {
                    valueElement.textContent = revealedKey;
                }

                const timestampElement = item.querySelector('.stock-item__timestamp');
                if (timestampElement && payload.viewed_at) {
                    const viewerLabel = payload.viewer?.name ?? '—';
                    const remarksDisplay = remarksResponse || '—';
                    timestampElement.textContent = `Viewed on: ${formatDateTime(payload.viewed_at)} | Viewed by: ${viewerLabel} | Remarks: ${remarksDisplay}`;
                }

                item.classList.add('stock-item--revealed');

                const timestampBlocks = item.querySelectorAll('.stock-item__timestamp');
                if (timestampBlocks.length > 0 && viewLimit !== null && viewCount !== null) {
                    const remainingViews = Math.max(Number(viewLimit) - Number(viewCount), 0);
                    const limitElement = timestampBlocks[timestampBlocks.length - 1];
                    limitElement.textContent = `Stock Limit: ${remainingViews} / ${viewLimit}`;
                }

                if (reachedLimit) {
                    // Keep item in fresh list until the page is refreshed, but disable further reveals.
                    const revealAction = item.querySelector('[data-stock-reveal]');
                    revealAction?.remove();

                    // Render view history if available.
                    if (viewLogs.length > 0) {
                        let logContainer = item.querySelector('.stock-item__views');
                        if (!logContainer) {
                            logContainer = document.createElement('div');
                            logContainer.className = 'stock-item__views';
                            const detailBlock = item.querySelector('.stock-item__details');
                            detailBlock?.appendChild(logContainer);
                        }
                        logContainer.innerHTML = '<strong>View history:</strong>';
                        const list = document.createElement('ul');
                        list.className = 'stock-view-log';
                        viewLogs.forEach((log) => {
                            const li = document.createElement('li');
                            const at = log.viewed_at ? formatDateTime(log.viewed_at) : '—';
                            li.textContent = `${at} | ${log.viewer || '—'} | Remarks: ${log.remarks || '—'}`;
                            list.appendChild(li);
                        });
                        logContainer.appendChild(list);
                    }
                } else {
                    // Keep in fresh list for next view.
                    button.disabled = false;
                    button.removeAttribute('aria-busy');
                }

                updateCounts();
                updateEmptyStates();
                filterItems();
            } catch (error) {
                console.error(error);
                if (modalError) {
                    modalError.textContent = 'Could not reveal this key right now. Please try again.';
                    modalError.hidden = false;
                }
                button.disabled = false;
                button.removeAttribute('aria-busy');
            } finally {
                modalSubmit?.removeAttribute('disabled');
            }
        });

        activateTab('fresh');
        updateCounts();
        updateEmptyStates();
        filterItems();
    });
</script>
