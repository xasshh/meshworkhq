<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::marketing')] #[Title('How it works')] class extends Component {}; ?>

<div>
    <x-marketing-nav />

    {{-- Statement --}}
    <section class="border-b border-line">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-14 sm:py-20 grid gap-6 max-w-3xl">
            <p class="flex items-center gap-2.5 font-data text-[10px] sm:text-[11px] uppercase tracking-[0.18em] text-brand-deep">
                <span class="w-2 h-2 bg-brand shrink-0" aria-hidden="true"></span>
                {{ __('How it works') }}
            </p>

            <h1 class="font-display-caps text-ink text-[2.5rem] sm:text-5xl lg:text-6xl">
                {{ __('A brief goes out in waves, not to a crowd.') }}
            </h1>

            <p class="text-base sm:text-lg text-ink-soft leading-relaxed max-w-[56ch]">
                {{ __('Most marketplaces show every job to everyone and let people race each other to the bottom. Meshwork HQ does the opposite. A brief is matched by skill and released to a small group first, so the people who see it early have a genuine advantage.') }}
            </p>
        </div>
    </section>

    {{-- The four steps --}}
    <section class="border-b border-line">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-14 sm:py-20">
            <ol class="grid gap-px bg-line border border-line">
                @foreach([
                    ['n' => '01', 'h' => __('The client writes a brief'), 'p' => __('What the work is, what it pays in naira, and the skills it needs. Posting is free and takes about two minutes. The brief stays a draft until they publish it, so nothing goes out half written.')],
                    ['n' => '02', 'h' => __('The engine picks who hears first'), 'p' => __('Every professional is filtered by skill overlap against the brief tags. Nobody outside that set is contacted, because a brief that reaches the wrong people is worse than one that reaches nobody.')],
                    ['n' => '03', 'h' => __('A credit opens the door'), 'p' => __('A professional who wants the job spends one credit to reveal the client and open a direct thread. That cost is the point: it means everyone who gets in touch has decided the brief is worth their money.')],
                    ['n' => '04', 'h' => __('They talk, and we get out of the way'), 'p' => __('The conversation is direct. Terms, scope and payment are between the two of you. Meshwork HQ takes no commission on the work itself, ever.')],
                ] as $step)
                    <li class="bg-paper p-6 sm:p-10 grid sm:grid-cols-[auto_1fr] gap-4 sm:gap-10 items-start">
                        <span class="font-display-caps text-4xl sm:text-5xl text-brand">{{ $step['n'] }}</span>
                        <div class="grid gap-3">
                            <h2 class="font-display text-xl sm:text-2xl text-ink leading-tight">{{ $step['h'] }}</h2>
                            <p class="text-sm sm:text-base text-ink-soft leading-relaxed max-w-[60ch]">{{ $step['p'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- The waves, explained properly --}}
    <section class="bg-navy">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-14 sm:py-20 grid gap-10">
            <div class="grid gap-4 max-w-2xl">
                <p class="font-data text-[10px] uppercase tracking-[0.18em] text-brand-lit">{{ __('The wave system') }}</p>
                <h2 class="font-display-caps text-3xl sm:text-4xl lg:text-5xl text-paper">
                    {{ __('Three waves, one day, fifty people at most') }}
                </h2>
                <p class="text-base text-paper/60 leading-relaxed">
                    {{ __('A brief is never broadcast. It opens to a wider group only if the first group does not answer it, and it stops at fifty people no matter what.') }}
                </p>
            </div>

            <div class="grid sm:grid-cols-3 gap-px bg-navy-raised border border-navy-raised">
                @foreach([
                    ['w' => __('Wave 1'), 't' => __('Immediately'), 'n' => __('10 professionals'), 'd' => __('The closest skill matches. If you are here, you are looking at a brief almost nobody else can see yet.')],
                    ['w' => __('Wave 2'), 't' => __('After 6 hours'), 'n' => __('15 more'), 'd' => __('Opens only if the brief still needs answers. Twenty five people can now see it.')],
                    ['w' => __('Wave 3'), 't' => __('After 24 hours'), 'n' => __('The rest'), 'd' => __('The full matched pool, capped at fifty. Crowded, and the interface tells you so.')],
                ] as $i => $wave)
                    <div class="bg-navy p-6 sm:p-7 grid gap-3 content-start">
                        <div class="wave-track">
                            @for($seg = 0; $seg < 3; $seg++)
                                <span class="wave-seg {{ $seg > $i ? '!bg-paper/15' : '' }}"
                                      @if($seg === $i) data-state="live" style="--wave-progress: 0.55" @endif
                                      @if($seg < $i) data-state="past" @endif></span>
                            @endfor
                        </div>
                        <p class="font-data text-[10px] uppercase tracking-[0.14em] text-brand-lit">{{ $wave['w'] }} &middot; {{ $wave['t'] }}</p>
                        <p class="font-display text-xl text-paper">{{ $wave['n'] }}</p>
                        <p class="text-sm text-paper/60 leading-relaxed">{{ $wave['d'] }}</p>
                    </div>
                @endforeach
            </div>

            <p class="text-sm text-paper/50 max-w-[60ch]">
                {{ __('Every alert card shows which wave the brief is in and how long until it opens wider, so you always know how much of a head start you still have.') }}
            </p>
        </div>
    </section>

    {{-- What it costs --}}
    <section class="border-b border-line">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-14 sm:py-20 grid lg:grid-cols-2 gap-10 lg:gap-16 items-center">
            <div class="grid gap-5">
                <p class="font-data text-[10px] uppercase tracking-[0.18em] text-brand-deep">{{ __('What it costs') }}</p>
                <h2 class="font-display-caps text-3xl sm:text-4xl text-ink max-w-[18ch]">
                    {{ __('Clients post free. Professionals pay per lead.') }}
                </h2>
                <p class="text-sm sm:text-base text-ink-soft leading-relaxed max-w-[52ch]">
                    {{ __('There is no commission on the work and no cut of the fee. The only thing that ever costs money is a credit, and a credit only buys one thing: the contact details behind a brief, and a thread to reach them.') }}
                </p>
            </div>

            <dl class="grid sm:grid-cols-2 gap-px bg-line border border-line">
                @foreach([
                    ['k' => __('Posting a brief'), 'v' => __('Free')],
                    ['k' => __('Commission on work'), 'v' => __('None')],
                    ['k' => __('Cost to unlock a brief'), 'v' => __('1 credit')],
                    ['k' => __('Credits on joining'), 'v' => __('3 free')],
                ] as $fact)
                    <div class="bg-paper p-5 sm:p-6 grid gap-2">
                        <dt class="font-data text-[10px] uppercase tracking-[0.14em] text-ink-faint">{{ $fact['k'] }}</dt>
                        <dd class="font-display text-2xl text-ink">{{ $fact['v'] }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    {{-- Close --}}
    <section class="border-b border-line">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-14 sm:py-20 grid gap-6 justify-items-start">
            <h2 class="font-display-caps text-3xl sm:text-4xl text-ink max-w-[20ch]">{{ __('Ready when you are') }}</h2>
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="{{ route('client.register') }}"
                   class="btn-lift font-data text-[11px] uppercase tracking-[0.14em] font-semibold px-7 py-4 bg-brand-deep text-paper text-center">
                    {{ __('Post a brief') }}
                </a>
                <a href="{{ route('for-talent') }}" wire:navigate
                   class="font-data text-[11px] uppercase tracking-[0.14em] font-semibold px-7 py-4 border border-ink text-ink text-center hover:bg-ink hover:text-paper transition-colors">
                    {{ __('I am here to find work') }}
                </a>
            </div>
        </div>
    </section>


    <x-faq-section
        :eyebrow="__('Common questions')"
        :heading="__('Before you post')"
        :items="[
            ['q' => __('What does it cost to post a brief?'), 'a' => __('Nothing. Posting is free, there is no limit during early access, and Meshwork HQ takes no commission on the work you agree. The only thing that ever costs money is a credit, and only professionals buy those.')],
            ['q' => __('Why do professionals pay to contact me?'), 'a' => __('Because it changes who gets in touch. A professional spends one credit to reveal your details and open a thread, so the people who message you have decided your brief is worth their own money. You read a handful of serious pitches instead of a hundred copy pasted ones.')],
            ['q' => __('How quickly will people see my brief?'), 'a' => __('The ten closest skill matches are alerted within seconds of publishing. If the brief still needs answers after six hours, fifteen more are notified, and after twenty four hours the rest of the matched pool, capped at fifty people in total.')],
            ['q' => __('Who exactly gets alerted?'), 'a' => __('Only professionals who share at least one skill tag with your brief. Nobody outside that set is contacted, because a brief that reaches the wrong people is worse than one that reaches nobody.')],
            ['q' => __('What happens after someone unlocks my brief?'), 'a' => __('A direct message thread opens between the two of you. Terms, scope and payment are agreed between you, off the platform, and Meshwork HQ takes no cut of the fee.')],
            ['q' => __('How long does a brief stay live?'), 'a' => __('Thirty days from publication, after which it expires automatically. You can close it earlier at any point, or mark someone as hired, which closes it to new pitches.')],
            ['q' => __('Do I have to hire anyone?'), 'a' => __('No. If nothing fits, close the brief. There is no obligation and no fee either way.')],
        ]"
    />

    <x-marketing-footer />
</div>
