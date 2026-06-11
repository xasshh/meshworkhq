<?php

use App\Enums\BriefStatus;
use App\Models\Brief;
use App\Models\Skill;
use App\Services\BriefService;
use App\Services\MatchingService;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Post a Brief')] class extends Component
{
    public string $step = 'form'; // form | calibrating | radar

    public string $title = '';

    public string $description = '';

    public string $budget = '';

    public string $location = '';

    public bool $isRemote = true;

    /** @var array<int, string> */
    public array $selectedTags = [];

    public string $customTag = '';

    public int $matchedCount = 0;

    public ?string $briefUlid = null;

    public function submitForm(): void
    {
        $this->validate([
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['required', 'string', 'min:20'],
            'budget' => ['required', 'numeric', 'min:1000'],
            'location' => ['nullable', 'string', 'max:120'],
        ]);

        $this->selectedTags = $this->suggestTags();
        $this->step = 'calibrating';
    }

    public function toggleTag(string $tag): void
    {
        if (in_array($tag, $this->selectedTags)) {
            $this->selectedTags = array_values(array_diff($this->selectedTags, [$tag]));
        } elseif (count($this->selectedTags) < 10) {
            $this->selectedTags[] = $tag;
        }
    }

    public function addCustomTag(): void
    {
        $tag = trim($this->customTag);

        if ($tag !== '' && ! in_array($tag, $this->selectedTags) && count($this->selectedTags) < 10) {
            $this->selectedTags[] = $tag;
        }

        $this->customTag = '';
    }

    public function confirmTags(BriefService $briefService, MatchingService $matchingService): void
    {
        $this->validate(
            ['selectedTags' => ['required', 'array', 'min:1']],
            ['selectedTags.required' => 'Select at least one skill tag so we can match professionals.'],
        );

        $brief = Brief::create([
            'client_id' => auth()->id(),
            'title' => $this->title,
            'description' => $this->description,
            'budget_max' => (int) $this->budget,
            'skill_tags' => array_values($this->selectedTags),
            'location' => $this->location !== '' ? $this->location : null,
            'is_remote' => $this->isRemote,
            'status' => BriefStatus::Draft,
        ]);

        $briefService->publish($brief);

        $this->matchedCount = $matchingService->findCandidates($brief->refresh())->count();
        $this->briefUlid = $brief->ulid;
        $this->step = 'radar';
    }

    public function restart(): void
    {
        $this->reset(['title', 'description', 'budget', 'location', 'isRemote', 'selectedTags', 'customTag', 'matchedCount', 'briefUlid']);
        $this->step = 'form';
    }

    /**
     * @return \Illuminate\Support\Collection<int, Skill>
     */
    #[Computed]
    public function taxonomy(): \Illuminate\Support\Collection
    {
        return Skill::active()->get();
    }

    /**
     * Keyword-match the brief text against the skill taxonomy.
     *
     * @return array<int, string>
     */
    private function suggestTags(): array
    {
        $text = Str::lower($this->title.' '.$this->description);

        return Skill::active()->get()
            ->filter(fn (Skill $skill) => str_contains($text, Str::lower($skill->name)))
            ->pluck('name')
            ->take(10)
            ->values()
            ->all();
    }
}; ?>

