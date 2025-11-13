@extends('layouts.app')

@section('content')
    <article>
        <header>
            <h1>Log in</h1>
            <p>Access your account to manage the sales dashboard.</p>
        </header>

        <form method="POST" action="{{ route('login') }}" novalidate>
            @csrf

            <label for="email">
                Email address
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                >
            </label>
            @error('email')
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

            <label for="remember">
                <input type="checkbox" id="remember" name="remember">
                Remember me
            </label>

            <button type="submit">Log in</button>
        </form>

        <footer>
            @if (\App\Models\SiteSetting::bool('registration_enabled', true) && Route::has('register'))
                <p>
                    Need an account?
                    <a href="{{ route('register') }}">Register now</a>.
                </p>
            @else
                <p>New registrations are disabled. Contact an administrator to create an account.</p>
            @endif
        </footer>
    </article>
@endsection
