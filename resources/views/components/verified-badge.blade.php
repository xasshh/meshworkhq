@props(['user', 'showUnverified' => false])

@php
    $status = $user?->verification_status;
@endphp

@if($user && $user->isVerified())
    <span {{ $attributes->class(['pill']) }} data-tone="live" title="{{ __('Identity confirmed by Meshwork HQ') }}">
        {{ __('Verified') }}
    </span>
@elseif($showUnverified && $status)
    <span {{ $attributes->class(['pill']) }} data-tone="{{ $status->tone() }}">
        {{ $status->label() }}
    </span>
@endif
