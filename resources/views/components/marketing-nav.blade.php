@php
    $links = [
        ['label' => __('How it works'), 'href' => route('how-it-works'), 'route' => 'how-it-works'],
        ['label' => __('For talent'), 'href' => route('for-talent'), 'route' => 'for-talent'],
        ['label' => __('Find professionals'), 'href' => route('directory'), 'route' => 'directory'],
    ];
@endphp

<header
    x-data="{ open: false }"
    @keydown.escape.window="open = false"
    class="sticky top-0 z-40 bg-paper/90 backdrop-blur-sm border-b border-line"
>
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
        <div class="h-16 sm:h-20 flex items-center gap-6">

            <a href="{{ route('home') }}" wire:navigate class="shrink-0" aria-label="{{ __('Meshwork HQ home') }}">
                <img
                    src="{{ asset('images/meshwork-lockup.png') }}"
                    alt="Meshwork HQ"
                    width="500"
                    height="109"
                    class="h-7 sm:h-8 w-auto"
                />
            </a>

            {{-- Desktop nav --}}
            <nav class="hidden lg:flex items-center gap-8 ml-4" aria-label="{{ __('Main') }}">
                @foreach($links as $link)
                    <a
                        href="{{ $link['href'] }}"
                        @if(isset($link['route'])) wire:navigate @endif
                        class="font-data text-[11px] uppercase tracking-[0.14em] transition-colors {{ isset($link['route']) && request()->routeIs($link['route']) ? 'text-ink font-semibold' : 'text-ink-soft hover:text-ink' }}"
                    >{{ $link['label'] }}</a>
                @endforeach
            </nav>

            <div class="ml-auto flex items-center gap-2 sm:gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" wire:navigate
                       class="btn-lift font-data text-[11px] uppercase tracking-[0.12em] font-semibold px-4 sm:px-5 py-3 bg-brand-deep text-paper">
                        {{ __('Dashboard') }}
                    </a>
                @else
                    <a href="{{ route('professional.login') }}"
                       class="hidden sm:inline-flex font-data text-[11px] uppercase tracking-[0.14em] text-ink-soft hover:text-ink transition-colors px-2">
                        {{ __('Log in') }}
                    </a>
                    <a href="{{ route('client.register') }}"
                       class="btn-lift font-data text-[11px] uppercase tracking-[0.12em] font-semibold px-4 sm:px-5 py-3 bg-brand-deep text-paper whitespace-nowrap">
                        {{ __('Post a brief') }}
                    </a>
                @endauth

                {{-- Mobile trigger --}}
                <button
                    type="button"
                    @click="open = ! open"
                    :aria-expanded="open ? 'true' : 'false'"
                    aria-controls="marketing-menu"
                    class="lg:hidden w-10 h-10 -mr-2 grid place-items-center text-ink"
                    aria-label="{{ __('Menu') }}"
                >
                    <span class="grid gap-[5px]" aria-hidden="true">
                        <span class="block w-5 h-0.5 bg-current transition-transform" :class="open && 'translate-y-[7px] rotate-45'"></span>
                        <span class="block w-5 h-0.5 bg-current transition-opacity" :class="open && 'opacity-0'"></span>
                        <span class="block w-5 h-0.5 bg-current transition-transform" :class="open && '-translate-y-[7px] -rotate-45'"></span>
                    </span>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile panel --}}
    <div
        id="marketing-menu"
        x-show="open"
        x-cloak
        x-collapse
        class="lg:hidden border-t border-line bg-paper"
    >
        <nav class="max-w-6xl mx-auto px-4 sm:px-6 py-4 grid" aria-label="{{ __('Main') }}">
            @foreach($links as $link)
                <a
                    href="{{ $link['href'] }}"
                    @if(isset($link['route'])) wire:navigate @endif
                    @click="open = false"
                    class="font-data text-[11px] uppercase tracking-[0.14em] text-ink-soft hover:text-ink py-3.5 border-b border-line-soft last:border-b-0 transition-colors"
                >{{ $link['label'] }}</a>
            @endforeach

            @guest
                <a href="{{ route('professional.login') }}"
                   class="font-data text-[11px] uppercase tracking-[0.14em] text-ink-soft hover:text-ink py-3.5 transition-colors">
                    {{ __('Log in') }}
                </a>
            @endguest
        </nav>
    </div>
</header>
