@php
    $columns = [
        __('For clients') => [
            ['label' => __('Post a brief'), 'href' => route('client.register')],
            ['label' => __('Find professionals'), 'href' => route('directory')],
            ['label' => __('How it works'), 'href' => route('how-it-works')],
            ['label' => __('Client sign in'), 'href' => route('client.login')],
        ],
        __('For professionals') => [
            ['label' => __('Get alerts'), 'href' => route('professional.register')],
            ['label' => __('Why alert first'), 'href' => route('for-talent')],
            ['label' => __('What credits cost'), 'href' => route('how-it-works').'#pricing'],
            ['label' => __('Professional sign in'), 'href' => route('professional.login')],
        ],
    ];
@endphp

<footer class="bg-navy text-paper">
    <div class="shell-wide">

        {{-- Closing pitch --}}
        <div class="py-14 sm:py-20 grid lg:grid-cols-[1.2fr_1fr] gap-10 lg:gap-16 border-b border-paper/10">
            <div class="grid gap-5 content-start">
                <img
                    src="{{ asset('images/meshwork-lockup-light.png') }}"
                    alt="Meshwork HQ"
                    width="500"
                    height="109"
                    class="h-8 w-auto"
                />

                <p class="font-display-caps text-2xl sm:text-3xl text-paper max-w-[16ch]">
                    {{ __('Good work finds you first.') }}
                </p>

                <p class="text-sm text-paper/55 leading-relaxed max-w-[46ch]">
                    {{ __('Free to post, no commission on completed work, and naira from end to end. Built in Nigeria for the people doing the work here.') }}
                </p>
            </div>

            <div class="grid sm:grid-cols-2 gap-8 sm:gap-6">
                @foreach($columns as $heading => $items)
                    <div class="grid gap-4 content-start">
                        <p class="font-sans text-[10px] font-bold uppercase tracking-[0.1em] text-paper/40">{{ $heading }}</p>
                        <ul class="grid gap-3">
                            @foreach($items as $item)
                                <li>
                                    <a href="{{ $item['href'] }}"
                                       class="text-sm text-paper/70 hover:text-paper transition-colors">
                                        {{ $item['label'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Baseline --}}
        <div class="py-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <p class="font-sans text-[10px] font-bold uppercase tracking-[0.1em] text-paper/35">
                &copy; {{ date('Y') }} Meshwork HQ
            </p>
            <p class="font-sans text-[10px] font-bold uppercase tracking-[0.1em] text-paper/35">
                {{ __('Nigeria') }}
            </p>
        </div>
    </div>
</footer>
