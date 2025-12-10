@extends('layouts.app')

@push('styles')
    @include('partials.dashboard-styles')
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
