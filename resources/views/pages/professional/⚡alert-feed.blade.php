<?php

use App\Enums\AlertStatus;
use App\Exceptions\AlreadyUnlockedException;
use App\Exceptions\BriefNotAvailableException;
use App\Exceptions\InsufficientCreditsException;
use App\Models\Alert;
use App\Services\UnlockService;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Alert feed')] class extends Component
{
    use WithPagination;

    #[Url]
    public string $filter = 'all';

    #[Url]
    public string $sort = 'newest';

    public function updatingFilter(): void
    {
        $this->resetPage();
    }

    public function updatingSort(): void
    {
        $this->resetPage();
    }

    public function markViewed(int $alertId): void
    {
        Alert::where('id', $alertId)
            ->where('professional_id', auth()->id())
            ->firstOrFail()
            ->markViewed();
    }

    public function unlock(int $alertId, UnlockService $unlockService): void
    {
        $alert = Alert::with('brief')
            ->where('id', $alertId)
            ->where('professional_id', auth()->id())
            ->firstOrFail();

        try {
            $unlock = $unlockService->unlock(auth()->user(), $alert->brief);

            $this->redirectRoute('professional.conversation', ['id' => $unlock->conversation?->id], navigate: true);
        } catch (AlreadyUnlockedException) {
            Flux::toast(variant: 'warning', text: __('You have already unlocked this brief.'));
        } catch (InsufficientCreditsException $e) {
            Flux::toast(
                variant: 'danger',
                text: __('You need :required credit to unlock this. You have :available.', [
                    'required' => $e->required,
                    'available' => $e->available,
                ]),
            );
            $this->redirectRoute('professional.wallet', navigate: true);
        } catch (BriefNotAvailableException $e) {
            Flux::toast(variant: 'danger', text: $e->getMessage());
        }
    }

    public function render(): \Illuminate\View\View
    {
        $query = Alert::query()
            ->with(['brief.client', 'unlock.conversation'])
            ->where('professional_id', auth()->id())
            ->whereHas('brief');

        if ($this->filter === 'unread') {
            $query->where('status', AlertStatus::Notified);
        } elseif ($this->filter === 'unlocked') {
            $query->where('status', AlertStatus::Unlocked);
        }

        match ($this->sort) {
            'budget' => $query->join('briefs', 'briefs.id', '=', 'alerts.brief_id')
                ->orderByDesc('briefs.budget_max')
                ->select('alerts.*'),
            default => $query->latest('notified_at'),
        };

        return view('pages::professional.⚡alert-feed', [
            'alerts' => $query->paginate(20),
            'unreadCount' => Alert::where('professional_id', auth()->id())
                ->where('status', AlertStatus::Notified)
                ->count(),
        ]);
    }
}; ?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8 sm:py-10">

    <x-page-header
        :eyebrow="now()->format('l, j F')"
        :title="$unreadCount > 0
            ? trans_choice('{1}:count new brief matched you|[2,*]:count new briefs matched you', $unreadCount, ['count' => $unreadCount])
            : __('Alert feed')"
        :description="$unreadCount === 0 ? __('Nothing new right now. Matched briefs land here the moment they are published.') : null"
    >
        <x-slot:actions>
            <div class="flex gap-1" role="group" aria-label="{{ __('Filter alerts') }}">
                @foreach(['all' => __('All'), 'unread' => __('Unread'), 'unlocked' => __('Unlocked')] as $value => $label)
                    <button
                        type="button"
                        wire:click="$set('filter', '{{ $value }}')"
                        @if($filter === $value) aria-pressed="true" @endif
                        class="text-xs font-semibold px-2.5 py-1.5 border transition-colors {{ $filter === $value ? 'bg-ink text-paper border-ink' : 'border-line text-ink-soft hover:text-ink hover:border-ink-faint' }}"
                    >{{ $label }}</button>
                @endforeach
            </div>

            <select
                wire:model.live="sort"
                aria-label="{{ __('Sort alerts') }}"
                class="text-xs font-semibold border border-line bg-paper text-ink-soft py-1.5 pl-2.5 pr-7"
            >
                <option value="newest">{{ __('Newest first') }}</option>
                <option value="budget">{{ __('Highest budget') }}</option>
            </select>
        </x-slot:actions>
    </x-page-header>

    <div class="mt-6 grid gap-4 stagger" wire:key="alerts-{{ $filter }}-{{ $sort }}-{{ $alerts->currentPage() }}">

        @forelse($alerts as $alert)
            @php
                $brief = $alert->brief;
                $isUnlocked = $alert->status === AlertStatus::Unlocked;
                $matchedTags = collect($brief->skill_tags ?? [])
                    ->intersect(collect(auth()->user()->skill_tags ?? []))
                    ->values();
            @endphp

            <article
                wire:key="alert-{{ $alert->id }}"
                class="panel card-lift p-5 sm:p-6 grid gap-4"
                @if($alert->status === AlertStatus::Notified)
                    x-intersect.once="$wire.markViewed({{ $alert->id }})"
                @endif
            >
                @if($brief->status->canReceivePitches())
                    <x-wave-rail :brief="$brief" />
                @endif

                <div class="flex items-start justify-between gap-4 flex-wrap">
                    <h2 class="font-display text-lg text-ink leading-tight max-w-[34ch]">
                        <a href="{{ route('professional.brief.detail', ['ulid' => $brief->ulid]) }}" wire:navigate class="hover:text-brand-deep transition-colors">
                            {{ $brief->title }}
                        </a>
                    </h2>

                    @if($isUnlocked)
                        <span class="pill" data-tone="warn">{{ __('Unlocked by you') }}</span>
                    @elseif($brief->status->canReceivePitches())
                        <span class="pill" data-tone="live">{{ __('Taking pitches') }}</span>
                    @else
                        <span class="pill" data-tone="muted">{{ $brief->status->label() }}</span>
                    @endif
                </div>

                <p class="text-sm text-ink-soft leading-relaxed line-clamp-3 max-w-[60ch]">
                    {{ $brief->description }}
                </p>

                @if($matchedTags->isNotEmpty() || ! empty($brief->skill_tags))
                    <div class="flex flex-wrap gap-1.5">
                        @foreach(array_slice($brief->skill_tags ?? [], 0, 5) as $tag)
                            <span class="tag" @if($matchedTags->contains($tag)) data-hit="true" @endif>{{ $tag }}</span>
                        @endforeach
                    </div>
                @endif

                <div class="flex flex-wrap gap-y-3 gap-x-8">
                    <div class="fact">
                        <span class="fact-key">{{ __('Budget') }}</span>
                        <span class="fact-value">
                            @if($brief->budget_min && $brief->budget_max)
                                &#8358;{{ number_format($brief->budget_min) }} {{ __('to') }} &#8358;{{ number_format($brief->budget_max) }}
                            @elseif($brief->budget_max)
                                &#8358;{{ number_format($brief->budget_max) }}
                            @else
                                {{ __('Not stated') }}
                            @endif
                        </span>
                    </div>

                    <div class="fact">
                        <span class="fact-key">{{ __('Location') }}</span>
                        <span class="fact-value">{{ $brief->is_remote ? __('Remote') : ($brief->location ?: __('Not stated')) }}</span>
                    </div>

                    <div class="fact">
                        <span class="fact-key">{{ __('Posted') }}</span>
                        <span class="fact-value">{{ $brief->published_at?->diffForHumans(short: true) ?? __('Just now') }}</span>
                    </div>

                    <div class="fact">
                        <span class="fact-key">{{ __('Unlocked by') }}</span>
                        <span class="fact-value">{{ $brief->total_unlocks }} {{ __('of') }} {{ $brief->waveAudience() }}</span>
                    </div>
                </div>

                @unless($isUnlocked)
                    <div class="seal">
                        <div class="seal-row">
                            <span class="seal-key">{{ __('Client') }}</span>
                            <span class="seal-redact"></span>
                        </div>
                        <div class="seal-row">
                            <span class="seal-key">{{ __('Email') }}</span>
                            <span class="seal-redact" data-width="short"></span>
                        </div>
                    </div>
                @endunless

                <div class="flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-line-soft">
                    @if($isUnlocked)
                        <span class="text-[11px] text-ink-faint">
                            {{ __('Unlocked :when', ['when' => $alert->unlock?->unlocked_at?->format('j M, H:i') ?? __('recently')]) }}
                        </span>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('professional.brief.detail', ['ulid' => $brief->ulid]) }}" wire:navigate
                               class="text-xs font-semibold px-4 py-2.5 border border-line text-ink hover:border-ink-faint transition-colors">
                                {{ __('Open brief') }}
                            </a>
                            @if($alert->unlock?->conversation)
                                <a href="{{ route('professional.conversation', ['id' => $alert->unlock->conversation->id]) }}" wire:navigate
                                   class="btn-lift text-xs font-semibold px-4 py-2.5 bg-ink text-paper">
                                    {{ __('Open thread') }}
                                </a>
                            @endif
                        </div>
                    @else
                        <span class="text-[11px] text-ink-faint max-w-[36ch]">
                            {{ __('Credits are free while Meshwork HQ is in early access.') }}
                        </span>
                        <button
                            type="button"
                            wire:click="unlock({{ $alert->id }})"
                            wire:loading.attr="disabled"
                            wire:target="unlock({{ $alert->id }})"
                            @disabled(! $brief->isAvailableForUnlock())
                            class="btn-lift inline-flex items-center gap-2.5 text-xs font-semibold px-4 py-2.5 bg-brand-deep text-paper disabled:opacity-40 disabled:cursor-not-allowed"
                        >
                            <span wire:loading.remove wire:target="unlock({{ $alert->id }})">{{ __('Unlock this brief') }}</span>
                            <span wire:loading wire:target="unlock({{ $alert->id }})">{{ __('Unlocking') }}</span>
                            <span class="font-data text-[11px] font-semibold pl-2.5 border-l border-ink/25">1 {{ __('credit') }}</span>
                        </button>
                    @endif
                </div>
            </article>
        @empty
            <div class="panel p-10 sm:p-14 text-center grid gap-3 justify-items-center">
                <div class="w-10 h-10 border border-line grid place-items-center">
                    <flux:icon name="bell-alert" variant="micro" class="text-ink-faint" />
                </div>
                <h2 class="font-display text-lg text-ink">{{ __('No alerts yet') }}</h2>
                <p class="text-sm text-ink-soft max-w-[42ch]">
                    {{ __('Briefs matching your skills arrive here. Make sure your profile is complete so the matching engine can find you.') }}
                </p>
                <a href="{{ route('professional.profile') }}" wire:navigate
                   class="btn-lift mt-2 text-xs font-semibold px-4 py-2.5 bg-ink text-paper">
                    {{ __('Complete your profile') }}
                </a>
            </div>
        @endforelse

    </div>

    @if($alerts->hasPages())
        <div class="mt-8">
            {{ $alerts->links() }}
        </div>
    @endif
</div>
