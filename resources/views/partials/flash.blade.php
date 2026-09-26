@if (session('status'))
    <div class="mb-4 rounded-2xl border border-canopy/20 bg-mist px-4 py-3 text-sm">{{ session('status') }}</div>
@endif
@if ($errors->any())
    <div class="mb-4 rounded-2xl border border-clay/30 bg-clay/10 px-4 py-3 text-sm text-clay">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif
