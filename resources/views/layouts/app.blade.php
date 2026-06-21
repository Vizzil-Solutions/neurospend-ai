<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>@yield('title', 'NEUROSPEND | Command Center')</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Bodoni+Moda:ital,wght@0,400..900;1,400..900&amp;family=Geist:wght@100..900&amp;family=JetBrains+Mono:ital,wght@0,100..800;1,100..800&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <style>
        body {
            background-color: #050608;
            color: #e2e2e8;
            -webkit-font-smoothing: antialiased;
        }
        .glass-panel {
            background: rgba(26, 28, 32, 0.4);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .edge-light-primary {
            border: 1px solid rgba(82, 255, 172, 0.2);
        }
        .glow-primary {
            box-shadow: 0 0 20px rgba(82, 255, 172, 0.1);
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(82, 255, 172, 0.2);
            border-radius: 10px;
        }
        .neural-pulse {
            position: absolute;
            width: 100%;
            height: 100%;
            background-image: radial-gradient(circle at center, #00e29003 0%, transparent 70%);
            animation: pulse 8s infinite alternate ease-in-out;
        }
        @keyframes pulse {
            0% { transform: scale(1); opacity: 0.3; }
            100% { transform: scale(1.5); opacity: 0.8; }
        }
        /* Mobile Sidebar Drawer */
        #appSidebar.open {
            transform: translateX(0);
        }
    </style>
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
                        "data-mono": ["JetBrains Mono"],
                        "body-md": ["Geist"],
                        "title-md": ["Geist"]
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-background text-on-background min-h-screen relative flex flex-col font-body-md custom-scrollbar selection:bg-primary-fixed-dim selection:text-on-primary-fixed">
    <!-- Atmospheric Ambient Pulse -->
    <div class="absolute inset-0 pointer-events-none z-0 overflow-hidden">
        <div class="neural-pulse"></div>
    </div>

    <!-- Mobile Overlay -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-black/60 z-40 hidden backdrop-blur-sm transition-opacity duration-300"></div>

    @php
        if (!function_exists('format_currency')) {
            function format_currency($amount, $currency = 'USD') {
                $symbols = [
                    'USD' => '$', 'EUR' => '€', 'GBP' => '£', 'PKR' => '₨', 'INR' => '₹', 
                    'JPY' => '¥', 'AUD' => 'A$', 'CAD' => 'C$', 'AED' => 'د.إ', 'SGD' => 'S$'
                ];
                $sym = $symbols[$currency] ?? '$';
                if ($currency === 'AED') {
                    return number_format($amount, 2) . ' ' . $sym;
                }
                return $sym . number_format($amount, 2);
            }
        }

        if (!function_exists('currency_symbol')) {
            function currency_symbol($currency = 'USD') {
                $symbols = [
                    'USD' => '$', 'EUR' => '€', 'GBP' => '£', 'PKR' => '₨', 'INR' => '₹', 
                    'JPY' => '¥', 'AUD' => 'A$', 'CAD' => 'C$', 'AED' => 'د.إ', 'SGD' => 'S$'
                ];
                return $symbols[$currency] ?? '$';
            }
        }

        if (!function_exists('format_date')) {
            function format_date($dateStr) {
                if (!$dateStr) return 'N/A';
                try {
                    return \Carbon\Carbon::parse($dateStr)->format('M d, Y');
                } catch (\Exception $e) {
                    return $dateStr;
                }
            }
        }

        $navGroups = [
            [
                'label' => 'Command Center',
                'items' => [
                    ['name' => 'Dashboard', 'href' => '/', 'icon' => 'dashboard'],
                    ['name' => 'Accounts', 'href' => '/accounts', 'icon' => 'account_balance_wallet'],
                    ['name' => 'Ledger', 'href' => '/transactions', 'icon' => 'receipt_long'],
                ]
            ],
            [
                'label' => 'Strategy Engine',
                'items' => [
                    ['name' => 'Survival Mode', 'href' => '/survival', 'icon' => 'shield'],
                    ['name' => 'Debt Snowball', 'href' => '/debt', 'icon' => 'credit_card'],
                    ['name' => 'Recurring Bills', 'href' => '/bills', 'icon' => 'receipt'],
                ]
            ],
            [
                'label' => 'NOVA Intelligence',
                'items' => [
                    ['name' => 'Analysis Node', 'href' => '/analysis', 'icon' => 'pie_chart'],
                    ['name' => 'Neural Coach', 'href' => '/coach', 'icon' => 'psychology'],
                ]
            ],
            [
                'label' => 'System Node',
                'items' => [
                    ['name' => 'Data Sync', 'href' => '/import', 'icon' => 'database'],
                    ['name' => 'Configuration', 'href' => '/settings', 'icon' => 'settings'],
                ]
            ]
        ];
        $currentPath = Request::path() === '/' ? '/' : '/' . Request::path();
    @endphp

    <!-- Top AppBar -->
    <header class="flex justify-between items-center px-container-padding h-20 w-full fixed top-0 z-50 bg-surface-dim/80 backdrop-blur-xl border-b border-white/10">
        <div class="flex items-center gap-4">
            <!-- Mobile Menu Toggle -->
            <button class="md:hidden flex items-center justify-center p-2 text-on-surface-variant hover:text-primary-fixed-dim" id="openSidebarBtn">
                <span class="material-symbols-outlined">menu</span>
            </button>
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 shrink-0">
                    <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                      <defs>
                        <linearGradient id="logo-grad-small" x1="0%" y1="0%" x2="100%" y2="100%">
                          <stop offset="0%" style="stop-color:#00e290;stop-opacity:1" />
                          <stop offset="100%" style="stop-color:#00e0ff;stop-opacity:1" />
                        </linearGradient>
                      </defs>
                      <path d="M20 80V20L40 50L60 20V80" stroke="url(#logo-grad-small)" stroke-width="10" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                      <circle cx="20" cy="20" r="5" fill="#00e290"/>
                      <circle cx="40" cy="50" r="5" fill="#00e0ff"/>
                      <circle cx="60" cy="20" r="5" fill="#00e290"/>
                    </svg>
                </div>
                <h1 class="font-label-caps text-label-caps tracking-widest text-primary-fixed hidden sm:inline-block">NEUROSPEND</h1>
            </div>
        </div>

        <div class="flex items-center gap-6">
            <!-- Quick Actions Dropdown -->
            <div class="relative group">
                <button class="w-10 h-10 rounded-full bg-primary-fixed/10 border border-primary-fixed/20 text-primary-fixed flex items-center justify-center hover:bg-primary-fixed/20 transition-colors cursor-pointer" title="Quick Actions">
                    <span class="material-symbols-outlined text-[20px]">add</span>
                </button>
                <div class="absolute right-0 top-full mt-2 w-56 bg-surface-container-high border border-outline-variant/30 rounded-md shadow-2xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 py-2 z-50">
                    <div class="px-4 py-2 border-b border-white/5 mb-1">
                        <span class="font-label-caps text-[10px] text-outline tracking-wider uppercase">Quick Actions</span>
                    </div>
                    <a href="/transactions" class="flex items-center gap-3 px-4 py-2 hover:bg-surface-variant transition-colors text-on-surface">
                        <span class="material-symbols-outlined text-[16px] text-primary-fixed-dim">add_circle</span>
                        <span class="font-label-caps text-[11px] uppercase tracking-wider">Add Income</span>
                    </a>
                    <a href="/transactions" class="flex items-center gap-3 px-4 py-2 hover:bg-surface-variant transition-colors text-on-surface">
                        <span class="material-symbols-outlined text-[16px] text-error">remove_circle</span>
                        <span class="font-label-caps text-[11px] uppercase tracking-wider">Add Expense</span>
                    </a>
                    <a href="/bills" class="flex items-center gap-3 px-4 py-2 hover:bg-surface-variant transition-colors text-on-surface">
                        <span class="material-symbols-outlined text-[16px] text-secondary">receipt</span>
                        <span class="font-label-caps text-[11px] uppercase tracking-wider">Add Obligation</span>
                    </a>
                    <a href="/debt" class="flex items-center gap-3 px-4 py-2 hover:bg-surface-variant transition-colors text-on-surface">
                        <span class="material-symbols-outlined text-[16px] text-error-container">credit_card</span>
                        <span class="font-label-caps text-[11px] uppercase tracking-wider">Add Debt</span>
                    </a>
                    <a href="/accounts" class="flex items-center gap-3 px-4 py-2 hover:bg-surface-variant transition-colors text-on-surface">
                        <span class="material-symbols-outlined text-[16px] text-tertiary-fixed">account_balance_wallet</span>
                        <span class="font-label-caps text-[11px] uppercase tracking-wider">Add Account</span>
                    </a>
                    <a href="/survival" class="flex items-center gap-3 px-4 py-2 hover:bg-surface-variant transition-colors text-on-surface">
                        <span class="material-symbols-outlined text-[16px] text-primary">savings</span>
                        <span class="font-label-caps text-[11px] uppercase tracking-wider">Add Savings</span>
                    </a>
                </div>
            </div>

            <div class="flex flex-col items-end">
                <span class="font-data-mono text-data-mono text-primary-fixed-dim">
                    @if(isset($safeToSpend))
                        {{ format_currency($safeToSpend, Auth::user()->setting->currency ?? 'USD') }} Safe
                    @elseif(Auth::user() && Auth::user()->accounts()->first())
                        {{ format_currency(Auth::user()->accounts()->first()->balance, Auth::user()->setting->currency ?? 'USD') }} Balance
                    @else
                        {{ currency_symbol(Auth::user()->setting->currency ?? 'USD') }}0.00 Safe
                    @endif
                </span>
                <span class="font-label-caps text-[9px] text-outline-variant tracking-tighter uppercase">DIAGNOSTIC SYNCED</span>
            </div>
            
            <div class="w-10 h-10 rounded overflow-hidden border border-primary-fixed/20 bg-surface-container flex items-center justify-center">
                <span class="font-label-caps text-[12px] text-primary-fixed tracking-wider font-bold">{{ strtoupper(substr(Auth::user()->name ?? 'OP', 0, 2)) }}</span>
            </div>
        </div>
    </header>

    <!-- SideNavBar (Desktop & Mobile Drawer) -->
    <aside id="appSidebar" class="flex flex-col h-full py-gutter fixed left-0 top-0 z-50 w-64 bg-surface-container-lowest border-r border-white/5 -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out pt-24">
        <!-- Close button inside drawer for mobile -->
        <button class="md:hidden absolute top-4 right-4 p-2 text-on-surface-variant hover:text-primary-fixed" id="closeSidebarBtn">
            <span class="material-symbols-outlined">close</span>
        </button>

        <div class="px-6 mb-8">
            <h2 class="font-title-md text-title-md text-primary-fixed mb-1">COMMAND CENTER</h2>
            <p class="font-label-caps text-[10px] text-outline tracking-widest uppercase">
                @yield('page_title', 'ACTIVE NODE')
            </p>
        </div>

        <nav class="flex-1 overflow-y-auto space-y-6 custom-scrollbar pb-8">
            @foreach($navGroups as $group)
                <div class="flex flex-col gap-1">
                    <h3 class="text-[10px] font-black text-outline uppercase tracking-[0.2em] px-6 mb-2">
                        {{ $group['label'] }}
                    </h3>
                    @foreach($group['items'] as $item)
                        @php
                            $isActive = ($currentPath === $item['href']);
                        @endphp
                        <a href="{{ $item['href'] }}" class="flex items-center gap-4 px-6 py-3 transition-all duration-200 {{ $isActive ? 'bg-primary-fixed/10 text-primary-fixed border-l-4 border-primary-fixed translate-x-1 font-bold' : 'text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface' }}">
                            <span class="material-symbols-outlined text-[20px]">{{ $item['icon'] }}</span>
                            <span class="font-label-caps text-label-caps text-[12px]">{{ $item['name'] }}</span>
                        </a>
                    @endforeach
                </div>
            @endforeach
        </nav>

        <div class="px-6 mt-auto border-t border-white/5 pt-4 space-y-4">
            <form action="/auth/logout" method="POST" id="logoutForm" class="w-full">
                @csrf
                <button type="submit" class="w-full h-12 bg-surface-container-high border border-outline-variant/20 text-on-surface font-label-caps text-[11px] tracking-widest uppercase hover:bg-error-container/20 hover:text-error transition-all flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-[16px]">logout</span>
                    <span>Deauthorize Node</span>
                </button>
            </form>
            <div class="flex justify-between text-[10px] text-outline-variant font-label-caps">
                <span class="flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-primary-fixed-dim animate-ping"></span> SECURE TUNNEL</span>
                <span>v3.0.0</span>
            </div>
        </div>
    </aside>

    <!-- Main Content Canvas -->
    <main class="md:ml-64 pt-24 pb-12 px-container-padding min-h-screen z-10 flex flex-col">
        <div class="max-w-7xl w-full mx-auto space-y-gutter flex-1">
            
            <!-- Global Flash Messages -->
            @if(session('success'))
                <div class="bg-primary-fixed-dim/10 border border-primary-fixed-dim/30 text-primary-fixed-dim p-4 rounded-none flex items-center gap-3 animate-pulse">
                    <span class="material-symbols-outlined text-[20px]">verified</span>
                    <span class="font-label-caps text-xs tracking-wider">{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="bg-error-container/20 border border-error/30 text-error p-4 rounded-none">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="material-symbols-outlined text-[20px]">report</span>
                        <span class="font-label-caps text-xs tracking-wider">CRITICAL SYSTEM ERROR</span>
                    </div>
                    <ul class="list-disc pl-8 font-data-mono text-xs text-on-surface-variant space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <script>
        // Mobile Sidebar Controls
        const openSidebarBtn = document.getElementById('openSidebarBtn');
        const closeSidebarBtn = document.getElementById('closeSidebarBtn');
        const appSidebar = document.getElementById('appSidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        if (openSidebarBtn && closeSidebarBtn && appSidebar && sidebarOverlay) {
            openSidebarBtn.addEventListener('click', () => {
                appSidebar.classList.remove('-translate-x-full');
                sidebarOverlay.classList.remove('hidden');
            });

            closeSidebarBtn.addEventListener('click', () => {
                appSidebar.classList.add('-translate-x-full');
                sidebarOverlay.classList.add('hidden');
            });

            sidebarOverlay.addEventListener('click', () => {
                appSidebar.classList.add('-translate-x-full');
                sidebarOverlay.classList.add('hidden');
            });
        }
    </script>
    @yield('scripts')
</body>
</html>
