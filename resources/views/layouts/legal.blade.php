<!-- resources/views/layouts/legal.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('description')">
    <title>@yield('title') - {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600|space-grotesk:600,700|jetbrains-mono:500,600&display=swap" rel="stylesheet" />

    <style>
        :root {
            --ink: #0e0e1a;
            --ink-mute: #63636f;
            --paper: #fafaf8;
            --paper-dim: #f1f0ec;
            --line: #e4e2dc;
            --indigo: #4f46e5;
            --indigo-deep: #3730a3;
            --indigo-soft: #eef0ff;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Figtree', -apple-system, BlinkMacSystemFont, sans-serif;
            line-height: 1.65;
            color: var(--ink);
            margin: 0;
            background-color: var(--paper);
        }

        h1, h2, h3 {
            font-family: 'Space Grotesk', 'Figtree', sans-serif;
            letter-spacing: -0.01em;
            color: var(--ink);
        }

        a { color: var(--indigo); }

        /* Nav */
        .nav {
            border-bottom: 1px solid var(--line);
            background: rgba(250, 250, 248, 0.9);
        }

        .nav-inner {
            max-width: 1180px;
            margin: 0 auto;
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            text-decoration: none;
        }

        .brand-mark {
            height: 2.1rem;
            width: 2.1rem;
            background: var(--indigo);
            color: white;
            border-radius: 8px;
            padding: 0.4rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .brand-name {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--ink);
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            font-size: 0.9rem;
            color: var(--ink-mute);
        }

        .nav-links a {
            color: inherit;
            text-decoration: none;
            transition: color 0.15s ease;
        }

        .nav-links a:hover {
            color: var(--indigo);
        }

        .nav-cta {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--ink);
            text-decoration: none;
            border: 1px solid var(--line);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            white-space: nowrap;
            transition: border-color 0.15s ease, background-color 0.15s ease;
        }

        .nav-cta:hover {
            border-color: var(--indigo);
            background: var(--indigo-soft);
            color: var(--indigo);
        }

        /* Layout */
        .legal-page {
            max-width: 1180px;
            margin: 0 auto;
            padding: 3.5rem 2rem 6rem;
            display: grid;
            grid-template-columns: 15rem 1fr;
            gap: 4rem;
            align-items: start;
        }

        .toc {
            position: sticky;
            top: 2rem;
        }

        .eyebrow {
            font-family: 'JetBrains Mono', ui-monospace, monospace;
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.12em;
            color: var(--indigo);
            margin: 0 0 1rem;
        }

        .toc ol {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
        }

        .toc a {
            font-size: 0.875rem;
            color: var(--ink-mute);
            text-decoration: none;
            display: block;
        }

        .toc a:hover {
            color: var(--indigo);
        }

        .legal-content h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0 0 0.5rem;
            line-height: 1.1;
        }

        .updated-date {
            color: var(--ink-mute);
            font-size: 0.9rem;
            margin-bottom: 2.5rem;
            font-family: 'JetBrains Mono', monospace;
        }

        .legal-content section {
            padding: 2rem 0;
            border-top: 1px solid var(--line);
            scroll-margin-top: 2rem;
        }

        .legal-content section:first-of-type {
            border-top: none;
            padding-top: 0;
        }

        .legal-content h2 {
            font-size: 1.35rem;
            font-weight: 700;
            margin: 0 0 1rem;
            display: flex;
            align-items: baseline;
            gap: 0.6rem;
        }

        .legal-content h2 .num {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--indigo);
        }

        .legal-content h3 {
            font-size: 1rem;
            font-weight: 700;
            margin: 1.5rem 0 0.5rem;
        }

        .legal-content p, .legal-content li {
            color: #3d3d47;
            font-size: 0.98rem;
        }

        .legal-content ul {
            padding-left: 1.25rem;
        }

        .legal-content li {
            margin-bottom: 0.5rem;
        }

        .callout {
            background: var(--indigo-soft);
            border-radius: 10px;
            padding: 1.1rem 1.25rem;
            font-size: 0.92rem;
            color: var(--indigo-deep);
            margin: 1rem 0;
        }

        .contact-email {
            font-family: 'JetBrains Mono', monospace;
            font-size: 1.05rem;
        }

        .footer {
            border-top: 1px solid var(--line);
        }

        .footer-inner {
            max-width: 1180px;
            margin: 0 auto;
            padding: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .footer-inner p {
            margin: 0;
            color: var(--ink-mute);
        }

        .footer-links {
            display: flex;
            gap: 1.5rem;
        }

        .footer-links a {
            color: var(--ink-mute);
            text-decoration: none;
        }

        .footer-links a:hover {
            color: var(--indigo);
        }

        @media (max-width: 560px) {
            .footer-inner {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
        }

        @media (max-width: 860px) {
            .legal-page {
                grid-template-columns: 1fr;
                padding: 2.5rem 1.5rem 4rem;
                gap: 2rem;
            }

            .toc {
                position: static;
                border: 1px solid var(--line);
                border-radius: 12px;
                padding: 1.25rem;
                background: white;
            }

            .legal-content h1 { font-size: 2rem; }

            .nav-links { display: none; }
        }
    </style>
</head>
<body>
    <header class="nav">
        <div class="nav-inner">
            <a href="{{ url('/') }}" class="brand">
                <span class="brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8 2V5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M16 2V5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M3.5 9.09H20.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M21 8.5V17C21 20 19.5 22 16 22H8C4.5 22 3 20 3 17V8.5C3 5.5 4.5 3.5 8 3.5H16C19.5 3.5 21 5.5 21 8.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span class="brand-name">Synqs</span>
            </a>

            <nav class="nav-links" aria-label="Page sections">
                <a href="{{ url('/') }}#features">Features</a>
                <a href="{{ url('/') }}#how-it-works">How it works</a>
                <a href="{{ url('/') }}#communities">Communities</a>
                <a href="{{ route('docs') }}">Docs</a>
            </nav>

            <a href="/auth/google" class="nav-cta">Sign in with Google</a>
        </div>
    </header>

    <div class="legal-page">
        <aside class="toc">
            <p class="eyebrow">ON THIS PAGE</p>
            <ol>
                @yield('toc')
            </ol>
        </aside>

        <main class="legal-content">
            <h1>@yield('title')</h1>
            <p class="updated-date">Last updated: @yield('updated', 'July 29, 2026')</p>

            @yield('content')
        </main>
    </div>

    <footer class="footer">
        <div class="footer-inner">
            <p>&copy; {{ date('Y') }} Synqs</p>
            <div class="footer-links">
                <a href="{{ route('docs') }}">Docs</a>
                <a href="{{ route('privacy-policy') }}">Privacy Policy</a>
                <a href="{{ route('terms-of-service') }}">Terms of Service</a>
            </div>
        </div>
    </footer>
</body>
</html>
