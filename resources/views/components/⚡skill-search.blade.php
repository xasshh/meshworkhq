<?php

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public string $query = '';

    #[Computed]
    public function results(): \Illuminate\Database\Eloquent\Collection
    {
        if (strlen(trim($this->query)) < 2) {
            return collect();
        }

        $term = trim($this->query);

        return User::where('role', Role::Professional)
            ->where(function ($q) use ($term) {
                $q->whereRaw('lower(skill_tags) like ?', ['%'.strtolower($term).'%'])
                    ->orWhereRaw('lower(professional_title) like ?', ['%'.strtolower($term).'%'])
                    ->orWhereRaw('lower(bio) like ?', ['%'.strtolower($term).'%']);
            })
            ->take(12)
            ->get();
    }
};
?>

<div>
    {{-- Search bar --}}
    <div class="relative max-w-2xl mx-auto">
        <div class="flex items-center gap-3 bg-white border border-slate-200 rounded-2xl px-5 py-4 shadow-sm focus-within:border-emerald-main focus-within:ring-2 focus-within:ring-emerald-main/20 transition-all duration-200">
            <svg class="w-5 h-5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803a7.5 7.5 0 0010.607 10.607z"/>
            </svg>
            <input
                wire:model.live.debounce.350ms="query"
                type="search"
                placeholder="Search by skill — e.g. Branding, Web Development, Photography..."
                class="flex-1 bg-transparent text-slate-main placeholder-slate-400 text-sm focus:outline-none"
            />
            <div wire:loading.delay wire:target="query">
                <svg class="w-4 h-4 text-slate-300 animate-spin shrink-0" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
            </div>
            @if ($query)
                <button wire:click="$set('query', '')" class="shrink-0 text-slate-300 hover:text-slate-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            @endif
        </div>
    </div>

    {{-- Results --}}
    @if (strlen(trim($query)) >= 2)
        <div class="mt-10" wire:loading.class="opacity-60" wire:target="query">
            @if ($this->results->isNotEmpty())
                <div class="mb-6 text-center">
                    <p class="text-sm text-slate-500">
                        Found <span class="font-semibold text-slate-main">{{ $this->results->count() }}</span>
                        professional{{ $this->results->count() !== 1 ? 's' : '' }} matching
                        <span class="font-semibold text-emerald-deep">"{{ $query }}"</span>
                    </p>
                </div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach ($this->results as $professional)
                        <div class="bg-white border border-slate-200 rounded-2xl p-5 hover:border-emerald-main/50 hover:shadow-md transition-all duration-200 group">
                            <div class="flex items-start gap-3 mb-3">
                                @if ($professional->avatar_path)
                                    <img src="{{ Storage::url($professional->avatar_path) }}"
                                         class="w-12 h-12 rounded-xl object-cover shrink-0"
                                         alt="{{ $professional->name }}" />
                                @else
                                    <div class="w-12 h-12 rounded-xl bg-slate-main flex items-center justify-center text-white font-semibold text-sm shrink-0 group-hover:bg-emerald-main transition-colors duration-200">
                                        {{ $professional->initials() }}
                                    </div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-slate-main truncate">{{ $professional->name }}</p>
                                    @if ($professional->professional_title)
                                        <p class="text-xs text-emerald-deep font-medium mt-0.5">{{ $professional->professional_title }}</p>
                                    @endif
                                </div>
                            </div>
                            @if ($professional->bio)
                                <p class="text-xs text-slate-500 leading-relaxed mb-3 line-clamp-2">{{ $professional->bio }}</p>
                            @endif
                            @if ($professional->skill_tags)
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach (array_slice($professional->skill_tags, 0, 4) as $tag)
                                        <span class="text-[10px] font-medium bg-slate-50 border border-slate-200 text-slate-500 px-2 py-0.5 rounded-full">{{ $tag }}</span>
                                    @endforeach
                                    @if (count($professional->skill_tags) > 4)
                                        <span class="text-[10px] font-medium text-slate-400 self-center">+{{ count($professional->skill_tags) - 4 }} more</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-14">
                    <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803a7.5 7.5 0 0010.607 10.607z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-semibold text-slate-600 mb-1">No professionals found</p>
                    <p class="text-xs text-slate-400">Try a different skill like "Design", "Photography", or "Development"</p>
                </div>
            @endif
        </div>
    @endif
</div>
