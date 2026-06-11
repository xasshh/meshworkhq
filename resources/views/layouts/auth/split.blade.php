<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased font-sans">
        <div class="min-h-screen grid lg:grid-cols-2">

            {{-- ── LEFT: Brand Panel ── --}}
            <div class="hidden lg:flex flex-col bg-slate-main relative overflow-hidden">

                {{-- Depth layers --}}
                <div class="absolute inset-0" style="background: radial-gradient(ellipse 70% 55% at 15% -5%, rgba(27, 80, 212, 0.35) 0%, transparent 65%);"></div>
                <div class="absolute inset-0" style="background: radial-gradient(ellipse 50% 40% at 85% 100%, rgba(27, 80, 212, 0.18) 0%, transparent 60%);"></div>

                {{-- Refined grid pattern --}}
                <div class="absolute inset-0 opacity-[0.035]"
                     style="background-image: linear-gradient(rgba(255,255,255,0.6) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.6) 1px, transparent 1px); background-size: 32px 32px;"></div>

                {{-- Blue accent bar --}}
                <div class="absolute top-0 left-0 w-0.5 h-full bg-emerald-main/60"></div>

                <div class="relative z-10 flex flex-col h-full p-12">
                    {{-- Logo --}}
                    <a href="{{ route('home') }}" class="flex items-center gap-3 mb-auto group" wire:navigate>
                        <div class="w-9 h-9 bg-emerald-main rounded-xl flex items-center justify-center shadow-lg shadow-emerald-main/30 group-hover:shadow-emerald-main/50 transition-shadow duration-300">
                            <span class="text-white font-display text-lg font-bold leading-none">M</span>
                        </div>
                        <span class="text-white font-display font-semibold text-base tracking-tight">Meshwork <span class="text-emerald-main">HQ</span></span>
                    </a>

                    {{-- Central content --}}
                    <div class="py-12">
                        <div class="inline-flex items-center gap-2 bg-emerald-main/12 border border-emerald-main/25 rounded-full px-3 py-1.5 mb-7">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-main animate-pulse-dot"></span>
                            <span class="text-xs font-semibold text-emerald-main uppercase tracking-wider">Alert-First Platform</span>
                        </div>

                        <h2 class="font-display text-[38px] text-white leading-[1.12] mb-5 tracking-tight">
                            The marketplace<br>that comes to you.
                        </h2>
                        <p class="text-slate-400 leading-relaxed mb-9 max-w-sm text-[15px]">
                            Post a brief once. Our engine matches and alerts the right professionals instantly — no browsing, no bidding wars.
                        </p>

                        {{-- Trust signals --}}
                        <div class="space-y-3.5 mb-10">
                            @foreach ([
                                ['icon' => 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z', 'text' => 'Paystack & Flutterwave — Naira-native payments'],
                                ['icon' => 'M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0', 'text' => 'SMS + Email alerts dispatched in under 60 seconds'],
                                ['icon' => 'M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z', 'text' => 'Zero commission on completed engagements'],
                            ] as $signal)
                                <div class="flex items-center gap-3.5">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-main/12 border border-emerald-main/20 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4 text-emerald-main" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $signal['icon'] }}" />
                                        </svg>
                                    </div>
                                    <p class="text-sm text-slate-300 leading-snug">{{ $signal['text'] }}</p>
                                </div>
                            @endforeach
                        </div>

                        {{-- Testimonial --}}
                        <div class="relative overflow-hidden rounded-2xl border border-white/10 bg-white/5 backdrop-blur-sm p-5">
                            <div class="absolute top-0 right-0 w-24 h-24 rounded-full bg-emerald-main/5 blur-2xl"></div>
                            <div class="relative">
                                <div class="flex items-center gap-0.5 mb-3">
                                    @for ($i = 0; $i < 5; $i++)
                                        <svg class="w-3.5 h-3.5 text-emerald-main fill-current" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                </div>
                                <p class="text-sm text-slate-300 leading-relaxed mb-4">
                                    "Within two hours of posting my brief, I had three qualified designers in my inbox. I've never hired this fast."
                                </p>
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-emerald-main flex items-center justify-center shrink-0">
                                        <span class="text-white text-xs font-bold font-display">TC</span>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold text-white">Tunde Coker</p>
                                        <p class="text-[11px] text-slate-500">Founder, Apata Digital · Lagos</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <p class="text-xs text-slate-600 mt-auto">© {{ date('Y') }} Meshwork HQ · African-first talent marketplace</p>
                </div>
            </div>

            {{-- ── RIGHT: Form Panel ── --}}
            <div class="flex items-center justify-center p-8 lg:p-12 bg-white relative">
                {{-- Subtle top-right decorative element --}}
                <div class="absolute top-0 right-0 w-72 h-72 rounded-full bg-emerald-soft opacity-40 blur-3xl pointer-events-none -translate-y-1/2 translate-x-1/3"></div>

                <div class="w-full max-w-sm relative z-10">
                    {{-- Mobile logo --}}
                    <div class="flex justify-center mb-8 lg:hidden">
                        <a href="{{ route('home') }}" class="flex items-center gap-2.5" wire:navigate>
                            <div class="w-8 h-8 bg-emerald-main rounded-lg flex items-center justify-center shadow-md shadow-emerald-main/25">
                                <span class="text-white font-display font-bold text-base leading-none">M</span>
                            </div>
                            <span class="font-display font-semibold text-slate-main text-sm">Meshwork <span class="text-emerald-main">HQ</span></span>
                        </a>
                    </div>
                    {{ $slot }}
                </div>
            </div>
        </div>

        <x-toast-hub />

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
