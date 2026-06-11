<?php

use App\Enums\AlertStatus;
use App\Models\Alert;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Brief Alerts')] class extends Component {

    use WithPagination;

    public string $filter = 'all';

    public function updatingFilter(): void
    {
        $this->resetPage();
    }

    public function markViewed(int $alertId): void
    {
        $alert = Alert::where('id', $alertId)
            ->where('professional_id', auth()->id())
            ->firstOrFail();

        $alert->markViewed();
    }

    public function render(): \Illuminate\View\View
    {
        $query = Alert::with(['brief.client'])
            ->where('professional_id', auth()->id())
            ->latest('notified_at');

        if ($this->filter === 'unread') {
            $query->where('status', AlertStatus::Notified);
        } elseif ($this->filter === 'unlocked') {
            $query->where('status', AlertStatus::Unlocked);
        }

        return view('pages::professional.⚡alert-feed', [
            'alerts' => $query->paginate(20),
            'unreadCount' => Alert::where('professional_id', auth()->id())
                ->where('status', AlertStatus::Notified)
                ->count(),
        ]);
    }

}; ?>

<div class="min-h-screen bg-[--color-emerald-soft]">
    <div class="max-w-4xl mx-auto px-4 py-8">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-[--color-slate-main]" style="font-family: 'DM Serif Display', serif;">
                    Brief Alerts
                </h1>
                @if($unreadCount > 0)
                    <p class="text-sm text-[--color-emerald-deep] mt-1">
                        {{ $unreadCount }} new {{ Str::plural('alert', $unreadCount) }} waiting
                    </p>
                @endif
            </div>
            <a href="{{ route('professional.wallet') }}"
               class="flex items-center gap-2 bg-white border border-[--color-emerald-border] rounded-xl px-4 py-2 text-sm font-medium text-[--color-slate-main] hover:border-[--color-emerald-main] transition-colors">
                <flux:icon.credit-card class="w-4 h-4 text-[--color-emerald-main]" />
                {{ auth()->user()->credits }} Credits
            </a>
        </div>

        {{-- Filter tabs --}}
        <div class="flex gap-1 bg-white border border-slate-200 rounded-xl p-1 w-fit mb-6">
            @foreach(['all' => 'All Alerts', 'unread' => 'Unread', 'unlocked' => 'Unlocked'] as $value => $label)
                <button wire:click="$set('filter', '{{ $value }}')"
                        class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all
                               {{ $filter === $value ? 'bg-[--color-emerald-main] text-white shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- Alert list --}}
        <div class="space-y-3">
            @forelse($alerts as $alert)
                @php $brief = $alert->brief; @endphp
                <div class="bg-white border {{ $alert->status === \App\Enums\AlertStatus::Notified ? 'border-[--color-emerald-border]' : 'border-slate-200' }} rounded-2xl p-5 hover:shadow-sm transition-shadow"
                     wire:key="alert-{{ $alert->id }}">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            {{-- Status dot --}}
                            <div class="flex items-center gap-2 mb-2">
                                @if($alert->status === \App\Enums\AlertStatus::Notified)
                                    <span class="w-2 h-2 rounded-full bg-[--color-emerald-main] flex-shrink-0"></span>
                                    <span class="text-xs font-medium text-[--color-emerald-deep] uppercase tracking-wide">New</span>
                                @elseif($alert->status === \App\Enums\AlertStatus::Unlocked)
                                    <span class="w-2 h-2 rounded-full bg-blue-500 flex-shrink-0"></span>
                                    <span class="text-xs font-medium text-blue-600 uppercase tracking-wide">Unlocked</span>
                                @else
                                    <span class="w-2 h-2 rounded-full bg-slate-300 flex-shrink-0"></span>
                                    <span class="text-xs text-slate-400 uppercase tracking-wide">Viewed</span>
                                @endif
                                <span class="text-xs text-slate-400 ml-auto">Wave {{ $alert->wave }} · {{ $alert->notified_at?->diffForHumans() }}</span>
                            </div>

                            <h3 class="font-semibold text-[--color-slate-main] text-base leading-snug">
                                {{ $brief->title }}
                            </h3>
                            <p class="text-sm text-slate-500 mt-1">{{ $brief->client->name ?? 'Client' }} · {{ $brief->is_remote ? 'Remote' : $brief->location }}</p>

                            @if($brief->budget_max)
                                <p class="text-sm font-medium text-[--color-emerald-deep] mt-1">
                                    ₦{{ number_format($brief->budget_min ?? 0) }} – ₦{{ number_format($brief->budget_max) }}
                                </p>
                            @endif

                            @if(!empty($brief->skill_tags))
                                <div class="flex flex-wrap gap-1.5 mt-3">
                                    @foreach(array_slice($brief->skill_tags, 0, 4) as $tag)
                                        <span class="px-2 py-0.5 bg-[--color-emerald-soft] border border-[--color-emerald-border] text-[--color-emerald-deep] rounded-full text-xs">
                                            {{ $tag }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-col gap-2 flex-shrink-0">
                            @if($alert->status !== \App\Enums\AlertStatus::Unlocked)
                                <form action="{{ route('professional.brief.unlock', ['ulid' => $brief->ulid]) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                            class="px-4 py-2 bg-[--color-emerald-main] hover:bg-[--color-emerald-deep] text-white text-sm font-medium rounded-xl transition-colors whitespace-nowrap">
                                        Unlock · 1 Credit
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('professional.conversation', ['id' => $alert->unlock?->conversation?->id]) }}"
                                   class="px-4 py-2 bg-slate-900 text-white text-sm font-medium rounded-xl transition-colors whitespace-nowrap text-center">
                                    Open Chat
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-16 bg-white border border-slate-200 rounded-2xl">
                    <div class="w-12 h-12 bg-[--color-emerald-soft] rounded-full flex items-center justify-center mx-auto mb-4">
                        <flux:icon.bell class="w-6 h-6 text-[--color-emerald-main]" />
                    </div>
                    <p class="text-slate-600 font-medium">No alerts yet</p>
                    <p class="text-sm text-slate-400 mt-1">We'll notify you when briefs matching your skills are posted.</p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($alerts->hasPages())
            <div class="mt-6">{{ $alerts->links() }}</div>
        @endif

    </div>
</div>
