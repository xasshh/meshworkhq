<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * A rejection has to carry its reason. Telling someone they failed a check
 * without saying why leaves them to guess, and the reason is already recorded
 * on the account by VerificationService::reject().
 */
class VerificationRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $reason,
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
        return (new MailMessage)
            ->subject('We could not verify your Meshwork HQ account')
            ->greeting("Hello {$notifiable->name},")
            ->line('We were unable to verify your account with the details you submitted.')
            ->line("**Reason:** {$this->reason}")
            ->line('You can submit again with corrected details. Your uploaded document was deleted once the review was recorded, so you will need to attach it again.')
            ->action('Submit again', route('client.verification'))
            ->line('If you think this is a mistake, reply to this email and we will take another look.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'verification_rejected',
            'reason' => $this->reason,
        ];
    }
}
