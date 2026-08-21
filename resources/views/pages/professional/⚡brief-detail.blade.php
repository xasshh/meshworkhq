<?php

use App\Enums\AlertStatus;
use App\Exceptions\AlreadyUnlockedException;
use App\Exceptions\BriefNotAvailableException;
use App\Exceptions\InsufficientCreditsException;
use App\Models\Alert;
use App\Models\Brief;
use App\Models\Unlock;
use App\Services\UnlockService;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Brief')] class extends Component
{
    public Brief $brief;

    public ?Alert $alert = null;

    public ?Unlock $unlock = null;

    public function mount(string $ulid): void
    {
        $this->brief = Brief::where('ulid', $ulid)->with(['client'])->firstOrFail();

        $this->loadProfessionalContext();

        if ($this->alert?->status === AlertStatus::Notified) {
            $this->alert->markViewed();
        }
    }

    public function unlockBrief(UnlockService $unlockService): void
    {
        try {
            $unlock = $unlockService->unlock(auth()->user(), $this->brief);

            $this->redirectRoute('professional.conversation', ['id' => $unlock->conversation?->id], navigate: true);
        } catch (AlreadyUnlockedException) {
            $this->loadProfessionalContext();
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

    private function loadProfessionalContext(): void
    {
        $this->alert = Alert::where('brief_id', $this->brief->id)
            ->where('professional_id', auth()->id())
            ->first();

        $this->unlock = Unlock::where('brief_id', $this->brief->id)
            ->where('professional_id', auth()->id())
            ->with('conversation')
            ->first();
    }
}; ?>

@php
    $isUnlocked = $unlock !== null;
    $matchedTags = collect($brief->skill_tags ?? [])
        ->intersect(collect(auth()->user()->skill_tags ?? []))
        ->values();
@endphp

<div class="shell-narrow py-8 sm:py-10">

    <a href="{{ route('professional.alerts') }}" wire:navigate
       class="inline-flex items-center gap-1.5 text-xs font-semibold text-ink-soft hover:text-ink transition-colors mb-5">
        <flux:icon name="arrow-left" variant="micro" />
        {{ __('Alert feed') }}
    </a>

    <article class="panel p-6 sm:p-8 grid gap-6">

        @if($brief->status->canReceivePitches())
            <x-wave-rail :brief="$brief" />
        @endif

        <div class="flex items-start justify-between gap-4 flex-wrap">
            <h1 class="font-display text-2xl text-ink leading-tight max-w-[30ch]">{{ $brief->title }}</h1>

            @if($isUnlocked)
                <span class="pill" data-tone="warn">{{ __('Unlocked by you') }}</span>
            @else
                <span class="pill" data-tone="{{ $brief->status->canReceivePitches() ? 'live' : 'muted' }}">{{ $brief->status->label() }}</span>
            @endif
        </div>

        <div class="flex flex-wrap gap-y-4 gap-x-8 py-5 border-y border-line-soft">
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
                <span class="fact-value">{{ $brief->published_at?->format('j M Y') ?? __('Not published') }}</span>
            </div>
            <div class="fact">
                <span class="fact-key">{{ __('Unlocked by') }}</span>
                <span class="fact-value">{{ $brief->total_unlocks }} {{ __('of') }} {{ $brief->waveAudience() }}</span>
            </div>
            @if($brief->expires_at)
                <div class="fact">
                    <span class="fact-key">{{ __('Expires') }}</span>
                    <span class="fact-value">{{ $brief->expires_at->format('j M Y') }}</span>
                </div>
            @endif
        </div>

        <div class="grid gap-3">
            <p class="eyebrow">{{ __('The brief') }}</p>
            <p class="text-sm text-ink leading-relaxed whitespace-pre-line max-w-prose">{{ $brief->description }}</p>
        </div>

        @if(! empty($brief->skill_tags))
            <div class="grid gap-3">
                <p class="eyebrow">{{ __('Skills asked for') }}</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($brief->skill_tags as $tag)
                        <span class="tag" @if($matchedTags->contains($tag)) data-hit="true" @endif>{{ $tag }}</span>
                    @endforeach
                </div>
                @if($matchedTags->isNotEmpty())
                    <p class="text-xs text-ink-faint">
                        {{ trans_choice('{1}:count skill on your profile matches|[2,*]:count skills on your profile match', $matchedTags->count(), ['count' => $matchedTags->count()]) }}
                    </p>
                @endif
            </div>
        @endif

        {{-- The client record: sealed or open --}}
        <div class="grid gap-3">
            <div class="flex items-center justify-between gap-3 flex-wrap">
                <p class="eyebrow">{{ __('Client') }}</p>
                <div class="flex items-center gap-2">
                    <x-verified-badge :user="$brief->client" />
                    <x-track-record :user="$brief->client" />
                </div>
            </div>

            @if($isUnlocked)
                <div class="seal" data-open="true">
                    <div class="seal-row">
                        <span class="seal-key">{{ __('Name') }}</span>
                        <span class="seal-value">{{ $brief->client->name }}</span>
                    </div>
                    <div class="seal-row">
                        <span class="seal-key">{{ __('Email') }}</span>
                        <span class="seal-value">{{ $brief->client->email }}</span>
                    </div>
                    @if($brief->client->phone)
                        <div class="seal-row">
                            <span class="seal-key">{{ __('Phone') }}</span>
                            <span class="seal-value">{{ $brief->client->phone }}</span>
                        </div>
                    @endif
                    @if($brief->client->company_name)
                        <div class="seal-row">
                            <span class="seal-key">{{ __('Company') }}</span>
                            <span class="seal-value">{{ $brief->client->company_name }}</span>
                        </div>
                    @endif
                </div>
            @else
                <div class="seal">
                    <div class="seal-row">
                        <span class="seal-key">{{ __('Name') }}</span>
                        <span class="seal-redact"></span>
                    </div>
                    <div class="seal-row">
                        <span class="seal-key">{{ __('Email') }}</span>
                        <span class="seal-redact" data-width="short"></span>
                    </div>
                    <div class="seal-row">
                        <span class="seal-key">{{ __('Company') }}</span>
                        <span class="seal-redact" data-width="short"></span>
                    </div>
                </div>
            @endif
        </div>

        {{-- Action --}}
        <div class="flex flex-wrap items-center justify-between gap-4 pt-5 border-t border-line-soft">
            @if($isUnlocked)
                <span class="text-xs text-ink-faint">
                    {{ __('Unlocked :when', ['when' => $unlock->unlocked_at?->format('j M Y, H:i') ?? __('recently')]) }}
                </span>
                @if($unlock->conversation)
                    <a href="{{ route('professional.conversation', ['id' => $unlock->conversation->id]) }}" wire:navigate
                       class="btn btn-ink btn-sm">
                        {{ $unlock->conversation->messages()->exists() ? __('Open thread') : __('Write your pitch') }}
                    </a>
                @endif
            @else
                <span class="text-xs text-ink-faint max-w-[38ch]">
                    {{ __('One credit reveals the client and opens a direct thread. Credits are free while Meshwork HQ is in early access.') }}
                </span>
                <button
                    type="button"
                    wire:click="unlockBrief"
                    wire:loading.attr="disabled"
                    @disabled(! $brief->isAvailableForUnlock())
                    class="btn btn-primary btn-sm"
                >
                    <span wire:loading.remove wire:target="unlockBrief">
                        {{ $brief->isAvailableForUnlock() ? __('Unlock this brief') : __('No longer taking pitches') }}
                    </span>
                    <span wire:loading wire:target="unlockBrief">{{ __('Unlocking') }}</span>
                    @if($brief->isAvailableForUnlock())
                        <span class="font-data text-[11px] font-semibold pl-2.5 border-l border-ink/25">1 {{ __('credit') }}</span>
                    @endif
                </button>
            @endif
        </div>
    </article>
</div>
