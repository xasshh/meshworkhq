<?php

namespace App\Notifications;

use App\Models\Brief;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * The best message the platform ever sends: you got the job.
 */
class HiredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Brief $brief,
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
        $conversation = $this->brief->conversations()
            ->where('professional_id', $notifiable->id)
            ->first();

        $url = $conversation
            ? route('professional.conversation', ['id' => $conversation->id])
            : route('professional.brief.detail', ['ulid' => $this->brief->ulid]);

        return (new MailMessage)
            ->subject("You have been hired: {$this->brief->title}")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$this->brief->client->name} has hired you for this brief.")
            ->line("**{$this->brief->title}**")
            ->action('Open the conversation', $url)
            ->line('Agree the details directly with the client. Meshwork HQ takes no commission on the work.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'hired',
            'brief_id' => $this->brief->id,
            'brief_ulid' => $this->brief->ulid,
            'brief_title' => $this->brief->title,
            'client_name' => $this->brief->client->name,
        ];
    }
}
