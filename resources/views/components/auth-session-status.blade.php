@props([
    'status',
])

@if ($status)
    <div {{ $attributes->merge(['class' => 'animate-fade-in-up']) }}>
        <div class="flex items-start gap-3 px-4 py-3.5 bg-emerald-soft border border-emerald-border rounded-xl">
            <div class="w-6 h-6 rounded-lg bg-emerald-main flex items-center justify-center shrink-0 mt-px">
                <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <p class="text-sm font-medium text-slate-700 leading-snug">{{ $status }}</p>
        </div>
    </div>
@endif
