<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewPitchNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Message $message,
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
        $conversation = $this->message->conversation;
        $brief = $conversation->brief;
        $sender = $this->message->sender;
        $conversationUrl = route('client.conversation', ['id' => $conversation->id]);

        return (new MailMessage)
            ->subject("New message on \"{$brief->title}\"")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$sender->name} sent you a message about your brief.")
            ->line("**{$brief->title}**")
            ->action('Read & Reply', $conversationUrl);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $conversation = $this->message->conversation;

        return [
            'type' => 'new_pitch',
            'message_id' => $this->message->id,
            'conversation_id' => $conversation->id,
            'brief_id' => $conversation->brief_id,
            'brief_title' => $conversation->brief->title,
            'sender_id' => $this->message->sender_id,
            'sender_name' => $this->message->sender->name,
        ];
    }
}
