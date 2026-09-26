@extends('layouts.site')

@section('content')
    <section class="grid items-center gap-10 py-8 lg:grid-cols-[1.15fr_.85fr]">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-brass">{{ __('ui.hero.kicker') }}</p>
            <h1 class="mt-4 max-w-xl font-display text-5xl leading-[1.05] text-forest md:text-6xl">{{ __('ui.hero.title') }}</h1>
            <p class="mt-5 max-w-lg text-lg text-ink/75">{{ __('ui.hero.text') }}</p>
            <form data-search-form action="{{ route('clinics.index') }}" class="mt-8 grid gap-3 rounded-[1.6rem] bg-foam p-3 shadow-xl shadow-ink/5 md:grid-cols-[1fr_1fr_auto]">
                <select class="field" name="specialty">
                    <option value="">{{ __('ui.hero.any') }}</option>
                    @foreach ($specialties as $specialty)
                        <option value="{{ $specialty }}">{{ $specialty }}</option>
                    @endforeach
                </select>
                <input class="field" name="q" placeholder="{{ __('ui.hero.area') }}">
                <input type="hidden" name="lat">
                <input type="hidden" name="lng">
                <button class="btn" type="submit">{{ __('ui.hero.search') }}</button>
            </form>
            <button class="mt-3 text-sm font-semibold text-forest" type="button" data-geo>{{ __('ui.hero.near') }}</button>
        </div>
        <div class="ticket px-8 py-10">
            <p class="text-xs uppercase tracking-[0.2em] text-ink/50">{{ __('ui.booking.now_serving') }}</p>
            <div class="mt-6 flex items-center justify-between gap-4">
                <div class="token-disc">
                    <div>
                        <span class="kicker">Token</span>
                        <span class="num">24</span>
                    </div>
                </div>
                <div>
                    <p class="font-display text-3xl text-forest">Shanti OPD</p>
                    <p class="mt-2 text-sm text-ink/60">Room 1 · Dr. Meera Shah</p>
                    <p class="mt-4 text-sm">Next 25 · 26 · 27</p>
                </div>
            </div>
            @if ($board)
                <a class="btn btn-brass mt-8 w-full" href="{{ route('display.show', $board) }}">{{ __('ui.hero.board') }}</a>
            @endif
        </div>
    </section>

    <section class="mt-8 grid gap-4 md:grid-cols-3">
        @foreach ([['1t','1d'], ['2t','2d'], ['3t','3d']] as $index => $pair)
            <article class="rounded-[1.5rem] bg-forest p-6 text-foam {{ $index === 1 ? 'md:-translate-y-3' : '' }}">
                <p class="font-display text-4xl text-brass-soft">0{{ $index + 1 }}</p>
                <h2 class="mt-3 text-2xl">{{ __('ui.how.'.$pair[0]) }}</h2>
                <p class="mt-2 text-sm text-foam/75">{{ __('ui.how.'.$pair[1]) }}</p>
            </article>
        @endforeach
    </section>

    <section class="mt-14">
        <div class="flex items-end justify-between">
            <h2 class="font-display text-4xl">{{ __('ui.clinic.directory') }}</h2>
            <a class="text-sm font-semibold" href="{{ route('clinics.index') }}">{{ __('ui.nav.clinics') }}</a>
        </div>
        <div class="mt-6 grid gap-4 md:grid-cols-3">
            @foreach ($clinics as $clinic)
                <a href="{{ route('clinics.show', $clinic) }}" class="rounded-[1.5rem] bg-foam p-5 shadow-lg shadow-ink/5">
                    <p class="pill">{{ $clinic->specialty }}</p>
                    <h3 class="mt-4 font-display text-2xl">{{ $clinic->name }}</h3>
                    <p class="mt-2 text-sm text-ink/60">{{ $clinic->city }} · {{ $clinic->doctors_count }} {{ __('ui.clinic.doctors') }}</p>
                    <p class="mt-4 text-sm">★ {{ number_format((float) $clinic->reviews_avg_rating, 1) }}</p>
                </a>
            @endforeach
        </div>
    </section>
@endsection
