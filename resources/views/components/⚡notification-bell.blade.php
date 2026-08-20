<?php

use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public function markAllRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
        unset($this->notifications, $this->unreadCount);
    }

    public function markRead(string $notificationId): void
    {
        auth()->user()->notifications()->where('id', $notificationId)->first()?->markAsRead();
        unset($this->notifications, $this->unreadCount);
    }

    #[Computed]
    public function unreadCount(): int
    {
        return auth()->user()->unreadNotifications()->count();
    }

    /**
     * @return \Illuminate\Notifications\DatabaseNotificationCollection
     */
    #[Computed]
    public function notifications(): \Illuminate\Notifications\DatabaseNotificationCollection
    {
        return auth()->user()->notifications()->latest()->take(10)->get();
    }

    public function notificationUrl(array $data): ?string
    {
        return match ($data['type'] ?? null) {
            'brief_alert' => auth()->user()->isProfessional() && isset($data['brief_ulid'])
                ? route('professional.brief.detail', ['ulid' => $data['brief_ulid']])
                : route('professional.alerts'),
            'brief_unlocked' => route('client.briefs'),
            'new_pitch' => isset($data['conversation_id'])
                ? route(auth()->user()->isClient() ? 'client.conversation' : 'professional.conversation', ['id' => $data['conversation_id']])
                : null,
            default => null,
        };
    }

    public function notificationLabel(array $data): string
    {
        return match ($data['type'] ?? null) {
            'brief_alert' => 'New brief match: '.($data['title'] ?? 'a brief matching your skills'),
            'brief_unlocked' => ($data['professional_name'] ?? 'A professional').' unlocked "'.($data['brief_title'] ?? 'your brief').'"',
            'new_pitch' => ($data['sender_name'] ?? 'Someone').' sent a message re: '.($data['brief_title'] ?? 'your brief'),
            default => 'Notification',
        };
    }
}; ?>

{{--
    The panel is positioned fixed and measured from the bell, because the
    sidebar it lives in is only 16rem wide: anchored inside it, a 20rem panel
    runs straight off the left edge of the screen.
--}}
<div
    x-data="{
        open: false,
        top: 0,
        left: 0,
        place() {
            const bell = this.$refs.bell.getBoundingClientRect();
            const width = Math.min(320, window.innerWidth - 16);
            this.top = bell.bottom + 8;
            this.left = Math.min(Math.max(8, bell.left), window.innerWidth - width - 8);
        },
    }"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
    @resize.window="if (open) place()"
    class="relative"
>
    {{-- Bell button --}}
    <button
        type="button"
        x-ref="bell"
        :aria-expanded="open ? 'true' : 'false'"
        @click="open = ! open; if (open) { place(); $wire.call('$refresh') }"
        class="relative w-9 h-9 flex items-center justify-center text-ink-faint hover:text-ink hover:bg-chalk-soft transition-colors duration-150"
        aria-label="Notifications"
    >
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
        </svg>
        @if ($this->unreadCount > 0)
            <span class="absolute top-0.5 right-0.5 min-w-4 h-4 px-1 bg-brand-deep text-paper font-data text-[9px] font-semibold flex items-center justify-center">
                {{ $this->unreadCount > 9 ? '9+' : $this->unreadCount }}
            </span>
        @endif
    </button>

    {{-- Dropdown --}}
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-1 scale-98"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0 translate-y-1"
        :style="`top: ${top}px; left: ${left}px;`"
        class="fixed w-80 max-w-[calc(100vw-1rem)] max-h-[min(28rem,calc(100vh-6rem))] overflow-y-auto bg-paper border border-line shadow-xl shadow-ink/20 z-[60]"
    >
        <div class="px-4 py-3 border-b border-line-soft flex items-center justify-between">
            <p class="text-sm font-semibold text-ink">Notifications</p>
            @if ($this->unreadCount > 0)
                <button wire:click="markAllRead" class="text-[11px] font-semibold text-ink-soft hover:text-ink transition-colors">
                    Mark all read
                </button>
            @endif
        </div>

        <div>
            @forelse ($this->notifications as $notification)
                @php
                    $url = $this->notificationUrl($notification->data);
                    $isUnread = $notification->read_at === null;
                @endphp
                <a
                    href="{{ $url ?? '#' }}"
                    @if ($url) wire:navigate @endif
                    wire:click="markRead('{{ $notification->id }}')"
                    class="flex items-start gap-3 px-4 py-3 hover:bg-chalk-soft transition-colors border-b border-line-soft last:border-0 {{ $isUnread ? 'bg-brand-wash' : '' }}"
                >
                    <div class="w-7 h-7 {{ $isUnread ? 'bg-ink' : 'bg-chalk-soft border border-line' }} flex items-center justify-center shrink-0 mt-0.5">
                        @if (($notification->data['type'] ?? '') === 'brief_alert')
                            <svg class="w-3.5 h-3.5 {{ $isUnread ? 'text-paper' : 'text-ink-faint' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                        @elseif (($notification->data['type'] ?? '') === 'brief_unlocked')
                            <svg class="w-3.5 h-3.5 {{ $isUnread ? 'text-paper' : 'text-ink-faint' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 119 0v3.75M3.75 21.75h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H3.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                        @else
                            <svg class="w-3.5 h-3.5 {{ $isUnread ? 'text-paper' : 'text-ink-faint' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs {{ $isUnread ? 'font-semibold text-ink' : 'text-ink-soft' }} leading-snug">
                            {{ $this->notificationLabel($notification->data) }}
                        </p>
                        <p class="font-data text-[10px] text-ink-faint mt-0.5">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                    @if ($isUnread)
                        <span class="w-1.5 h-1.5 rounded-full bg-brand shrink-0 mt-1.5"></span>
                    @endif
                </a>
            @empty
                <div class="px-4 py-8 text-center">
                    <p class="text-xs text-ink-soft">No notifications yet.</p>
                    <p class="text-[10px] text-ink-faint mt-1">Brief matches and pitch activity will appear here.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
