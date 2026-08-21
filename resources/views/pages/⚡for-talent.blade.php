<?php

use App\Enums\Role;
use App\Models\Skill;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::marketing')] #[Title('For talent')] class extends Component
{
    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Skill>
     */
    #[Computed]
    public function skills(): \Illuminate\Database\Eloquent\Collection
    {
        return Skill::active()->take(14)->get();
    }

    #[Computed]
    public function professionalCount(): int
    {
        return User::where('role', Role::Professional)->count();
    }
}; ?>

<div>
    <x-marketing-nav />

    {{-- Hero. Split the same way the auth screens are: copy one side, photo the
         other, filling the column. The photograph is cropped square for this,
         because the full 2.23:1 frame loses both faces in a tall panel. --}}
    <section class="bg-navy">
        <div class="grid lg:grid-cols-[1.05fr_0.95fr] items-stretch">
            <div class="order-2 lg:order-1 px-4 sm:px-6 lg:pl-[max(1.5rem,calc((100vw-72rem)/2))] lg:pr-14 py-14 sm:py-20 grid gap-6 content-center">
                <p class="flex items-center gap-2.5 font-sans text-[10px] sm:text-[11px] font-bold uppercase tracking-[0.1em] text-brand-lit">
                    <span class="w-2 h-2 bg-brand shrink-0" aria-hidden="true"></span>
                    {{ __('For talent') }}
                </p>

                <h1 class="font-display-caps text-paper text-[2.5rem] sm:text-5xl lg:text-6xl">
                    {{ __('Stop scrolling job boards.') }}
                </h1>

                <p class="text-base sm:text-lg text-paper/65 leading-relaxed max-w-[46ch]">
                    {{ __('Work that matches your skills is sent to you the moment it is posted. You are one of ten people who can see it, and you decide whether it is worth a credit.') }}
                </p>

                <div class="flex flex-col sm:flex-row gap-3 mt-1">
                    <a href="{{ route('professional.register') }}"
                       class="btn-lift font-sans text-[11px] font-bold uppercase tracking-[0.1em] font-semibold px-7 py-4 bg-brand text-navy-deep text-center">
                        {{ __('Create your profile') }}
                    </a>
                    <a href="{{ route('professional.login') }}"
                       class="font-sans text-[11px] font-bold uppercase tracking-[0.1em] font-semibold px-7 py-4 border border-paper/25 text-paper/80 text-center hover:text-paper hover:border-paper/50 transition-colors">
                        {{ __('Sign in') }}
                    </a>
                </div>

                <p class="text-xs text-paper/40">{{ __('Three free unlocks when you join. No commission on what you earn.') }}</p>
            </div>

            <div class="order-1 lg:order-2 relative min-h-[20rem] sm:min-h-[26rem] lg:min-h-0">
                <img
                    src="{{ asset('images/for-talent-panel.jpg') }}"
                    alt="{{ __('Two professionals reviewing matched brief alerts together') }}"
                    width="768"
                    height="716"
                    loading="eager"
                    fetchpriority="high"
                    class="absolute inset-0 w-full h-full object-cover object-center"
                />
            </div>
        </div>
    </section>

    {{-- Why it is different --}}
    <section class="border-b border-line">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-14 sm:py-20">
            <div class="grid gap-3 mb-10 max-w-2xl">
                <p class="font-sans text-[10px] font-bold uppercase tracking-[0.1em] text-brand-deep">{{ __('Why bother') }}</p>
                <h2 class="font-display-caps text-3xl sm:text-4xl lg:text-5xl text-ink">
                    {{ __('The maths of a job board is against you') }}
                </h2>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-px bg-line border border-line">
                @foreach([
                    ['h' => __('Ten, not two hundred'), 'p' => __('A brief reaches the ten closest skill matches first. You are not writing the two hundredth proposal nobody will read.')],
                    ['h' => __('Six hours of head start'), 'p' => __('Wave 2 opens six hours after wave 1, wave 3 a day later. Being early is worth something here, and the interface shows you exactly how early you are.')],
                    ['h' => __('You choose what to pay for'), 'p' => __('One credit per unlock, spent only on briefs you actually want. No subscription needed to be seen.')],
                    ['h' => __('No commission, ever'), 'p' => __('What you agree with the client is what you get. Meshwork HQ takes nothing from the fee.')],
                    ['h' => __('Naira from end to end'), 'p' => __('Priced and paid the way you already pay for things, with no currency conversion in the way.')],
                    ['h' => __('You see who you are pitching to'), 'p' => __('Clients can verify their identity with NIN or CAC, and the badge shows on their briefs.')],
                ] as $point)
                    <div class="bg-paper p-6 sm:p-7 grid gap-3 content-start">
                        <h3 class="font-display text-lg text-ink leading-tight">{{ $point['h'] }}</h3>
                        <p class="text-sm text-ink-soft leading-relaxed">{{ $point['p'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Getting started --}}
    <section class="border-b border-line">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-14 sm:py-20 grid lg:grid-cols-[1fr_1.1fr] gap-10 lg:gap-16">
            <div class="grid gap-5 content-start">
                <p class="font-sans text-[10px] font-bold uppercase tracking-[0.1em] text-brand-deep">{{ __('Getting started') }}</p>
                <h2 class="font-display-caps text-3xl sm:text-4xl text-ink max-w-[16ch]">
                    {{ __('Your profile is the matching engine') }}
                </h2>
                <p class="text-sm sm:text-base text-ink-soft leading-relaxed max-w-[52ch]">
                    {{ __('There is no application and no approval queue. Sign up, tag your skills, write a few lines about your work, and briefs start arriving. Profiles need to be at least 70 percent complete before alerts begin, which takes about five minutes.') }}
                </p>
                <a href="{{ route('professional.register') }}"
                   class="btn-lift w-fit mt-1 font-sans text-[11px] font-bold uppercase tracking-[0.1em] font-semibold px-7 py-4 bg-brand-deep text-paper">
                    {{ __('Create your profile') }}
                </a>
            </div>

            <div class="grid gap-4 content-start">
                <p class="font-sans text-[10px] font-bold uppercase tracking-[0.1em] text-ink-faint">{{ __('Skills briefs are being posted for') }}</p>
                @if($this->skills->isNotEmpty())
                    <div class="flex flex-wrap gap-2">
                        @foreach($this->skills as $skill)
                            <span class="tag">{{ $skill->name }}</span>
                        @endforeach
                    </div>
                @endif
                <p class="text-xs text-ink-faint">
                    {{ __('Tag any of these on your profile and matching briefs reach you automatically. You can add your own too.') }}
                </p>

                <a href="{{ route('directory') }}" wire:navigate
                   class="font-sans text-[11px] font-bold uppercase tracking-[0.1em] text-brand-deep hover:underline mt-2">
                    {{ trans_choice('{0}See the directory|{1}See the :count professional already here|[2,*]See the :count professionals already here', $this->professionalCount, ['count' => $this->professionalCount]) }}
                </a>
            </div>
        </div>
    </section>

    {{-- Credits, honestly --}}
    <section class="border-b border-line">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-14 sm:py-20 grid gap-6 max-w-3xl">
            <p class="font-sans text-[10px] font-bold uppercase tracking-[0.1em] text-brand-deep">{{ __('About credits') }}</p>
            <h2 class="font-display-caps text-3xl sm:text-4xl text-ink">{{ __('What a credit actually buys') }}</h2>
            <p class="text-sm sm:text-base text-ink-soft leading-relaxed">
                {{ __('One credit reveals the client behind a brief and opens a direct thread with them. That is the whole product. You start with three free, and after that credits are bought in packs.') }}
            </p>
            <p class="text-sm sm:text-base text-ink-soft leading-relaxed">
                {{ __('Nothing is deducted for browsing, for being matched, or for being alerted. You only ever spend on a brief you have read and decided you want.') }}
            </p>
            <a href="{{ route('how-it-works') }}" wire:navigate
               class="font-sans text-[11px] font-bold uppercase tracking-[0.1em] text-brand-deep hover:underline">
                {{ __('See how the whole thing works') }}
            </a>
        </div>
    </section>


    <x-faq-section
        dark
        :eyebrow="__('Common questions')"
        :heading="__('Before you join')"
        :items="[
            ['q' => __('Does it cost anything to join?'), 'a' => __('No. Creating a profile and receiving alerts is free, and there is no subscription needed to be seen. You start with three free unlocks.')],
            ['q' => __('What exactly does a credit buy?'), 'a' => __('One credit reveals the client behind a brief and opens a direct thread with them. Nothing is deducted for browsing, for being matched, or for being alerted. You only ever spend on a brief you have read and decided you want.')],
            ['q' => __('Does Meshwork HQ take a cut of what I earn?'), 'a' => __('Never. What you agree with the client is what you get. The platform earns from credits, not from your fee, so there is no incentive for us to sit between you and the work.')],
            ['q' => __('How do I get alerted first?'), 'a' => __('Briefs go to the ten closest skill matches immediately, then widen after six and twenty four hours. Tagging your skills accurately and keeping your profile complete is what puts you in that first group.')],
            ['q' => __('Why is my profile not receiving alerts?'), 'a' => __('Profiles need to be at least 70 percent complete before alerts begin, and you need at least one skill tag, since matching is done on skills. Your profile page lists exactly which fields are still missing.')],
            ['q' => __('How do I know the client is real?'), 'a' => __('Clients can verify their identity with a NIN or a CAC registration, and a verified badge shows on their briefs and in conversation. You can also see how many professionals have pitched to them before.')],
            ['q' => __('How do I pay for credits?'), 'a' => __('Through Paystack, in naira, by card, bank transfer or USSD. Credits land in your wallet as soon as the payment clears.')],
        ]"
    />

    <x-marketing-footer />
</div>
