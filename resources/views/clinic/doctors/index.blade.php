@extends('layouts.panel')

@section('content')
    <div class="flex items-center justify-between">
        <h1 class="font-display text-4xl">{{ __('ui.panel.doctors') }}</h1>
        <a class="btn" href="{{ route('clinic.doctors.create') }}">+</a>
    </div>
    <div class="mt-6 grid gap-4">
        @foreach ($doctors as $doctor)
            <article class="rounded-[1.4rem] bg-foam p-5">
                <div class="flex flex-wrap justify-between gap-3">
                    <div>
                        <h2 class="font-display text-2xl">Dr. {{ $doctor->user->name }}</h2>
                        <p class="text-sm text-ink/60">{{ $doctor->specialization }} · ₹{{ number_format($doctor->consultation_fee) }} · {{ $doctor->room }}</p>
                    </div>
                    <a class="btn btn-sm btn-ghost" href="{{ route('clinic.doctors.edit', $doctor) }}">Edit</a>
                </div>
                <form method="POST" action="{{ route('clinic.leaves.store', $doctor) }}" class="mt-3 flex flex-wrap gap-2">
                    @csrf
                    <input class="field max-w-[11rem]" type="date" name="leave_date" required>
                    <input class="field max-w-xs" name="reason" placeholder="Reason">
                    <button class="btn btn-sm" type="submit">{{ __('ui.doctor.leave') }}</button>
                </form>
                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach ($doctor->leaves as $leave)
                        <form method="POST" action="{{ route('clinic.leaves.destroy', $leave) }}">
                            @csrf
                            @method('DELETE')
                            <button class="pill" type="submit">{{ $leave->leave_date->format('d M') }} ×</button>
                        </form>
                    @endforeach
                </div>
            </article>
        @endforeach
    </div>
    <h2 class="mt-10 font-display text-2xl">{{ __('ui.roles.receptionist') }}</h2>
    <ul class="mt-3 text-sm">
        @foreach ($receptionists as $person)
            <li>{{ $person->name }} · {{ $person->mobile }}</li>
        @endforeach
    </ul>
    <form method="POST" action="{{ route('clinic.reception.store') }}" class="mt-3 grid max-w-3xl gap-2 md:grid-cols-4">
        @csrf
        <input class="field" name="name" placeholder="{{ __('ui.auth.name') }}" required>
        <input class="field" name="mobile" placeholder="{{ __('ui.auth.mobile') }}" required>
        <input class="field" type="password" name="password" placeholder="{{ __('ui.auth.password') }}" required>
        <button class="btn" type="submit">+</button>
    </form>
@endsection
