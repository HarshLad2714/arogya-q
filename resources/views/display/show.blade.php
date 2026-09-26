@extends('layouts.display')

@section('content')
    <main class="min-h-screen px-6 py-8 md:px-10" data-display="{{ route('live.clinic', $clinic) }}" data-ws="{{ \App\Support\QueueSocket::url() }}">
        <header class="flex items-end justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-[0.22em] text-[#f3e2b8]">{{ __('ui.brand') }}</p>
                <h1 class="font-display text-4xl md:text-6xl">{{ $clinic->name }}</h1>
            </div>
            <p class="font-display text-4xl text-[#f3e2b8]" data-clock></p>
        </header>
        <div class="mt-8 grid gap-6 lg:grid-cols-2" data-boards></div>
    </main>
@endsection
