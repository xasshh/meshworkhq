<?php

use App\Enums\BriefStatus;
use App\Models\Brief;
use App\Services\BriefService;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('My Briefs')] class extends Component {

    public function publish(int $briefId): void
    {
        $brief = Brief::where('id', $briefId)
            ->where('client_id', auth()->id())
            ->firstOrFail();

        app(BriefService::class)->publish($brief);

        session()->flash('success', "\"{$brief->title}\" is now live and matching professionals.");
    }

    public function close(int $briefId): void
    {
        $brief = Brief::where('id', $briefId)
            ->where('client_id', auth()->id())
            ->firstOrFail();

        app(BriefService::class)->close($brief);
    }

    public function render(): \Illuminate\View\View
    {
        return view('pages::client.⚡my-briefs', [
            'briefs' => Brief::where('client_id', auth()->id())
                ->latest()
                ->get(),
        ]);
    }

}; ?>

<div class="min-h-screen bg-[--color-emerald-soft]">
    <div class="max-w-5xl mx-auto px-4 py-8">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-2xl font-bold text-[--color-slate-main]" style="font-family: 'DM Serif Display', serif;">
                My Briefs
            </h1>
            <a href="{{ route('client.brief.create') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-[--color-emerald-main] hover:bg-[--color-emerald-deep] text-white rounded-xl text-sm font-medium transition-colors shadow-sm shadow-[--color-emerald-main]/20">
                <flux:icon.plus class="w-4 h-4" />
                Post a Brief
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 px-4 py-3 bg-[--color-emerald-soft] border border-[--color-emerald-border] text-[--color-emerald-deep] rounded-xl text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        {{-- Brief cards --}}
        @forelse($briefs as $brief)
            <div class="bg-white border border-slate-200 rounded-2xl p-6 mb-4 hover:shadow-sm transition-shadow"
                 wire:key="brief-{{ $brief->id }}">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3 mb-2">
                            @php
                                $statusColors = [
                                    'draft'              => 'bg-slate-100 text-slate-500',
                                    'ai_review'          => 'bg-amber-50 text-amber-600',
                                    'published'          => 'bg-[--color-emerald-soft] text-[--color-emerald-deep]',
                                    'receiving_pitches'  => 'bg-blue-50 text-blue-600',
                                    'shortlisting'       => 'bg-purple-50 text-purple-600',
                                    'hired'              => 'bg-[--color-emerald-soft] text-[--color-emerald-main]',
                                    'closed'             => 'bg-slate-100 text-slate-500',
                                    'expired'            => 'bg-red-50 text-red-500',
                                ];
                                $colorClass = $statusColors[$brief->status->value] ?? 'bg-slate-100 text-slate-500';
                            @endphp
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorClass }}">
                                {{ $brief->status->label() }}
                            </span>
                            @if($brief->total_unlocks > 0)
                                <span class="text-xs text-slate-400">
                                    {{ $brief->total_unlocks }} {{ Str::plural('pitch', $brief->total_unlocks) }}
                                </span>
                            @endif
                        </div>

                        <h3 class="font-semibold text-[--color-slate-main] text-base">{{ $brief->title }}</h3>

                        <div class="flex items-center gap-4 mt-2 text-xs text-slate-400">
                            @if($brief->budget_max)
                                <span>₦{{ number_format($brief->budget_min ?? 0) }} – ₦{{ number_format($brief->budget_max) }}</span>
                            @endif
                            @if($brief->published_at)
                                <span>Published {{ $brief->published_at->diffForHumans() }}</span>
                            @else
                                <span>Created {{ $brief->created_at->diffForHumans() }}</span>
                            @endif
                            @if($brief->expires_at && $brief->status->isActive())
                                <span class="text-amber-500">Expires {{ $brief->expires_at->diffForHumans() }}</span>
                            @endif
                        </div>

                        @if(!empty($brief->skill_tags))
                            <div class="flex flex-wrap gap-1.5 mt-3">
                                @foreach(array_slice($brief->skill_tags, 0, 5) as $tag)
                                    <span class="px-2 py-0.5 bg-slate-50 border border-slate-200 text-slate-500 rounded-full text-xs">{{ $tag }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-col gap-2 flex-shrink-0">
                        @if($brief->status === BriefStatus::Draft)
                            <button wire:click="publish({{ $brief->id }})"
                                    wire:confirm="Publish this brief? It will be matched to professionals immediately."
                                    class="px-4 py-2 bg-[--color-emerald-main] hover:bg-[--color-emerald-deep] text-white text-sm font-medium rounded-xl transition-colors">
                                Publish
                            </button>
                        @endif

                        <a href="{{ route('client.brief.detail', ['ulid' => $brief->ulid]) }}"
                           class="px-4 py-2 border border-slate-200 hover:border-slate-300 text-slate-600 text-sm font-medium rounded-xl transition-colors text-center">
                            View
                        </a>

                        @if($brief->status->isActive())
                            <button wire:click="close({{ $brief->id }})"
                                    wire:confirm="Close this brief? Professionals will no longer be able to pitch."
                                    class="px-4 py-2 border border-red-100 hover:border-red-200 text-red-500 text-sm font-medium rounded-xl transition-colors">
                                Close
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-16 bg-white border border-slate-200 rounded-2xl">
                <div class="w-12 h-12 bg-[--color-emerald-soft] rounded-full flex items-center justify-center mx-auto mb-4">
                    <flux:icon.document-text class="w-6 h-6 text-[--color-emerald-main]" />
                </div>
                <p class="text-slate-600 font-medium">No briefs yet</p>
                <p class="text-sm text-slate-400 mt-1 mb-6">Post your first brief to start receiving pitches from professionals.</p>
                <a href="{{ route('client.brief.create') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-[--color-emerald-main] text-white rounded-xl text-sm font-medium">
                    Post a Brief
                </a>
            </div>
        @endforelse

    </div>
</div>
