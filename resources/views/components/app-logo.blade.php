@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-lg bg-emerald-main shadow-md shadow-emerald-main/30">
            <span class="text-white font-display font-bold text-sm leading-none">M</span>
        </x-slot>
        <span class="font-display font-semibold text-white tracking-tight">Meshwork <span class="text-emerald-main">HQ</span></span>
    </flux:sidebar.brand>
@else
    <flux:brand {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-lg bg-emerald-main shadow-md shadow-emerald-main/30">
            <span class="text-white font-display font-bold text-sm leading-none">M</span>
        </x-slot>
        <span class="font-display font-semibold text-white tracking-tight">Meshwork <span class="text-emerald-main">HQ</span></span>
    </flux:brand>
@endif
