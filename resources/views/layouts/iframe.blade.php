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

        <style>
            body {
                margin: 0;
                padding: 0;
                background: #f8fafc;
            }

            main {
                padding: 1rem;
            }
        </style>

        @stack('styles')
    </head>
    <body>
        <main>
            @yield('content')
        </main>

        @stack('scripts')
    </body>
</html>
