{{--
  Reusable SEO + social-share head partial. Include with:
    @include('partials.seo-head', [
        'title'       => 'Page title — The Church of Peace',
        'description' => 'One-sentence summary, 140-160 chars ideal.',
        'path'        => '/the-route',
        'image'       => '/og-image.jpg',  // optional
    ])
  All params optional except title — sensible defaults below.
--}}
@php
  $base = rtrim(config('app.url', 'https://app.thechurchofpeace.org'), '/');
  $title       ??= 'The Church of Peace · Shalom SDA Church · Bronx, NY';  // leads with the brand — Google was routing "church of peace" searches to /contact because ITS title matched tighter
  $description ??= 'Shalom Seventh-day Adventist Church in the Bronx — Sabbath worship, weekly bulletin, Sabbath School lesson, sermon archive, and prayer meetings.';
  $path        ??= request()->getPathInfo();
  $url          = $base . $path;
  $image        = $base . ($image ?? '/og-default.png?v=3');  // ?v bumps force scrapers (Apple/FB cache per URL) to refetch after a redesign
@endphp

<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}">
<link rel="canonical" href="{{ $url }}">

{{-- Open Graph (Facebook, LinkedIn, iMessage) --}}
<meta property="og:type"        content="website">
<meta property="og:site_name"   content="The Church of Peace · Shalom SDA">
<meta property="og:title"       content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url"         content="{{ $url }}">
<meta property="og:image"       content="{{ $image }}">
<meta property="og:locale"      content="en_US">

{{-- Twitter / X card --}}
<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image"       content="{{ $image }}">

{{-- Crawler hints --}}
<meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large">
<meta name="theme-color" content="#03617A">
<meta name="google-site-verification" content="Blmt4l_spzs8CdR0p_7JvKL_fm6JVTuH-_BgwjS9wkI">
