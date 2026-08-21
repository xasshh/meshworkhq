<?php

use Flux\Flux;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Profile')] class extends Component
{
    use WithFileUploads;

    public string $professional_title = '';

    public string $bio = '';

    public string $phone = '';

    public bool $showHireCount = true;

    public string $newPortfolioUrl = '';

    public string $newSkill = '';

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $photo = null;

    /** @var array<int, string> */
    public array $portfolioUrls = [];

    /** @var array<int, string> */
    public array $skills = [];

    public function mount(): void
    {
        $user = auth()->user();

        $this->professional_title = $user->professional_title ?? '';
        $this->bio = $user->bio ?? '';
        $this->phone = $user->phone ?? '';
        $this->portfolioUrls = array_values(array_filter([$user->portfolio_url ?? '']));
        $this->skills = $user->skill_tags ?? [];
        $this->showHireCount = (bool) $user->show_hire_count;
    }

    public function updatedPhoto(): void
    {
        $this->validate(['photo' => ['image', 'max:2048', 'mimes:jpeg,jpg,png']]);

        $user = auth()->user();
        $previous = $user->avatar_path;

        $user->update(['avatar_path' => $this->photo->store('avatars', 'public')]);

        if ($previous) {
            Storage::disk('public')->delete($previous);
        }

        $this->photo = null;

        Flux::toast(variant: 'success', text: __('Photo updated.'));
    }

    public function save(): void
    {
        $this->validate([
            'professional_title' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:50'],
            'portfolioUrls.*' => ['url', 'max:255'],
        ]);

        auth()->user()->update([
            'professional_title' => $this->professional_title ?: null,
            'bio' => $this->bio ?: null,
            'phone' => $this->phone ?: null,
            'portfolio_url' => $this->portfolioUrls[0] ?? null,
            'skill_tags' => $this->skills,
            'show_hire_count' => $this->showHireCount,
        ]);

        Flux::toast(variant: 'success', text: __('Profile saved.'));
    }

    public function addPortfolioUrl(): void
    {
        $url = trim($this->newPortfolioUrl);

        if ($url === '' || in_array($url, $this->portfolioUrls, true) || count($this->portfolioUrls) >= 5) {
            return;
        }

        $this->portfolioUrls[] = $url;
        $this->newPortfolioUrl = '';
    }

    public function removePortfolioUrl(int $index): void
    {
        unset($this->portfolioUrls[$index]);
        $this->portfolioUrls = array_values($this->portfolioUrls);
    }

    public function addSkill(?string $skill = null): void
    {
        $skill = trim($skill ?? $this->newSkill);

        if ($skill === '' || in_array($skill, $this->skills, true) || count($this->skills) >= 15) {
            return;
        }

        $this->skills[] = $skill;
        $this->newSkill = '';
    }

    public function removeSkill(int $index): void
    {
        unset($this->skills[$index]);
        $this->skills = array_values($this->skills);
    }

    #[Computed]
    public function completeness(): int
    {
        return auth()->user()->profileCompleteness();
    }

    /** @return array<int, string> */
    #[Computed]
    public function missing(): array
    {
        return auth()->user()->missingProfileFields();
    }
}; ?>

