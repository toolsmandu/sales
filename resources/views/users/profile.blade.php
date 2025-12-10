@extends('layouts.app')

@push('styles')
    @include('partials.dashboard-styles')
    <style>
        .orders-layout-panel {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 0.6rem;
            padding: 0.85rem;
            margin-top: 0.35rem;
            border: 1px dashed rgba(59, 130, 246, 0.35);
            border-radius: 0.85rem;
            background: rgba(59, 130, 246, 0.05);
        }

        .orders-layout-panel label {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
            font-weight: 600;
            font-size: 0.92rem;
        }

        .orders-layout-panel input[type="range"] {
            accent-color: #2563eb;
        }

        .orders-layout-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            justify-content: flex-end;
        }
    </style>
@endpush

@section('content')
    <div class="dashboard-grid">
        @include('partials.dashboard-sidebar')

        <section class="dashboard-content stack">
            <section class="card stack">
                <h2>My Profile</h2>

                <form method="POST" action="{{ route('dashboard.profile.update') }}" class="form-grid form-grid--compact">
                    @csrf
                    @method('PUT')

                    <label for="profile-name">
                        Name
                        <input
                            type="text"
                            id="profile-name"
                            name="name"
                            value="{{ old('name', $user->name) }}"
                            required
                            autocomplete="name"
                        >
                    </label>
                    @error('name')
                        <small role="alert">{{ $message }}</small>
                    @enderror

                    <label for="profile-email">
                        Email
                        <input
                            type="email"
                            id="profile-email"
                            name="email"
                            value="{{ old('email', $user->email) }}"
                            required
                            autocomplete="email"
                        >
                    </label>
                    @error('email')
                        <small role="alert">{{ $message }}</small>
                    @enderror

                    <div class="form-actions">
                        <button type="submit">Update Profile</button>
                    </div>
                </form>
            </section>

            <section class="card stack">
                <div>
                    <h2>Order Form Layout</h2>
                    <p class="helper-text" style="margin: 0;">Adjust how the Add Order form columns render. Changes save to your browser and apply on the Add Order page.</p>
                </div>
                <div class="orders-layout-panel" data-orders-layout-panel>
                    <label>
                        Purchase Date Width (%)
                        <input type="range" min="20" max="60" value="33" data-layout-input data-row="primary" data-col="1">
                    </label>
                    <label>
                        Phone Number Width (%)
                        <input type="range" min="20" max="60" value="33" data-layout-input data-row="primary" data-col="2">
                    </label>
                    <label>
                        Email Width (%)
                        <input type="range" min="20" max="60" value="33" data-layout-input data-row="primary" data-col="3">
                    </label>
                    <label>
                        Row Gap (px)
                        <input type="range" min="0" max="24" value="0" data-layout-input data-row="primary" data-gap>
                    </label>
                    <label>
                        Product Width (%)
                        <input type="range" min="20" max="70" value="45" data-layout-input data-row="secondary" data-col="1">
                    </label>
                    <label>
                        Amount Width (%)
                        <input type="range" min="15" max="50" value="25" data-layout-input data-row="secondary" data-col="2">
                    </label>
                    <label>
                        Submit Width (%)
                        <input type="range" min="15" max="50" value="30" data-layout-input data-row="secondary" data-col="3">
                    </label>
                    <label>
                        Second Row Gap (px)
                        <input type="range" min="0" max="24" value="8" data-layout-input data-row="secondary" data-gap>
                    </label>
                </div>
                <div class="orders-layout-actions">
                    <button type="button" class="button" data-layout-reset>Reset to default</button>
                    <span class="helper-text" data-layout-status style="margin: 0;">Saved.</span>
                </div>
            </section>

            <section class="card stack">
                <h2>Change Password</h2>

                <form method="POST" action="{{ route('dashboard.profile.password.update') }}" class="form-grid form-grid--compact">
                    @csrf
                    @method('PUT')

                    <label for="profile-password">
                        New password
                        <input
                            type="password"
                            id="profile-password"
                            name="password"
                            required
                            autocomplete="new-password"
                        >
                    </label>
                    @error('password')
                        <small role="alert">{{ $message }}</small>
                    @enderror

                    <label for="profile-password-confirmation">
                        Confirm Password
                        <input
                            type="password"
                            id="profile-password-confirmation"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                        >
                    </label>
                    @error('password_confirmation')
                        <small role="alert">{{ $message }}</small>
                    @enderror

                    <div class="form-actions">
                        <button type="submit" class="button">Update Password</button>
                    </div>
                </form>

            </section>

            @if ($user?->role === 'employee')
                <section class="card stack">
                    <h2>Work Schedule</h2>

                    @php
                        $schedule = is_array($workSchedule ?? null) ? $workSchedule : [];
                        $headerRow = $schedule[0] ?? [];
                        $bodyRows = array_slice($schedule, 1);
                    @endphp

                    @if (empty(array_filter($schedule)))
                        <p class="helper-text">Work schedule is not set yet.</p>
                    @else
                        <div class="table-wrapper">
                            <table class="sales-table">
                                @if (!empty($headerRow))
                                    <thead>
                                        <tr>
                                            @foreach ($headerRow as $cell)
                                                <th>{{ $cell !== '' ? $cell : '—' }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                @endif
                                <tbody>
                                    @forelse ($bodyRows as $row)
                                        <tr>
                                            @foreach ($row as $cell)
                                                <td>{{ $cell !== '' ? $cell : '—' }}</td>
                                            @endforeach
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="helper-text">No schedule rows available.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <h2 style="margin-bottom: 0.35rem;">Rules</h2>
                    @if (!empty($workScheduleRules))
                        <div class="stack" style="margin: 0;">
                            @foreach ($workScheduleRules as $rule)
                                <h3 class="helper-text" style="margin: 0;">{{ $loop->iteration }}. {{ $rule }}</h3>
                            @endforeach
                        </div>
                    @else
                        <p class="helper-text" style="margin: 0;">No rules have been provided.</p>
                    @endif
                </section>
            @endif
        </section>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const panel = document.querySelector('[data-orders-layout-panel]');
    if (!panel) return;

    const inputs = panel.querySelectorAll('[data-layout-input]');
    const resetButton = document.querySelector('[data-layout-reset]');
    const status = document.querySelector('[data-layout-status]');
    const layoutStorageKey = 'ordersLayoutConfig';

    const defaultLayout = {
        primary: { col1: 33, col2: 33, col3: 33, gap: 0 },
        secondary: { col1: 45, col2: 25, col3: 30, gap: 8 },
    };

    const loadLayoutConfig = () => {
        try {
            const raw = localStorage.getItem(layoutStorageKey);
            if (!raw) return { ...defaultLayout };
            const parsed = JSON.parse(raw);
            return {
                primary: { ...defaultLayout.primary, ...(parsed.primary || {}) },
                secondary: { ...defaultLayout.secondary, ...(parsed.secondary || {}) },
            };
        } catch (_err) {
            return { ...defaultLayout };
        }
    };

    const persistLayoutConfig = (config) => {
        try {
            localStorage.setItem(layoutStorageKey, JSON.stringify(config));
        } catch (_err) {
            /* ignore */
        }
    };

    const showStatus = (message) => {
        if (!status) return;
        status.textContent = message;
        status.style.opacity = '1';
        window.setTimeout(() => {
            status.style.opacity = '0.8';
        }, 1500);
    };

    const hydrateInputs = () => {
        const config = loadLayoutConfig();
        inputs.forEach((input) => {
            const rowKey = input.dataset.row || 'primary';
            if (input.dataset.gap !== undefined) {
                input.value = config[rowKey]?.gap ?? 0;
            } else if (input.dataset.col) {
                input.value = config[rowKey]?.[`col${input.dataset.col}`] ?? 0;
            }
        });
    };

    const saveFromInputs = () => {
        const config = loadLayoutConfig();
        inputs.forEach((input) => {
            const rowKey = input.dataset.row || 'primary';
            const parsed = parseFloat(input.value || '0');
            if (input.dataset.gap !== undefined) {
                config[rowKey].gap = parsed;
            } else if (input.dataset.col) {
                config[rowKey][`col${input.dataset.col}`] = parsed;
            }
        });
        persistLayoutConfig(config);
        showStatus('Saved');
    };

    hydrateInputs();
    saveFromInputs();

    inputs.forEach((input) => {
        input.addEventListener('input', saveFromInputs);
        input.addEventListener('change', saveFromInputs);
    });

    if (resetButton) {
        resetButton.addEventListener('click', () => {
            persistLayoutConfig(defaultLayout);
            hydrateInputs();
            saveFromInputs();
            showStatus('Reset to default');
        });
    }
});
</script>
@endpush
