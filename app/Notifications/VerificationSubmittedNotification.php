<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells staff a verification is waiting.
 *
 * A submission that nobody is told about sits pending indefinitely, and the
 * client has been promised a decision. This is what turns the queue from
 * something you have to remember to check into something that reaches you.
 */
class VerificationSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly User $applicant,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $type = strtoupper((string) $this->applicant->verification_type);
        $company = $this->applicant->company_name ?: 'Not given';

        return (new MailMessage)
            ->subject("Verification to review: {$this->applicant->name}")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$this->applicant->name} has submitted {$type} verification and is waiting on a decision.")
            ->line("**Company:** {$company}")
            ->line('**Reference:** '.($this->applicant->verification_reference ?: 'Not given'))
            ->action('Review it', route('admin.verifications'))
            ->line('The client is told they will hear back either way, so please do not leave it sitting.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'verification_submitted',
            'applicant_id' => $this->applicant->id,
            'applicant_name' => $this->applicant->name,
            'verification_type' => $this->applicant->verification_type,
        ];
    }
}
