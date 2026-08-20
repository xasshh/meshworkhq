<?php

use App\Enums\BriefStatus;
use App\Exceptions\BriefNotAvailableException;
use App\Models\Brief;
use App\Models\User;
use App\Services\BriefService;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Title('Brief')] class extends Component
{
    public Brief $brief;

    #[Url]
    public string $tab = 'brief';

    public function mount(string $ulid): void
    {
        $this->brief = Brief::where('ulid', $ulid)
            ->where('client_id', auth()->id())
            ->with(['unlocks.professional', 'unlocks.conversation'])
            ->firstOrFail();
    }

    public function hire(int $professionalId): void
    {
        $professional = User::findOrFail($professionalId);

        try {
            app(BriefService::class)->hire($this->brief, $professional);

            $this->refreshBrief();

            Flux::toast(
                variant: 'success',
                text: __('You hired :name. The brief is closed to new pitches.', ['name' => $professional->name]),
            );
        } catch (BriefNotAvailableException $e) {
            Flux::toast(variant: 'danger', text: $e->getMessage());
        }
    }

    public function publish(): void
    {
        try {
            app(BriefService::class)->publish($this->brief);

            $this->refreshBrief();

            Flux::toast(variant: 'success', text: __('Brief published. Matched professionals are being notified.'));
        } catch (BriefNotAvailableException $e) {
            Flux::toast(variant: 'danger', text: $e->getMessage());
        }
    }

    public function close(): void
    {
        try {
            app(BriefService::class)->close($this->brief);

            $this->refreshBrief();

            Flux::toast(variant: 'success', text: __('Brief closed.'));
        } catch (BriefNotAvailableException $e) {
            Flux::toast(variant: 'danger', text: $e->getMessage());
        }
    }

    /**
     * An audit trail assembled from what the brief already records, so the
     * client can see what the platform did on their behalf.
     *
     * @return array<int, array{when: \DateTimeInterface, what: string}>
     */
    #[Computed]
    public function activity(): array
    {
        $events = [];

        $events[] = ['when' => $this->brief->created_at, 'what' => __('Brief created as a draft')];

        if ($this->brief->published_at) {
            $events[] = ['when' => $this->brief->published_at, 'what' => __('Published and sent to the matching engine')];

            if ($this->brief->total_alerts_sent > 0) {
                $events[] = [
                    'when' => $this->brief->published_at,
                    'what' => trans_choice(
                        '{1}:count professional notified|[2,*]:count professionals notified',
                        $this->brief->total_alerts_sent,
                        ['count' => $this->brief->total_alerts_sent],
                    ),
                ];
            }
        }

        foreach ($this->brief->unlocks as $unlock) {
            $events[] = [
                'when' => $unlock->unlocked_at ?? $unlock->created_at,
                'what' => __(':name unlocked your brief', ['name' => $unlock->professional?->name ?? __('A professional')]),
            ];
        }

        if ($this->brief->status === BriefStatus::Hired) {
            $events[] = [
                'when' => $this->brief->updated_at,
                'what' => __('You hired :name', ['name' => $this->brief->hiredProfessional?->name ?? __('a professional')]),
            ];
        }

        usort($events, fn (array $a, array $b): int => $b['when'] <=> $a['when']);

        return $events;
    }

    private function refreshBrief(): void
    {
        $this->brief = $this->brief->fresh(['unlocks.professional', 'unlocks.conversation']);
    }
}; ?>

