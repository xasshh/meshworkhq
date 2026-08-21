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
            <a href="{{ route('professional.alerts') }}" wire:navigate class="btn btn-primary">
                <flux:icon name="bell-alert" variant="micro" />
                {{ __('Go to alert feed') }}
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- Profile gate. The single most consequential thing on this page, so it
         gets the accent and sits above everything else. --}}
    @if($this->stats['completeness'] < 70)
        <div class="mt-6 panel-accent p-5 flex items-start gap-4">
            <span class="icon-badge" data-tone="brand" data-size="lg">
                <flux:icon name="exclamation-triangle" class="w-5 h-5" />
            </span>
            <div class="min-w-0 grid gap-2">
                <p class="font-display text-base text-ink">{{ __('Your alerts are paused') }}</p>
                <p class="text-sm text-ink-soft max-w-prose">
                    {{ __('The matching engine only sends briefs to profiles that are at least 70% complete. Yours is at :pct%.', ['pct' => $this->stats['completeness']]) }}
                </p>
                <div class="mt-1 h-1.5 bg-line rounded-full overflow-hidden max-w-xs">
                    <div class="h-full bg-brand-deep rounded-full transition-all duration-700"
                         style="width: {{ $this->stats['completeness'] }}%"></div>
                </div>
                <a href="{{ route('professional.profile') }}" wire:navigate class="btn btn-primary btn-sm w-fit mt-2">
                    {{ __('Finish your profile') }}
                </a>
            </div>
        </div>
    @endif

    {{-- Where you stand. Credits carry the warm accent: that is the one number
         here that is money rather than activity. --}}
    <div class="mt-6 grid grid-cols-2 lg:grid-cols-4 gap-3">
        @php
            $tiles = [
                ['label' => __('New alerts'), 'value' => $this->stats['newAlerts'], 'href' => route('professional.alerts'), 'icon' => 'bell-alert', 'tone' => 'brand'],
                ['label' => __('Credits'), 'value' => $this->stats['credits'], 'href' => route('professional.wallet'), 'icon' => 'wallet', 'tone' => 'ember'],
                ['label' => __('Pitches sent'), 'value' => $this->stats['pitches'], 'href' => route('professional.pitches'), 'icon' => 'paper-airplane', 'tone' => 'default'],
                ['label' => __('Unread messages'), 'value' => $this->unreadMessages, 'href' => route('professional.messages'), 'icon' => 'chat-bubble-left-right', 'tone' => 'default'],
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

        {{-- Recent alerts --}}
        <section class="lg:col-span-3 panel overflow-hidden">
            <div class="flex items-center justify-between gap-4 p-5 border-b border-line-soft">
                <h2 class="font-display text-base text-ink">{{ __('Latest matches') }}</h2>
                <a href="{{ route('professional.alerts') }}" wire:navigate
                   class="inline-flex items-center gap-1 text-xs font-semibold text-brand-deep hover:text-ink transition-colors">
                    {{ __('See all') }}
                    <flux:icon name="arrow-right" variant="micro" />
                </a>
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
                            <span class="pill" data-tone="ember">{{ __('Unlocked') }}</span>
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
                <div class="m-5 empty-state">
                    <span class="icon-badge" data-tone="brand" data-size="lg">
                        <flux:icon name="inbox" class="w-5 h-5" />
                    </span>
                    <p class="font-display text-base text-ink">{{ __('No matched briefs yet') }}</p>
                    <p class="text-sm text-ink-soft max-w-[42ch]">
                        {{ __('They will appear here as soon as a client posts something in your skills.') }}
                    </p>
                    <a href="{{ route('professional.profile') }}" wire:navigate class="btn btn-ghost btn-sm mt-1">
                        {{ __('Add more skills') }}
                    </a>
                </div>
            @endforelse
        </section>

        <div class="lg:col-span-2 grid gap-6">

            {{-- Funnel --}}
            <section class="panel p-5 grid gap-4">
                <div class="flex items-start gap-3">
                    <span class="icon-badge" data-tone="brand">
                        <flux:icon name="chart-bar" variant="micro" />
                    </span>
                    <div class="min-w-0">
                        <h2 class="font-display text-base text-ink">{{ __('Last 30 days') }}</h2>
                        <p class="text-xs text-ink-faint mt-0.5">{{ __('How your alerts are converting.') }}</p>
                    </div>
                </div>

                @php
                    $funnel = $this->funnel;
                    $peak = max(1, $funnel['received']);
                    $steps = [
                        ['label' => __('Received'), 'value' => $funnel['received'], 'class' => 'bg-brand-lit'],
                        ['label' => __('Viewed'), 'value' => $funnel['viewed'], 'class' => 'bg-brand'],
                        ['label' => __('Unlocked'), 'value' => $funnel['unlocked'], 'class' => 'bg-ember'],
                    ];
                @endphp

                <div class="grid gap-3">
                    @foreach($steps as $step)
                        <div class="grid gap-1.5">
                            <div class="flex items-baseline justify-between gap-3">
                                <span class="text-xs text-ink-soft">{{ $step['label'] }}</span>
                                <span class="font-data text-xs font-semibold text-ink">{{ $step['value'] }}</span>
                            </div>
                            <div class="h-2 bg-line rounded-full overflow-hidden">
                                <div class="h-full {{ $step['class'] }} rounded-full transition-all duration-700"
                                     style="width: {{ round($step['value'] / $peak * 100) }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Credit activity --}}
            <section class="panel overflow-hidden">
                <div class="flex items-center justify-between gap-4 p-5 border-b border-line-soft">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="icon-badge" data-tone="ember">
                            <flux:icon name="wallet" variant="micro" />
                        </span>
                        <h2 class="font-display text-base text-ink">{{ __('Credit activity') }}</h2>
                    </div>
                    <a href="{{ route('professional.wallet') }}" wire:navigate
                       class="text-xs font-semibold text-ember-deep hover:text-ink transition-colors shrink-0">{{ __('Wallet') }}</a>
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
