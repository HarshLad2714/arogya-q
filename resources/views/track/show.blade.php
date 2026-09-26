@extends('layouts.site')

@section('content')
    <section class="ticket mx-auto max-w-xl p-8" data-tracker="{{ route('live.token', $token) }}" data-ws="{{ \App\Support\QueueSocket::url() }}" data-now="{{ __('ui.queue.now') }}" data-next="{{ __('ui.queue.next') }}" data-ahead-label="{{ __('ui.queue.ahead_label') }}">
        <p class="text-xs uppercase tracking-[0.18em] text-brass">{{ $token->clinic->name }}</p>
        <h1 class="mt-2 font-display text-4xl text-forest">Dr. {{ $token->doctor->user->name }}</h1>
        <p class="mt-1 text-sm text-ink/60">{{ $token->date->format('d M Y') }} · {{ $token->doctor->room }}</p>
        <div class="mt-8 grid grid-cols-3 gap-3 text-center">
            <div>
                <p class="text-xs uppercase tracking-wide text-ink/50">{{ __('ui.queue.your_token') }}</p>
                <p class="font-display text-5xl">{{ $token->token_number }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wide text-ink/50">{{ __('ui.booking.now_serving') }}</p>
                <p class="font-display text-5xl" data-current>{{ $live['current_token'] }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wide text-ink/50">{{ __('ui.queue.ahead') }}</p>
                <p class="font-display text-5xl" data-ahead>{{ $ahead }}</p>
            </div>
        </div>
        <p class="mt-6 text-center text-lg" data-note>
            @if ($token->token_number === (int) $live['current_token'])
                {{ __('ui.queue.now') }}
            @elseif ($ahead === 0)
                {{ __('ui.queue.next') }}
            @else
                {{ __('ui.queue.ahead_label', ['count' => $ahead]) }}
            @endif
        </p>
        <p class="mt-2 text-center text-sm text-ink/60"><span data-wait>{{ $waitMinutes }}</span> {{ __('ui.queue.minutes') }}</p>
        <p class="mt-6 text-center"><span class="pill pill-live">{{ $token->status->label() }}</span></p>
    </section>
@endsection
