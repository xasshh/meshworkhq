@props([
    'items' => [],
    'eyebrow' => null,
    'heading' => null,
    'dark' => false,
])

@php
    // The same questions drive both the visible list and the structured data,
    // so the markup a crawler reads can never drift from what a person sees.
    // Google treats a mismatch between the two as a reason to drop the rich
    // result entirely.
    app(App\Support\Seo::class)->schema([
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => collect($items)->map(fn (array $item): array => [
            '@type' => 'Question',
            'name' => $item['q'],
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => $item['a'],
            ],
        ])->values()->all(),
    ]);
@endphp

<section {{ $attributes->class(['border-b border-line' => ! $dark, 'bg-navy' => $dark]) }}>
    <div class="shell-wide py-14 sm:py-20 grid lg:grid-cols-[0.8fr_1.2fr] gap-10 lg:gap-16 items-start">

        <div class="grid gap-3 lg:sticky lg:top-24">
            @if($eyebrow)
                <p class="font-data text-[10px] uppercase tracking-[0.18em] {{ $dark ? 'text-brand-lit' : 'text-brand-deep' }}">
                    {{ $eyebrow }}
                </p>
            @endif

            <h2 class="font-display-caps text-3xl sm:text-4xl {{ $dark ? 'text-paper' : 'text-ink' }}">
                {{ $heading ?? __('Questions') }}
            </h2>
        </div>

        <div class="grid" x-data="{ open: 0 }">
            @foreach($items as $index => $item)
                <div class="border-t {{ $dark ? 'border-paper/15' : 'border-line' }} @if($loop->last) border-b @endif">
                    <h3>
                        <button
                            type="button"
                            @click="open = open === {{ $index }} ? null : {{ $index }}"
                            :aria-expanded="open === {{ $index }} ? 'true' : 'false'"
                            aria-controls="faq-answer-{{ $index }}"
                            class="w-full flex items-start justify-between gap-6 py-5 text-left group"
                        >
                            <span class="text-base sm:text-lg font-semibold {{ $dark ? 'text-paper' : 'text-ink' }} leading-snug">
                                {{ $item['q'] }}
                            </span>

                            <span class="shrink-0 mt-1 w-5 h-5 relative" aria-hidden="true">
                                <span class="absolute inset-x-0 top-1/2 h-0.5 -translate-y-1/2 {{ $dark ? 'bg-brand-lit' : 'bg-brand-deep' }}"></span>
                                <span class="absolute inset-y-0 left-1/2 w-0.5 -translate-x-1/2 {{ $dark ? 'bg-brand-lit' : 'bg-brand-deep' }} transition-transform duration-200"
                                      :class="open === {{ $index }} && 'scale-y-0'"></span>
                            </span>
                        </button>
                    </h3>

                    {{-- Rendered always, hidden visually when collapsed: the answer
                         must exist in the HTML for the FAQ schema to be honest. --}}
                    <div
                        id="faq-answer-{{ $index }}"
                        x-show="open === {{ $index }}"
                        x-collapse
                        @if($index !== 0) x-cloak @endif
                    >
                        <p class="pb-6 pr-10 text-sm sm:text-base {{ $dark ? 'text-paper/65' : 'text-ink-soft' }} leading-relaxed max-w-[62ch]">
                            {{ $item['a'] }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
