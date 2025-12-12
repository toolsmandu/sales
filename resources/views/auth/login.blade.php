@extends('layouts.app')

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --login-primary: #2f63f6;
            --login-primary-strong: #2940d3;
            --login-surface: #ffffff;
            --login-ink: #0f172a;
            --login-muted: #64748b;
            --login-border: rgba(15, 23, 42, 0.08);
            --login-pill: rgba(47, 99, 246, 0.12);
        }

        .login-page {
            position: relative;
            min-height: 100vh;
            padding: 3rem clamp(1rem, 3vw, 2.5rem) 3.5rem;
            background: radial-gradient(circle at 10% 20%, rgba(59, 130, 246, 0.08), transparent 40%),
                        radial-gradient(circle at 85% 10%, rgba(99, 102, 241, 0.08), transparent 35%),
                        #f6f8fb;
            overflow: hidden;
            font-family: 'Space Grotesk', 'Helvetica Neue', Arial, sans-serif;
            display: flex;
            align-items: center;
        }

        .login-ambient {
            position: absolute;
            inset: -40% -25% auto;
            height: 65%;
            background: radial-gradient(circle at 30% 30%, rgba(47, 99, 246, 0.16), transparent 45%),
                        radial-gradient(circle at 70% 50%, rgba(99, 102, 241, 0.14), transparent 40%);
            filter: blur(18px);
            z-index: 0;
        }

        .login-shell {
            position: relative;
            z-index: 1;
            max-width: 1280px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: clamp(2rem, 5vw, 3.5rem);
            align-items: center;
        }

        .login-hero {
            display: grid;
            gap: 1.25rem;
        }

        .login-brand {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            font-size: clamp(1.2rem, 2.4vw, 1.5rem);
            color: var(--login-ink);
        }

        .login-brand__accent {
            color: var(--login-primary);
        }

        .login-badge {
            display: inline-flex;
            align-items: center;
            justify-content: flex-start;
            padding: 0.55rem 1rem;
            background: #e9f0ff;
            color: var(--login-primary-strong);
            border-radius: 999px;
            font-weight: 600;
            font-size: 0.85rem;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            width: fit-content;
            box-shadow: 0 10px 30px rgba(47, 99, 246, 0.08);
        }

        .login-heading {
            font-size: clamp(2rem, 4vw, 3rem);
            line-height: 1.1;
            color: var(--login-ink);
            margin: 0.35rem 0 0.5rem;
            letter-spacing: -0.02em;
        }

        .login-heading .text-accent {
            color: var(--login-primary);
        }

        .login-heading .text-glow {
            color: #4b5ffd;
            text-shadow: 0 10px 30px rgba(47, 99, 246, 0.18);
        }

        .login-lead {
            font-size: 1.05rem;
            color: var(--login-muted);
            max-width: 640px;
            margin: 0;
        }

        .login-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 0.35rem;
        }

        .login-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border-radius: 12px;
            padding: 0.85rem 1.4rem;
            font-weight: 700;
            text-decoration: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        .login-btn--primary {
            background: linear-gradient(135deg, #2f63f6, #4154ff);
            color: #fff;
            box-shadow: 0 15px 35px rgba(47, 99, 246, 0.35);
        }

        .login-btn--ghost {
            background: rgba(255, 255, 255, 0.9);
            color: var(--login-ink);
            border: 1px solid var(--login-border);
        }

        .login-btn:hover,
        .login-btn:focus-visible {
            transform: translateY(-1px);
            box-shadow: 0 16px 36px rgba(47, 99, 246, 0.18);
            outline: none;
        }

        .login-perks {
            display: flex;
            flex-wrap: wrap;
            gap: 0.9rem 1.2rem;
            padding: 0;
            margin: 1rem 0 0;
            list-style: none;
            color: var(--login-muted);
            font-weight: 500;
        }

        .login-perks li {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.4rem 0.85rem;
            border: 1px solid var(--login-border);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.8);
        }

        .login-perks svg {
            width: 1rem;
            height: 1rem;
            color: var(--login-primary);
        }

        .login-card {
            position: relative;
            background: var(--login-surface);
            border-radius: 18px;
            border: 1px solid var(--login-border);
            padding: clamp(1.5rem, 4vw, 2.25rem);
            box-shadow: 0 25px 50px rgba(15, 23, 42, 0.12);
        }

        .login-card__chip {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: var(--login-pill);
            color: var(--login-primary-strong);
            border-radius: 999px;
            padding: 0.45rem 0.95rem;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .login-card__title {
            margin: 0.65rem 0 0.4rem;
            font-size: 1.4rem;
            color: var(--login-ink);
        }

        .login-card__subtitle {
            margin: 0;
            color: var(--login-muted);
        }

        .login-form {
            display: grid;
            gap: 1rem;
            margin-top: 1.2rem;
        }

        .login-field {
            display: grid;
            gap: 0.35rem;
        }

        .login-field label {
            font-weight: 600;
            color: var(--login-ink);
        }

        .login-field input {
            width: 100%;
            padding: 0.85rem 0.9rem;
            border-radius: 12px;
            border: 1px solid var(--login-border);
            background: #f9fbff;
            font-size: 1rem;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .login-field input:focus {
            outline: none;
            border-color: rgba(47, 99, 246, 0.6);
            box-shadow: 0 0 0 4px rgba(47, 99, 246, 0.12);
        }

        .login-error {
            color: #b02a37;
            font-size: 0.9rem;
        }

        .login-remember {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.6rem;
            font-size: 0.95rem;
            color: var(--login-muted);
        }

        .login-remember label {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            font-weight: 600;
            color: var(--login-ink);
        }

        .login-submit {
            border: none;
            cursor: pointer;
            width: 100%;
            font-size: 1rem;
        }

        .login-footer {
            margin-top: 1rem;
            color: var(--login-muted);
            font-size: 0.96rem;
        }

        @media (max-width: 860px) {
            .login-page {
                padding-top: 1.5rem;
                align-items: flex-start;
            }

            .login-shell {
                grid-template-columns: 1fr;
            }

            .login-heading {
                font-size: clamp(1.9rem, 8vw, 2.4rem);
            }

            .login-card {
                order: -1;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $registrationEnabled = \App\Models\SiteSetting::bool('registration_enabled', true) && Route::has('register');
        $isLoginPreview = $isLoginPreview ?? false;
        $loginContent = \App\Support\LoginContent::current();
        $perks = array_values(array_filter($loginContent['perks'] ?? []));
        $primaryCtaHref = $loginContent['cta_primary_link'] ?: ($registrationEnabled ? route('register') : '#login-form');
    @endphp

    <section class="login-page">
        <div class="login-ambient" aria-hidden="true"></div>

        <div class="login-shell">
            <div class="login-hero">
                <div class="login-brand">
                    <span>{{ strtoupper(config('app.name', 'Toolsmandu')) }}</span>
                    <span class="login-brand__accent">{{ $loginContent['brand_accent'] ?? '' }}</span>
                </div>
                <div class="login-badge">{{ $loginContent['badge'] ?? '' }}</div>
                <h1 class="login-heading">
                    {{ $loginContent['headline_prefix'] ?? '' }} <span class="text-accent">{{ $loginContent['headline_accent'] ?? '' }}</span> {{ $loginContent['headline_suffix'] ?? '' }}
                </h1>
                <p class="login-lead">
                    {{ $loginContent['lead'] ?? '' }}
                </p>
                @if (count($perks) > 0)
                    <ul class="login-perks">
                        @foreach ($perks as $perk)
                            <li>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ $perk }}
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="login-card" id="login-form">
                <h2 class="login-card__title">{{ $loginContent['card_title'] ?? '' }}</h2>
                <p class="login-card__subtitle">
                    @if ($isLoginPreview)
                        Preview how your customers experience the login screen.
                    @else
                        {{ $loginContent['card_subtitle'] ?? '' }}
                    @endif
                </p>

                @if ($errors->any())
                    <article role="alert" class="login-error" style="background:#fff4f4;border:1px solid rgba(176,42,55,0.2);border-radius:12px;padding:0.75rem 1rem;margin-top:1rem;">
                        {{ __('auth.failed') }}
                    </article>
                @endif

                <form method="POST" action="{{ route('login') }}" class="login-form" novalidate>
                    @csrf

                    <div class="login-field">
                        <label for="email">Email address</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            autocomplete="email"
                            required
                            autofocus
                        >
                        @error('email')
                            <span class="login-error" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="login-field">
                        <label for="password">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            autocomplete="current-password"
                            required
                        >
                        @error('password')
                            <span class="login-error" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="login-remember">
                        <label for="remember">
                            <input type="checkbox" id="remember" name="remember">
                            Remember me
                        </label>
                        <span style="color: var(--login-muted);">Secure login · Encrypted</span>
                    </div>

                    <button type="submit" class="login-btn login-btn--primary login-submit">Log in</button>
                </form>

                <p class="login-footer">
                    @if ($registrationEnabled)
                        Need an account? <a href="{{ route('register') }}" style="color: var(--login-primary); font-weight: 700;">Register now</a>.
                    @else
                        New registrations are disabled. Contact an administrator to create an account.
                    @endif
                </p>
            </div>
        </div>
    </section>
@endsection
