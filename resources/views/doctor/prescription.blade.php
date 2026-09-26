@extends('layouts.panel')

@section('content')
    <h1 class="font-display text-4xl">{{ $token->patient->name }} · #{{ $token->token_number }}</h1>
    <form method="POST" action="{{ route('doctor.prescription.store', $token) }}" class="mt-6 max-w-3xl space-y-3">
        @csrf
        <div data-medicines class="space-y-2">
            @for ($i = 0; $i < 3; $i++)
                <div class="medicine-row grid gap-2 md:grid-cols-4">
                    <input class="field" name="medicines[{{ $i }}][name]" placeholder="Medicine" value="{{ data_get($token->prescription, 'medicines.'.$i.'.name') }}">
                    <input class="field" name="medicines[{{ $i }}][dosage]" placeholder="Dosage" value="{{ data_get($token->prescription, 'medicines.'.$i.'.dosage') }}">
                    <input class="field" name="medicines[{{ $i }}][duration]" placeholder="Duration" value="{{ data_get($token->prescription, 'medicines.'.$i.'.duration') }}">
                    <input class="field" name="medicines[{{ $i }}][timing]" placeholder="Timing" value="{{ data_get($token->prescription, 'medicines.'.$i.'.timing') }}">
                </div>
            @endfor
        </div>
        <button class="text-sm font-semibold" type="button" data-add-medicine>+ medicine</button>
        <textarea class="field" name="notes" rows="3" placeholder="Notes">{{ $token->prescription?->notes }}</textarea>
        <input class="field" type="date" name="follow_up_date" value="{{ optional($token->prescription?->follow_up_date)->toDateString() }}">
        <button class="btn" type="submit">{{ __('ui.doctor.write') }}</button>
    </form>
@endsection
