<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Bootstrap 5.3 -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">

        <link rel="stylesheet" href="{{ asset('css/jb-tokens.css') }}">
        <link rel="stylesheet" href="{{ asset('css/bootstrap-theme.css') }}">

        @vite(['resources/js/app.js'])
    </head>
    <body>
        <div class="jb-guest-wrap">
            <div class="jb-guest-card">
                <div class="text-center mb-4">
                    <a href="/" class="jb-brand-link justify-content-center" style="font-size:var(--text-xl)">
                        <span class="jb-brand-mark" style="width:2.5rem;height:2.5rem;box-shadow:var(--shadow-md)">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 2L2 7l10 5 10-5-10-5z" fill="currentColor" opacity="0.9"/>
                                <path d="M2 17l10 5 10-5M2 12l10 5 10-5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                            </svg>
                        </span>
                        <span>{{ config('app.name', 'JB Work Shop') }}</span>
                    </a>
                </div>

                {{ $slot }}
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
