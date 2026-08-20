<?php

namespace App\Notifications;

use App\Models\Brief;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Confirmation to the client that their brief is live and who it reached.
 */
class BriefPublishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Brief $brief,
        private readonly int $matchedCount,
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
        $url = route('client.brief.detail', ['ulid' => $this->brief->ulid]);

        $mail = (new MailMessage)
            ->subject("Your brief is live: {$this->brief->title}")
            ->greeting("Hello {$notifiable->name},")
            ->line('Your brief has been published and the matching engine has started work.')
            ->line("**{$this->brief->title}**");

        $mail = $this->matchedCount > 0
            ? $mail->line("It matched {$this->matchedCount} professional(s). The ten closest matches have been alerted first, and more are notified over the next day if you need them.")
            : $mail->line('No professional matches these skills yet. The brief stays live, and anyone who joins with matching skills will be alerted.');

        return $mail
            ->action('View your brief', $url)
            ->line('Professionals spend a credit to reach you, so the ones who get in touch have decided your brief is worth their money.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'brief_published',
            'brief_id' => $this->brief->id,
            'brief_ulid' => $this->brief->ulid,
            'brief_title' => $this->brief->title,
            'matched_count' => $this->matchedCount,
        ];
    }
}
