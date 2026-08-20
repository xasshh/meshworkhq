@props(['user'])

@php
    $count = $user?->publicTrackRecord();
@endphp

{{-- Nothing to show for a hidden count, and nothing to gain from advertising a
     zero, which reads as a warning rather than a neutral fact. --}}
@if($count > 0)
    <span {{ $attributes->class(['pill']) }} data-tone="live">
        @if($user->isProfessional())
            {{ trans_choice('{1}Hired :count time|[2,*]Hired :count times', $count, ['count' => $count]) }}
        @else
            {{ trans_choice('{1}:count professional pitched|[2,*]:count professionals pitched', $count, ['count' => $count]) }}
        @endif
    </span>
@endif
