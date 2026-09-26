@extends('layouts.panel')

@section('content')
    <div class="flex flex-wrap items-end justify-between gap-3">
        <h1 class="font-display text-4xl">{{ __('ui.desk.title') }}</h1>
        @if ($clinic)
            <a class="btn btn-brass" href="{{ route('display.show', $clinic) }}" target="_blank">{{ __('ui.desk.board') }}</a>
        @endif
    </div>
    <form method="POST" action="{{ route('desk.walkin') }}" class="mt-6 grid gap-3 rounded-[1.4rem] bg-foam p-4 md:grid-cols-4">
        @csrf
        <select class="field" name="doctor_id" required>
            @foreach ($doctors as $doctor)
                <option value="{{ $doctor->id }}">Dr. {{ $doctor->user->name }} · now {{ $live[$doctor->id]['current_token'] ?? 0 }}</option>
            @endforeach
        </select>
        <input class="field" name="name" placeholder="{{ __('ui.auth.name') }}" required>
        <input class="field" name="mobile" placeholder="{{ __('ui.auth.mobile') }}" required>
        <button class="btn" type="submit">{{ __('ui.desk.walkin') }}</button>
    </form>
    <div class="mt-6 overflow-x-auto rounded-[1.4rem] bg-foam">
        <table class="w-full text-left text-sm">
            <thead><tr class="border-b"><th class="p-3">#</th><th>Patient</th><th>Doctor</th><th>Status</th><th>Pay</th><th></th></tr></thead>
            <tbody>
                @foreach ($tokens as $token)
                    <tr class="border-b border-ink/10">
                        <td class="p-3 font-display text-xl">{{ $token->token_number }}</td>
                        <td>{{ $token->patient->name }}<br>{{ $token->patient->mobile }}</td>
                        <td>{{ $token->doctor->user->name }}</td>
                        <td>{{ $token->status->label() }}</td>
                        <td>{{ $token->payment?->status?->label() }}</td>
                        <td class="space-x-2 p-3">
                            <form class="inline" method="POST" action="{{ route('desk.arrive', $token) }}">@csrf<button class="btn btn-sm btn-ghost">Arrive</button></form>
                            <form class="inline" method="POST" action="{{ route('desk.cash', $token) }}">@csrf<button class="btn btn-sm">Cash</button></form>
                            <form class="inline" method="POST" action="{{ route('desk.noshow', $token) }}">@csrf<button class="btn btn-sm btn-ghost">No-show</button></form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
