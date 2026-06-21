<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>NEUROSPEND | Calibration</title>
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
        }

        .glass-panel {
            background: rgba(26, 28, 32, 0.45);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .input-focus-ring:focus {
            outline: none;
            box-shadow: 0 0 0 2px #00e290;
            border-color: #00e290;
        }

        .active-glow {
            box-shadow: 0 0 30px rgba(0, 226, 144, 0.15);
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
            background-image: radial-gradient(circle at center, #00e29004 0%, transparent 70%);
            animation: pulse 8s infinite alternate ease-in-out;
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 0.3; }
            100% { transform: scale(1.5); opacity: 0.8; }
        }
        
        .step-view { display: none; }
        .step-view.active { display: block; }
        
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="font-body-md selection:bg-primary-fixed-dim selection:text-on-primary-fixed min-h-screen relative flex items-center justify-center p-gutter overflow-x-hidden">
    <!-- Atmospheric Background -->
    <div class="absolute inset-0 pointer-events-none z-0">
        <div class="neural-pulse"></div>
        <div class="absolute top-0 right-0 w-1/2 h-full bg-gradient-to-l from-primary-fixed-dim/5 to-transparent"></div>
    </div>

    <!-- Main Container -->
    <main class="w-full max-w-lg glass-panel p-8 relative z-10 space-y-stack-md flex flex-col justify-between">
        
        <!-- Step 1: Welcome -->
        <div id="step-1" class="step-view active text-center py-6 space-y-stack-md">
            <div class="flex justify-center mb-4">
                <div class="w-24 h-24 relative">
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
            
            <div class="space-y-unit">
                <p class="font-label-caps text-label-caps text-primary-fixed-dim tracking-[0.2em]">SYSTEM INITIALIZATION</p>
                <h1 class="font-display-lg text-headline-lg text-primary tracking-tight font-light leading-none">
                    NOVA Intelligence
                </h1>
            </div>
            
            <p class="font-body-md text-on-surface-variant max-w-md mx-auto leading-relaxed text-[15px] font-light">
                Welcome to NeuroSpend. I am NOVA, your local-first neural financial advisor. I exist to monitor, optimize, and defend your liquidity vectors.
            </p>
            
            <button type="button" onclick="nextStep(2)" class="w-full h-16 bg-primary-fixed text-on-primary-fixed font-label-caps text-[13px] tracking-widest uppercase active:scale-[0.98] transition-all relative overflow-hidden group shadow-[0_0_30px_rgba(82,255,172,0.1)] hover:shadow-[0_0_40px_rgba(82,255,172,0.2)]">
                <span class="relative z-10 flex items-center justify-center space-x-stack-sm">
                    <span>Begin Calibration</span>
                    <span class="material-symbols-outlined text-[18px]">arrow_right_alt</span>
                </span>
                <div class="absolute inset-0 bg-white/20 -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
            </button>
        </div>

        <!-- Step 2: Map Core Stream -->
        <div id="step-2" class="step-view py-2 space-y-stack-md">
            <header class="flex items-center space-x-stack-sm">
                <span class="material-symbols-outlined text-primary-fixed-dim text-[32px]">account_balance_wallet</span>
                <div>
                    <h2 class="font-display-lg text-[22px] text-primary leading-tight font-light">Map Core Liquidity</h2>
                    <p class="font-body-md text-[13px] text-on-surface-variant uppercase tracking-wider">Connect your primary checking node</p>
                </div>
            </header>
            
            <div class="space-y-stack-sm pt-gutter">
                <div class="space-y-unit">
                    <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Ledger Identifier</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">label</span>
                        <input id="accName" type="text" class="w-full h-14 bg-surface-container-lowest border border-outline-variant/30 px-12 font-data-mono text-data-mono text-primary rounded-none transition-all input-focus-ring" placeholder="Primary Checking">
                    </div>
                </div>
                
                <div class="space-y-unit">
                    <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Current Balance</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 font-data-mono text-primary-fixed-dim font-bold">$</span>
                        <input id="accBal" type="number" class="w-full h-14 bg-surface-container-lowest border border-outline-variant/30 pl-10 pr-4 font-data-mono text-data-mono text-primary rounded-none transition-all input-focus-ring" placeholder="0.00">
                    </div>
                </div>
            </div>

            <div class="flex space-x-gutter pt-gutter">
                <button type="button" onclick="nextStep(3)" class="w-1/3 h-14 bg-surface-container-high border border-outline-variant/20 text-on-surface font-label-caps text-[11px] tracking-widest uppercase hover:bg-surface-variant transition-colors">Skip</button>
                <button type="button" onclick="saveStep2()" class="w-2/3 h-14 bg-primary-fixed text-on-primary-fixed font-label-caps text-[11px] tracking-widest uppercase relative overflow-hidden group shadow-[0_0_30px_rgba(82,255,172,0.1)]">
                    <span class="relative z-10 flex items-center justify-center space-x-unit">
                        <span>Sync Node</span>
                        <span class="material-symbols-outlined text-[16px]">sync</span>
                    </span>
                </button>
            </div>
        </div>

        <!-- Step 3: Payday Configuration -->
        <div id="step-3" class="step-view py-2 space-y-stack-md">
            <header class="flex items-center space-x-stack-sm">
                <span class="material-symbols-outlined text-secondary-container text-[32px]">calendar_today</span>
                <div>
                    <h2 class="font-display-lg text-[22px] text-primary leading-tight font-light">Injection Cycle</h2>
                    <p class="font-body-md text-[13px] text-on-surface-variant uppercase tracking-wider">Configure recurring income events</p>
                </div>
            </header>
            
            <div class="space-y-stack-sm pt-gutter">
                <div class="space-y-unit">
                    <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Frequency</label>
                    <select id="paydayFreq" class="w-full h-14 bg-surface-container-lowest border border-outline-variant/30 px-4 font-data-mono text-data-mono text-primary rounded-none transition-all focus:ring-2 focus:ring-primary-fixed-dim focus:border-primary-fixed-dim" onchange="togglePaydayInput()">
                        <option value="monthly" class="bg-surface-container-lowest">Monthly</option>
                        <option value="15_days" class="bg-surface-container-lowest">15 Days (Semi-Monthly)</option>
                        <option value="weekly" class="bg-surface-container-lowest">Weekly</option>
                    </select>
                </div>

                <div id="monthlyPaydayWrapper" class="space-y-unit">
                    <label id="paydayDateLabel" class="font-label-caps text-label-caps text-on-surface-variant ml-1">Monthly Payday (1-31)</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-[18px]">event</span>
                        <input id="paydayDate" type="number" min="1" max="31" class="w-full h-14 bg-surface-container-lowest border border-outline-variant/30 px-12 font-data-mono text-data-mono text-primary rounded-none transition-all input-focus-ring" placeholder="e.g. 15" value="1">
                    </div>
                </div>

                <div id="weeklyPaydayWrapper" class="space-y-unit" style="display: none;">
                    <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Payday Day</label>
                    <select id="paydayDateWeekly" class="w-full h-14 bg-surface-container-lowest border border-outline-variant/30 px-4 font-data-mono text-data-mono text-primary rounded-none transition-all focus:ring-2 focus:ring-primary-fixed-dim focus:border-primary-fixed-dim">
                        <option value="1" class="bg-surface-container-lowest">Monday</option>
                        <option value="2" class="bg-surface-container-lowest">Tuesday</option>
                        <option value="3" class="bg-surface-container-lowest">Wednesday</option>
                        <option value="4" class="bg-surface-container-lowest">Thursday</option>
                        <option value="5" class="bg-surface-container-lowest">Friday</option>
                        <option value="6" class="bg-surface-container-lowest">Saturday</option>
                        <option value="0" class="bg-surface-container-lowest">Sunday</option>
                    </select>
                </div>

                <div class="space-y-unit">
                    <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Expected Inflow Mass</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 font-data-mono text-primary-fixed-dim font-bold">$</span>
                        <input id="paydayAmount" type="number" class="w-full h-14 bg-surface-container-lowest border border-outline-variant/30 pl-10 pr-4 font-data-mono text-data-mono text-primary rounded-none transition-all input-focus-ring" placeholder="0.00">
                    </div>
                </div>
            </div>

            <div class="flex space-x-gutter pt-gutter">
                <button type="button" onclick="nextStep(4)" class="w-1/3 h-14 bg-surface-container-high border border-outline-variant/20 text-on-surface font-label-caps text-[11px] tracking-widest uppercase hover:bg-surface-variant transition-colors">Skip</button>
                <button type="button" onclick="saveStep3()" class="w-2/3 h-14 bg-primary-fixed text-on-primary-fixed font-label-caps text-[11px] tracking-widest uppercase relative overflow-hidden group shadow-[0_0_30px_rgba(82,255,172,0.1)]">
                    <span class="relative z-10 flex items-center justify-center space-x-unit">
                        <span>Configure Cycle</span>
                        <span class="material-symbols-outlined text-[16px]">done</span>
                    </span>
                </button>
            </div>
        </div>

        <!-- Step 4: Complete -->
        <div id="step-4" class="step-view text-center py-6 space-y-stack-md">
            <div class="flex justify-center mb-4">
                <div class="w-20 h-20 relative flex items-center justify-center bg-primary-fixed-dim/10 rounded-full border border-primary-fixed-dim/20">
                    <span class="material-symbols-outlined text-primary-fixed-dim text-[48px] animate-pulse">check_circle</span>
                </div>
            </div>
            
            <div class="space-y-unit">
                <p class="font-label-caps text-label-caps text-primary-fixed-dim tracking-[0.2em]">CALIBRATION SYNC COMPLETE</p>
                <h1 class="font-display-lg text-headline-lg text-primary tracking-tight font-light leading-none">
                    Sentinel Live
                </h1>
            </div>
            
            <p class="font-body-md text-on-surface-variant max-w-sm mx-auto leading-relaxed text-[15px] font-light">
                All neural financial parameters have been synchronized. NOVA ledger tracking is active.
            </p>

            <button type="button" onclick="finishOnboarding()" class="w-full h-16 bg-primary-fixed text-on-primary-fixed font-label-caps text-[13px] tracking-widest uppercase mt-gutter active:scale-[0.98] transition-all relative overflow-hidden group shadow-[0_0_30px_rgba(82,255,172,0.1)] hover:shadow-[0_0_40px_rgba(82,255,172,0.2)]">
                <span class="relative z-10 flex items-center justify-center space-x-stack-sm">
                    <span>Enter Command Center</span>
                    <span class="material-symbols-outlined text-[18px]">dashboard</span>
                </span>
                <div class="absolute inset-0 bg-white/20 -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
            </button>
        </div>

        <!-- Progress Indicators -->
        <footer class="pt-gutter border-t border-outline-variant/10 flex justify-center space-x-2">
            <div id="dot-1" class="h-1 rounded-full transition-all duration-500 w-8 bg-primary-fixed-dim"></div>
            <div id="dot-2" class="h-1 rounded-full transition-all duration-500 w-4 bg-outline-variant/30"></div>
            <div id="dot-3" class="h-1 rounded-full transition-all duration-500 w-4 bg-outline-variant/30"></div>
            <div id="dot-4" class="h-1 rounded-full transition-all duration-500 w-4 bg-outline-variant/30"></div>
        </footer>
    </main>

    <!-- Corner Accents for premium aesthetics -->
    <div class="absolute top-4 left-4 w-8 h-8 border-t border-l border-primary-fixed/20 pointer-events-none"></div>
    <div class="absolute bottom-4 right-4 w-8 h-8 border-b border-r border-primary-fixed/20 pointer-events-none"></div>

    <script>
        let currentStepNum = 1;

        function nextStep(stepNum) {
            // Hide all step views
            document.querySelectorAll('.step-view').forEach(view => {
                view.classList.remove('active');
            });
            // Show new step view
            document.getElementById(`step-${stepNum}`).classList.add('active');
            currentStepNum = stepNum;

            // Update progress dots
            for (let i = 1; i <= 4; i++) {
                const dot = document.getElementById(`dot-${i}`);
                if (i === stepNum) {
                    dot.className = 'h-1 rounded-full transition-all duration-500 w-8 bg-primary-fixed-dim';
                } else if (i < stepNum) {
                    dot.className = 'h-1 rounded-full transition-all duration-500 w-4 bg-primary-fixed';
                } else {
                    dot.className = 'h-1 rounded-full transition-all duration-500 w-4 bg-outline-variant/30';
                }
            }
        }

        function togglePaydayInput() {
            const freq = document.getElementById('paydayFreq').value;
            const monthlyWrapper = document.getElementById('monthlyPaydayWrapper');
            const weeklyWrapper = document.getElementById('weeklyPaydayWrapper');
            const label = document.getElementById('paydayDateLabel');

            if (freq === 'weekly') {
                monthlyWrapper.style.display = 'none';
                weeklyWrapper.style.display = 'block';
            } else {
                weeklyWrapper.style.display = 'none';
                monthlyWrapper.style.display = 'block';
                if (freq === '15_days') {
                    label.innerText = 'Primary Payday (1-31)';
                } else {
                    label.innerText = 'Monthly Payday (1-31)';
                }
            }
        }

        function saveStep2() {
            const name = document.getElementById('accName').value;
            const balance = document.getElementById('accBal').value;

            if (!name || !balance) return;

            fetch('/onboarding', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    step: 2,
                    account_name: name,
                    account_balance: parseFloat(balance)
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    nextStep(3);
                } else {
                    alert('Sync error: ' + (data.error || 'Check parameters.'));
                }
            });
        }

        function saveStep3() {
            const freq = document.getElementById('paydayFreq').value;
            const amount = document.getElementById('paydayAmount').value;
            
            let dateVal = '';
            if (freq === 'weekly') {
                dateVal = document.getElementById('paydayDateWeekly').value;
            } else {
                dateVal = document.getElementById('paydayDate').value;
            }

            if (!dateVal || !amount) return;

            fetch('/onboarding', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    step: 3,
                    payday_freq: freq,
                    payday_date: dateVal,
                    payday_amount: parseFloat(amount)
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    nextStep(4);
                } else {
                    alert('Configuration error: ' + (data.error || 'Check parameters.'));
                }
            });
        }

        function finishOnboarding() {
            fetch('/onboarding', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    step: 4
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '/';
                }
            });
        }

        // Input Focus Visual Enhancement
        const inputs = document.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.addEventListener('focus', () => {
                input.parentElement.parentElement.classList.add('active-glow');
            });
            input.addEventListener('blur', () => {
                input.parentElement.parentElement.classList.remove('active-glow');
            });
        });
    </script>
</body>
</html>
