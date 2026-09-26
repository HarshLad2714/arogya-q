@extends('layouts.panel')

@section('content')
    <div class="no-print mb-4 flex gap-2">
        <a class="btn btn-sm btn-ghost" href="{{ route('patient.history') }}">{{ __('ui.common.back') }}</a>
        <button class="btn btn-sm" type="button" data-print>{{ __('ui.common.print') }}</button>
    </div>
    <article class="mx-auto max-w-2xl rounded-[1.5rem] bg-foam p-8">
        <p class="text-xs uppercase tracking-[0.18em] text-brass">{{ __('ui.brand') }}</p>
        <h1 class="font-display text-4xl">{{ $prescription->token->clinic->name }}</h1>
        <p class="mt-2">Dr. {{ $prescription->token->doctor->user->name }} · {{ $prescription->created_at->format('d M Y') }}</p>
        <p class="mt-1 text-sm">{{ $prescription->patient->name }} · Token #{{ $prescription->token->token_number }}</p>
        <table class="mt-6 w-full text-left text-sm">
            <thead><tr class="border-b"><th class="py-2">Medicine</th><th>Dosage</th><th>Duration</th><th>Timing</th></tr></thead>
            <tbody>
                @foreach ($prescription->medicines as $medicine)
                    <tr class="border-b border-ink/10">
                        <td class="py-2">{{ $medicine['name'] ?? '' }}</td>
                        <td>{{ $medicine['dosage'] ?? '' }}</td>
                        <td>{{ $medicine['duration'] ?? '' }}</td>
                        <td>{{ $medicine['timing'] ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @if ($prescription->notes)
            <p class="mt-4">{{ $prescription->notes }}</p>
        @endif
        @if ($prescription->follow_up_date)
            <p class="mt-3 font-semibold">{{ __('ui.patient.follow_up') }}: {{ $prescription->follow_up_date->format('d M Y') }}</p>
        @endif
    </article>
@endsection
