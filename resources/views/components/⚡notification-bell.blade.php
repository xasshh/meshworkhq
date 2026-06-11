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

<div x-data="{ open: false }" class="relative" @click.outside="open = false">
    {{-- Bell button --}}
    <button
        type="button"
        @click="open = ! open; if (open) { $wire.call('$refresh') }"
        class="relative w-9 h-9 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-colors duration-150"
        aria-label="Notifications"
    >
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
        </svg>
        @if ($this->unreadCount > 0)
            <span class="absolute -top-0.5 -right-0.5 min-w-4.5 h-4.5 px-1 rounded-full bg-emerald-main text-white text-[9px] font-bold flex items-center justify-center ring-2 ring-white">
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
        class="absolute right-0 mt-2 w-80 bg-white rounded-2xl border border-slate-200 shadow-xl shadow-slate-900/8 z-50 overflow-hidden"
    >
        <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
            <p class="text-sm font-bold text-slate-main">Notifications</p>
            @if ($this->unreadCount > 0)
                <button wire:click="markAllRead" class="text-[11px] font-semibold text-emerald-deep hover:text-emerald-main transition-colors">
                    Mark all read
                </button>
            @endif
        </div>

        <div class="max-h-80 overflow-y-auto">
            @forelse ($this->notifications as $notification)
                @php
                    $url = $this->notificationUrl($notification->data);
                    $isUnread = $notification->read_at === null;
                @endphp
                <a
                    href="{{ $url ?? '#' }}"
                    @if ($url) wire:navigate @endif
                    wire:click="markRead('{{ $notification->id }}')"
                    class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50 transition-colors border-b border-slate-50 last:border-0 {{ $isUnread ? 'bg-emerald-soft/40' : '' }}"
                >
                    <div class="w-7 h-7 rounded-lg {{ $isUnread ? 'bg-emerald-main' : 'bg-slate-100' }} flex items-center justify-center shrink-0 mt-0.5">
                        @if (($notification->data['type'] ?? '') === 'brief_alert')
                            <svg class="w-3.5 h-3.5 {{ $isUnread ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                        @elseif (($notification->data['type'] ?? '') === 'brief_unlocked')
                            <svg class="w-3.5 h-3.5 {{ $isUnread ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 119 0v3.75M3.75 21.75h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H3.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                        @else
                            <svg class="w-3.5 h-3.5 {{ $isUnread ? 'text-white' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs {{ $isUnread ? 'font-bold text-slate-main' : 'font-medium text-slate-500' }} leading-snug">
                            {{ $this->notificationLabel($notification->data) }}
                        </p>
                        <p class="text-[10px] text-slate-400 mt-0.5">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                    @if ($isUnread)
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-main shrink-0 mt-1.5"></span>
                    @endif
                </a>
            @empty
                <div class="px-4 py-8 text-center">
                    <p class="text-xs text-slate-400">No notifications yet.</p>
                    <p class="text-[10px] text-slate-300 mt-1">Brief matches and pitch activity will appear here.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
