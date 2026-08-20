<?php

use App\Models\Conversation;
use App\Models\Message;
use App\Notifications\NewPitchNotification;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Conversation')] class extends Component
{
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

        $this->markIncomingAsRead();
    }

    public function send(): void
    {
        $this->validate(['body' => ['required', 'string', 'max:5000']]);

        $user = auth()->user();

        $message = Message::create([
            'conversation_id' => $this->conversation->id,
            'sender_id' => $user->id,
            'body' => $this->body,
        ]);

        $this->conversation->update(['last_message_at' => now()]);

        $recipient = $this->conversation->client_id === $user->id
            ? $this->conversation->professional
            : $this->conversation->client;

        $recipient->notify(new NewPitchNotification($message));

        $this->body = '';
        $this->conversation->load('messages.sender');
    }

    private function markIncomingAsRead(): void
    {
        $this->conversation->messages()
            ->where('sender_id', '!=', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function counterpart(): ?\App\Models\User
    {
        return $this->conversation->client_id === auth()->id()
            ? $this->conversation->professional
            : $this->conversation->client;
    }
}; ?>

@php
    $user = auth()->user();
    $isPro = $user->isProfessional();
    $counterpart = $this->counterpart();
    $brief = $this->conversation->brief;
@endphp

<div class="max-w-3xl mx-auto px-4 sm:px-6 py-8 sm:py-10">

    <a href="{{ $isPro ? route('professional.messages') : route('client.messages') }}" wire:navigate
       class="inline-flex items-center gap-1.5 text-xs font-semibold text-ink-soft hover:text-ink transition-colors mb-5">
        <flux:icon name="arrow-left" variant="micro" />
        {{ __('All messages') }}
    </a>

    {{-- Who and what this thread is about --}}
    <header class="panel p-5 grid gap-4">
        <div class="flex items-start gap-4">
            <div class="w-11 h-11 bg-chalk-soft border border-line grid place-items-center shrink-0">
                <span class="font-display text-sm text-ink-faint">{{ $counterpart?->initials() ?? '?' }}</span>
            </div>

            <div class="min-w-0 flex-1">
                <h1 class="font-display text-lg text-ink leading-tight truncate">{{ $counterpart?->name ?? __('Unknown') }}</h1>
                <p class="text-xs text-ink-faint mt-0.5">
                    @if($isPro)
                        {{ $counterpart?->company_name ?: __('Client') }}
                    @else
                        {{ $counterpart?->professional_title ?: __('Professional') }}
                    @endif
                </p>
            </div>

            {{-- Not shrink-0: these badges sit beside a name on one flex row, and
                 refusing to shrink pushed them off the side of a phone screen. --}}
            <div class="flex flex-wrap items-center justify-end gap-2 min-w-0">
                <x-verified-badge :user="$counterpart" />
                <x-track-record :user="$counterpart" />
                <span class="pill" data-tone="{{ $brief->status->isActive() ? 'live' : 'muted' }}">{{ $brief->status->label() }}</span>
            </div>
        </div>

        {{-- The unlocked contact record. This is what the credit bought. --}}
        @if($isPro)
            <div class="seal" data-open="true">
                <div class="seal-row">
                    <span class="seal-key">{{ __('Client') }}</span>
                    <span class="seal-value">{{ $counterpart?->name }}</span>
                </div>
                <div class="seal-row">
                    <span class="seal-key">{{ __('Email') }}</span>
                    <span class="seal-value">{{ $counterpart?->email }}</span>
                </div>
                @if($counterpart?->phone)
                    <div class="seal-row">
                        <span class="seal-key">{{ __('Phone') }}</span>
                        <span class="seal-value">{{ $counterpart->phone }}</span>
                    </div>
                @endif
            </div>
        @endif

        <div class="flex items-center justify-between gap-3 pt-4 border-t border-line-soft">
            <div class="min-w-0">
                <p class="eyebrow">{{ __('About this brief') }}</p>
                <p class="text-sm text-ink truncate mt-1">{{ $brief->title }}</p>
            </div>
            <a href="{{ $isPro ? route('professional.brief.detail', ['ulid' => $brief->ulid]) : route('client.brief.detail', ['ulid' => $brief->ulid]) }}"
               wire:navigate class="text-xs font-semibold text-ink-soft hover:text-ink transition-colors shrink-0">
                {{ __('Open brief') }}
            </a>
        </div>
    </header>

    {{-- Thread --}}
    <div class="mt-6 grid gap-3">
        @forelse($this->conversation->messages as $message)
            @php $mine = $message->sender_id === $user->id; @endphp

            <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}" wire:key="message-{{ $message->id }}">
                <div class="max-w-[80%] sm:max-w-[70%] grid gap-1.5">
                    <div class="{{ $mine ? 'bg-ink text-paper' : 'bg-paper text-ink border border-line' }} px-4 py-3">
                        <p class="text-sm leading-relaxed whitespace-pre-line wrap-anywhere">{{ $message->body }}</p>
                    </div>
                    <p class="font-data text-[10px] text-ink-faint {{ $mine ? 'text-right' : '' }}">
                        {{ $mine ? __('You') : $message->sender->name }}
                        &middot; {{ $message->created_at->format('j M, H:i') }}
                        @if($mine && $message->read_at)
                            &middot; {{ __('read') }}
                        @endif
                    </p>
                </div>
            </div>
        @empty
            <div class="panel p-8 sm:p-10 text-center grid gap-2">
                <h2 class="font-display text-base text-ink">
                    {{ $isPro ? __('Write the first message') : __('Nothing said yet') }}
                </h2>
                <p class="text-sm text-ink-soft max-w-[46ch] mx-auto">
                    @if($isPro)
                        {{ __('You spent a credit to get here. Say what you would do, roughly what it costs, and when you could start.') }}
                    @else
                        {{ __('This professional unlocked your brief. They will usually open the conversation.') }}
                    @endif
                </p>
            </div>
        @endforelse
    </div>

    {{-- Composer --}}
    <form wire:submit="send" class="mt-6 panel p-4 grid gap-3">
        <flux:textarea
            wire:model="body"
            rows="3"
            :placeholder="$isPro ? __('What you would do, what it costs, when you can start.') : __('Write a reply')"
            :label="__('Message')"
            class="!mb-0"
        />

        <div class="flex items-center justify-between gap-3">
            <span class="text-[11px] text-ink-faint">{{ __('Messages are logged. Keep payment discussions on terms you both agree in writing.') }}</span>
            <button type="submit" wire:loading.attr="disabled" class="btn-lift text-xs font-semibold px-5 py-2.5 bg-ink text-paper shrink-0">
                <span wire:loading.remove wire:target="send">{{ __('Send') }}</span>
                <span wire:loading wire:target="send">{{ __('Sending') }}</span>
            </button>
        </div>
    </form>
</div>
