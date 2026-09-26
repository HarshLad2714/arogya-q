@extends('layouts.panel')

@section('content')
    <h1 class="font-display text-4xl">{{ __('ui.patient.hello') }}, {{ auth()->user()->name }}</h1>
    @if ($liveToken)
        <a href="{{ route('track.show', $liveToken) }}" class="ticket mt-6 block p-6">
            <p class="text-xs uppercase tracking-[0.16em] text-brass">{{ __('ui.queue.live') }}</p>
            <p class="mt-2 font-display text-5xl">#{{ $liveToken->token_number }}</p>
            <p class="mt-2">Dr. {{ $liveToken->doctor->user->name }} · {{ $liveToken->clinic->name }}</p>
        </a>
    @endif
    <div class="mt-8 grid gap-3">
        @forelse ($tokens as $token)
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-foam px-4 py-3">
                <div>
                    <p class="font-semibold">#{{ $token->token_number }} · Dr. {{ $token->doctor->user->name }}</p>
                    <p class="text-sm text-ink/60">{{ $token->date->format('d M Y') }} · {{ $token->clinic->name }}</p>
                </div>
                <span class="pill">{{ $token->status->label() }}</span>
            </div>
        @empty
            <p>{{ __('ui.patient.empty') }} <a class="font-semibold" href="{{ route('clinics.index') }}">{{ __('ui.nav.clinics') }}</a></p>
        @endforelse
    </div>
    <h2 class="mt-10 font-display text-2xl">{{ __('ui.queue.live') }}</h2>
    <div class="mt-3 space-y-2">
        @forelse ($notifications as $note)
            <p class="rounded-2xl bg-foam px-4 py-3 text-sm">{{ $note->data['message'] ?? '' }}</p>
        @empty
            <p class="text-sm text-ink/50">—</p>
        @endforelse
    </div>
@endsection
