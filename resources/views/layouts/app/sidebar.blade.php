@php
    $user = auth()->user();
    $isPro = $user->isProfessional();

    // A count beside the nav item, so a waiting submission is visible without
    // remembering to look.
    $pendingVerifications = $user->isAdmin()
        ? \App\Models\User::where('verification_status', \App\Enums\VerificationStatus::Pending)->count()
        : 0;

    $unreadAlerts = $isPro
        ? \App\Models\Alert::where('professional_id', $user->id)
            ->where('status', \App\Enums\AlertStatus::Notified)
            ->count()
        : 0;

    $unreadMessages = \App\Models\Message::whereHas('conversation', function ($q) use ($user, $isPro) {
        $q->where($isPro ? 'professional_id' : 'client_id', $user->id);
    })
        ->where('sender_id', '!=', $user->id)
        ->whereNull('read_at')
        ->count();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-paper text-ink antialiased font-sans">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-line bg-paper">

            <flux:sidebar.header class="border-b border-line-soft pb-4">
                <a href="{{ route('dashboard') }}" wire:navigate class="shrink-0" aria-label="{{ __('Meshwork HQ') }}">
                    <img src="{{ asset('images/meshwork-lockup.png') }}" alt="Meshwork HQ"
                         width="500" height="109" class="h-7 w-auto" />
                </a>
                <div class="ml-auto hidden lg:block">
                    @livewire('notification-bell')
                </div>
                <flux:sidebar.collapse class="lg:hidden ml-auto text-ink-faint hover:text-ink transition-colors" />
            </flux:sidebar.header>

            <flux:sidebar.nav class="pt-4">

                @if($isPro)
                    <flux:sidebar.group :heading="__('Work')" class="grid">
                        <x-nav-link :href="route('professional.alerts')" :current="request()->routeIs('professional.alerts')" icon="bell-alert" :badge="$unreadAlerts">
                            {{ __('Alert feed') }}
                        </x-nav-link>
                        <x-nav-link :href="route('professional.dashboard')" :current="request()->routeIs('professional.dashboard')" icon="home">
                            {{ __('Overview') }}
                        </x-nav-link>
                        <x-nav-link :href="route('professional.pitches')" :current="request()->routeIs('professional.pitches')" icon="paper-airplane">
                            {{ __('My pitches') }}
                        </x-nav-link>
                        <x-nav-link :href="route('professional.messages')" :current="request()->routeIs('professional.messages', 'professional.conversation')" icon="chat-bubble-left-right" :badge="$unreadMessages">
                            {{ __('Messages') }}
                        </x-nav-link>
                    </flux:sidebar.group>

                    <flux:sidebar.group :heading="__('Account')" class="grid">
                        <x-nav-link :href="route('professional.profile')" :current="request()->routeIs('professional.profile')" icon="user-circle">
                            {{ __('Profile') }}
                        </x-nav-link>
                        <x-nav-link :href="route('professional.wallet')" :current="request()->routeIs('professional.wallet')" icon="wallet">
                            {{ __('Wallet') }}
                        </x-nav-link>
                    </flux:sidebar.group>

                    {{-- Credit meter. Countable ticks, not a progress bar. --}}
                    <div class="mx-1 mt-3 bg-ink rounded-xl p-4 shadow-[var(--shadow-soft)]">
                        <p class="eyebrow text-paper/40 mb-2">{{ __('Credits') }}</p>
                        <div class="flex items-end gap-2 mb-3">
                            <span class="font-display text-3xl font-semibold text-paper leading-none tracking-tight">{{ number_format($user->credits) }}</span>
                            <span class="text-[11px] text-paper/50 pb-0.5">{{ __('available') }}</span>
                        </div>
                        <div class="grid grid-flow-col gap-[3px] mb-3">
                            @for($i = 1; $i <= 10; $i++)
                                <span class="h-3 rounded-sm {{ $i <= min(10, $user->credits) ? 'bg-ember' : 'bg-paper/15' }}"></span>
                            @endfor
                        </div>
                        <a href="{{ route('professional.wallet') }}" wire:navigate
                           class="block text-center text-[11px] font-semibold text-navy-deep bg-ember-lit rounded-full py-2 btn-lift">
                            {{ __('Buy credits') }}
                        </a>
                    </div>
                @else
                    <flux:sidebar.group :heading="__('Work')" class="grid">
                        <x-nav-link :href="route('client.dashboard')" :current="request()->routeIs('client.dashboard')" icon="home">
                            {{ __('Overview') }}
                        </x-nav-link>
                        <x-nav-link :href="route('client.briefs')" :current="request()->routeIs('client.briefs', 'client.brief.detail')" icon="document-text">
                            {{ __('My briefs') }}
                        </x-nav-link>
                        <x-nav-link :href="route('client.messages')" :current="request()->routeIs('client.messages', 'client.conversation')" icon="chat-bubble-left-right" :badge="$unreadMessages">
                            {{ __('Messages') }}
                        </x-nav-link>
                        <x-nav-link :href="route('directory')" :current="request()->routeIs('directory')" icon="users">
                            {{ __('Find professionals') }}
                        </x-nav-link>
                    </flux:sidebar.group>

                    <flux:sidebar.group :heading="__('Account')" class="grid">
                        <x-nav-link :href="route('client.profile')" :current="request()->routeIs('client.profile')" icon="building-office">
                            {{ __('Company profile') }}
                        </x-nav-link>
                        <x-nav-link :href="route('client.verification')" :current="request()->routeIs('client.verification')" icon="shield-check">
                            {{ __('Verification') }}
                        </x-nav-link>
                    </flux:sidebar.group>

                    <div class="px-1 mt-3">
                        <a href="{{ route('client.brief.create') }}" wire:navigate
                           class="btn-lift flex items-center justify-center gap-2 w-full bg-ink text-paper font-semibold text-sm py-2.5 px-4">
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            {{ __('Post a brief') }}
                        </a>
                    </div>
                @endif

                {{-- Staff only. An admin is also a client or a professional, so
                     this sits alongside whichever nav they already see. --}}
                @if($user->isAdmin())
                    <flux:sidebar.group :heading="__('Admin')" class="grid">
                        <x-nav-link :href="route('admin.verifications')" :current="request()->routeIs('admin.verifications')" icon="shield-check" :badge="$pendingVerifications">
                            {{ __('Verifications') }}
                        </x-nav-link>
                    </flux:sidebar.group>
                @endif

            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav class="pb-2 border-t border-line-soft pt-3">
                <x-nav-link :href="route('profile.edit')" :current="request()->routeIs('profile.edit', 'appearance.edit', 'security.edit')" icon="cog-6-tooth">
                    {{ __('Settings') }}
                </x-nav-link>
            </flux:sidebar.nav>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        {{-- Mobile header --}}
        <flux:header class="lg:hidden border-b border-line bg-paper">
            <flux:sidebar.toggle class="lg:hidden text-ink-soft hover:text-ink transition-colors" icon="bars-2" inset="left" />
            <a href="{{ route('dashboard') }}" wire:navigate class="mx-auto" aria-label="{{ __('Meshwork HQ') }}">
                <img src="{{ asset('images/meshwork-lockup.png') }}" alt="Meshwork HQ"
                     width="500" height="109" class="h-7 w-auto" />
            </a>
            @livewire('notification-bell')
            <flux:dropdown position="top" align="end">
                <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />
                <flux:menu>
                    <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                        <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" />
                        <div class="grid flex-1 text-start text-sm leading-tight">
                            <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                            <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                        </div>
                    </div>
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
