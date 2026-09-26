@extends('layouts.site')

@section('content')
    <a class="text-sm" href="{{ route('clinics.show', $doctor->clinic) }}">{{ __('ui.common.back') }}</a>
    <div class="mt-4 grid gap-6 lg:grid-cols-[1.1fr_.9fr]">
        <div>
            <p class="text-xs uppercase tracking-[0.16em] text-brass">{{ $doctor->clinic->name }}</p>
            <h1 class="mt-2 font-display text-5xl text-forest">Dr. {{ $doctor->user->name }}</h1>
            <p class="mt-3 text-ink/70">{{ $doctor->specialization }} · {{ $doctor->qualification }} · {{ $doctor->room }}</p>
            <p class="mt-2 font-semibold">₹{{ number_format($doctor->consultation_fee) }}</p>
            @auth
                <form method="POST" action="{{ route('booking.store', $doctor) }}" class="mt-8 space-y-4">
                    @csrf
                    <fieldset>
                        <legend class="text-sm font-semibold">{{ __('ui.booking.date') }}</legend>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @php $picked = false; @endphp
                            @foreach ($days as $day)
                                <label class="cursor-pointer">
                                    <input class="peer sr-only" type="radio" name="date" value="{{ $day['date'] }}" @disabled(!$day['available']) @checked($day['available'] && ! $picked && ($picked = true))>
                                    <span class="grid h-20 w-16 place-items-center rounded-2xl border border-ink/10 bg-foam text-center peer-checked:bg-forest peer-checked:text-foam peer-disabled:opacity-30">
                                        <span class="text-xs">{{ $day['label'] }}</span>
                                        <span class="font-display text-2xl">{{ $day['day'] }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                    <label class="block text-sm font-semibold">{{ __('ui.booking.symptoms') }}
                        <textarea class="field mt-2" name="symptoms" rows="3"></textarea>
                    </label>
                    <label class="block text-sm font-semibold">{{ __('ui.booking.pay') }}
                        <select class="field mt-2" name="pay_mode">
                            <option value="online">{{ __('ui.status.online') }}</option>
                            <option value="cash">{{ __('ui.status.cash') }}</option>
                        </select>
                    </label>
                    <button class="btn" type="submit">{{ __('ui.clinic.book') }}</button>
                </form>
            @else
                <a class="btn mt-8" href="{{ route('login', ['redirect' => url()->current()]) }}">{{ __('ui.booking.login_to_book') }}</a>
            @endauth
        </div>
        <aside class="ticket p-8" data-doctor-live="{{ route('live.doctor', $doctor) }}" data-ws="{{ \App\Support\QueueSocket::url() }}">
            <p class="text-xs uppercase tracking-[0.18em] text-ink/50">{{ __('ui.booking.now_serving') }}</p>
            <div class="token-disc mx-auto mt-6">
                <div>
                    <span class="kicker">Token</span>
                    <span class="num" data-current>{{ $live['current_token'] ?? 0 }}</span>
                </div>
            </div>
            <p class="mt-6 text-center text-sm text-ink/60">{{ $doctor->room }} · ~{{ $live['avg_consultation_minutes'] ?? 12 }} {{ __('ui.queue.minutes') }}</p>
        </aside>
    </div>
@endsection
