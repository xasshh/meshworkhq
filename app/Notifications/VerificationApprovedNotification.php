<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * The verification page tells a client they will hear back either way, so a
 * decision that reaches nobody is a promise broken. Without this a client has
 * to keep returning to the page to find out whether anything happened.
 */
class VerificationApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

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
            ->subject('Your Meshwork HQ account is verified')
            ->greeting("Hello {$notifiable->name},")
            ->line('Your verification has been approved.')
            ->line('A verified badge now appears on your briefs and on your company profile. Professionals can see it before deciding whether to spend a credit, so your briefs are more likely to be answered.')
            ->action('View your profile', route('client.profile'))
            ->line('Nothing else is needed from you.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'verification_approved',
            'verified_at' => $notifiable->verification_reviewed_at?->toIso8601String(),
        ];
    }
}
