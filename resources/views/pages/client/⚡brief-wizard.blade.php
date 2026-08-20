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

new #[Title('Post a brief')] class extends Component
{
    /** One of: form, calibrating, live */
    public string $step = 'form';

    public string $title = '';

    public string $description = '';

    public string $budgetMin = '';

    public string $budgetMax = '';

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
            'budgetMin' => ['nullable', 'numeric', 'min:1000'],
            'budgetMax' => ['required', 'numeric', 'min:1000', 'gte:budgetMin'],
            'location' => ['nullable', 'string', 'max:120'],
        ], [
            'budgetMax.required' => __('Give professionals a budget to work with.'),
            'budgetMax.gte' => __('The top of the range cannot be lower than the bottom.'),
            'description.min' => __('A few more details will get you far better matches.'),
        ]);

        $this->selectedTags = $this->suggestTags();
        $this->step = 'calibrating';
    }

    public function toggleTag(string $tag): void
    {
        if (in_array($tag, $this->selectedTags, true)) {
            $this->selectedTags = array_values(array_diff($this->selectedTags, [$tag]));
        } elseif (count($this->selectedTags) < 10) {
            $this->selectedTags[] = $tag;
        }
    }

    public function addCustomTag(): void
    {
        $tag = trim($this->customTag);

        if ($tag !== '' && ! in_array($tag, $this->selectedTags, true) && count($this->selectedTags) < 10) {
            $this->selectedTags[] = $tag;
        }

        $this->customTag = '';
    }

    public function backToForm(): void
    {
        $this->step = 'form';
    }

    public function confirmTags(BriefService $briefService, MatchingService $matchingService): void
    {
        $this->validate(
            ['selectedTags' => ['required', 'array', 'min:1']],
            ['selectedTags.required' => __('Pick at least one skill so we know who to alert.')],
        );

        $brief = Brief::create([
            'client_id' => auth()->id(),
            'title' => $this->title,
            'description' => $this->description,
            'budget_min' => $this->budgetMin !== '' ? (int) $this->budgetMin : null,
            'budget_max' => (int) $this->budgetMax,
            'skill_tags' => array_values($this->selectedTags),
            'location' => $this->location !== '' ? $this->location : null,
            'is_remote' => $this->isRemote,
            'status' => BriefStatus::Draft,
        ]);

        $briefService->publish($brief);

        $this->matchedCount = $matchingService->findCandidates($brief->refresh())->count();
        $this->briefUlid = $brief->ulid;
        $this->step = 'live';
    }

    public function restart(): void
    {
        $this->reset();
        $this->step = 'form';
    }

    /**
     * @return \Illuminate\Support\Collection<string, \Illuminate\Support\Collection<int, Skill>>
     */
    #[Computed]
    public function taxonomy(): \Illuminate\Support\Collection
    {
        return Skill::active()->get()->groupBy('category');
    }

    /**
     * Keyword match the brief text against the skill taxonomy. Deliberately
     * simple: AI tagging arrives in closed beta (spec section 18.2).
     *
     * @return array<int, string>
     */
    private function suggestTags(): array
    {
        $text = Str::lower($this->title.' '.$this->description);

        return Skill::active()->get()
            ->filter(fn (Skill $skill): bool => str_contains($text, Str::lower($skill->name)))
            ->pluck('name')
            ->take(10)
            ->values()
            ->all();
    }
}; ?>

