@extends('layouts.app')

@section('title', 'NEUROSPEND | Tactical Command')
@section('page_title', 'TACTICAL COMMAND')

@section('content')
<div class="space-y-gutter animate-fade-in pb-10">
    <!-- Hero Row: Bento Grid Layout -->
    <div class="grid grid-cols-12 gap-gutter">
        <!-- Main Tactical Hero Card -->
        <div class="col-span-12 lg:col-span-8 glass-panel p-8 relative overflow-hidden group min-h-[240px] flex flex-col justify-between">
            <div class="shimmer-bg absolute inset-0 pointer-events-none"></div>
            <div class="absolute -right-16 -top-16 w-64 h-64 bg-primary-fixed-dim/10 rounded-full blur-[80px] pointer-events-none group-hover:bg-primary-fixed-dim/15 transition-all duration-700"></div>
            
            <div class="relative z-10 space-y-stack-sm">
                <p class="font-label-caps text-label-caps text-primary-fixed-dim tracking-[0.25em]">TACTICAL LIQUIDITY VECTORS</p>
                <h3 class="font-display-lg text-4xl sm:text-[48px] {{ $summary['safeToSpend'] < 0 ? 'text-error' : 'text-primary' }} tracking-tight font-light leading-none flex items-baseline gap-3">
                    {{ format_currency(abs($summary['safeToSpend']), $currency) }}
                    @if($summary['safeToSpend'] < 0)
                        <span class="font-body-md text-xs sm:text-[14px] text-error/80 tracking-widest uppercase">Shortfall</span>
                    @else
                        <span class="font-body-md text-xs sm:text-[14px] text-on-surface-variant tracking-widest uppercase">Safe-To-Spend</span>
                    @endif
                </h3>
                <p class="font-body-md text-on-surface-variant max-w-lg leading-relaxed text-[15px] font-light pt-2">
                    @if($summary['safeToSpend'] < 0)
                        You are low this amount to cover your obligations due prior to your next injection cycle reset on <span class="font-bold text-error">{{ format_date($summary['nextPayday']) }}</span>.
                    @else
                        Residual command balance calculated after accounting for obligations due prior to next injection cycle reset on <span class="font-bold text-primary">{{ format_date($summary['nextPayday']) }}</span>.
                    @endif
                </p>
            </div>

            <!-- Mini Metrics Row -->
            <div class="relative z-10 grid grid-cols-3 gap-gutter pt-6 border-t border-white/5 mt-8">
                <div class="space-y-unit">
                    <span class="block font-label-caps text-[9px] text-outline tracking-wider uppercase">Residual Days</span>
                    <span class="font-data-mono text-base text-primary font-bold">{{ $summary['daysUntilPayday'] }}d</span>
                </div>
                <div class="space-y-unit">
                    <span class="block font-label-caps text-[9px] text-outline tracking-wider uppercase">Pacing Speed</span>
                    <span class="font-data-mono text-base {{ $summary['safeToSpend'] < 0 ? 'text-error' : 'text-primary-fixed-dim' }} font-bold">
                        {{ format_currency($summary['daysUntilPayday'] > 0 && $summary['safeToSpend'] > 0 ? ($summary['safeToSpend'] / $summary['daysUntilPayday']) : 0, $currency) }}/d
                    </span>
                </div>
                <div class="space-y-unit">
                    <span class="block font-label-caps text-[9px] text-outline tracking-wider uppercase">Gross Reserve</span>
                    <span class="font-data-mono text-base text-secondary-container font-bold">{{ format_currency($summary['totalBalance'], $currency) }}</span>
                </div>
            </div>
        </div>

        <!-- Nova Diagnostic Core Status Card -->
        <div class="col-span-12 lg:col-span-4 glass-panel p-8 flex flex-col justify-between relative overflow-hidden group">
            <div class="absolute -right-20 -bottom-20 w-48 h-48 bg-secondary-container/5 rounded-full blur-[60px] pointer-events-none"></div>
            
            <div class="space-y-stack-md">
                <div class="flex justify-between items-start">
                    <span class="font-label-caps text-label-caps text-outline tracking-wider">SENTINEL STATUS</span>
                    <div class="px-2 py-0.5 border border-primary-fixed-dim/20 bg-primary-fixed-dim/5 text-primary-fixed-dim font-label-caps text-[9px] tracking-wider uppercase">NOVA ACTIVE</div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-none border border-primary-fixed-dim/20 flex items-center justify-center bg-primary-fixed-dim/5">
                        <span class="material-symbols-outlined text-primary-fixed-dim text-[28px] animate-pulse">psychology</span>
                    </div>
                    <div>
                        <h4 class="font-title-md text-primary font-light">Autonomous Audit</h4>
                        <p class="font-label-caps text-[10px] text-on-surface-variant tracking-wider">TELEMETRY SYNC COMPLETE</p>
                    </div>
                </div>

                <div class="p-4 bg-surface-container-lowest/80 border border-outline-variant/10">
                    <p class="font-body-md text-[13px] text-on-surface-variant leading-relaxed">
                        @if($topInsight)
                            <span class="{{ $topInsight['type'] === 'danger' ? 'text-error' : ($topInsight['type'] === 'warning' ? 'text-warning' : 'text-primary-fixed-dim') }} font-bold block uppercase tracking-wider mb-1 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">{{ $topInsight['type'] === 'danger' ? 'warning' : ($topInsight['type'] === 'warning' ? 'error' : 'check_circle') }}</span>
                                {{ $topInsight['title'] }}
                            </span>
                            {{ $topInsight['message'] }}
                        @else
                            <span class="text-primary-fixed-dim font-bold block uppercase tracking-wider mb-1 flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">check_circle</span> SYSTEM OPTIMAL</span>
                            Burn pacing satisfying core parameters. Runway clear for normal operational procedures.
                        @endif
                    </p>
                </div>
            </div>

            <a href="/coach" class="w-full h-12 bg-surface-container-high border border-outline-variant/20 text-on-surface font-label-caps text-[11px] tracking-widest uppercase hover:bg-surface-variant transition-colors flex items-center justify-center gap-2 mt-6">
                <span class="material-symbols-outlined text-[16px]">chat</span>
                <span>Consult Nova AI</span>
            </a>
        </div>
    </div>

    <!-- Secondary Bento Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-gutter">
        <div class="glass-panel p-6 space-y-stack-sm relative overflow-hidden group">
            <div class="flex justify-between items-start">
                <span class="block font-label-caps text-label-caps text-outline tracking-wider">UPCOMING OBLIGATIONS</span>
                <button onclick="document.getElementById('oneTimeBillModal').classList.remove('hidden')" class="w-6 h-6 border border-white/5 flex items-center justify-center hover:bg-surface-variant transition-colors text-outline hover:text-white" title="Add One-Time Obligation">
                    <span class="material-symbols-outlined text-[14px]">add</span>
                </button>
            </div>
            <div class="font-data-mono text-2xl text-error font-bold">{{ format_currency($summary['upcomingBillsTotal'], $currency) }}</div>
            <p class="font-label-caps text-[10px] text-outline-variant tracking-wider uppercase">Due prior to next payday</p>
        </div>

        <div class="glass-panel p-6 space-y-stack-sm relative overflow-hidden group">
            <span class="block font-label-caps text-label-caps text-outline tracking-wider">LIABILITIES TARGETS</span>
            <div class="font-data-mono text-2xl text-secondary-container font-bold">{{ $summary['debtCount'] ?? 0 }} Targets</div>
            <p class="font-label-caps text-[10px] text-outline-variant tracking-wider uppercase">Active debt payoff queue</p>
        </div>

        <div class="glass-panel p-6 space-y-stack-sm relative overflow-hidden group">
            <span class="block font-label-caps text-label-caps text-outline tracking-wider">INJECTION RUNWAY</span>
            <div class="font-data-mono text-2xl text-primary-fixed-dim font-bold">{{ $summary['daysUntilPayday'] }} Days</div>
            <p class="font-label-caps text-[10px] text-outline-variant tracking-wider uppercase">Days until injection cycle reset</p>
        </div>

        <div class="glass-panel p-6 space-y-stack-sm relative overflow-hidden group">
            <span class="block font-label-caps text-label-caps text-outline tracking-wider">MONTHLY BASE FLOW</span>
            <div class="font-data-mono text-2xl text-primary font-bold">{{ format_currency($summary['monthlyIncome'], $currency) }}</div>
            <p class="font-label-caps text-[10px] text-outline-variant tracking-wider uppercase">Gross income sync matrix</p>
        </div>
    </div>

    <!-- Analytics & Ledger Section -->
    <div class="grid grid-cols-12 gap-gutter">
        <!-- Chart -->
        <div class="col-span-12 lg:col-span-8 glass-panel p-8 min-h-[380px] flex flex-col justify-between relative overflow-hidden">
            <div class="flex justify-between items-center mb-6">
                <h3 class="font-label-caps text-label-caps text-secondary flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary-fixed-dim">query_stats</span>
                    <span>BURN PACE TRACKER (30 DAYS)</span>
                </h3>
            </div>
            
            <div class="flex-1 relative w-full h-[260px]">
                <canvas id="spendChart" class="w-full h-full"></canvas>
            </div>
        </div>

        <!-- Ledger -->
        <div class="col-span-12 lg:col-span-4 glass-panel p-8 flex flex-col justify-between relative overflow-hidden">
            <div class="space-y-stack-md">
                <div class="flex justify-between items-center">
                    <h3 class="font-label-caps text-label-caps text-secondary flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary-fixed-dim">receipt_long</span>
                        <span>RECENT SYSTEM TELEMETRY</span>
                    </h3>
                </div>

                <div class="flex gap-2">
                    <button onclick="openTxModal('income')" class="flex-1 h-8 bg-surface-container-high border border-outline-variant/20 text-primary-fixed-dim font-label-caps text-[10px] tracking-widest uppercase hover:bg-surface-variant transition-colors flex items-center justify-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">add_circle</span> INCOME
                    </button>
                    <button onclick="openTxModal('expense')" class="flex-1 h-8 bg-surface-container-high border border-outline-variant/20 text-error font-label-caps text-[10px] tracking-widest uppercase hover:bg-surface-variant transition-colors flex items-center justify-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">remove_circle</span> EXPENSE
                    </button>
                </div>

                @if($recentTransactions->count() > 0)
                    <div class="divide-y divide-white/5 space-y-3 pt-3">
                        @foreach($recentTransactions as $tx)
                            <div class="flex items-center justify-between pt-3 first:pt-0">
                                <div>
                                    <h4 class="font-body-md text-sm font-semibold text-primary truncate max-w-[160px]" title="{{ $tx->description }}">{{ $tx->description ?: 'Transaction' }}</h4>
                                    <span class="font-data-mono text-[10px] text-on-surface-variant uppercase">{{ $tx->category }} · {{ format_date($tx->date) }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="font-data-mono text-sm font-bold {{ $tx->type === 'income' ? 'text-primary-fixed-dim' : 'text-primary' }}">
                                        {{ $tx->type === 'income' ? '+' : '-' }}{{ format_currency($tx->amount, $currency) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-12 text-center space-y-3">
                        <span class="material-symbols-outlined text-[36px] text-outline opacity-40">inventory_2</span>
                        <p class="font-label-caps text-[11px] text-outline uppercase tracking-wider">No transaction logs available.</p>
                    </div>
                @endif
            </div>

            <a href="/transactions" class="w-full h-12 bg-surface-container-high border border-outline-variant/20 text-on-surface font-label-caps text-[11px] tracking-widest uppercase hover:bg-surface-variant transition-colors flex items-center justify-center gap-2 mt-6">
                <span class="material-symbols-outlined text-[16px]">receipt_long</span>
                <span>Access Full Ledger</span>
            </a>
        </div>
    </div>

    <!-- Add One-Time Bill Modal -->
    <div id="oneTimeBillModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-background/80 backdrop-blur-sm" onclick="document.getElementById('oneTimeBillModal').classList.add('hidden')"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md p-6 glass-panel border border-white/10 animate-scale-in shadow-2xl">
            <div class="flex justify-between items-center mb-6">
                <h3 class="font-headline-lg text-lg text-primary font-light">Add One-Time Obligation</h3>
                <button onclick="document.getElementById('oneTimeBillModal').classList.add('hidden')" class="text-outline hover:text-white transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <form action="/bills" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="frequency" value="one_time">
                <input type="hidden" name="category" value="One-Time Obligation">
                
                <div>
                    <label class="block font-label-caps text-[10px] text-outline tracking-wider uppercase mb-1">Obligation Name</label>
                    <input type="text" name="name" required class="w-full h-10 bg-surface-container-lowest border border-outline-variant/30 px-3 font-body-md text-sm text-primary transition-all focus:border-primary-fixed-dim outline-none" placeholder="e.g. Tax Payment">
                </div>
                
                <div>
                    <label class="block font-label-caps text-[10px] text-outline tracking-wider uppercase mb-1">Amount</label>
                    <input type="number" step="0.01" name="amount" required class="w-full h-10 bg-surface-container-lowest border border-outline-variant/30 px-3 font-data-mono text-sm text-primary transition-all focus:border-primary-fixed-dim outline-none" placeholder="0.00">
                </div>

                <div>
                    <label class="block font-label-caps text-[10px] text-outline tracking-wider uppercase mb-1">Due Date</label>
                    <input type="date" name="due_date" required class="w-full h-10 bg-surface-container-lowest border border-outline-variant/30 px-3 font-data-mono text-sm text-primary transition-all focus:border-primary-fixed-dim outline-none" value="{{ \Carbon\Carbon::now()->toDateString() }}">
                </div>

                <div class="pt-4 flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('oneTimeBillModal').classList.add('hidden')" class="h-10 px-4 font-label-caps text-[11px] tracking-widest uppercase text-outline hover:text-white transition-colors">Cancel</button>
                    <button type="submit" class="h-10 px-6 bg-primary-fixed text-on-primary-fixed font-label-caps text-[11px] tracking-widest uppercase hover:brightness-110 active:scale-95 transition-all">Add Obligation</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Transaction Modal -->
    <div id="transactionModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-background/80 backdrop-blur-sm" onclick="document.getElementById('transactionModal').classList.add('hidden')"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md p-6 glass-panel border border-white/10 animate-scale-in shadow-2xl">
            <div class="flex justify-between items-center mb-6">
                <h3 id="txModalTitle" class="font-headline-lg text-lg text-primary font-light">Add Transaction</h3>
                <button onclick="document.getElementById('transactionModal').classList.add('hidden')" class="text-outline hover:text-white transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            
            <form action="/transactions" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="type" id="txType" value="expense">
                
                <div>
                    <label class="block font-label-caps text-[10px] text-outline tracking-wider uppercase mb-1">Account</label>
                    <select name="account_id" required class="w-full h-10 bg-surface-container-lowest border border-outline-variant/30 px-3 font-body-md text-sm text-primary transition-all focus:border-primary-fixed-dim outline-none appearance-none">
                        @foreach($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->name }} ({{ format_currency($account->balance, $currency) }})</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block font-label-caps text-[10px] text-outline tracking-wider uppercase mb-1">Description</label>
                    <input type="text" name="description" required class="w-full h-10 bg-surface-container-lowest border border-outline-variant/30 px-3 font-body-md text-sm text-primary transition-all focus:border-primary-fixed-dim outline-none" placeholder="e.g. Groceries">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-label-caps text-[10px] text-outline tracking-wider uppercase mb-1">Amount</label>
                        <input type="number" step="0.01" name="amount" required class="w-full h-10 bg-surface-container-lowest border border-outline-variant/30 px-3 font-data-mono text-sm text-primary transition-all focus:border-primary-fixed-dim outline-none" placeholder="0.00">
                    </div>
                    <div>
                        <label class="block font-label-caps text-[10px] text-outline tracking-wider uppercase mb-1">Date</label>
                        <input type="date" name="date" required class="w-full h-10 bg-surface-container-lowest border border-outline-variant/30 px-3 font-data-mono text-sm text-primary transition-all focus:border-primary-fixed-dim outline-none" value="{{ \Carbon\Carbon::now()->toDateString() }}">
                    </div>
                </div>

                <div>
                    <label class="block font-label-caps text-[10px] text-outline tracking-wider uppercase mb-1">Category</label>
                    <select name="category" required class="w-full h-10 bg-surface-container-lowest border border-outline-variant/30 px-3 font-body-md text-sm text-primary transition-all focus:border-primary-fixed-dim outline-none appearance-none">
                        <option value="Food & Dining">Food & Dining</option>
                        <option value="Shopping">Shopping</option>
                        <option value="Housing">Housing</option>
                        <option value="Transportation">Transportation</option>
                        <option value="Vehicle">Vehicle</option>
                        <option value="Entertainment">Entertainment</option>
                        <option value="Communication">Communication</option>
                        <option value="Financial Expenses">Financial Expenses</option>
                        <option value="Investments">Investments</option>
                        <option value="Income">Income</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="pt-4 flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('transactionModal').classList.add('hidden')" class="h-10 px-4 font-label-caps text-[11px] tracking-widest uppercase text-outline hover:text-white transition-colors">Cancel</button>
                    <button type="submit" id="txSubmitBtn" class="h-10 px-6 bg-error text-white font-label-caps text-[11px] tracking-widest uppercase hover:brightness-110 active:scale-95 transition-all">Add Expense</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function openTxModal(type) {
        document.getElementById('transactionModal').classList.remove('hidden');
        document.getElementById('txType').value = type;
        if (type === 'income') {
            document.getElementById('txModalTitle').innerText = 'Add Income';
            document.getElementById('txSubmitBtn').innerText = 'Add Income';
            document.getElementById('txSubmitBtn').className = 'h-10 px-6 bg-primary-fixed text-on-primary-fixed font-label-caps text-[11px] tracking-widest uppercase hover:brightness-110 active:scale-95 transition-all';
        } else {
            document.getElementById('txModalTitle').innerText = 'Add Expense';
            document.getElementById('txSubmitBtn').innerText = 'Add Expense';
            document.getElementById('txSubmitBtn').className = 'h-10 px-6 bg-error text-white font-label-caps text-[11px] tracking-widest uppercase hover:brightness-110 active:scale-95 transition-all';
        }
    }

    document.addEventListener("DOMContentLoaded", function () {
        const ctx = document.getElementById('spendChart').getContext('2d');
        const chartData = @json($chartData);
        
        const labels = chartData.map(d => d.displayDate);
        const data = chartData.map(d => d.spend);

        // Grid lines gradient styling
        let fillGrad = ctx.createLinearGradient(0, 0, 0, 260);
        fillGrad.addColorStop(0, 'rgba(0, 226, 144, 0.15)');
        fillGrad.addColorStop(1, 'rgba(0, 226, 144, 0.0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Daily Expenditures',
                    data: data,
                    borderColor: '#00e290',
                    backgroundColor: fillGrad,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#00e290',
                    pointHoverBorderColor: '#ffffff',
                    pointHoverBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#111318',
                        titleColor: '#e2e2e8',
                        bodyColor: '#e2e2e8',
                        borderColor: 'rgba(255, 255, 255, 0.08)',
                        borderWidth: 1,
                        padding: 12,
                        cornerRadius: 0,
                        displayColors: false,
                        titleFont: {
                            family: 'JetBrains Mono',
                            size: 11
                        },
                        bodyFont: {
                            family: 'JetBrains Mono',
                            size: 12
                        },
                        callbacks: {
                            label: function(context) {
                                let label = '';
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('en-US', { style: 'currency', currency: '{{ $currency }}' }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#849588',
                            font: {
                                size: 10,
                                family: 'JetBrains Mono'
                            },
                            maxTicksLimit: 6
                        }
                    },
                    y: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.04)',
                            drawBorder: false
                        },
                        ticks: {
                            color: '#849588',
                            font: {
                                size: 10,
                                family: 'JetBrains Mono'
                            },
                            callback: function(value) {
                                if (value >= 1000) {
                                    return (value / 1000).toFixed(0) + 'k';
                                }
                                return value;
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endsection
