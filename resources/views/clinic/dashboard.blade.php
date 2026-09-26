@extends('layouts.panel')

@section('content')
    <h1 class="font-display text-4xl">{{ $clinic->name }}</h1>
    @if ($clinic->status->value !== 'approved')
        <p class="mt-4 rounded-2xl bg-brass-soft px-4 py-3 text-sm">{{ __('ui.clinic.pending_note') }} @if($clinic->rejection_reason) {{ $clinic->rejection_reason }} @endif</p>
    @endif
    <div class="mt-6 grid gap-3 md:grid-cols-4">
        @foreach ([['ui.common.visits', $stats['total']], ['ui.panel.revenue', '₹'.number_format($stats['revenue'])], ['ui.panel.noshow', $stats['no_show_rate'].'%'], ['ui.status.cancelled', $stats['cancelled']]] as $card)
            <article class="rounded-3xl bg-foam p-5">
                <p class="text-xs uppercase tracking-[0.14em] text-ink/50">{{ __($card[0]) }}</p>
                <p class="mt-2 font-display text-4xl">{{ $card[1] }}</p>
            </article>
        @endforeach
    </div>
    <h2 class="mt-10 font-display text-2xl">{{ __('ui.panel.peak') }}</h2>
    @php $max = max(1, collect($stats['hours'])->max('count')); @endphp
    <div class="mt-4 flex h-40 items-end gap-2">
        @foreach ($stats['hours'] as $hour)
            <div class="flex flex-1 flex-col items-center gap-1">
                <div class="w-full rounded-t-lg bg-forest" style="height: {{ ($hour['count'] / $max) * 100 }}%"></div>
                <span class="text-[10px] text-ink/50">{{ $hour['label'] }}</span>
            </div>
        @endforeach
    </div>
    <h2 class="mt-10 font-display text-2xl">{{ __('ui.panel.performance') }}</h2>
    <div class="mt-4 overflow-x-auto rounded-[1.4rem] bg-foam">
        <table class="w-full text-left text-sm">
            <thead><tr class="border-b"><th class="p-3">Doctor</th><th>Visits</th><th>Done</th><th>Rating</th><th>Fees</th></tr></thead>
            <tbody>
                @foreach ($stats['doctors'] as $row)
                    <tr class="border-b border-ink/10">
                        <td class="p-3">{{ $row['name'] }}<br><span class="text-ink/50">{{ $row['specialization'] }}</span></td>
                        <td>{{ $row['booked'] }}</td>
                        <td>{{ $row['completed'] }}</td>
                        <td>★ {{ $row['rating'] }}</td>
                        <td>₹{{ number_format($row['revenue']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
