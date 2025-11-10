@extends('layouts.app')

@push('styles')
    @include('partials.dashboard-styles')
    @include('partials.product-combobox-styles')
    @include('chatbot.partials.styles')
    <style>
        .qa-grid {
            display: grid;
            gap: 1.25rem;
        }

        .qa-pair {
            display: grid;
            gap: 1rem;
            border: 1px solid rgba(148, 163, 184, 0.25);
            border-radius: 1rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
        }

        .qa-pair label {
            display: grid;
            gap: 0.5rem;
            font-weight: 600;
            color: rgba(15, 23, 42, 0.85);
        }

        .qa-pair textarea {
            border-radius: 0.8rem;
            border: 1px solid rgba(148, 163, 184, 0.35);
            padding: 0.75rem 0.85rem;
            font-size: 0.95rem;
            resize: vertical;
            min-height: 48px;
        }

        .qa-pair textarea:focus-visible {
            outline: none;
            border-color: rgba(79, 70, 229, 0.45);
            box-shadow: 0 0 0 3px rgba(129, 140, 248, 0.18);
        }

        .rich-text-editor {
            display: grid;
            gap: 0.6rem;
            border: 1px solid rgba(148, 163, 184, 0.35);
            border-radius: 1rem;
            padding: 0.75rem;
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.95) 0%, rgba(248, 250, 252, 0.65) 100%);
        }

        .rich-text-editor__toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .rich-text-editor__toolbar button {
            border: 1px solid rgba(79, 70, 229, 0.22);
            background: rgba(79, 70, 229, 0.1);
            color: rgba(30, 41, 59, 0.92);
            border-radius: 999px;
            padding: 0.35rem;
            font-size: 0.88rem;
            cursor: pointer;
            transition: background 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
        }

        .rich-text-editor__toolbar button:hover,
        .rich-text-editor__toolbar button:focus-visible {
            background: rgba(79, 70, 229, 0.18);
            box-shadow: 0 8px 16px rgba(79, 70, 229, 0.18);
            outline: none;
            transform: translateY(-1px);
        }

        .rich-text-editor__icon {
            width: 18px;
            height: 18px;
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 1px, 1px);
            white-space: nowrap;
            border: 0;
        }

        .rich-text-editor__content {
            min-height: 160px;
            padding: 0.75rem;
            border-radius: 0.85rem;
            background: #fff;
            border: 1px solid rgba(148, 163, 184, 0.3);
            overflow: auto;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .rich-text-editor__content:focus-visible {
            outline: none;
            border-color: rgba(79, 70, 229, 0.45);
            box-shadow: 0 0 0 3px rgba(129, 140, 248, 0.2);
        }

        .rich-text-editor__content h2,
        .rich-text-editor__content h3,
        .rich-text-editor__content h4 {
            margin: 0.75rem 0 0.5rem;
            font-weight: 600;
            color: rgba(30, 41, 59, 0.95);
        }

        .rich-text-editor__content ul,
        .rich-text-editor__content ol {
            padding-left: 1.5rem;
            margin: 0.6rem 0;
        }

        .rich-text-editor__content img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0.75rem auto;
            border-radius: 0.65rem;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.12);
        }

        .qa-actions {
            display: flex;
            justify-content: flex-end;
        }

        .qa-remove {
            border: 1px solid rgba(248, 113, 113, 0.4);
            background: rgba(248, 113, 113, 0.12);
            color: rgba(153, 27, 27, 0.95);
            border-radius: 999px;
            padding: 0.35rem 0.85rem;
            cursor: pointer;
            transition: background 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
        }

        .qa-remove:hover,
        .qa-remove:focus-visible {
            background: rgba(248, 113, 113, 0.2);
            box-shadow: 0 8px 16px rgba(248, 113, 113, 0.18);
            outline: none;
            transform: translateY(-1px);
        }

        .form-actions {
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
        }

        .form-actions button {
            border-radius: 999px;
            padding: 0.6rem 1.25rem;
            font-weight: 600;
            cursor: pointer;
        }

        .form-actions .ghost-button {
            border: 1px solid rgba(79, 70, 229, 0.18);
            background: rgba(79, 70, 229, 0.08);
            color: rgba(30, 41, 59, 0.9);
        }

        .form-actions .ghost-button:hover,
        .form-actions .ghost-button:focus-visible {
            background: rgba(79, 70, 229, 0.14);
            outline: none;
        }

        @media (max-width: 768px) {
            .form-actions {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
@endpush

@section('content')
    <div class="dashboard-grid">
        @include('partials.dashboard-sidebar')

        <section class="dashboard-content">
            <div class="chatbot-grid">
                @if (session('status'))
                    <article role="status">
                        {{ session('status') }}
                    </article>
                @endif

                @if ($errors->any())
                    <article role="alert">
                        <strong>There was a problem:</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </article>
                @endif

                <section class="card">
                    <header>
                        <h2>Chatbot Knowledge Base</h2>
                        <p class="muted">Choose a product, then craft question-and-answer pairs. Use the formatting tools to add headings, lists, or screenshots pasted from your clipboard before saving.</p>
                    </header>

                    @php
                        $selectedProduct = $products->firstWhere('id', (int) old('product_id', $selectedProductId));
                    @endphp

                    <form method="POST" action="{{ route('chatbot.entries.store') }}" class="qa-grid" id="chatbot-knowledge-form">
                        @csrf

                        <div class="product-combobox" data-product-combobox>
                            <label for="knowledge-product-input">
                                Product
                                <input
                                    type="text"
                                    id="knowledge-product-input"
                                    class="product-combobox__input"
                                    name="product_search"
                                    value="{{ old('product_search', $selectedProduct->name ?? '') }}"
                                    placeholder="Choose product..."
                                    autocomplete="off"
                                    data-selected-name="{{ $selectedProduct->name ?? '' }}"
                                    {{ $products->isEmpty() ? 'disabled' : '' }}
                                    required
                                >
                            </label>
                            <input
                                type="hidden"
                                name="product_id"
                                value="{{ $selectedProduct->id ?? $selectedProductId }}"
                                data-product-selected
                            >

                            <div class="product-combobox__dropdown" role="listbox" aria-label="Product options">
                                @if ($products->isEmpty())
                                    <p class="product-combobox__empty">No products available yet.</p>
                                @else
                                    <p class="product-combobox__empty" data-empty-message hidden>No matching products found.</p>
                                    @foreach ($products as $product)
                                        <button
                                            type="button"
                                            class="product-combobox__option {{ $selectedProduct && $selectedProduct->id === $product->id ? 'is-active' : '' }}"
                                            data-product-option
                                            data-product-id="{{ $product->id }}"
                                            data-product-name="{{ $product->name }}"
                                            role="option"
                                            aria-selected="{{ $selectedProduct && $selectedProduct->id === $product->id ? 'true' : 'false' }}"
                                        >
                                            {{ $product->name }}
                                        </button>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <div id="qa-container">
                            @php
                                $oldQuestions = old('questions', ['']);
                                $oldAnswers = old('answers', ['']);
                            @endphp
                            @foreach ($oldQuestions as $index => $question)
                                <div class="qa-pair">
                                    <label>
                                        Question
                                        <textarea name="questions[]" rows="2" required>{{ $question }}</textarea>
                                    </label>
                                    <label>
                                        Answer
                                        <div class="rich-text-editor" data-rich-editor>
                                            <div class="rich-text-editor__toolbar" data-rich-editor-toolbar>
                                                <button type="button" data-rich-editor-command="bold" aria-label="Bold">
                                                    <svg class="rich-text-editor__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                        <path d="M7 12h4a4 4 0 0 1 0 8H7z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                                        <path d="M7 4h3a4 4 0 0 1 0 8H7z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                    <span class="sr-only">Bold</span>
                                                </button>
                                                <button type="button" data-rich-editor-command="italic" aria-label="Italic">
                                                    <svg class="rich-text-editor__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                        <path d="M19 4h-9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                                        <path d="M14 4l-2 16" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                                        <path d="M5 20h9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                    <span class="sr-only">Italic</span>
                                                </button>
                                                <button type="button" data-rich-editor-command="underline" aria-label="Underline">
                                                    <svg class="rich-text-editor__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                        <path d="M6 4v7a6 6 0 0 0 12 0V4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                                        <path d="M4 20h16" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                    <span class="sr-only">Underline</span>
                                                </button>
                                                <button type="button" data-rich-editor-command="formatBlock" data-command-value="p" aria-label="Paragraph">
                                                    <svg class="rich-text-editor__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                        <path d="M10 4h5a4 4 0 0 1 0 8h-3v8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                                        <path d="M10 4v16" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                    <span class="sr-only">Paragraph</span>
                                                </button>
                                                <button type="button" data-rich-editor-command="formatBlock" data-command-value="h2" aria-label="Heading 2">
                                                    <svg class="rich-text-editor__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                        <path d="M4 6v12M10 6v12M4 12h6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                                        <path d="M14 10h5l-5 7h5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                    <span class="sr-only">Heading 2</span>
                                                </button>
                                                <button type="button" data-rich-editor-command="formatBlock" data-command-value="h3" aria-label="Heading 3">
                                                    <svg class="rich-text-editor__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                        <path d="M4 6v12M10 6v12M4 12h6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                                        <path d="M14 10h3a2.5 2.5 0 0 1 0 5H15A2.5 2.5 0 0 1 18 18.5h-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                    <span class="sr-only">Heading 3</span>
                                                </button>
                                                <button type="button" data-rich-editor-command="insertUnorderedList" aria-label="Bulleted list">
                                                    <svg class="rich-text-editor__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                        <path d="M8 6h13M8 12h13M8 18h13" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                                        <circle cx="4" cy="6" r="1.5" fill="currentColor" />
                                                        <circle cx="4" cy="12" r="1.5" fill="currentColor" />
                                                        <circle cx="4" cy="18" r="1.5" fill="currentColor" />
                                                    </svg>
                                                    <span class="sr-only">Bulleted list</span>
                                                </button>
                                                <button type="button" data-rich-editor-command="insertOrderedList" aria-label="Numbered list">
                                                    <svg class="rich-text-editor__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                        <path d="M8 6h13M8 12h13M8 18h13" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                                        <path d="M4 5.5H5v3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                                        <path d="M3.5 12.5h2L3 16h2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                                        <path d="M3 18.5h2.5L4 21" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                    <span class="sr-only">Numbered list</span>
                                                </button>
                                                <button type="button" data-rich-editor-command="justifyLeft" aria-label="Align left">
                                                    <svg class="rich-text-editor__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                        <path d="M4 6h16M4 12h12M4 18h16" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                    <span class="sr-only">Align left</span>
                                                </button>
                                                <button type="button" data-rich-editor-command="justifyCenter" aria-label="Align center">
                                                    <svg class="rich-text-editor__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                        <path d="M5 6h14M3 12h18M7 18h10" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                    <span class="sr-only">Align center</span>
                                                </button>
                                                <button type="button" data-rich-editor-command="justifyRight" aria-label="Align right">
                                                    <svg class="rich-text-editor__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                        <path d="M4 6h16M8 12h12M4 18h16" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                    <span class="sr-only">Align right</span>
                                                </button>
                                                <button type="button" data-rich-editor-command="justifyFull" aria-label="Justify">
                                                    <svg class="rich-text-editor__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                        <path d="M4 6h16M4 10h16M4 14h16M4 18h16" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                    <span class="sr-only">Justify</span>
                                                </button>
                                                <button type="button" data-rich-editor-upload="true" aria-label="Insert image">
                                                    <svg class="rich-text-editor__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                        <path d="M4 5h16a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                                        <path d="M4 16l4.5-4.5a1 1 0 0 1 1.5.083L12 15l3-3a1 1 0 0 1 1.5.083L20 16" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                                        <circle cx="8" cy="9" r="1.5" fill="currentColor" />
                                                    </svg>
                                                    <span class="sr-only">Insert image</span>
                                                </button>
                                            </div>
                                            <div
                                                class="rich-text-editor__content"
                                                contenteditable="true"
                                                data-rich-editor-content
                                            >{!! $oldAnswers[$index] ?? '' !!}</div>
                                            <input type="file" accept="image/*" data-rich-editor-upload-input hidden>
                                        </div>
                                        <textarea
                                            name="answers[]"
                                            hidden
                                            required
                                            data-rich-editor-input
                                        >{{ $oldAnswers[$index] ?? '' }}</textarea>
                                    </label>
                                    <div class="qa-actions">
                                        <button type="button" class="qa-remove" data-remove-qa>Remove</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="form-actions">
                            <button type="button" class="ghost-button" id="add-qa-button">Add Q&amp;A</button>
                            <button type="submit">Save Knowledge</button>
                        </div>
                    </form>
                </section>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    @include('partials.dashboard-scripts')
    @include('partials.product-combobox-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const qaContainer = document.getElementById('qa-container');
            const addButton = document.getElementById('add-qa-button');
            const form = document.getElementById('chatbot-knowledge-form');
            const editors = new Set();

            const initializeRichEditor = (scope) => {
                const content = scope.querySelector('[data-rich-editor-content]');
                const input = scope.querySelector('[data-rich-editor-input]');
                const toolbar = scope.querySelector('[data-rich-editor-toolbar]');
                const uploadInput = scope.querySelector('[data-rich-editor-upload-input]');

                if (!content || !input || content.dataset.richEditorReady === 'true') {
                    return;
                }

                content.dataset.richEditorReady = 'true';
                const normalize = (value) => value.trim();
                const initialValue = normalize(input.value);
                content.innerHTML = initialValue;
                input.value = initialValue;

                const updateHidden = () => {
                    input.value = normalize(content.innerHTML);
                };

                const handleToolbarClick = (event) => {
                    const button = event.target.closest('button');
                    if (!button) {
                        return;
                    }

                    event.preventDefault();

                    if (button.dataset.richEditorUpload === 'true') {
                        uploadInput?.click();
                        return;
                    }

                    const command = button.dataset.richEditorCommand;
                    if (!command) {
                        return;
                    }

                    content.focus({ preventScroll: true });
                    let value = button.dataset.commandValue || null;
                    if (command === 'formatBlock' && value) {
                        value = `<${value}>`;
                    }

                    document.execCommand(command, false, value);
                    updateHidden();
                };

                const insertImageDataUrl = (dataUrl) => {
                    if (typeof dataUrl !== 'string' || dataUrl.trim() === '') {
                        return;
                    }

                    content.focus({ preventScroll: true });
                    document.execCommand('insertImage', false, dataUrl);
                    updateHidden();
                };

                const processImageFile = (file) => {
                    if (!file || !file.type.startsWith('image/')) {
                        return Promise.resolve(null);
                    }

                    const MAX_DIMENSION = 3000;
                    const TARGET_SIZE = 1.8 * 1024 * 1024; // ~1.8MB
                    const MIN_QUALITY = 0.4;
                    const MIN_DIMENSION = 900;

                    return new Promise((resolve) => {
                        const reader = new FileReader();
                        reader.addEventListener('load', (loadEvent) => {
                            const source = loadEvent.target?.result;
                            if (typeof source !== 'string') {
                                resolve(null);
                                return;
                            }

                            const image = new Image();
                            image.addEventListener('load', () => {
                                let { width, height } = image;

                                const dimensionScale = Math.min(1, MAX_DIMENSION / Math.max(width, height));
                                width = Math.max(1, Math.round(width * dimensionScale));
                                height = Math.max(1, Math.round(height * dimensionScale));

                                const canvas = document.createElement('canvas');
                                const ctx = canvas.getContext('2d');
                                if (!ctx) {
                                    resolve(source);
                                    return;
                                }

                                const drawToCanvas = () => {
                                    canvas.width = Math.max(1, Math.round(width));
                                    canvas.height = Math.max(1, Math.round(height));
                                    ctx.fillStyle = '#ffffff';
                                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                                    ctx.drawImage(image, 0, 0, canvas.width, canvas.height);
                                };

                                let quality = 0.82;
                                let dataUrl;

                                const encode = () => canvas.toDataURL('image/jpeg', quality);
                                drawToCanvas();
                                dataUrl = encode();

                                const bytesFromDataUrl = (value) => Math.max(0, Math.round(value.length * 0.75));

                                while (bytesFromDataUrl(dataUrl) > TARGET_SIZE) {
                                    if (quality > MIN_QUALITY) {
                                        quality = Math.max(MIN_QUALITY, quality - 0.08);
                                    } else if (Math.max(width, height) > MIN_DIMENSION) {
                                        width *= 0.85;
                                        height *= 0.85;
                                        drawToCanvas();
                                    } else {
                                        break;
                                    }
                                    dataUrl = encode();
                                }

                                resolve(dataUrl);
                            });
                            image.addEventListener('error', () => resolve(null));
                            image.src = source;
                        });
                        reader.readAsDataURL(file);
                    });
                };

                const handlePaste = async (event) => {
                    const items = event.clipboardData?.items;
                    if (!items || items.length === 0) {
                        return;
                    }

                    const tasks = Array.from(items)
                        .filter((item) => item.kind === 'file' && item.type.startsWith('image/'))
                        .map((item) => processImageFile(item.getAsFile() ?? null));

                    if (tasks.length === 0) {
                        return;
                    }

                    event.preventDefault();
                    const results = await Promise.all(tasks);
                    results.filter(Boolean).forEach((dataUrl) => insertImageDataUrl(dataUrl));
                };

                const handleUploadChange = async (event) => {
                    const fileInput = event.target;
                    if (!(fileInput instanceof HTMLInputElement) || !fileInput.files) {
                        return;
                    }

                    const [file] = fileInput.files;
                    fileInput.value = '';

                    const dataUrl = await processImageFile(file ?? null);
                    if (dataUrl) {
                        insertImageDataUrl(dataUrl);
                    }
                };

                const record = {
                    scope,
                    updateHidden,
                    destroy() {
                        toolbar?.removeEventListener('click', handleToolbarClick);
                        content.removeEventListener('input', updateHidden);
                        content.removeEventListener('blur', updateHidden);
                        content.removeEventListener('paste', handlePaste);
                        uploadInput?.removeEventListener('change', handleUploadChange);
                    },
                };

                content.addEventListener('input', updateHidden);
                content.addEventListener('blur', updateHidden);
                content.addEventListener('paste', handlePaste);
                toolbar?.addEventListener('click', handleToolbarClick);
                uploadInput?.addEventListener('change', handleUploadChange);

                editors.add(record);
                updateHidden();
            };

            const createQaBlock = () => {
                const wrapper = document.createElement('div');
                wrapper.className = 'qa-pair';
                wrapper.innerHTML = `
                    <label>
                        Question
                        <textarea name="questions[]" rows="2" required></textarea>
                    </label>
                    <label>
                        Answer
                        <div class="rich-text-editor" data-rich-editor>
                            <div class="rich-text-editor__toolbar" data-rich-editor-toolbar>
                                <button type="button" data-rich-editor-command="bold" aria-label="Bold">
                                    <svg class="rich-text-editor__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M7 12h4a4 4 0 0 1 0 8H7z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M7 4h3a4 4 0 0 1 0 8H7z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span class="sr-only">Bold</span>
                                </button>
                                <button type="button" data-rich-editor-command="italic" aria-label="Italic">
                                    <svg class="rich-text-editor__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M19 4h-9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M14 4l-2 16" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M5 20h9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span class="sr-only">Italic</span>
                                </button>
                                <button type="button" data-rich-editor-command="underline" aria-label="Underline">
                                    <svg class="rich-text-editor__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M6 4v7a6 6 0 0 0 12 0V4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M4 20h16" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span class="sr-only">Underline</span>
                                </button>
                                <button type="button" data-rich-editor-command="formatBlock" data-command-value="p" aria-label="Paragraph">
                                    <svg class="rich-text-editor__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M10 4h5a4 4 0 0 1 0 8h-3v8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M10 4v16" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span class="sr-only">Paragraph</span>
                                </button>
                                <button type="button" data-rich-editor-command="formatBlock" data-command-value="h2" aria-label="Heading 2">
                                    <svg class="rich-text-editor__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M4 6v12M10 6v12M4 12h6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M14 10h5l-5 7h5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span class="sr-only">Heading 2</span>
                                </button>
                                <button type="button" data-rich-editor-command="formatBlock" data-command-value="h3" aria-label="Heading 3">
                                    <svg class="rich-text-editor__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M4 6v12M10 6v12M4 12h6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M14 10h3a2.5 2.5 0 0 1 0 5H15A2.5 2.5 0 0 1 18 18.5h-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span class="sr-only">Heading 3</span>
                                </button>
                                                <button type="button" data-rich-editor-command="formatBlock" data-command-value="h4" aria-label="Heading 4">
                                                    <svg class="rich-text-editor__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                        <path d="M4 6v12M10 6v12M4 12h6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                                        <path d="M18 6v12M14 14h4M14 6v8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                    <span class="sr-only">Heading 4</span>
                                                </button>
                                <button type="button" data-rich-editor-command="insertUnorderedList" aria-label="Bulleted list">
                                    <svg class="rich-text-editor__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M8 6h13M8 12h13M8 18h13" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                        <circle cx="4" cy="6" r="1.5" fill="currentColor" />
                                        <circle cx="4" cy="12" r="1.5" fill="currentColor" />
                                        <circle cx="4" cy="18" r="1.5" fill="currentColor" />
                                    </svg>
                                    <span class="sr-only">Bulleted list</span>
                                </button>
                                <button type="button" data-rich-editor-command="insertOrderedList" aria-label="Numbered list">
                                    <svg class="rich-text-editor__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M8 6h13M8 12h13M8 18h13" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M4 5.5H5v3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M3.5 12.5h2L3 16h2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M3 18.5h2.5L4 21" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span class="sr-only">Numbered list</span>
                                </button>
                                <button type="button" data-rich-editor-command="justifyLeft" aria-label="Align left">
                                    <svg class="rich-text-editor__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M4 6h16M4 12h12M4 18h16" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span class="sr-only">Align left</span>
                                </button>
                                <button type="button" data-rich-editor-command="justifyCenter" aria-label="Align center">
                                    <svg class="rich-text-editor__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M5 6h14M3 12h18M7 18h10" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span class="sr-only">Align center</span>
                                </button>
                                <button type="button" data-rich-editor-command="justifyRight" aria-label="Align right">
                                    <svg class="rich-text-editor__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M4 6h16M8 12h12M4 18h16" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span class="sr-only">Align right</span>
                                </button>
                                <button type="button" data-rich-editor-command="justifyFull" aria-label="Justify">
                                    <svg class="rich-text-editor__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M4 6h16M4 10h16M4 14h16M4 18h16" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <span class="sr-only">Justify</span>
                                </button>
                                <button type="button" data-rich-editor-upload="true" aria-label="Insert image">
                                    <svg class="rich-text-editor__icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M4 5h16a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M4 16l4.5-4.5a1 1 0 0 1 1.5.083L12 15l3-3a1 1 0 0 1 1.5.083L20 16" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                                        <circle cx="8" cy="9" r="1.5" fill="currentColor" />
                                    </svg>
                                    <span class="sr-only">Insert image</span>
                                </button>
                            </div>
                            <div class="rich-text-editor__content" contenteditable="true" data-rich-editor-content></div>
                            <input type="file" accept="image/*" data-rich-editor-upload-input hidden>
                        </div>
                        <textarea name="answers[]" hidden required data-rich-editor-input></textarea>
                    </label>
                    <div class="qa-actions">
                        <button type="button" class="qa-remove" data-remove-qa>Remove</button>
                    </div>
                `;
                return wrapper;
            };

            qaContainer?.querySelectorAll('.qa-pair').forEach((pair) => initializeRichEditor(pair));

            addButton?.addEventListener('click', () => {
                const block = createQaBlock();
                qaContainer?.append(block);
                initializeRichEditor(block);
                block.querySelector('textarea[name="questions[]"]')?.focus();
            });

            qaContainer?.addEventListener('click', (event) => {
                const remove = event.target.closest('[data-remove-qa]');
                if (!remove) {
                    return;
                }

                const block = remove.closest('.qa-pair');
                const existingBlocks = qaContainer.querySelectorAll('.qa-pair').length;
                if (existingBlocks <= 1) {
                    alert('At least one Q&A pair is required.');
                    return;
                }

                if (block) {
                    Array.from(editors).forEach((editor) => {
                        if (editor.scope === block) {
                            editor.destroy();
                            editors.delete(editor);
                        }
                    });
                    block.remove();
                }
            });

            form?.addEventListener('submit', () => {
                Array.from(editors).forEach((editor) => {
                    if (!document.contains(editor.scope)) {
                        editors.delete(editor);
                        return;
                    }
                    editor.updateHidden();
                });
            });
        });
    </script>
@endpush
