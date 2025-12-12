@extends('layouts.app')

@section('content')
    @php
        $loginContent = $loginContent ?? \App\Support\LoginContent::current();
        $perks = implode("\n", $loginContent['perks'] ?? []);
    @endphp

    <section class="login-settings">
        <header class="login-settings__header">
            <div>
                <p class="login-settings__eyebrow">Login page</p>
                <h1>Customize login content</h1>
                <p class="login-settings__lede">Edit the text your customers see on the login page. Use “Preview login” to open the live layout without signing out.</p>
            </div>
            <div class="login-settings__actions">
                <a class="login-settings__preview" href="{{ route('dashboard.login.preview') }}" target="_blank" rel="noopener">Preview login</a>
            </div>
        </header>

        <form method="POST" action="{{ route('dashboard.login.customize.update') }}" class="login-settings__form">
            @csrf

            <div class="login-settings__grid">
                <div class="login-settings__card">
                    <h2>Hero</h2>
                    <label>
                        Badge text
                        <input type="text" name="badge" value="{{ old('badge', $loginContent['badge'] ?? '') }}" required>
                    </label>
                    <label>
                        Brand accent word
                        <input type="text" name="brand_accent" value="{{ old('brand_accent', $loginContent['brand_accent'] ?? '') }}" required>
                    </label>
                    <label>
                        Headline prefix
                        <input type="text" name="headline_prefix" value="{{ old('headline_prefix', $loginContent['headline_prefix'] ?? '') }}" required>
                    </label>
                    <label>
                        Headline accent
                        <input type="text" name="headline_accent" value="{{ old('headline_accent', $loginContent['headline_accent'] ?? '') }}" required>
                    </label>
                    <label>
                        Headline suffix
                        <input type="text" name="headline_suffix" value="{{ old('headline_suffix', $loginContent['headline_suffix'] ?? '') }}" required>
                    </label>
                    <label>
                        Lead paragraph
                        <textarea name="lead" rows="3" required>{{ old('lead', $loginContent['lead'] ?? '') }}</textarea>
                    </label>
                </div>

                <div class="login-settings__card">
                    <h2>Calls to action</h2>
                    <label>
                        Primary button label
                        <input type="text" name="cta_primary_label" value="{{ old('cta_primary_label', $loginContent['cta_primary_label'] ?? '') }}" required>
                    </label>
                    <label>
                        Primary button link (optional)
                        <input type="url" name="cta_primary_link" value="{{ old('cta_primary_link', $loginContent['cta_primary_link'] ?? '') }}" placeholder="https://example.com/signup">
                    </label>
                    <label>
                        Secondary button label
                        <input type="text" name="cta_secondary_label" value="{{ old('cta_secondary_label', $loginContent['cta_secondary_label'] ?? '') }}" required>
                    </label>
                    <label>
                        Perks (one per line)
                        <textarea name="perks" rows="4" placeholder="Instant Access&#10;99.9% Uptime&#10;Simple Pricing">{{ old('perks', $perks) }}</textarea>
                    </label>
                </div>

                <div class="login-settings__card">
                    <h2>Login card</h2>
                    <label>
                        Chip label
                        <input type="text" name="card_chip" value="{{ old('card_chip', $loginContent['card_chip'] ?? '') }}" required>
                    </label>
                    <label>
                        Card title
                        <input type="text" name="card_title" value="{{ old('card_title', $loginContent['card_title'] ?? '') }}" required>
                    </label>
                    <label>
                        Card subtitle
                        <textarea name="card_subtitle" rows="3" required>{{ old('card_subtitle', $loginContent['card_subtitle'] ?? '') }}</textarea>
                    </label>
                </div>
            </div>

            @if ($errors->any())
                <article role="alert" class="login-settings__errors">
                    <strong>Update failed:</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </article>
            @endif

            @if (session('status'))
                <article role="status" class="login-settings__status">
                    {{ session('status') }}
                </article>
            @endif

            <div class="login-settings__footer">
                <button type="submit">Save changes</button>
                <a class="login-settings__preview" href="{{ route('dashboard.login.preview') }}" target="_blank" rel="noopener">Preview login</a>
            </div>
        </form>
    </section>

    @push('styles')
        <style>
            .login-settings {
                display: grid;
                gap: 1.5rem;
                padding: 1rem 0.5rem 2rem;
            }

            .login-settings__header {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 1rem;
            }

            .login-settings__eyebrow {
                text-transform: uppercase;
                letter-spacing: 0.08em;
                font-size: 0.85rem;
                color: #6b7280;
                margin: 0 0 0.2rem;
            }

            .login-settings__lede {
                margin: 0.35rem 0 0;
                color: #4b5563;
            }

            .login-settings__actions {
                display: flex;
                gap: 0.75rem;
            }

            .login-settings__form {
                display: grid;
                gap: 1.25rem;
            }

            .login-settings__grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
                gap: 1rem;
            }

            .login-settings__card {
                border: 1px solid rgba(15, 23, 42, 0.1);
                border-radius: 12px;
                padding: 1rem;
                background: #fff;
                box-shadow: 0 10px 25px rgba(15, 23, 42, 0.06);
                display: grid;
                gap: 0.65rem;
            }

            .login-settings__card h2 {
                margin: 0 0 0.2rem;
            }

            .login-settings__card label {
                display: grid;
                gap: 0.35rem;
                font-weight: 600;
            }

            .login-settings__card input,
            .login-settings__card textarea {
                width: 100%;
                border-radius: 10px;
                border: 1px solid rgba(15, 23, 42, 0.12);
                padding: 0.7rem 0.8rem;
            }

            .login-settings__card textarea {
                resize: vertical;
            }

            .login-settings__errors,
            .login-settings__status {
                border-radius: 12px;
                padding: 0.85rem 1rem;
                border: 1px solid rgba(15, 23, 42, 0.12);
            }

            .login-settings__errors {
                background: #fff4f4;
                color: #b91c1c;
                border-color: rgba(185, 28, 28, 0.25);
            }

            .login-settings__errors ul {
                margin: 0.35rem 0 0;
            }

            .login-settings__status {
                background: #f0fdf4;
                color: #15803d;
                border-color: rgba(21, 128, 61, 0.25);
            }

            .login-settings__footer {
                display: flex;
                align-items: center;
                gap: 0.85rem;
            }

            .login-settings__preview {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0.6rem 1rem;
                border-radius: 10px;
                border: 1px solid rgba(15, 23, 42, 0.12);
                text-decoration: none;
            }

            @media (max-width: 640px) {
                .login-settings__header {
                    flex-direction: column;
                }

                .login-settings__footer {
                    flex-direction: column;
                    align-items: flex-start;
                }
            }
        </style>
    @endpush
@endsection
