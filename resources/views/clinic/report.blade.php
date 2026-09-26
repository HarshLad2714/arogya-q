@extends('layouts.panel')

@section('content')
    <div class="no-print flex flex-wrap items-end justify-between gap-3">
        <h1 class="font-display text-4xl">{{ __('ui.panel.reports') }}</h1>
        <div class="flex gap-2">
            <a class="btn btn-sm btn-ghost" href="{{ route('clinic.reports.export', ['from' => $from, 'to' => $to]) }}">{{ __('ui.common.export') }}</a>
            <button class="btn btn-sm" type="button" data-print>{{ __('ui.common.print') }}</button>
        </div>
    </div>
    <form class="no-print mt-4 flex flex-wrap gap-2" method="GET">
        <input class="field max-w-[11rem]" type="date" name="from" value="{{ $from }}">
        <input class="field max-w-[11rem]" type="date" name="to" value="{{ $to }}">
        <button class="btn btn-sm" type="submit">{{ __('ui.common.search') }}</button>
    </form>
    <p class="mt-4 text-sm">{{ $clinic->name }} · {{ $from }} → {{ $to }} · ₹{{ number_format($stats['revenue']) }}</p>
    <table class="mt-4 w-full bg-foam text-left text-sm">
        <thead><tr class="border-b"><th class="p-2">Date</th><th>#</th><th>Patient</th><th>Doctor</th><th>Status</th><th>Pay</th></tr></thead>
        <tbody>
            @foreach ($rows as $token)
                <tr class="border-b border-ink/10">
                    <td class="p-2">{{ $token->date->format('d M') }}</td>
                    <td>{{ $token->token_number }}</td>
                    <td>{{ $token->patient?->name }}</td>
                    <td>{{ $token->doctor?->user?->name }}</td>
                    <td>{{ $token->status->label() }}</td>
                    <td>{{ $token->payment?->status?->label() }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
