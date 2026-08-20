@props([
    'status',
])

@if ($status)
    <div {{ $attributes->merge(['class' => 'animate-fade-in-up']) }}>
        <div class="flex items-start gap-3 px-4 py-3.5 bg-live-wash border border-live/30">
            <svg class="w-4 h-4 text-live shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            <p class="text-sm text-ink leading-snug">{{ $status }}</p>
        </div>
    </div>
@endif
