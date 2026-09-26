@extends('layouts.panel')

@section('content')
    <h1 class="font-display text-4xl">{{ __('ui.brand') }}</h1>
    <div class="mt-6 grid gap-3 md:grid-cols-4">
        @foreach ([
            [__('ui.platform.clinics'), $stats['clinics']],
            [__('ui.platform.pending'), $stats['pending']],
            [__('ui.common.patients'), $stats['patients']],
            [__('ui.platform.share'), '₹'.number_format($stats['share'])],
        ] as $card)
            <article class="rounded-3xl bg-foam p-5">
                <p class="text-xs uppercase tracking-[0.14em] text-ink/50">{{ $card[0] }}</p>
                <p class="mt-2 font-display text-4xl">{{ $card[1] }}</p>
            </article>
        @endforeach
    </div>
    <p class="mt-4 text-sm text-ink/60">{{ __('ui.common.visits') }} {{ $stats['visits_today'] }} · {{ __('ui.panel.revenue') }} ₹{{ number_format($stats['revenue']) }} · {{ $stats['share_percent'] }}%</p>
    <h2 class="mt-8 font-display text-2xl">{{ __('ui.platform.pending') }}</h2>
    <div class="mt-3 space-y-2">
        @forelse ($pending as $clinic)
            <a class="block rounded-2xl bg-foam px-4 py-3" href="{{ route('platform.clinics', ['status' => 'pending']) }}">{{ $clinic->name }} · {{ $clinic->city }}</a>
        @empty
            <p class="text-sm text-ink/50">—</p>
        @endforelse
    </div>
@endsection
