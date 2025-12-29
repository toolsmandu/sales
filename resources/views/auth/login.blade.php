@extends('layouts.app')

@php
    $isHome = $isHomeLogin ?? false;
@endphp

@push('styles')
    @if ($isHome)
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
        <style>
            :root {
                --login-primary: #5aae54ff;
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
                flex-direction: column;
                align-items: stretch;
                justify-content: center;
                gap: 1.25rem;
            }

            .login-page--home {
                background: #092651;
                color: #ffffff;
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
                width: 100%;
                margin: 0 auto;
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
                gap: clamp(2rem, 5vw, 3.5rem);
                align-items: center;
            }

            .login-shell--center {
                grid-template-columns: minmax(320px, 520px);
                justify-content: center;
            }

            .login-hero {
                display: grid;
                gap: 1.25rem;
            }

            .login-page--home .login-hero,
            .login-page--home .login-heading,
            .login-page--home .login-lead,
            .login-page--home .login-perks li,
            .login-page--home .login-brand {
                color: #ffffff;
            }

            .login-page--home .login-perks li {
                border-color: rgba(255, 255, 255, 0.3);
                background: rgba(255, 255, 255, 0.08);
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

            .login-page--home .login-brand__accent {
                color: #ffffff;
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

            .login-radio-grid {
                display: grid;
                gap: 0.5rem;
            }

            .login-radio {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.65rem 0.85rem;
                border-radius: 12px;
                border: 1px solid var(--login-border);
                background: rgba(255, 255, 255, 0.6);
                cursor: pointer;
                transition: border-color 0.15s ease, box-shadow 0.15s ease;
                position: relative;
            }

            .login-radio input[type="radio"] {
                appearance: none;
                width: 1.1rem;
                height: 1.1rem;
                border: 2px solid var(--login-border);
                border-radius: 6px;
                display: inline-grid;
                place-content: center;
                background: #fff;
                transition: border-color 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;
            }

            .login-radio input[type="radio"]::after {
                content: '✓';
                color: #fff;
                font-size: 0.8rem;
                font-weight: 700;
                transform: scale(0.5);
                transition: transform 0.15s ease, opacity 0.15s ease;
                opacity: 0;
            }

            .login-radio input[type="radio"]:checked {
                border-color: var(--login-primary);
                background: linear-gradient(135deg, #2f63f6, #4154ff);
                box-shadow: 0 6px 16px rgba(47, 99, 246, 0.15);
            }

            .login-radio input[type="radio"]:checked::after {
                opacity: 1;
                transform: scale(1);
            }

            .login-radio:hover,
            .login-radio:focus-within {
                border-color: rgba(47, 99, 246, 0.45);
                box-shadow: 0 10px 24px rgba(47, 99, 246, 0.08);
            }

            .login-page--home .login-radio {
                background: rgba(255, 255, 255, 0.08);
                border-color: rgba(255, 255, 255, 0.35);
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

            .login-page--home .login-card {
                background: rgba(255, 255, 255, 0.08);
                border-color: rgba(255, 255, 255, 0.2);
                color: #ffffff;
                box-shadow: 0 25px 50px rgba(0, 0, 0, 0.35);
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

            .login-page--home .login-card__title {
                color: #ffffff;
            }

            .login-card__subtitle {
                margin: 0;
                color: var(--login-muted);
            }

            .login-page--home .login-card__subtitle,
            .login-page--home .login-status {
                color: #e5ecff;
            }

            .login-form {
                display: grid;
                gap: 1rem;
                margin-top: 1.2rem;
            }

            .track-otp-row {
                display: grid;
                gap: 0.75rem;
                grid-template-columns: 2fr auto;
                align-items: end;
                margin-top: 1rem;
            }

            .otp-actions {
                display: flex;
                gap: 0.6rem;
                align-items: center;
                flex-wrap: wrap;
                margin-top: 0.35rem;
            }

            .login-status {
                margin-top: 0.9rem;
                border-radius: 12px;
                padding: 0.85rem 1rem;
                border: 1px solid var(--login-border);
                color: var(--login-ink);
            }

            .login-status--info {
                background: #63c58bff;
                border-color: rgba(47, 99, 246, 0.25);
            }

            .login-status--success {
                background: #63c58bff;
                border-color: rgba(21, 128, 61, 0.25);
                color: #166534;
            }

            .tracking-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 1.2rem;
                font-size: 0.95rem;
            }

            .login-page--home .tracking-table {
                color: #ffffff;
            }

            .tracking-table th,
            .tracking-table td {
                text-align: center;
                padding: 0.65rem 0.5rem;
                border-bottom: 1px solid var(--login-border);
            }

            .login-page--home .tracking-table th,
            .login-page--home .tracking-table td {
                border-color: rgba(255, 255, 255, 0.25);
            }

            .tracking-table th {
                color: var(--login-muted);
                font-weight: 700;
            }

            .login-page--home .tracking-table th {
                color: #000000;
            }

            .tracking-table tbody tr:hover {
                background: rgba(47, 99, 246, 0.04);
            }

            .login-page--home .tracking-table tbody tr:hover {
                background: rgba(255, 255, 255, 0.08);
            }
            .tracking-orders {
                width: 100%;
                max-width: 1280px;
                margin: 0.75rem auto 0;
                padding: 0 0.5rem 1.5rem;
            }

            .login-field {
                display: grid;
                gap: 0.35rem;
            }

            .login-field label {
                font-weight: 600;
                color: var(--login-ink);
            }

            .login-page--home .login-field label {
                color: #ffffff;
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

            .login-page--home .login-field input {
                background: rgba(255, 255, 255, 0.1);
                border-color: rgba(255, 255, 255, 0.25);
                color: #ffffff;
            }

            .login-page--home .login-field input::placeholder {
                color: rgba(255, 255, 255, 0.7);
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
                    justify-items: center;
                    text-align: center;
                }

                .login-heading {
                    font-size: clamp(1.9rem, 8vw, 2.4rem);
                }

                .login-hero {
                    order: 1;
                    text-align: center;
                }

                .login-card {
                    order: 2;
                }

                .login-perks {
                    justify-content: center;
                }
            }
        </style>
    @else
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
    @endif
@endpush

@section('content')
    @php
        $registrationEnabled = \App\Models\SiteSetting::bool('registration_enabled', true) && Route::has('register');
        $isLoginPreview = $isLoginPreview ?? false;
        $loginContent = \App\Support\LoginContent::current();
        $logoUrl = null;
        if ($isHome && file_exists(public_path('logo.png'))) {
            $logoUrl = asset('logo.png');
        } elseif (!empty($loginContent['logo_path'])) {
            $logoUrl = Storage::disk('public')->url($loginContent['logo_path']);
        }
        if (!$logoUrl && file_exists(public_path('logo.png'))) {
            $logoUrl = asset('logo.png');
        }
        $perks = array_values(array_filter($loginContent['perks'] ?? []));
        $isLoginRoute = request()->routeIs('login');
        $trackingState = $trackingState ?? [];
        $trackingOrders = $trackingOrders ?? collect();
    @endphp

    @if ($isHome)
        <section class="login-page {{ $isHome ? 'login-page--home' : '' }}">
            <div class="login-ambient" aria-hidden="true"></div>

            <div class="login-shell {{ $isLoginRoute ? 'login-shell--center' : '' }}">
                @unless ($isLoginRoute)
                    <div class="login-hero">
                        @if (!empty($logoUrl))
                            <div class="login-brand">
                                <img src="{{ $logoUrl }}" alt="{{ config('app.name', 'Toolsmandu') }} logo" style="max-height: 56px; width: auto;">
                            </div>
                        @else
                            <div class="login-brand">
                                <span>{{ strtoupper(config('app.name', 'Toolsmandu')) }}</span>
                                <span class="login-brand__accent">{{ $loginContent['brand_accent'] ?? '' }}</span>
                            </div>
                        @endif
                        <h1 class="login-heading">
                            {{ $loginContent['headline_prefix'] ?? '' }} <span class="text-accent">{{ $loginContent['headline_accent'] ?? '' }}</span> {{ $loginContent['headline_suffix'] ?? '' }}
                        </h1>
                        <p class="login-lead">
                            {!! $loginContent['lead'] ?? '' !!}
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
                @endunless

                <div class="login-card" id="login-form">
                    <h2 class="login-card__title">{{ $isHome ? 'Track your order' : ($loginContent['card_title'] ?? '') }}</h2>
                    @if ($isHome)
                        <p class="login-card__subtitle">You can view your all purchases made on Whatsapp.</p>
                    @elseif (! $isLoginRoute)
                        <p class="login-card__subtitle">
                            @if ($isLoginPreview)
                                Preview how your customers experience the login screen.
                            @else
                                {{ $loginContent['card_subtitle'] ?? '' }}
                            @endif
                        </p>
                    @endif

                    @if (! $isHome && $errors->any())
                        <article role="alert" class="login-error" style="background:#fff4f4;border:1px solid rgba(176,42,55,0.2);border-radius:12px;padding:0.75rem 1rem;margin-top:1rem;">
                            {{ __('auth.failed') }}
                        </article>
                    @endif

                    @if ($isHome)
                        <form method="POST" action="{{ route('home') }}" class="login-form" novalidate>
                            @csrf

                            <div class="login-field">
                                <label for="track-phone">Whatsapp number</label>
                                <input
                                    type="text"
                                    id="track-phone"
                                    name="phone"
                                    placeholder="Enter with country code:+97798XXXXXXXX"
                                    value="{{ $trackingState['phone_display'] ?? old('phone', '') }}"
                                    required
                                >
                                @error('phone')
                                    <span class="login-error" role="alert">{{ $message }}</span>
                                @enderror
                            </div>

                            @if (($trackingState['status'] ?? '') !== 'otp_sent')
                                <button type="submit" class="login-btn login-btn--primary login-submit">Track Order</button>
                                @if (($trackingState['status'] ?? '') === '')
                                    <p style="margin:0.15rem 0 0;font-size:0.9rem;color:var(--login-muted);text-align:center;">
                                        <a href="{{ route('login') }}" style="color:inherit;text-decoration:underline;">Login as Admin</a>
                                    </p>
                                @endif
                            @elseif (($trackingState['status'] ?? '') !== 'verified' && ($trackingState['status'] ?? '') !== 'choose_email')
                                <article class="login-status login-status--info" style="margin-top:0;">A 6 digit OTP sent to your email: {{ $trackingState['masked_email'] ?? '' }}</article>
                            @endif
                        </form>

                        @if (($trackingState['status'] ?? '') === 'choose_email')
                            <form method="POST" action="{{ route('home') }}" class="login-form" novalidate>
                                @csrf
                                <input type="hidden" name="phone" value="{{ $trackingState['phone_display'] ?? '' }}">
                                <div class="login-field">
                                    <label>Select an email to receive the code</label>
                                    <div class="login-radio-grid">
                                        @foreach (($trackingState['email_options'] ?? []) as $index => $option)
                                            <label class="login-radio">
                                                <input
                                                    type="radio"
                                                    name="email_choice"
                                                    value="{{ $option['value'] ?? '' }}"
                                                    @checked($index === 0)
                                                    required
                                                >
                                                <span>{{ $option['label'] ?? $option['value'] ?? '' }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    @error('email_choice')
                                        <span class="login-error" role="alert">{{ $message }}</span>
                                    @enderror
                                </div>
                                <button type="submit" class="login-btn login-btn--primary login-submit">Send Code</button>
                            </form>
                        @endif

                        @if (in_array($trackingState['status'] ?? '', ['otp_sent', 'verified'], true) && ($trackingState['status'] ?? '') !== 'verified')
                            <form method="POST" action="{{ route('home') }}" class="track-otp-row" novalidate>
                                @csrf
                                <input type="hidden" name="phone" value="{{ $trackingState['phone_display'] ?? '' }}">
                                <input type="hidden" name="email_choice" value="{{ $trackingState['selected_email'] ?? '' }}">
                                <div class="login-field" style="margin:0;">
                                    <label for="otp">Enter 6 digit OTP</label>
                                    <input
                                        type="text"
                                        id="otp"
                                        name="otp"
                                        value=""
                                        inputmode="numeric"
                                        pattern="\\d{6}"
                                        maxlength="6"
                                        required
                                    >
                                    @error('otp')
                                        <span class="login-error" role="alert">{{ $message }}</span>
                                    @enderror
                                </div>
                                <button type="submit" class="login-btn login-btn--primary login-submit">Verify</button>
                            </form>
                            <div class="otp-actions">
                                <form method="POST" action="{{ route('home') }}">
                                    @csrf
                                    <input type="hidden" name="phone" value="{{ $trackingState['phone_display'] ?? '' }}">
                                    <input type="hidden" name="email_choice" value="{{ $trackingState['selected_email'] ?? '' }}">
                                    <button type="submit" class="login-btn login-btn--ghost login-submit" style="width:auto;padding:0.45rem 0.8rem;">Resend OTP</button>
                                </form>
                                <form method="POST" action="{{ route('home') }}">
                                    @csrf
                                    <input type="hidden" name="reset" value="1">
                                    <button type="submit" class="login-btn login-btn--ghost login-submit" style="width:auto;padding:0.45rem 0.8rem;color:#b91c1c;border-color:rgba(185,28,28,0.4);">Change Number</button>
                                </form>
                            </div>
                        @elseif (($trackingState['status'] ?? '') === 'verified')
                            <article class="login-status login-status--success" style="margin-top:1rem;">Your purchases are listed below.</article>
                        @endif

                        @if ($trackingOrders->count() === 0 && ($trackingState['status'] ?? '') === 'verified')
                            <article class="login-status login-status--info">No purchases found for that phone number.</article>
                        @endif

                    @else
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
                                @unless ($isLoginRoute)
                                    <span style="color: var(--login-muted);">Secure login · Encrypted</span>
                                @endunless
                            </div>

                            <button type="submit" class="login-btn login-btn--primary login-submit">Log in</button>
                        </form>
                    @endif

                </div>
            </div>
            @if ($isHome && $trackingOrders->count() > 0)
                <div class="tracking-orders">
                    <h3 style="color:#fff;">Your Orders:</h3>
                    <table class="tracking-table" aria-live="polite">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Purchase Date</th>
                                <th>Product</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($trackingOrders as $order)
                                <tr>
                                    <td>{{ $order->serial_number ?? ('#'.$order->id) }}</td>
                                    <td>{{ optional($order->purchase_date)->format('Y-m-d') }}</td>
                                    <td>{{ $order->product_name }}</td>
                                    <td>{{ $order->sales_amount ? number_format((float) $order->sales_amount, 2) : '—' }}</td>
                                    <td style="text-transform: capitalize;">{{ $order->status ?? 'pending' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    @else
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
                    @else
                        Keep your credentials safe. You can change them in your profile after signing in.
                    @endif
                </div>
            </div>
        </section>
    @endif
@endsection
