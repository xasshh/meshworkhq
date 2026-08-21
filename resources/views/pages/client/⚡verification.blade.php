<?php

use App\Enums\VerificationStatus;
use App\Exceptions\VerificationNotAllowedException;
use App\Services\VerificationService;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Verification')] class extends Component
{
    use WithFileUploads;

    /** 'nin' for an individual, 'cac' for a registered company. */
    public string $method = 'nin';

    public string $nin = '';

    public string $rcNumber = '';

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $certificate = null;

    public function mount(): void
    {
        $this->method = auth()->user()->company_name ? 'cac' : 'nin';
    }

    #[Computed]
    public function status(): VerificationStatus
    {
        return auth()->user()->verification_status;
    }

    public function submit(VerificationService $verification): void
    {
        $user = auth()->user();

        try {
            if ($this->method === 'nin') {
                $this->validate([
                    'nin' => ['required', 'digits:11'],
                ], [
                    'nin.digits' => __('A NIN is 11 digits.'),
                ]);

                $status = $verification->submitNin($user, $this->nin);

                // Out of the component state the moment it has been used.
                $this->nin = '';
            } else {
                $this->validate([
                    'rcNumber' => ['required', 'string', 'max:30'],
                    'certificate' => ['required', 'file', 'mimes:pdf,jpeg,jpg,png', 'max:5120'],
                ], [
                    'certificate.required' => __('Attach your CAC certificate.'),
                ]);

                $status = $verification->submitCac($user, $this->rcNumber, $this->certificate);

                $this->certificate = null;
            }

            Flux::toast(
                variant: $status === VerificationStatus::Verified ? 'success' : 'default',
                text: $status === VerificationStatus::Verified
                    ? __('You are verified.')
                    : __('Submitted. We will review this and let you know.'),
            );

            unset($this->status);
        } catch (VerificationNotAllowedException $e) {
            Flux::toast(variant: 'warning', text: $e->getMessage());
        }
    }
}; ?>

