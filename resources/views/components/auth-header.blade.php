@props([
    'title',
    'description',
])

<div class="flex w-full flex-col">
    <h1 class="font-display text-[26px] font-bold text-slate-main tracking-tight">{{ $title }}</h1>
    <p class="text-sm text-slate-400 mt-1.5 leading-relaxed">{{ $description }}</p>
</div>
