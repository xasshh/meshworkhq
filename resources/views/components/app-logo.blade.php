@props([
    // 'dark' renders the near black mark, for chalk and paper grounds.
    // 'light' renders the near white mark, for indigo grounds.
    'variant' => 'dark',
    'wordmark' => true,
    'href' => null,
    'markClass' => 'w-7 h-7',
])

@php
    $src = $variant === 'light'
        ? asset('images/meshwork-mark-light.png')
        : asset('images/meshwork-mark-dark.png');

    $wordColor = $variant === 'light' ? 'text-paper' : 'text-ink';

    // When the wordmark is present it already names the brand, so the image is
    // decorative and an alt would be announced twice.
    $alt = $wordmark ? '' : 'Meshwork HQ';
@endphp

<{{ $href ? 'a' : 'span' }}
    @if($href) href="{{ $href }}" @endif
    {{ $attributes->class(['inline-flex items-center gap-2.5 shrink-0']) }}
>
    <img
        src="{{ $src }}"
        alt="{{ $alt }}"
        width="150"
        height="150"
        class="{{ $markClass }} shrink-0"
    />

    @if($wordmark)
        <span class="font-display text-sm {{ $wordColor }} whitespace-nowrap">Meshwork HQ</span>
    @endif
</{{ $href ? 'a' : 'span' }}>
