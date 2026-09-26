<div class="flex items-center gap-1 text-xs font-semibold tracking-wide">
    @foreach (['en' => 'EN', 'hi' => 'हि', 'gu' => 'ગુ'] as $code => $label)
        <a href="{{ route('locale.switch', $code) }}" class="rounded-full px-2 py-1 {{ app()->getLocale() === $code ? 'bg-forest text-foam' : 'text-ink/70' }}">{{ $label }}</a>
    @endforeach
</div>
