@extends('layouts.site')

@section('content')
    <form method="POST" action="{{ route('register') }}" class="mx-auto max-w-md space-y-3">
        @csrf
        <h1 class="font-display text-4xl text-forest">{{ __('ui.nav.register') }}</h1>
        <input class="field" name="name" value="{{ old('name') }}" placeholder="{{ __('ui.auth.name') }}" required>
        <input class="field" name="mobile" value="{{ old('mobile') }}" inputmode="numeric" maxlength="10" placeholder="{{ __('ui.auth.mobile') }}" required>
        <input class="field" type="email" name="email" value="{{ old('email') }}" placeholder="{{ __('ui.auth.email') }} ({{ __('ui.common.optional') }})">
        <select class="field" name="language_pref">
            <option value="en" @selected(old('language_pref', 'en') === 'en')>English</option>
            <option value="hi" @selected(old('language_pref') === 'hi')>हिन्दी</option>
            <option value="gu" @selected(old('language_pref') === 'gu')>ગુજરાતી</option>
        </select>
        <input class="field" type="password" name="password" placeholder="{{ __('ui.auth.password') }}" required>
        <input class="field" type="password" name="password_confirmation" placeholder="{{ __('ui.auth.confirm') }}" required>
        <button class="btn w-full" type="submit">{{ __('ui.auth.send_otp') }}</button>
        <p class="text-sm"><a href="{{ route('register.clinic') }}">{{ __('ui.nav.clinic_register') }}</a></p>
    </form>
@endsection
