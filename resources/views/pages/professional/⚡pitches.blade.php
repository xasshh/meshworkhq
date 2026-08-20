<?php

use App\Enums\BriefStatus;
use App\Models\Unlock;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('My pitches')] class extends Component
{
    use WithPagination;

    /**
     * Where a pitch stands, derived from the conversation rather than stored,
     * so it stays accurate without a separate state column to keep in sync.
     *
     * @return array{label: string, tone: string}
     */
    public function pitchState(Unlock $unlock): array
    {
        $brief = $unlock->brief;

        if ($brief->status === BriefStatus::Hired) {
            return $brief->hired_professional_id === $unlock->professional_id
                ? ['label' => __('Hired'), 'tone' => 'live']
                : ['label' => __('Client hired someone else'), 'tone' => 'muted'];
        }

        if (in_array($brief->status, [BriefStatus::Closed, BriefStatus::Expired], true)) {
            return ['label' => $brief->status->label(), 'tone' => 'muted'];
        }

        $conversation = $unlock->conversation;

        if ($conversation === null || $conversation->messages->isEmpty()) {
            return ['label' => __('Not pitched yet'), 'tone' => 'warn'];
        }

        $clientHasReplied = $conversation->messages
            ->contains(fn ($message) => $message->sender_id === $conversation->client_id);

        return $clientHasReplied
            ? ['label' => __('Client replied'), 'tone' => 'live']
            : ['label' => __('Waiting on client'), 'tone' => 'default'];
    }

    public function render(): \Illuminate\View\View
    {
        return view('pages::professional.⚡pitches', [
            'unlocks' => Unlock::query()
                ->with(['brief', 'conversation.messages'])
                ->where('professional_id', auth()->id())
                ->whereHas('brief')
                ->latest('unlocked_at')
                ->paginate(15),
        ]);
    }
}; ?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8 sm:py-10">

    <x-page-header
        :eyebrow="__('Work')"
        :title="__('My pitches')"
        :description="__('Every brief you have spent a credit on, and where each one stands.')"
    />

    @if($unlocks->total() > 0)
        {{-- relative matters: the sr-only label in the actions column is absolutely
             positioned, so without a containing block here it escapes this scroller
             and drags the whole page sideways on a phone. --}}
        <div class="mt-6 panel overflow-x-auto relative">
            <table class="w-full min-w-[44rem] text-sm">
                <thead>
                    <tr class="bg-chalk-soft">
                        <th class="text-left font-data text-[10px] uppercase tracking-[0.13em] text-ink-faint font-medium px-5 py-3">{{ __('Brief') }}</th>
                        <th class="text-left font-data text-[10px] uppercase tracking-[0.13em] text-ink-faint font-medium px-5 py-3">{{ __('Unlocked') }}</th>
                        <th class="text-left font-data text-[10px] uppercase tracking-[0.13em] text-ink-faint font-medium px-5 py-3">{{ __('Budget') }}</th>
                        <th class="text-left font-data text-[10px] uppercase tracking-[0.13em] text-ink-faint font-medium px-5 py-3">{{ __('State') }}</th>
                        <th class="px-5 py-3"><span class="sr-only">{{ __('Actions') }}</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($unlocks as $unlock)
                        @php $state = $this->pitchState($unlock); @endphp
                        <tr class="border-t border-line-soft hover:bg-chalk-soft transition-colors" wire:key="unlock-{{ $unlock->id }}">
                            <td class="px-5 py-4">
                                <a href="{{ route('professional.brief.detail', ['ulid' => $unlock->brief->ulid]) }}" wire:navigate
                                   class="font-semibold text-ink hover:text-brand-deep transition-colors">
                                    {{ $unlock->brief->title }}
                                </a>
                            </td>
                            <td class="px-5 py-4 font-data text-xs text-ink-soft whitespace-nowrap">
                                {{ $unlock->unlocked_at?->format('j M Y') ?? '' }}
                            </td>
                            <td class="px-5 py-4 font-data text-xs text-ink-soft whitespace-nowrap">
                                @if($unlock->brief->budget_max)
                                    &#8358;{{ number_format($unlock->brief->budget_max) }}
                                @else
                                    {{ __('Not stated') }}
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <span class="pill" @if($state['tone'] !== 'default') data-tone="{{ $state['tone'] }}" @endif>{{ $state['label'] }}</span>
                            </td>
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                @if($unlock->conversation)
                                    <a href="{{ route('professional.conversation', ['id' => $unlock->conversation->id]) }}" wire:navigate
                                       class="text-xs font-semibold text-ink-soft hover:text-ink transition-colors">
                                        {{ $unlock->conversation->messages->isEmpty() ? __('Write pitch') : __('Open thread') }}
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($unlocks->hasPages())
            <div class="mt-6">{{ $unlocks->links() }}</div>
        @endif
    @else
        <div class="mt-6 panel p-10 sm:p-14 text-center grid gap-3 justify-items-center">
            <div class="w-10 h-10 border border-line grid place-items-center">
                <flux:icon name="paper-airplane" variant="micro" class="text-ink-faint" />
            </div>
            <h2 class="font-display text-lg text-ink">{{ __('No pitches yet') }}</h2>
            <p class="text-sm text-ink-soft max-w-[44ch]">
                {{ __('When you unlock a brief it appears here, so you can track whether the client replied.') }}
            </p>
            <a href="{{ route('professional.alerts') }}" wire:navigate class="btn-lift mt-2 text-xs font-semibold px-4 py-2.5 bg-ink text-paper">
                {{ __('Browse your alerts') }}
            </a>
        </div>
    @endif
</div>
