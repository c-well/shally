<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png?v=2">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png?v=2">
    <link rel="shortcut icon" href="/favicon.ico?v=2">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Shalom SDA') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=cormorant-garamond:400,500,600,700&family=instrument-sans:500,600&family=poppins:400,500,600&family=jetbrains-mono:400,500&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            @font-face {
                font-family: 'Xtreem';
                src: url('/fonts/XtreemMedium.ttf') format('truetype');
                font-weight: 500; font-style: normal; font-display: swap;
            }
            html, body {
                background: #fefcef;
                font-family: 'Poppins', sans-serif;
                color: #1a2332;
                -webkit-font-smoothing: antialiased;
                -moz-osx-font-smoothing: grayscale;
            }
            .auth-brand {
                display: inline-flex; align-items: center; justify-content: center;
                gap: 8px;
                font-family: 'Instrument Sans', ui-sans-serif, sans-serif;
                font-size: 16px; font-weight: 600;
                letter-spacing: 0.08em; line-height: 1;
                color: #1a2332; text-decoration: none;
                text-transform: uppercase; white-space: nowrap;
            }
            .auth-brand em {
                font-family: 'Xtreem', 'Cormorant Garamond', serif;
                font-style: normal; font-weight: 500;
                color: #03617A; text-transform: lowercase;
                font-size: 56px; letter-spacing: -0.02em; line-height: 0.8;
                display: inline-block; padding-right: 8px;
                transform: translateY(2px);
            }
            .auth-tagline {
                font-family: 'JetBrains Mono', monospace;
                font-size: 10px; letter-spacing: 0.3em;
                text-transform: uppercase; color: #03617A;
                margin-top: 12px; text-align: center;
            }
            .auth-card {
                border: 1px solid color-mix(in srgb, var(--ink) 15%, transparent);
            }
            .auth-footer {
                font-family: 'JetBrains Mono', monospace;
                font-size: 10px; letter-spacing: 0.2em;
                text-transform: uppercase; color: #3d4a5f;
                margin-top: 24px;
            }
        </style>
    </head>
    <body class="antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            <div class="mb-6 text-center">
                <a href="/" class="auth-brand"><em>Shalom</em> SDA</a>
                <div class="auth-tagline">Seventh-day Adventist · Bronx, NY</div>
            </div>
            <div class="auth-card w-full sm:max-w-md px-6 py-8 bg-white overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
            <p class="auth-footer">Church of Peace · Staff &amp; member access</p>
        </div>
    </body>
</html>
