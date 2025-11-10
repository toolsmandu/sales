@extends('layouts.app')

@section('content')
    <article>
        <header>
            <h1>Create an account</h1>
            <p>Register to start managing the sales website.</p>
        </header>

        <form method="POST" action="{{ route('register') }}" novalidate>
            @csrf

            <label for="name">
                Full name
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    autofocus
                >
            </label>
            @error('name')
                <small role="alert">{{ $message }}</small>
            @enderror

            <label for="email">
                Email address
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                >
            </label>
            @error('email')
                <small role="alert">{{ $message }}</small>
            @enderror

            <label for="role">
                User type
                <select
                    id="role"
                    name="role"
                    required
                >
                    <option value="" disabled {{ old('role') === null ? 'selected' : '' }}>Select a user type</option>
                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="employee" {{ old('role') === 'employee' ? 'selected' : '' }}>Employee</option>
                </select>
            </label>
            @error('role')
                <small role="alert">{{ $message }}</small>
            @enderror

            <label for="password">
                Password
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                >
            </label>
            @error('password')
                <small role="alert">{{ $message }}</small>
            @enderror

            <label for="password_confirmation">
                Confirm password
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    required
                >
            </label>

            <button type="submit">Register</button>
        </form>

        <footer>
            <p>
                Already registered?
                <a href="{{ route('login') }}">Log in</a>.
            </p>
        </footer>
    </article>
@endsection
