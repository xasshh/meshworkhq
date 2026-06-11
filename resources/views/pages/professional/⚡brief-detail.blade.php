<?php

use App\Models\Alert;
use App\Models\Brief;
use App\Models\Unlock;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Brief Details')] class extends Component {

    public Brief $brief;
    public ?Alert $alert = null;
    public ?Unlock $unlock = null;

    public function mount(string $ulid): void
    {
        $this->brief = Brief::where('ulid', $ulid)
            ->with(['client'])
            ->firstOrFail();

        $user = auth()->user();

        $this->alert = Alert::where('brief_id', $this->brief->id)
            ->where('professional_id', $user->id)
            ->first();

        $this->unlock = Unlock::where('brief_id', $this->brief->id)
            ->where('professional_id', $user->id)
            ->with('conversation')
            ->first();

        // Mark alert as viewed.
        if ($this->alert && $this->alert->status === \App\Enums\AlertStatus::Notified) {
            $this->alert->markViewed();
        }
    }

    public function render(): \Illuminate\View\View
    {
        return view('pages::professional.⚡brief-detail');
    }

}; ?>

<div class="min-h-screen bg-[--color-emerald-soft]">
    <div class="max-w-3xl mx-auto px-4 py-8">

        {{-- Back --}}
        <a href="{{ route('professional.alerts') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700 mb-6 transition-colors">
            <flux:icon.arrow-left class="w-4 h-4" />
            Back to Alerts
        </a>

        {{-- Brief card --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-7 mb-6">
            <div class="flex items-start justify-between gap-4 mb-5">
                <div>
                    <h1 class="text-xl font-bold text-[--color-slate-main]" style="font-family: 'DM Serif Display', serif;">
                        {{ $brief->title }}
                    </h1>
                    @if($brief->is_remote)
                        <span class="inline-block mt-1.5 px-2.5 py-0.5 bg-[--color-emerald-soft] border border-[--color-emerald-border] text-[--color-emerald-deep] rounded-full text-xs font-medium">Remote</span>
                    @elseif($brief->location)
                        <span class="inline-block mt-1.5 px-2.5 py-0.5 bg-slate-100 text-slate-500 rounded-full text-xs">{{ $brief->location }}</span>
                    @endif
                </div>

                @if($this->unlock)
                    <span class="px-3 py-1.5 bg-blue-50 text-blue-600 rounded-xl text-xs font-medium flex-shrink-0">Unlocked ✓</span>
                @elseif($brief->isAvailableForUnlock())
                    <form action="{{ route('professional.brief.unlock', ['ulid' => $brief->ulid]) }}" method="POST" class="flex-shrink-0">
                        @csrf
                        <button type="submit"
                                class="px-5 py-2.5 bg-[--color-emerald-main] hover:bg-[--color-emerald-deep] text-white font-medium rounded-xl text-sm transition-colors shadow-sm shadow-[--color-emerald-main]/20">
                            Unlock Brief · 1 Credit
                        </button>
                    </form>
                @else
                    <span class="px-3 py-1 bg-slate-100 text-slate-400 rounded-xl text-xs">No longer accepting pitches</span>
                @endif
            </div>

            {{-- Description only shown after unlock --}}
            @if($this->unlock)
                <p class="text-slate-600 text-sm leading-relaxed mb-6">{{ $brief->description }}</p>

                {{-- Client contact info --}}
                <div class="bg-[--color-emerald-soft] border border-[--color-emerald-border] rounded-xl p-4 mb-5">
                    <p class="text-xs font-medium text-[--color-emerald-deep] uppercase tracking-wide mb-3">Client Details</p>
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-white border border-[--color-emerald-border] flex items-center justify-center">
                            <span class="text-sm font-bold text-[--color-emerald-deep]">{{ substr($brief->client->name, 0, 1) }}</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-[--color-slate-main]">{{ $brief->client->name }}</p>
                            @if($brief->client->company_name)
                                <p class="text-xs text-slate-400">{{ $brief->client->company_name }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 mb-5">
                    <p class="text-sm text-slate-400 text-center">
                        Unlock this brief to see the full description and client contact details.
                    </p>
                </div>
            @endif

            {{-- Budget & meta --}}
            <div class="grid grid-cols-2 gap-4 text-sm border-t border-slate-100 pt-5">
                @if($brief->budget_max)
                    <div>
                        <p class="text-xs text-slate-400 uppercase tracking-wide mb-1">Budget Range</p>
                        <p class="font-semibold text-[--color-slate-main]">₦{{ number_format($brief->budget_min ?? 0) }} – ₦{{ number_format($brief->budget_max) }}</p>
                    </div>
                @endif
                @if($brief->published_at)
                    <div>
                        <p class="text-xs text-slate-400 uppercase tracking-wide mb-1">Posted</p>
                        <p class="font-medium text-[--color-slate-main]">{{ $brief->published_at->diffForHumans() }}</p>
                    </div>
                @endif
            </div>

            @if(!empty($brief->skill_tags))
                <div class="flex flex-wrap gap-1.5 mt-5 pt-5 border-t border-slate-100">
                    @foreach($brief->skill_tags as $tag)
                        <span class="px-2.5 py-1 bg-[--color-emerald-soft] border border-[--color-emerald-border] text-[--color-emerald-deep] rounded-full text-xs">{{ $tag }}</span>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Open conversation CTA if unlocked --}}
        @if($this->unlock?->conversation)
            <a href="{{ route('professional.conversation', ['id' => $this->unlock->conversation->id]) }}"
               class="flex items-center justify-center gap-2 w-full py-3.5 bg-[--color-slate-main] hover:bg-slate-800 text-white font-medium rounded-2xl text-sm transition-colors">
                <flux:icon.chat-bubble-left-right class="w-4 h-4" />
                Open Conversation
            </a>
        @endif

    </div>
</div>
