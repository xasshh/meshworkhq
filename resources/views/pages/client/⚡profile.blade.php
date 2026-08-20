<?php

use Flux\Flux;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Company profile')] class extends Component
{
    use WithFileUploads;

    public string $companyName = '';

    public string $companyRole = '';

    public string $companySize = '';

    public string $companyDescription = '';

    public string $companyServices = '';

    public bool $showEngagementCount = true;

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $logo = null;

    public function mount(): void
    {
        $user = auth()->user();

        $this->companyName = $user->company_name ?? '';
        $this->companyRole = $user->company_role ?? '';
        $this->companySize = $user->company_size ?? '';
        $this->companyDescription = $user->company_description ?? '';
        $this->companyServices = $user->company_services ?? '';
        $this->showEngagementCount = (bool) $user->show_engagement_count;
    }

    public function updatedLogo(): void
    {
        $this->validate(['logo' => ['image', 'max:2048', 'mimes:jpeg,jpg,png']]);

        $user = auth()->user();
        $previous = $user->logo_path;

        $user->update(['logo_path' => $this->logo->store('logos', 'public')]);

        if ($previous) {
            Storage::disk('public')->delete($previous);
        }

        $this->logo = null;

        Flux::toast(variant: 'success', text: __('Logo updated.'));
    }

    public function save(): void
    {
        $this->validate([
            'companyName' => ['nullable', 'string', 'max:255'],
            'companyRole' => ['nullable', 'string', 'max:255'],
            'companySize' => ['nullable', 'string', Rule::in(['solo', '2-10', '11-50', '51-200', '200+'])],
            'companyDescription' => ['nullable', 'string', 'max:1000'],
            'companyServices' => ['nullable', 'string', 'max:500'],
        ]);

        auth()->user()->update([
            'company_name' => $this->companyName ?: null,
            'company_role' => $this->companyRole ?: null,
            'company_size' => $this->companySize ?: null,
            'company_description' => $this->companyDescription ?: null,
            'company_services' => $this->companyServices ?: null,
            'show_engagement_count' => $this->showEngagementCount,
        ]);

        Flux::toast(variant: 'success', text: __('Company profile saved.'));
    }
}; ?>

<div class="max-w-3xl mx-auto px-4 sm:px-6 py-8 sm:py-10">

    <x-page-header
        :eyebrow="__('Account')"
        :title="__('Company profile')"
        :description="__('Professionals see this when they decide whether your brief is worth a credit. A filled in profile gets better pitches.')"
    />

    <form wire:submit="save" class="grid gap-6 mt-6">

        {{-- Logo --}}
        <section class="panel p-5 sm:p-6 grid gap-4">
            <p class="eyebrow">{{ __('Logo') }}</p>

            <div class="flex items-center gap-5 flex-wrap">
                <div class="w-20 h-20 bg-chalk-soft border border-line grid place-items-center overflow-hidden shrink-0">
                    @if($logo)
                        <img src="{{ $logo->temporaryUrl() }}" alt="" class="w-full h-full object-contain p-2">
                    @elseif(auth()->user()->logo_path)
                        <img src="{{ Storage::disk('public')->url(auth()->user()->logo_path) }}" alt="" class="w-full h-full object-contain p-2">
                    @else
                        <span class="font-display text-xl text-ink-faint">{{ auth()->user()->initials() }}</span>
                    @endif
                </div>

                <div class="grid gap-1.5">
                    <label class="btn-lift inline-flex items-center text-xs font-semibold px-4 py-2.5 bg-ink text-paper cursor-pointer w-fit">
                        <input type="file" wire:model="logo" accept="image/jpeg,image/png" class="sr-only">
                        <span wire:loading.remove wire:target="logo">{{ __('Choose a logo') }}</span>
                        <span wire:loading wire:target="logo">{{ __('Uploading') }}</span>
                    </label>
                    <p class="text-[11px] text-ink-faint">{{ __('JPG or PNG, up to 2MB.') }}</p>
                    @error('logo') <p class="text-[11px] text-critical">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        {{-- Company --}}
        <section class="panel p-5 sm:p-6 grid gap-5">
            <p class="eyebrow">{{ __('The business') }}</p>

            <flux:input wire:model="companyName" :label="__('Company name')" maxlength="255" />

            <flux:input wire:model="companyRole" :label="__('Your role')" :placeholder="__('Operations Director')" maxlength="255" />

            <flux:select wire:model="companySize" :label="__('Company size')" :placeholder="__('Select a size')">
                <flux:select.option value="solo">{{ __('Just me') }}</flux:select.option>
                <flux:select.option value="2-10">{{ __('2 to 10 people') }}</flux:select.option>
                <flux:select.option value="11-50">{{ __('11 to 50 people') }}</flux:select.option>
                <flux:select.option value="51-200">{{ __('51 to 200 people') }}</flux:select.option>
                <flux:select.option value="200+">{{ __('Over 200 people') }}</flux:select.option>
            </flux:select>
        </section>

        {{-- Background --}}
        <section class="panel p-5 sm:p-6 grid gap-5">
            <p class="eyebrow">{{ __('What professionals should know') }}</p>

            <flux:textarea
                wire:model="companyDescription"
                :label="__('About the business')"
                :placeholder="__('What the company does, who it serves, and how it works with outside talent.')"
                rows="4"
                maxlength="1000"
            />

            <flux:textarea
                wire:model="companyServices"
                :label="__('What you usually hire for')"
                :placeholder="__('Brand work, product design, occasional development support.')"
                rows="3"
                maxlength="500"
            />
        </section>

        {{-- Track record --}}
        <section class="panel p-5 sm:p-6 grid gap-4">
            <div>
                <p class="eyebrow">{{ __('Track record') }}</p>
                <p class="text-sm text-ink-soft mt-2">
                    {{ __('Professionals spend a credit to reach you, so they weigh up whether your briefs are worth answering. Showing how many have pitched to you before is the clearest signal that they are.') }}
                </p>
            </div>

            <div class="flex items-center justify-between gap-4 flex-wrap py-3 border-y border-line-soft">
                <div>
                    <p class="text-sm font-semibold text-ink">
                        {{ trans_choice('{0}No professional has pitched to you yet|{1}:count professional has pitched to you|[2,*]:count professionals have pitched to you', auth()->user()->engagementCount(), ['count' => auth()->user()->engagementCount()]) }}
                    </p>
                    <p class="text-xs text-ink-faint mt-1">{{ __('Shown to professionals when they open one of your briefs.') }}</p>
                </div>
                <x-track-record :user="auth()->user()" />
            </div>

            <flux:checkbox wire:model="showEngagementCount" :label="__('Show my track record to professionals')" />
        </section>

        <div class="flex items-center justify-end">
            <button type="submit" wire:loading.attr="disabled" class="btn-lift text-xs font-semibold px-5 py-2.5 bg-ink text-paper">
                <span wire:loading.remove wire:target="save">{{ __('Save profile') }}</span>
                <span wire:loading wire:target="save">{{ __('Saving') }}</span>
            </button>
        </div>
    </form>
</div>
