@extends('layouts.app')

@php
    $paymentMethodLimits = $paymentMethodLimits ?? [];
@endphp

@push('styles')
    @include('partials.dashboard-styles')
    <style>
        .qr-layout {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .qr-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .qr-form {
            border: 1px dashed rgba(15, 23, 42, 0.2);
            border-radius: 0.9rem;
            padding: 1.25rem;
            background: rgba(15, 23, 42, 0.03);
        }

        .qr-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 1.25rem;
        }

        @media (max-width: 1400px) {
            .qr-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }

        @media (max-width: 1100px) {
            .qr-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 800px) {
            .qr-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 540px) {
            .qr-grid {
                grid-template-columns: 1fr;
            }
        }

        .qr-card {
            border: 1px solid rgba(15, 23, 42, 0.08);
            border-radius: 0.9rem;
            background: #fff;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.06);
        }

        .qr-card img {
            width: 100%;
            height: 220px;
            object-fit: contain;
            border-radius: 0.6rem;
            background: rgba(15, 23, 42, 0.04);
        }

        .qr-available {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.5rem;
            border: 1px solid rgba(34, 197, 94, 0.2);
            background: rgba(34, 197, 94, 0.08);
            border-radius: 0.6rem;
            padding: 0.4rem 0.65rem;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .qr-visibility-badge {
            align-self: flex-start;
            padding: 0.35rem 0.65rem;
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .qr-visibility-badge--visible {
            background: rgba(22, 163, 74, 0.12);
            color: #15803d;
        }

        .qr-visibility-badge--hidden {
            background: rgba(239, 68, 68, 0.12);
            color: #b91c1c;
        }

        .qr-card__footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .qr-visibility-toggle {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            margin-top: 0.35rem;
        }

        .qr-visibility-toggle input {
            width: 16px;
            height: 16px;
        }

        .qr-copy-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .qr-copy-btn svg {
            width: 16px;
            height: 16px;
        }

        .qr-inline-actions {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            flex-wrap: nowrap;
        }

        .ghost-button--danger {
            border-color: rgba(239, 68, 68, 0.35);
            background: rgba(239, 68, 68, 0.08);
            color: #b91c1c;
        }

        .ghost-button--danger:hover,
        .ghost-button--danger:focus-visible {
            background: rgba(239, 68, 68, 0.18);
            border-color: rgba(239, 68, 68, 0.6);
            color: #7f1d1d;
            outline: none;
        }

        .qr-edit-form {
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            border-top: 1px solid rgba(15, 23, 42, 0.08);
            display: grid;
            gap: 0.65rem;
        }

        .is-hidden {
            display: none;
        }
    </style>
@endpush

@section('content')
    <div class="dashboard-grid">
        @include('partials.dashboard-sidebar')

        <section class="dashboard-content qr-layout">
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

            <div class="card">
                <div class="qr-toolbar">
                    <h2>Available QR</h2>
                    <button type="button" id="qr-toggle">Add QR</button>
                </div>


                <div id="qr-form-wrapper" class="qr-form is-hidden">
                    <form class="form-grid form-grid--compact" method="POST" action="{{ route('qr.scan.store') }}" enctype="multipart/form-data">
                        @csrf
                        <label for="qr-name">
                            QR Name
                            <input
                                type="text"
                                id="qr-name"
                                name="name"
                                value="{{ old('name') }}"
                                placeholder="Eg. Khalti personal"
                                required
                            >
                        </label>

                        <label for="qr-image">
                            QR Image
                            <input
                                type="file"
                                id="qr-image"
                                name="qr_image"
                                accept="image/png,image/jpeg,image/webp,image/svg+xml"
                                required
                            >
                        </label>

                        <label for="qr-description">
                            Description
                            <textarea
                                id="qr-description"
                                name="description"
                                rows="2"
                                placeholder="Optional description or remarks"
                            >{{ old('description') }}</textarea>
                        </label>

                        <label class="qr-visibility-toggle">
                            <input
                                type="checkbox"
                                name="visible_to_employees"
                                value="1"
                                {{ old('visible_to_employees', '1') ? 'checked' : '' }}
                            >
                            Show to employees
                        </label>

                        <div class="form-actions form-actions--row">
                            <button type="submit">Save QR</button>
                            <button type="button" class="ghost-button" id="qr-cancel">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                @if ($qrs->isEmpty())
                    <p class="helper-text">No QR entries yet. Click “Add QR” to upload one.</p>
                @else
                    <div class="qr-grid">
                        @foreach ($qrs as $qr)
                            @php
                                $qrSrc = $qr->image_data;
                                if (! $qrSrc && $qr->file_path) {
                                    $qrSrc = asset('storage/' . ltrim($qr->file_path, '/'));
                                }
                                $methodStats = $paymentMethodLimits[$qr->payment_method_number] ?? null;
                                $availableText = $methodStats
                                    ? ($methodStats['available'] === null
                                        ? 'Unlimited'
                                        : number_format($methodStats['available'], 2))
                                    : 'N/A';
                            @endphp
                            <article class="qr-card">
                                <center><strong>{{ $qr->name }}</strong></center>
                                @if (auth()->user()?->isAdmin())
                                    <div class="qr-visibility-badge {{ $qr->visible_to_employees ? 'qr-visibility-badge--visible' : 'qr-visibility-badge--hidden' }}">
                                        {{ $qr->visible_to_employees ? 'Visible to employees' : 'Hidden from employees' }}
                                    </div>
                                @endif
                                <img src="{{ $qrSrc }}" alt="QR code for {{ $qr->name }}">
                                <div class="qr-available">
                                    <span> Limit:</span>
                                    <span>Rs {{ $availableText }}</span>
                                </div>
                                @if ($qr->payment_method_number)
                                @endif
                                @if ($qr->description)
                                    <p>{{ $qr->description }}</p>
                                @endif
                                <div class="qr-card__footer">
                                    <button
                                        type="button"
                                        class="ghost-button qr-copy-btn"
                                        data-copy-src="{{ $qrSrc }}"
                                    >
                                        <svg viewBox="0 0 24 24" fill="none">
                                            <path d="M9 9h10v12H9z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                        </svg>
                                        QR
                                    </button>
                                    @if ($qr->description)
                                        <button
                                            type="button"
                                            class="ghost-button qr-copy-btn"
                                            data-copy-text="{{ $qr->description }}"
                                        >
                                            <svg viewBox="0 0 24 24" fill="none">
                                            <path d="M9 9h10v12H9z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                                        </svg>
                                            Text
                                        </button>
                                    @endif
                                    @if (auth()->user()?->role === 'admin')
                                        <button
                                            type="button"
                                            class="ghost-button"
                                            data-qr-edit-toggle="{{ $qr->id }}"
                                            aria-label="Edit {{ $qr->name }}"
                                        >
                                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none">
                                                <path d="M4 15.5V20h4.5L19 9.5l-4.5-4.5L4 15.5z" fill="currentColor"/>
                                                <path d="M13.5 5.5l4 4" stroke="currentColor" stroke-width="1.2"/>
                                            </svg>
                                            Edit
                                        </button>
                                    @endif
                                </div>
                                @if (auth()->user()?->role === 'admin')
                                    <form
                                        class="qr-edit-form is-hidden"
                                        data-qr-edit-form="{{ $qr->id }}"
                                        method="POST"
                                        action="{{ route('qr.scan.update', $qr) }}"
                                        enctype="multipart/form-data"
                                    >
                                        @csrf
                                        @method('PUT')
                                        <label>
                                            QR Name
                                            <input type="text" name="name" value="{{ $qr->name }}" required>
                                        </label>
                                        <label>
                                            Replace Image
                                            <input type="file" name="qr_image" accept="image/png,image/jpeg,image/webp,image/svg+xml">
                                        </label>
                                        <label>
                                            Description
                                            <textarea name="description" rows="2" placeholder="Optional description">{{ $qr->description }}</textarea>
                                        </label>
                                        <label class="qr-visibility-toggle">
                                            <input
                                                type="checkbox"
                                                name="visible_to_employees"
                                                value="1"
                                                {{ $qr->visible_to_employees ? 'checked' : '' }}
                                            >
                                            Show to employees
                                        </label>
                                        <div class="form-actions form-actions--row">
                                            <button type="submit">Update</button>
                                            <div class="form-actions__buttons qr-inline-actions">
                                                <button type="button" class="ghost-button" data-qr-edit-cancel="{{ $qr->id }}">Cancel</button>
                                                <button
                                                    type="button"
                                                    class="ghost-button ghost-button--danger"
                                                    data-qr-delete-trigger="{{ $qr->id }}"
                                                    data-confirm="Delete QR '{{ $qr->name }}'?"
                                                >
                                                    Delete
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                    <form
                                        id="qr-delete-form-{{ $qr->id }}"
                                        class="qr-delete-form"
                                        method="POST"
                                        action="{{ route('qr.scan.destroy', $qr) }}"
                                        hidden
                                    >
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const formWrapper = document.getElementById('qr-form-wrapper');
            const toggleBtn = document.getElementById('qr-toggle');
            const cancelBtn = document.getElementById('qr-cancel');

            const normalizeSrc = (src) => {
                if (!src) {
                    return '';
                }

                try {
                    const url = new URL(src, window.location.origin);
                    if (window.location.protocol === 'https:' && url.protocol === 'http:') {
                        url.protocol = 'https:';
                    }
                    return url.toString();
                } catch (error) {
                    return src;
                }
            };

            const blobToPng = (blob) => new Promise((resolve, reject) => {
                const img = new Image();
                const objectUrl = URL.createObjectURL(blob);

                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    canvas.width = img.width;
                    canvas.height = img.height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0);
                    canvas.toBlob((pngBlob) => {
                        URL.revokeObjectURL(objectUrl);
                        if (pngBlob) {
                            resolve(pngBlob);
                        } else {
                            reject(new Error('Unable to convert image'));
                        }
                    }, 'image/png');
                };

                img.onerror = () => {
                    URL.revokeObjectURL(objectUrl);
                    reject(new Error('Unable to load image'));
                };

                img.src = objectUrl;
            });

            const copyImageToClipboard = async (src) => {
                if (!navigator.clipboard?.write || typeof ClipboardItem === 'undefined') {
                    throw new Error('Image clipboard not supported');
                }

                const resolvedSrc = normalizeSrc(src);
                const response = await fetch(resolvedSrc, { mode: 'cors' });
                if (!response.ok) {
                    throw new Error('Failed to fetch image');
                }

                const blob = await response.blob();
                const writeBlob = async (mimeType, data) => {
                    await navigator.clipboard.write([new ClipboardItem({ [mimeType]: data })]);
                };

                try {
                    await writeBlob(blob.type || 'image/png', blob);
                } catch (error) {
                    const pngBlob = await blobToPng(blob);
                    await writeBlob('image/png', pngBlob);
                }
            };

            const setFormVisible = (visible) => {
                formWrapper?.classList.toggle('is-hidden', !visible);
            };

            toggleBtn?.addEventListener('click', () => {
                const shouldShow = formWrapper?.classList.contains('is-hidden');
                setFormVisible(shouldShow);
            });

            cancelBtn?.addEventListener('click', () => setFormVisible(false));

            document.querySelectorAll('.qr-copy-btn').forEach((button) => {
                const originalText = button.textContent.trim();
                button.addEventListener('click', async () => {
                    const text = button.dataset.copyText;
                    const src = button.dataset.copySrc;

                    try {
                        if (text) {
                            await navigator.clipboard.writeText(text);
                        } else if (src) {
                            await copyImageToClipboard(src);
                        } else {
                            return;
                        }
                        button.textContent = 'Copied!';
                    } catch (error) {
                        console.error('Unable to copy', error);
                        button.textContent = 'Copy failed';
                    }

                    setTimeout(() => {
                        button.textContent = originalText;
                    }, 2000);
                });
            });

            document.querySelectorAll('[data-qr-edit-toggle]').forEach((button) => {
                button.addEventListener('click', () => {
                    const id = button.dataset.qrEditToggle;
                    const form = document.querySelector(`[data-qr-edit-form=\"${id}\"]`);
                    form?.classList.toggle('is-hidden');
                });
            });

            document.querySelectorAll('[data-qr-edit-cancel]').forEach((button) => {
                button.addEventListener('click', () => {
                    const id = button.dataset.qrEditCancel;
                    const form = document.querySelector(`[data-qr-edit-form=\"${id}\"]`);
                    form?.classList.add('is-hidden');
                });
            });

            document.querySelectorAll('[data-qr-delete-trigger]').forEach((button) => {
                button.addEventListener('click', () => {
                    const id = button.dataset.qrDeleteTrigger;
                    if (!id) {
                        return;
                    }

                    const form = document.getElementById(`qr-delete-form-${id}`);
                    if (!form) {
                        return;
                    }

                    const message = button.dataset.confirm || 'Delete this QR?';
                    if (window.confirm(message)) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
