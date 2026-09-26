@extends('layouts.site')

@section('content')
    <div class="mx-auto max-w-md space-y-6">
        <h1 class="font-display text-4xl text-forest">{{ __('ui.auth.reset') }}</h1>
        @if (!session('reset_mobile'))
            <form method="POST" action="{{ route('password.email') }}" class="space-y-3">
                @csrf
                <input class="field" name="mobile" placeholder="{{ __('ui.auth.mobile') }}" required>
                <button class="btn w-full" type="submit">{{ __('ui.auth.send_otp') }}</button>
            </form>
        @else
            @if (session('demo_otp'))
                <p class="rounded-2xl bg-brass-soft px-4 py-3 text-sm">{{ __('ui.auth.demo_code') }}: <strong>{{ session('demo_otp') }}</strong></p>
            @endif
            <form method="POST" action="{{ route('password.update') }}" class="space-y-3">
                @csrf
                <input class="field" name="code" placeholder="OTP" required>
                <input class="field" type="password" name="password" placeholder="{{ __('ui.auth.password') }}" required>
                <input class="field" type="password" name="password_confirmation" placeholder="{{ __('ui.auth.confirm') }}" required>
                <button class="btn w-full" type="submit">{{ __('ui.auth.reset') }}</button>
            </form>
        @endif
    </div>
@endsection
