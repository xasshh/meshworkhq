<?php

use App\Models\CreditBundle;
use App\Models\CreditTransaction;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Wallet')] class extends Component
{
    use WithPagination;

    #[Computed]
    public function bundles(): \Illuminate\Database\Eloquent\Collection
    {
        return CreditBundle::active()->get();
    }


    public function render(): \Illuminate\View\View
    {
        return view('pages::professional.⚡wallet', [
            'balance' => (int) auth()->user()->credits,
            'transactions' => CreditTransaction::where('user_id', auth()->id())
                ->latest()
                ->paginate(20),
        ]);
    }
}; ?>

<div class="max-w-3xl mx-auto px-4 sm:px-6 py-8 sm:py-10">

    <x-page-header
        :eyebrow="__('Account')"
        :title="__('Wallet')"
        :description="__('One credit unlocks one brief. Every movement is recorded below.')"
    />

    {{-- Balance --}}
    <section class="mt-6 bg-ink p-6 sm:p-8 grid gap-5">
        <div class="flex items-end justify-between gap-6 flex-wrap">
            <div>
                <p class="eyebrow text-paper/40">{{ __('Available balance') }}</p>
                <div class="flex items-end gap-2.5 mt-3">
                    <span class="font-data text-5xl font-medium text-paper leading-none tracking-tight">{{ number_format($balance) }}</span>
                    <span class="text-sm text-paper/50 pb-1">{{ trans_choice('credit|credits', $balance) }}</span>
                </div>
            </div>

            <p class="font-data text-[10px] uppercase tracking-[0.13em] text-paper/40 max-w-[20ch] text-right">
                {{ trans_choice('{0}no unlocks left|{1}:count unlock left|[2,*]:count unlocks left', $balance, ['count' => $balance]) }}
            </p>
        </div>

        <div class="grid grid-flow-col gap-[3px]" aria-hidden="true">
            @for($i = 1; $i <= 20; $i++)
                <span class="h-4 {{ $i <= min(20, $balance) ? 'bg-brand' : 'bg-paper/15' }}"></span>
            @endfor
        </div>
    </section>

    <div class="mt-4 panel p-5 border-l-2 border-l-brand grid gap-1.5">
        <p class="text-sm font-semibold text-ink">{{ __('You started with three free unlocks') }}</p>
        <p class="text-sm text-ink-soft max-w-prose">
            {{ __('After those, credits are bought in packs below. One credit reveals one client and opens one thread. Nothing else ever costs money.') }}
        </p>
    </div>

    {{-- Bundles --}}
    <section class="mt-8">
        <div class="flex items-end justify-between gap-4 flex-wrap pb-4 border-b border-line">
            <div>
                <h2 class="font-display text-lg text-ink">{{ __('Credit bundles') }}</h2>
                <p class="text-sm text-ink-soft mt-1.5">{{ __('Naira pricing. Larger bundles cost less per credit.') }}</p>
            </div>
            <span class="pill" data-tone="live">{{ __('Paystack secured') }}</span>
        </div>

        <div class="mt-5 grid sm:grid-cols-2 gap-px bg-line border border-line">
            @foreach($this->bundles as $bundle)
                <div class="bg-paper p-5 grid gap-3" wire:key="bundle-{{ $bundle->id }}">
                    <div class="flex items-baseline justify-between gap-3">
                        <span class="font-display text-sm text-ink">{{ $bundle->name }}</span>
                        <span class="font-data text-xs text-ink-faint">{{ $bundle->credits }} {{ __('credits') }}</span>
                    </div>

                    <div class="flex items-baseline gap-2">
                        <span class="font-data text-2xl font-medium text-ink leading-none">&#8358;{{ number_format($bundle->price_ngn) }}</span>
                        <span class="font-data text-[10px] text-ink-faint">&#8358;{{ number_format($bundle->price_per_credit) }} {{ __('each') }}</span>
                    </div>

                    <form method="POST" action="{{ route('professional.wallet.checkout', ['bundle' => $bundle->id]) }}">
                        @csrf
                        <button type="submit"
                                class="btn-lift w-full font-data text-[10px] uppercase tracking-[0.12em] font-semibold px-4 py-3 bg-brand-deep text-paper">
                            {{ __('Buy this pack') }}
                        </button>
                    </form>
                </div>
            @endforeach
        </div>

        <p class="mt-4 text-sm text-ink-soft max-w-prose">
            {{ __('Payment is handled by Paystack. Pay by card, bank transfer or USSD in naira. Credits land in your wallet the moment the payment clears.') }}
        </p>
    </section>

    {{-- Ledger --}}
    <section class="mt-8">
        <h2 class="font-display text-lg text-ink pb-4 border-b border-line">{{ __('Credit history') }}</h2>

        @if($transactions->total() > 0)
            <div class="mt-5 panel">
                @foreach($transactions as $transaction)
                    <div class="flex items-center justify-between gap-4 px-5 py-4 border-b border-line-soft last:border-b-0" wire:key="transaction-{{ $transaction->id }}">
                        <div class="min-w-0">
                            <p class="text-sm text-ink truncate">{{ $transaction->description ?: ucfirst($transaction->type->value) }}</p>
                            <p class="font-data text-[10px] text-ink-faint mt-1">
                                {{ $transaction->created_at->format('j M Y, H:i') }}
                                @if($transaction->expires_at)
                                    &middot; {{ $transaction->isExpired() ? __('expired') : __('expires :date', ['date' => $transaction->expires_at->format('j M Y')]) }}
                                @endif
                            </p>
                        </div>

                        <div class="text-right shrink-0">
                            <p class="font-data text-sm font-semibold {{ $transaction->amount > 0 ? 'text-live' : 'text-ink' }}">
                                {{ $transaction->amount > 0 ? '+' : '' }}{{ $transaction->amount }}
                            </p>
                            <p class="font-data text-[10px] text-ink-faint mt-0.5">{{ __('balance') }} {{ $transaction->balance_after }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($transactions->hasPages())
                <div class="mt-6">{{ $transactions->links() }}</div>
            @endif
        @else
            <p class="mt-5 panel p-8 text-center text-sm text-ink-faint">{{ __('Nothing here yet.') }}</p>
        @endif
    </section>
</div>
