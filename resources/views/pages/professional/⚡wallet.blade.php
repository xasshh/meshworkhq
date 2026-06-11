<?php

use App\Models\CreditBundle;
use App\Models\CreditTransaction;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('My Wallet')] class extends Component {

    use WithPagination;

    public function render(): \Illuminate\View\View
    {
        $user = auth()->user();

        return view('pages::professional.⚡wallet', [
            'balance' => $user->credits,
            'bundles' => CreditBundle::active()->orderBy('sort_order')->get(),
            'transactions' => CreditTransaction::where('user_id', $user->id)
                ->latest()
                ->paginate(20),
        ]);
    }

}; ?>

<div class="min-h-screen bg-[--color-emerald-soft]">
    <div class="max-w-4xl mx-auto px-4 py-8">

        {{-- Header --}}
        <h1 class="text-2xl font-bold text-[--color-slate-main] mb-8" style="font-family: 'DM Serif Display', serif;">
            My Wallet
        </h1>

        {{-- Balance card --}}
        <div class="bg-[--color-slate-main] rounded-2xl p-8 mb-8 text-white relative overflow-hidden">
            <div class="absolute inset-0 opacity-5">
                <div class="absolute top-4 right-8 w-32 h-32 rounded-full border-4 border-white"></div>
                <div class="absolute top-12 right-20 w-20 h-20 rounded-full border-4 border-white"></div>
            </div>
            <p class="text-white/60 text-sm uppercase tracking-widest mb-1">Available Credits</p>
            <p class="text-6xl font-bold" style="font-family: 'DM Serif Display', serif;">{{ $balance }}</p>
            <p class="text-white/50 text-sm mt-2">1 credit = 1 brief unlock</p>
        </div>

        {{-- Credit bundles --}}
        <h2 class="text-lg font-semibold text-[--color-slate-main] mb-4">Buy Credits</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-10">
            @foreach($bundles as $bundle)
                <div class="bg-white border border-slate-200 rounded-2xl p-6 hover:border-[--color-emerald-main] hover:shadow-sm transition-all group">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <p class="font-semibold text-[--color-slate-main]">{{ $bundle->name }}</p>
                            <p class="text-3xl font-bold text-[--color-emerald-main] mt-1" style="font-family: 'DM Serif Display', serif;">
                                {{ $bundle->credits }} <span class="text-base font-medium text-slate-400">credits</span>
                            </p>
                        </div>
                        <p class="text-lg font-bold text-[--color-slate-main]">₦{{ number_format($bundle->price_kobo / 100) }}</p>
                    </div>
                    <p class="text-xs text-slate-400 mb-4">₦{{ number_format($bundle->price_kobo / $bundle->credits / 100) }} per credit</p>
                    <button class="w-full py-2.5 bg-[--color-slate-main] group-hover:bg-[--color-emerald-main] text-white rounded-xl text-sm font-medium transition-colors">
                        Purchase
                    </button>
                </div>
            @endforeach
        </div>

        {{-- Transaction history --}}
        <h2 class="text-lg font-semibold text-[--color-slate-main] mb-4">Transaction History</h2>
        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
            @forelse($transactions as $tx)
                <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 last:border-0"
                     wire:key="tx-{{ $tx->id }}">
                    <div>
                        <p class="text-sm font-medium text-[--color-slate-main]">{{ $tx->description ?? $tx->type->value }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $tx->created_at->format('d M Y, H:i') }}</p>
                    </div>
                    <span class="text-sm font-semibold {{ $tx->amount > 0 ? 'text-[--color-emerald-main]' : 'text-red-500' }}">
                        {{ $tx->amount > 0 ? '+' : '' }}{{ $tx->amount }}
                    </span>
                </div>
            @empty
                <div class="text-center py-10 text-slate-400 text-sm">No transactions yet.</div>
            @endforelse
        </div>

        @if($transactions->hasPages())
            <div class="mt-4">{{ $transactions->links() }}</div>
        @endif

    </div>
</div>
