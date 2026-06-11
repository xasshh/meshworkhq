<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Meshwork HQ — Alert-First Talent Marketplace for Africa</title>
    <meta name="description" content="Post a brief. Our engine matches and alerts qualified professionals instantly via SMS and email. No browsing, no bidding wars, zero commission." />
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white font-sans antialiased text-slate-main">

    {{-- ── TOP NAV ── --}}
    <nav class="fixed top-0 inset-x-0 z-50 bg-white/95 backdrop-blur-sm border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2.5 group">
                <div class="w-8 h-8 bg-emerald-main rounded-lg flex items-center justify-center shrink-0 shadow-md shadow-emerald-main/25 group-hover:shadow-emerald-main/40 transition-shadow duration-300">
                    <span class="text-white font-display font-bold text-base leading-none">M</span>
                </div>
                <span class="font-display font-semibold text-slate-main text-sm tracking-tight">Meshwork <span class="text-emerald-main">HQ</span></span>
            </a>
            <div class="hidden md:flex items-center gap-8">
                <a href="#why-different" class="text-sm text-slate-500 hover:text-slate-900 transition-colors">Why Us</a>
                <a href="#verticals" class="text-sm text-slate-500 hover:text-slate-900 transition-colors">Services</a>
                <a href="#how-it-works" class="text-sm text-slate-500 hover:text-slate-900 transition-colors">How It Works</a>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('login') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors hidden sm:inline">Sign in</a>
                <a href="{{ route('register') }}" class="text-sm font-semibold bg-emerald-main text-white px-4 py-2 rounded-lg hover:bg-emerald-deep transition-colors">Get Started</a>
            </div>
        </div>
    </nav>

    {{-- ── HERO ── --}}
    <section class="pt-32 pb-24 lg:pt-40 lg:pb-32 relative overflow-hidden">
        <div class="absolute inset-0 opacity-[0.035]" style="background-image: radial-gradient(circle, #0F172A 1px, transparent 1px); background-size: 24px 24px;"></div>
        <div class="absolute top-0 inset-x-0 h-px bg-gradient-to-r from-transparent via-emerald-main/30 to-transparent"></div>

        <div class="relative max-w-7xl mx-auto px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-16 lg:gap-24 items-center">

                <div class="animate-fade-in-up">
                    <div class="inline-flex items-center gap-2 bg-emerald-soft border border-emerald-border text-emerald-deep text-xs font-semibold px-3 py-1.5 rounded-full mb-7">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-main animate-pulse-dot"></span>
                        Now live — matching professionals across Nigeria
                    </div>
                    <h1 class="font-display text-5xl sm:text-6xl lg:text-[66px] text-slate-main leading-[1.04] tracking-tight mb-5">
                        The Alert-First Marketplace for Top African Talent.
                    </h1>
                    <p class="text-lg font-medium text-slate-600 mb-2">No scrolling job boards. No race to the bottom.</p>
                    <p class="text-base text-slate-400 leading-relaxed mb-8 max-w-md">
                        Clients post structural project briefs. Our background engine instantly matches and alerts qualified professionals directly via SMS and email. They unlock the lead and pitch you directly.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-3 mb-5">
                        <a href="{{ route('register') }}" class="btn-lift inline-flex items-center justify-center gap-2 bg-emerald-main text-white font-semibold text-sm px-6 py-3.5 rounded-xl hover:bg-emerald-deep transition-colors shadow-md shadow-emerald-main/20">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            Post a Brief — It's Free
                        </a>
                        <a href="{{ route('register') }}" class="btn-lift inline-flex items-center justify-center gap-2 border border-slate-200 text-slate-700 font-semibold text-sm px-6 py-3.5 rounded-xl hover:bg-slate-50 transition-colors">
                            Join as a Professional
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                        </a>
                    </div>
                    <p class="text-xs text-slate-400">Free to post · No commission on completed work · Paystack-native payments</p>
                </div>

                <div class="animate-fade-in-up" style="animation-delay: 100ms">
                    <div class="relative">
                        <div class="bg-slate-main rounded-2xl p-6 shadow-2xl shadow-slate-900/25">
                            <div class="flex items-start justify-between mb-5">
                                <div>
                                    <p class="text-[10px] font-bold text-white/30 uppercase tracking-widest mb-1">Matching Engine</p>
                                    <div class="flex items-center gap-2">
                                        <div class="w-2 h-2 rounded-full bg-emerald-main animate-pulse"></div>
                                        <p class="text-white font-semibold text-sm">Live Dispatch Active</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-display text-3xl text-white leading-none">27</p>
                                    <p class="text-[10px] text-white/40 mt-0.5">professionals alerted</p>
                                </div>
                            </div>
                            <div class="bg-white/5 border border-white/10 rounded-xl p-4 mb-4">
                                <p class="text-[10px] text-white/30 uppercase tracking-wider font-semibold mb-1.5">Active Brief</p>
                                <p class="text-sm font-semibold text-white mb-2.5">Brand Identity for Fintech App</p>
                                <div class="flex items-center justify-between">
                                    <div class="flex flex-wrap gap-1.5">
                                        <span class="text-[10px] bg-emerald-main/15 text-emerald-main border border-emerald-main/20 px-2 py-0.5 rounded-full font-medium">Branding</span>
                                        <span class="text-[10px] bg-white/8 text-white/50 border border-white/10 px-2 py-0.5 rounded-full font-medium">Logo</span>
                                        <span class="text-[10px] bg-white/8 text-white/50 border border-white/10 px-2 py-0.5 rounded-full font-medium">Figma</span>
                                    </div>
                                    <span class="text-xs font-semibold text-white/60">$1,200</span>
                                </div>
                            </div>
                            <p class="text-[10px] text-white/30 uppercase tracking-wider font-semibold mb-2">Alerts dispatched</p>
                            <div class="space-y-2">
                                @foreach ([
                                    ['initials' => 'AO', 'name' => 'Adaeze O.', 'role' => 'Brand Designer · Lagos', 'delay' => '0ms'],
                                    ['initials' => 'EN', 'name' => 'Emeka N.', 'role' => 'Creative Director · Abuja', 'delay' => '150ms'],
                                    ['initials' => 'FA', 'name' => 'Fatima A.', 'role' => 'Visual Designer · Kano', 'delay' => '300ms'],
                                ] as $pro)
                                    <div class="flex items-center justify-between bg-white/5 border border-white/8 rounded-lg px-3 py-2.5 animate-slide-in-right" style="animation-delay: {{ $pro['delay'] }}">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-full bg-emerald-main/15 border border-emerald-main/25 flex items-center justify-center shrink-0">
                                                <span class="text-emerald-main text-[10px] font-bold">{{ $pro['initials'] }}</span>
                                            </div>
                                            <div>
                                                <p class="text-xs font-semibold text-white leading-none">{{ $pro['name'] }}</p>
                                                <p class="text-[10px] text-white/35 mt-0.5">{{ $pro['role'] }}</p>
                                            </div>
                                        </div>
                                        <span class="text-[10px] font-semibold text-emerald-main bg-emerald-main/10 border border-emerald-main/20 px-2 py-0.5 rounded-full">Alert Sent</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="absolute -top-4 -right-3 bg-white border border-slate-200 rounded-xl px-4 py-2.5 shadow-lg">
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">Project Budget</p>
                            <p class="font-display text-xl text-slate-main leading-tight">$1,200</p>
                        </div>
                        <div class="absolute -bottom-4 -left-3 bg-emerald-main text-white rounded-xl px-4 py-2.5 shadow-lg shadow-emerald-main/25">
                            <p class="text-[9px] font-bold uppercase tracking-wider opacity-70">First alert in</p>
                            <p class="font-semibold text-sm">Under 60 seconds</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ── SKILL SEARCH ── --}}
    <section class="py-16 bg-white border-t border-slate-100">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center mb-8">
                <p class="text-xs font-bold text-emerald-main uppercase tracking-widest mb-3">Find Talent Instantly</p>
                <h2 class="font-display text-3xl sm:text-4xl text-slate-main mb-3">Search Professionals by Skill</h2>
                <p class="text-slate-500 text-sm max-w-md mx-auto">Type a skill to discover registered professionals — real talent, real profiles.</p>
            </div>
            @livewire('skill-search')
        </div>
    </section>

    {{-- ── WHY WE'RE DIFFERENT ── --}}
    <section id="why-different" class="py-24 bg-light-canvas border-t border-slate-100">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="mb-12">
                <p class="text-xs font-bold text-emerald-main uppercase tracking-widest mb-3">Our Differentiation</p>
                <h2 class="font-display text-4xl sm:text-5xl text-slate-main mb-4">Why We Are Different</h2>
                <p class="text-slate-500 max-w-xl leading-relaxed">Six structural advantages that no single existing platform delivers simultaneously — especially not in the African market.</p>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-px bg-slate-200 rounded-2xl border border-slate-200 overflow-hidden">
                @foreach ([
                    ['icon' => 'M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z', 'title' => 'Zero Platform Commission', 'body' => 'Upwork takes up to 20% of every transaction. We take nothing once a lead is unlocked. You and the client transact directly.', 'badge' => 'vs. Upwork 20% cut'],
                    ['icon' => 'M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0', 'title' => 'Alert-First, Not Browse-First', 'body' => 'Professionals don\'t live inside our app scrolling and bidding. Our engine works in the background — alerts arrive the moment a match lands.', 'badge' => 'vs. Fiverr browse model'],
                    ['icon' => 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z', 'title' => 'Credit Economy', 'body' => 'Professionals only spend credits on leads they actually want. This forces us to send high-quality alerts — our economics and product quality are inseparably tied.', 'badge' => 'vs. subscription fatigue'],
                    ['icon' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 18z', 'title' => 'Local Payment Native', 'body' => 'Paystack and Flutterwave from day one. Buy credits via USSD, bank transfer, or Naira card. No PayPal friction, no international wire delays.', 'badge' => 'vs. USD-only platforms'],
                    ['icon' => 'M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-15 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21m-9-1.5h10.5a2.25 2.25 0 0 0 2.25-2.25V6.75a2.25 2.25 0 0 0-2.25-2.25H6.75A2.25 2.25 0 0 0 4.5 6.75v10.5a2.25 2.25 0 0 0 2.25 2.25Zm.75-12h9v9h-9v-9Z', 'title' => 'AI as a First-Class Layer', 'body' => 'AI woven through brief enhancement, semantic matching, pricing intelligence, and pitch coaching — not bolted on. Vector embeddings surface non-obvious skill matches.', 'badge' => 'vs. keyword-only search'],
                    ['icon' => 'M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5z', 'title' => 'Verified Reputation Signals', 'body' => 'Professionals carry a badge tied to real platform activity — not anonymous endorsements. Reviews are anchored in actual unlock-to-engagement events.', 'badge' => 'vs. unverified profiles'],
                ] as $pillar)
                    <div class="bg-white p-6 lg:p-8 group hover:bg-emerald-soft transition-colors duration-200">
                        <div class="w-9 h-9 rounded-lg bg-emerald-soft group-hover:bg-white border border-emerald-border flex items-center justify-center mb-4 transition-colors">
                            <svg class="w-4.5 h-4.5 text-emerald-deep" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $pillar['icon'] }}" />
                            </svg>
                        </div>
                        <h3 class="font-semibold text-slate-main text-sm mb-2">{{ $pillar['title'] }}</h3>
                        <p class="text-sm text-slate-500 leading-relaxed mb-4">{{ $pillar['body'] }}</p>
                        <span class="inline-block text-[10px] font-bold uppercase tracking-wider text-slate-400 bg-slate-50 border border-slate-100 px-2.5 py-1 rounded-full">{{ $pillar['badge'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── VERTICALS ── --}}
    <section id="verticals" class="py-24 bg-white border-t border-slate-100">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="mb-12">
                <p class="text-xs font-bold text-emerald-main uppercase tracking-widest mb-3">Service Categories</p>
                <h2 class="font-display text-4xl sm:text-5xl text-slate-main mb-4">Find the Right Expert</h2>
                <p class="text-slate-500 max-w-xl leading-relaxed">Three launch verticals chosen for proven talent density in the Nigerian and broader West African freelance economy.</p>
            </div>
            <div class="grid lg:grid-cols-3 gap-6">
                @foreach ([
                    ['label' => 'Creative Services', 'num' => '01', 'desc' => 'Visual storytelling, brand identity, and media production for brands that want to stand out.', 'dot' => 'bg-violet-500', 'text' => 'text-violet-600', 'skills' => ['Graphic Design', 'Brand Identity', 'Videography', 'Photography', 'Motion Graphics', 'Illustration']],
                    ['label' => 'Digital Services', 'num' => '02', 'desc' => 'Technology, growth, and digital presence solutions for modern businesses operating online.', 'dot' => 'bg-sky-500', 'text' => 'text-sky-600', 'skills' => ['Web Development', 'Mobile Apps', 'SEO Strategy', 'Social Media Management', 'Content Writing', 'UI/UX Design']],
                    ['label' => 'Corporate & Event', 'num' => '03', 'desc' => 'Professional services for organisations, corporate functions, and live event experiences.', 'dot' => 'bg-amber-500', 'text' => 'text-amber-600', 'skills' => ['Technical Writing', 'Event Planning', 'MC & Hosting', 'Virtual Assistance', 'Project Management', 'Training & Facilitation']],
                ] as $v)
                    <div class="border border-slate-200 rounded-2xl overflow-hidden card-lift bg-white">
                        <div class="px-6 pt-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-2 h-2 rounded-full {{ $v['dot'] }}"></div>
                                    <span class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $v['label'] }}</span>
                                </div>
                                <span class="font-display text-3xl text-slate-100">{{ $v['num'] }}</span>
                            </div>
                            <p class="text-slate-500 text-sm leading-relaxed mb-5">{{ $v['desc'] }}</p>
                        </div>
                        <div class="px-6 pb-6">
                            <div class="flex flex-wrap gap-2 mb-5">
                                @foreach ($v['skills'] as $skill)
                                    <span class="text-xs font-medium text-slate-600 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-full">{{ $skill }}</span>
                                @endforeach
                            </div>
                            <div class="pt-4 border-t border-slate-100">
                                <a href="{{ route('register') }}" class="text-xs font-semibold {{ $v['text'] }} hover:underline inline-flex items-center gap-1.5">
                                    Post a brief in this category
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ── FEATURED PROFESSIONALS CAROUSEL ── --}}
    @if ($featuredProfessionals->isNotEmpty())
    <section class="py-24 bg-white border-t border-slate-100 overflow-hidden">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="mb-10 flex items-end justify-between">
                <div>
                    <p class="text-xs font-bold text-emerald-main uppercase tracking-widest mb-3">Top Talent</p>
                    <h2 class="font-display text-4xl sm:text-5xl text-slate-main mb-3">Featured Professionals</h2>
                    <p class="text-slate-500 max-w-md leading-relaxed">A curated selection of skilled professionals ready to be matched with your next brief.</p>
                </div>
                <a href="{{ route('register') }}" class="hidden lg:inline-flex items-center gap-2 text-sm font-semibold text-emerald-deep hover:text-emerald-main transition-colors shrink-0 mb-1">
                    View all &amp; join
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                </a>
            </div>

            {{-- Alpine.js carousel --}}
            <div
                x-data="{
                    current: 0,
                    total: {{ $featuredProfessionals->count() }},
                    visibleCount() { return window.innerWidth >= 1024 ? 3 : window.innerWidth >= 640 ? 2 : 1; },
                    maxIndex() { return Math.max(0, this.total - this.visibleCount()); },
                    prev() { this.current = Math.max(0, this.current - 1); },
                    next() { this.current = Math.min(this.maxIndex(), this.current + 1); },
                    slideWidth() { return 100 / this.visibleCount(); }
                }"
                class="relative"
            >
                {{-- Track --}}
                <div class="overflow-hidden">
                    <div
                        class="flex transition-transform duration-500 ease-out"
                        :style="`transform: translateX(-${current * slideWidth()}%)`"
                    >
                        @foreach ($featuredProfessionals as $professional)
                            <div class="w-full sm:w-1/2 lg:w-1/3 shrink-0 px-3">
                                <div class="bg-white border border-slate-200 rounded-2xl p-6 hover:border-emerald-main/40 hover:shadow-md transition-all duration-200 group h-full">
                                    <div class="flex items-start gap-4 mb-4">
                                        @if ($professional->avatar_path)
                                            <img src="{{ \Illuminate\Support\Facades\Storage::url($professional->avatar_path) }}"
                                                 class="w-14 h-14 rounded-xl object-cover shrink-0"
                                                 alt="{{ $professional->name }}" />
                                        @else
                                            <div class="w-14 h-14 rounded-xl bg-slate-main flex items-center justify-center text-white font-semibold text-lg shrink-0 group-hover:bg-emerald-main transition-colors duration-300">
                                                {{ $professional->initials() }}
                                            </div>
                                        @endif
                                        <div class="min-w-0 flex-1">
                                            <p class="font-semibold text-slate-main truncate">{{ $professional->name }}</p>
                                            @if ($professional->professional_title)
                                                <p class="text-xs text-emerald-deep font-semibold mt-0.5">{{ $professional->professional_title }}</p>
                                            @endif
                                            <div class="flex items-center gap-1.5 mt-1.5">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-main inline-block"></span>
                                                <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Available</span>
                                            </div>
                                        </div>
                                    </div>

                                    @if ($professional->bio)
                                        <p class="text-sm text-slate-500 leading-relaxed mb-4 line-clamp-3">{{ $professional->bio }}</p>
                                    @endif

                                    @if ($professional->skill_tags && count($professional->skill_tags) > 0)
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach (array_slice($professional->skill_tags, 0, 5) as $tag)
                                                <span class="text-[10px] font-medium bg-slate-50 border border-slate-200 text-slate-500 px-2.5 py-1 rounded-full">{{ $tag }}</span>
                                            @endforeach
                                            @if (count($professional->skill_tags) > 5)
                                                <span class="text-[10px] text-slate-400 self-center font-medium">+{{ count($professional->skill_tags) - 5 }} more</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Navigation --}}
                <div class="flex items-center justify-center gap-3 mt-8">
                    <button
                        @click="prev"
                        :disabled="current === 0"
                        :class="current === 0 ? 'opacity-30 cursor-not-allowed' : 'hover:bg-slate-main hover:text-white hover:border-slate-main'"
                        class="w-9 h-9 rounded-full border border-slate-200 bg-white text-slate-500 flex items-center justify-center transition-all duration-200"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                    </button>

                    {{-- Dot indicators --}}
                    <div class="flex gap-1.5">
                        @for ($i = 0; $i < $featuredProfessionals->count(); $i++)
                            <button
                                @click="current = {{ $i }}"
                                :class="current === {{ $i }} ? 'bg-slate-main w-5' : 'bg-slate-200 w-1.5'"
                                class="h-1.5 rounded-full transition-all duration-300"
                            ></button>
                        @endfor
                    </div>

                    <button
                        @click="next"
                        :disabled="current >= maxIndex()"
                        :class="current >= maxIndex() ? 'opacity-30 cursor-not-allowed' : 'hover:bg-slate-main hover:text-white hover:border-slate-main'"
                        class="w-9 h-9 rounded-full border border-slate-200 bg-white text-slate-500 flex items-center justify-center transition-all duration-200"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </section>
    @endif

    {{-- ── HOW IT WORKS ── --}}
    <section id="how-it-works" class="py-24 bg-slate-main">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="mb-14 text-center">
                <p class="text-xs font-bold text-emerald-main uppercase tracking-widest mb-3">The Three-Part Loop</p>
                <h2 class="font-display text-4xl sm:text-5xl text-white mb-4">How It Works</h2>
                <p class="text-slate-400 max-w-xl mx-auto leading-relaxed">Two parallel journeys — one for clients, one for professionals — with a shared matching engine powering both sides.</p>
            </div>
            <div class="grid lg:grid-cols-3 gap-8 lg:gap-12 items-start">
                <div>
                    <div class="inline-flex items-center gap-2 border border-white/10 bg-white/5 rounded-full px-3 py-1 mb-7">
                        <span class="w-1.5 h-1.5 rounded-full bg-white/40"></span>
                        <span class="text-xs font-semibold text-white/50 uppercase tracking-wider">Client Journey</span>
                    </div>
                    <div class="space-y-5">
                        @foreach ([
                            ['n' => '01', 'title' => 'Post a Structured Brief', 'body' => 'Describe your project with skill tags, budget, and timeline. AI suggests improvements and extracts skill signals in real time.'],
                            ['n' => '02', 'title' => 'Confirm AI-Extracted Tags', 'body' => 'Review the skill signals our engine pulled from your description. Edit or approve in one click before going live.'],
                            ['n' => '03', 'title' => 'See a Live Match Counter', 'body' => 'Watch a real-time counter of professionals being notified right now. Your brief is working the moment you post it.'],
                            ['n' => '04', 'title' => 'Professionals Pitch You', 'body' => 'Qualified professionals who unlocked your brief contact you directly. No anonymous quotes, no cold proposals.'],
                            ['n' => '05', 'title' => 'Hire & Transact Off-Platform', 'body' => 'Contract and pay directly. We take zero commission on the completed engagement — ever.'],
                        ] as $s)
                            <div class="flex gap-4">
                                <div class="shrink-0 w-8 h-8 rounded-full border border-white/15 bg-white/5 flex items-center justify-center mt-0.5">
                                    <span class="text-[10px] font-bold text-white/35">{{ $s['n'] }}</span>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-white mb-1">{{ $s['title'] }}</p>
                                    <p class="text-xs text-slate-400 leading-relaxed">{{ $s['body'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center justify-center py-8 lg:py-0">
                    <div class="text-center">
                        <div class="relative inline-flex items-center justify-center w-48 h-48 mx-auto mb-6">
                            <span class="absolute inline-flex w-full h-full rounded-full bg-emerald-main/10 animate-pulse-ring"></span>
                            <span class="absolute inline-flex w-full h-full rounded-full bg-emerald-main/07 animate-pulse-ring" style="animation-delay: 0.6s"></span>
                            <span class="absolute inline-flex w-full h-full rounded-full bg-emerald-main/04 animate-pulse-ring" style="animation-delay: 1.2s"></span>
                            <div class="relative w-24 h-24 rounded-full bg-emerald-main flex items-center justify-center z-10 shadow-xl shadow-emerald-main/30">
                                <div class="text-center">
                                    <p class="font-display text-white text-xl leading-none">MHQ</p>
                                    <p class="text-white/60 text-[9px] font-bold uppercase tracking-widest mt-0.5">Engine</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-wrap justify-center gap-2">
                            @foreach (['Semantic matching', 'Multi-channel alerts', 'Quality scoring', 'Smart throttling'] as $label)
                                <span class="inline-flex items-center gap-1.5 bg-emerald-main/10 border border-emerald-main/15 text-emerald-main text-[10px] font-semibold px-3 py-1 rounded-full">
                                    <span class="w-1 h-1 rounded-full bg-emerald-main"></span>
                                    {{ $label }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div>
                    <div class="inline-flex items-center gap-2 bg-emerald-main/10 border border-emerald-main/20 rounded-full px-3 py-1 mb-7">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-main animate-pulse-dot"></span>
                        <span class="text-xs font-semibold text-emerald-main uppercase tracking-wider">Professional Journey</span>
                    </div>
                    <div class="space-y-5">
                        @foreach ([
                            ['n' => '01', 'title' => 'Build Your Profile', 'body' => 'Conversational onboarding creates a polished profile in under five minutes. Add skills, portfolio links, and your bio.'],
                            ['n' => '02', 'title' => 'Receive a Matched Alert', 'body' => 'Your first alert arrives via SMS and email — a brief calibrated to your declared skill profile, not a mass blast.'],
                            ['n' => '03', 'title' => 'Review the Brief Preview', 'body' => 'See the project scope, budget, and skill tags. Decide whether the lead is worth pursuing before spending anything.'],
                            ['n' => '04', 'title' => 'Spend 1 Credit to Unlock', 'body' => 'One credit reveals the client\'s full brief and direct contact details. Spend only on leads you genuinely want.'],
                            ['n' => '05', 'title' => 'Pitch and Close the Deal', 'body' => 'Contact the client directly. AI reviews your opening message for clarity and tone before you hit send.'],
                        ] as $s)
                            <div class="flex gap-4">
                                <div class="shrink-0 w-8 h-8 rounded-full border border-emerald-main/25 bg-emerald-main/10 flex items-center justify-center mt-0.5">
                                    <span class="text-[10px] font-bold text-emerald-main">{{ $s['n'] }}</span>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-white mb-1">{{ $s['title'] }}</p>
                                    <p class="text-xs text-slate-400 leading-relaxed">{{ $s['body'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ── FOOTER CTA ── --}}
    <section class="py-24 bg-white border-t border-slate-100">
        <div class="max-w-2xl mx-auto px-6 text-center">
            <p class="text-xs font-bold text-emerald-main uppercase tracking-widest mb-5">Ready to Start?</p>
            <h2 class="font-display text-4xl sm:text-5xl text-slate-main mb-5 leading-tight">One brief. Dozens of<br>qualified professionals.</h2>
            <p class="text-slate-500 mb-8 leading-relaxed max-w-lg mx-auto">Join the first cohort. Early-access clients receive free brief posting credit and dedicated onboarding support from the founding team.</p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('register') }}" class="btn-lift inline-flex items-center justify-center gap-2 bg-emerald-main text-white font-semibold px-8 py-4 rounded-xl hover:bg-emerald-deep transition-colors text-sm shadow-md shadow-emerald-main/20">
                    Post Your First Brief
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                </a>
                <a href="{{ route('register') }}" class="btn-lift inline-flex items-center justify-center border border-slate-200 text-slate-700 font-semibold px-8 py-4 rounded-xl hover:bg-slate-50 transition-colors text-sm">
                    Apply as a Professional
                </a>
            </div>
        </div>
    </section>

    {{-- ── FOOTER ── --}}
    <footer class="bg-slate-main py-10">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 bg-emerald-main rounded-lg flex items-center justify-center">
                        <span class="text-white font-display text-sm leading-none">M</span>
                    </div>
                    <span class="text-white font-semibold text-sm">Meshwork <span class="text-emerald-main">HQ</span></span>
                </div>
                <div class="flex flex-wrap items-center justify-center gap-4 text-xs text-slate-500">
                    <span>Alert-First Talent Marketplace</span>
                    <span>·</span><span>Lagos, Nigeria</span>
                    <span>·</span><span>Paystack-native</span>
                    <span>·</span><a href="{{ route('login') }}" class="hover:text-slate-400 transition-colors">Sign in</a>
                    <span>·</span><a href="{{ route('register') }}" class="hover:text-slate-400 transition-colors">Get Started</a>
                </div>
                <p class="text-xs text-slate-600">© {{ date('Y') }} Meshwork HQ</p>
            </div>
        </div>
    </footer>

    <x-toast-hub />

</body>
</html>
