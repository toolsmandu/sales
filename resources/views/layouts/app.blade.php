<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="icon" type="image/x-icon" href="{{ asset('fav.ico') }}">

        <title>{{ config('app.name', 'Toolsmandu Order Tracking') }}</title>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
        @endif
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

        @php
            $authUser = \Illuminate\Support\Facades\Auth::user();
            $registrationEnabled = \App\Models\SiteSetting::bool('registration_enabled', true);
            $hideHeader = request()->routeIs('login') || request()->routeIs('home') || ($isLoginPreview ?? false);
            $activeAttendance = null;
            if ($authUser) {
                $activeAttendance = \App\Models\AttendanceLog::where('user_id', $authUser->id)
                    ->whereNull('ended_at')
                    ->latest('started_at')
                    ->first();
            }
        @endphp

        <style>
            :root {
                font-size: 15px;
            }

            body {
                font-size: 0.95rem;
                line-height: 1.6;
            }

            h1 {
                font-size: 1.9rem;
            }

            h2 {
                font-size: 1.45rem;
            }

            h3 {
                font-size: 1.2rem;
            }

            .app-header,
            .app-main {
                width: 100%;
                max-width: 100%;
      
                box-sizing: border-box;
            }

            .app-header nav {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1.5rem;
                flex-wrap: wrap;
            }

            .app-header nav ul {
                display: inline-flex;
                align-items: center;
                gap: 1rem;
                margin: 0;
                padding: 0;
                list-style: none;
            }

            .app-header nav ul li {
                display: inline-flex;
                align-items: center;
                gap: 0.6rem;
            }

            .app-logo {
                display: inline-block;
                height: 38px;
                width: auto;
            }

            .profile-menu {
                position: relative;
                display: flex;
                align-items: center;
            }

            .profile-menu__trigger {
                display: flex;
                align-items: center;
                gap: 0.65rem;
                border: none;
                background: transparent;
                color: inherit;
                padding: 0.4rem 0.6rem;
                border-radius: 999px;
                cursor: pointer;
                transition: background-color 0.2s ease;
            }

            .profile-menu__trigger:focus-visible,
            .profile-menu__trigger:hover {
                background-color: rgba(0, 0, 0, 0.05);
            }

            [data-profile-open="true"] .profile-menu__trigger {
                background-color: rgba(0, 0, 0, 0.06);
            }

            .profile-menu__avatar {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 2rem;
                height: 2rem;
                border-radius: 50%;
                background-color: var(--primary, #0d6efd);
                color: #fff;
                font-weight: 600;
                text-transform: uppercase;
                font-size: 0.95rem;
            }

            .profile-menu__name {
                font-weight: 600;
            }

            .profile-menu__chevron {
                width: 1rem;
                height: 1rem;
            }

            .profile-menu__panel {
                position: absolute;
                right: 0;
                top: calc(100% + 0.4rem);
                min-width: 11rem;
                background: #fff;
                border-radius: 0.6rem;
                box-shadow: 0 0.6rem 1.2rem rgba(18, 38, 63, 0.12);
                border: 1px solid rgba(60, 72, 88, 0.12);
                z-index: 10;
                padding: 0.55rem 0.55rem 0.65rem;
                display: none;
                flex-direction: column;
                gap: 0.4rem;
            }

            [data-profile-open="true"] .profile-menu__panel {
                display: flex;
            }

            .profile-menu__item {
                display: flex;
                align-items: center;
                gap: 0.65rem;
                width: 100%;
                padding: 0.55rem 0.6rem;
                border: none;
                background: none;
                color: inherit;
                text-align: left;
                border-radius: 0.6rem;
                text-decoration: none;
                font-size: 0.95rem;
                font-weight: 500;
                cursor: pointer;
                transition: background-color 0.2s ease, color 0.2s ease;
            }

            .profile-menu__item:hover,
            .profile-menu__item:focus-visible {
                background-color: rgba(13, 110, 253, 0.12);
                outline: none;
            }

            .profile-menu__setting {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.35rem;
            }

            .profile-menu__setting-options {
                display: flex;
                gap: 0.6rem;
                width: 100%;
            }

            .profile-menu__setting-options label {
                display: inline-flex;
                align-items: center;
                gap: 0.25rem;
                font-size: 0.85rem;
                font-weight: 500;
            }

            .profile-menu__item--danger {
                color: #b02a37;
            }

            nav ul {
                align-items: center;
            }

            .profile-menu__meta {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                padding: 0.6rem 0.6rem 0.4rem;
            }

            .profile-menu__meta-avatar {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 2.6rem;
                height: 2.6rem;
                border-radius: 50%;
                background: linear-gradient(135deg, rgba(13, 110, 253, 0.92), rgba(99, 179, 237, 0.92));
                color: #fff;
                font-size: 1.2rem;
                font-weight: 600;
                text-transform: uppercase;
            }

            .profile-menu__meta-info {
                display: flex;
                flex-direction: column;
                gap: 0.2rem;
            }

            .profile-menu__meta-name {
                font-weight: 600;
                font-size: 1rem;
                color: #1a1a1a;
            }

            .profile-menu__meta-email {
                font-size: 0.85rem;
                color: #6c757d;
                word-break: break-all;
            }

            .profile-menu__divider {
                height: 1px;
                background: linear-gradient(to right, rgba(0, 0, 0, 0.08), rgba(0, 0, 0, 0.02));
                margin: 0.1rem 0;
            }

            .profile-menu__icon {
                width: 1.2rem;
                height: 1.2rem;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                color: #0d6efd;
            }

            .profile-menu__item--danger .profile-menu__icon {
                color: #b02a37;
            }

            .profile-menu__label {
                flex: 1;
            }

            .header-attendance-actions {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                margin-right: 1rem;
                position: relative;
                top: 8px;
            }

                        .header-attendance-indicator {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                margin-right: 1rem;
                position: relative;
                top: -8px;
            }

            .header-attendance-actions form {
                display: inline-flex;
            }

            .header-attendance-actions button {
                padding: 0.35rem 0.85rem;
                border-radius: 999px;
                border: 1px solid rgba(79, 70, 229, 0.35);
                background: rgba(79, 70, 229, 0.12);
                color: #312e81;
                font-weight: 600;
                cursor: pointer;
                transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease;
            }

            .header-attendance-actions button:hover,
            .header-attendance-actions button:focus-visible {
                background: rgba(79, 70, 229, 0.2);
                color: #1e1b4b;
                transform: translateY(-1px);
            }

            .header-attendance-actions button:disabled {
                opacity: 0.45;
                cursor: not-allowed;
                transform: none;
            }

            .header-attendance-indicator {
                font-size: 0.8rem;
                color: rgba(15, 23, 42, 0.65);
            }

            .header-notifications {
                position: relative;
                margin-right: 1rem;
            }

            .header-notifications__trigger {
                width: 2.5rem;
                height: 2.5rem;
                border-radius: 50%;
                border: none;
                background: rgba(15, 23, 42, 0.08);
                display: inline-flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                position: relative;
                transition: background 0.2s ease;
            }

            .header-notifications__trigger:hover,
            .header-notifications__trigger:focus-visible {
                background: rgba(15, 23, 42, 0.15);
                outline: none;
            }

            .header-notifications__trigger svg {
                width: 1.1rem;
                height: 1.1rem;
                color: #1d4ed8;
            }

            .header-notifications__badge {
                position: absolute;
                top: -0.25rem;
                right: -0.25rem;
                min-width: 1.2rem;
                padding: 0.1rem 0.35rem;
                border-radius: 999px;
                background: #ef4444;
                color: #fff;
                font-size: 0.7rem;
                font-weight: 700;
                text-align: center;
            }

            .header-notifications__panel {
                position: absolute;
                right: 0;
                top: calc(100% + 0.35rem);
                width: min(320px, 85vw);
                background: #fff;
                border-radius: 0.75rem;
                border: 1px solid rgba(15, 23, 42, 0.1);
                box-shadow: 0 20px 40px rgba(15, 23, 42, 0.15);
                padding: 0.85rem;
                display: none;
                flex-direction: column;
                gap: 0.75rem;
                z-index: 35;
            }

            .header-notifications[data-notification-open="true"] .header-notifications__panel {
                display: flex;
            }

            .header-notifications__item {
                border: 1px solid rgba(148, 163, 184, 0.35);
                border-radius: 0.75rem;
                padding: 0.65rem 0.85rem;
                background: linear-gradient(135deg, rgba(248, 250, 252, 0.95), rgba(224, 231, 255, 0.4));
                display: flex;
                gap: 0.75rem;
                align-items: flex-start;
                transition: transform 0.2s ease, border-color 0.2s ease;
                position: relative;
            }

            .header-notifications__dismiss {
                position: absolute;
                top: 0.35rem;
                right: 0.35rem;
                border: none;
                background: transparent;
                color: rgba(15, 23, 42, 0.35);
                cursor: pointer;
                font-size: 0.9rem;
                padding: 0.15rem;
                transition: color 0.15s ease;
            }

            .header-notifications__dismiss:hover,
            .header-notifications__dismiss:focus-visible {
                color: rgba(15, 23, 42, 0.65);
                outline: none;
            }

            .header-notifications__item:hover,
            .header-notifications__item:focus-visible {
                border-color: rgba(59, 130, 246, 0.5);
                transform: translateX(2px);
                outline: none;
            }

            .header-notifications__icon {
                width: 2.4rem;
                height: 2.4rem;
                border-radius: 0.75rem;
                background: rgba(59, 130, 246, 0.15);
                display: inline-flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }

            .header-notifications__icon svg {
                width: 1.2rem;
                height: 1.2rem;
                color: #1d4ed8;
            }

            .header-notifications__content {
                flex: 1;
                display: grid;
                gap: 0.25rem;
            }

            .header-notifications__title {
                margin: 0;
                font-weight: 600;
                font-size: 0.92rem;
                color: rgba(15, 23, 42, 0.95);
            }

            .header-notifications__message,
            .header-notifications__time {
                margin: 0;
                font-size: 0.85rem;
                color: rgba(15, 23, 42, 0.75);
                line-height: 1.5;
                white-space: pre-line;
            }

            .header-notifications__time {
                margin-top: 0.35rem;
                font-size: 0.8rem;
                color: rgba(15, 23, 42, 0.65);
            }

            .chatbot-fab {
                position: fixed;
                right: 1.25rem;
                bottom: 1.25rem;
                z-index: 50;
                width: 3.1rem;
                height: 3.1rem;
                border-radius: 50%;
                border: none;
                background: #0f172a;
                color: #fff;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 12px 30px rgba(15, 23, 42, 0.25);
                cursor: pointer;
                transition: transform 0.2s ease, box-shadow 0.2s ease;
            }

            .chatbot-fab:hover,
            .chatbot-fab:focus-visible {
                transform: translateY(-2px);
                box-shadow: 0 16px 32px rgba(15, 23, 42, 0.28);
                outline: none;
            }

            .chatbot-panel {
                position: fixed;
                right: 1.25rem;
                bottom: 5.1rem;
                width: min(420px, calc(100vw - 2.5rem));
                height: min(70vh, 640px);
                background: #fff;
                border-radius: 16px;
                box-shadow: 0 18px 44px rgba(15, 23, 42, 0.28);
                border: 1px solid rgba(15, 23, 42, 0.1);
                overflow: hidden;
                display: none;
                z-index: 50;
            }

            .chatbot-panel.is-open {
                display: block;
            }

            .chatbot-panel__frame {
                width: 100%;
                height: 100%;
                border: none;
            }

            @media (max-width: 768px) {
                .chatbot-panel {
                    right: 0.75rem;
                    left: 0.75rem;
                    width: auto;
                    bottom: 5rem;
                }
            }
        </style>

        @stack('styles')
    </head>
    <body>
        @unless ($hideHeader)
        <header class="app-header">
            <nav>
                <ul>
                    <li>
                        <img src="{{ asset('logo.png') }}" alt="{{ config('app.name', 'Toolsmandu') }}" class="app-logo">
                    </li>
                </ul>
                <ul>
                    @auth
                        @if (($authUser->role ?? null) !== 'admin')
                            <li class="header-attendance-actions">
                                <form method="POST" action="{{ route('user-logs.attendance.start') }}">
                                    @csrf
                                    <button type="submit" @disabled($activeAttendance)>Start Work</button>
                                </form>
                                @if ($activeAttendance)
                                    <span class="header-attendance-indicator">
                                        Active since {{ $activeAttendance->started_at->setTimezone('Asia/Kathmandu')->format('g:i A') }}
                                    </span>
                                @endif
                            </li>
                        @endif
                        @php
                            $notificationCount = $headerNotifications['count'] ?? 0;
                        @endphp
                        @if ($notificationCount > 0)
                            <li class="header-notifications" data-notification-open="false">
                                <button type="button" class="header-notifications__trigger" aria-haspopup="true" aria-expanded="false" aria-label="View notifications">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
                                        <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0 1 18 14.158V11a6.002 6.002 0 0 0-4-5.659V5a2 2 0 1 0-4 0v0.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h11z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M9 21c.863.641 1.862 1 3 1s2.137-.359 3-1" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                    <span class="header-notifications__badge">{{ $notificationCount }}</span>
                                </button>
                                <div class="header-notifications__panel" role="menu">
                                    @foreach (($headerNotifications['items'] ?? []) as $notification)
                                        @php
                                            $type = $notification['type'] ?? '';
                                            $isTaskNotification = in_array($type, ['today', 'overdue'], true);
                                            $iconSvg = match ($type) {
                                                'shift' => '<path d="M12 6v6l3 2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /><circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="1.8" fill="none" />',
                                                default => '<path d="M9 11l3 3L22 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /><path d="M21 11.5V18a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h11" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />',
                                            };
                                        @endphp
                                        <article
                                            class="header-notifications__item"
                                            data-notification-type="{{ $type }}"
                                            @if(!empty($notification['id'])) data-notification-id="{{ $notification['id'] }}" @endif
                                            @if(!empty($notification['employee_id'])) data-employee-id="{{ $notification['employee_id'] }}" @endif
                                            @if(!empty($notification['link']))
                                                data-notification-link="{{ $notification['link'] }}"
                                                role="button"
                                                tabindex="0"
                                            @endif
                                        >
                                            @unless($type === 'sales')
                                                <button type="button" class="header-notifications__dismiss" aria-label="Dismiss notification">
                                                    &times;
                                                </button>
                                            @endunless
                                            <div class="header-notifications__icon" aria-hidden="true">
                                                <svg viewBox="0 0 24 24" fill="none">{!! $iconSvg !!}</svg>
                                            </div>
                                            <div class="header-notifications__content">
                                                <p class="header-notifications__title">{{ $notification['title'] ?? 'Notification' }}</p>
                                                <p class="header-notifications__message">{{ $notification['message'] ?? '' }}</p>
                                                @if ($type === 'shift' && !empty($notification['current_time']))
                                                    <p class="header-notifications__time">Current time (+05:45 GMT): {{ $notification['current_time'] }}</p>
                                                @endif
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            </li>
                        @endif
                        <li class="profile-menu">
                            <button type="button" class="profile-menu__trigger" aria-haspopup="true" aria-expanded="false">
                                <span class="profile-menu__avatar">{{ strtoupper(substr($authUser->name ?? 'U', 0, 1)) }}</span>
                                <span class="profile-menu__name">{{ $authUser->name }}</span>
                                <svg class="profile-menu__chevron" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06l-4.24 4.24a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 .02-1.08z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <div class="profile-menu__panel" role="menu">
                                <div class="profile-menu__meta" role="none">
                                    <span class="profile-menu__meta-avatar">{{ strtoupper(substr($authUser->name ?? 'U', 0, 1)) }}</span>
                                    <div class="profile-menu__meta-info">
                                        <span class="profile-menu__meta-name">{{ $authUser->name }}</span>                                    </div>
                                </div>
                                <div class="profile-menu__divider" role="none"></div>
                                @if (($authUser->role ?? null) === 'admin')
                                    <form
                                        method="POST"
                                        action="{{ route('dashboard.settings.registration') }}"
                                        class="profile-menu__item profile-menu__setting"
                                        role="none"
                                    >
                                        @csrf
                                        <span class="profile-menu__label">User registration</span>
                                        <div class="profile-menu__setting-options">
                                            <label>
                                                <input
                                                    type="radio"
                                                    name="registration_enabled"
                                                    value="1"
                                                    @checked($registrationEnabled)
                                                    onchange="this.form.submit()"
                                                >
                                                Enable
                                            </label>
                                            <label>
                                                <input
                                                    type="radio"
                                                    name="registration_enabled"
                                                    value="0"
                                                    @checked(!$registrationEnabled)
                                                    onchange="this.form.submit()"
                                                >
                                                Disable
                                            </label>
                                        </div>
                                    </form>
                                @endif
                                <a class="profile-menu__item" href="{{ route('dashboard.profile.edit') }}" role="menuitem">
                                    <span class="profile-menu__icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 12c2.761 0 5-2.239 5-5S14.761 2 12 2 7 4.239 7 7s2.239 5 5 5z"/>
                                            <path d="M4 22a8 8 0 0 1 16 0"/>
                                        </svg>
                                    </span>
                                    <span class="profile-menu__label">Profile</span>
                                </a>
                                @if (($authUser->role ?? null) === 'admin')
                                    <a class="profile-menu__item" href="{{ route('dashboard.users.index') }}" role="menuitem">
                                        <span class="profile-menu__icon" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                                <circle cx="9" cy="7" r="4"/>
                                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                            </svg>
                                        </span>
                                        <span class="profile-menu__label">Edit Team</span>
                                </a>
                                @endif

                                @if (($authUser->role ?? null) === 'admin')
                                    <a class="profile-menu__item" href="{{ route('dashboard.login.customize') }}" role="menuitem">
                                        <span class="profile-menu__icon" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M12 20h9"/>
                                                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                                            </svg>
                                        </span>
                                        <span class="profile-menu__label">Customize Login</span>
                                    </a>
                                @endif

                                @if (($authUser->role ?? null) === 'admin' || ($isImpersonating ?? false))
                                    @if ($isImpersonating ?? false)
                                        <form method="POST" action="{{ route('dashboard.impersonate.stop') }}" class="profile-menu__item profile-menu__setting" role="none">
                                            @csrf
                                            <div class="profile-menu__setting-options" style="flex-direction: column; gap: 0.4rem;">
                                                <button type="submit" class="ghost-button">Return to {{ $impersonatorName ?? 'admin' }}</button>
                                            </div>
                                        </form>
                                    @elseif (($authUser->role ?? null) === 'admin')
                                        <form method="POST" action="{{ route('dashboard.impersonate.start') }}" class="profile-menu__item profile-menu__setting" role="none">
                                            @csrf
                                            <span class="profile-menu__label">Login as employee</span>
                                            <div class="profile-menu__setting-options" style="flex-direction: column; gap: 0.4rem;">
                                                <select name="employee_id" required>
                                                    <option value="">Select employee</option>
                                                    @foreach (($impersonationEmployees ?? collect()) as $employeeOption)
                                                        <option value="{{ $employeeOption->id }}">{{ $employeeOption->name }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="ghost-button ghost-button--slim">Login</button>
                                            </div>
                                        </form>
                                    @endif
                                @endif
                                <div class="profile-menu__divider" role="none"></div>
                                <form method="POST" action="{{ route('logout') }}" role="none">
                                    @csrf
                                    <button type="submit" class="profile-menu__item profile-menu__item--danger" role="menuitem">
                                        <span class="profile-menu__icon" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M9 21h6"/>
                                                <path d="M10 17h4"/>
                                                <path d="M3 7h18"/>
                                                <path d="M10 11h4"/>
                                                <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2l1-12"/>
                                                <path d="M9 7l1-3h4l1 3"/>
                                            </svg>
                                        </span>
                                        <span class="profile-menu__label">Sign out</span>
                                    </button>
                                </form>
                            </div>
                        </li>
                    @else
                        <li><a href="{{ route('login') }}">Log in</a></li>
                        @if ($registrationEnabled && Route::has('register'))
                            <li><a href="{{ route('register') }}">Register</a></li>
                        @endif
                    @endauth
                </ul>
            </nav>
        </header>
        @endunless

        @auth
            <button type="button" class="chatbot-fab" id="chatbot-fab" aria-label="Open chatbot" aria-expanded="false" aria-controls="chatbot-panel">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M352 0c0-17.7-14.3-32-32-32S288-17.7 288 0l0 64-96 0c-53 0-96 43-96 96l0 224c0 53 43 96 96 96l256 0c53 0 96-43 96-96l0-224c0-53-43-96-96-96l-96 0 0-64zM160 368c0-13.3 10.7-24 24-24l32 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-32 0c-13.3 0-24-10.7-24-24zm120 0c0-13.3 10.7-24 24-24l32 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-32 0c-13.3 0-24-10.7-24-24zm120 0c0-13.3 10.7-24 24-24l32 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-32 0c-13.3 0-24-10.7-24-24zM224 176a48 48 0 1 1 0 96 48 48 0 1 1 0-96zm144 48a48 48 0 1 1 96 0 48 48 0 1 1 -96 0zM64 224c0-17.7-14.3-32-32-32S0 206.3 0 224l0 96c0 17.7 14.3 32 32 32s32-14.3 32-32l0-96zm544-32c-17.7 0-32 14.3-32 32l0 96c0 17.7 14.3 32 32 32s32-14.3 32-32l0-96c0-17.7-14.3-32-32-32z"/></svg>
            </button>
            <section class="chatbot-panel" id="chatbot-panel" role="dialog" aria-label="Chatbot">
                <iframe class="chatbot-panel__frame" src="{{ url('/chatbot?embed=1') }}" title="Chatbot"></iframe>
            </section>
        @endauth

        <main class="app-main">
            @if (session('status'))
                <article role="alert">
                    {{ session('status') }}
                </article>
            @endif

            @yield('content')
        </main>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const profileMenu = document.querySelector('.profile-menu');
                if (!profileMenu) {
                    return;
                }

                const trigger = profileMenu.querySelector('.profile-menu__trigger');
                const panel = profileMenu.querySelector('.profile-menu__panel');

                if (!trigger || !panel) {
                    return;
                }

                let isOpen = false;

                const focusFirstMenuItem = () => {
                    const firstItem = panel.querySelector('[role="menuitem"]');
                    if (firstItem) {
                        firstItem.focus({ preventScroll: true });
                    }
                };

                const setOpenState = (open, { focusFirst = false } = {}) => {
                    if (isOpen === open) {
                        return;
                    }

                    isOpen = open;
                    profileMenu.dataset.profileOpen = open ? 'true' : 'false';
                    trigger.setAttribute('aria-expanded', open ? 'true' : 'false');

                    if (open && focusFirst) {
                        focusFirstMenuItem();
                    }
                };

                const openMenu = (options = {}) => setOpenState(true, options);
                const closeMenu = () => setOpenState(false);

                trigger.addEventListener('click', () => {
                    if (isOpen) {
                        closeMenu();
                    } else {
                        openMenu();
                    }
                });

                trigger.addEventListener('keydown', (event) => {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        openMenu({ focusFirst: true });
                    } else if (event.key === 'ArrowDown') {
                        event.preventDefault();
                        openMenu({ focusFirst: true });
                    } else if (event.key === 'Escape') {
                        event.preventDefault();
                        closeMenu();
                    }
                });

                document.addEventListener('click', (event) => {
                    if (!profileMenu.contains(event.target)) {
                        closeMenu();
                    }
                });

                document.addEventListener('focusin', (event) => {
                    if (!profileMenu.contains(event.target)) {
                        closeMenu();
                    }
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && isOpen) {
                        closeMenu();
                        trigger.focus({ preventScroll: true });
                    }
                });

                const notificationMenu = document.querySelector('.header-notifications');
                if (notificationMenu) {
                    const notificationTrigger = notificationMenu.querySelector('.header-notifications__trigger');
                    const notificationPanel = notificationMenu.querySelector('.header-notifications__panel');
                    if (notificationTrigger && notificationPanel) {
                        let notificationOpen = false;

                        const setNotificationOpen = (open) => {
                            notificationOpen = open;
                            notificationMenu.dataset.notificationOpen = open ? 'true' : 'false';
                            notificationTrigger.setAttribute('aria-expanded', open ? 'true' : 'false');
                        };

                        notificationTrigger.addEventListener('click', () => {
                            setNotificationOpen(!notificationOpen);
                        });

                        document.addEventListener('click', (event) => {
                            if (!notificationMenu.contains(event.target)) {
                                setNotificationOpen(false);
                            }
                        });

                    document.addEventListener('keydown', (event) => {
                        if (event.key === 'Escape' && notificationOpen) {
                            setNotificationOpen(false);
                            notificationTrigger.focus({ preventScroll: true });
                        }
                    });

                        notificationPanel.addEventListener('click', (event) => {
                            const dismissButton = event.target.closest('.header-notifications__dismiss');
                            if (dismissButton) {
                                const item = dismissButton.closest('.header-notifications__item');
                                if (item) {
                                    const notificationType = item.getAttribute('data-notification-type') || '';
                                    const notificationId = item.getAttribute('data-notification-id') || notificationType;
                                    const employeeId = item.getAttribute('data-employee-id') || '';
                                    item.remove();

                                    if (notificationType === 'employee_overdue' && employeeId) {
                                        fetch(@json(route('dashboard.notifications.overdue-snooze')), {
                                            method: 'POST',
                                            headers: {
                                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                                'Content-Type': 'application/json',
                                                'Accept': 'application/json',
                                            },
                                            credentials: 'same-origin',
                                            body: JSON.stringify({ employee_id: employeeId }),
                                        }).catch(() => {});
                                    } else if (notificationId) {
                                        fetch(@json(route('dashboard.notifications.hide')), {
                                            method: 'POST',
                                            headers: {
                                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                                'Content-Type': 'application/json',
                                                'Accept': 'application/json',
                                            },
                                            credentials: 'same-origin',
                                            body: JSON.stringify({ notification_id: notificationId }),
                                        }).catch(() => {});
                                    }
                                }
                                event.stopPropagation();
                                return;
                            }

                            const target = event.target.closest('[data-notification-link]');
                            if (target) {
                                const href = target.getAttribute('data-notification-link');
                                if (href) {
                                    window.location.href = href;
                                }
                            }
                        });

                        notificationPanel.addEventListener('keydown', (event) => {
                            if (event.key === 'Enter' || event.key === ' ') {
                                const target = event.target.closest('[data-notification-link]');
                                if (target) {
                                    event.preventDefault();
                                    const href = target.getAttribute('data-notification-link');
                                    if (href) {
                                        window.location.href = href;
                                    }
                                }
                            }
                        });
                    }
                }
            });
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const fab = document.getElementById('chatbot-fab');
                const panel = document.getElementById('chatbot-panel');
                if (!fab || !panel) {
                    return;
                }

                const togglePanel = (open) => {
                    panel.classList.toggle('is-open', open);
                    fab.setAttribute('aria-expanded', open ? 'true' : 'false');
                };

                fab.addEventListener('click', () => {
                    const isOpen = panel.classList.contains('is-open');
                    togglePanel(!isOpen);
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && panel.classList.contains('is-open')) {
                        togglePanel(false);
                    }
                });
            });
        </script>

        @stack('scripts')
    </body>
</html>
