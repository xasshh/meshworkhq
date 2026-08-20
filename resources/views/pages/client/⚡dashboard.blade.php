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

<div class="max-w-5xl mx-auto px-4 sm:px-6 py-8 sm:py-10">

    <x-page-header
        :eyebrow="now()->format('l, j F')"
        :title="__('Good to see you, :name', ['name' => str(auth()->user()->name)->before(' ')])"
        :description="$this->hasEverPosted ? __('Where your briefs stand today.') : null"
    >
        <x-slot:actions>
            <a href="{{ route('client.brief.create') }}" wire:navigate class="btn-lift text-xs font-semibold px-4 py-2.5 bg-ink text-paper">
                {{ __('Post a brief') }}
            </a>
        </x-slot:actions>
    </x-page-header>

    @unless($this->hasEverPosted)
        {{-- First run. One job on this screen. --}}
        <section class="mt-6 panel p-8 sm:p-12 grid gap-4 max-w-2xl">
            <p class="eyebrow">{{ __('Getting started') }}</p>
            <h2 class="font-display text-2xl text-ink leading-tight">{{ __('Post your first brief') }}</h2>
            <p class="text-sm text-ink-soft max-w-prose">
                {{ __('Describe the work, set a budget, and pick the skills it needs. Within seconds the ten best matched professionals in Nigeria are notified, and the ones who want the job pay to reach you. Posting is free.') }}
            </p>
            <div class="flex flex-wrap gap-2 mt-2">
                <a href="{{ route('client.brief.create') }}" wire:navigate class="btn-lift text-xs font-semibold px-5 py-2.5 bg-ink text-paper">
                    {{ __('Write a brief') }}
                </a>
                <a href="{{ route('directory') }}" wire:navigate class="text-xs font-semibold px-5 py-2.5 border border-line text-ink hover:border-ink-faint transition-colors">
                    {{ __('Browse professionals first') }}
                </a>
            </div>
        </section>
    @else
        {{-- Stats --}}
        <div class="mt-6 grid grid-cols-2 lg:grid-cols-4 gap-px bg-line border border-line">
            @php
                $tiles = [
                    ['label' => __('Active briefs'), 'value' => $this->stats['activeBriefs'], 'href' => route('client.briefs')],
                    ['label' => __('Pitches received'), 'value' => $this->stats['totalPitches'], 'href' => route('client.briefs')],
                    ['label' => __('Pros notified'), 'value' => $this->stats['alertsSent'], 'href' => route('client.briefs')],
                    ['label' => __('Unread messages'), 'value' => $this->stats['unreadMessages'], 'href' => route('client.messages')],
                ];
            @endphp

            @foreach($tiles as $tile)
                <a href="{{ $tile['href'] }}" wire:navigate class="bg-paper p-5 grid gap-2 hover:bg-chalk-soft transition-colors">
                    <span class="eyebrow">{{ $tile['label'] }}</span>
                    <span class="font-data text-3xl font-medium text-ink leading-none">{{ number_format($tile['value']) }}</span>
                </a>
            @endforeach
        </div>

        <div class="mt-6 grid lg:grid-cols-5 gap-6 items-start">

            {{-- Active briefs --}}
            <section class="lg:col-span-3 panel">
                <div class="flex items-center justify-between gap-4 p-5 border-b border-line-soft">
                    <h2 class="font-display text-base text-ink">{{ __('Active briefs') }}</h2>
                    <a href="{{ route('client.briefs') }}" wire:navigate class="text-xs font-semibold text-ink-soft hover:text-ink transition-colors">{{ __('See all') }}</a>
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
                    <div class="p-8 text-center grid gap-3 justify-items-center">
                        <p class="text-sm text-ink-soft">{{ __('No briefs are live right now.') }}</p>
                        <a href="{{ route('client.brief.create') }}" wire:navigate class="btn-lift text-xs font-semibold px-4 py-2.5 bg-ink text-paper">
                            {{ __('Post a brief') }}
                        </a>
                    </div>
                @endforelse
            </section>

            {{-- Recent pitches --}}
            <section class="lg:col-span-2 panel">
                <div class="flex items-center justify-between gap-4 p-5 border-b border-line-soft">
                    <h2 class="font-display text-base text-ink">{{ __('Who reached out') }}</h2>
                    <a href="{{ route('client.messages') }}" wire:navigate class="text-xs font-semibold text-ink-soft hover:text-ink transition-colors">{{ __('Messages') }}</a>
                </div>

                @forelse($this->recentPitches as $unlock)
                    <div class="flex items-start gap-3 px-5 py-4 border-b border-line-soft last:border-b-0" wire:key="pitch-{{ $unlock->id }}">
                        <div class="w-8 h-8 bg-chalk-soft border border-line grid place-items-center shrink-0">
                            <span class="font-display text-[10px] text-ink-faint">{{ $unlock->professional?->initials() }}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold text-ink truncate">{{ $unlock->professional?->name }}</p>
                            <p class="text-[11px] text-ink-faint truncate">{{ $unlock->professional?->professional_title ?: __('Professional') }}</p>
                            @if($unlock->conversation)
                                <a href="{{ route('client.conversation', ['id' => $unlock->conversation->id]) }}" wire:navigate
                                   class="inline-block mt-1.5 text-[11px] font-semibold text-ink-soft hover:text-ink transition-colors">
                                    {{ __('Open thread') }}
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="p-5 text-xs text-ink-faint">{{ __('No professional has unlocked a brief yet.') }}</p>
                @endforelse
            </section>
        </div>
    @endunless
</div>
