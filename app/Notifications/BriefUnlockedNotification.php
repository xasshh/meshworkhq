<?php

namespace App\Notifications;

use App\Models\Unlock;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BriefUnlockedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Unlock $unlock,
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
        $brief = $this->unlock->brief;
        $professional = $this->unlock->professional;
        $conversationUrl = route('client.conversation', ['id' => $this->unlock->conversation?->id]);

        return (new MailMessage)
            ->subject("A professional unlocked your brief: {$brief->title}")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$professional->name} has unlocked and is pitching on your brief.")
            ->line("**{$brief->title}**")
            ->action('View Pitch & Start Conversation', $conversationUrl)
            ->line('Reply directly through the platform to discuss further.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $brief = $this->unlock->brief;
        $professional = $this->unlock->professional;

        return [
            'type' => 'brief_unlocked',
            'unlock_id' => $this->unlock->id,
            'brief_id' => $brief->id,
            'brief_title' => $brief->title,
            'professional_id' => $professional->id,
            'professional_name' => $professional->name,
        ];
    }
}
