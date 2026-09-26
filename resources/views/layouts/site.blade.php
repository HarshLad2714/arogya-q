<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? __('ui.brand') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,560;9..144,680&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen text-ink antialiased">
    <header class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-5 py-5">
        <a href="{{ route('home') }}" class="text-forest">@include('partials.mark')</a>
        <nav class="hidden items-center gap-6 text-sm md:flex">
            <a href="{{ route('clinics.index') }}">{{ __('ui.nav.clinics') }}</a>
            <a href="{{ route('register.clinic') }}">{{ __('ui.nav.clinic_register') }}</a>
        </nav>
        <div class="flex items-center gap-3">
            @include('partials.locale')
            @auth
                <a class="btn btn-sm" href="{{ route('dashboard') }}">{{ __('ui.nav.dashboard') }}</a>
            @else
                <a class="text-sm font-semibold" href="{{ route('login') }}">{{ __('ui.nav.login') }}</a>
                <a class="btn btn-sm" href="{{ route('register') }}">{{ __('ui.nav.register') }}</a>
            @endauth
        </div>
    </header>
    <main class="mx-auto max-w-6xl px-5 pb-16">
        @include('partials.flash')
        @yield('content')
    </main>
    <footer class="border-t border-ink/10 px-5 py-8 text-center text-sm text-ink/60">
        {{ __('ui.footer.line') }}
    </footer>
    @stack('scripts')
</body>
</html>
