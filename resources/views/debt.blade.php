@extends('layouts.app')

@section('title', 'NEUROSPEND | Debt Command')
@section('page_title', 'DEBT COMMAND')

@php
    $activeDebts = $debts->filter(function($d) { return !$d->is_paid_off; });
    $totalDebt = $activeDebts->sum('current_balance');
    $totalMinimum = $activeDebts->sum('minimum_payment');
    
    $blendedInterest = 0;
    if ($totalDebt > 0) {
        $blendedInterest = $activeDebts->reduce(function($carry, $d) use ($totalDebt) {
            return $carry + ($d->interest_rate * ($d->current_balance / $totalDebt));
        }, 0);
    }
    
    if ($strategy === 'avalanche') {
        $sortedDebts = $activeDebts->sortByDesc('interest_rate');
    } else {
        $sortedDebts = $activeDebts->sortBy('current_balance');
    }
    
    $monthsToPayoff = ($activeDebts->count() > 0 && $totalMinimum > 0) ? (int)round($totalDebt / $totalMinimum) : 0;
@endphp

@section('content')
<div class="space-y-gutter animate-fade-in pb-10">
    <!-- Header Hero Bento -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pt-2">
        <div class="space-y-unit">
            <h1 class="font-display-lg text-3xl text-primary font-light flex items-center gap-3 tracking-tight">
                <span class="material-symbols-outlined text-primary-fixed-dim text-[32px]">credit_card</span>
                <span>Debt Command Center</span>
            </h1>
            <p class="font-body-md text-on-surface-variant uppercase tracking-wider text-xs">Track active liabilities and execute algorithmic paydown strategies</p>
        </div>
        <button onclick="toggleAddDebtForm()" class="h-12 px-6 bg-primary-fixed text-on-primary-fixed font-label-caps text-[11px] tracking-widest uppercase hover:brightness-110 active:scale-95 transition-all flex items-center gap-2 shadow-[0_0_20px_rgba(82,255,172,0.1)]">
            <span class="material-symbols-outlined text-[16px]">add</span>
            <span>Register Liability</span>
        </button>
    </div>

    <!-- Add Debt Form (Collapsible Command Panel) -->
    <div id="addDebtCard" class="glass-panel p-8 hidden relative animate-scale-in">
        <form method="POST" action="/debt" class="space-y-6">
            @csrf
            <h3 class="font-title-md text-title-md text-white flex items-center gap-2 mb-4">
                <span class="material-symbols-outlined text-primary-fixed-dim text-lg">add_card</span>
                <span>REGISTER LIQUIDITY OBLIGATION</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="space-y-unit">
                    <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Debt Name</label>
                    <input type="text" name="name" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 px-4 font-data-mono text-data-mono text-primary rounded-none transition-all input-focus-ring" placeholder="e.g. Chase Sapphire" required>
                </div>
                <div class="space-y-unit">
                    <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Debt Vector (Type)</label>
                    <select name="type" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 px-4 font-data-mono text-data-mono text-primary rounded-none transition-all focus:ring-2 focus:ring-primary-fixed-dim focus:border-primary-fixed-dim" required>
                        <option value="credit_card" class="bg-surface-container-lowest">Credit Card</option>
                        <option value="loan" class="bg-surface-container-lowest">Personal Loan</option>
                        <option value="car_loan" class="bg-surface-container-lowest">Car Loan</option>
                        <option value="mortgage" class="bg-surface-container-lowest">Mortgage</option>
                    </select>
                </div>
                <div class="space-y-unit">
                    <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Original Balance</label>
                    <input type="number" step="0.01" name="original_amount" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 px-4 font-data-mono text-data-mono text-primary rounded-none transition-all input-focus-ring" placeholder="0.00" required>
                </div>
                <div class="space-y-unit">
                    <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Current Balance</label>
                    <input type="number" step="0.01" name="current_balance" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 px-4 font-data-mono text-data-mono text-primary rounded-none transition-all input-focus-ring" placeholder="0.00" required>
                </div>
                <div class="space-y-unit">
                    <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Annual Interest Rate (%)</label>
                    <input type="number" step="0.01" name="interest_rate" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 px-4 font-data-mono text-data-mono text-primary rounded-none transition-all input-focus-ring" placeholder="e.g. 24.99" required>
                </div>
                <div class="space-y-unit">
                    <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Minimum Monthly Payment</label>
                    <input type="number" step="0.01" name="minimum_payment" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 px-4 font-data-mono text-data-mono text-primary rounded-none transition-all input-focus-ring" placeholder="0.00" required>
                </div>
                <div class="space-y-unit">
                    <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Payment Due Day (1-31)</label>
                    <input type="number" min="1" max="31" name="due_day" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 px-4 font-data-mono text-data-mono text-primary rounded-none transition-all input-focus-ring" value="15" required>
                </div>
                <div class="flex items-center space-x-3 pt-6">
                    <input type="checkbox" name="exclude_from_balance" id="excludeFromBalance" value="1" class="w-5 h-5 bg-surface-container-lowest border border-outline-variant/30 text-primary-fixed-dim focus:ring-primary-fixed-dim rounded-none">
                    <label for="excludeFromBalance" class="font-label-caps text-[10px] text-on-surface-variant uppercase tracking-wider cursor-pointer">Exclude from Accounts list</label>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="toggleAddDebtForm()" class="h-12 px-6 bg-surface-container-high border border-outline-variant/20 text-on-surface font-label-caps text-[11px] tracking-widest uppercase hover:bg-surface-variant transition-colors">Cancel</button>
                <button type="submit" class="h-12 px-8 bg-primary-fixed text-on-primary-fixed font-label-caps text-[11px] tracking-widest uppercase hover:brightness-110 active:scale-95 transition-all">Register Liability</button>
            </div>
        </form>
    </div>

    <!-- KPI Summary Bento Block -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-gutter">
        <div class="glass-panel p-6 space-y-stack-sm relative overflow-hidden group">
            <span class="block font-label-caps text-label-caps text-outline tracking-wider">TOTAL DEBT MASS</span>
            <div class="font-data-mono text-2xl text-primary font-bold">{{ format_currency($totalDebt, $currency) }}</div>
            <p class="font-label-caps text-[10px] text-outline-variant tracking-wider uppercase">Aggregated liability volume</p>
        </div>

        <div class="glass-panel p-6 space-y-stack-sm relative overflow-hidden group">
            <span class="block font-label-caps text-label-caps text-outline tracking-wider">BLENDED APR</span>
            <div class="font-data-mono text-2xl text-warning font-bold">{{ number_format($blendedInterest, 2) }}%</div>
            <p class="font-label-caps text-[10px] text-outline-variant tracking-wider uppercase">Weighted average APR rate</p>
        </div>

        <div class="glass-panel p-6 space-y-stack-sm relative overflow-hidden group">
            <span class="block font-label-caps text-label-caps text-outline tracking-wider">MINIMUM OBLIGATION</span>
            <div class="font-data-mono text-2xl text-error font-bold">{{ format_currency($totalMinimum, $currency) }}</div>
            <p class="font-label-caps text-[10px] text-outline-variant tracking-wider uppercase">Required monthly flow velocity</p>
        </div>

        <div class="glass-panel p-6 space-y-stack-sm relative overflow-hidden group">
            <span class="block font-label-caps text-label-caps text-outline tracking-wider">PAYOFF TIMELINE</span>
            <div class="font-data-mono text-2xl text-primary-fixed-dim font-bold">{{ $monthsToPayoff }} Months</div>
            <p class="font-label-caps text-[10px] text-outline-variant tracking-wider uppercase">Estimated payoff duration</p>
        </div>
    </div>

    <!-- Active Trajectories and Strategy -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter">
        <!-- Main Paydown Area -->
        <div class="lg:col-span-8 space-y-6">
            <div class="flex justify-between items-center bg-surface-container-lowest/80 border border-white/5 p-3">
                <span class="font-label-caps text-[11px] text-outline pl-3 uppercase tracking-wider">Trajectory Engine:</span>
                <div class="flex gap-2">
                    <a href="/debt?strategy=avalanche" class="h-10 px-4 flex items-center gap-2 font-label-caps text-[10px] uppercase tracking-wider transition-all {{ $strategy === 'avalanche' ? 'bg-primary-fixed text-on-primary-fixed' : 'text-on-surface-variant hover:text-white' }}">
                        <span class="material-symbols-outlined text-[14px]">bolt</span>
                        <span>Avalanche (High APR)</span>
                    </a>
                    <a href="/debt?strategy=snowball" class="h-10 px-4 flex items-center gap-2 font-label-caps text-[10px] uppercase tracking-wider transition-all {{ $strategy === 'snowball' ? 'bg-primary-fixed text-on-primary-fixed' : 'text-on-surface-variant hover:text-white' }}">
                        <span class="material-symbols-outlined text-[14px]">cyclone</span>
                        <span>Snowball (Low Balance)</span>
                    </a>
                </div>
            </div>

            @if($sortedDebts->count() === 0)
                <div class="glass-panel p-16 flex flex-col items-center justify-center text-center space-y-4">
                    <div class="w-16 h-16 rounded-full bg-primary-fixed-dim/10 flex items-center justify-center border border-primary-fixed-dim/20">
                        <span class="material-symbols-outlined text-primary-fixed-dim text-[36px] animate-pulse">verified_user</span>
                    </div>
                    <h3 class="font-headline-lg text-xl text-primary font-light">Absolute Freedom</h3>
                    <p class="font-body-md text-xs text-on-surface-variant max-w-sm">No outstanding liabilities detected in active memory nodes. System safety parameters optimal.</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($sortedDebts as $index => $debt)
                        <div class="glass-panel p-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-6 relative group hover:border-primary-fixed-dim/30 transition-all duration-300">
                            
                            <!-- Delete button -->
                            <form action="/debt/{{ $debt->id }}" method="POST" class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity" onsubmit="return confirm('Erase liability configuration?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1 text-on-surface-variant hover:text-error transition-colors">
                                    <span class="material-symbols-outlined text-[16px]">delete</span>
                                </button>
                            </form>

                            <div class="flex items-center gap-4">
                                @if($index === 0)
                                    <div class="w-12 h-12 bg-primary-fixed-dim/15 border border-primary-fixed-dim/30 text-primary-fixed-dim flex items-center justify-center animate-pulse" title="Target debt Priority Vector">
                                        <span class="material-symbols-outlined text-[24px]">target</span>
                                    </div>
                                @else
                                    <div class="w-12 h-12 bg-surface-container-high border border-white/5 text-outline flex items-center justify-center">
                                        <span class="font-data-mono text-xs font-bold">#{{ $index + 1 }}</span>
                                    </div>
                                @endif
                                <div>
                                    <h4 class="font-title-md text-base text-primary leading-tight font-semibold">{{ $debt->name }}</h4>
                                    <span class="font-label-caps text-[9px] text-outline uppercase tracking-wider">{{ $debt->type }} · APR: {{ $debt->interest_rate }}%</span>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-6 items-center">
                                <div class="space-y-unit">
                                    <span class="block font-label-caps text-[9px] text-outline uppercase tracking-wider">Balance</span>
                                    <span class="font-data-mono font-bold text-primary text-base">{{ format_currency($debt->current_balance, $currency) }}</span>
                                </div>
                                <div class="space-y-unit">
                                    <span class="block font-label-caps text-[9px] text-outline uppercase tracking-wider">Min Due</span>
                                    <span class="font-data-mono text-on-surface-variant text-sm">{{ format_currency($debt->minimum_payment, $currency) }}</span>
                                </div>
                                <button onclick="openPayoffModal({{ $debt->id }}, '{{ $debt->name }}', {{ $debt->current_balance }})" class="h-10 px-4 bg-surface-container-high border border-outline-variant/20 text-on-surface font-label-caps text-[10px] tracking-widest uppercase hover:bg-surface-variant transition-colors flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px]">done_all</span>
                                    <span>Log Payment</span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Strategy Column -->
        <div class="lg:col-span-4 glass-panel p-8 h-fit space-y-6">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-primary-fixed-dim text-[28px]">psychology</span>
                <h3 class="font-headline-lg text-lg text-primary font-light">Algorithmic Guidance</h3>
            </div>
            
            <div class="space-y-4 font-body-md text-[13px] text-on-surface-variant leading-relaxed font-light">
                @if($strategy === 'avalanche')
                    <p>
                        Your active trajectory is simulated via <strong class="text-primary font-semibold">Avalanche</strong>. High-interest parameters are prioritized to minimize long-term capital bleed.
                    </p>
                    <p>
                        Targeting high APR nodes yields the maximum mathematical saving velocity. Continue injecting capital surplus into the priority target.
                    </p>
                @else
                    <p>
                        Your active trajectory is simulated via <strong class="text-primary font-semibold">Snowball</strong>. Low-balance parameters are targeted first to build momentum.
                    </p>
                    <p>
                        Erasing small nodes quickly delivers tactical psychological victories. Keep executing snowball cycles with surplus capital.
                    </p>
                @endif

                <div class="border border-primary-fixed-dim/20 bg-primary-fixed-dim/5 p-4 mt-6">
                    <p class="font-body-md text-[12px] text-on-surface-variant leading-relaxed">
                        <strong class="text-primary-fixed-dim font-bold block uppercase tracking-wider mb-1">NOVA CORE CRITERIA</strong>
                        Apply baseline minimum payment thresholds on all passive nodes to avoid service deprecation. Route all residual capital vectors straight to priority index #1.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payoff Modal -->
<div id="payoffModal" class="fixed inset-0 bg-black/80 z-[999] hidden flex items-center justify-center p-4 backdrop-blur-xl animate-fade-in transition-all duration-300">
    <div class="glass-panel max-w-md w-full p-8 shadow-2xl relative">
        <button onclick="closePayoffModal()" class="absolute right-4 top-4 text-on-surface-variant hover:text-white transition-colors">
            <span class="material-symbols-outlined">close</span>
        </button>
        
        <h3 class="font-headline-lg text-xl text-primary mb-6 flex items-center gap-3 font-light">
            <span class="material-symbols-outlined text-primary-fixed-dim text-[24px]">payments</span>
            <span>Log Repayment Vector</span>
        </h3>
        
        <form id="payoffModalForm" method="POST" action="" class="space-y-6">
            @csrf
            <div class="space-y-4">
                <div class="space-y-unit">
                    <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Target Liability Node</label>
                    <input type="text" id="modalDebtName" class="w-full h-12 bg-surface-container-high border border-outline-variant/30 px-4 font-data-mono text-data-mono text-primary rounded-none transition-all cursor-not-allowed" disabled>
                </div>

                <div class="space-y-unit">
                    <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Liquidity Source (Pay From)</label>
                    <select required name="account_id" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 px-4 font-data-mono text-data-mono text-primary rounded-none transition-all focus:ring-2 focus:ring-primary-fixed-dim focus:border-primary-fixed-dim">
                        <option value="" disabled selected>Select Account...</option>
                        @foreach($accounts as $acc)
                            @if($acc->type !== 'credit')
                                <option value="{{ $acc->id }}" class="bg-surface-container-lowest">{{ $acc->name }} ({{ format_currency($acc->balance, $currency) }})</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="space-y-unit">
                    <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Repayment Value</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 font-data-mono text-primary-fixed-dim font-bold">$</span>
                        <input type="number" required step="0.01" min="0.01" name="payment_amount" id="modalPaymentAmount" class="w-full h-14 bg-surface-container-lowest border border-outline-variant/30 pl-10 pr-4 font-data-mono text-data-mono text-primary rounded-none transition-all input-focus-ring">
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closePayoffModal()" class="h-12 px-6 bg-surface-container-high border border-outline-variant/20 text-on-surface font-label-caps text-[11px] tracking-widest uppercase hover:bg-surface-variant transition-colors">Cancel</button>
                <button type="submit" class="h-12 px-8 bg-primary-fixed text-on-primary-fixed font-label-caps text-[11px] tracking-widest uppercase hover:brightness-110 active:scale-95 transition-all">Confirm Payment</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function toggleAddDebtForm() {
        const form = document.getElementById('addDebtCard');
        form.classList.toggle('hidden');
    }

    function openPayoffModal(id, name, balance) {
        document.getElementById('modalDebtName').value = name;
        document.getElementById('modalPaymentAmount').value = balance;
        document.getElementById('payoffModalForm').action = `/debt/${id}/pay`;
        document.getElementById('payoffModal').classList.remove('hidden');
    }

    function closePayoffModal() {
        document.getElementById('payoffModal').classList.add('hidden');
    }
</script>
@endsection
