<?php

use App\Models\Brief;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Unlock;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Overview')] class extends Component
{
    /**
     * @return array<string, int>
     */
    #[Computed]
    public function stats(): array
    {
        $briefIds = Brief::where('client_id', auth()->id())->pluck('id');

        return [
            'activeBriefs' => Brief::where('client_id', auth()->id())->active()->count(),
            'totalPitches' => Unlock::whereIn('brief_id', $briefIds)->count(),
            'alertsSent' => (int) Brief::where('client_id', auth()->id())->sum('total_alerts_sent'),
            'unreadMessages' => Message::whereHas('conversation', fn ($q) => $q->where('client_id', auth()->id()))
                ->where('sender_id', '!=', auth()->id())
                ->whereNull('read_at')
                ->count(),
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Brief>
     */
    #[Computed]
    public function activeBriefs(): \Illuminate\Database\Eloquent\Collection
    {
        return Brief::where('client_id', auth()->id())
            ->active()
            ->withCount('unlocks')
            ->latest('published_at')
            ->take(4)
            ->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Unlock>
     */
    #[Computed]
    public function recentPitches(): \Illuminate\Database\Eloquent\Collection
    {
        return Unlock::with(['professional', 'brief', 'conversation'])
            ->whereIn('brief_id', Brief::where('client_id', auth()->id())->pluck('id'))
            ->whereHas('brief')
            ->latest('unlocked_at')
            ->take(5)
            ->get();
    }

    #[Computed]
    public function hasEverPosted(): bool
    {
        return Brief::where('client_id', auth()->id())->exists();
    }
}; ?>

<div class="shell py-8 sm:py-10">

    <x-page-header
        :eyebrow="now()->format('l, j F')"
        :title="__('Good to see you, :name', ['name' => str(auth()->user()->name)->before(' ')])"
        :description="$this->hasEverPosted ? __('Where your briefs stand today.') : null"
    >
        <x-slot:actions>
            <a href="{{ route('client.brief.create') }}" wire:navigate class="btn btn-primary">
                <flux:icon name="plus" variant="micro" />
                {{ __('Post a brief') }}
            </a>
        </x-slot:actions>
    </x-page-header>

    @unless($this->hasEverPosted)
        {{-- First run. One job on this screen, so nothing competes with it. --}}
        <section class="mt-6 panel-accent p-8 sm:p-12 grid gap-4 max-w-2xl">
            <span class="icon-badge" data-tone="brand" data-size="lg">
                <flux:icon name="document-plus" class="w-5 h-5" />
            </span>
            <p class="eyebrow">{{ __('Getting started') }}</p>
            <h2 class="font-display text-2xl text-ink leading-tight">{{ __('Post your first brief') }}</h2>
            <p class="text-sm text-ink-soft max-w-prose">
                {{ __('Describe the work, set a budget, and pick the skills it needs. Within seconds the ten best matched professionals in Nigeria are notified, and the ones who want the job pay to reach you. Posting is free.') }}
            </p>
            <div class="flex flex-wrap gap-2 mt-2">
                <a href="{{ route('client.brief.create') }}" wire:navigate class="btn btn-primary">
                    {{ __('Write a brief') }}
                </a>
                <a href="{{ route('directory') }}" wire:navigate class="btn btn-ghost">
                    {{ __('Browse professionals first') }}
                </a>
            </div>
        </section>
    @else
        {{-- Where things stand. Pitches received carry the warm accent: that is
             the number that means someone paid to reach you. --}}
        <div class="mt-6 grid grid-cols-2 lg:grid-cols-4 gap-3">
            @php
                $tiles = [
                    ['label' => __('Active briefs'), 'value' => $this->stats['activeBriefs'], 'href' => route('client.briefs'), 'icon' => 'document-text', 'tone' => 'brand'],
                    ['label' => __('Pitches received'), 'value' => $this->stats['totalPitches'], 'href' => route('client.briefs'), 'icon' => 'hand-raised', 'tone' => 'ember'],
                    ['label' => __('Pros notified'), 'value' => $this->stats['alertsSent'], 'href' => route('client.briefs'), 'icon' => 'signal', 'tone' => 'default'],
                    ['label' => __('Unread messages'), 'value' => $this->stats['unreadMessages'], 'href' => route('client.messages'), 'icon' => 'chat-bubble-left-right', 'tone' => 'default'],
                ];
            @endphp

            @foreach($tiles as $tile)
                <a href="{{ $tile['href'] }}" wire:navigate class="stat" data-tone="{{ $tile['tone'] }}">
                    <span class="icon-badge" data-tone="{{ $tile['tone'] }}">
                        <flux:icon name="{{ $tile['icon'] }}" variant="micro" />
                    </span>
                    <span class="stat-value">{{ number_format($tile['value']) }}</span>
                    <span class="eyebrow">{{ $tile['label'] }}</span>
                </a>
            @endforeach
        </div>

        <div class="mt-6 grid lg:grid-cols-5 gap-6 items-start">

            {{-- Active briefs --}}
            <section class="lg:col-span-3 panel overflow-hidden">
                <div class="flex items-center justify-between gap-4 p-5 border-b border-line-soft">
                    <h2 class="font-display text-base text-ink">{{ __('Active briefs') }}</h2>
                    <a href="{{ route('client.briefs') }}" wire:navigate
                       class="inline-flex items-center gap-1 text-xs font-semibold text-brand-deep hover:text-ink transition-colors">
                        {{ __('See all') }}
                        <flux:icon name="arrow-right" variant="micro" />
                    </a>
                </div>

                @forelse($this->activeBriefs as $brief)
                    <article class="p-5 border-b border-line-soft last:border-b-0 grid gap-3" wire:key="brief-{{ $brief->id }}">
                        <x-wave-rail :brief="$brief" />

                        <div class="flex items-start justify-between gap-3 flex-wrap">
                            <h3 class="text-sm font-semibold text-ink leading-snug max-w-[38ch]">
                                <a href="{{ route('client.brief.detail', ['ulid' => $brief->ulid]) }}" wire:navigate class="hover:text-brand-deep transition-colors">
                                    {{ $brief->title }}
                                </a>
                            </h3>
                            <span class="pill" data-tone="live">{{ $brief->status->label() }}</span>
                        </div>

                        <div class="flex flex-wrap gap-y-2 gap-x-6">
                            <div class="fact">
                                <span class="fact-key">{{ __('Pitches') }}</span>
                                <span class="fact-value">{{ $brief->unlocks_count }}</span>
                            </div>
                            <div class="fact">
                                <span class="fact-key">{{ __('Pros notified') }}</span>
                                <span class="fact-value">{{ $brief->total_alerts_sent }}</span>
                            </div>
                            <div class="fact">
                                <span class="fact-key">{{ __('Expires') }}</span>
                                <span class="fact-value">{{ $brief->expires_at?->format('j M') ?? '' }}</span>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="m-5 empty-state">
                        <span class="icon-badge" data-tone="brand" data-size="lg">
                            <flux:icon name="document-text" class="w-5 h-5" />
                        </span>
                        <p class="font-display text-base text-ink">{{ __('No briefs are live right now') }}</p>
                        <p class="text-sm text-ink-soft max-w-[42ch]">
                            {{ __('Post one and the ten best matched professionals hear about it within seconds.') }}
                        </p>
                        <a href="{{ route('client.brief.create') }}" wire:navigate class="btn btn-primary btn-sm mt-1">
                            {{ __('Post a brief') }}
                        </a>
                    </div>
                @endforelse
            </section>

            {{-- Recent pitches --}}
            <section class="lg:col-span-2 panel overflow-hidden">
                <div class="flex items-center justify-between gap-4 p-5 border-b border-line-soft">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="icon-badge" data-tone="ember">
                            <flux:icon name="hand-raised" variant="micro" />
                        </span>
                        <h2 class="font-display text-base text-ink">{{ __('Who reached out') }}</h2>
                    </div>
                    <a href="{{ route('client.messages') }}" wire:navigate
                       class="text-xs font-semibold text-brand-deep hover:text-ink transition-colors shrink-0">{{ __('Messages') }}</a>
                </div>

                @forelse($this->recentPitches as $unlock)
                    <div class="flex items-start gap-3 px-5 py-4 border-b border-line-soft last:border-b-0" wire:key="pitch-{{ $unlock->id }}">
                        <div class="w-9 h-9 rounded-full bg-brand-wash border border-line grid place-items-center shrink-0">
                            <span class="font-display text-[11px] text-brand-deep">{{ $unlock->professional?->initials() }}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold text-ink truncate">{{ $unlock->professional?->name }}</p>
                            <p class="text-[11px] text-ink-faint truncate">{{ $unlock->professional?->professional_title ?: __('Professional') }}</p>
                            @if($unlock->conversation)
                                <a href="{{ route('client.conversation', ['id' => $unlock->conversation->id]) }}" wire:navigate
                                   class="inline-flex items-center gap-1 mt-1.5 text-[11px] font-semibold text-brand-deep hover:text-ink transition-colors">
                                    {{ __('Open thread') }}
                                    <flux:icon name="arrow-right" variant="micro" />
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="m-5 empty-state">
                        <span class="icon-badge" data-tone="ember" data-size="lg">
                            <flux:icon name="hand-raised" class="w-5 h-5" />
                        </span>
                        <p class="text-sm text-ink-soft max-w-[36ch]">
                            {{ __('No professional has unlocked a brief yet. They pay a credit to reach you, so the ones who do are serious.') }}
                        </p>
                    </div>
                @endforelse
            </section>
        </div>
    @endunless
</div>
