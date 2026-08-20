<?php

use App\Enums\AlertStatus;
use App\Models\Alert;
use App\Models\CreditTransaction;
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
        return [
            'newAlerts' => Alert::forProfessional(auth()->id())->unread()->count(),
            'credits' => (int) auth()->user()->credits,
            'pitches' => Unlock::where('professional_id', auth()->id())->count(),
            'completeness' => auth()->user()->profileCompleteness(),
        ];
    }

    /**
     * Engagement funnel for the last 30 days (spec section 16.1).
     *
     * @return array<string, int>
     */
    #[Computed]
    public function funnel(): array
    {
        $base = Alert::forProfessional(auth()->id())
            ->where('notified_at', '>=', now()->subDays(30));

        return [
            'received' => (clone $base)->count(),
            'viewed' => (clone $base)->whereIn('status', [AlertStatus::Viewed, AlertStatus::Unlocked])->count(),
            'unlocked' => (clone $base)->where('status', AlertStatus::Unlocked)->count(),
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Alert>
     */
    #[Computed]
    public function recentAlerts(): \Illuminate\Database\Eloquent\Collection
    {
        return Alert::with(['brief'])
            ->forProfessional(auth()->id())
            ->whereHas('brief')
            ->latest('notified_at')
            ->take(3)
            ->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, CreditTransaction>
     */
    #[Computed]
    public function recentTransactions(): \Illuminate\Database\Eloquent\Collection
    {
        return CreditTransaction::where('user_id', auth()->id())
            ->latest()
            ->take(5)
            ->get();
    }

    #[Computed]
    public function unreadMessages(): int
    {
        return Message::whereHas('conversation', fn ($q) => $q->where('professional_id', auth()->id()))
            ->where('sender_id', '!=', auth()->id())
            ->whereNull('read_at')
            ->count();
    }
}; ?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 py-8 sm:py-10">

    <x-page-header
        :eyebrow="now()->format('l, j F')"
        :title="__('Good to see you, :name', ['name' => str(auth()->user()->name)->before(' ')])"
        :description="__('Where things stand this morning.')"
    >
        <x-slot:actions>
            <a href="{{ route('professional.alerts') }}" wire:navigate class="btn-lift text-xs font-semibold px-4 py-2.5 bg-ink text-paper">
                {{ __('Go to alert feed') }}
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- Profile gate. The single most consequential thing on this page. --}}
    @if($this->stats['completeness'] < 70)
        <div class="mt-6 panel p-5 border-l-2 border-l-brand grid gap-2">
            <p class="font-display text-base text-ink">{{ __('Your alerts are paused') }}</p>
            <p class="text-sm text-ink-soft max-w-prose">
                {{ __('The matching engine only sends briefs to profiles that are at least 70% complete. Yours is at :pct%.', ['pct' => $this->stats['completeness']]) }}
            </p>
            <a href="{{ route('professional.profile') }}" wire:navigate class="btn-lift w-fit mt-2 text-xs font-semibold px-4 py-2.5 bg-brand-deep text-paper">
                {{ __('Finish your profile') }}
            </a>
        </div>
    @endif

    {{-- Stats --}}
    <div class="mt-6 grid grid-cols-2 lg:grid-cols-4 gap-px bg-line border border-line">
        @php
            $tiles = [
                ['label' => __('New alerts'), 'value' => $this->stats['newAlerts'], 'href' => route('professional.alerts')],
                ['label' => __('Credits'), 'value' => $this->stats['credits'], 'href' => route('professional.wallet')],
                ['label' => __('Pitches sent'), 'value' => $this->stats['pitches'], 'href' => route('professional.pitches')],
                ['label' => __('Unread messages'), 'value' => $this->unreadMessages, 'href' => route('professional.messages')],
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

        {{-- Recent alerts --}}
        <section class="lg:col-span-3 panel">
            <div class="flex items-center justify-between gap-4 p-5 border-b border-line-soft">
                <h2 class="font-display text-base text-ink">{{ __('Latest matches') }}</h2>
                <a href="{{ route('professional.alerts') }}" wire:navigate class="text-xs font-semibold text-ink-soft hover:text-ink transition-colors">{{ __('See all') }}</a>
            </div>

            @forelse($this->recentAlerts as $alert)
                <article class="p-5 border-b border-line-soft last:border-b-0 grid gap-3" wire:key="overview-alert-{{ $alert->id }}">
                    @if($alert->brief->status->canReceivePitches())
                        <x-wave-rail :brief="$alert->brief" />
                    @endif

                    <div class="flex items-start justify-between gap-3 flex-wrap">
                        <h3 class="text-sm font-semibold text-ink leading-snug max-w-[38ch]">
                            <a href="{{ route('professional.brief.detail', ['ulid' => $alert->brief->ulid]) }}" wire:navigate class="hover:text-brand-deep transition-colors">
                                {{ $alert->brief->title }}
                            </a>
                        </h3>
                        @if($alert->status === AlertStatus::Unlocked)
                            <span class="pill" data-tone="warn">{{ __('Unlocked') }}</span>
                        @endif
                    </div>

                    <div class="flex flex-wrap gap-y-2 gap-x-6">
                        <div class="fact">
                            <span class="fact-key">{{ __('Budget') }}</span>
                            <span class="fact-value">
                                @if($alert->brief->budget_max)
                                    &#8358;{{ number_format($alert->brief->budget_max) }}
                                @else
                                    {{ __('Not stated') }}
                                @endif
                            </span>
                        </div>
                        <div class="fact">
                            <span class="fact-key">{{ __('Posted') }}</span>
                            <span class="fact-value">{{ $alert->brief->published_at?->diffForHumans(short: true) ?? __('Just now') }}</span>
                        </div>
                    </div>
                </article>
            @empty
                <div class="p-8 text-center grid gap-2">
                    <p class="text-sm text-ink-soft">{{ __('No matched briefs yet.') }}</p>
                    <p class="text-xs text-ink-faint">{{ __('They will appear here as soon as a client posts something in your skills.') }}</p>
                </div>
            @endforelse
        </section>

        <div class="lg:col-span-2 grid gap-6">

            {{-- Funnel --}}
            <section class="panel p-5 grid gap-4">
                <div>
                    <h2 class="font-display text-base text-ink">{{ __('Last 30 days') }}</h2>
                    <p class="text-xs text-ink-faint mt-1">{{ __('How your alerts are converting.') }}</p>
                </div>

                @php
                    $funnel = $this->funnel;
                    $peak = max(1, $funnel['received']);
                    $steps = [
                        ['label' => __('Received'), 'value' => $funnel['received']],
                        ['label' => __('Viewed'), 'value' => $funnel['viewed']],
                        ['label' => __('Unlocked'), 'value' => $funnel['unlocked']],
                    ];
                @endphp

                <div class="grid gap-3">
                    @foreach($steps as $step)
                        <div class="grid gap-1.5">
                            <div class="flex items-baseline justify-between gap-3">
                                <span class="text-xs text-ink-soft">{{ $step['label'] }}</span>
                                <span class="font-data text-xs font-medium text-ink">{{ $step['value'] }}</span>
                            </div>
                            <div class="h-1.5 bg-line">
                                <div class="h-full bg-brand transition-all duration-700" style="width: {{ round($step['value'] / $peak * 100) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Credit activity --}}
            <section class="panel">
                <div class="flex items-center justify-between gap-4 p-5 border-b border-line-soft">
                    <h2 class="font-display text-base text-ink">{{ __('Credit activity') }}</h2>
                    <a href="{{ route('professional.wallet') }}" wire:navigate class="text-xs font-semibold text-ink-soft hover:text-ink transition-colors">{{ __('Wallet') }}</a>
                </div>

                @forelse($this->recentTransactions as $transaction)
                    <div class="flex items-center justify-between gap-3 px-5 py-3 border-b border-line-soft last:border-b-0" wire:key="tx-{{ $transaction->id }}">
                        <div class="min-w-0">
                            <p class="text-xs text-ink truncate">{{ $transaction->description ?: $transaction->type->value }}</p>
                            <p class="text-[10px] text-ink-faint font-data mt-0.5">{{ $transaction->created_at->format('j M, H:i') }}</p>
                        </div>
                        <span class="font-data text-xs font-semibold shrink-0 {{ $transaction->amount > 0 ? 'text-live' : 'text-ink-soft' }}">
                            {{ $transaction->amount > 0 ? '+' : '' }}{{ $transaction->amount }}
                        </span>
                    </div>
                @empty
                    <p class="p-5 text-xs text-ink-faint">{{ __('No credit activity yet.') }}</p>
                @endforelse
            </section>
        </div>
    </div>
</div>
