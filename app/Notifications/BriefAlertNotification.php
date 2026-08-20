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

        // Link to the brief page, not the POST unlock endpoint: spending a
        // credit is always a deliberate action taken in the app.
        $briefUrl = route('professional.brief.detail', ['ulid' => $brief->ulid]);

        return (new MailMessage)
            ->subject("New brief match: {$brief->title}")
            ->greeting("Hello {$notifiable->name},")
            ->line('A new brief has been posted that matches your skills.')
            ->line("**{$brief->title}**")
            ->when($brief->budget_max, fn ($mail) => $mail->line(
                $brief->budget_min
                    ? 'Budget: ₦'.number_format($brief->budget_min).' to ₦'.number_format($brief->budget_max)
                    : 'Budget: ₦'.number_format($brief->budget_max)
            ))
            ->line("You are in wave {$this->alert->wave} of 3, so only a small group can see this right now.")
            ->action('View this brief', $briefUrl)
            ->line('Unlocking costs 1 credit and reveals the client contact details, along with a direct thread to reach them.');
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