<div class="max-w-3xl mx-auto px-4 sm:px-6 py-8 sm:py-10">

    <a href="{{ route('client.briefs') }}" wire:navigate
       class="inline-flex items-center gap-1.5 text-xs font-semibold text-ink-soft hover:text-ink transition-colors mb-5">
        <flux:icon name="arrow-left" variant="micro" />
        {{ __('My briefs') }}
    </a>

    <header class="pb-6 border-b border-line">
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <h1 class="font-display text-2xl sm:text-3xl text-ink leading-tight max-w-[28ch]">{{ $brief->title }}</h1>
            <span class="pill" data-tone="{{ $brief->status->isActive() ? 'live' : 'muted' }}">{{ $brief->status->label() }}</span>
        </div>

        <div class="mt-5 flex flex-wrap gap-y-3 gap-x-8">
            <div class="fact">
                <span class="fact-key">{{ __('Pros notified') }}</span>
                <span class="fact-value">{{ $brief->total_alerts_sent }}</span>
            </div>
            <div class="fact">
                <span class="fact-key">{{ __('Unlocks') }}</span>
                <span class="fact-value">{{ $brief->total_unlocks }}</span>
            </div>
            <div class="fact">
                <span class="fact-key">{{ __('Posted') }}</span>
                <span class="fact-value">{{ $brief->published_at?->format('j M Y') ?? __('Draft') }}</span>
            </div>
            @if($brief->expires_at)
                <div class="fact">
                    <span class="fact-key">{{ __('Expires') }}</span>
                    <span class="fact-value">{{ $brief->expires_at->format('j M Y') }}</span>
                </div>
            @endif
        </div>

        @if($brief->status === BriefStatus::Draft || $brief->status->isActive())
            <div class="mt-5 flex flex-wrap gap-2">
                @if($brief->status === BriefStatus::Draft)
                    <button type="button" wire:click="publish" class="btn-lift text-xs font-semibold px-4 py-2.5 bg-ink text-paper">
                        {{ __('Publish brief') }}
                    </button>
                @else
                    <button type="button" wire:click="close"
                            wire:confirm="{{ __('Close this brief? No new professionals will be alerted.') }}"
                            class="text-xs font-semibold px-4 py-2.5 border border-line text-ink-soft hover:text-ink hover:border-ink-faint transition-colors">
                        {{ __('Close brief') }}
                    </button>
                @endif
            </div>
        @endif
    </header>

    {{-- Tabs, per spec section 4.2 --}}
    <div class="mt-6 flex gap-1 border-b border-line" role="tablist">
        @foreach(['brief' => __('Brief'), 'responses' => __('Responses'), 'activity' => __('Activity')] as $value => $label)
            <button
                type="button"
                role="tab"
                @if($tab === $value) aria-selected="true" @endif
                wire:click="$set('tab', '{{ $value }}')"
                class="text-xs font-semibold px-4 py-2.5 border-b-2 -mb-px transition-colors {{ $tab === $value ? 'border-brand text-ink' : 'border-transparent text-ink-faint hover:text-ink-soft' }}"
            >
                {{ $label }}
                @if($value === 'responses' && $brief->unlocks->count() > 0)
                    <span class="font-data text-[10px] ml-1">{{ $brief->unlocks->count() }}</span>
                @endif
            </button>
        @endforeach
    </div>

    <div class="mt-6">

        @if($tab === 'brief')
            <div class="panel p-6 grid gap-6">
                <div class="grid gap-3">
                    <p class="eyebrow">{{ __('What you asked for') }}</p>
                    <p class="text-sm text-ink leading-relaxed whitespace-pre-line max-w-prose">{{ $brief->description }}</p>
                </div>

                <div class="flex flex-wrap gap-y-4 gap-x-8 pt-5 border-t border-line-soft">
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
                </div>

                @if(! empty($brief->skill_tags))
                    <div class="grid gap-3 pt-5 border-t border-line-soft">
                        <p class="eyebrow">{{ __('Skills') }}</p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($brief->skill_tags as $tag)
                                <span class="tag">{{ $tag }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

        @elseif($tab === 'responses')
            @forelse($brief->unlocks as $unlock)
                <article class="panel p-5 mb-3 grid gap-4" wire:key="response-{{ $unlock->id }}">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-chalk-soft border border-line grid place-items-center shrink-0">
                            <span class="font-display text-xs text-ink-faint">{{ $unlock->professional?->initials() }}</span>
                        </div>

                        <div class="min-w-0 flex-1">
                            <h2 class="text-sm font-semibold text-ink">{{ $unlock->professional?->name }}</h2>
                            <p class="text-xs text-ink-faint mt-0.5">{{ $unlock->professional?->professional_title ?: __('Professional') }}</p>
                        </div>

                        @if($brief->hired_professional_id === $unlock->professional_id)
                            <span class="pill" data-tone="live">{{ __('Hired') }}</span>
                        @else
                            <x-track-record :user="$unlock->professional" />
                        @endif
                    </div>

                    @if($unlock->professional?->bio)
                        <p class="text-sm text-ink-soft line-clamp-3">{{ $unlock->professional->bio }}</p>
                    @endif

                    @if(! empty($unlock->professional?->skill_tags))
                        <div class="flex flex-wrap gap-1.5">
                            @foreach(array_slice($unlock->professional->skill_tags, 0, 5) as $tag)
                                <span class="tag" @if(in_array($tag, $brief->skill_tags ?? [], true)) data-hit="true" @endif>{{ $tag }}</span>
                            @endforeach
                        </div>
                    @endif

                    <div class="flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-line-soft">
                        <span class="font-data text-[11px] text-ink-faint">
                            {{ __('Unlocked :when', ['when' => $unlock->unlocked_at?->format('j M, H:i') ?? '']) }}
                        </span>

                        <div class="flex flex-wrap gap-2">
                            @if($unlock->conversation)
                                <a href="{{ route('client.conversation', ['id' => $unlock->conversation->id]) }}" wire:navigate
                                   class="text-xs font-semibold px-4 py-2.5 border border-line text-ink hover:border-ink-faint transition-colors">
                                    {{ __('Open thread') }}
                                </a>
                            @endif

                            @if(in_array($brief->status, [BriefStatus::ReceivingPitches, BriefStatus::Shortlisting], true))
                                <button type="button"
                                        wire:click="hire({{ $unlock->professional_id }})"
                                        wire:confirm="{{ __('Hire :name? This closes the brief to new pitches.', ['name' => $unlock->professional?->name]) }}"
                                        class="btn-lift text-xs font-semibold px-4 py-2.5 bg-ink text-paper">
                                    {{ __('Hire') }}
                                </button>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="panel p-10 sm:p-14 text-center grid gap-3 justify-items-center">
                    <h2 class="font-display text-lg text-ink">{{ __('No responses yet') }}</h2>
                    <p class="text-sm text-ink-soft max-w-[44ch]">
                        @if($brief->status === BriefStatus::Draft)
                            {{ __('This brief is still a draft. Publish it and matched professionals will be notified within seconds.') }}
                        @else
                            {{ __('Professionals have been notified. Each one who wants the job pays a credit to reach you, so responses arrive deliberately rather than all at once.') }}
                        @endif
                    </p>
                </div>
            @endforelse

        @else
            <div class="panel p-6">
                <ol class="grid gap-0">
                    @foreach($this->activity as $event)
                        <li class="flex gap-4 py-3 border-b border-line-soft last:border-b-0">
                            <span class="font-data text-[10px] text-ink-faint whitespace-nowrap pt-0.5 w-28 shrink-0">
                                {{ $event['when']?->format('j M, H:i') }}
                            </span>
                            <span class="text-sm text-ink-soft">{{ $event['what'] }}</span>
                        </li>
                    @endforeach
                </ol>
            </div>
        @endif
    </div>
</div>
