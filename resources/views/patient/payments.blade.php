@extends('layouts.panel')

@section('content')
    <h1 class="font-display text-4xl">{{ __('ui.patient.payments') }}</h1>
    <div class="mt-6 space-y-3">
        @foreach ($payments as $payment)
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-foam px-4 py-3">
                <div>
                    <p class="font-semibold">₹{{ number_format($payment->amount) }} · {{ $payment->clinic->name }}</p>
                    <p class="text-sm text-ink/60">{{ $payment->mode->label() }} · {{ $payment->status->label() }} @if($payment->transaction_id) · {{ $payment->transaction_id }} @endif</p>
                </div>
                @if ($payment->status->value === 'pending')
                    <form method="POST" action="{{ route('patient.payments.pay', $payment) }}">
                        @csrf
                        <button class="btn btn-sm" type="submit">{{ __('ui.patient.pay_now') }}</button>
                    </form>
                @endif
            </div>
        @endforeach
    </div>
    <div class="mt-6">{{ $payments->links() }}</div>
@endsection
