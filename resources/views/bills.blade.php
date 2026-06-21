@extends('layouts.app')

@section('title', 'NEUROSPEND | Bills Manager')
@section('page_title', 'BILLS MANAGER')

@section('content')
<div class="space-y-gutter animate-fade-in pb-10">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pt-2">
        <div class="space-y-unit">
            <h1 class="font-display-lg text-3xl text-primary font-light flex items-center gap-3 tracking-tight">
                <span class="material-symbols-outlined text-primary-fixed-dim text-[32px]">receipt</span>
                <span>Bills Manager</span>
            </h1>
            <p class="font-body-md text-on-surface-variant uppercase tracking-wider text-xs">Track strategic recurring outflows and configure variable usage multipliers</p>
        </div>
        <button onclick="toggleAddBillForm()" class="h-12 px-6 bg-primary-fixed text-on-primary-fixed font-label-caps text-[11px] tracking-widest uppercase hover:brightness-110 active:scale-95 transition-all flex items-center gap-2 shadow-[0_0_20px_rgba(82,255,172,0.1)]">
            <span class="material-symbols-outlined text-[16px]">add</span>
            <span>Add Bill</span>
        </button>
    </div>

    <!-- Add Bill Form (Collapsible command card) -->
    <div id="addBillCard" class="glass-panel p-8 hidden relative animate-scale-in">
        <button onclick="toggleAddBillForm()" class="absolute right-6 top-6 text-on-surface-variant hover:text-white transition-colors">
            <span class="material-symbols-outlined">close</span>
        </button>
        
        <h3 class="font-headline-lg text-lg flex items-center gap-2 mb-6 text-white font-light">
            <span class="material-symbols-outlined text-primary-fixed-dim">post_add</span>
            <span>NEW RECURRING OBLIGATION</span>
        </h3>
        
        <form method="POST" action="/bills" class="space-y-6">
            @csrf
            
            <div class="space-y-unit">
                <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Contribution Logic</label>
                <div class="flex gap-2 p-1 bg-surface-container-lowest border border-white/5 w-fit">
                    <button type="button" id="fixedBtn" onclick="setVariable(false)" class="h-10 px-4 flex items-center justify-center font-label-caps text-[10px] uppercase tracking-wider transition-all bg-primary-fixed text-on-primary-fixed font-bold">Fixed Monthly</button>
                    <button type="button" id="variableBtn" onclick="setVariable(true)" class="h-10 px-4 flex items-center justify-center font-label-caps text-[10px] uppercase tracking-wider transition-all text-on-surface-variant hover:text-white">Usage Based (Avg)</button>
                </div>
                <input type="hidden" name="is_variable" id="isVariableInput" value="0">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-unit">
                    <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Bill Name</label>
                    <input type="text" name="name" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 px-4 font-data-mono text-data-mono text-primary rounded-none transition-all input-focus-ring" placeholder="e.g. Electric or Gas" required>
                </div>
                
                <div class="space-y-unit">
                    <label class="font-label-caps text-label-caps text-on-surface-variant ml-1" id="amountLabel">Exact Amount</label>
                    <input type="number" step="0.01" name="amount" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 px-4 font-data-mono text-data-mono text-primary rounded-none transition-all input-focus-ring" placeholder="0.00" required>
                </div>

                <div class="space-y-unit">
                    <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Category</label>
                    <select name="category" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 px-4 font-data-mono text-data-mono text-primary rounded-none focus:ring-2 focus:ring-primary-fixed-dim focus:border-primary-fixed-dim" required>
                        <option value="Utilities" class="bg-surface-container-lowest">Utilities</option>
                        <option value="Housing & Rent" class="bg-surface-container-lowest">Housing & Rent</option>
                        <option value="Subscriptions" class="bg-surface-container-lowest">Subscriptions</option>
                        <option value="Insurance" class="bg-surface-container-lowest">Insurance</option>
                        <option value="Education" class="bg-surface-container-lowest">Education</option>
                        <option value="Healthcare" class="bg-surface-container-lowest">Healthcare</option>
                        <option value="Other Obligations" class="bg-surface-container-lowest">Other Obligations</option>
                    </select>
                </div>

                <div class="space-y-unit">
                    <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Frequency</label>
                    <select name="frequency" id="addFrequency" onchange="onFrequencyChange(this.value)" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 px-4 font-data-mono text-data-mono text-primary rounded-none focus:ring-2 focus:ring-primary-fixed-dim focus:border-primary-fixed-dim" required>
                        <option value="monthly" class="bg-surface-container-lowest">Monthly</option>
                        <option value="weekly" class="bg-surface-container-lowest">Weekly</option>
                        <option value="yearly" class="bg-surface-container-lowest">Yearly</option>
                        <option value="one_time" class="bg-surface-container-lowest">One-Time</option>
                    </select>
                </div>

                <div class="space-y-unit hidden" id="addDueMonthWrap">
                    <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Due Month</label>
                    <select name="due_month" id="addDueMonth" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 px-4 font-data-mono text-data-mono text-primary rounded-none focus:ring-2 focus:ring-primary-fixed-dim focus:border-primary-fixed-dim">
                        <option value="1" class="bg-surface-container-lowest">January</option>
                        <option value="2" class="bg-surface-container-lowest">February</option>
                        <option value="3" class="bg-surface-container-lowest">March</option>
                        <option value="4" class="bg-surface-container-lowest">April</option>
                        <option value="5" class="bg-surface-container-lowest">May</option>
                        <option value="6" class="bg-surface-container-lowest">June</option>
                        <option value="7" class="bg-surface-container-lowest">July</option>
                        <option value="8" class="bg-surface-container-lowest">August</option>
                        <option value="9" class="bg-surface-container-lowest">September</option>
                        <option value="10" class="bg-surface-container-lowest">October</option>
                        <option value="11" class="bg-surface-container-lowest">November</option>
                        <option value="12" class="bg-surface-container-lowest">December</option>
                    </select>
                </div>

                <div class="space-y-unit" id="addDueDayWrap">
                    <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Due Day of Month (1-31)</label>
                    <input type="number" name="due_day" min="1" max="31" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 px-4 font-data-mono text-data-mono text-primary rounded-none transition-all input-focus-ring" value="1">
                </div>

                <div class="space-y-unit hidden" id="addDueDateWrap">
                    <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Specific Due Date</label>
                    <input type="date" name="due_date" id="addDueDate" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 px-4 font-data-mono text-data-mono text-primary rounded-none transition-all input-focus-ring" value="{{ \Carbon\Carbon::now()->toDateString() }}">
                </div>

                <div class="flex items-center space-x-3 pt-6">
                    <input type="checkbox" name="is_auto_pay" id="isAutoPay" value="1" class="w-5 h-5 bg-surface-container-lowest border border-outline-variant/30 text-primary-fixed-dim focus:ring-primary-fixed-dim rounded-none">
                    <label for="isAutoPay" class="font-label-caps text-[10px] text-on-surface-variant uppercase tracking-wider cursor-pointer">Auto-Pay Enabled</label>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" onclick="toggleAddBillForm()" class="h-12 px-6 bg-surface-container-high border border-outline-variant/20 text-on-surface font-label-caps text-[11px] tracking-widest uppercase hover:bg-surface-variant transition-colors">Cancel</button>
                <button type="submit" class="h-12 px-8 bg-primary-fixed text-on-primary-fixed font-label-caps text-[11px] tracking-widest uppercase hover:brightness-110 active:scale-95 transition-all">Save Obligation</button>
            </div>
        </form>
    </div>

    <!-- Active Bills List -->
    @if($bills->count() === 0)
        <div class="glass-panel p-16 flex flex-col items-center justify-center text-center space-y-4">
            <span class="material-symbols-outlined text-[48px] text-outline opacity-40">clinical_notes</span>
            <h3 class="font-headline-lg text-xl text-primary font-light">No obligations mapped.</h3>
            <p class="font-body-md text-xs text-on-surface-variant max-w-sm">Active obligations tracked here will automatically calibrate the tactical Safe-to-Spend runway.</p>
            <button onclick="toggleAddBillForm()" class="h-12 px-6 bg-primary-fixed text-on-primary-fixed font-label-caps text-[11px] tracking-widest uppercase hover:brightness-110 active:scale-95 transition-all">Add Your First Bill</button>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-gutter">
            @foreach($bills as $bill)
                <div class="glass-panel p-6 flex flex-col justify-between hover:border-primary-fixed-dim/30 transition-all duration-300 relative group">
                    
                    <!-- Action buttons -->
                    <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1">
                        <button onclick='openEditBillModal(@json($bill))' class="p-1 text-on-surface-variant hover:text-primary-fixed-dim transition-colors" title="Edit Bill">
                            <span class="material-symbols-outlined text-[16px]">edit</span>
                        </button>
                        <form action="/bills/{{ $bill->id }}" method="POST" onsubmit="return confirm('Remove this recurring bill configuration?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1 text-on-surface-variant hover:text-error transition-colors" title="Delete Bill">
                                <span class="material-symbols-outlined text-[16px]">delete</span>
                            </button>
                        </form>
                    </div>

                    <div class="space-y-6">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-primary-fixed-dim/10 border border-primary-fixed-dim/20 text-primary-fixed-dim flex items-center justify-center">
                                <span class="material-symbols-outlined text-[24px]">receipt</span>
                            </div>
                            <div>
                                <h3 class="font-title-md text-base text-primary font-semibold leading-none">{{ $bill->name }}</h3>
                                <span class="font-label-caps text-[9px] text-outline uppercase tracking-wider mt-1 block">{{ $bill->category }}</span>
                            </div>
                        </div>

                        <!-- Amount Display -->
                        <div class="space-y-unit">
                            <p class="font-label-caps text-[9px] text-outline uppercase tracking-widest">Cost Projection</p>
                            <div class="font-data-mono text-3xl font-bold text-primary flex items-baseline gap-1">
                                {{ format_currency($bill->amount, $currency) }}
                                @if($bill->frequency === 'one_time')
                                    <span class="font-body-md text-xs text-on-surface-variant font-light">/One-Time</span>
                                @else
                                    <span class="font-body-md text-xs text-on-surface-variant font-light">/{{ substr($bill->frequency, 0, 3) }}</span>
                                @endif
                            </div>
                            @if($bill->is_variable)
                                <p class="font-label-caps text-[9px] text-primary-fixed-dim uppercase tracking-wider flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[12px]">analytics</span>
                                    <span>Avg: {{ format_currency($bill->average, $currency) }} (Usage-Based)</span>
                                </p>
                            @else
                                <p class="font-label-caps text-[9px] text-outline-variant uppercase tracking-wider">{{ $bill->frequency === 'one_time' ? 'Fixed Obligation' : 'Fixed Monthly' }}</p>
                            @endif
                        </div>

                        <div class="pt-4 border-t border-white/5 space-y-2 text-xs font-body-md font-light text-on-surface-variant">
                            <div class="flex justify-between">
                                <span>Next Due:</span>
                                <span class="font-data-mono text-primary font-bold">{{ format_date($bill->next_due_date) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span>Auto-Pay Status:</span>
                                <button onclick="toggleAutoPay({{ $bill->id }}, this)" class="h-6 px-2 border font-label-caps text-[9px] tracking-wider uppercase transition-colors {{ $bill->is_auto_pay ? 'bg-primary-fixed-dim/15 border-primary-fixed-dim/30 text-primary-fixed-dim' : 'bg-surface-container-high border-white/5 text-outline' }}">
                                    {{ $bill->is_auto_pay ? 'Enabled' : 'Disabled' }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-white/5 flex gap-2">
                        <button onclick="openPayModal({{ $bill->id }}, '{{ $bill->name }}', {{ $bill->amount }})" class="flex-1 h-10 bg-primary-fixed text-on-primary-fixed font-label-caps text-[10px] tracking-widest uppercase hover:brightness-110 active:scale-95 transition-all">
                            Log Payment
                        </button>
                        @if($bill->history->count() > 0)
                            <button onclick="toggleHistory({{ $bill->id }})" class="h-10 px-3 bg-surface-container-high border border-outline-variant/20 text-on-surface font-label-caps hover:bg-surface-variant transition-colors" title="Payment Telemetry History">
                                <span class="material-symbols-outlined text-[16px] mt-0.5">history</span>
                            </button>
                        @endif
                    </div>

                    <!-- Payment history panel (collapsible inside card) -->
                    <div id="history-{{ $bill->id }}" class="hidden mt-4 pt-4 border-t border-white/5 space-y-2 text-left animate-scale-in">
                        <h4 class="font-label-caps text-[9px] text-outline tracking-wider uppercase mb-2">Payment Log</h4>
                        @foreach($bill->history as $h)
                            <div class="flex justify-between text-xs py-1 border-b border-white/5 last:border-0 font-body-md font-light text-on-surface-variant">
                                <span class="font-data-mono">{{ format_date($h->date) }}</span>
                                <span class="font-data-mono font-bold text-primary">{{ format_currency($h->amount, $currency) }}</span>
                            </div>
                        @endforeach
                    </div>

                </div>
            @endforeach
        </div>
    @endif
</div>

<!-- Pay Modal -->
<div id="payModal" class="fixed inset-0 bg-black/80 z-[999] hidden flex items-center justify-center p-4 backdrop-blur-xl animate-fade-in transition-all duration-300">
    <div class="glass-panel max-w-md w-full p-8 shadow-2xl relative">
        <button onclick="closePayModal()" class="absolute right-4 top-4 text-on-surface-variant hover:text-white transition-colors">
            <span class="material-symbols-outlined">close</span>
        </button>
        
        <h3 class="font-headline-lg text-xl text-primary mb-6 flex items-center gap-3 font-light">
            <span class="material-symbols-outlined text-primary-fixed-dim text-[24px]">payments</span>
            <span>Record Payment Event</span>
        </h3>
        
        <form id="payModalForm" method="POST" action="" class="space-y-6">
            @csrf
            <div class="space-y-4">
                <div class="space-y-unit">
                    <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Bill Name</label>
                    <input type="text" id="modalBillName" class="w-full h-12 bg-surface-container-high border border-outline-variant/30 px-4 font-data-mono text-data-mono text-primary rounded-none transition-all cursor-not-allowed" disabled>
                </div>

                <div class="space-y-unit">
                    <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Liquidity Account Node (Pay From)</label>
                    <select required name="account_id" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 px-4 font-data-mono text-data-mono text-primary rounded-none focus:ring-2 focus:ring-primary-fixed-dim focus:border-primary-fixed-dim">
                        <option value="" disabled selected class="bg-surface-container-lowest">Select Account...</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" class="bg-surface-container-lowest">{{ $acc->name }} ({{ format_currency($acc->balance, $currency) }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-unit">
                    <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Amount Paid</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 font-data-mono text-primary-fixed-dim font-bold">$</span>
                        <input type="number" required step="0.01" min="0.01" name="pay_amount" id="modalPayAmount" class="w-full h-14 bg-surface-container-lowest border border-outline-variant/30 pl-10 pr-4 font-data-mono text-data-mono text-primary rounded-none transition-all input-focus-ring">
                    </div>
                </div>

                <div class="space-y-unit">
                    <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Billing Period (Optional)</label>
                    <input type="text" name="pay_period" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 px-4 font-data-mono text-data-mono text-primary rounded-none transition-all input-focus-ring" placeholder="e.g. May 2026">
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closePayModal()" class="h-12 px-6 bg-surface-container-high border border-outline-variant/20 text-on-surface font-label-caps text-[11px] tracking-widest uppercase hover:bg-surface-variant transition-colors">Cancel</button>
                <button type="submit" class="h-12 px-8 bg-primary-fixed text-on-primary-fixed font-label-caps text-[11px] tracking-widest uppercase hover:brightness-110 active:scale-95 transition-all">Confirm Payment</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Bill Modal -->
<div id="editBillModal" class="fixed inset-0 bg-black/80 z-[999] hidden flex items-center justify-center p-4 backdrop-blur-xl animate-fade-in transition-all duration-300">
    <div class="glass-panel max-w-md w-full p-8 shadow-2xl relative">
        <button onclick="closeEditBillModal()" class="absolute right-4 top-4 text-on-surface-variant hover:text-white transition-colors">
            <span class="material-symbols-outlined">close</span>
        </button>
        
        <h3 class="font-headline-lg text-xl text-primary mb-6 flex items-center gap-3 font-light">
            <span class="material-symbols-outlined text-primary-fixed-dim text-[24px]">edit_document</span>
            <span>Edit Recurring Obligation</span>
        </h3>
        
        <form id="editBillModalForm" method="POST" action="" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div class="space-y-unit">
                <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Contribution Logic</label>
                <div class="flex gap-2 p-1 bg-surface-container-lowest border border-white/5 w-fit">
                    <button type="button" id="editFixedBtn" onclick="setEditVariable(false)" class="h-10 px-4 flex items-center justify-center font-label-caps text-[10px] uppercase tracking-wider transition-all bg-primary-fixed text-on-primary-fixed font-bold">Fixed Monthly</button>
                    <button type="button" id="editVariableBtn" onclick="setEditVariable(true)" class="h-10 px-4 flex items-center justify-center font-label-caps text-[10px] uppercase tracking-wider transition-all text-on-surface-variant hover:text-white">Usage Based (Avg)</button>
                </div>
                <input type="hidden" name="is_variable" id="editIsVariableInput" value="0">
            </div>

            <div class="space-y-4">
                <div class="space-y-unit">
                    <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Bill Name</label>
                    <input type="text" name="name" id="editBillName" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 px-4 font-data-mono text-data-mono text-primary rounded-none transition-all input-focus-ring" required>
                </div>

                <div class="space-y-unit">
                    <label class="font-label-caps text-label-caps text-on-surface-variant ml-1" id="editAmountLabel">Exact Amount</label>
                    <input type="number" step="0.01" name="amount" id="editBillAmount" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 px-4 font-data-mono text-data-mono text-primary rounded-none transition-all input-focus-ring" required>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-unit">
                        <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Category</label>
                        <select name="category" id="editBillCategory" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 px-3 font-data-mono text-data-mono text-primary rounded-none focus:ring-2 focus:ring-primary-fixed-dim focus:border-primary-fixed-dim" required>
                            <option value="Utilities" class="bg-surface-container-lowest">Utilities</option>
                            <option value="Housing & Rent" class="bg-surface-container-lowest">Housing & Rent</option>
                            <option value="Subscriptions" class="bg-surface-container-lowest">Subscriptions</option>
                            <option value="Insurance" class="bg-surface-container-lowest">Insurance</option>
                            <option value="Education" class="bg-surface-container-lowest">Education</option>
                            <option value="Healthcare" class="bg-surface-container-lowest">Healthcare</option>
                            <option value="Other Obligations" class="bg-surface-container-lowest">Other Obligations</option>
                        </select>
                    </div>

                    <div class="space-y-unit">
                        <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Frequency</label>
                        <select name="frequency" id="editBillFrequency" onchange="onEditFrequencyChange(this.value)" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 px-3 font-data-mono text-data-mono text-primary rounded-none focus:ring-2 focus:ring-primary-fixed-dim focus:border-primary-fixed-dim" required>
                            <option value="monthly" class="bg-surface-container-lowest">Monthly</option>
                            <option value="weekly" class="bg-surface-container-lowest">Weekly</option>
                            <option value="yearly" class="bg-surface-container-lowest">Yearly</option>
                            <option value="one_time" class="bg-surface-container-lowest">One-Time</option>
                        </select>
                    </div>
                </div>

                <div class="space-y-unit hidden" id="editDueMonthWrap">
                    <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Due Month</label>
                    <select name="due_month" id="editDueMonth" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 px-4 font-data-mono text-data-mono text-primary rounded-none focus:ring-2 focus:ring-primary-fixed-dim focus:border-primary-fixed-dim">
                        <option value="1" class="bg-surface-container-lowest">January</option>
                        <option value="2" class="bg-surface-container-lowest">February</option>
                        <option value="3" class="bg-surface-container-lowest">March</option>
                        <option value="4" class="bg-surface-container-lowest">April</option>
                        <option value="5" class="bg-surface-container-lowest">May</option>
                        <option value="6" class="bg-surface-container-lowest">June</option>
                        <option value="7" class="bg-surface-container-lowest">July</option>
                        <option value="8" class="bg-surface-container-lowest">August</option>
                        <option value="9" class="bg-surface-container-lowest">September</option>
                        <option value="10" class="bg-surface-container-lowest">October</option>
                        <option value="11" class="bg-surface-container-lowest">November</option>
                        <option value="12" class="bg-surface-container-lowest">December</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-unit" id="editDueDayWrap">
                        <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Due Day (1-31)</label>
                        <input type="number" name="due_day" id="editBillDueDay" min="1" max="31" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 px-4 font-data-mono text-data-mono text-primary rounded-none transition-all input-focus-ring">
                    </div>

                    <div class="space-y-unit hidden" id="editDueDateWrap">
                        <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Specific Due Date</label>
                        <input type="date" name="due_date" id="editDueDate" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 px-4 font-data-mono text-data-mono text-primary rounded-none transition-all input-focus-ring" value="{{ \Carbon\Carbon::now()->toDateString() }}">
                    </div>

                    <div class="flex items-center space-x-2 pt-6">
                        <input type="checkbox" name="is_auto_pay" id="editBillIsAutoPay" value="1" class="w-5 h-5 bg-surface-container-lowest border border-outline-variant/30 text-primary-fixed-dim focus:ring-primary-fixed-dim rounded-none">
                        <label for="editBillIsAutoPay" class="font-label-caps text-[9px] text-on-surface-variant uppercase tracking-wider cursor-pointer">Auto-Pay</label>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="closeEditBillModal()" class="h-12 px-6 bg-surface-container-high border border-outline-variant/20 text-on-surface font-label-caps text-[11px] tracking-widest uppercase hover:bg-surface-variant transition-colors">Cancel</button>
                <button type="submit" class="h-12 px-8 bg-primary-fixed text-on-primary-fixed font-label-caps text-[11px] tracking-widest uppercase hover:brightness-110 active:scale-95 transition-all">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function toggleAddBillForm() {
        const form = document.getElementById('addBillCard');
        form.classList.toggle('hidden');
    }

    function setVariable(val) {
        document.getElementById('isVariableInput').value = val ? "1" : "0";
        const fixedBtn = document.getElementById('fixedBtn');
        const variableBtn = document.getElementById('variableBtn');
        const amountLabel = document.getElementById('amountLabel');

        if (val) {
            variableBtn.className = "h-10 px-4 flex items-center justify-center font-label-caps text-[10px] uppercase tracking-wider transition-all bg-primary-fixed text-on-primary-fixed font-bold";
            fixedBtn.className = "h-10 px-4 flex items-center justify-center font-label-caps text-[10px] uppercase tracking-wider transition-all text-on-surface-variant hover:text-white";
            amountLabel.innerText = "Monthly Estimate (Initial)";
        } else {
            fixedBtn.className = "h-10 px-4 flex items-center justify-center font-label-caps text-[10px] uppercase tracking-wider transition-all bg-primary-fixed text-on-primary-fixed font-bold";
            variableBtn.className = "h-10 px-4 flex items-center justify-center font-label-caps text-[10px] uppercase tracking-wider transition-all text-on-surface-variant hover:text-white";
            amountLabel.innerText = "Exact Amount";
        }
    }

    function toggleHistory(id) {
        const panel = document.getElementById(`history-${id}`);
        panel.classList.toggle('hidden');
    }

    function toggleAutoPay(id, buttonEl) {
        fetch(`/bills/${id}/toggle-autopay`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const isEnabled = buttonEl.innerText.trim() === 'ENABLED';
                if (isEnabled) {
                    buttonEl.innerText = 'DISABLED';
                    buttonEl.className = 'h-6 px-2 border font-label-caps text-[9px] tracking-wider uppercase transition-colors bg-surface-container-high border-white/5 text-outline';
                } else {
                    buttonEl.innerText = 'ENABLED';
                    buttonEl.className = 'h-6 px-2 border font-label-caps text-[9px] tracking-wider uppercase transition-colors bg-primary-fixed-dim/15 border-primary-fixed-dim/30 text-primary-fixed-dim';
                }
            }
        });
    }

    function openPayModal(id, name, amount) {
        document.getElementById('modalBillName').value = name;
        document.getElementById('modalPayAmount').value = amount;
        document.getElementById('payModalForm').action = `/bills/${id}/pay`;
        document.getElementById('payModal').classList.remove('hidden');
    }

    function closePayModal() {
        document.getElementById('payModal').classList.add('hidden');
    }

    // --- Frequency / Month toggle for ADD form ---
    function onFrequencyChange(val) {
        const wrapMonth = document.getElementById('addDueMonthWrap');
        const wrapDay = document.getElementById('addDueDayWrap');
        const wrapDate = document.getElementById('addDueDateWrap');

        if (val === 'yearly') {
            wrapMonth.classList.remove('hidden');
            wrapDay.classList.remove('hidden');
            wrapDate.classList.add('hidden');
        } else if (val === 'one_time') {
            wrapMonth.classList.add('hidden');
            wrapDay.classList.add('hidden');
            wrapDate.classList.remove('hidden');
        } else {
            wrapMonth.classList.add('hidden');
            wrapDay.classList.remove('hidden');
            wrapDate.classList.add('hidden');
        }
    }

    // --- Frequency / Month toggle for EDIT form ---
    function onEditFrequencyChange(val) {
        const wrapMonth = document.getElementById('editDueMonthWrap');
        const wrapDay = document.getElementById('editDueDayWrap');
        const wrapDate = document.getElementById('editDueDateWrap');

        if (val === 'yearly') {
            wrapMonth.classList.remove('hidden');
            wrapDay.classList.remove('hidden');
            wrapDate.classList.add('hidden');
        } else if (val === 'one_time') {
            wrapMonth.classList.add('hidden');
            wrapDay.classList.add('hidden');
            wrapDate.classList.remove('hidden');
        } else {
            wrapMonth.classList.add('hidden');
            wrapDay.classList.remove('hidden');
            wrapDate.classList.add('hidden');
        }
    }

    function openEditBillModal(bill) {
        document.getElementById('editBillName').value = bill.name;
        document.getElementById('editBillAmount').value = bill.amount;
        document.getElementById('editBillCategory').value = bill.category;
        document.getElementById('editBillFrequency').value = bill.frequency;
        document.getElementById('editBillDueDay').value = bill.due_day;
        document.getElementById('editBillIsAutoPay').checked = !!bill.is_auto_pay;
        
        setEditVariable(!!bill.is_variable);

        // Handle due_month and due_date for specific bills
        onEditFrequencyChange(bill.frequency);
        if (bill.frequency === 'yearly' && bill.due_month) {
            document.getElementById('editDueMonth').value = bill.due_month;
        } else if (bill.frequency === 'one_time') {
            // we try to parse next_due_date to fill the date picker
            document.getElementById('editDueDate').value = bill.next_due_date.substring(0, 10);
        }
        
        document.getElementById('editBillModalForm').action = `/bills/${bill.id}`;
        document.getElementById('editBillModal').classList.remove('hidden');
    }

    function closeEditBillModal() {
        document.getElementById('editBillModal').classList.add('hidden');
    }

    function setEditVariable(val) {
        document.getElementById('editIsVariableInput').value = val ? "1" : "0";
        const fixedBtn = document.getElementById('editFixedBtn');
        const variableBtn = document.getElementById('editVariableBtn');
        const amountLabel = document.getElementById('editAmountLabel');

        if (val) {
            variableBtn.className = "h-10 px-4 flex items-center justify-center font-label-caps text-[10px] uppercase tracking-wider transition-all bg-primary-fixed text-on-primary-fixed font-bold";
            fixedBtn.className = "h-10 px-4 flex items-center justify-center font-label-caps text-[10px] uppercase tracking-wider transition-all text-on-surface-variant hover:text-white";
            amountLabel.innerText = "Monthly Estimate (Initial)";
        } else {
            fixedBtn.className = "h-10 px-4 flex items-center justify-center font-label-caps text-[10px] uppercase tracking-wider transition-all bg-primary-fixed text-on-primary-fixed font-bold";
            variableBtn.className = "h-10 px-4 flex items-center justify-center font-label-caps text-[10px] uppercase tracking-wider transition-all text-on-surface-variant hover:text-white";
            amountLabel.innerText = "Exact Amount";
        }
    }
</script>
@endsection
