@extends('layouts.app')

@push('styles')
    <style>
        :root {
            --auth-bg: linear-gradient(135deg, #0f172a 0%, #1f1b2e 55%, #132752 100%);
            --auth-card: rgba(255, 255, 255, 0.08);
            --auth-border: rgba(255, 255, 255, 0.12);
            --auth-text: #e2e8f0;
            --auth-muted: #94a3b8;
            --auth-accent: #5af0c4;
            --auth-danger: #fca5a5;
        }

        .auth-page {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: clamp(1.5rem, 4vw, 3rem);
            background: var(--auth-bg);
            position: relative;
            overflow: hidden;
            color: var(--auth-text);
        }

        .auth-blur {
            position: absolute;
            width: 60vmax;
            height: 60vmax;
            background: radial-gradient(circle, rgba(90, 240, 196, 0.14), transparent 45%);
            filter: blur(60px);
            opacity: 0.9;
            pointer-events: none;
        }

        .auth-blur--left {
            top: -20%;
            left: -25%;
        }

        .auth-blur--right {
            bottom: -25%;
            right: -20%;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.18), transparent 45%);
        }

        .auth-card {
            position: relative;
            width: min(520px, 100%);
            padding: clamp(1.75rem, 3vw, 2.5rem);
            border-radius: 24px;
            border: 1px solid var(--auth-border);
            background: var(--auth-card);
            backdrop-filter: blur(8px);
            box-shadow: 0 25px 65px rgba(0, 0, 0, 0.35);
        }

        .auth-brand {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            font-size: 1.4rem;
            color: var(--auth-text);
        }

        .auth-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.45rem 0.9rem;
            border-radius: 999px;
            border: 1px solid var(--auth-border);
            background: rgba(255, 255, 255, 0.06);
            color: var(--auth-muted);
            font-weight: 600;
            margin-top: 0.85rem;
        }

        .auth-title {
            margin: 1.1rem 0 0.35rem;
            font-size: clamp(1.85rem, 3vw, 2.3rem);
            color: var(--auth-text);
            letter-spacing: -0.02em;
            text-align: center;
        }

        .auth-subtitle {
            margin: 0;
            color: var(--auth-muted);
            line-height: 1.6;
        }

        .auth-form {
            margin-top: 1.35rem;
            display: grid;
            gap: 1rem;
        }

        .auth-field {
            display: grid;
            gap: 0.35rem;
        }

        .auth-field label {
            font-weight: 700;
            color: var(--auth-text);
        }

        .auth-input {
            width: 100%;
            padding: 0.85rem 0.95rem;
            border-radius: 12px;
            border: 1px solid var(--auth-border);
            background: rgba(255, 255, 255, 0.06);
            color: var(--auth-text);
            font-size: 1rem;
            transition: border-color 0.18s ease, box-shadow 0.18s ease;
        }

        .auth-input:focus {
            outline: none;
            border-color: rgba(90, 240, 196, 0.8);
            box-shadow: 0 0 0 4px rgba(90, 240, 196, 0.14);
        }

        .auth-remember {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            color: var(--auth-muted);
            font-size: 0.95rem;
        }

        .auth-checkbox {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            font-weight: 600;
            color: var(--auth-text);
        }

        .auth-submit {
            border: none;
            cursor: pointer;
            border-radius: 14px;
            padding: 0.95rem 1.1rem;
            background: linear-gradient(135deg, #10b981, #22d3ee);
            color: #0b1525;
            font-weight: 800;
            letter-spacing: 0.02em;
            font-size: 1rem;
            box-shadow: 0 16px 40px rgba(16, 185, 129, 0.3);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
            width: 100%;
        }

        .auth-submit:hover,
        .auth-submit:focus-visible {
            transform: translateY(-1px);
            box-shadow: 0 20px 50px rgba(16, 185, 129, 0.35);
            outline: none;
        }

        .auth-error {
            background: rgba(248, 113, 113, 0.12);
            border: 1px solid rgba(248, 113, 113, 0.35);
            color: var(--auth-danger);
            padding: 0.75rem 0.9rem;
            border-radius: 12px;
            font-weight: 600;
        }

        .auth-footer {
            margin-top: 0.85rem;
            color: var(--auth-muted);
            font-size: 0.95rem;
        }

        .auth-footer a {
            color: var(--auth-accent);
            font-weight: 700;
            text-decoration: none;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }
    </style>
@endpush

@section('content')
    @php
        $logoUrl = null;
        if (file_exists(public_path('logo.png'))) {
            $logoUrl = asset('logo.png');
        }
    @endphp

    <section class="auth-page">
        <div class="auth-blur auth-blur--left"></div>
        <div class="auth-blur auth-blur--right"></div>

        <div class="auth-card">
            <div class="auth-brand">
                @if ($logoUrl)
                @endif
            </div>
         
            <h1 class="auth-title">Welcome back</h1>
            @if ($errors->any())
                <div class="auth-error" role="alert">
                    {{ __('auth.failed') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="auth-form" novalidate>
                @csrf

                <div class="auth-field">
                    <label for="email">Email address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        required
                        class="auth-input"
                        autofocus
                    >
                </div>

                <div class="auth-field">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        autocomplete="current-password"
                        required
                        class="auth-input"
                    >
                </div>

                <div class="auth-remember">
                    <label for="remember" class="auth-checkbox">
                        <input type="checkbox" id="remember" name="remember">
                        Remember me
                    </label>
                </div>

                <button type="submit" class="auth-submit">Log in</button>
            </form>

            <div class="auth-footer">
                @if (Route::has('register'))
                    New here? <a href="{{ route('register') }}">Create an account</a>.
                @else
                    Keep your credentials safe. You can change them in your profile after signing in.
                @endif
            </div>
        </div>
    </section>
@endsection
