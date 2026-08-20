@props(['brief'])

@php
    $wave = $brief->currentWave();
    $nextWaveAt = $brief->nextWaveAt();
    $progress = $brief->waveProgress();
@endphp

<div {{ $attributes->class(['wave-rail']) }}>
    <div class="wave-track" role="img" aria-label="{{ __('Wave :wave of 3', ['wave' => $wave]) }}">
        @for($segment = 1; $segment <= 3; $segment++)
            <span
                class="wave-seg"
                @if($segment < $wave) data-state="past" @endif
                @if($segment === $wave) data-state="live" style="--wave-progress: {{ number_format(max(0.06, $progress), 3) }}" @endif
            ></span>
        @endfor
    </div>

    <div class="wave-meta">
        <span class="wave-who">{{ __('Wave :wave', ['wave' => $wave]) }} &middot; {{ $brief->waveAudience() }} {{ __('pros') }}</span>
        <span class="wave-when">
            @if($nextWaveAt)
                {{ __('opens wider in :time', ['time' => now()->diffForHumans($nextWaveAt, ['parts' => 2, 'short' => true, 'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE])]) }}
            @else
                {{ __('open to all matches') }}
            @endif
        </span>
    </div>
</div>
