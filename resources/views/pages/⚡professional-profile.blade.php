<?php

use App\Enums\Role;
use App\Exceptions\BriefNotAvailableException;
use App\Models\Brief;
use App\Models\User;
use App\Services\AlertService;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::marketing')] class extends Component
{
    public User $professional;

    public ?int $briefId = null;

    public function mount(int $id): void
    {
        $this->professional = User::where('id', $id)
            ->where('role', Role::Professional)
            ->firstOrFail();

        $this->briefId = $this->invitableBriefs->first()?->id;
    }

    /**
     * A public directory page wants the person's name in the tab and in search
     * results, which a static #[Title] attribute cannot give us.
     */
    public function rendering(\Illuminate\View\View $view): void
    {
        $pro = $this->professional;
        $title = $pro->name.', '.($pro->professional_title ?: __('professional'));

        $view->title($title);

        $seo = app(\App\Support\Seo::class);

        $seo->set(description: str($pro->bio ?: __(':name is a :title available for work through Meshwork HQ.', [
            'name' => $pro->name,
            'title' => $pro->professional_title ?: __('professional'),
        ]))->squish()->limit(155)->value());

        if ($pro->avatar_path) {
            $seo->image(\Illuminate\Support\Facades\Storage::disk('public')->url($pro->avatar_path));
        }

        // A thin profile is worth a page for the person who owns it, but not
        // worth putting in front of a searcher.
        if (blank($pro->bio)) {
            $seo->noindex();
        }

        $seo->schema(array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'ProfilePage',
            'mainEntity' => array_filter([
                '@type' => 'Person',
                'name' => $pro->name,
                'jobTitle' => $pro->professional_title,
                'description' => $pro->bio,
                'url' => route('professionals.show', ['id' => $pro->id]),
                'image' => $pro->avatar_path
                    ? \Illuminate\Support\Facades\Storage::disk('public')->url($pro->avatar_path)
                    : null,
                'knowsAbout' => $pro->skill_tags ?: null,
                'workLocation' => ['@type' => 'Place', 'address' => ['@type' => 'PostalAddress', 'addressCountry' => 'NG']],
            ]),
        ]));
    }

    /**
     * Live briefs the viewing client could put this professional on.
     *
     * @return \Illuminate\Support\Collection<int, Brief>
     */
    #[Computed]
    public function invitableBriefs(): \Illuminate\Support\Collection
    {
        if (! auth()->check() || ! auth()->user()->isClient()) {
            return collect();
        }

        return Brief::where('client_id', auth()->id())
            ->active()
            ->latest('published_at')
            ->get()
            ->filter(fn (Brief $brief): bool => $brief->isAvailableForUnlock())
            ->values();
    }

    #[Computed]
    public function alreadyInvited(): bool
    {
        if ($this->briefId === null) {
            return false;
        }

        return \App\Models\Alert::where('brief_id', $this->briefId)
            ->where('professional_id', $this->professional->id)
            ->exists();
    }

    public function invite(AlertService $alerts): void
    {
        $brief = Brief::where('id', $this->briefId)
            ->where('client_id', auth()->id())
            ->firstOrFail();

        try {
            $alerts->inviteToBrief($brief, $this->professional);

            unset($this->alreadyInvited);

            Flux::toast(
                variant: 'success',
                text: __(':name has been alerted to your brief. They will unlock it to reach you.', [
                    'name' => $this->professional->name,
                ]),
            );
        } catch (BriefNotAvailableException $e) {
            Flux::toast(variant: 'danger', text: $e->getMessage());
        }
    }
}; ?>

@php
    $pro = $this->professional;
    $viewerIsClient = auth()->check() && auth()->user()->isClient();
@endphp

