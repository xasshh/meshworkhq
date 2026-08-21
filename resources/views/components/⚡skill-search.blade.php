<?php

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public string $query = '';

    #[Computed]
    public function results(): \Illuminate\Support\Collection
    {
        if (strlen(trim($this->query)) < 2) {
            return collect();
        }

        $term = strtolower(trim($this->query));

        return User::where('role', Role::Professional)
            ->where(function ($q) use ($term) {
                $q->whereRaw('lower(skill_tags) like ?', ['%'.$term.'%'])
                    ->orWhereRaw('lower(professional_title) like ?', ['%'.$term.'%'])
                    ->orWhereRaw('lower(bio) like ?', ['%'.$term.'%']);
            })
            ->take(12)
            ->get();
    }
}; ?>

<div>
    {{-- Search --}}
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center gap-3 bg-paper border border-line px-4 py-3 focus-within:border-ink transition-colors duration-200">
            <svg class="w-4 h-4 text-ink-faint shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803a7.5 7.5 0 0010.607 10.607z"/>
            </svg>

            <input
                wire:model.live.debounce.350ms="query"
                type="search"
                placeholder="{{ __('Search by skill, for example Branding or Photography') }}"
                aria-label="{{ __('Search professionals by skill') }}"
                class="flex-1 bg-transparent text-ink placeholder-ink-faint text-sm focus:outline-none"
            />

            <div wire:loading.delay wire:target="query">
                <svg class="w-4 h-4 text-ink-faint animate-spin shrink-0" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
            </div>

            @if ($query)
                <button type="button" wire:click="$set('query', '')" class="shrink-0 text-ink-faint hover:text-ink transition-colors" aria-label="{{ __('Clear search') }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            @endif
        </div>
    </div>

    {{-- Results --}}
    @if (strlen(trim($query)) >= 2)
        <div class="mt-8" wire:loading.class="opacity-60" wire:target="query">
            @if ($this->results->isNotEmpty())
                <p class="eyebrow text-center mb-4">
                    {{ trans_choice('{1}:count professional matching ":term"|[2,*]:count professionals matching ":term"', $this->results->count(), ['count' => $this->results->count(), 'term' => $query]) }}
                </p>

                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-px bg-line border border-line">
                    @foreach ($this->results as $professional)
                        <article class="bg-paper p-5 grid gap-3 content-start" wire:key="result-{{ $professional->id }}">
                            <div class="flex items-start gap-3">
                                <div class="w-9 h-9 rounded-full bg-brand-wash border border-line grid place-items-center shrink-0 overflow-hidden">
                                    @if ($professional->avatar_path)
                                        <img src="{{ Storage::disk('public')->url($professional->avatar_path) }}" alt="" class="w-full h-full object-cover">
                                    @else
                                        <span class="font-display text-[11px] text-ink-faint">{{ $professional->initials() }}</span>
                                    @endif
                                </div>

                                <div class="min-w-0 flex-1">
                                    <h3 class="text-sm font-semibold text-ink truncate">{{ $professional->name }}</h3>
                                    @if ($professional->professional_title)
                                        <p class="text-xs text-ink-faint truncate">{{ $professional->professional_title }}</p>
                                    @endif
                                </div>
                            </div>

                            @if ($professional->bio)
                                <p class="text-sm text-ink-soft line-clamp-2">{{ $professional->bio }}</p>
                            @endif

                            @if ($professional->skill_tags)
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach (array_slice($professional->skill_tags, 0, 4) as $tag)
                                        <span class="tag">{{ $tag }}</span>
                                    @endforeach
                                    @if (count($professional->skill_tags) > 4)
                                        <span class="tag">+{{ count($professional->skill_tags) - 4 }}</span>
                                    @endif
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12 grid gap-2 justify-items-center">
                    <p class="text-sm font-semibold text-ink">{{ __('Nobody matches that yet') }}</p>
                    <p class="text-xs text-ink-faint max-w-[40ch]">{{ __('Try a broader skill, for example Design, Photography, or Development.') }}</p>
                </div>
            @endif
        </div>
    @endif
</div>
