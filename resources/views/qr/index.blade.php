@extends('layouts.app')

@php
    use Illuminate\Support\Facades\Storage;
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
            grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
            gap: 1.25rem;
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

        .qr-card__footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
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
                    <h2>Payment QRs</h2>
                    <button type="button" id="qr-toggle">Add QR</button>
                </div>
                <p class="helper-text" style="margin-bottom: 0.75rem;">
                    Please write your Whatsapp number in Payment remarks to track your payment quickly.
                </p>

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
                                $qrSrc = $qr->image_data ?? Storage::disk('public')->url($qr->file_path);
                            @endphp
                            <article class="qr-card">
                                <strong>{{ $qr->name }}</strong>
                                <img src="{{ $qrSrc }}" alt="QR code for {{ $qr->name }}">
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
                                        Copy QR
                                    </button>
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
                                        <div class="form-actions form-actions--row">
                                            <button type="submit">Update</button>
                                            <button type="button" class="ghost-button" data-qr-edit-cancel="{{ $qr->id }}">Cancel</button>
                                        </div>
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
                    const src = button.dataset.copySrc;
                    if (!src) {
                        return;
                    }

                    try {
                        const response = await fetch(src);
                        const blob = await response.blob();
                        await navigator.clipboard.write([
                            new ClipboardItem({ [blob.type]: blob })
                        ]);
                        button.textContent = 'Copied!';
                    } catch (error) {
                        console.error('Unable to copy QR', error);
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
        });
    </script>
@endpush
