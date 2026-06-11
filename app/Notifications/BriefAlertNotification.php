<?php

namespace App\Notifications;

use App\Models\Alert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BriefAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Alert $alert,
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
        $brief = $this->alert->brief;
        $unlockUrl = route('professional.brief.unlock', ['ulid' => $brief->ulid]);

        return (new MailMessage)
            ->subject("New brief match: {$brief->title}")
            ->greeting("Hello {$notifiable->name},")
            ->line('A new brief has been posted that matches your skills.')
            ->line("**{$brief->title}**")
            ->when($brief->budget_max, fn ($mail) => $mail->line(
                'Budget: ₦'.number_format($brief->budget_min ?? 0).' – ₦'.number_format($brief->budget_max)
            ))
            ->action('View & Unlock Brief', $unlockUrl)
            ->line('Unlocking costs 1 credit and opens the client contact details.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $brief = $this->alert->brief;

        return [
            'type' => 'brief_alert',
            'alert_id' => $this->alert->id,
            'brief_id' => $brief->id,
            'brief_ulid' => $brief->ulid,
            'title' => $brief->title,
            'wave' => $this->alert->wave,
        ];
    }
}
