@extends('layouts.app')

@section('title', 'NEUROSPEND | Survival Command')
@section('page_title', 'SURVIVAL COMMAND')

@section('content')
<div class="space-y-gutter animate-fade-in pb-10">
    
    <!-- Top Stats / Bento Header -->
    <div class="grid grid-cols-12 gap-gutter">
        <!-- Main Metric: Safe-to-Spend -->
        <div class="col-span-12 lg:col-span-8 glass-panel p-8 flex flex-col justify-between relative overflow-hidden group glow-primary">
            <div class="absolute -right-16 -top-16 w-64 h-64 bg-primary-fixed-dim/10 rounded-full blur-[80px] pointer-events-none"></div>
            
            <div class="flex justify-between items-start z-10">
                <div class="space-y-unit">
                    <h3 class="font-label-caps text-label-caps text-primary-fixed-dim uppercase tracking-widest">Safe-to-Spend Daily Allowance</h3>
                    <p class="font-display-lg text-[64px] leading-none text-white tracking-tighter" id="dailyAllowanceVal">
                        {{ format_currency($dailyAllowance, $currency) }}
                    </p>
                </div>
                <div class="bg-surface-container-highest/50 p-2 rounded border border-white/5">
                    <span class="material-symbols-outlined text-primary-fixed-dim">security</span>
                </div>
            </div>

            <div class="mt-12 flex items-end justify-between z-10 border-t border-white/5 pt-6">
                <div class="space-y-1">
                    <p class="font-label-caps text-[10px] text-outline uppercase tracking-wider">Tactical Cycle Residual</p>
                    <p class="font-data-mono text-data-mono text-primary">
                        {{ format_currency($safeToSpend, $currency) }} distributed across {{ $daysLeft }} days
                    </p>
                </div>
                <!-- Visual telemetry bars -->
                <div class="flex gap-2">
                    <div class="h-12 w-1 bg-primary-fixed/20 rounded-full overflow-hidden">
                        <div class="h-2/3 w-full bg-primary-fixed-dim"></div>
                    </div>
                    <div class="h-12 w-1 bg-primary-fixed/20 rounded-full overflow-hidden">
                        <div class="h-1/2 w-full bg-primary-fixed-dim"></div>
                    </div>
                    <div class="h-12 w-1 bg-primary-fixed/20 rounded-full overflow-hidden">
                        <div class="h-4/5 w-full bg-primary-fixed-dim"></div>
                    </div>
                    <div class="h-12 w-1 bg-primary-fixed/20 rounded-full overflow-hidden">
                        <div class="h-1/3 w-full bg-primary-fixed-dim"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Ring: Days Remaining -->
        <div class="col-span-12 lg:col-span-4 glass-panel p-8 flex flex-col items-center justify-center text-center relative overflow-hidden group">
            @php
                $dashoffset = 440 - (440 * min(max($progressPercentage, 0), 100) / 100);
            @endphp
            <div class="relative w-40 h-40 flex items-center justify-center">
                <!-- SVG Ring -->
                <svg class="w-full h-full transform -rotate-90">
                    <circle class="text-white/5" cx="80" cy="80" fill="transparent" r="70" stroke="currentColor" stroke-width="8"></circle>
                    <circle class="text-primary-fixed-dim drop-shadow-[0_0_8px_rgba(0,226,144,0.4)]" cx="80" cy="80" fill="transparent" r="70" stroke="currentColor" stroke-dasharray="440" stroke-dashoffset="{{ $dashoffset }}" stroke-width="8" stroke-linecap="round"></circle>
                </svg>
                <div class="absolute flex flex-col items-center">
                    <span class="font-data-mono text-4xl text-white">{{ $daysLeft }}</span>
                    <span class="font-label-caps text-[9px] text-outline tracking-widest">DAYS LEFT</span>
                </div>
            </div>
            <div class="mt-6 w-full pt-4 border-t border-white/5">
                <button onclick="toggleOverrideModal()" class="flex items-center justify-center gap-2 w-full font-label-caps text-label-caps text-on-surface-variant hover:text-white transition-colors">
                    <span class="material-symbols-outlined text-sm">calendar_month</span>
                    <span>CYCLE OVERRIDE</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Active cycle checklist -->
    <div class="grid grid-cols-12 gap-gutter">
        <!-- Checklist Cards -->
        <div class="col-span-12 lg:col-span-7 glass-panel p-8 flex flex-col">
            <h3 class="font-title-md text-title-md text-white mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary-fixed-dim">event_upcoming</span>
                <span>STRATEGIC OBLIGATIONS</span>
            </h3>
            
            @if($upcomingBills->count() === 0)
                <div class="flex-1 flex flex-col items-center justify-center py-12 text-center space-y-3">
                    <span class="material-symbols-outlined text-[48px] text-primary-fixed-dim animate-pulse">verified_user</span>
                    <h4 class="font-title-md text-sm text-white">Clear Horizon</h4>
                    <p class="font-body-md text-xs text-on-surface-variant max-w-xs leading-relaxed">No upcoming obligations tracked before your next payday cycle.</p>
                </div>
            @else
                <div class="flex-1 overflow-y-auto custom-scrollbar space-y-4">
                    @foreach($upcomingBills as $bill)
                        <div class="flex items-center justify-between p-4 bg-white/5 rounded-none border border-white/5 hover:bg-white/10 transition-all">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-none bg-surface-container-high flex items-center justify-center border border-white/5">
                                    <span class="material-symbols-outlined text-primary-fixed-dim text-xl">receipt</span>
                                </div>
                                <div>
                                    <p class="font-title-md text-sm text-white">{{ $bill->name }}</p>
                                    <p class="font-label-caps text-[9px] text-outline tracking-wider">DUE ON {{ format_date($bill->next_due_date) }}</p>
                                </div>
                            </div>
                            <span class="font-data-mono text-sm font-bold text-error">-{{ format_currency($bill->amount, $currency) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Telemetry Audit / Stats Card -->
        <div class="col-span-12 lg:col-span-5 glass-panel p-8 flex flex-col justify-between">
            <div class="space-y-stack-md">
                <h3 class="font-title-md text-title-md text-white mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary-fixed-dim">shield</span>
                    <span>RUNWAY DIAGNOSTICS</span>
                </h3>

                <div class="space-y-4">
                    <div class="flex justify-between items-center pb-3 border-b border-white/5">
                        <span class="font-label-caps text-[11px] text-outline tracking-wider uppercase">Projected Inflow</span>
                        <span class="font-data-mono text-sm text-primary-fixed-dim font-bold">+{{ format_currency($settings->payday_amount, $currency) }}</span>
                    </div>
                    <div class="flex justify-between items-center pb-3 border-b border-white/5">
                        <span class="font-label-caps text-[11px] text-outline tracking-wider uppercase">Cycle Obligations</span>
                        <span class="font-data-mono text-sm text-error font-bold">{{ format_currency($summary['upcomingBillsTotal'] ?? 0, $currency) }}</span>
                    </div>
                    <div class="flex justify-between items-center pb-3 border-b border-white/5">
                        <span class="font-label-caps text-[11px] text-outline tracking-wider uppercase">Fluid Balance</span>
                        <span class="font-data-mono text-sm text-secondary-container font-bold">{{ format_currency($summary['totalBalance'] ?? 0, $currency) }}</span>
                    </div>
                </div>
            </div>

            <!-- NOVA AI Diagnostic Quote -->
            <div class="mt-8 border border-primary-fixed-dim/20 bg-primary-fixed-dim/5 p-4 relative">
                <div class="flex items-start gap-4">
                    <span class="material-symbols-outlined text-primary-fixed-dim text-[24px] animate-pulse">psychology</span>
                    <div class="space-y-1">
                        <h5 class="font-label-caps text-[9px] text-primary-fixed-dim tracking-widest">NOVA AI SENTINEL</h5>
                        <p class="font-body-md text-xs text-on-surface-variant italic">
                            @if($daysLeft <= 3)
                                "Payday injection imminent. Limit discretionary spend vectors to preserve the safe-to-spend buffer. Calibration is optimal."
                            @elseif($dailyAllowance <= 15)
                                "Warning: Tight daily allowance corridor. Consolidate liquidity streams and postpone non-essential network upgrades."
                            @else
                                "Runway pace matches target criteria. System safety parameters are fully operational."
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payday Override Modal -->
<div id="overrideModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/80 backdrop-blur-xl hidden transition-all duration-300">
    <div class="glass-panel w-full max-w-sm p-8 shadow-2xl relative">
        <div class="flex justify-between items-center mb-6">
            <h3 class="font-headline-lg text-xl text-primary font-light">Override Cycle</h3>
            <button onclick="toggleOverrideModal()" class="text-on-surface-variant hover:text-white transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        
        <p class="font-body-md text-xs text-on-surface-variant leading-relaxed mb-6">
            Configure a customized, one-time date for the **upcoming** payday. Standard day of month: Day {{ $settings->payday_date }}.
        </p>

        <div class="space-y-6">
            <form method="POST" action="/settings/override" class="space-y-4">
                @csrf
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-primary-fixed-dim text-[18px]">calendar_today</span>
                    <input type="date" name="payday_override" class="w-full h-14 bg-surface-container-lowest border border-outline-variant/30 px-12 font-data-mono text-data-mono text-primary rounded-none transition-all input-focus-ring" value="{{ $settings->payday_override }}">
                </div>
                <button type="submit" class="w-full h-12 bg-primary-fixed text-on-primary-fixed font-label-caps text-[11px] tracking-widest uppercase hover:brightness-110 active:scale-95 transition-all">Apply Override</button>
            </form>
            
            @if($settings->payday_override)
                <form method="POST" action="/settings/override/clear">
                    @csrf
                    <button type="submit" class="font-label-caps text-[10px] text-error/60 hover:text-error py-2 w-full text-center hover:underline">Revert to Standard Day</button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function toggleOverrideModal() {
        const modal = document.getElementById('overrideModal');
        modal.classList.toggle('hidden');
    }
</script>
@endsection
