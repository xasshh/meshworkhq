@props([
    'href',
    'current' => false,
    'icon' => null,
    'badge' => 0,
])

@php
    $badge = (int) $badge;
@endphp

<a
    href="{{ $href }}"
    wire:navigate
    @if($current) aria-current="page" @endif
    {{ $attributes->class([
        'group flex items-center gap-2.5 px-2.5 py-2 text-sm border-l-2 transition-colors duration-150',
        'border-brand bg-chalk-soft text-ink font-semibold' => $current,
        'border-transparent text-ink-soft hover:text-ink hover:bg-chalk-soft' => ! $current,
    ]) }}
>
    @if($icon)
        <flux:icon :name="$icon" variant="micro" class="shrink-0 {{ $current ? 'text-ink' : 'text-ink-faint group-hover:text-ink-soft' }}" />
    @endif

    <span class="flex-1 truncate">{{ $slot }}</span>

    @if($badge > 0)
        <span class="font-data text-[10px] font-semibold leading-none px-1.5 py-1 bg-brand-deep text-paper">
            {{ $badge > 99 ? '99+' : $badge }}
        </span>
    @endif
</a>
