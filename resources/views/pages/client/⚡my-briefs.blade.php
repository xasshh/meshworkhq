<?php

use App\Enums\BriefStatus;
use App\Exceptions\BriefNotAvailableException;
use App\Models\Brief;
use App\Services\BriefService;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('My briefs')] class extends Component
{
    use WithPagination;

    #[Url]
    public string $status = 'all';

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function publish(int $briefId): void
    {
        $brief = $this->ownedBrief($briefId);

        try {
            app(BriefService::class)->publish($brief);

            Flux::toast(
                variant: 'success',
                text: __(':title is live. Matched professionals are being notified.', ['title' => $brief->title]),
            );
        } catch (BriefNotAvailableException $e) {
            Flux::toast(variant: 'danger', text: $e->getMessage());
        }
    }

    public function close(int $briefId): void
    {
        try {
            app(BriefService::class)->close($this->ownedBrief($briefId));

            Flux::toast(variant: 'success', text: __('Brief closed.'));
        } catch (BriefNotAvailableException $e) {
            Flux::toast(variant: 'danger', text: $e->getMessage());
        }
    }

    private function ownedBrief(int $briefId): Brief
    {
        return Brief::where('id', $briefId)
            ->where('client_id', auth()->id())
            ->firstOrFail();
    }

    public function render(): \Illuminate\View\View
    {
        $query = Brief::where('client_id', auth()->id())->withCount('unlocks');

        match ($this->status) {
            'draft' => $query->where('status', BriefStatus::Draft),
            'live' => $query->active(),
            'closed' => $query->whereIn('status', [BriefStatus::Hired, BriefStatus::Closed, BriefStatus::Expired]),
            default => null,
        };

        return view('pages::client.⚡my-briefs', [
            'briefs' => $query->latest()->paginate(15),
        ]);
    }
}; ?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8 sm:py-10">

    <x-page-header
        :eyebrow="__('Work')"
        :title="__('My briefs')"
        :description="__('Every brief you have posted, and how each one is performing.')"
    >
        <x-slot:actions>
            <div class="flex gap-1" role="group" aria-label="{{ __('Filter briefs') }}">
                @foreach(['all' => __('All'), 'draft' => __('Drafts'), 'live' => __('Live'), 'closed' => __('Closed')] as $value => $label)
                    <button
                        type="button"
                        wire:click="$set('status', '{{ $value }}')"
                        @if($status === $value) aria-pressed="true" @endif
                        class="text-xs font-semibold px-2.5 py-1.5 border transition-colors {{ $status === $value ? 'bg-ink text-paper border-ink' : 'border-line text-ink-soft hover:text-ink hover:border-ink-faint' }}"
                    >{{ $label }}</button>
                @endforeach
            </div>

            <a href="{{ route('client.brief.create') }}" wire:navigate class="btn-lift text-xs font-semibold px-4 py-2.5 bg-ink text-paper">
                {{ __('Post a brief') }}
            </a>
        </x-slot:actions>
    </x-page-header>

    @if($briefs->total() > 0)
        <div class="mt-6 grid gap-4 stagger" wire:key="briefs-{{ $status }}-{{ $briefs->currentPage() }}">
            @foreach($briefs as $brief)
                <article class="panel card-lift p-5 sm:p-6 grid gap-4" wire:key="brief-{{ $brief->id }}">

                    @if($brief->status->canReceivePitches())
                        <x-wave-rail :brief="$brief" />
                    @endif

                    <div class="flex items-start justify-between gap-4 flex-wrap">
                        <h2 class="font-display text-lg text-ink leading-tight max-w-[34ch]">
                            <a href="{{ route('client.brief.detail', ['ulid' => $brief->ulid]) }}" wire:navigate class="hover:text-brand-deep transition-colors">
                                {{ $brief->title }}
                            </a>
                        </h2>
                        <span class="pill" data-tone="{{ $brief->status->isActive() ? 'live' : ($brief->status === \App\Enums\BriefStatus::Draft ? 'warn' : 'muted') }}">
                            {{ $brief->status->label() }}
                        </span>
                    </div>

                    <p class="text-sm text-ink-soft line-clamp-2 max-w-[60ch]">{{ $brief->description }}</p>

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
                            <span class="fact-key">{{ __('Pros notified') }}</span>
                            <span class="fact-value">{{ $brief->total_alerts_sent }}</span>
                        </div>
                        <div class="fact">
                            <span class="fact-key">{{ __('Pitches') }}</span>
                            <span class="fact-value">{{ $brief->unlocks_count }}</span>
                        </div>
                        <div class="fact">
                            <span class="fact-key">{{ $brief->published_at ? __('Posted') : __('Created') }}</span>
                            <span class="fact-value">{{ ($brief->published_at ?? $brief->created_at)->format('j M Y') }}</span>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-line-soft">
                        <a href="{{ route('client.brief.detail', ['ulid' => $brief->ulid]) }}" wire:navigate
                           class="text-xs font-semibold text-ink-soft hover:text-ink transition-colors">
                            {{ $brief->unlocks_count > 0 ? trans_choice('{1}View :count response|[2,*]View :count responses', $brief->unlocks_count, ['count' => $brief->unlocks_count]) : __('View brief') }}
                        </a>

                        <div class="flex flex-wrap gap-2">
                            @if($brief->status === \App\Enums\BriefStatus::Draft)
                                <button type="button" wire:click="publish({{ $brief->id }})"
                                        class="btn-lift text-xs font-semibold px-4 py-2.5 bg-ink text-paper">
                                    {{ __('Publish') }}
                                </button>
                            @elseif($brief->status->isActive())
                                <button type="button" wire:click="close({{ $brief->id }})"
                                        wire:confirm="{{ __('Close this brief? No new professionals will be alerted.') }}"
                                        class="text-xs font-semibold px-4 py-2.5 border border-line text-ink-soft hover:text-ink hover:border-ink-faint transition-colors">
                                    {{ __('Close') }}
                                </button>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        @if($briefs->hasPages())
            <div class="mt-8">{{ $briefs->links() }}</div>
        @endif
    @else
        <div class="mt-6 panel p-10 sm:p-14 text-center grid gap-3 justify-items-center">
            <div class="w-10 h-10 border border-line grid place-items-center">
                <flux:icon name="document-text" variant="micro" class="text-ink-faint" />
            </div>
            <h2 class="font-display text-lg text-ink">
                {{ $status === 'all' ? __('No briefs yet') : __('Nothing in this filter') }}
            </h2>
            <p class="text-sm text-ink-soft max-w-[44ch]">
                {{ __('Describe the work, set a budget, pick the skills. The ten best matched professionals hear about it within seconds.') }}
            </p>
            <a href="{{ route('client.brief.create') }}" wire:navigate class="btn-lift mt-2 text-xs font-semibold px-4 py-2.5 bg-ink text-paper">
                {{ __('Post your first brief') }}
            </a>
        </div>
    @endif
</div>
