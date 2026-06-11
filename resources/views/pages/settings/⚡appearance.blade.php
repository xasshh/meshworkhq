<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Appearance settings')] class extends Component
{
    //
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Appearance settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Appearance')" :subheading="__('Meshwork HQ uses a crisp light theme designed for clarity and readability')">
        <div class="flex items-center gap-3.5 px-4 py-3.5 bg-emerald-soft border border-emerald-border rounded-xl max-w-md">
            <div class="w-8 h-8 rounded-lg bg-white border border-emerald-border flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-emerald-main" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-slate-main">{{ __('Light theme active') }}</p>
                <p class="text-xs text-slate-500 mt-0.5">{{ __('Additional themes are on our roadmap.') }}</p>
            </div>
        </div>
    </x-pages::settings.layout>
</section>
