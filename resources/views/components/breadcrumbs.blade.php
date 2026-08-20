@props(['items' => []])

@php
    // The visible trail and the structured data come from one array, so a
    // crawler is never shown a path a person cannot see.
    app(App\Support\Seo::class)->schema([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => collect($items)->values()->map(fn (array $item, int $i): array => array_filter([
            '@type' => 'ListItem',
            'position' => $i + 1,
            'name' => $item['label'],
            'item' => $item['url'] ?? null,
        ]))->all(),
    ]);
@endphp

<nav {{ $attributes->class(['flex items-center flex-wrap gap-x-2 gap-y-1']) }} aria-label="{{ __('Breadcrumb') }}">
    @foreach($items as $index => $item)
        @if(! $loop->first)
            <span class="font-data text-[10px] text-ink-faint" aria-hidden="true">/</span>
        @endif

        @if(isset($item['url']) && ! $loop->last)
            <a href="{{ $item['url'] }}" wire:navigate
               class="font-data text-[10px] uppercase tracking-[0.14em] text-ink-soft hover:text-ink transition-colors">
                {{ $item['label'] }}
            </a>
        @else
            <span class="font-data text-[10px] uppercase tracking-[0.14em] text-ink-faint" @if($loop->last) aria-current="page" @endif>
                {{ $item['label'] }}
            </span>
        @endif
    @endforeach
</nav>
