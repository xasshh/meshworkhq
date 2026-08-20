<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-paper antialiased font-sans">
        <div class="min-h-screen lg:grid lg:grid-cols-[1.05fr_0.95fr]">

            {{-- Brand panel. Photograph under a navy wash so the type stays readable. --}}
            <div class="hidden lg:block relative overflow-hidden bg-navy">
                <img
                    src="{{ asset('images/auth-professional.jpg') }}"
                    alt=""
                    width="800"
                    height="1000"
                    class="absolute inset-0 w-full h-full object-cover object-top"
                />

                {{-- Two layers: a flat wash for contrast, then bottom weight for the type. --}}
                <div class="absolute inset-0 bg-navy/70" aria-hidden="true"></div>
                <div class="absolute inset-0" aria-hidden="true"
                     style="background: linear-gradient(to top, rgba(22,32,43,0.95) 0%, rgba(22,32,43,0.45) 45%, rgba(22,32,43,0.12) 100%);"></div>

                <div class="relative z-10 flex flex-col h-full p-10 xl:p-14">
                    <a href="{{ route('home') }}" class="w-fit">
                        <img
                            src="{{ asset('images/meshwork-lockup-light.png') }}"
                            alt="Meshwork HQ"
                            width="500"
                            height="109"
                            class="h-9 w-auto"
                        />
                    </a>

                    <div class="mt-auto grid gap-6 max-w-md">
                        <p class="flex items-center gap-2.5 font-data text-[11px] uppercase tracking-[0.18em] text-brand-lit">
                            <span class="w-2 h-2 bg-brand shrink-0" aria-hidden="true"></span>
                            {{ __('Alert first, not search first') }}
                        </p>

                        <h2 class="font-display-caps text-4xl xl:text-5xl text-paper">
                            {{ __('Good work finds you before it finds everyone else.') }}
                        </h2>

                        <p class="text-sm text-paper/70 leading-relaxed max-w-[42ch]">
                            {{ __('A brief goes to the ten best matched professionals first. Six hours later, fifteen more. A day later, everyone else.') }}
                        </p>

                        <div class="grid gap-2 pt-2">
                            <div class="wave-track">
                                <span class="wave-seg" data-state="live" style="--wave-progress: 0.34"></span>
                                <span class="wave-seg !bg-paper/20"></span>
                                <span class="wave-seg !bg-paper/20"></span>
                            </div>
                            <div class="wave-meta">
                                <span class="wave-who !text-brand-lit">{{ __('Wave 1') }} &middot; 10 {{ __('pros') }}</span>
                                <span class="wave-when !text-paper/45">{{ __('opens wider in 5h 42m') }}</span>
                            </div>
                        </div>
                    </div>

                    <p class="font-data text-[10px] uppercase tracking-[0.14em] text-paper/30 mt-10">
                        &copy; {{ date('Y') }} Meshwork HQ &middot; {{ __('Nigeria') }}
                    </p>
                </div>
            </div>

            {{-- Form panel --}}
            <div class="flex items-center justify-center px-4 py-10 sm:px-8 sm:py-14 lg:p-12 bg-paper">
                <div class="w-full max-w-sm">

                    <div class="flex justify-center mb-10 lg:hidden">
                        <a href="{{ route('home') }}">
                            <img
                                src="{{ asset('images/meshwork-lockup.png') }}"
                                alt="Meshwork HQ"
                                width="500"
                                height="109"
                                class="h-8 w-auto"
                            />
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
