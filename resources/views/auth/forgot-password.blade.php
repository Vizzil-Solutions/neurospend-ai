<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>NEUROSPEND | Recover Node</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Bodoni+Moda:ital,wght@0,400..900;1,400..900&amp;family=JetBrains+Mono:ital,wght@0,100..800;1,100..800&amp;family=Geist:wght@100..900&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <!-- Tailwind Configuration -->
    <script id="tailwind-config">
      tailwind.config = {
        darkMode: "class",
        theme: {
          extend: {
            "colors": {
                    "tertiary-fixed": "#f6d9ff",
                    "outline-variant": "#3a4a3f",
                    "surface-dim": "#111318",
                    "primary-fixed-dim": "#00e290",
                    "tertiary": "#fffbff",
                    "surface-container-high": "#282a2e",
                    "on-secondary-fixed": "#001f25",
                    "inverse-on-surface": "#2f3035",
                    "surface-bright": "#37393e",
                    "primary-fixed": "#52ffac",
                    "on-primary-container": "#007146",
                    "error-container": "#93000a",
                    "on-tertiary-container": "#952cc8",
                    "secondary-container": "#00e0ff",
                    "on-background": "#e2e2e8",
                    "on-surface-variant": "#b9cbbd",
                    "on-surface": "#e2e2e8",
                    "outline": "#849588",
                    "on-primary": "#003920",
                    "on-secondary-fixed-variant": "#004e5a",
                    "secondary": "#b9f1ff",
                    "on-primary-fixed": "#002111",
                    "background": "#111318",
                    "on-error-container": "#ffdad6",
                    "inverse-primary": "#006d43",
                    "surface-container-low": "#1a1c20",
                    "primary-container": "#00ffa3",
                    "surface-container-lowest": "#0c0e12",
                    "secondary-fixed-dim": "#00daf8",
                    "on-tertiary-fixed-variant": "#7200a3",
                    "surface": "#111318",
                    "primary": "#f5fff5",
                    "secondary-fixed": "#a5eeff",
                    "tertiary-container": "#f5d5ff",
                    "on-error": "#690005",
                    "surface-container": "#1e2024",
                    "surface-tint": "#00e290",
                    "inverse-surface": "#e2e2e8",
                    "tertiary-fixed-dim": "#e9b3ff",
                    "surface-variant": "#333539",
                    "on-tertiary": "#510074",
                    "on-primary-fixed-variant": "#005231",
                    "surface-container-highest": "#333539",
                    "on-secondary": "#00363f",
                    "on-secondary-container": "#005f6d",
                    "error": "#ffb4ab",
                    "on-tertiary-fixed": "#310048"
            },
            "borderRadius": {
                    "DEFAULT": "0.125rem",
                    "lg": "0.25rem",
                    "xl": "0.5rem",
                    "full": "0.75rem"
            },
            "spacing": {
                    "gutter": "16px",
                    "container-padding": "24px",
                    "section-gap": "48px",
                    "stack-md": "16px",
                    "stack-sm": "8px",
                    "unit": "4px"
            },
            "fontFamily": {
                    "display-lg": ["Bodoni Moda"],
                    "headline-lg": ["Bodoni Moda"],
                    "label-caps": ["JetBrains Mono"],
                    "headline-lg-mobile": ["Bodoni Moda"],
                    "data-mono": ["JetBrains Mono"],
                    "body-md": ["Geist"],
                    "title-md": ["Geist"]
            },
            "fontSize": {
                    "display-lg": ["48px", {"lineHeight": "1.1", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                    "headline-lg": ["32px", {"lineHeight": "1.2", "fontWeight": "600"}],
                    "label-caps": ["11px", {"lineHeight": "1", "letterSpacing": "0.1em", "fontWeight": "700"}],
                    "headline-lg-mobile": ["28px", {"lineHeight": "1.2", "fontWeight": "600"}],
                    "data-mono": ["14px", {"lineHeight": "1.4", "letterSpacing": "0.05em", "fontWeight": "500"}],
                    "body-md": ["16px", {"lineHeight": "1.6", "fontWeight": "400"}],
                    "title-md": ["20px", {"lineHeight": "1.4", "letterSpacing": "0.01em", "fontWeight": "600"}]
            }
          },
        },
      }
    </script>
    <style>
        body {
            background-color: #050608;
            color: #e2e2e8;
            overflow: hidden;
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .input-focus-ring:focus {
            outline: none;
            box-shadow: 0 0 0 2px #00e290;
            border-color: #00e290;
        }

        .active-glow {
            box-shadow: 0 0 20px rgba(0, 226, 144, 0.2);
        }

        .shimmer-bg {
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.03), transparent);
            background-size: 200% 100%;
            animation: shimmer 5s infinite linear;
        }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        .neural-pulse {
            position: absolute;
            width: 100%;
            height: 100%;
            background-image: radial-gradient(circle at center, #00e29005 0%, transparent 70%);
            animation: pulse 8s infinite alternate ease-in-out;
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 0.3; }
            100% { transform: scale(1.5); opacity: 0.8; }
        }
        
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="font-body-md selection:bg-primary-fixed-dim selection:text-on-primary-fixed">
<main class="flex h-screen w-full flex-col md:flex-row overflow-hidden relative">
    <!-- Background Animation/Atmosphere -->
    <div class="absolute inset-0 pointer-events-none z-0">
        <div class="neural-pulse"></div>
        <div class="absolute top-0 right-0 w-1/2 h-full bg-gradient-to-l from-primary-fixed-dim/5 to-transparent"></div>
    </div>
    
    <!-- Left Column: Branding & Ethos -->
    <section class="relative w-full md:w-3/5 lg:w-2/3 h-1/3 md:h-full flex items-center justify-center p-container-padding overflow-hidden border-b md:border-b-0 md:border-r border-outline-variant/20 z-10">
        <div class="max-w-xl w-full flex flex-col items-center md:items-start space-y-stack-md text-center md:text-left">
            <!-- Brand Anchor -->
            <div class="relative group">
                <div class="absolute -inset-8 bg-primary-fixed-dim/10 blur-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-1000"></div>
                <div class="w-32 h-32 md:w-48 md:h-48 relative z-10 transition-transform duration-700 group-hover:scale-105">
                    <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                      <defs>
                        <linearGradient id="logo-grad" x1="0%" y1="0%" x2="100%" y2="100%">
                          <stop offset="0%" style="stop-color:#00e290;stop-opacity:1" />
                          <stop offset="100%" style="stop-color:#00e0ff;stop-opacity:1" />
                        </linearGradient>
                      </defs>
                      <path d="M20 80V20L40 50L60 20V80" stroke="url(#logo-grad)" stroke-width="8" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                      <circle cx="20" cy="20" r="4" fill="#00e290"/>
                      <circle cx="40" cy="50" r="4" fill="#00e0ff"/>
                      <circle cx="60" cy="20" r="4" fill="#00e290"/>
                      <path d="M60 80Q75 80 80 65" stroke="#b9cbbd" stroke-width="4" fill="none" stroke-linecap="round"/>
                    </svg>
                </div>
            </div>
            
            <!-- Typography block -->
            <div class="space-y-stack-sm">
                <p class="font-label-caps text-label-caps text-primary-fixed-dim tracking-[0.25em]">SECURE INSTANCE TERMINAL</p>
                <h1 class="font-display-lg text-[40px] md:text-display-lg text-primary tracking-tight font-light leading-none">
                    Neuro<span class="font-bold text-primary-fixed">Spend</span>
                </h1>
                <p class="font-body-md text-on-surface-variant max-w-md leading-relaxed text-[15px] md:text-body-md font-light">
                    Recover master key access. Recalibrate and restore connectivity to your local neural ledger nodes.
                </p>
            </div>
            
            <!-- Live Data stream / telemetry simulation -->
            <div class="w-full max-w-sm hidden md:flex items-center space-x-stack-sm bg-surface-container-lowest/60 border border-outline-variant/10 px-4 py-3 rounded-none relative overflow-hidden">
                <div class="shimmer-bg absolute inset-0"></div>
                <div class="relative z-10 flex items-center space-x-3 w-full">
                    <span class="w-2 h-2 rounded-full bg-primary-fixed-dim animate-ping shrink-0"></span>
                    <span class="font-label-caps text-[9px] text-primary-fixed tracking-wider uppercase">Telemetry Stream:</span>
                    <span id="log-stream" class="font-data-mono text-[11px] text-on-surface-variant truncate transition-opacity duration-300">Awaiting master key recovery command...</span>
                </div>
            </div>
        </div>
        
        <!-- Subtle Corner Lines -->
        <div class="absolute top-0 left-0 w-8 h-8 border-t border-l border-white/5"></div>
        <div class="absolute bottom-0 right-0 w-8 h-8 border-b border-r border-white/5"></div>
    </section>
    
    <!-- Right Column: Command Center Console (Forms) -->
    <section class="w-full md:w-2/5 lg:w-1/3 h-2/3 md:h-full bg-surface-dim relative z-10 flex flex-col justify-between p-container-padding border-t md:border-t-0 md:border-l border-outline-variant/20 overflow-y-auto">
        <div class="my-auto space-y-stack-md">
            <!-- Form Header -->
            <header class="space-y-stack-sm">
                <h2 class="font-headline-lg text-headline-lg text-primary tracking-tight">RECOVER NODE</h2>
                <p class="font-body-md text-on-surface-variant text-[14px]">TRANSMIT MASTER KEY RECOVERY SIGNAL</p>
            </header>

            <!-- Alerts Display -->
            @if($errors->any())
                <div class="bg-error-container/20 border border-error/30 text-error p-4 rounded-none space-y-1 animate-pulse">
                    <div class="flex items-center space-x-2">
                        <span class="material-symbols-outlined text-[18px]">report</span>
                        <span class="font-label-caps text-[11px] tracking-wide">RECOVERY EXCEPTION</span>
                    </div>
                    <p class="text-xs text-on-surface-variant">{{ $errors->first() }}</p>
                </div>
            @endif

            @if(session('status'))
                <div class="bg-primary-fixed-dim/10 border border-primary-fixed-dim/30 text-primary-fixed-dim p-4 rounded-none space-y-1">
                    <div class="flex items-center space-x-2">
                        <span class="material-symbols-outlined text-[18px]">verified</span>
                        <span class="font-label-caps text-[11px] tracking-wide">SIGNAL TRANSMITTED</span>
                    </div>
                    <p class="text-xs text-on-surface-variant">{{ session('status') }}</p>
                </div>
            @endif

            <form method="POST" action="/auth/forgot-password" class="space-y-stack-md">
                @csrf
                <!-- Email Field -->
                <div class="space-y-unit">
                    <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">IDENTIFIER (EMAIL)</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">alternate_email</span>
                        <input id="email" name="email" class="w-full h-14 bg-surface-container-lowest border border-outline-variant/30 px-12 font-data-mono text-data-mono text-primary rounded-none transition-all input-focus-ring" placeholder="operator@neurospend.ai" type="email" value="{{ old('email') }}" required autofocus/>
                    </div>
                </div>

                <!-- CTA -->
                <button type="submit" class="w-full h-16 bg-primary-fixed text-on-primary-fixed font-label-caps text-[13px] tracking-widest uppercase mt-gutter active:scale-[0.98] transition-all relative overflow-hidden group shadow-[0_0_30px_rgba(82,255,172,0.1)] hover:shadow-[0_0_40px_rgba(82,255,172,0.2)]">
                    <span class="relative z-10 flex items-center justify-center space-x-stack-sm">
                        <span>Transmit Recovery Signal</span>
                        <span class="material-symbols-outlined text-[18px]">send</span>
                    </span>
                    <div class="absolute inset-0 bg-white/20 -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                </button>
            </form>
            
            <footer class="pt-section-gap flex flex-col items-center space-y-gutter">
                <p class="text-on-surface-variant font-label-caps text-[10px] opacity-70 uppercase tracking-widest">
                    Remember credentials? <a href="/auth/login" class="text-primary-fixed-dim hover:underline font-bold">Authenticate Node</a>
                </p>
                <div class="flex space-x-gutter">
                    <div class="w-1.5 h-1.5 bg-primary-fixed-dim animate-pulse"></div>
                    <div class="w-1.5 h-1.5 bg-primary-fixed-dim opacity-50"></div>
                    <div class="w-1.5 h-1.5 bg-primary-fixed-dim opacity-25"></div>
                </div>
            </footer>
        </div>
        
        <!-- Corner Accents -->
        <div class="absolute top-0 right-0 w-16 h-16 border-t border-r border-primary-fixed/20"></div>
        <div class="absolute bottom-0 left-0 w-16 h-16 border-b border-l border-primary-fixed/20"></div>
    </section>
</main>

<script>
    // Atmospheric Data Stream Simulation
    const logs = [
        "Awaiting target key identity...",
        "Validating network channels...",
        "Encoding secure telemetry packet...",
        "Transmitting recovery sequence..."
    ];
    let logIndex = 0;
    const logElement = document.getElementById('log-stream');
    
    if (logElement) {
        setInterval(() => {
            logIndex = (logIndex + 1) % logs.length;
            logElement.style.opacity = '0';
            setTimeout(() => {
                logElement.textContent = logs[logIndex];
                logElement.style.opacity = '1';
            }, 300);
        }, 4000);
    }

    // Input Focus Visual Enhancement
    const inputs = document.querySelectorAll('input');
    inputs.forEach(input => {
        if (input.type !== 'checkbox') {
            input.addEventListener('focus', () => {
                input.parentElement.parentElement.classList.add('active-glow');
            });
            input.addEventListener('blur', () => {
                input.parentElement.parentElement.classList.remove('active-glow');
            });
        }
    });
</script>
</body>
</html>
