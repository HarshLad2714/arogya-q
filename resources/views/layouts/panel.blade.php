<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? __('ui.nav.dashboard') }} · {{ __('ui.brand') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,560;9..144,680&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#efe7da] text-ink antialiased">
    <div class="min-h-screen lg:grid lg:grid-cols-[250px_1fr]">
        <aside data-sidebar class="z-20 bg-forest text-foam max-lg:fixed max-lg:inset-y-0 max-lg:w-64 max-lg:-translate-x-full max-lg:transition lg:translate-x-0">
            <div class="flex h-full flex-col p-5">
                <a href="{{ route('home') }}" class="text-foam">@include('partials.mark')</a>
                <p class="mt-6 text-xs uppercase tracking-[0.16em] text-foam/50">{{ auth()->user()->role->label() }}</p>
                <nav class="mt-4 space-y-1">
                    @foreach ($navItems as $item)
                        <a href="{{ route($item['route']) }}" class="panel-link {{ request()->routeIs($item['match']) ? 'active' : '' }}">{{ $item['label'] }}</a>
                    @endforeach
                </nav>
                <div class="mt-auto rounded-2xl bg-white/10 p-3 text-sm">
                    <p class="font-semibold">{{ auth()->user()->name }}</p>
                    <p class="text-foam/70">{{ auth()->user()->mobile }}</p>
                </div>
            </div>
        </aside>
        <div>
            <header class="flex items-center justify-between gap-3 px-5 py-4">
                <button data-nav-toggle class="btn btn-ghost btn-sm lg:hidden" type="button">Menu</button>
                <div class="ml-auto flex items-center gap-3">
                    @include('partials.locale')
                    <a class="text-sm" href="{{ route('profile.edit') }}">{{ __('ui.nav.profile') }}</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-sm btn-ghost" type="submit">{{ __('ui.nav.logout') }}</button>
                    </form>
                </div>
            </header>
            <main class="px-5 pb-12">
                @include('partials.flash')
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
