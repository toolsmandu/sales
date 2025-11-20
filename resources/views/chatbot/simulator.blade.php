@extends('layouts.app')

@push('styles')
    @include('partials.dashboard-styles')
    @include('chatbot.partials.styles')
    @include('partials.product-combobox-styles')
@endpush

@section('content')
    <div class="dashboard-grid">
        @include('partials.dashboard-sidebar')

        <section class="dashboard-content">
            <div class="chatbot-grid">
                <section class="card chatbot-simulator">
                    <header>
                        <h2>Chatbot</h2>
                    </header>

                    @php
                        $selectedProduct = $products->firstWhere('id', $selectedProductId);
                        $topResult = $chatbotResults->first();
                        $additionalResults = $chatbotResults->skip(1)->values();
                        $initialConversation = [];

                        if ($selectedProductId > 0 && $searchTerm !== '') {
                            $timestamp = now()->timezone('Asia/Kathmandu')->toIso8601String();
                            $initialConversation[] = [
                                'role' => 'user',
                                'content' => $searchTerm,
                                'timestamp' => $timestamp,
                                'product_tag' => $selectedProduct->name ?? 'All products',
                            ];

                            $isAllQuery = strcasecmp($searchTerm, 'all') === 0;

                            if ($chatbotResults->isEmpty()) {
                                $initialConversation[] = [
                                    'role' => 'assistant',
                                    'content' => 'I couldn’t find a matching answer for that query. Try a broader keyword or enrich the knowledge base for this product.',
                                    'timestamp' => $timestamp,
                                    'related_answers' => [],
                                ];
                            } elseif ($isAllQuery) {
                                $initialConversation[] = [
                                    'role' => 'assistant',
                                    'content' => $selectedProduct
                                        ? "Here are all saved questions for {$selectedProduct->name}."
                                        : 'Here are all saved questions for this product.',
                                    'timestamp' => $timestamp,
                                    'type' => 'question-list',
                                    'options' => $chatbotResults->map(fn ($entry) => [
                                        'question' => $entry->question,
                                        'answer' => $entry->answer,
                                    ])->values(),
                                ];
                            } else {
                                $initialConversation[] = [
                                    'role' => 'assistant',
                                    'content' => $topResult->answer,
                                    'timestamp' => $timestamp,
                                    'related_answers' => $additionalResults->pluck('answer')->values()->all(),
                                ];
                            }
                        }
                    @endphp

                    <div class="chatbot-setup">
                        <div class="product-combobox" data-product-combobox data-chat-product-combobox>
                            <label for="chatbot-product-input">
                                Product
                                <input
                                    type="text"
                                    id="chatbot-product-input"
                                    class="product-combobox__input"
                                    placeholder="Search product..."
                                    autocomplete="off"
                                    value="{{ $selectedProduct->name ?? '' }}"
                                    data-selected-name="{{ $selectedProduct->name ?? '' }}"
                                    {{ $products->isEmpty() ? 'disabled' : '' }}
                                >
                            </label>

                            <input
                                type="hidden"
                                name="product"
                                value="{{ $selectedProductId ?: '' }}"
                                data-product-selected
                                data-chat-product-input
                                form="chatbot-simulator-form"
                            >

                            <div class="product-combobox__dropdown" role="listbox" aria-label="Product options">
                                @if ($products->isEmpty())
                                    <p class="product-combobox__empty">No products available yet.</p>
                                @else
                                    <p class="product-combobox__empty" data-empty-message hidden>No matching products found.</p>
                                    @foreach ($products as $product)
                                        @php
                                            $isSelectedOption = $selectedProductId === $product->id;
                                        @endphp
                                        <button
                                            type="button"
                                            class="product-combobox__option {{ $isSelectedOption ? 'is-active' : '' }}"
                                            data-product-option
                                            data-product-name="{{ $product->name }}"
                                            data-product-id="{{ $product->id }}"
                                            role="option"
                                            aria-selected="{{ $isSelectedOption ? 'true' : 'false' }}"
                                        >
                                            {{ $product->name }}
                                        </button>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <p class="chatbot-setup__hint" data-chat-setup-hint>
                            @if ($products->isEmpty())
                                Add a product to start simulating conversations.
                            @elseif ($selectedProduct)
                                You’re chatting about {{ $selectedProduct->name }}.
                            @else
                                Search and pick a product to begin, then start chatting below.
                            @endif
                        </p>
                    </div>

                    <div class="chatbot-interface" data-chat-interface {{ $selectedProductId > 0 ? '' : 'hidden' }}>
                        <div class="chatbot-window" role="log" aria-live="polite" data-chat-window>
                            <div class="chatbot-placeholder" data-chat-placeholder>
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M12 3c4.97 0 9 3.582 9 8 0 3.636-2.88 6.676-6.84 7.692-.207.053-.395.185-.5.362l-1.09 1.856a.5.5 0 0 1-.86-.022l-.915-1.68a.7.7 0 0 0-.462-.337C7.02 18.539 3 15.538 3 11c0-4.418 4.03-8 9-8Z" stroke="currentColor" stroke-width="1.4" />
                                    <path d="M8 11h8M8 7h5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" />
                                </svg>
                                <div>Select a product and start your conversation.</div>
                            </div>
                        </div>

                        <form method="GET" action="{{ route('chatbot.start') }}" class="chatbot-input" id="chatbot-simulator-form">
                            <div class="chatbot-input__toolbar">
                                <div class="chatbot-input__field">
                                    <label for="chatbot-term">
                                        Ask the assistant <i>(Type "all" to list all topics.)</i>
                                        <textarea
                                            id="chatbot-term"
                                            name="term"
                                            rows="3"
                                            placeholder="Type question here..."
                                            autocomplete="on"
                                        ></textarea>
                                    </label>
                                </div>
                            </div>

                            <div class="chatbot-input__actions">
                                <button type="submit" id="chatbot-send-button" disabled>Send</button>
                            </div>
                        </form>
                    </div>
                </section>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    @include('partials.dashboard-scripts')
    @include('partials.product-combobox-scripts')
    <script>
        (function () {
            const endpoint = @json(route('chatbot.start'));
            const chatInterface = document.querySelector('[data-chat-interface]');
            const chatWindow = chatInterface?.querySelector('[data-chat-window]');
            if (!chatWindow) {
                return;
            }

            const placeholder = chatWindow.querySelector('[data-chat-placeholder]');
            const productLabel = chatWindow.querySelector('[data-chat-product-label]');
            const form = document.getElementById('chatbot-simulator-form');
            const productCombobox = document.querySelector('[data-chat-product-combobox]');
            const productHiddenInput = document.querySelector('[data-chat-product-input]');
            const productDisplayInput = productCombobox?.querySelector('.product-combobox__input');
            const messageInput = document.getElementById('chatbot-term');
            const sendButton = document.getElementById('chatbot-send-button');
            const hint = document.querySelector('[data-chat-setup-hint]');

            const state = {
                productId: {{ $selectedProductId > 0 ? $selectedProductId : 'null' }},
                productName: @json($selectedProduct->name ?? ''),
                messages: [],
                busy: false,
            };

            let questionListMessage = null;
            let followUpMessage = null;
            let pendingQuestionListData = null;

            const initialMessages = @json($initialConversation);
            const productsAvailable = {{ $products->isEmpty() ? 'false' : 'true' }};

            const formatTimestamp = (isoString) => {
                try {
                    return new Date(isoString).toLocaleString('en-US', {
                        hour: 'numeric',
                        minute: '2-digit',
                        month: 'short',
                        day: 'numeric',
                    });
                } catch (error) {
                    return new Date().toLocaleString('en-US', {
                        hour: 'numeric',
                        minute: '2-digit',
                    });
                }
            };

            const cloneQuestionListData = (message) => {
                const clone = {
                    ...message,
                };

                if (Array.isArray(message.options)) {
                    clone.options = message.options.map((option) => ({ ...option }));
                }

                if (Array.isArray(message.related_answers)) {
                    clone.related_answers = [...message.related_answers];
                }

                return clone;
            };

            const updateInterfaceVisibility = () => {
                if (!chatInterface) {
                    return;
                }

                const hasProduct = !!state.productId;
                const shouldShow = hasProduct && productsAvailable;
                chatInterface.hidden = !shouldShow;

                if (hint) {
                    if (!productsAvailable) {
                        hint.textContent = 'Add a product to start simulating conversations.';
                    } else if (!hasProduct) {
                        hint.textContent = 'Search and pick a product to begin, then start chatting below.';
                    } else {
                        hint.textContent = `You’re chatting about ${state.productName || 'this product'}.`;
                    }
                }

                if (productLabel) {
                    productLabel.textContent = state.productName || 'this product';
                }

                updatePlaceholderText();
            };

            const updatePlaceholderText = () => {
                if (!placeholder) {
                    return;
                }

                if (!state.productId) {
                    placeholder.hidden = false;
                    return;
                }

                if (state.messages.length === 0) {
                    placeholder.hidden = false;
                    return;
                }

                placeholder.hidden = true;
            };

            const resetConversation = () => {
                state.messages = [];
                questionListMessage = null;
                followUpMessage = null;
                pendingQuestionListData = null;
                chatWindow.querySelectorAll('.chatbot-message').forEach((element) => element.remove());
                updatePlaceholderText();
            };

            function handleQuestionSelection(option) {
                if (!option) {
                    return;
                }

                const timestamp = new Date().toISOString();
                const preservedQuestionList = questionListMessage
                    ? cloneQuestionListData(questionListMessage.data)
                    : null;

                if (questionListMessage?.element) {
                    questionListMessage.element.remove();
                }
                questionListMessage = null;
                if (followUpMessage?.element) {
                    followUpMessage.element.remove();
                }
                followUpMessage = null;
                pendingQuestionListData = null;

                appendMessage({
                    role: 'user',
                    content: option.question,
                    timestamp,
                    product_tag: state.productName || 'Selected product',
                });
                appendMessage({
                    role: 'assistant',
                    content: option.answer,
                    timestamp,
                    related_answers: [],
                });

                if (preservedQuestionList) {
                    showFollowUpPrompt(preservedQuestionList);
                }
            }

            function showFollowUpPrompt(questionListData) {
                if (followUpMessage?.element) {
                    followUpMessage.element.remove();
                }

                pendingQuestionListData = cloneQuestionListData(questionListData);

                appendMessage({
                    role: 'assistant',
                    content: 'Do you need more help?',
                    type: 'follow-up',
                    timestamp: new Date().toISOString(),
                    options: [
                        { label: 'Yes', value: 'yes' },
                        { label: 'No', value: 'no' },
                    ],
                }, { trackState: false });
            }

            function handleFollowUpSelection(choice) {
                const timestamp = new Date().toISOString();

                if (followUpMessage?.element) {
                    followUpMessage.element.remove();
                }
                followUpMessage = null;

                if (choice === 'yes' && pendingQuestionListData) {
                    pendingQuestionListData.timestamp = timestamp;
                    pendingQuestionListData.content = state.productName
                        ? `Here are all saved questions for ${state.productName}.`
                        : 'Here are all saved questions for this product.';
                    appendMessage(pendingQuestionListData, { trackState: false });
                } else if (choice === 'no') {
                    appendMessage({
                        role: 'assistant',
                        content: 'Okay!',
                        timestamp,
                        related_answers: [],
                    });
                }

                pendingQuestionListData = null;
            }

            function buildMessageElement(message) {
                const wrapper = document.createElement('div');
                wrapper.className = 'chatbot-message';
                if (message.role === 'user') {
                    wrapper.classList.add('chatbot-message--user');
                }

                const avatar = document.createElement('span');
                avatar.className = 'chatbot-message__avatar';
                avatar.textContent = message.role === 'user' ? 'You' : 'AI';

                const bubble = document.createElement('div');
                bubble.className = 'chatbot-message__bubble';

                const meta = document.createElement('div');
                meta.className = 'chatbot-message__meta';

                if (message.role === 'user' && message.product_tag) {
                    const tag = document.createElement('span');
                    tag.className = 'chatbot-message__tag';
                    tag.textContent = message.product_tag;
                    meta.appendChild(tag);
                } else if (message.role === 'assistant') {
                    const name = document.createElement('strong');
                    name.textContent = 'Assistant';
                    meta.appendChild(name);
                }

                const time = document.createElement('span');
                time.textContent = formatTimestamp(message.timestamp || new Date().toISOString());
                meta.appendChild(time);

                bubble.appendChild(meta);

                let hasCustomContent = false;
                if (message.type === 'question-list' && Array.isArray(message.options) && message.options.length > 0) {
                    if (message.content) {
                        const intro = document.createElement('div');
                        intro.className = 'chatbot-message__content';
                        intro.textContent = message.content;
                        bubble.appendChild(intro);
                    }

                    const optionsWrapper = document.createElement('div');
                    optionsWrapper.className = 'chatbot-message__options';

                    message.options.forEach((option) => {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'chatbot-message__option';
                        button.textContent = option.question;
                        button.addEventListener('click', () => handleQuestionSelection(option));
                        optionsWrapper.appendChild(button);
                    });

                    bubble.appendChild(optionsWrapper);
                    hasCustomContent = true;
                }
                else if (message.type === 'follow-up' && Array.isArray(message.options) && message.options.length > 0) {
                    if (message.content) {
                        const intro = document.createElement('div');
                        intro.className = 'chatbot-message__content';
                        intro.textContent = message.content;
                        bubble.appendChild(intro);
                    }

                    const optionsWrapper = document.createElement('div');
                    optionsWrapper.className = 'chatbot-message__options';

                    message.options.forEach((option) => {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'chatbot-message__option';
                        button.textContent = option.label ?? option.value;
                        button.addEventListener('click', () => handleFollowUpSelection(option.value));
                        optionsWrapper.appendChild(button);
                    });

                    bubble.appendChild(optionsWrapper);
                    hasCustomContent = true;
                }

                if (!hasCustomContent) {
                    const content = document.createElement('div');
                    content.className = 'chatbot-message__content';
                    content.innerHTML = message.content;
                    bubble.appendChild(content);
                }

                if (message.type !== 'question-list' && Array.isArray(message.related_answers) && message.related_answers.length > 0) {
                    const listWrapper = document.createElement('div');
                    listWrapper.className = 'chatbot-message__content';
                    const heading = document.createElement('strong');
                    heading.textContent = 'Other related answers:';
                    listWrapper.appendChild(heading);

                    const list = document.createElement('ul');
                    list.className = 'chatbot-message__list';
                    message.related_answers.forEach((answer) => {
                        const item = document.createElement('li');
                        item.innerHTML = answer;
                        list.appendChild(item);
                    });
                    listWrapper.appendChild(list);
                    bubble.appendChild(listWrapper);
                }

                wrapper.appendChild(avatar);
                wrapper.appendChild(bubble);
                return wrapper;
            }

            function appendMessage(message, { trackState = true } = {}) {
                if (message.type === 'question-list') {
                    if (questionListMessage?.element) {
                        questionListMessage.element.remove();
                    }
                    if (followUpMessage?.element) {
                        followUpMessage.element.remove();
                    }
                    followUpMessage = null;
                    pendingQuestionListData = null;
                } else if (message.type === 'follow-up' && followUpMessage?.element) {
                    followUpMessage.element.remove();
                }

                if (trackState) {
                    state.messages.push(message);
                }

                const element = buildMessageElement(message);
                chatWindow.appendChild(element);
                updatePlaceholderText();
                chatWindow.scrollTop = chatWindow.scrollHeight;

                if (message.type === 'question-list') {
                    questionListMessage = {
                        data: cloneQuestionListData(message),
                        element,
                    };
                } else if (message.type === 'follow-up') {
                    followUpMessage = {
                        data: cloneQuestionListData(message),
                        element,
                    };
                }

                return element;
            }

            const updateSendState = () => {
                const hasProduct = !!state.productId;
                const messageValue = messageInput ? messageInput.value.trim() : '';

                if (messageInput) {
                    messageInput.disabled = !hasProduct || state.busy || !productsAvailable;
                }

                if (sendButton) {
                    sendButton.disabled = !hasProduct || messageValue.length === 0 || state.busy || !productsAvailable;
                }
            };

            const syncProductState = (productIdValue, productNameValue) => {
                let numericId = null;
                if (productIdValue !== null && productIdValue !== undefined && String(productIdValue).trim() !== '') {
                    const parsed = Number(productIdValue);
                    numericId = Number.isFinite(parsed) ? parsed : null;
                }
                const readableName = (productNameValue ?? '').trim();
                const changed = state.productId !== numericId;

                state.productId = numericId;
                state.productName = readableName;

                if (changed) {
                    if (messageInput) {
                        messageInput.value = '';
                    }
                    resetConversation();
                } else {
                    updatePlaceholderText();
                }

                updateInterfaceVisibility();
                updateSendState();

                if (changed && numericId && messageInput) {
                    messageInput.focus({ preventScroll: true });
                }
            };

            const setBusy = (busy) => {
                state.busy = busy;
                updateSendState();
            };

            updateInterfaceVisibility();
            resetConversation();

            if (Array.isArray(initialMessages) && initialMessages.length > 0) {
                initialMessages.forEach((message) => appendMessage(message));
            }

            updateSendState();

            if (productCombobox) {
                productCombobox.addEventListener('product-combobox:select', (event) => {
                    const detail = event.detail ?? {};
                    syncProductState(detail.productId ?? '', detail.productName ?? '');
                });
            }

            if (productHiddenInput) {
                productHiddenInput.addEventListener('change', () => {
                    const hiddenValue = productHiddenInput.value || '';
                    const selectedName = hiddenValue
                        ? (productDisplayInput?.dataset.selectedName ?? productDisplayInput?.value ?? '')
                        : '';
                    syncProductState(hiddenValue, selectedName);
                });
            }

            if (!productsAvailable) {
                return;
            }

            messageInput?.addEventListener('input', updateSendState);

            if (messageInput) {
                messageInput.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter' && !event.shiftKey) {
                        event.preventDefault();
                        if (form && !sendButton?.disabled) {
                            if (typeof form.requestSubmit === 'function') {
                                form.requestSubmit(sendButton || null);
                            } else {
                                form.submit();
                            }
                        }
                    }
                });
            }

            form?.addEventListener('submit', async (event) => {
                event.preventDefault();

                if (!messageInput) {
                    return;
                }

                if (!state.productId || state.busy) {
                    return;
                }

                const question = messageInput.value.trim();
                if (question === '') {
                    updateSendState();
                    return;
                }

                const userMessage = {
                    role: 'user',
                    content: question,
                    timestamp: new Date().toISOString(),
                    product_tag: state.productName || 'Selected product',
                };
                appendMessage(userMessage);

                messageInput.value = '';
                setBusy(true);

                try {
                    const url = new URL(endpoint, window.location.origin);
                    url.searchParams.set('product', String(state.productId));
                    url.searchParams.set('term', question);

                    const response = await fetch(url.toString(), {
                        headers: {
                            'Accept': 'application/json',
                        },
                    });

                    if (!response.ok) {
                        throw new Error(`Request failed with status ${response.status}`);
                    }

                    const data = await response.json();
                    const answers = Array.isArray(data.answers) ? data.answers : [];
                    const normalizedQuery = (data.query ?? '').trim().toLowerCase();

                    if (normalizedQuery === 'all' && answers.length > 0) {
                        appendMessage({
                            role: 'assistant',
                            type: 'question-list',
                            content: state.productName
                                ? `Here are all saved questions for ${state.productName}.`
                                : 'Here are all saved questions for this product.',
                            timestamp: data.timestamp ?? new Date().toISOString(),
                            options: answers.map((item) => ({
                                question: item.question,
                                answer: item.answer,
                            })),
                        });
                    } else {
                        const primary = answers.length > 0 ? answers[0] : null;
                        const related = answers.length > 1 ? answers.slice(1).map((item) => item.answer) : [];

                        const assistantMessage = {
                            role: 'assistant',
                            content: primary ? primary.answer : 'I couldn’t find a matching answer for that query. Try a broader keyword or enrich the knowledge base for this product.',
                            timestamp: data.timestamp ?? new Date().toISOString(),
                            related_answers: related,
                        };
                        appendMessage(assistantMessage);
                    }
                } catch (error) {
                    console.error(error);
                    appendMessage({
                        role: 'assistant',
                        content: 'Something went wrong while fetching the answer. Please try again.',
                        timestamp: new Date().toISOString(),
                        related_answers: [],
                    });
                } finally {
                    updateInterfaceVisibility();
                    setBusy(false);
                }
            });
        })();
    </script>
@endpush