<div>
    <x-marketing-nav />

    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-10 sm:py-14">

        <x-breadcrumbs
            class="mb-6"
            :items="[
                ['label' => __('Home'), 'url' => route('home')],
                ['label' => __('Professionals'), 'url' => route('directory')],
                ['label' => $this->professional->name],
            ]"
        />

        <div class="grid lg:grid-cols-[1.4fr_1fr] gap-8 lg:gap-12 items-start">

            {{-- Profile --}}
            <div class="grid gap-8">
                <header class="flex items-start gap-5 flex-wrap sm:flex-nowrap">
                    <div class="w-20 h-20 bg-chalk border border-line grid place-items-center shrink-0 overflow-hidden">
                        @if($pro->avatar_path)
                            <img src="{{ Storage::disk('public')->url($pro->avatar_path) }}" alt="" class="w-full h-full object-cover">
                        @else
                            <span class="font-display text-xl text-ink-faint">{{ $pro->initials() }}</span>
                        @endif
                    </div>

                    <div class="min-w-0 grid gap-2">
                        <h1 class="font-display text-3xl text-ink leading-tight">{{ $pro->name }}</h1>
                        @if($pro->professional_title)
                            <p class="text-base text-ink-soft">{{ $pro->professional_title }}</p>
                        @endif
                        <div class="flex flex-wrap items-center gap-2 mt-1">
                            <x-verified-badge :user="$pro" />
                            <x-track-record :user="$pro" />
                        </div>
                    </div>
                </header>

                @if($pro->bio)
                    <section class="grid gap-3">
                        <p class="font-data text-[10px] uppercase tracking-[0.16em] text-ink-faint">{{ __('About') }}</p>
                        <p class="text-sm sm:text-base text-ink leading-relaxed whitespace-pre-line max-w-prose">{{ $pro->bio }}</p>
                    </section>
                @endif

                @if(! empty($pro->skill_tags))
                    <section class="grid gap-3">
                        <p class="font-data text-[10px] uppercase tracking-[0.16em] text-ink-faint">{{ __('Skills') }}</p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($pro->skill_tags as $tag)
                                <span class="tag">{{ $tag }}</span>
                            @endforeach
                        </div>
                        <p class="text-xs text-ink-faint">
                            {{ __('Briefs tagged with any of these reach :name automatically.', ['name' => str($pro->name)->before(' ')]) }}
                        </p>
                    </section>
                @endif

                @if($pro->portfolio_url)
                    <section class="grid gap-3">
                        <p class="font-data text-[10px] uppercase tracking-[0.16em] text-ink-faint">{{ __('Portfolio') }}</p>
                        <a href="{{ $pro->portfolio_url }}" target="_blank" rel="noopener noreferrer"
                           class="font-data text-sm text-brand-deep hover:underline break-all">
                            {{ $pro->portfolio_url }}
                        </a>
                    </section>
                @endif
            </div>

            {{-- Reaching them --}}
            <aside class="panel p-5 sm:p-6 grid gap-4 lg:sticky lg:top-24">
                <p class="font-data text-[10px] uppercase tracking-[0.16em] text-ink-faint">{{ __('Work with them') }}</p>

                @if($viewerIsClient && $this->invitableBriefs->isNotEmpty())
                    <p class="text-sm text-ink-soft leading-relaxed">
                        {{ __('Put :name on one of your live briefs. They are alerted straight away, and unlock it to message you.', ['name' => str($pro->name)->before(' ')]) }}
                    </p>

                    @if($this->invitableBriefs->count() > 1)
                        <flux:select wire:model.live="briefId" :label="__('Which brief?')">
                            @foreach($this->invitableBriefs as $brief)
                                <flux:select.option value="{{ $brief->id }}">{{ $brief->title }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    @else
                        <div class="bg-chalk-soft border border-line p-3">
                            <p class="font-data text-[10px] uppercase tracking-[0.14em] text-ink-faint mb-1">{{ __('Brief') }}</p>
                            <p class="text-sm text-ink">{{ $this->invitableBriefs->first()->title }}</p>
                        </div>
                    @endif

                    @if($this->alreadyInvited)
                        <p class="pill w-fit" data-tone="live">{{ __('Already alerted') }}</p>
                        <p class="text-xs text-ink-faint">
                            {{ __('They have this brief. If they have not replied, they may not have unlocked it yet.') }}
                        </p>
                    @else
                        <button type="button" wire:click="invite" wire:loading.attr="disabled"
                                class="btn-lift font-data text-[11px] uppercase tracking-[0.14em] font-semibold px-5 py-3.5 bg-brand-deep text-paper">
                            <span wire:loading.remove wire:target="invite">{{ __('Alert them to this brief') }}</span>
                            <span wire:loading wire:target="invite">{{ __('Sending') }}</span>
                        </button>
                    @endif

                @elseif($viewerIsClient)
                    <p class="text-sm text-ink-soft leading-relaxed">
                        {{ __('You have no live briefs right now. Post one and :name can be alerted to it.', ['name' => str($pro->name)->before(' ')]) }}
                    </p>
                    <a href="{{ route('client.brief.create') }}" wire:navigate
                       class="btn-lift font-data text-[11px] uppercase tracking-[0.14em] font-semibold px-5 py-3.5 bg-brand-deep text-paper text-center">
                        {{ __('Post a brief') }}
                    </a>

                @else
                    <p class="text-sm text-ink-soft leading-relaxed">
                        {{ __('Post a brief describing the work. :name is alerted if it matches their skills, and unlocks it to pitch you directly.', ['name' => str($pro->name)->before(' ')]) }}
                    </p>
                    <a href="{{ auth()->check() ? route('dashboard') : route('client.register') }}"
                       class="btn-lift font-data text-[11px] uppercase tracking-[0.14em] font-semibold px-5 py-3.5 bg-brand-deep text-paper text-center">
                        {{ __('Post a brief') }}
                    </a>
                    <p class="text-xs text-ink-faint">{{ __('Free to post. No commission on completed work.') }}</p>
                @endif
            </aside>
        </div>
    </div>

    <x-marketing-footer />
</div>
