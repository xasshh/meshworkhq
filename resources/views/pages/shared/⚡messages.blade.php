<?php

use App\Models\Conversation;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Messages')] class extends Component
{
    use WithPagination;

    public function render(): \Illuminate\View\View
    {
        $user = auth()->user();
        $isPro = $user->isProfessional();

        $conversations = Conversation::query()
            ->with(['brief', 'latestMessage', $isPro ? 'client' : 'professional'])
            ->where($isPro ? 'professional_id' : 'client_id', $user->id)
            ->whereHas('brief')
            ->orderByRaw('COALESCE(last_message_at, created_at) DESC')
            ->paginate(20);

        return view('pages::shared.⚡messages', [
            'conversations' => $conversations,
            'isPro' => $isPro,
        ]);
    }
}; ?>

<div class="max-w-3xl mx-auto px-4 sm:px-6 py-8 sm:py-10">

    <x-page-header
        :eyebrow="__('Work')"
        :title="__('Messages')"
        :description="$isPro
            ? __('Every brief you have unlocked has a thread here, anchored to that brief.')
            : __('One thread for each professional who unlocked a brief of yours.')"
    />

    @if($conversations->total() > 0)
        <div class="mt-6 panel">
            @foreach($conversations as $conversation)
                @php
                    $counterpart = $isPro ? $conversation->client : $conversation->professional;
                    $unread = $conversation->unreadCountFor(auth()->id());
                    $latest = $conversation->latestMessage;
                    $route = $isPro ? 'professional.conversation' : 'client.conversation';
                @endphp

                <a
                    href="{{ route($route, ['id' => $conversation->id]) }}"
                    wire:navigate
                    wire:key="conversation-{{ $conversation->id }}"
                    class="flex items-start gap-4 p-5 border-b border-line-soft last:border-b-0 hover:bg-chalk-soft transition-colors"
                >
                    <div class="w-9 h-9 bg-chalk-soft border border-line grid place-items-center shrink-0">
                        <span class="font-display text-[11px] text-ink-faint">{{ $counterpart?->initials() ?? '?' }}</span>
                    </div>

                    <div class="min-w-0 flex-1 grid gap-1">
                        <div class="flex items-baseline justify-between gap-3">
                            <span class="text-sm font-semibold text-ink truncate">{{ $counterpart?->name ?? __('Unknown') }}</span>
                            <span class="font-data text-[10px] text-ink-faint shrink-0">
                                {{ ($conversation->last_message_at ?? $conversation->created_at)->diffForHumans(short: true) }}
                            </span>
                        </div>

                        <p class="text-xs text-ink-faint truncate">{{ $conversation->brief->title }}</p>

                        <p class="text-sm text-ink-soft truncate">
                            @if($latest)
                                @if($latest->sender_id === auth()->id())<span class="text-ink-faint">{{ __('You:') }}</span> @endif
                                {{ $latest->body }}
                            @else
                                <span class="text-ink-faint italic">{{ __('No messages yet') }}</span>
                            @endif
                        </p>
                    </div>

                    @if($unread > 0)
                        <span class="font-data text-[10px] font-semibold leading-none px-1.5 py-1 bg-brand-deep text-paper shrink-0">{{ $unread }}</span>
                    @endif
                </a>
            @endforeach
        </div>

        @if($conversations->hasPages())
            <div class="mt-6">{{ $conversations->links() }}</div>
        @endif
    @else
        <div class="mt-6 panel p-10 sm:p-14 text-center grid gap-3 justify-items-center">
            <div class="w-10 h-10 border border-line grid place-items-center">
                <flux:icon name="chat-bubble-left-right" variant="micro" class="text-ink-faint" />
            </div>
            <h2 class="font-display text-lg text-ink">{{ __('No conversations yet') }}</h2>
            <p class="text-sm text-ink-soft max-w-[44ch]">
                @if($isPro)
                    {{ __('Unlocking a brief opens a thread with the client. Nothing here yet.') }}
                @else
                    {{ __('When a professional unlocks one of your briefs, their thread appears here.') }}
                @endif
            </p>
            <a href="{{ $isPro ? route('professional.alerts') : route('client.brief.create') }}" wire:navigate
               class="btn-lift mt-2 text-xs font-semibold px-4 py-2.5 bg-ink text-paper">
                {{ $isPro ? __('Browse your alerts') : __('Post a brief') }}
            </a>
        </div>
    @endif
</div>
