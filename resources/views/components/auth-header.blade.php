@props([
    'title',
    'description',
])

<div class="flex w-full flex-col">
    <h1 class="font-display text-2xl text-ink leading-tight">{{ $title }}</h1>
    <p class="text-sm text-ink-soft mt-2 leading-relaxed">{{ $description }}</p>
</div>
