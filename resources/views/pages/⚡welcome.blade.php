<?php

use App\Enums\Role;
use App\Models\Brief;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::marketing')] #[Title('Meshwork HQ')] class extends Component
{
    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    #[Computed]
    public function featuredProfessionals(): \Illuminate\Database\Eloquent\Collection
    {
        return User::where('role', Role::Professional)
            ->whereNotNull('professional_title')
            ->latest()
            ->take(3)
            ->get();
    }

    /**
     * @return array<string, int>
     */
    #[Computed]
    public function counts(): array
    {
        return [
            'professionals' => User::where('role', Role::Professional)->count(),
            'briefs' => Brief::whereNotNull('published_at')->count(),
        ];
    }
}; ?>

<div>
    <x-marketing-nav />

    {{-- ── HERO ─────────────────────────────────────────────────
         The product is the visual: a real alert card, overlapping the
         portrait of the person who receives it. --}}
    <section class="border-b border-line overflow-hidden">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-12 sm:py-16 lg:py-24">
            <div class="grid lg:grid-cols-[1.05fr_0.95fr] gap-10 lg:gap-16 items-center">

                <div class="grid gap-6 lg:gap-7">
                    <p class="flex items-center gap-2.5 font-sans text-[10px] sm:text-[11px] font-bold uppercase tracking-[0.1em] text-brand-deep">
                        <span class="w-2 h-2 bg-brand shrink-0" aria-hidden="true"></span>
                        {{ __('Alert first, not search first') }}
                    </p>

                    <h1 class="font-display text-ink text-[2.25rem] leading-[1.05] sm:text-5xl lg:text-[3.25rem] xl:text-[3.5rem] max-w-[15ch]">
                        {{ __('The alert first marketplace for top African talent.') }}
                    </h1>

                    <div class="w-16 h-1 rounded-full bg-ember" aria-hidden="true"></div>

                    <p class="text-lg sm:text-xl font-semibold text-ink leading-snug max-w-[30ch]">
                        {{ __('No scrolling job boards. No race to the bottom.') }}
                    </p>

                    <p class="text-sm sm:text-base text-ink-soft leading-relaxed max-w-[52ch]">
                        {{ __('Clients post a brief. The matching engine alerts the ten best matched professionals within seconds, six hours before the next fifteen. They unlock the lead and pitch you directly.') }}
                    </p>

                    <div class="flex flex-col sm:flex-row gap-3 mt-1">
                        <a href="{{ route('client.register') }}"
                           class="btn btn-primary px-7 py-3.5 text-center">
                            {{ __('Post a brief') }}
                        </a>
                        <a href="{{ route('professional.register') }}"
                           class="btn btn-ghost px-7 py-3.5 text-center">
                            {{ __('Get alerts as talent') }}
                        </a>
                    </div>

                    <p class="text-xs text-ink-faint">
                        {{ __('Free to post. No commission on completed work. Naira from end to end.') }}
                    </p>
                </div>

                {{-- The photograph already carries the alert cards, so it needs
                     no overlay of ours competing with it. --}}
                <div class="relative max-w-sm sm:max-w-md lg:max-w-none mx-auto w-full">
                    <img
                        src="{{ asset('images/hero-professional.jpg') }}"
                        alt="{{ __('A professional receiving a matched brief alert on their phone') }}"
                        width="675"
                        height="900"
                        loading="eager"
                        fetchpriority="high"
                        class="w-full h-auto object-cover lg:max-h-[38rem] lg:w-full lg:object-top"
                    />

                </div>
            </div>
        </div>
    </section>

    {{-- ── HOW IT WORKS ───────────────────────────────────────── --}}
    <section id="how-it-works" class="border-b border-line scroll-mt-20">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-14 sm:py-20">
            <div class="grid gap-3 mb-10 sm:mb-14 max-w-2xl">
                <p class="font-sans text-[10px] font-bold uppercase tracking-[0.1em] text-brand-deep">{{ __('How it works') }}</p>
                <h2 class="font-display-caps text-3xl sm:text-4xl lg:text-5xl text-ink">
                    {{ __('Four steps, and nobody wastes anybody time') }}
                </h2>
            </div>

            <ol class="grid sm:grid-cols-2 lg:grid-cols-4 gap-px bg-line border border-line">
                @foreach([
                    ['n' => '01', 'h' => __('A brief is posted'), 'p' => __('The client describes the work, sets a naira budget, and tags the skills it needs. Posting costs nothing.')],
                    ['n' => '02', 'h' => __('Ten people hear first'), 'p' => __('The engine ranks every professional by skill fit and alerts the top ten immediately. Not a broadcast.')],
                    ['n' => '03', 'h' => __('One credit opens the door'), 'p' => __('A professional who wants the job spends a credit to see the client and start a thread. Paying filters the noise.')],
                    ['n' => '04', 'h' => __('They talk directly'), 'p' => __('No commission and no middleman on the fee. We make the introduction, then get out of the way.')],
                ] as $stage)
                    <li class="bg-paper p-6 sm:p-7 grid gap-3 content-start">
                        <span class="font-data text-[11px] text-brand-deep tracking-[0.1em]">{{ $stage['n'] }}</span>
                        <h3 class="font-display text-lg text-ink leading-tight">{{ $stage['h'] }}</h3>
                        <p class="text-sm text-ink-soft leading-relaxed">{{ $stage['p'] }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- ── FOR TALENT: the dark statement panel ─────────────────
         Full bleed navy, headline left, portrait right, edge to edge. --}}
    <section id="for-talent" class="bg-navy scroll-mt-20">
        <div class="grid lg:grid-cols-2 items-stretch">

            <div class="order-2 lg:order-1 px-4 sm:px-6 lg:pl-[max(1.5rem,calc((100vw-72rem)/2))] lg:pr-16 py-14 sm:py-20 lg:py-24 grid gap-6 content-center">
                <img
                    src="{{ asset('images/meshwork-lockup-light.png') }}"
                    alt="Meshwork HQ"
                    width="500"
                    height="109"
                    class="h-8 sm:h-9 w-auto"
                />

                <h2 class="font-display-caps text-3xl sm:text-4xl lg:text-5xl text-paper max-w-[16ch]">
                    {{ __('The work reaches you before it reaches the crowd.') }}
                </h2>

                <p class="text-base sm:text-lg text-paper/60 leading-relaxed max-w-[46ch]">
                    {{ __('You are not competing with two hundred applicants. You are one of ten people who even know the brief exists, and you decide whether it is worth a credit.') }}
                </p>

                <ul class="grid gap-3 mt-1">
                    @foreach([
                        __('Three free unlocks when you join'),
                        __('Verified clients, so you know who you are pitching to'),
                        __('No commission taken from what you earn'),
                    ] as $point)
                        <li class="flex items-start gap-3">
                            <span class="w-2 h-2 bg-brand mt-1.5 shrink-0" aria-hidden="true"></span>
                            <span class="text-sm text-paper/75 leading-snug">{{ $point }}</span>
                        </li>
                    @endforeach
                </ul>

                <a href="{{ route('professional.register') }}"
                   class="btn w-fit mt-2 px-7 py-3.5 bg-ember-lit text-navy-deep">
                    {{ __('Get alerts as talent') }}
                </a>
            </div>

            <div class="order-1 lg:order-2 relative min-h-[20rem] sm:min-h-[28rem] lg:min-h-0">
                <img
                    src="{{ asset('images/auth-professional.jpg') }}"
                    alt="{{ __('A professional reviewing matched briefs') }}"
                    width="800"
                    height="1000"
                    loading="lazy"
                    class="absolute inset-0 w-full h-full object-cover object-top"
                />
            </div>
        </div>
    </section>

    {{-- ── FOR CLIENTS ────────────────────────────────────────── --}}
    <section class="border-b border-line">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-14 sm:py-20 grid lg:grid-cols-2 gap-10 lg:gap-16 items-center">
            <div class="grid gap-5">
                <p class="font-sans text-[10px] font-bold uppercase tracking-[0.1em] text-brand-deep">{{ __('For clients') }}</p>
                <h2 class="font-display-caps text-3xl sm:text-4xl text-ink max-w-[16ch]">
                    {{ __('Fewer pitches, and better ones') }}
                </h2>
                <p class="text-sm sm:text-base text-ink-soft leading-relaxed max-w-[50ch]">
                    {{ __('Because professionals pay to reach you, only the ones who genuinely want the work get in touch. You read a handful of serious pitches instead of sorting through a hundred.') }}
                </p>
                <a href="{{ route('client.register') }}"
                   class="btn btn-primary w-fit mt-1 px-7 py-3.5">
                    {{ __('Post your first brief') }}
                </a>
            </div>

            <dl id="pricing" class="grid sm:grid-cols-2 gap-px bg-line border border-line scroll-mt-20">
                @foreach([
                    ['k' => __('Posting a brief'), 'v' => __('Free')],
                    ['k' => __('Commission on work'), 'v' => __('None')],
                    ['k' => __('Cost to unlock a brief'), 'v' => __('1 credit')],
                    ['k' => __('Credits in early access'), 'v' => __('Free')],
                ] as $fact)
                    <div class="bg-paper p-5 sm:p-6 grid gap-2">
                        <dt class="font-sans text-[10px] font-bold uppercase tracking-[0.1em] text-ink-faint">{{ $fact['k'] }}</dt>
                        <dd class="font-display text-2xl text-ink">{{ $fact['v'] }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    {{-- ── DIRECTORY PREVIEW ──────────────────────────────────── --}}
    @if($this->featuredProfessionals->isNotEmpty())
        <section class="border-b border-line">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-14 sm:py-20">
                <div class="flex items-end justify-between gap-6 flex-wrap pb-6 border-b border-line mb-6">
                    <div class="grid gap-3">
                        <p class="font-sans text-[10px] font-bold uppercase tracking-[0.1em] text-brand-deep">{{ __('The directory') }}</p>
                        <h2 class="font-display-caps text-3xl sm:text-4xl text-ink">{{ __('Who is here') }}</h2>
                    </div>
                    <a href="{{ route('directory') }}" wire:navigate
                       class="font-sans text-[11px] font-bold uppercase tracking-[0.1em] text-ink-soft hover:text-ink transition-colors">
                        {{ __('Browse everyone') }}
                    </a>
                </div>

                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($this->featuredProfessionals as $professional)
                        <a href="{{ route('professionals.show', ['id' => $professional->id]) }}" wire:navigate
                           class="panel card-lift p-5 grid gap-3 content-start group">
                            <div class="flex items-start gap-3">
                                <div class="w-11 h-11 bg-chalk border border-line grid place-items-center shrink-0 overflow-hidden">
                                    @if($professional->avatar_path)
                                        <img src="{{ Storage::disk('public')->url($professional->avatar_path) }}" alt="" class="w-full h-full object-cover">
                                    @else
                                        <span class="font-display text-sm text-ink-faint">{{ $professional->initials() }}</span>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-sm font-semibold text-ink truncate group-hover:text-brand-deep transition-colors">{{ $professional->name }}</h3>
                                    <p class="text-xs text-ink-faint truncate">{{ $professional->professional_title }}</p>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center gap-1.5">
                                <x-verified-badge :user="$professional" />
                                <x-track-record :user="$professional" />
                            </div>

                            @if($professional->bio)
                                <p class="text-sm text-ink-soft line-clamp-2">{{ $professional->bio }}</p>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <x-marketing-footer />
</div>
