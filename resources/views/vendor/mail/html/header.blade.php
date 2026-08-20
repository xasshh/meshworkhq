@props(['url'])

@php
    $logo = (string) config('mail.logo_url');

    // An inbox cannot reach a private host, so a localhost URL would render as
    // a broken image in every client. Fall back to the wordmark as text.
    $logoIsReachable = $logo !== ''
        && ! preg_match('/^https?:\/\/(localhost|127\.0\.0\.1|\[::1\])(:\d+)?(\/|$)/i', $logo);
@endphp

<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if ($logoIsReachable)
<img src="{{ $logo }}" class="logo" alt="{{ config('app.name') }}">
@else
{!! $slot !!}
@endif
</a>
</td>
</tr>
