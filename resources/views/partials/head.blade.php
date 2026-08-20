@php
    $seo = app(App\Support\Seo::class);

    $appName = config('app.name', 'Meshwork HQ');
    $pageTitle = filled($title ?? null) && $title !== $appName
        ? $title.' - '.$appName
        : $appName;

    $description = $seo->resolvedDescription();
    $canonical = $seo->resolvedCanonical();
    $ogImage = $seo->resolvedImage();
    $noindex = $seo->shouldNoindex();
@endphp

<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $pageTitle }}</title>

@if($description)
    <meta name="description" content="{{ $description }}">
@endif

{{-- One canonical per page, query string stripped, so filtered and paginated
     views do not compete with the page they came from. --}}
<link rel="canonical" href="{{ $canonical }}">

@if($noindex)
    <meta name="robots" content="noindex, nofollow">
@else
    <meta name="robots" content="index, follow, max-image-preview:large">
@endif

{{-- Social cards --}}
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ $appName }}">
<meta property="og:title" content="{{ $pageTitle }}">
@if($description)
    <meta property="og:description" content="{{ $description }}">
@endif
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:locale" content="en_NG">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $pageTitle }}">
@if($description)
    <meta name="twitter:description" content="{{ $description }}">
@endif
<meta name="twitter:image" content="{{ $ogImage }}">

<link rel="icon" href="{{ asset('favicon-32.png') }}" type="image/png" sizes="32x32">
<link rel="icon" href="{{ asset('favicon-16.png') }}" type="image/png" sizes="16x16">
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
<meta name="theme-color" content="#1E2A38">

@unless($noindex)
    <x-schema.organisation />

    @foreach($seo->schemas as $schema)
        <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endforeach
@endunless

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])

@include('partials.analytics')
