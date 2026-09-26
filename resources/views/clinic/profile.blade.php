@extends('layouts.panel')

@section('content')
    <h1 class="font-display text-4xl">{{ __('ui.panel.profile') }}</h1>
    <form method="POST" action="{{ route('clinic.profile.update') }}" enctype="multipart/form-data" class="mt-6 grid max-w-3xl gap-3 md:grid-cols-2">
        @csrf
        @method('PUT')
        <input class="field" name="name" value="{{ old('name', $clinic->name) }}" required>
        <select class="field" name="specialty">
            @foreach ($specialties as $specialty)
                <option value="{{ $specialty }}" @selected($clinic->specialty === $specialty)>{{ $specialty }}</option>
            @endforeach
        </select>
        <input class="field md:col-span-2" name="address" value="{{ $clinic->address }}" required>
        <input class="field" name="city" value="{{ $clinic->city }}" required>
        <input class="field" name="phone" value="{{ $clinic->phone }}">
        <input class="field" name="latitude" value="{{ $clinic->latitude }}">
        <input class="field" name="longitude" value="{{ $clinic->longitude }}">
        <input class="field" type="number" name="cancel_cutoff_minutes" value="{{ $clinic->cancel_cutoff_minutes }}">
        <input class="field" type="number" name="refund_percent" value="{{ $clinic->refund_percent }}">
        <input class="field md:col-span-2" name="services" value="{{ implode(', ', $clinic->services ?? []) }}" placeholder="OPD, Lab, Vaccine">
        <textarea class="field md:col-span-2" name="description" rows="4">{{ $clinic->description }}</textarea>
        <input class="field md:col-span-2" type="file" name="document">
        <button class="btn" type="submit">{{ __('ui.common.saved') }}</button>
    </form>
@endsection