<div class="shell-narrow py-8 sm:py-10">

    <x-page-header
        :eyebrow="__('Account')"
        :title="__('Your profile')"
        :description="__('The matching engine reads this. The more it knows, the better the briefs it sends you.')"
    />

    {{-- Completeness. Named fields, not just a percentage. --}}
    <section class="panel p-5 sm:p-6 mt-6 grid gap-4">
        <div class="flex items-end justify-between gap-4 flex-wrap">
            <div>
                <p class="eyebrow">{{ __('Profile strength') }}</p>
                <p class="font-data text-3xl font-medium text-ink leading-none mt-2">{{ $this->completeness }}%</p>
            </div>

            @if($this->completeness >= 70)
                <span class="pill" data-tone="live">{{ __('Receiving alerts') }}</span>
            @else
                <span class="pill" data-tone="warn">{{ __('Alerts paused below 70%') }}</span>
            @endif
        </div>

        <div class="grid grid-flow-col gap-[3px]" aria-hidden="true">
            @for($i = 1; $i <= 20; $i++)
                <span class="h-2 {{ $i * 5 <= $this->completeness ? 'bg-brand' : 'bg-line' }}"></span>
            @endfor
        </div>

        @if(count($this->missing) > 0)
            <p class="text-sm text-ink-soft">
                {{ __('Still to add:') }}
                <span class="text-ink font-semibold">{{ implode(', ', $this->missing) }}</span>
            </p>
        @else
            <p class="text-sm text-ink-soft">{{ __('Everything is filled in. You are in the strongest position for wave 1 matching.') }}</p>
        @endif
    </section>

    <form wire:submit="save" class="grid gap-6 mt-6">

        {{-- Photo --}}
        <section class="panel p-5 sm:p-6 grid gap-4">
            <p class="eyebrow">{{ __('Photo') }}</p>

            <div class="flex items-center gap-5 flex-wrap">
                <div class="w-20 h-20 bg-chalk-soft border border-line grid place-items-center overflow-hidden shrink-0">
                    @if($photo)
                        <img src="{{ $photo->temporaryUrl() }}" alt="" class="w-full h-full object-cover">
                    @elseif(auth()->user()->avatar_path)
                        <img src="{{ Storage::disk('public')->url(auth()->user()->avatar_path) }}" alt="" class="w-full h-full object-cover">
                    @else
                        <span class="font-display text-xl text-ink-faint">{{ auth()->user()->initials() }}</span>
                    @endif
                </div>

                <div class="grid gap-1.5">
                    <label class="btn btn-ink btn-sm cursor-pointer w-fit">
                        <input type="file" wire:model="photo" accept="image/jpeg,image/png" class="sr-only">
                        <span wire:loading.remove wire:target="photo">{{ __('Choose a photo') }}</span>
                        <span wire:loading wire:target="photo">{{ __('Uploading') }}</span>
                    </label>
                    <p class="text-[11px] text-ink-faint">{{ __('JPG or PNG, up to 2MB.') }}</p>
                    @error('photo') <p class="text-[11px] text-critical">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        {{-- Identity --}}
        <section class="panel p-5 sm:p-6 grid gap-5">
            <p class="eyebrow">{{ __('How clients see you') }}</p>

            <flux:input
                wire:model="professional_title"
                :label="__('Professional title')"
                :placeholder="__('Brand designer and art director')"
                maxlength="255"
            />

            <flux:textarea
                wire:model="bio"
                :label="__('Short bio')"
                :placeholder="__('What you do, who you do it for, and what makes your work worth paying for.')"
                rows="4"
                maxlength="500"
            />
            <p class="text-[11px] text-ink-faint -mt-3">{{ strlen($bio) }} / 500</p>

            <flux:input
                wire:model="phone"
                :label="__('Phone number')"
                type="tel"
                placeholder="0803 000 0000"
                maxlength="50"
            />
        </section>

        {{-- Skills --}}
        <section class="panel p-5 sm:p-6 grid gap-4">
            <div>
                <p class="eyebrow">{{ __('Skills') }}</p>
                <p class="text-sm text-ink-soft mt-2">{{ __('A brief reaches you when at least one skill here matches its tags. Up to 15.') }}</p>
            </div>

            @if(count($skills) > 0)
                <div class="flex flex-wrap gap-1.5">
                    @foreach($skills as $index => $skill)
                        <span class="tag" data-hit="true" wire:key="skill-{{ $index }}">
                            {{ $skill }}
                            <button type="button" wire:click="removeSkill({{ $index }})" class="ml-1.5 hover:text-critical transition-colors" aria-label="{{ __('Remove :skill', ['skill' => $skill]) }}">&times;</button>
                        </span>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-ink-faint">{{ __('No skills yet. Without at least one, the matching engine cannot reach you.') }}</p>
            @endif

            @if(count($skills) < 15)
                <div class="flex gap-2">
                    <flux:input wire:model="newSkill" wire:keydown.enter.prevent="addSkill" :placeholder="__('Add a skill')" class="flex-1" />
                    <button type="button" wire:click="addSkill" class="text-xs font-semibold px-4 border border-line text-ink hover:border-ink-faint transition-colors">
                        {{ __('Add') }}
                    </button>
                </div>
            @endif
        </section>

        {{-- Track record --}}
        <section class="panel p-5 sm:p-6 grid gap-4">
            <div>
                <p class="eyebrow">{{ __('Track record') }}</p>
                <p class="text-sm text-ink-soft mt-2">
                    {{ __('Clients have no reviews to read yet, so the number of times you have been hired is the strongest proof you have. It is counted from real hires and cannot be edited.') }}
                </p>
            </div>

            <div class="flex items-center justify-between gap-4 flex-wrap py-3 border-y border-line-soft">
                <div>
                    <p class="text-sm font-semibold text-ink">
                        {{ trans_choice('{0}Not hired through Meshwork HQ yet|{1}Hired :count time|[2,*]Hired :count times', auth()->user()->hiresCount(), ['count' => auth()->user()->hiresCount()]) }}
                    </p>
                    <p class="text-xs text-ink-faint mt-1">{{ __('Shown on your directory card and in conversations.') }}</p>
                </div>
                <x-track-record :user="auth()->user()" />
            </div>

            <flux:checkbox wire:model="showHireCount" :label="__('Show my hire count to clients')" />
        </section>

        {{-- Portfolio --}}
        <section class="panel p-5 sm:p-6 grid gap-4">
            <div>
                <p class="eyebrow">{{ __('Portfolio') }}</p>
                <p class="text-sm text-ink-soft mt-2">{{ __('Links to work you want clients to see. The first one shows on your public profile.') }}</p>
            </div>

            @if(count($portfolioUrls) > 0)
                <ul class="grid gap-2">
                    @foreach($portfolioUrls as $index => $url)
                        <li class="flex items-center gap-3 py-2 px-3 bg-chalk-soft border border-line" wire:key="portfolio-{{ $index }}">
                            <span class="font-data text-xs text-ink truncate flex-1">{{ $url }}</span>
                            <button type="button" wire:click="removePortfolioUrl({{ $index }})" class="text-ink-faint hover:text-critical transition-colors text-sm" aria-label="{{ __('Remove link') }}">&times;</button>
                        </li>
                    @endforeach
                </ul>
            @endif

            @error('portfolioUrls.*') <p class="text-[11px] text-critical">{{ $message }}</p> @enderror

            @if(count($portfolioUrls) < 5)
                <div class="flex gap-2">
                    <flux:input wire:model="newPortfolioUrl" wire:keydown.enter.prevent="addPortfolioUrl" type="url" placeholder="https://" class="flex-1" />
                    <button type="button" wire:click="addPortfolioUrl" class="text-xs font-semibold px-4 border border-line text-ink hover:border-ink-faint transition-colors">
                        {{ __('Add') }}
                    </button>
                </div>
            @endif
        </section>

        <div class="flex items-center justify-end gap-3">
            <button type="submit" wire:loading.attr="disabled" class="btn btn-ink btn-sm">
                <span wire:loading.remove wire:target="save">{{ __('Save profile') }}</span>
                <span wire:loading wire:target="save">{{ __('Saving') }}</span>
            </button>
        </div>
    </form>
</div>
