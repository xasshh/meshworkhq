<?php

use App\Enums\BriefStatus;
use App\Models\Brief;
use App\Models\Unlock;
use App\Services\BriefService;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Brief Details')] class extends Component {

    public Brief $brief;

    public function mount(string $ulid): void
    {
        $this->brief = Brief::where('ulid', $ulid)
            ->where('client_id', auth()->id())
            ->with(['unlocks.professional', 'unlocks.conversation'])
            ->firstOrFail();
    }

    public function hire(int $professionalId): void
    {
        $professional = \App\Models\User::findOrFail($professionalId);
        app(BriefService::class)->hire($this->brief, $professional);
        $this->brief->refresh()->load(['unlocks.professional', 'unlocks.conversation']);
        session()->flash('success', "You've hired {$professional->name}. The brief is now closed.");
    }

    public function render(): \Illuminate\View\View
    {
        return view('pages::client.⚡brief-detail');
    }

}; ?>

<div class="min-h-screen bg-[--color-emerald-soft]">
    <div class="max-w-4xl mx-auto px-4 py-8">

        {{-- Back --}}
        <a href="{{ route('client.briefs') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700 mb-6 transition-colors">
            <flux:icon.arrow-left class="w-4 h-4" />
            All Briefs
        </a>

        @if(session('success'))
            <div class="mb-6 px-4 py-3 bg-[--color-emerald-soft] border border-[--color-emerald-border] text-[--color-emerald-deep] rounded-xl text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        {{-- Brief header card --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-7 mb-6">
            <div class="flex items-start justify-between gap-4 mb-5">
                <h1 class="text-xl font-bold text-[--color-slate-main]" style="font-family: 'DM Serif Display', serif;">
                    {{ $brief->title }}
                </h1>
                @php
                    $statusColors = [
                        'draft'             => 'bg-slate-100 text-slate-500',
                        'ai_review'         => 'bg-amber-50 text-amber-600',
                        'published'         => 'bg-[--color-emerald-soft] text-[--color-emerald-deep]',
                        'receiving_pitches' => 'bg-blue-50 text-blue-600',
                        'shortlisting'      => 'bg-purple-50 text-purple-600',
                        'hired'             => 'bg-[--color-emerald-soft] text-[--color-emerald-main]',
                        'closed'            => 'bg-slate-100 text-slate-500',
                        'expired'           => 'bg-red-50 text-red-500',
                    ];
                @endphp
                <span class="px-3 py-1 rounded-full text-xs font-medium flex-shrink-0 {{ $statusColors[$brief->status->value] ?? 'bg-slate-100 text-slate-500' }}">
                    {{ $brief->status->label() }}
                </span>
            </div>

            <p class="text-slate-600 text-sm leading-relaxed mb-5">{{ $brief->description }}</p>

            <div class="grid grid-cols-2 gap-4 text-sm">
                @if($brief->budget_max)
                    <div>
                        <p class="text-xs text-slate-400 uppercase tracking-wide mb-1">Budget</p>
                        <p class="font-medium text-[--color-slate-main]">₦{{ number_format($brief->budget_min ?? 0) }} – ₦{{ number_format($brief->budget_max) }}</p>
                    </div>
                @endif
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wide mb-1">Location</p>
                    <p class="font-medium text-[--color-slate-main]">{{ $brief->is_remote ? 'Remote' : ($brief->location ?? 'Not specified') }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wide mb-1">Total Pitches</p>
                    <p class="font-medium text-[--color-slate-main]">{{ $brief->total_unlocks }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wide mb-1">Alerts Sent</p>
                    <p class="font-medium text-[--color-slate-main]">{{ $brief->total_alerts_sent }}</p>
                </div>
            </div>

            @if(!empty($brief->skill_tags))
                <div class="flex flex-wrap gap-1.5 mt-5 pt-5 border-t border-slate-100">
                    @foreach($brief->skill_tags as $tag)
                        <span class="px-2.5 py-1 bg-[--color-emerald-soft] border border-[--color-emerald-border] text-[--color-emerald-deep] rounded-full text-xs">{{ $tag }}</span>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Pitches --}}
        <h2 class="text-lg font-semibold text-[--color-slate-main] mb-4">
            Professionals Who Pitched
            <span class="text-slate-400 font-normal text-sm ml-2">{{ $brief->unlocks->count() }}</span>
        </h2>

        @forelse($brief->unlocks as $unlock)
            @php $professional = $unlock->professional; @endphp
            <div class="bg-white border border-slate-200 rounded-2xl p-5 mb-3 flex items-center justify-between gap-4"
                 wire:key="unlock-{{ $unlock->id }}">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-[--color-emerald-soft] border border-[--color-emerald-border] flex items-center justify-center flex-shrink-0">
                        <span class="text-sm font-bold text-[--color-emerald-deep]">{{ substr($professional->name, 0, 1) }}</span>
                    </div>
                    <div>
                        <p class="font-medium text-[--color-slate-main] text-sm">{{ $professional->name }}</p>
                        @if($professional->professional_title)
                            <p class="text-xs text-slate-400">{{ $professional->professional_title }}</p>
                        @endif
                        <p class="text-xs text-slate-400 mt-0.5">Pitched {{ $unlock->unlocked_at->diffForHumans() }}</p>
                    </div>
                </div>

                <div class="flex gap-2 flex-shrink-0">
                    @if($unlock->conversation)
                        <a href="{{ route('client.conversation', ['id' => $unlock->conversation->id]) }}"
                           class="px-4 py-2 border border-slate-200 hover:border-slate-300 text-slate-600 text-sm font-medium rounded-xl transition-colors">
                            Message
                        </a>
                    @endif

                    @if(in_array($brief->status, [\App\Enums\BriefStatus::ReceivingPitches, \App\Enums\BriefStatus::Shortlisting]) && $brief->hired_professional_id === null)
                        <button wire:click="hire({{ $professional->id }})"
                                wire:confirm="Hire {{ $professional->name }}? This will close the brief to new pitches."
                                class="px-4 py-2 bg-[--color-emerald-main] hover:bg-[--color-emerald-deep] text-white text-sm font-medium rounded-xl transition-colors">
                            Hire
                        </button>
                    @elseif($brief->hired_professional_id === $professional->id)
                        <span class="px-3 py-2 bg-[--color-emerald-soft] text-[--color-emerald-deep] text-xs font-medium rounded-xl">
                            Hired ✓
                        </span>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-center py-10 bg-white border border-slate-200 rounded-2xl text-slate-400 text-sm">
                No pitches yet. Professionals are being notified.
            </div>
        @endforelse

    </div>
</div>
