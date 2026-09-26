@extends('layouts.site')

@section('content')
    <div class="py-20 text-center">
        <p class="font-display text-7xl text-forest">404</p>
        <p class="mt-3">This token is not on the board.</p>
        <a class="btn mt-6" href="{{ route('home') }}">{{ __('ui.nav.home') }}</a>
    </div>
@endsection
