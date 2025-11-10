<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Toolsmandu') }}</title>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
        @endif

        @php
            $authUser = \Illuminate\Support\Facades\Auth::user();
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
                margin: 0;
                padding: 0 1.5rem;
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
        </style>

        @stack('styles')
    </head>
    <body>
        <header class="app-header">
            <nav>
                <ul>
                    <li>
                        <strong>{{ config('app.name', 'Toolsmandu') }}</strong>
                    </li>
                </ul>
                <ul>
                    @auth
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
                                        <span class="profile-menu__meta-name">{{ $authUser->name }}</span>
                                        <span class="profile-menu__meta-email">{{ $authUser->email }}</span>
                                    </div>
                                </div>
                                <div class="profile-menu__divider" role="none"></div>
                                <a class="profile-menu__item" href="{{ route('dashboard.profile.edit') }}" role="menuitem">
                                    <span class="profile-menu__icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 12c2.761 0 5-2.239 5-5S14.761 2 12 2 7 4.239 7 7s2.239 5 5 5z"/>
                                            <path d="M4 22a8 8 0 0 1 16 0"/>
                                        </svg>
                                    </span>
                                    <span class="profile-menu__label">Profile Settings</span>
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
                        <li><a href="{{ route('register') }}">Register</a></li>
                    @endauth
                </ul>
            </nav>
        </header>

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
            });
        </script>

        @stack('scripts')
    </body>
</html>
