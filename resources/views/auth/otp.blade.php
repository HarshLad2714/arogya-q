@extends('layouts.site')

@section('content')
    <form method="POST" action="{{ route('otp.verify') }}" class="mx-auto max-w-md space-y-3">
        @csrf
        <h1 class="font-display text-4xl text-forest">{{ __('ui.auth.otp_title') }}</h1>
        <p class="text-sm text-ink/60">{{ $mobile }}</p>
        @if ($demoOtp)
            <p class="rounded-2xl bg-brass-soft px-4 py-3 text-sm">{{ __('ui.auth.demo_code') }}: <strong>{{ $demoOtp }}</strong></p>
        @endif
        <input class="field text-center font-display text-3xl tracking-[0.4em]" name="code" inputmode="numeric" maxlength="6" required>
        <button class="btn w-full" type="submit">{{ __('ui.auth.verify') }}</button>
    </form>
    <form method="POST" action="{{ route('otp.resend') }}" class="mx-auto mt-3 max-w-md">
        @csrf
        <button class="text-sm font-semibold" type="submit">{{ __('ui.auth.resend') }}</button>
    </form>
@endsection
