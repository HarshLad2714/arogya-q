@extends('layouts.panel')

@php
    $schedules = collect($doctor?->schedules ?? [])->groupBy('day_of_week');
@endphp

@section('content')
    <h1 class="font-display text-4xl">{{ $doctor ? 'Dr. '.$doctor->user->name : __('ui.panel.doctors') }}</h1>
    <form method="POST" action="{{ $doctor ? route('clinic.doctors.update', $doctor) : route('clinic.doctors.store') }}" class="mt-6 grid max-w-3xl gap-3 md:grid-cols-2">
        @csrf
        @if ($doctor) @method('PUT') @endif
        <input class="field" name="name" value="{{ old('name', $doctor?->user?->name) }}" placeholder="{{ __('ui.auth.name') }}" required>
        <input class="field" name="mobile" value="{{ old('mobile', $doctor?->user?->mobile) }}" placeholder="{{ __('ui.auth.mobile') }}" required>
        <input class="field" name="email" value="{{ old('email', $doctor?->user?->email) }}" placeholder="{{ __('ui.auth.email') }}">
        <input class="field" type="password" name="password" placeholder="{{ __('ui.auth.password') }}" {{ $doctor ? '' : 'required' }}>
        <input class="field" name="specialization" value="{{ old('specialization', $doctor?->specialization) }}" placeholder="Specialization" required>
        <input class="field" name="qualification" value="{{ old('qualification', $doctor?->qualification) }}" placeholder="Qualification" required>
        <input class="field" type="number" name="experience_years" value="{{ old('experience_years', $doctor->experience_years ?? 5) }}" required>
        <input class="field" type="number" name="consultation_fee" value="{{ old('consultation_fee', $doctor->consultation_fee ?? 400) }}" required>
        <input class="field" name="room" value="{{ old('room', $doctor->room ?? 'Room 1') }}" required>
        <input class="field" type="number" name="max_tokens_per_day" value="{{ old('max_tokens_per_day', $doctor->max_tokens_per_day ?? 40) }}" required>
        <input class="field" type="number" name="avg_consultation_minutes" value="{{ old('avg_consultation_minutes', $doctor?->schedules->first()->avg_consultation_minutes ?? 12) }}" required>
        @if ($doctor)
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $doctor->is_active))> Active</label>
        @endif
        <textarea class="field md:col-span-2" name="bio" rows="3">{{ old('bio', $doctor?->bio) }}</textarea>
        <div class="md:col-span-2 space-y-2">
            @foreach (__('ui.days') as $day => $label)
                @php
                    $rows = $schedules[$day] ?? collect();
                    $morning = $rows->get(0);
                    $evening = $rows->get(1);
                    $start = $morning ? substr((string) $morning->start_time, 0, 5) : (!$doctor && $day >= 1 && $day <= 6 ? '10:00' : '');
                    $end = $morning ? substr((string) $morning->end_time, 0, 5) : (!$doctor && $day >= 1 && $day <= 6 ? '13:00' : '');
                    $estart = $evening ? substr((string) $evening->start_time, 0, 5) : '';
                    $eend = $evening ? substr((string) $evening->end_time, 0, 5) : '';
                @endphp
                <div class="grid items-center gap-2 md:grid-cols-[70px_1fr_1fr_1fr_1fr]">
                    <span class="text-sm font-semibold">{{ $label }}</span>
                    <input class="field" type="time" name="schedules[{{ $day }}][start]" value="{{ $start }}">
                    <input class="field" type="time" name="schedules[{{ $day }}][end]" value="{{ $end }}">
                    <input class="field" type="time" name="schedules[{{ $day }}][evening_start]" value="{{ $estart }}">
                    <input class="field" type="time" name="schedules[{{ $day }}][evening_end]" value="{{ $eend }}">
                </div>
            @endforeach
        </div>
        <button class="btn" type="submit">{{ __('ui.common.saved') }}</button>
    </form>
@endsection
