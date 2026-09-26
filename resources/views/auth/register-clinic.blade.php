@extends('layouts.site')

@section('content')
    <form method="POST" action="{{ route('register.clinic') }}" enctype="multipart/form-data" class="mx-auto grid max-w-3xl gap-3 md:grid-cols-2">
        @csrf
        <h1 class="font-display text-4xl text-forest md:col-span-2">{{ __('ui.nav.clinic_register') }}</h1>
        <input class="field" name="name" value="{{ old('name') }}" placeholder="{{ __('ui.auth.name') }}" required>
        <input class="field" name="mobile" value="{{ old('mobile') }}" placeholder="{{ __('ui.auth.mobile') }}" required>
        <input class="field" type="email" name="email" value="{{ old('email') }}" placeholder="{{ __('ui.auth.email') }}">
        <input class="field" name="clinic_name" value="{{ old('clinic_name') }}" placeholder="Clinic name" required>
        <select class="field" name="specialty" required>
            @foreach ($specialties as $specialty)
                <option value="{{ $specialty }}" @selected(old('specialty') === $specialty)>{{ $specialty }}</option>
            @endforeach
        </select>
        <input class="field" name="city" value="{{ old('city', 'Ahmedabad') }}" placeholder="City" required>
        <input class="field md:col-span-2" name="address" value="{{ old('address') }}" placeholder="Address" required>
        <input class="field" name="pincode" value="{{ old('pincode') }}" placeholder="Pincode">
        <input class="field" name="state" value="{{ old('state', 'Gujarat') }}" placeholder="State">
        <input class="field" name="latitude" value="{{ old('latitude') }}" placeholder="Latitude">
        <input class="field" name="longitude" value="{{ old('longitude') }}" placeholder="Longitude">
        <textarea class="field md:col-span-2" name="description" placeholder="About the clinic">{{ old('description') }}</textarea>
        <input class="field md:col-span-2" type="file" name="document" accept=".pdf,.jpg,.jpeg,.png">
        <input class="field" type="password" name="password" placeholder="{{ __('ui.auth.password') }}" required>
        <input class="field" type="password" name="password_confirmation" placeholder="{{ __('ui.auth.confirm') }}" required>
        <button class="btn md:col-span-2" type="submit">{{ __('ui.nav.clinic_register') }}</button>
    </form>
@endsection
