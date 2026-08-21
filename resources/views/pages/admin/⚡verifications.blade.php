<?php

use App\Enums\VerificationStatus;
use App\Models\User;
use App\Services\VerificationService;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Verifications')] class extends Component
{
    /** The submission currently open for a decision. */
    public ?int $reviewing = null;

    public string $legalName = '';

    public string $reason = '';

    /** @return \Illuminate\Database\Eloquent\Collection<int, User> */
    #[Computed]
    public function pending()
    {
        return User::where('verification_status', VerificationStatus::Pending)
            ->orderBy('verification_submitted_at')
            ->get();
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, User> */
    #[Computed]
    public function decided()
    {
        return User::whereIn('verification_status', [VerificationStatus::Verified, VerificationStatus::Rejected])
            ->whereNotNull('verification_reviewed_at')
            ->orderByDesc('verification_reviewed_at')
            ->limit(10)
            ->get();
    }

    public function open(int $id): void
    {
        $applicant = $this->pendingOrFail($id);

        $this->reviewing = $id;
        $this->legalName = $applicant->company_name ?: $applicant->name;
        $this->reason = '';
    }

    public function cancel(): void
    {
        $this->reset(['reviewing', 'legalName', 'reason']);
    }

    public function approve(VerificationService $verification): void
    {
        $applicant = $this->pendingOrFail($this->reviewing);

        $this->validate([
            'legalName' => ['required', 'string', 'max:255'],
        ], [
            'legalName.required' => __('Record the legal name you checked against.'),
        ]);

        $verification->approve($applicant, $this->legalName);

        unset($this->pending, $this->decided);
        $this->cancel();

        Flux::toast(variant: 'success', text: __('Approved. They have been emailed.'));
    }

    public function reject(VerificationService $verification): void
    {
        $applicant = $this->pendingOrFail($this->reviewing);

        $this->validate([
            'reason' => ['required', 'string', 'max:500'],
        ], [
            'reason.required' => __('Say why, so they know what to correct.'),
        ]);

        $verification->reject($applicant, $this->reason);

        unset($this->pending, $this->decided);
        $this->cancel();

        Flux::toast(text: __('Rejected. They have been emailed the reason.'));
    }

    /**
     * Never act on an account that is not actually awaiting a decision: the id
     * arrives from the browser, and two reviewers could be looking at once.
     */
    private function pendingOrFail(?int $id): User
    {
        return User::where('id', $id)
            ->where('verification_status', VerificationStatus::Pending)
            ->firstOrFail();
    }
}; ?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8 sm:py-10">

    <x-page-header
        :eyebrow="__('Admin')"
        :title="__('Verifications')"
        :description="__('Clients waiting on a decision. They have been told they will hear back either way, so nothing should sit here long.')"
    />

    <div class="mt-6 grid gap-4">
        @forelse($this->pending as $applicant)
            <article class="panel p-5 grid gap-4" wire:key="pending-{{ $applicant->id }}">

                <div class="flex flex-wrap items-start justify-between gap-x-4 gap-y-2">
                    <div class="min-w-0">
                        <h2 class="font-display text-base text-ink">{{ $applicant->name }}</h2>
                        <p class="text-xs text-ink-faint mt-0.5 wrap-anywhere">{{ $applicant->email }}</p>
                    </div>
                    <span class="pill" data-tone="warn">
                        {{ strtoupper($applicant->verification_type ?: 'unknown') }}
                    </span>
                </div>

                <div class="seal" data-open="true">
                    <div class="seal-row">
                        <span class="seal-key">{{ __('Company') }}</span>
                        <span class="seal-value">{{ $applicant->company_name ?: __('Not given') }}</span>
                    </div>
                    <div class="seal-row">
                        <span class="seal-key">{{ $applicant->verification_type === 'cac' ? __('RC number') : __('NIN ends') }}</span>
                        <span class="seal-value">{{ $applicant->verification_reference ?: __('Not given') }}</span>
                    </div>
                    <div class="seal-row">
                        <span class="seal-key">{{ __('Waiting') }}</span>
                        <span class="seal-value">{{ $applicant->verification_submitted_at?->diffForHumans() }}</span>
                    </div>
                </div>

                @if($applicant->verification_type === 'cac' && $applicant->verification_document_path)
                    <a href="{{ route('admin.verifications.document', ['user' => $applicant->id]) }}"
                       target="_blank" rel="noopener"
                       class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-deep hover:text-ink transition-colors w-fit">
                        <flux:icon name="document-text" variant="micro" />
                        {{ __('Open the certificate') }}
                    </a>
                    <p class="text-[11px] text-ink-faint">
                        {{ __('Check the company name and RC number against the CAC register before deciding. The file is deleted once you record a decision.') }}
                    </p>
                @elseif($applicant->verification_type === 'nin')
                    <p class="text-[11px] text-ink-faint max-w-prose">
                        {{ __('Only the last four digits are stored, so there is nothing here to check a NIN against. Approve on the strength of what you already know about this client.') }}
                    </p>
                @endif

                @if($this->reviewing === $applicant->id)
                    <div class="grid gap-4 pt-4 border-t border-line-soft">
                        <flux:input
                            wire:model="legalName"
                            :label="__('Legal name you checked against')"
                            :placeholder="__('As written on the certificate')"
                        />
                        <flux:textarea
                            wire:model="reason"
                            rows="2"
                            :label="__('Reason, only needed to reject')"
                            :placeholder="__('The certificate did not match the registration number.')"
                        />

                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" wire:click="approve" wire:loading.attr="disabled"
                                    class="btn-lift text-xs font-semibold px-5 py-2.5 bg-ink text-paper">
                                {{ __('Approve') }}
                            </button>
                            <button type="button" wire:click="reject" wire:loading.attr="disabled"
                                    class="btn-lift text-xs font-semibold px-5 py-2.5 border border-critical text-critical">
                                {{ __('Reject') }}
                            </button>
                            <button type="button" wire:click="cancel"
                                    class="text-xs font-semibold text-ink-soft hover:text-ink transition-colors px-2">
                                {{ __('Cancel') }}
                            </button>
                        </div>
                    </div>
                @else
                    <button type="button" wire:click="open({{ $applicant->id }})"
                            class="btn-lift w-fit text-xs font-semibold px-5 py-2.5 bg-brand-deep text-paper">
                        {{ __('Review') }}
                    </button>
                @endif
            </article>
        @empty
            <div class="panel p-8 sm:p-10 text-center grid gap-2">
                <h2 class="font-display text-base text-ink">{{ __('Nothing waiting') }}</h2>
                <p class="text-sm text-ink-soft">{{ __('Every submission has been decided.') }}</p>
            </div>
        @endforelse
    </div>

    @if($this->decided->isNotEmpty())
        <section class="mt-10">
            <p class="eyebrow mb-3">{{ __('Recently decided') }}</p>
            <div class="panel divide-y divide-line-soft">
                @foreach($this->decided as $applicant)
                    <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-1 p-4" wire:key="decided-{{ $applicant->id }}">
                        <div class="min-w-0">
                            <p class="text-sm text-ink truncate">{{ $applicant->name }}</p>
                            <p class="text-[11px] text-ink-faint">{{ $applicant->verification_reviewed_at?->diffForHumans() }}</p>
                        </div>
                        <span class="pill" data-tone="{{ $applicant->verification_status->tone() }}">
                            {{ $applicant->verification_status->label() }}
                        </span>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</div>
