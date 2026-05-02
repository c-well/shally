<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $bulletin->title }} — Shalom SDA</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=cormorant-garamond:400,500,600,700&family=poppins:400,500,600&family=jetbrains-mono:400,500&display=swap" rel="stylesheet" />
    <style>
        :root {
            --parchment: #fefcef;
            --ink: #1a2332;
            --ink-soft: #3d4a5f;
            --brass: #b08d3c;
            --teal: #03617A;
            --teal-dark: #024357;
            --line: rgba(26, 35, 50, 0.12);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { background: var(--parchment); }
        body {
            font-family: 'Poppins', ui-sans-serif, system-ui, sans-serif;
            color: var(--ink);
            font-size: 16px;
            line-height: 1.65;
            /* Sharp type rendering */
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
            font-feature-settings: "kern" 1, "liga" 1, "calt" 1;
            font-kerning: normal;
        }
        /* Display headings — tighter tracking, OpenType on */
        h1, h2, h3, h4, .brand, .card h1, .bulletin-body h2, .bulletin-body h3 {
            font-feature-settings: "kern" 1, "liga" 1, "dlig" 1;
            text-rendering: geometricPrecision;
        }
        a { color: inherit; }

        /* Top bar */
        .topbar {
            background: var(--parchment);
            border-bottom: 1px solid var(--line);
            position: sticky; top: 0; z-index: 10;
            backdrop-filter: blur(6px);
        }
        .topbar .inner {
            max-width: 1080px;
            margin: 0 auto;
            padding: 22px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }
        .brand {
            font-family: 'Cormorant Garamond', serif;
            font-size: 22px;
            font-weight: 500;
            line-height: 1;
            letter-spacing: -0.01em;
            text-decoration: none;
            color: var(--ink);
        }
        .brand em { font-style: normal; color: var(--teal); font-weight: 500; }
        .sublogo {
            font-family: 'JetBrains Mono', monospace;
            font-size: 9px;
            letter-spacing: 0.25em;
            color: var(--ink-soft);
            margin-top: 4px;
            text-transform: uppercase;
        }
        .actions { display: flex; gap: 10px; }
        .btn {
            font-family: 'JetBrains Mono', monospace;
            font-size: 10px;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            padding: 10px 18px;
            border-radius: 2px;
            text-decoration: none;
            white-space: nowrap;
            cursor: pointer;
            transition: all 0.2s;
            background: transparent;
            border: 1px solid var(--line);
            color: var(--ink-soft);
        }
        .btn:hover { color: var(--teal); border-color: var(--teal); }
        .btn.primary {
            background: var(--teal);
            color: #ffffff;
            border-color: var(--teal);
        }
        .btn.primary:hover {
            background: var(--teal-dark);
            border-color: var(--teal-dark);
            color: #ffffff;
        }

        /* Main */
        main { max-width: 1080px; margin: 0 auto; padding: 56px 32px 80px; }

        /* White card */
        .card {
            background: #ffffff;
            border: 1px solid var(--line);
            border-radius: 4px;
            padding: 72px 88px;
            box-shadow: 0 1px 2px rgba(26,35,50,0.04), 0 4px 24px -12px rgba(26,35,50,0.08);
        }
        .card header { margin-bottom: 36px; padding-bottom: 28px; border-bottom: 1px solid var(--line); }
        .card h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: clamp(2.25rem, 4.5vw, 3.5rem);
            font-weight: 500;
            line-height: 1.05;
            letter-spacing: -0.03em;
            color: var(--ink);
        }
        .service-date {
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            letter-spacing: 0.25em;
            color: var(--ink-soft);
            text-transform: uppercase;
            margin-top: 16px;
        }
        .draft-flag {
            display: inline-block;
            font-family: 'JetBrains Mono', monospace;
            font-size: 9px;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--brass);
            background: rgba(176, 141, 60, 0.12);
            padding: 4px 10px;
            border-radius: 20px;
            margin-top: 14px;
        }

        .bulletin-body { font-size: 17px; line-height: 1.75; color: var(--ink); max-width: 760px; }
        .bulletin-body p { margin: 16px 0; }
        .bulletin-body h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 28px;
            font-weight: 500;
            color: var(--teal-dark);
            margin: 44px 0 14px;
            letter-spacing: -0.02em;
        }
        .bulletin-body h3 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 22px;
            font-weight: 500;
            color: var(--ink);
            margin: 28px 0 10px;
            letter-spacing: -0.015em;
        }
        .bulletin-body ul, .bulletin-body ol { margin: 16px 0 16px 26px; }
        .bulletin-body ul { list-style: disc; }
        .bulletin-body ol { list-style: decimal; }
        .bulletin-body li { margin: 8px 0; }
        .bulletin-body strong { font-weight: 600; }
        .bulletin-body a { color: var(--teal); text-decoration: underline; text-underline-offset: 3px; }
        /* Pull quote — clean sans matching the body, quiet weight, generous space */
        .bulletin-body blockquote {
            border: 0;
            padding: 4px 0 4px 40px;
            margin: 48px 0;
            color: var(--ink);
            font-family: 'Poppins', sans-serif;
            font-weight: 400;
            font-size: 20px;
            line-height: 1.6;
            letter-spacing: -0.005em;
        }
        .bulletin-body blockquote p { margin: 0; }
        .bulletin-body blockquote cite {
            display: block;
            margin-top: 14px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 9px;
            letter-spacing: 0.28em;
            text-transform: uppercase;
            color: rgba(3, 97, 122, 0.72);
            font-style: normal;
        }
        .bulletin-body img { max-width: 100%; height: auto; border-radius: 4px; margin: 16px 0; }

        /* Footer actions */
        .footer-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-top: 48px;
            padding-top: 32px;
            border-top: 1px solid var(--line);
        }
        .footer-actions .left { font-family: 'JetBrains Mono', monospace; font-size: 10px; letter-spacing: 0.2em; color: var(--ink-soft); text-transform: uppercase; }

        footer.site {
            padding: 24px;
            text-align: center;
            font-family: 'JetBrains Mono', monospace;
            font-size: 10px;
            letter-spacing: 0.2em;
            color: var(--ink-soft);
            text-transform: uppercase;
            border-top: 1px solid var(--line);
        }

        @media (max-width: 1000px) {
            .topbar .inner, main { padding-left: 24px; padding-right: 24px; }
            .card { padding: 56px 56px; }
        }
        @media (max-width: 700px) {
            .card { padding: 44px 36px; }
        }
        @media (max-width: 600px) {
            body { font-size: 15px; }
            .topbar .inner { padding: 16px 20px; gap: 8px; }
            .brand { font-size: 19px; }
            .btn { padding: 8px 14px; font-size: 9px; }
            .actions { gap: 6px; }
            main { padding: 32px 20px 56px; }
            .card { padding: 32px 24px; border-radius: 4px; }
            .bulletin-body { font-size: 16px; }
            .bulletin-body h2 { font-size: 24px; margin: 32px 0 10px; }
            .bulletin-body blockquote { font-size: 18px; padding-left: 18px; margin: 22px 0; }
            .footer-actions { flex-direction: column; align-items: stretch; }
            .footer-actions .left { text-align: center; }
        }

        @media print {
            .no-print { display: none !important; }
            html, body { background: #ffffff !important; }
            main { padding: 0; max-width: 100%; }
            .card { box-shadow: none !important; border: none !important; padding: 0 !important; }
            .bulletin-body { font-size: 11pt; }
        }
    </style>
</head>
<body>

@include('partials.site-menu')

<main>
    <article class="card">
        <header>
            <h1>{{ $bulletin->title }}</h1>
            <div class="service-date">{{ $bulletin->service_date->format('l, F j, Y') }}</div>
            @unless ($bulletin->is_published) <div class="draft-flag">● Draft</div> @endunless
        </header>

        <div class="bulletin-body">
            {!! $bulletin->body !!}
        </div>

        <div class="footer-actions no-print">
            <div class="left">Shalom SDA · {{ $bulletin->service_date->format('F j, Y') }}</div>
            <div class="actions">
                <a href="/" class="btn">Back</a>
                <button onclick="window.print()" class="btn primary">Print</button>
            </div>
        </div>
    </article>
</main>

<footer class="site no-print">
    &copy; {{ date('Y') }} Shalom SDA · Church of Peace
</footer>

</body>
</html>
