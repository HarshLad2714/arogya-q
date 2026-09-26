@extends('layouts.panel')

@section('content')
    <h1 class="font-display text-4xl">{{ __('ui.panel.reviews') }}</h1>
    <div class="mt-6 space-y-4">
        @foreach ($reviews as $review)
            <article class="rounded-[1.4rem] bg-foam p-5">
                <p class="font-semibold">★ {{ $review->rating }} · {{ $review->patient->name }} · Dr. {{ $review->doctor->user->name }}</p>
                <p class="mt-2">{{ $review->comment }}</p>
                @if ($review->response)
                    <p class="mt-2 text-sm text-forest">{{ $review->response }}</p>
                @else
                    <form method="POST" action="{{ route('clinic.reviews.respond', $review) }}" class="mt-3 flex gap-2">
                        @csrf
                        <input class="field" name="response" placeholder="{{ __('ui.platform.reason') }}" required>
                        <button class="btn btn-sm" type="submit">Reply</button>
                    </form>
                @endif
            </article>
        @endforeach
    </div>
    <div class="mt-6">{{ $reviews->links() }}</div>
@endsection
