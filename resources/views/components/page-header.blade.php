@props([
    'eyebrow' => null,
    'title',
    'description' => null,
])

<header {{ $attributes->class(['flex flex-wrap items-end justify-between gap-x-8 gap-y-4 pb-6 border-b border-line']) }}>
    <div class="min-w-0">
        @if($eyebrow)
            <p class="eyebrow mb-2">{{ $eyebrow }}</p>
        @endif

        <h1 class="font-display text-2xl sm:text-3xl text-ink leading-none">{{ $title }}</h1>

        @if($description)
            <p class="mt-3 text-sm text-ink-soft max-w-prose">{{ $description }}</p>
        @endif
    </div>

    @if(isset($actions))
        <div class="flex flex-wrap items-center gap-2 shrink-0">
            {{ $actions }}
        </div>
    @endif
</header>
