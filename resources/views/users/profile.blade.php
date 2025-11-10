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
        </section>
    </div>
@endsection