<div class="max-w-2xl mx-auto px-4 sm:px-6 py-8 sm:py-12">

    {{-- Progress. Three real steps, so numbering carries meaning. --}}
    @php
        $steps = ['form' => __('The brief'), 'calibrating' => __('Skills'), 'live' => __('Live')];
        $currentIndex = array_search($step, array_keys($steps), true);
    @endphp

    <ol class="flex items-center gap-2 mb-8">
        @foreach($steps as $key => $label)
            @php $index = $loop->index; @endphp
            <li class="flex items-center gap-2 {{ $loop->last ? '' : 'flex-1' }}">
                <span class="font-data text-[10px] w-5 h-5 grid place-items-center shrink-0 {{ $index <= $currentIndex ? 'bg-ink text-paper' : 'bg-line text-ink-faint' }}">
                    {{ $index + 1 }}
                </span>
                <span class="text-xs font-semibold whitespace-nowrap {{ $index <= $currentIndex ? 'text-ink' : 'text-ink-faint' }}">{{ $label }}</span>
                @unless($loop->last)
                    <span class="flex-1 h-px {{ $index < $currentIndex ? 'bg-ink' : 'bg-line' }}"></span>
                @endunless
            </li>
        @endforeach
    </ol>

    @if($step === 'form')
        <div class="animate-fade-in-up">
            <header class="pb-6 border-b border-line">
                <h1 class="font-display text-2xl sm:text-3xl text-ink leading-none">{{ __('Describe the work') }}</h1>
                <p class="mt-3 text-sm text-ink-soft max-w-prose">
                    {{ __('The clearer this is, the better the professionals who answer. Posting is free and takes about two minutes.') }}
                </p>
            </header>

            <form wire:submit="submitForm" class="mt-6 grid gap-6">
                <section class="panel p-5 sm:p-6 grid gap-5">
                    <flux:input
                        wire:model="title"
                        :label="__('What do you need?')"
                        :placeholder="__('Full brand identity for a logistics company')"
                        maxlength="255"
                        required
                    />

                    <flux:textarea
                        wire:model="description"
                        :label="__('Describe it properly')"
                        :placeholder="__('What the work involves, what success looks like, your timeline, and anything a professional would need to know before quoting.')"
                        rows="6"
                        required
                    />
                </section>

                <section class="panel p-5 sm:p-6 grid gap-5">
                    <div>
                        <p class="eyebrow">{{ __('Budget in naira') }}</p>
                        <p class="text-sm text-ink-soft mt-2">{{ __('A range is fine. Briefs without a budget get far fewer serious pitches.') }}</p>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <flux:input wire:model="budgetMin" :label="__('From (optional)')" type="number" min="1000" step="1000" placeholder="350000" />
                        <flux:input wire:model="budgetMax" :label="__('Up to')" type="number" min="1000" step="1000" placeholder="500000" required />
                    </div>
                </section>

                <section class="panel p-5 sm:p-6 grid gap-5">
                    <p class="eyebrow">{{ __('Where') }}</p>

                    <flux:checkbox wire:model.live="isRemote" :label="__('This can be done remotely')" />

                    @unless($isRemote)
                        <flux:input wire:model="location" :label="__('Location')" :placeholder="__('Lagos')" maxlength="120" />
                    @endunless
                </section>

                <div class="flex justify-end">
                    <button type="submit" class="btn-lift text-xs font-semibold px-5 py-2.5 bg-ink text-paper">
                        {{ __('Continue to skills') }}
                    </button>
                </div>
            </form>
        </div>

    @elseif($step === 'calibrating')
        <div class="animate-fade-in-up">
            <header class="pb-6 border-b border-line">
                <h1 class="font-display text-2xl sm:text-3xl text-ink leading-none">{{ __('Which skills does this need?') }}</h1>
                <p class="mt-3 text-sm text-ink-soft max-w-prose">
                    {{ __('This is what the matching engine reads. A professional is alerted when at least one of these is on their profile. We have pre-selected what we could read from your brief.') }}
                </p>
            </header>

            <div class="mt-6 panel p-5 sm:p-6 grid gap-6">

                @if(count($selectedTags) > 0)
                    <div class="grid gap-3">
                        <p class="eyebrow">{{ __('Selected') }} ({{ count($selectedTags) }}/10)</p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($selectedTags as $tag)
                                <button type="button" wire:click="toggleTag('{{ addslashes($tag) }}')" class="tag" data-hit="true" wire:key="selected-{{ $tag }}">
                                    {{ $tag }} <span class="ml-1.5">&times;</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                @error('selectedTags')
                    <p class="text-xs text-critical">{{ $message }}</p>
                @enderror

                @foreach($this->taxonomy as $category => $skills)
                    <div class="grid gap-3" wire:key="category-{{ $category }}">
                        <p class="eyebrow">{{ $category }}</p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($skills as $skill)
                                <button
                                    type="button"
                                    wire:click="toggleTag('{{ addslashes($skill->name) }}')"
                                    wire:key="skill-{{ $skill->id }}"
                                    class="tag transition-colors {{ in_array($skill->name, $selectedTags, true) ? '' : 'hover:border-ink-faint' }}"
                                    @if(in_array($skill->name, $selectedTags, true)) data-hit="true" @endif
                                >{{ $skill->name }}</button>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="grid gap-3 pt-5 border-t border-line-soft">
                    <p class="eyebrow">{{ __('Something else?') }}</p>
                    <div class="flex gap-2">
                        <flux:input wire:model="customTag" wire:keydown.enter.prevent="addCustomTag" :placeholder="__('Add your own skill')" class="flex-1" />
                        <button type="button" wire:click="addCustomTag" class="text-xs font-semibold px-4 border border-line text-ink hover:border-ink-faint transition-colors">
                            {{ __('Add') }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-between gap-3">
                <button type="button" wire:click="backToForm" class="text-xs font-semibold px-4 py-2.5 border border-line text-ink-soft hover:text-ink hover:border-ink-faint transition-colors">
                    {{ __('Back') }}
                </button>
                <button type="button" wire:click="confirmTags" wire:loading.attr="disabled" class="btn-lift text-xs font-semibold px-5 py-2.5 bg-ink text-paper">
                    <span wire:loading.remove wire:target="confirmTags">{{ __('Publish brief') }}</span>
                    <span wire:loading wire:target="confirmTags">{{ __('Publishing') }}</span>
                </button>
            </div>
        </div>

    @else
        <div class="animate-fade-in-up">
            <div class="bg-ink p-8 sm:p-12 grid gap-5">
                <p class="eyebrow text-paper/40">{{ __('Published') }}</p>

                <h1 class="font-display text-2xl sm:text-3xl text-paper leading-tight max-w-[22ch]">
                    {{ __('Your brief is live and the first wave is out') }}
                </h1>

                <div class="grid gap-2">
                    <div class="wave-track">
                        <span class="wave-seg" data-state="live" style="--wave-progress: 0.08"></span>
                        <span class="wave-seg"></span>
                        <span class="wave-seg"></span>
                    </div>
                    <div class="wave-meta">
                        <span class="wave-who">{{ __('Wave 1') }} &middot; {{ min(10, $matchedCount) }} {{ __('pros') }}</span>
                        <span class="wave-when !text-paper/40">{{ __('opens wider in 6h') }}</span>
                    </div>
                </div>

                <p class="text-sm text-paper/60 max-w-prose">
                    @if($matchedCount > 0)
                        {{ trans_choice(
                            '{1}:count professional matched your brief. The best ten hear about it first, and more are notified over the next day if you need them.|[2,*]:count professionals matched your brief. The best ten hear about it first, and more are notified over the next day if you need them.',
                            $matchedCount,
                            ['count' => $matchedCount],
                        ) }}
                    @else
                        {{ __('No professional matches these skills yet. Your brief stays live, and anyone who joins with matching skills will be alerted.') }}
                    @endif
                </p>

                <div class="flex flex-wrap gap-2 mt-2">
                    <a href="{{ route('client.brief.detail', ['ulid' => $briefUlid]) }}" wire:navigate
                       class="btn-lift text-xs font-semibold px-5 py-2.5 bg-brand-deep text-paper">
                        {{ __('View your brief') }}
                    </a>
                    <button type="button" wire:click="restart"
                            class="text-xs font-semibold px-5 py-2.5 border border-paper/25 text-paper/80 hover:text-paper hover:border-paper/50 transition-colors">
                        {{ __('Post another') }}
                    </button>
                </div>
            </div>

            <div class="mt-4 panel p-5 grid gap-1.5">
                <p class="text-sm font-semibold text-ink">{{ __('What happens now') }}</p>
                <p class="text-sm text-ink-soft max-w-prose">
                    {{ __('Professionals who want the job spend a credit to reach you. That cost is deliberate: it means the people who get in touch have decided your brief is worth their money, so you get fewer messages and better ones.') }}
                </p>
            </div>
        </div>
    @endif
</div>
