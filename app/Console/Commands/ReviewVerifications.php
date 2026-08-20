<?php

namespace App\Console\Commands;

use App\Enums\VerificationStatus;
use App\Models\User;
use App\Services\VerificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

/**
 * The review surface for pending verifications, until the admin panel exists.
 *
 * Deliberately a command rather than a web route: it keeps identity documents
 * off the public internet entirely, and reviewing is a staff task, not a
 * product feature.
 */
class ReviewVerifications extends Command
{
    protected $signature = 'verification:review';

    protected $description = 'Review pending client verification submissions';

    public function handle(VerificationService $verification): int
    {
        $pending = User::where('verification_status', VerificationStatus::Pending)
            ->orderBy('verification_submitted_at')
            ->get();

        if ($pending->isEmpty()) {
            $this->components->info('Nothing waiting for review.');

            return self::SUCCESS;
        }

        $this->components->info($pending->count().' waiting for review.');

        foreach ($pending as $user) {
            $this->newLine();
            $this->components->twoColumnDetail('<fg=white;options=bold>Account</>', $user->name.' <'.$user->email.'>');
            $this->components->twoColumnDetail('Type', strtoupper((string) $user->verification_type));
            $this->components->twoColumnDetail('Reference', (string) $user->verification_reference);
            $this->components->twoColumnDetail('Company', $user->company_name ?: 'not given');
            $this->components->twoColumnDetail('Submitted', (string) $user->verification_submitted_at);

            if ($user->verification_document_path) {
                $this->components->twoColumnDetail(
                    'Document',
                    Storage::disk('local')->path($user->verification_document_path),
                );
            }

            $decision = select(
                label: 'Decision',
                options: ['approve' => 'Approve', 'reject' => 'Reject', 'skip' => 'Skip for now'],
                default: 'skip',
            );

            if ($decision === 'approve') {
                $legalName = text(
                    label: 'Legal name on the document',
                    default: $user->company_name ?: $user->name,
                );

                $verification->approve($user, $legalName);
                $this->components->info("Approved {$user->name}.");
            } elseif ($decision === 'reject') {
                $reason = text(
                    label: 'Reason, shown to the user',
                    required: true,
                    placeholder: 'The certificate did not match the registration number.',
                );

                if (confirm("Reject {$user->name}?")) {
                    $verification->reject($user, $reason);
                    $this->components->warn("Rejected {$user->name}.");
                }
            }
        }

        return self::SUCCESS;
    }
}
