<?php

use App\Models\Conversation;
use App\Models\Message;
use App\Notifications\NewPitchNotification;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Conversation')] class extends Component {

    public Conversation $conversation;
    public string $body = '';

    public function mount(int $id): void
    {
        $user = auth()->user();

        $this->conversation = Conversation::with(['brief', 'client', 'professional', 'messages.sender'])
            ->where('id', $id)
            ->where(function ($q) use ($user) {
                $q->where('client_id', $user->id)
                  ->orWhere('professional_id', $user->id);
            })
            ->firstOrFail();

        // Mark messages as read for the current user.
        $this->conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function send(): void
    {
        $this->validate(['body' => ['required', 'string', 'max:5000']]);

        $user = auth()->user();

        $message = Message::create([
            'conversation_id' => $this->conversation->id,
            'sender_id'       => $user->id,
            'body'            => $this->body,
        ]);

        $this->conversation->update(['last_message_at' => now()]);

        // Notify the other party.
        $recipient = $this->conversation->client_id === $user->id
            ? $this->conversation->professional
            : $this->conversation->client;

        $recipient->notify(new NewPitchNotification($message));

        $this->body = '';
        $this->conversation->load('messages.sender');
    }

    public function render(): \Illuminate\View\View
    {
        return view('pages::shared.⚡conversation');
    }

}; ?>

<div class="min-h-screen bg-[--color-emerald-soft] flex flex-col">
    <div class="max-w-3xl mx-auto w-full px-4 py-6 flex flex-col flex-1">

        {{-- Thread header --}}
        <div class="bg-white border border-slate-200 rounded-2xl px-6 py-4 mb-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wide mb-0.5">Brief</p>
                    <p class="font-semibold text-[--color-slate-main] text-sm">{{ $conversation->brief->title }}</p>
                </div>
                <div class="text-right">
                    @php
                        $user = auth()->user();
                        $other = $conversation->client_id === $user->id
                            ? $conversation->professional
                            : $conversation->client;
                    @endphp
                    <p class="text-xs text-slate-400 mb-0.5">{{ $conversation->client_id === $user->id ? 'Professional' : 'Client' }}</p>
                    <p class="font-medium text-[--color-slate-main] text-sm">{{ $other->name }}</p>
                    @if($other->professional_title)
                        <p class="text-xs text-slate-400">{{ $other->professional_title }}</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Messages --}}
        <div class="flex-1 space-y-3 mb-4" id="message-list">
            @forelse($conversation->messages as $message)
                @php $isMine = $message->sender_id === auth()->id(); @endphp
                <div class="flex {{ $isMine ? 'justify-end' : 'justify-start' }}"
                     wire:key="msg-{{ $message->id }}">
                    <div class="max-w-[75%]">
                        @if(!$isMine)
                            <p class="text-xs text-slate-400 mb-1 ml-1">{{ $message->sender->name }}</p>
                        @endif
                        <div class="px-4 py-3 rounded-2xl text-sm leading-relaxed
                            {{ $isMine
                                ? 'bg-[--color-slate-main] text-white rounded-br-sm'
                                : 'bg-white border border-slate-200 text-slate-700 rounded-bl-sm' }}">
                            {{ $message->body }}
                        </div>
                        <p class="text-xs text-slate-400 mt-1 {{ $isMine ? 'text-right mr-1' : 'ml-1' }}">
                            {{ $message->created_at->format('H:i') }}
                            @if($isMine && $message->read_at)
                                · Read
                            @endif
                        </p>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 text-slate-400 text-sm">
                    No messages yet. Send the first message to start the conversation.
                </div>
            @endforelse
        </div>

        {{-- Compose --}}
        <div class="bg-white border border-slate-200 rounded-2xl p-4">
            <form wire:submit="send" class="flex gap-3">
                <textarea wire:model="body"
                          rows="2"
                          placeholder="Write your message…"
                          class="flex-1 resize-none border border-slate-200 rounded-xl px-3 py-2.5 text-sm text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[--color-emerald-main]/30 focus:border-[--color-emerald-main] transition-colors"></textarea>
                <button type="submit"
                        class="self-end px-5 py-2.5 bg-[--color-emerald-main] hover:bg-[--color-emerald-deep] text-white rounded-xl text-sm font-medium transition-colors flex-shrink-0">
                    Send
                </button>
            </form>
        </div>

    </div>

    {{-- Scroll to bottom on load --}}
    <script>
        document.addEventListener('livewire:navigated', () => {
            const list = document.getElementById('message-list');
            if (list) list.scrollIntoView({ block: 'end' });
        });
    </script>
</div>