<div class="flex items-start justify-center py-12 px-4">
        <div class="w-full max-w-2xl animate-fade-in-up">

            {{-- Progress bar --}}
            <div class="mb-8">
                <div class="flex items-center gap-2 mb-3">
                    @foreach (['form' => 'Brief Details', 'calibrating' => 'Skill Calibration', 'radar' => 'Matching Live'] as $key => $label)
                        <div class="flex items-center gap-2 {{ !$loop->first ? 'flex-1' : '' }}">
                            @if (!$loop->first)
                                <div class="flex-1 h-px {{ in_array($step, array_slice(array_keys(['form' => '', 'calibrating' => '', 'radar' => '']), $loop->index)) ? 'bg-emerald-main' : 'bg-slate-200' }} transition-colors duration-500"></div>
                            @endif
                            <div class="flex items-center gap-1.5">
                                <div @class([
                                    'w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-300',
                                    'bg-emerald-main text-white' => $step === $key || ($key === 'form' && in_array($step, ['calibrating', 'radar'])) || ($key === 'calibrating' && $step === 'radar'),
                                    'bg-slate-100 text-slate-400' => $step === 'form' && $key !== 'form',
                                ])>{{ $loop->index + 1 }}</div>
                                <span class="text-xs font-medium {{ $step === $key ? 'text-slate-main' : 'text-slate-400' }} hidden sm:inline">{{ $label }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-light-card rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

                {{-- ── STEP 1: FORM ── --}}
                @if ($step === 'form')
                    <div class="p-8 animate-fade-in-up">
                        <div class="mb-7">
                            <h1 class="font-display text-2xl font-bold text-slate-main tracking-tight">Post a Service Brief</h1>
                            <p class="text-slate-500 text-sm mt-1">Describe your project and we'll match you with the right professionals.</p>
                        </div>

                        <form wire:submit="submitForm" class="space-y-6">
                            <div>
                                <label class="block text-sm font-semibold text-slate-main mb-1.5">Brief Title <span class="text-red-400">*</span></label>
                                <input
                                    wire:model="title"
                                    type="text"
                                    placeholder="e.g. Brand identity design for a fintech startup"
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-light-canvas text-slate-main placeholder-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-main/30 focus:border-emerald-main transition"
                                />
                                @error('title') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-main mb-1.5">Project Description <span class="text-red-400">*</span></label>
                                <textarea
                                    wire:model="description"
                                    rows="5"
                                    placeholder="Describe scope, deliverables, style references, and any specific requirements..."
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-light-canvas text-slate-main placeholder-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-main/30 focus:border-emerald-main transition resize-none"
                                ></textarea>
                                @error('description') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <div class="grid sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-main mb-1.5">Budget (NGN) <span class="text-red-400">*</span></label>
                                    <div class="relative">
                                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-semibold text-sm">₦</span>
                                        <input
                                            wire:model="budget"
                                            type="number"
                                            min="1000"
                                            placeholder="250,000"
                                            class="w-full pl-8 pr-4 py-3 rounded-xl border border-slate-200 bg-light-canvas text-slate-main placeholder-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-main/30 focus:border-emerald-main transition"
                                        />
                                    </div>
                                    @error('budget') <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-main mb-1.5">Location</label>
                                    <input
                                        wire:model="location"
                                        type="text"
                                        placeholder="e.g. Lagos (optional)"
                                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-light-canvas text-slate-main placeholder-slate-400 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-main/30 focus:border-emerald-main transition"
                                    />
                                </div>
                            </div>

                            <label class="flex items-center gap-3 cursor-pointer select-none">
                                <input type="checkbox" wire:model="isRemote"
                                       class="w-4 h-4 rounded border-slate-300 text-emerald-main focus:ring-emerald-main/30" />
                                <span class="text-sm text-slate-600">Remote work is fine — professionals anywhere can pitch</span>
                            </label>

                            <button
                                type="submit"
                                class="btn-lift w-full bg-emerald-main text-white font-bold py-3.5 rounded-xl hover:bg-emerald-deep transition-all shadow-md shadow-emerald-main/20 flex items-center justify-center gap-2"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-75 cursor-wait"
                            >
                                <span wire:loading.remove>Analyse & Match Skills →</span>
                                <span wire:loading class="flex items-center gap-2">
                                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                    Analysing...
                                </span>
                            </button>
                        </form>
                    </div>
                @endif

                {{-- ── STEP 2: SKILL CALIBRATION ── --}}
                @if ($step === 'calibrating')
                    <div class="p-8 animate-fade-in-up">
                        <div class="mb-7">
                            <div class="inline-flex items-center gap-2 bg-emerald-soft text-emerald-deep text-xs font-semibold px-3 py-1.5 rounded-full mb-3 border border-emerald-border">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-main animate-pulse-dot"></span>
                                Matching Engine
                            </div>
                            <h2 class="font-display text-2xl font-bold text-slate-main tracking-tight">Confirm Your Skill Tags</h2>
                            <p class="text-slate-500 text-sm mt-1">These tags decide which professionals get alerted. Tap to toggle, or add your own.</p>
                        </div>

                        {{-- Brief summary --}}
                        <div class="bg-light-canvas border border-slate-100 rounded-xl p-5 mb-6 animate-slide-in-right">
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Your Brief</p>
                            <p class="text-sm font-semibold text-slate-main">{{ $title }}</p>
                            <div class="flex items-center gap-3 mt-2 text-xs text-slate-500">
                                <span class="font-bold text-emerald-deep">₦{{ number_format((float) $budget) }}</span>
                                <span>·</span>
                                <span>{{ $isRemote ? 'Remote OK' : ($location ?: 'On-site') }}</span>
                                @if ($location)
                                    <span>·</span>
                                    <span>{{ $location }}</span>
                                @endif
                            </div>
                        </div>

                        {{-- Selected tags --}}
                        <div class="mb-5">
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">
                                Selected Tags <span class="text-emerald-deep">({{ count($selectedTags) }}/10)</span>
                            </p>
                            <div class="flex flex-wrap gap-2 min-h-9">
                                @forelse ($selectedTags as $i => $tag)
                                    <button
                                        type="button"
                                        wire:click="toggleTag('{{ $tag }}')"
                                        class="inline-flex items-center gap-1.5 bg-slate-main text-white text-xs font-semibold px-3.5 py-1.5 rounded-full animate-slide-in-right hover:bg-slate-700 transition-colors"
                                        style="animation-delay: {{ $i * 60 }}ms"
                                    >
                                        <svg class="w-3 h-3 text-emerald-main" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        {{ $tag }}
                                        <span class="text-white/40 ml-0.5">×</span>
                                    </button>
                                @empty
                                    <p class="text-xs text-slate-400 italic self-center">No tags selected — pick from the suggestions below.</p>
                                @endforelse
                            </div>
                            @error('selectedTags') <p class="mt-2 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- Taxonomy suggestions --}}
                        <div class="mb-5">
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Add From Skill Catalog</p>
                            <div class="flex flex-wrap gap-1.5 max-h-32 overflow-y-auto pr-1">
                                @foreach ($this->taxonomy as $skill)
                                    @unless (in_array($skill->name, $selectedTags))
                                        <button
                                            type="button"
                                            wire:click="toggleTag('{{ $skill->name }}')"
                                            class="text-[11px] font-semibold px-2.5 py-1 rounded-full border bg-white text-slate-600 border-slate-200 hover:border-emerald-main hover:text-emerald-deep transition-colors"
                                        >+ {{ $skill->name }}</button>
                                    @endunless
                                @endforeach
                            </div>
                        </div>

                        {{-- Custom tag --}}
                        <div class="flex gap-2 mb-7">
                            <input
                                type="text"
                                wire:model="customTag"
                                wire:keydown.enter.prevent="addCustomTag"
                                placeholder="Add a custom skill tag..."
                                class="flex-1 px-3 py-2.5 rounded-lg border border-slate-200 bg-light-canvas text-slate-main placeholder-slate-300 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-main/30 focus:border-emerald-main transition"
                            />
                            <button type="button" wire:click="addCustomTag"
                                class="px-4 py-2.5 bg-slate-main text-white text-xs font-semibold rounded-lg hover:bg-slate-700 transition-colors">
                                Add
                            </button>
                        </div>

                        <div class="flex gap-3">
                            <button wire:click="$set('step', 'form')" class="px-5 py-3 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50 transition">
                                ← Edit Brief
                            </button>
                            <button wire:click="confirmTags"
                                class="btn-lift flex-1 bg-emerald-main text-white font-bold py-3 rounded-xl hover:bg-emerald-deep transition-all shadow-md shadow-emerald-main/20"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-75 cursor-wait"
                            >
                                <span wire:loading.remove wire:target="confirmTags">Publish & Start Matching →</span>
                                <span wire:loading wire:target="confirmTags" class="flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                    Publishing...
                                </span>
                            </button>
                        </div>
                    </div>
                @endif

                {{-- ── STEP 3: LIVE RADAR ── --}}
                @if ($step === 'radar')
                    <div class="p-8 text-center animate-fade-in-up">
                        <div class="mb-8">
                            <h2 class="font-display text-2xl font-bold text-slate-main tracking-tight">Your Brief Is Live</h2>
                            <p class="text-slate-500 text-sm mt-1">Matched professionals are being alerted by email and in-app, in waves.</p>
                        </div>

                        {{-- Radar animation --}}
                        <div class="relative flex items-center justify-center w-52 h-52 mx-auto mb-8">
                            <span class="absolute inline-flex w-full h-full rounded-full bg-emerald-main/20 animate-pulse-ring"></span>
                            <span class="absolute inline-flex w-full h-full rounded-full bg-emerald-main/15 animate-pulse-ring" style="animation-delay: 0.5s"></span>
                            <span class="absolute inline-flex w-full h-full rounded-full bg-emerald-main/10 animate-pulse-ring" style="animation-delay: 1s"></span>

                            <div class="relative w-20 h-20 rounded-full bg-emerald-main shadow-lg shadow-emerald-main/30 flex items-center justify-center z-10">
                                <svg class="w-9 h-9 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                        </div>

                        {{-- Real match stats --}}
                        <div class="bg-light-canvas border border-slate-100 rounded-xl px-6 py-5 mb-8 inline-block min-w-72">
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-2">Matching Engine</p>
                            @if ($matchedCount > 0)
                                <p class="font-display text-4xl font-bold text-slate-main leading-none mb-1">{{ $matchedCount }}</p>
                                <p class="text-slate-main font-semibold text-sm leading-relaxed">
                                    matched {{ Str::plural('professional', $matchedCount) }} being alerted
                                </p>
                                <p class="text-xs text-slate-400 mt-1.5">Wave 1 goes out now · waves 2 &amp; 3 follow over 24h</p>
                            @else
                                <p class="text-slate-main font-semibold text-sm leading-relaxed">
                                    Your brief is live. We'll alert professionals as soon as matching profiles join.
                                </p>
                            @endif
                            <div class="flex items-center justify-center gap-1.5 mt-3">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-main animate-bounce" style="animation-delay: 0ms"></span>
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-main animate-bounce" style="animation-delay: 150ms"></span>
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-main animate-bounce" style="animation-delay: 300ms"></span>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3 justify-center">
                            @if ($briefUlid)
                                <a href="{{ route('client.brief.detail', ['ulid' => $briefUlid]) }}" wire:navigate
                                   class="btn-lift px-6 py-3 bg-emerald-main text-white font-semibold rounded-xl hover:bg-emerald-deep transition text-sm">
                                    View My Brief
                                </a>
                            @endif
                            <a href="{{ route('client.dashboard') }}" wire:navigate
                               class="px-6 py-3 bg-slate-main text-white font-semibold rounded-xl hover:bg-slate-main/90 transition text-sm">
                                Go to Dashboard
                            </a>
                            <button wire:click="restart" class="px-6 py-3 border border-slate-200 text-slate-600 font-semibold rounded-xl hover:bg-slate-50 transition text-sm">
                                Post Another Brief
                            </button>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>
