<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>NEUROSPEND | Financial Command Center</title>

        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Bodoni+Moda:ital,opsz,wght@0,6..96,400..900;1,6..96,400..900&family=Geist:wght@100..900&family=JetBrains+Mono:ital,wght@0,100..800;1,100..800&display=swap" rel="stylesheet">
        <!-- Google Material Symbols -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

        <style>
            :root {
                --canvas-bg: #050608;
                --surface: rgba(26, 28, 32, 0.45);
                --outline-variant: rgba(255, 255, 255, 0.08);
                --primary-fixed: #00e290;
                --primary-fixed-dim: #52ffac;
                --secondary-fixed: #00e0ff;
                --tertiary-fixed: #e9b3ff;
                
                --font-sans: 'Geist', sans-serif;
                --font-display: 'Bodoni Moda', serif;
                --font-mono: 'JetBrains Mono', monospace;
            }

            * {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }

            body {
                background-color: var(--canvas-bg);
                color: #e2e2e8;
                font-family: var(--font-sans);
                overflow-x: hidden;
                min-height: 100vh;
                display: flex;
                flex-col: column;
                justify-content: space-between;
            }

            /* Premium Ambient Background Glows */
            .bg-glow-1 {
                position: fixed;
                top: -10%;
                left: -10%;
                width: 50%;
                height: 50%;
                background: radial-gradient(circle, rgba(0, 226, 144, 0.04) 0%, transparent 70%);
                z-index: 1;
                pointer-events: none;
            }

            .bg-glow-2 {
                position: fixed;
                bottom: -10%;
                right: -10%;
                width: 60%;
                height: 60%;
                background: radial-gradient(circle, rgba(233, 179, 255, 0.03) 0%, transparent 70%);
                z-index: 1;
                pointer-events: none;
            }

            .bg-grid {
                position: fixed;
                inset: 0;
                background-image: 
                    linear-gradient(rgba(255,255,255,0.015) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(255,255,255,0.015) 1px, transparent 1px);
                background-size: 60px 60px;
                z-index: 2;
                pointer-events: none;
            }

            header {
                width: 100%;
                max-width: 1200px;
                margin: 0 auto;
                padding: 2rem;
                display: flex;
                justify-content: space-between;
                align-items: center;
                position: relative;
                z-index: 10;
            }

            .logo-wrap {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                text-decoration: none;
            }

            .logo-symbol {
                font-family: var(--font-display);
                font-size: 1.5rem;
                font-weight: 900;
                color: var(--primary-fixed);
                border: 1px solid var(--outline-variant);
                width: 38px;
                height: 38px;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .logo-text {
                font-family: var(--font-display);
                font-size: 1.25rem;
                font-weight: 300;
                letter-spacing: 0.15em;
                color: #ffffff;
                text-transform: uppercase;
            }

            .nav-actions {
                display: flex;
                gap: 1.5rem;
            }

            .nav-link {
                color: #849588;
                text-decoration: none;
                font-family: var(--font-mono);
                font-size: 10px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.25em;
                transition: color 0.3s ease;
            }

            .nav-link:hover {
                color: #ffffff;
            }

            main {
                flex: 1;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 4rem 2rem;
                position: relative;
                z-index: 10;
            }

            .hero-card {
                background: var(--surface);
                backdrop-filter: blur(20px);
                border: 1px solid var(--outline-variant);
                max-width: 800px;
                width: 100%;
                padding: 4.5rem 3.5rem;
                text-align: center;
                position: relative;
            }

            .hero-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 2px;
                background: linear-gradient(90deg, transparent, var(--primary-fixed), transparent);
            }

            .hero-badge {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.5rem 1rem;
                border: 1px solid rgba(82, 255, 172, 0.15);
                background: rgba(82, 255, 172, 0.05);
                color: var(--primary-fixed-dim);
                font-family: var(--font-mono);
                font-size: 9px;
                font-weight: 700;
                letter-spacing: 0.2em;
                text-transform: uppercase;
                margin-bottom: 2rem;
            }

            .hero-title {
                font-family: var(--font-display);
                font-size: 3.5rem;
                font-weight: 300;
                line-height: 1.15;
                color: #ffffff;
                margin-bottom: 1.5rem;
                letter-spacing: -0.02em;
            }

            .hero-title span {
                color: var(--primary-fixed-dim);
                font-style: italic;
            }

            .hero-description {
                font-size: 1rem;
                line-height: 1.6;
                color: #849588;
                max-w: 600px;
                margin: 0 auto 3rem auto;
            }

            .cta-group {
                display: flex;
                justify-content: center;
                gap: 1.5rem;
            }

            .btn {
                height: 3.25rem;
                padding: 0 2.5rem;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-family: var(--font-sans);
                font-size: 11px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.2em;
                text-decoration: none;
                transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            }

            .btn-primary {
                background-color: var(--primary-fixed);
                color: #050608;
                box-shadow: 0 0 30px rgba(0, 226, 144, 0.15);
            }

            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 0 40px rgba(0, 226, 144, 0.25);
                filter: brightness(1.1);
            }

            .btn-secondary {
                border: 1px solid var(--outline-variant);
                color: #ffffff;
                background-color: rgba(255,255,255,0.02);
            }

            .btn-secondary:hover {
                transform: translateY(-2px);
                background-color: rgba(255,255,255,0.05);
                border-color: rgba(255,255,255,0.15);
            }

            footer {
                width: 100%;
                max-width: 1200px;
                margin: 0 auto;
                padding: 2rem;
                display: flex;
                justify-content: space-between;
                align-items: center;
                color: #3a4a3f;
                font-family: var(--font-mono);
                font-size: 9px;
                text-transform: uppercase;
                letter-spacing: 0.25em;
                position: relative;
                z-index: 10;
                border-top: 1px solid rgba(255, 255, 255, 0.03);
            }

            @media (max-width: 768px) {
                .hero-title {
                    font-size: 2.25rem;
                }
                .hero-card {
                    padding: 3rem 1.5rem;
                }
                .cta-group {
                    flex-direction: column;
                    gap: 1rem;
                }
                .btn {
                    width: 100%;
                }
            }
        </style>
    </head>
    <body>
        <div class="bg-glow-1"></div>
        <div class="bg-glow-2"></div>
        <div class="bg-grid"></div>

        <header>
            <a href="/" class="logo-wrap">
                <div class="logo-symbol">N</div>
                <div class="logo-text">NeuroSpend</div>
            </a>
            <div class="nav-actions">
                @auth
                    <a href="{{ url('/dashboard') }}" class="nav-link">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="nav-link">Log In</a>
                @endauth
            </div>
        </header>

        <main>
            <div class="hero-card">
                <div class="hero-badge">
                    <span class="material-symbols-outlined" style="font-size: 14px;">terminal</span>
                    <span>Local-First Heuristic Engine</span>
                </div>
                
                <h1 class="hero-title">
                    Tactical Cash Runway & <br>
                    <span>Deterministic Wealth Intelligence</span>
                </h1>

                <p class="hero-description">
                    Optimize residual capital efficiency, schedule complex debt paydowns, and calculate daily Safe-To-Spend allowances through localized telemetry. No cloud pipelines. No hallucinations.
                </p>

                <div class="cta-group">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn btn-primary">Enter Dashboard</a>
                    @else
                        <a href="{{ route('register') }}" class="btn btn-primary">Initialize Sync Node</a>
                        <a href="{{ route('login') }}" class="btn btn-secondary">Access Console</a>
                    @endauth
                </div>
            </div>
        </main>

        <footer>
            <span>v{{ app()->version() }} // LOCAL_NODE</span>
            <span>SYSTEM READY</span>
        </footer>
    </body>
</html>
