@extends('layouts.app')

@push('styles')
    @include('partials.dashboard-styles')
    @include('chatbot.partials.styles')
    <style>
        .knowledge-edit {
            display: grid;
            gap: 1.25rem;
        }

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

        .knowledge-edit__actions {
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
        }
    </style>
@endpush

@section('content')
    <div class="dashboard-grid">
        @include('partials.dashboard-sidebar')

        <section class="dashboard-content">
            <div class="chatbot-grid">
                <section class="card knowledge-edit">
                    <header>
                        <h2>Edit Knowledge Entry</h2>
                        <p class="muted">
                            Update the question and answer below. Changes are reflected immediately in the simulator and existing knowledge list.
                        </p>
                    </header>

                        <div>
                            <p class="muted">
                                Product:
                                <strong>{{ $products->firstWhere('id', $entry->product_id)?->name ?? 'Unknown product' }}</strong>
                            </p>
                            <a href="{{ route('chatbot.knowledgebase', ['product' => $entry->product_id]) }}" class="link">
                                &larr; Back to existing knowledge
                            </a>
                        </div>

                    <form method="POST" action="{{ route('chatbot.entries.update', $entry) }}" class="qa-grid" id="chatbot-entry-edit-form">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="redirect_product" value="{{ $redirectProduct }}">

                        <div class="qa-pair">
                            <label>
                                Question
                                <textarea name="question" rows="2" required>{{ old('question', $entry->question) }}</textarea>
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
                                    <div
                                        class="rich-text-editor__content"
                                        contenteditable="true"
                                        data-rich-editor-content
                                    >{!! old('answer', $entry->answer) !!}</div>
                                    <input type="file" accept="image/*" data-rich-editor-upload-input hidden>
                                </div>
                                <textarea
                                    name="answer"
                                    hidden
                                    required
                                    data-rich-editor-input
                                >{!! old('answer', $entry->answer) !!}</textarea>
                            </label>
                        </div>

                        <div class="knowledge-edit__actions">
                            <a class="ghost-button" href="{{ route('chatbot.knowledgebase', ['product' => $redirectProduct]) }}">
                                Cancel
                            </a>
                            <button type="submit">Save changes</button>
                        </div>
                    </form>
                </section>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    @include('partials.dashboard-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('chatbot-entry-edit-form');
            if (!form) {
                return;
            }

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
                const initialValue = normalize(input.value ?? '');
                content.innerHTML = initialValue;
                input.value = initialValue;

                const updateHidden = () => {
                    input.value = normalize(content.innerHTML);
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
                                const encode = () => canvas.toDataURL('image/jpeg', quality);
                                drawToCanvas();
                                let dataUrl = encode();
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

            form.querySelectorAll('[data-rich-editor]').forEach((editor) => initializeRichEditor(editor));

            window.addEventListener('beforeunload', () => {
                editors.forEach((editor) => editor.destroy());
                editors.clear();
            });
        });
    </script>
@endpush
