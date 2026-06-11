<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-light-canvas antialiased font-sans">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-slate-200/80 bg-white">
            <flux:sidebar.header class="border-b border-slate-100 pb-4">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 group" wire:navigate>
                    <div class="w-8 h-8 bg-emerald-main rounded-lg flex items-center justify-center shrink-0 shadow-md shadow-emerald-main/25 group-hover:shadow-emerald-main/40 transition-shadow duration-300">
                        <span class="text-white font-display font-bold text-base leading-none">M</span>
                    </div>
                    <span class="font-display font-semibold text-slate-main text-sm tracking-tight">Meshwork <span class="text-emerald-main">HQ</span></span>
                </a>
                <div class="ml-auto hidden lg:block">
                    @livewire('notification-bell')
                </div>
                <flux:sidebar.collapse class="lg:hidden ml-auto text-slate-400 hover:text-slate-600 transition-colors" />
            </flux:sidebar.header>

            <flux:sidebar.nav class="pt-3">
                <flux:sidebar.group :heading="__('Platform')" class="grid">
                    <flux:sidebar.item
                        icon="home"
                        :href="route('dashboard')"
                        :current="request()->routeIs('dashboard')"
                        wire:navigate
                        class="text-slate-600 hover:text-slate-900 hover:bg-slate-50 rounded-lg transition-all duration-150"
                    >
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                @if(auth()->user()->isClient())
                    <flux:sidebar.group :heading="__('Actions')" class="grid">
                        <div class="px-1 mt-1">
                            <a href="{{ route('client.brief.create') }}" wire:navigate
                               class="btn-lift flex items-center justify-center gap-2 w-full bg-emerald-main text-white font-semibold text-sm py-2.5 px-4 rounded-xl hover:bg-emerald-deep transition-colors shadow-sm shadow-emerald-main/20">
                                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                                {{ __('Post a Brief') }}
                            </a>
                        </div>
                    </flux:sidebar.group>
                @endif

                @if(auth()->user()->isProfessional())
                    <flux:sidebar.group :heading="__('Account')" class="grid">
                        <div class="mx-1 mt-1 rounded-xl bg-slate-main p-4 relative overflow-hidden">
                            {{-- Subtle blue glow inside the dark card --}}
                            <div class="absolute inset-0 opacity-30" style="background: radial-gradient(ellipse 80% 60% at 50% 0%, rgba(27, 80, 212, 0.5) 0%, transparent 70%);"></div>
                            <div class="relative z-10">
                                <p class="text-[10px] text-white/40 uppercase tracking-widest font-semibold mb-1.5">{{ __('Credits') }}</p>
                                <div class="flex items-end gap-1.5 mb-2.5">
                                    <p class="font-display text-white font-bold text-3xl leading-none">
                                        {{ number_format(auth()->user()->credits) }}
                                    </p>
                                    <span class="text-emerald-main text-xs font-semibold pb-0.5">{{ __('available') }}</span>
                                </div>
                                <div class="w-full h-1 bg-white/10 rounded-full overflow-hidden">
                                    <div class="h-full bg-emerald-main rounded-full transition-all duration-700"
                                         style="width: {{ min(100, auth()->user()->credits * 10) }}%"></div>
                                </div>
                            </div>
                        </div>
                    </flux:sidebar.group>
                @endif
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav class="pb-2 border-t border-slate-100 pt-3">
                <flux:sidebar.item
                    icon="cog-6-tooth"
                    :href="route('profile.edit')"
                    wire:navigate
                    class="text-slate-500 hover:text-slate-900 hover:bg-slate-50 rounded-lg transition-all duration-150"
                >
                    {{ __('Settings') }}
                </flux:sidebar.item>
            </flux:sidebar.nav>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile Header -->
        <flux:header class="lg:hidden border-b border-slate-200/80 bg-white/90 backdrop-blur-md">
            <flux:sidebar.toggle class="lg:hidden text-slate-500 hover:text-slate-700 transition-colors" icon="bars-2" inset="left" />
            <div class="flex items-center gap-2 mx-auto">
                <div class="w-7 h-7 bg-emerald-main rounded-lg flex items-center justify-center shadow-sm shadow-emerald-main/25">
                    <span class="text-white font-display font-bold text-sm leading-none">M</span>
                </div>
                <span class="font-display font-semibold text-slate-main text-sm tracking-tight">Meshwork <span class="text-emerald-main">HQ</span></span>
            </div>
            @livewire('notification-bell')
            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />
                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" />
                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>
                    <flux:menu.separator />
                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>
                    <flux:menu.separator />
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full cursor-pointer">
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        <x-toast-hub />

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
