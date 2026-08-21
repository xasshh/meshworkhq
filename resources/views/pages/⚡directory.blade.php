<?php

use App\Enums\Role;
use App\Models\Skill;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts::marketing')] #[Title('Find professionals')] class extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $skill = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSkill(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->skill = '';
        $this->resetPage();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Skill>
     */
    #[Computed]
    public function skills(): \Illuminate\Database\Eloquent\Collection
    {
        return Skill::active()->get();
    }

    public function render(): \Illuminate\View\View
    {
        // A missing bio should not make someone invisible: supply is thin
        // early on, and a titled profile is still worth surfacing.
        $query = User::query()
            ->where('role', Role::Professional)
            ->whereNotNull('professional_title');

        if ($this->search !== '') {
            $term = '%'.$this->search.'%';

            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('professional_title', 'like', $term)
                    ->orWhere('bio', 'like', $term);
            });
        }

        if ($this->skill !== '') {
            $query->whereJsonContains('skill_tags', $this->skill);
        }

        // Fullest profiles first, so the directory leads with the people a
        // client can actually judge rather than whoever signed up last.
        $query->orderByRaw('CASE WHEN bio IS NULL OR bio = \'\' THEN 1 ELSE 0 END')
            ->orderByRaw('CASE WHEN avatar_path IS NULL THEN 1 ELSE 0 END')
            ->latest();

        return view('pages::⚡directory', [
            'professionals' => $query->paginate(9),
        ]);
    }
}; ?>

<div class="min-h-screen">

    <x-marketing-nav />

    <div class="shell py-10 sm:py-14">

        <header class="pb-6 border-b border-line">
            <p class="eyebrow mb-3">{{ __('Directory') }}</p>
            <h1 class="font-display text-3xl sm:text-4xl text-ink leading-none">{{ __('Professionals on Meshwork HQ') }}</h1>
            <p class="mt-4 text-sm text-ink-soft max-w-prose">
                {{ __('Every professional here has a complete profile and is matched to briefs by skill. Browse them, or post a brief and let the right ones come to you.') }}
            </p>
        </header>

        {{-- Filters --}}
        <div class="mt-6 grid sm:grid-cols-[1fr_auto_auto] gap-2">
            <flux:input
                wire:model.live.debounce.400ms="search"
                :placeholder="__('Search by name, title or bio')"
                type="search"
            />

            <select
                wire:model.live="skill"
                aria-label="{{ __('Filter by skill') }}"
                class="text-sm border border-line bg-paper text-ink-soft py-2 pl-3 pr-8"
            >
                <option value="">{{ __('All skills') }}</option>
                @foreach($this->skills as $skillOption)
                    <option value="{{ $skillOption->name }}">{{ $skillOption->name }}</option>
                @endforeach
            </select>

            @if($search !== '' || $skill !== '')
                <button type="button" wire:click="clearFilters"
                        class="text-xs font-semibold px-4 py-2 border border-line text-ink-soft hover:text-ink hover:border-ink-faint transition-colors">
                    {{ __('Clear') }}
                </button>
            @endif
        </div>

        {{-- Results --}}
        @if($professionals->total() > 0)
            <p class="mt-6 eyebrow">
                {{ trans_choice('{1}:count professional|[2,*]:count professionals', $professionals->total(), ['count' => number_format($professionals->total())]) }}
            </p>

            {{-- Separate bordered cards rather than a gap-px mosaic: a part
                 filled last row then reads as a short row, not a missing tile. --}}
            <div class="mt-4 grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($professionals as $professional)
                    <a
                        href="{{ route('professionals.show', ['id' => $professional->id]) }}"
                        wire:navigate
                        wire:key="pro-{{ $professional->id }}"
                        class="panel card-lift p-5 grid gap-3 content-start group"
                    >
                        <div class="flex items-start gap-3">
                            <div class="w-11 h-11 rounded-full bg-brand-wash border border-line grid place-items-center shrink-0 overflow-hidden">
                                @if($professional->avatar_path)
                                    <img src="{{ Storage::disk('public')->url($professional->avatar_path) }}" alt="" class="w-full h-full object-cover">
                                @else
                                    <span class="font-display text-sm text-ink-faint">{{ $professional->initials() }}</span>
                                @endif
                            </div>

                            <div class="min-w-0 flex-1">
                                <h2 class="text-sm font-semibold text-ink truncate group-hover:text-brand-deep transition-colors">
                                    {{ $professional->name }}
                                </h2>
                                <p class="text-xs text-ink-faint truncate">{{ $professional->professional_title }}</p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-1.5">
                            <x-verified-badge :user="$professional" />
                            <x-track-record :user="$professional" />
                        </div>

                        @if($professional->bio)
                            <p class="text-sm text-ink-soft line-clamp-3">{{ $professional->bio }}</p>
                        @endif

                        @if(! empty($professional->skill_tags))
                            <div class="flex flex-wrap gap-1.5">
                                @foreach(array_slice($professional->skill_tags, 0, 3) as $tag)
                                    <span class="tag">{{ $tag }}</span>
                                @endforeach
                                @if(count($professional->skill_tags) > 3)
                                    <span class="tag">+{{ count($professional->skill_tags) - 3 }}</span>
                                @endif
                            </div>
                        @endif

                        <span class="font-sans text-[10px] font-bold uppercase tracking-[0.1em] text-brand-deep mt-1">
                            {{ __('View profile') }}
                        </span>
                    </a>
                @endforeach
            </div>

            @if($professionals->hasPages())
                <div class="mt-8">{{ $professionals->links() }}</div>
            @endif
        @else
            <div class="mt-6 empty-state">
                <h2 class="font-display text-lg text-ink">{{ __('Nobody matches that yet') }}</h2>
                <p class="text-sm text-ink-soft max-w-[44ch]">
                    {{ __('Try a different skill or clear the search. The directory grows as professionals complete their profiles.') }}
                </p>
                @if($search !== '' || $skill !== '')
                    <button type="button" wire:click="clearFilters" class="btn btn-ink btn-sm mt-2">
                        {{ __('Clear filters') }}
                    </button>
                @endif
            </div>
        @endif

    </div>

    <x-marketing-footer />
</div>
