@extends('layouts.site')

@section('content')
    <div class="mx-auto grid max-w-4xl gap-8 lg:grid-cols-2">
        <div class="rounded-[1.8rem] bg-forest p-8 text-foam">
            <h1 class="font-display text-5xl">{{ __('ui.auth.welcome') }}</h1>
            <p class="mt-4 text-foam/75">{{ __('ui.auth.demo_hint') }}</p>
            @if (app()->environment('local'))
                <div class="mt-6 space-y-2">
                    @foreach (config('arogya.demo_accounts') as $account)
                        <button type="button" class="w-full rounded-2xl bg-white/10 px-4 py-3 text-left" data-fill data-mobile="{{ $account['mobile'] }}" data-password="{{ $account['password'] }}">
                            <span class="block text-xs uppercase tracking-wide text-brass-soft">{{ $account['role'] }}</span>
                            <span>{{ $account['mobile'] }} · {{ $account['password'] }}</span>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
        <div>
            <div class="mb-4 flex gap-2">
                <button class="btn btn-sm bg-forest text-foam" type="button" data-auth-tab="password">{{ __('ui.auth.password_tab') }}</button>
                <button class="btn btn-sm btn-ghost" type="button" data-auth-tab="otp">{{ __('ui.auth.otp_tab') }}</button>
            </div>
            <form data-auth-panel="password" method="POST" action="{{ route('login') }}" class="space-y-3">
                @csrf
                <input class="field" name="mobile" inputmode="numeric" maxlength="10" placeholder="{{ __('ui.auth.mobile') }}" value="{{ old('mobile') }}" required>
                <input class="field" type="password" name="password" placeholder="{{ __('ui.auth.password') }}" required>
                <button class="btn w-full" type="submit">{{ __('ui.nav.login') }}</button>
            </form>
            <form data-auth-panel="otp" method="POST" action="{{ route('login.otp') }}" class="hidden space-y-3">
                @csrf
                <input class="field" name="mobile" inputmode="numeric" maxlength="10" placeholder="{{ __('ui.auth.mobile') }}" required>
                <button class="btn w-full" type="submit">{{ __('ui.auth.send_otp') }}</button>
            </form>
            <p class="mt-4 text-sm"><a href="{{ route('password.request') }}">{{ __('ui.auth.forgot') }}</a></p>
            <p class="mt-2 text-sm">{{ __('ui.auth.no_account') }} <a class="font-semibold" href="{{ route('register') }}">{{ __('ui.nav.register') }}</a></p>
        </div>
    </div>
@endsection