<div class="max-w-2xl mx-auto px-4 sm:px-6 py-8 sm:py-10">

    <x-page-header
        :eyebrow="__('Account')"
        :title="__('Get verified')"
        :description="__('Professionals spend a credit to reach you. Verification tells them there is a real, accountable person behind the brief, and it is the single biggest thing you can do to get better pitches.')"
    />

    {{-- Current state --}}
    <section class="panel p-5 sm:p-6 mt-6 grid gap-3">
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <p class="eyebrow">{{ __('Status') }}</p>
            <x-verified-badge :user="auth()->user()" :show-unverified="true" />
        </div>

        @if($this->status === VerificationStatus::Verified)
            <p class="text-sm text-ink-soft">
                {{ __('Your badge is showing on every brief you post and in every conversation.') }}
                @if(auth()->user()->verification_type === 'cac')
                    {{ __('Verified as a registered company, :rc.', ['rc' => auth()->user()->verification_reference]) }}
                @endif
            </p>
        @elseif($this->status === VerificationStatus::Pending)
            <p class="text-sm text-ink-soft">
                {{ __('Submitted :when. Every submission is checked by a person, and we will email you as soon as there is a decision.', ['when' => auth()->user()->verification_submitted_at?->diffForHumans()]) }}
            </p>
        @elseif($this->status === VerificationStatus::Rejected)
            <p class="text-sm text-ink-soft">
                {{ __('We could not confirm your last submission.') }}
                @if(auth()->user()->verification_notes)
                    <span class="text-ink font-semibold">{{ auth()->user()->verification_notes }}</span>
                @endif
                {{ __('You can submit again below.') }}
            </p>
        @else
            <p class="text-sm text-ink-soft">
                {{ __('Not verified yet. Briefs from unverified accounts still reach professionals, they just carry less weight.') }}
            </p>
        @endif
    </section>

    @if($this->status->canSubmit())
        <form wire:submit="submit" class="grid gap-6 mt-6">

            {{-- Method --}}
            <section class="panel p-5 sm:p-6 grid gap-4">
                <p class="eyebrow">{{ __('How are you hiring?') }}</p>

                <div class="grid sm:grid-cols-2 gap-3">
                    <label class="cursor-pointer border p-4 grid gap-1.5 transition-colors {{ $method === 'nin' ? 'border-ink bg-chalk-soft' : 'border-line hover:border-ink-faint' }}">
                        <input type="radio" wire:model.live="method" value="nin" class="sr-only">
                        <span class="text-sm font-semibold text-ink">{{ __('As an individual') }}</span>
                        <span class="text-xs text-ink-soft">{{ __('Verify with your National Identification Number.') }}</span>
                    </label>

                    <label class="cursor-pointer border p-4 grid gap-1.5 transition-colors {{ $method === 'cac' ? 'border-ink bg-chalk-soft' : 'border-line hover:border-ink-faint' }}">
                        <input type="radio" wire:model.live="method" value="cac" class="sr-only">
                        <span class="text-sm font-semibold text-ink">{{ __('As a company') }}</span>
                        <span class="text-xs text-ink-soft">{{ __('Verify with your CAC registration.') }}</span>
                    </label>
                </div>
            </section>

            @if($method === 'nin')
                <section class="panel p-5 sm:p-6 grid gap-5">
                    <div>
                        <p class="eyebrow">{{ __('National Identification Number') }}</p>
                        <p class="text-sm text-ink-soft mt-2">
                            {{ __('We check your NIN against the national register and keep only the result and the last four digits. The full number is never stored.') }}
                        </p>
                    </div>

                    <flux:input
                        wire:model="nin"
                        :label="__('NIN')"
                        inputmode="numeric"
                        maxlength="11"
                        placeholder="12345678901"
                        autocomplete="off"
                    />

                    <p class="text-[11px] text-ink-faint">
                        {{ __('The name on your NIN must match the name on this account.') }}
                    </p>
                </section>
            @else
                <section class="panel p-5 sm:p-6 grid gap-5">
                    <div>
                        <p class="eyebrow">{{ __('CAC registration') }}</p>
                        <p class="text-sm text-ink-soft mt-2">
                            {{ __('Give us your registration number and your certificate. We check both against the public CAC register, then delete the document once the decision is made.') }}
                        </p>
                    </div>

                    <flux:input
                        wire:model="rcNumber"
                        :label="__('Registration number')"
                        placeholder="RC1234567"
                        maxlength="30"
                    />

                    <div class="grid gap-2">
                        <label class="text-sm font-semibold text-ink">{{ __('Certificate of incorporation') }}</label>

                        <label class="btn btn-ink btn-sm cursor-pointer w-fit">
                            <input type="file" wire:model="certificate" accept=".pdf,image/jpeg,image/png" class="sr-only">
                            <span wire:loading.remove wire:target="certificate">
                                {{ $certificate ? __('Choose a different file') : __('Choose a file') }}
                            </span>
                            <span wire:loading wire:target="certificate">{{ __('Uploading') }}</span>
                        </label>

                        @if($certificate)
                            <p class="font-data text-[11px] text-ink">{{ $certificate->getClientOriginalName() }}</p>
                        @endif

                        <p class="text-[11px] text-ink-faint">{{ __('PDF, JPG or PNG, up to 5MB. Stored privately and deleted after review.') }}</p>
                        @error('certificate') <p class="text-[11px] text-critical">{{ $message }}</p> @enderror
                    </div>
                </section>
            @endif

            <div class="flex items-center justify-end">
                <button type="submit" wire:loading.attr="disabled" class="btn btn-ink btn-sm">
                    <span wire:loading.remove wire:target="submit">{{ __('Submit for verification') }}</span>
                    <span wire:loading wire:target="submit">{{ __('Submitting') }}</span>
                </button>
            </div>
        </form>
    @endif
</div>
