@extends('layouts.site')

@section('content')
    <h1 class="font-display text-5xl text-forest">{{ __('ui.clinic.directory') }}</h1>
    <form action="{{ route('clinics.index') }}" class="mt-6 grid gap-3 md:grid-cols-[1fr_1fr_auto]">
        <input class="field" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('ui.hero.area') }}">
        <select class="field" name="specialty">
            <option value="">{{ __('ui.hero.any') }}</option>
            @foreach ($specialties as $specialty)
                <option value="{{ $specialty }}" @selected(($filters['specialty'] ?? '') === $specialty)>{{ $specialty }}</option>
            @endforeach
        </select>
        @if (!empty($filters['lat']))
            <input type="hidden" name="lat" value="{{ $filters['lat'] }}">
            <input type="hidden" name="lng" value="{{ $filters['lng'] }}">
        @endif
        <button class="btn" type="submit">{{ __('ui.common.search') }}</button>
    </form>

    <div class="mt-8 grid gap-4 md:grid-cols-3">
        @forelse ($clinics as $clinic)
            <a href="{{ route('clinics.show', $clinic) }}" class="rounded-[1.6rem] bg-foam p-5">
                <div class="flex items-start justify-between gap-3">
                    <p class="pill">{{ $clinic->specialty }}</p>
                    <p class="text-sm">★ {{ number_format((float) $clinic->reviews_avg_rating, 1) }}</p>
                </div>
                <h2 class="mt-4 font-display text-3xl">{{ $clinic->name }}</h2>
                <p class="mt-2 text-sm text-ink/65">{{ $clinic->address }}, {{ $clinic->city }}</p>
                <p class="mt-4 text-sm font-semibold">
                    {{ $clinic->doctors_count }} {{ __('ui.clinic.doctors') }}
                    @if ($clinic->distance_km)
                        · {{ number_format($clinic->distance_km, 1) }} {{ __('ui.clinic.away') }}
                    @endif
                </p>
            </a>
        @empty
            <p class="text-ink/60">{{ __('ui.clinic.empty') }}</p>
        @endforelse
    </div>
    <div class="mt-8">{{ $clinics->links() }}</div>
@endsection
