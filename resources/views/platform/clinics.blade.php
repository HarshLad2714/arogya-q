@extends('layouts.panel')

@section('content')
    <h1 class="font-display text-4xl">{{ __('ui.platform.clinics') }}</h1>
    <div class="mt-4 flex gap-2 text-sm">
        @foreach (['' => 'All', 'pending' => __('ui.status.pending'), 'approved' => __('ui.status.approved'), 'rejected' => __('ui.status.rejected')] as $value => $label)
            <a class="pill {{ $status === $value ? 'bg-forest text-foam' : '' }}" href="{{ route('platform.clinics', ['status' => $value]) }}">{{ $label }}</a>
        @endforeach
    </div>
    <div class="mt-6 space-y-4">
        @foreach ($clinics as $clinic)
            <article class="rounded-[1.4rem] bg-foam p-5">
                <div class="flex flex-wrap justify-between gap-3">
                    <div>
                        <h2 class="font-display text-2xl">{{ $clinic->name }}</h2>
                        <p class="text-sm text-ink/60">{{ $clinic->specialty }} · {{ $clinic->city }} · {{ $clinic->admin?->name }} · {{ $clinic->admin?->mobile }}</p>
                        <p class="mt-1 text-sm">{{ $clinic->status->label() }} · {{ $clinic->doctors_count }} {{ __('ui.clinic.doctors') }}</p>
                        @if ($clinic->rejection_reason)
                            <p class="mt-1 text-sm text-clay">{{ $clinic->rejection_reason }}</p>
                        @endif
                        @foreach ($clinic->documents ?? [] as $doc)
                            <a class="text-sm underline" href="{{ asset('storage/'.$doc) }}" target="_blank">Document</a>
                        @endforeach
                    </div>
                    <div class="space-y-2">
                        <form method="POST" action="{{ route('platform.clinics.approve', $clinic) }}">@csrf<button class="btn btn-sm" type="submit">{{ __('ui.platform.approve') }}</button></form>
                        <form method="POST" action="{{ route('platform.clinics.reject', $clinic) }}" class="flex gap-2">
                            @csrf
                            <input class="field" name="reason" placeholder="{{ __('ui.platform.reason') }}" required>
                            <button class="btn btn-sm btn-clay" type="submit">{{ __('ui.platform.reject') }}</button>
                        </form>
                    </div>
                </div>
            </article>
        @endforeach
    </div>
    <div class="mt-6">{{ $clinics->links() }}</div>
@endsection
