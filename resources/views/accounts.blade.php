@extends('layouts.app')

@section('title', 'NEUROSPEND | Accounts')
@section('page_title', 'ACCOUNTS')

@section('content')
<div class="space-y-gutter animate-fade-in pb-10">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pt-2">
        <div class="space-y-unit">
            <h1 class="font-display-lg text-3xl text-primary font-light flex items-center gap-3 tracking-tight">
                <span class="material-symbols-outlined text-primary-fixed-dim text-[32px]">account_balance_wallet</span>
                <span>Account Command</span>
            </h1>
            <p class="font-body-md text-on-surface-variant uppercase tracking-wider text-xs">Manage autonomous financial streams, execute internal transfers, and monitor network liquidity</p>
        </div>
    </div>

    <!-- Total Liquidity Bento Hero Card -->
    <div class="glass-panel p-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-6 relative overflow-hidden group">
        <div class="shimmer-bg absolute inset-0 pointer-events-none"></div>
        <div class="absolute -right-16 -top-16 w-64 h-64 bg-primary-fixed-dim/10 rounded-full blur-[80px] pointer-events-none"></div>
        
        <div class="relative z-10 flex items-center gap-6">
            <div class="w-16 h-16 rounded-none bg-primary-fixed-dim/10 border border-primary-fixed-dim/20 flex items-center justify-center hidden sm:flex">
                <span class="material-symbols-outlined text-primary-fixed-dim text-[32px] animate-pulse">payments</span>
            </div>
            <div class="space-y-1">
                <span class="flex items-center gap-2 text-primary-fixed-dim font-label-caps text-label-caps tracking-widest uppercase">
                    TOTAL NETWORK LIQUIDITY
                </span>
                <h2 class="font-display-lg text-4xl sm:text-[48px] text-white tracking-tighter leading-none">
                    {{ format_currency($accounts->sum('balance'), $currency) }}
                </h2>
                <p class="font-label-caps text-[10px] text-outline tracking-wider uppercase flex items-center gap-2 mt-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-primary-fixed-dim animate-ping"></span>
                    Aggregated across {{ $accounts->count() }} active ledger nodes
                </p>
            </div>
        </div>
        
        <button onclick="toggleCreateForm()" class="h-12 px-6 bg-primary-fixed text-on-primary-fixed font-label-caps text-[11px] tracking-widest uppercase hover:brightness-110 active:scale-95 transition-all flex items-center gap-2 relative z-10 whitespace-nowrap">
            <span class="material-symbols-outlined text-[16px]">add</span>
            <span>Initialize Stream</span>
        </button>
    </div>

    <!-- Create Account Drawer (Collapsible) -->
    <div id="createAccountForm" class="glass-panel p-8 relative z-20 hidden animate-scale-in">
        <button onclick="toggleCreateForm()" class="absolute right-6 top-6 text-on-surface-variant hover:text-white transition-colors">
            <span class="material-symbols-outlined">close</span>
        </button>
        <h3 class="font-headline-lg text-lg flex items-center gap-2 mb-6 text-white font-light">
            <span class="material-symbols-outlined text-primary-fixed-dim">account_balance</span>
            <span>STREAM INITIALIZATION VECTOR</span>
        </h3>
        <form method="POST" action="/accounts" class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @csrf
            <div class="space-y-unit">
                <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Ledger Identifier</label>
                <input type="text" name="name" required class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 px-4 font-data-mono text-data-mono text-primary rounded-none transition-all input-focus-ring" placeholder="e.g. Neon Target Save">
            </div>
            <div class="space-y-unit">
                <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Financial Institution</label>
                <input type="text" name="institution" required class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 px-4 font-data-mono text-data-mono text-primary rounded-none transition-all input-focus-ring" placeholder="e.g. Chase Bank or Cash">
            </div>
            <div class="space-y-unit">
                <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Starting Balance</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 font-data-mono text-primary-fixed-dim font-bold">$</span>
                    <input type="number" name="balance" required step="0.01" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 pl-10 pr-4 font-data-mono text-data-mono text-primary rounded-none transition-all input-focus-ring" placeholder="0.00">
                </div>
            </div>
            <div class="md:col-span-2 space-y-unit">
                <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Account Vector (Type)</label>
                <select name="type" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 px-4 font-data-mono text-data-mono text-primary rounded-none transition-all focus:ring-2 focus:ring-primary-fixed-dim focus:border-primary-fixed-dim">
                    <option value="checking" class="bg-surface-container-lowest">Checking</option>
                    <option value="savings" class="bg-surface-container-lowest">Savings</option>
                    <option value="credit" class="bg-surface-container-lowest">Credit Card</option>
                    <option value="cash" class="bg-surface-container-lowest">Cash / Liquid</option>
                    <option value="investment" class="bg-surface-container-lowest">Investment</option>
                    <option value="other" class="bg-surface-container-lowest">Other Assets</option>
                </select>
            </div>
            <div class="md:col-span-1 flex items-end">
                <button type="submit" class="w-full h-12 bg-primary-fixed text-on-primary-fixed font-label-caps text-[11px] tracking-widest uppercase hover:brightness-110 active:scale-95 transition-all">
                    Map Account
                </button>
            </div>
        </form>
    </div>

    <!-- Content Columns -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter">
        
        <!-- Active database clusters (8 columns) -->
        <div class="lg:col-span-8 space-y-4">
            <h3 class="font-label-caps text-label-caps text-secondary tracking-widest uppercase mb-4">ACTIVE DATABASE CLUSTERS</h3>
            
            @if($accounts->count() === 0)
                <div class="glass-panel p-16 flex flex-col items-center justify-center text-center space-y-4">
                    <span class="material-symbols-outlined text-[48px] text-outline opacity-40">inventory_2</span>
                    <h3 class="font-headline-lg text-xl text-primary font-light">No active streams detected.</h3>
                    <p class="font-body-md text-xs text-on-surface-variant max-w-sm">NOVA requires at least one active ledger to calculate safe spending runway vectors. Initialize your first node below.</p>
                    <button onclick="toggleCreateForm()" class="h-12 px-6 bg-primary-fixed text-on-primary-fixed font-label-caps text-[11px] tracking-widest uppercase hover:brightness-110 active:scale-95 transition-all">Initialize First Account</button>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-gutter">
                    @foreach($accounts as $index => $acc)
                        <div class="glass-panel p-6 relative group hover:border-primary-fixed-dim/30 transition-all duration-300">
                            
                            <!-- Delete button -->
                            <form action="/accounts/{{ $acc->id }}" method="POST" class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-all z-10" onsubmit="return confirm('Are you sure you want to permanently delete this account? Any associated transactions will lose their relation.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-on-surface-variant hover:text-error hover:bg-white/5 transition-all">
                                    <span class="material-symbols-outlined text-[16px]">delete</span>
                                </button>
                            </form>
                            
                            <div class="w-12 h-12 bg-primary-fixed-dim/10 border border-primary-fixed-dim/20 text-primary-fixed-dim flex items-center justify-center mb-6">
                                <span class="material-symbols-outlined text-[24px]">
                                    {{ $acc->type === 'cash' ? 'payments' : ($acc->type === 'savings' ? 'savings' : 'account_balance') }}
                                </span>
                            </div>
                            
                            <h4 class="font-title-md text-lg text-primary truncate pr-8 font-semibold leading-none">{{ $acc->name }}</h4>
                            <div class="flex items-center gap-2 mt-2">
                                <span class="font-label-caps text-[9px] uppercase tracking-wider bg-surface-container-high px-2 py-0.5 text-outline">
                                    {{ $acc->type }}
                                </span>
                                <span class="font-body-md text-xs text-on-surface-variant truncate">— {{ $acc->institution }}</span>
                            </div>
                            
                            <div class="pt-4 border-t border-white/5 mt-6">
                                <p class="font-label-caps text-[9px] text-outline mb-1 uppercase tracking-widest">Current Balance</p>
                                <p class="font-data-mono text-2xl font-bold {{ $acc->balance < 0 ? 'text-error' : 'text-primary' }}">
                                    {{ format_currency($acc->balance, $currency) }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Transfer Node (4 columns) -->
        <div class="lg:col-span-4 glass-panel p-8 h-fit space-y-6">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-secondary-container text-[28px]">swap_horiz</span>
                <h3 class="font-headline-lg text-lg text-primary font-light">Internal Transfer</h3>
            </div>

            <form method="POST" action="/accounts/transfer" class="space-y-4">
                @csrf
                <div class="space-y-unit">
                    <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Source (From)</label>
                    <select required name="from_account_id" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 px-4 font-data-mono text-data-mono text-primary rounded-none focus:ring-2 focus:ring-primary-fixed-dim focus:border-primary-fixed-dim">
                        <option value="" disabled selected class="bg-surface-container-lowest">Select Source...</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" class="bg-surface-container-lowest">{{ $acc->name }} ({{ format_currency($acc->balance, $currency) }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-unit">
                    <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Destination (To)</label>
                    <select required name="to_account_id" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 px-4 font-data-mono text-data-mono text-primary rounded-none focus:ring-2 focus:ring-primary-fixed-dim focus:border-primary-fixed-dim">
                        <option value="" disabled selected class="bg-surface-container-lowest">Select Target...</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" class="bg-surface-container-lowest">{{ $acc->name }} ({{ format_currency($acc->balance, $currency) }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-unit">
                        <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Transfer Mass</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 font-data-mono text-primary-fixed-dim font-bold">$</span>
                            <input type="number" required min="0.01" step="0.01" name="amount" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 pl-10 pr-4 font-data-mono text-data-mono text-primary rounded-none transition-all input-focus-ring" placeholder="0.00">
                        </div>
                    </div>
                    <div class="space-y-unit">
                        <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Network Fee</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 font-data-mono text-outline font-bold">$</span>
                            <input type="number" min="0" step="0.01" name="fee" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 pl-10 pr-4 font-data-mono text-data-mono text-primary rounded-none transition-all input-focus-ring" placeholder="0.00">
                        </div>
                    </div>
                </div>

                <div class="space-y-unit">
                    <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Memo</label>
                    <input type="text" name="memo" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 px-4 font-data-mono text-data-mono text-primary rounded-none transition-all input-focus-ring" placeholder="Internal Transfer" value="Internal Transfer">
                </div>

                <button {{ $accounts->count() < 2 ? 'disabled' : '' }} type="submit" class="w-full h-12 bg-primary-fixed text-on-primary-fixed font-label-caps text-[11px] tracking-widest uppercase hover:brightness-110 active:scale-95 transition-all mt-4 disabled:opacity-50 disabled:cursor-not-allowed">
                    Execute Internal Transfer
                </button>
                
                @if($accounts->count() < 2)
                    <div class="border border-warning/20 bg-warning/5 p-4 flex gap-3 items-start mt-4">
                        <span class="material-symbols-outlined text-warning text-[18px] shrink-0">warning</span>
                        <p class="font-body-md text-xs text-on-surface-variant">Internal transfers require at least two active database streams.</p>
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function toggleCreateForm() {
        const form = document.getElementById('createAccountForm');
        form.classList.toggle('hidden');
    }
</script>
@endsection
