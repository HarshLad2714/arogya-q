@extends('layouts.panel')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs uppercase tracking-[0.16em] text-brass">{{ $doctor->room }}</p>
            <h1 class="font-display text-4xl">{{ __('ui.doctor.queue') }}</h1>
        </div>
        <form method="POST" action="{{ route('doctor.next') }}">
            @csrf
            <button class="btn btn-brass" type="submit">{{ __('ui.doctor.call_next') }}</button>
        </form>
    </div>
    <div class="token-disc mt-6">
        <div>
            <span class="kicker">Now</span>
            <span class="num">{{ $live['current_token'] ?? 0 }}</span>
        </div>
    </div>
    <div class="mt-8 overflow-x-auto rounded-[1.4rem] bg-foam">
        <table class="w-full text-left text-sm">
            <thead><tr class="border-b"><th class="p-3">#</th><th>Patient</th><th>Status</th><th>Pay</th><th></th></tr></thead>
            <tbody>
                @foreach ($tokens as $token)
                    <tr class="border-b border-ink/10">
                        <td class="p-3 font-display text-2xl">{{ $token->token_number }}</td>
                        <td>{{ $token->patient->name }}<br><span class="text-ink/50">{{ $token->symptoms }}</span></td>
                        <td><span class="pill">{{ $token->status->label() }}</span></td>
                        <td>{{ $token->payment?->status?->label() }}</td>
                        <td class="space-x-2 p-3 text-right">
                            <a class="btn btn-sm" href="{{ route('doctor.prescription', $token) }}">{{ __('ui.doctor.write') }}</a>
                            <form class="inline" method="POST" action="{{ route('doctor.complete', $token) }}">@csrf<button class="btn btn-sm btn-ghost" type="submit">{{ __('ui.doctor.complete') }}</button></form>
                            <form class="inline" method="POST" action="{{ route('doctor.noshow', $token) }}">@csrf<button class="btn btn-sm btn-ghost" type="submit">{{ __('ui.doctor.noshow') }}</button></form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <form method="POST" action="{{ route('doctor.leave') }}" class="mt-8 flex flex-wrap gap-2">
        @csrf
        <input class="field max-w-xs" type="date" name="leave_date" required>
        <input class="field max-w-xs" name="reason" placeholder="{{ __('ui.doctor.leave') }}">
        <button class="btn" type="submit">{{ __('ui.doctor.leave') }}</button>
    </form>
@endsection
