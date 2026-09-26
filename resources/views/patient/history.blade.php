@extends('layouts.panel')

@section('content')
    <h1 class="font-display text-4xl">{{ __('ui.patient.history') }}</h1>
    <div class="mt-6 space-y-4">
        @foreach ($tokens as $token)
            <article class="rounded-[1.4rem] bg-foam p-5">
                <div class="flex flex-wrap justify-between gap-3">
                    <div>
                        <p class="font-display text-2xl">#{{ $token->token_number }} · Dr. {{ $token->doctor->user->name }}</p>
                        <p class="text-sm text-ink/60">{{ $token->date->format('d M Y') }} · {{ $token->status->label() }}</p>
                    </div>
                    <div class="flex gap-2">
                        @if ($token->prescription)
                            <a class="btn btn-sm btn-ghost" href="{{ route('patient.prescriptions.show', $token->prescription) }}">{{ __('ui.patient.prescription') }}</a>
                        @endif
                        <a class="btn btn-sm btn-ghost" href="{{ route('track.show', $token) }}">{{ __('ui.queue.live') }}</a>
                    </div>
                </div>
                @if ($token->status->value === 'booked')
                    <div class="mt-4 flex flex-wrap gap-2">
                        <form method="POST" action="{{ route('patient.tokens.cancel', $token) }}">@csrf<button class="btn btn-sm btn-ghost" type="submit">{{ __('ui.patient.cancel') }}</button></form>
                        <form method="POST" action="{{ route('patient.tokens.reschedule', $token) }}" class="flex gap-2">
                            @csrf
                            <input class="field" type="date" name="date" required>
                            <button class="btn btn-sm" type="submit">{{ __('ui.patient.reschedule') }}</button>
                        </form>
                    </div>
                @endif
                @if ($token->status->value === 'completed' && !$token->review)
                    <form method="POST" action="{{ route('patient.reviews.store', $token) }}" class="mt-4 grid gap-2 md:grid-cols-[120px_1fr_auto]">
                        @csrf
                        <select class="field" name="rating" required>
                            @for ($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}">{{ $i }} ★</option>
                            @endfor
                        </select>
                        <input class="field" name="comment" placeholder="{{ __('ui.patient.review') }}">
                        <button class="btn btn-sm" type="submit">{{ __('ui.patient.review') }}</button>
                    </form>
                @endif
            </article>
        @endforeach
    </div>
    <div class="mt-6">{{ $tokens->links() }}</div>
@endsection
