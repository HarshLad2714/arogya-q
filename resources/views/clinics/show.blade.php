@extends('layouts.site')

@section('content')
    <p class="text-xs uppercase tracking-[0.18em] text-brass">{{ $clinic->specialty }} · {{ $clinic->city }}</p>
    <div class="mt-3 flex flex-wrap items-end justify-between gap-4">
        <h1 class="font-display text-5xl text-forest">{{ $clinic->name }}</h1>
        <a class="btn btn-brass" href="{{ route('display.show', $clinic) }}">{{ __('ui.queue.display') }}</a>
    </div>
    <p class="mt-4 max-w-2xl text-ink/70">{{ $clinic->description }}</p>
    <p class="mt-2 text-sm">{{ $clinic->address }} @if($clinic->pincode) · {{ $clinic->pincode }} @endif · ★ {{ number_format((float) $clinic->reviews_avg_rating, 1) }}</p>
    @if ($clinic->services)
        <div class="mt-4 flex flex-wrap gap-2">
            @foreach ($clinic->services as $service)
                <span class="pill">{{ $service }}</span>
            @endforeach
        </div>
    @endif

    @if ($clinic->latitude && $clinic->longitude)
        <div id="clinic-map" class="mt-6 h-64 overflow-hidden rounded-[1.5rem]"></div>
        @push('head')
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
        @endpush
        @push('scripts')
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
            <script>
                const map = L.map('clinic-map').setView([{{ $clinic->latitude }}, {{ $clinic->longitude }}], 15);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);
                L.marker([{{ $clinic->latitude }}, {{ $clinic->longitude }}]).addTo(map);
            </script>
        @endpush
    @endif

    <h2 class="mt-10 font-display text-3xl">{{ __('ui.clinic.doctors') }}</h2>
    <div class="mt-4 grid gap-4">
        @foreach ($doctors as $doctor)
            <article class="flex flex-wrap items-center justify-between gap-4 rounded-[1.5rem] bg-foam p-5">
                <div>
                    <h3 class="font-display text-2xl">Dr. {{ $doctor->user->name }}</h3>
                    <p class="text-sm text-ink/65">{{ $doctor->specialization }} · {{ $doctor->qualification }} · {{ $doctor->experience_years }} {{ __('ui.common.experience') }}</p>
                    <p class="mt-2 text-sm">{{ $doctor->room }} · ₹{{ number_format($doctor->consultation_fee) }} · ★ {{ number_format((float) $doctor->reviews_avg_rating, 1) }}</p>
                    <p class="mt-1 text-sm text-canopy">{{ __('ui.booking.now_serving') }} #{{ $doctor->live['current_token'] ?? 0 }}</p>
                </div>
                <a class="btn" href="{{ route('booking.create', $doctor) }}">{{ __('ui.clinic.book') }}</a>
            </article>
        @endforeach
    </div>

    <h2 class="mt-10 font-display text-3xl">{{ __('ui.clinic.reviews') }}</h2>
    <div class="mt-4 grid gap-3">
        @forelse ($clinic->reviews as $review)
            <article class="rounded-2xl bg-foam p-4">
                <p class="text-sm">★ {{ $review->rating }} · {{ $review->patient?->name }}</p>
                <p class="mt-2">{{ $review->comment }}</p>
                @if ($review->response)
                    <p class="mt-2 text-sm text-forest">{{ $review->response }}</p>
                @endif
            </article>
        @empty
            <p class="text-sm text-ink/60">{{ __('ui.clinic.empty') }}</p>
        @endforelse
    </div>
@endsection
