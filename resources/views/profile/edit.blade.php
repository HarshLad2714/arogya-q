@extends('layouts.panel')

@section('content')
    <h1 class="font-display text-4xl">{{ __('ui.nav.profile') }}</h1>
    <form method="POST" action="{{ route('profile.update') }}" class="mt-6 max-w-lg space-y-3">
        @csrf
        @method('PUT')
        <input class="field" name="name" value="{{ old('name', $user->name) }}" required>
        <input class="field" value="{{ $user->mobile }}" disabled>
        <input class="field" type="email" name="email" value="{{ old('email', $user->email) }}">
        <select class="field" name="language_pref">
            @foreach (['en' => 'English', 'hi' => 'हिन्दी', 'gu' => 'ગુજરાતી'] as $code => $label)
                <option value="{{ $code }}" @selected(old('language_pref', $user->language_pref) === $code)>{{ $label }}</option>
            @endforeach
        </select>
        <input class="field" type="password" name="password" placeholder="{{ __('ui.auth.password') }}">
        <input class="field" type="password" name="password_confirmation" placeholder="{{ __('ui.auth.confirm') }}">
        <button class="btn" type="submit">{{ __('ui.common.saved') }}</button>
    </form>
@endsection
