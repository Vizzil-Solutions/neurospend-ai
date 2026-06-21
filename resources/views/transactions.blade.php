@extends('layouts.app')

@section('title', 'NEUROSPEND | Ledger')
@section('page_title', 'LEDGER')

@section('content')
<div class="space-y-gutter animate-fade-in pb-10">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pt-2">
        <div class="space-y-unit">
            <h1 class="font-display-lg text-3xl text-primary font-light flex items-center gap-3 tracking-tight">
                <span class="material-symbols-outlined text-primary-fixed-dim text-[32px]">receipt_long</span>
                <span>Ledger Control</span>
            </h1>
            <p class="font-body-md text-on-surface-variant uppercase tracking-wider text-xs">Record incoming financial streams and optimize outgoing expenditure telemetry</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter">
        <!-- Quick Add Form (4 Columns) -->
        <div class="lg:col-span-4 glass-panel p-8 h-fit relative overflow-hidden group/form">
            <div class="absolute -right-10 -top-10 w-32 h-32 bg-primary-fixed-dim/5 rounded-full blur-[40px] pointer-events-none"></div>
            
            <h3 class="font-title-md text-title-md flex items-center gap-3 mb-6 text-primary relative z-10 font-semibold">
                <span class="material-symbols-outlined text-primary-fixed-dim animate-pulse">add_task</span>
                <span>Record Submission</span>
            </h3>

            <form method="POST" action="/transactions" class="space-y-6 relative z-10">
                @csrf
                <div class="flex gap-2 p-1 bg-surface-container-lowest border border-white/5">
                    <button type="button" id="expenseToggle" onclick="setTxType('expense')" class="flex-1 h-12 flex items-center justify-center gap-2 font-label-caps text-[10px] uppercase tracking-wider transition-all bg-error-container/20 border border-error/30 text-error font-bold">
                        <span class="material-symbols-outlined text-[14px]">trending_down</span>
                        <span>Expense</span>
                    </button>
                    <button type="button" id="incomeToggle" onclick="setTxType('income')" class="flex-1 h-12 flex items-center justify-center gap-2 font-label-caps text-[10px] uppercase tracking-wider transition-all text-on-surface-variant hover:text-white">
                        <span class="material-symbols-outlined text-[14px]">trending_up</span>
                        <span>Income</span>
                    </button>
                </div>
                <input type="hidden" name="type" id="txType" value="expense">

                <div class="space-y-unit">
                    <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Target Account Stream</label>
                    <select required name="account_id" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 px-4 font-data-mono text-data-mono text-primary rounded-none focus:ring-2 focus:ring-primary-fixed-dim focus:border-primary-fixed-dim">
                        <option value="" disabled selected class="bg-surface-container-lowest">Select Account Node...</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" class="bg-surface-container-lowest">{{ $acc->name }} ({{ format_currency($acc->balance, $currency) }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-unit">
                    <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Quantum Amount</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 font-data-mono text-primary-fixed-dim font-bold">$</span>
                        <input type="number" required step="0.01" min="0.01" name="amount" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 pl-10 pr-4 font-data-mono text-data-mono text-primary rounded-none transition-all input-focus-ring" placeholder="0.00">
                    </div>
                </div>

                <div class="space-y-unit">
                    <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Category Vector</label>
                    <select required name="category" id="txCategory" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 px-4 font-data-mono text-data-mono text-primary rounded-none focus:ring-2 focus:ring-primary-fixed-dim focus:border-primary-fixed-dim">
                        <option value="Food & Dining" class="bg-surface-container-lowest">Food & Dining</option>
                        <option value="Housing & Rent" class="bg-surface-container-lowest">Housing & Rent</option>
                        <option value="Utilities" class="bg-surface-container-lowest">Utilities</option>
                        <option value="Transportation" class="bg-surface-container-lowest">Transportation</option>
                        <option value="Entertainment" class="bg-surface-container-lowest">Entertainment</option>
                        <option value="Healthcare" class="bg-surface-container-lowest">Healthcare</option>
                        <option value="Shopping" class="bg-surface-container-lowest">Shopping</option>
                        <option value="Fees & Charges" class="bg-surface-container-lowest">Fees & Charges</option>
                        <option value="Education" class="bg-surface-container-lowest">Education</option>
                        <option value="Travel" class="bg-surface-container-lowest">Travel</option>
                        <option value="Other Expense" class="bg-surface-container-lowest">Other Expense</option>
                    </select>
                </div>

                <div class="space-y-unit">
                    <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Trajectory Date</label>
                    <input type="date" required name="date" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 px-4 font-data-mono text-data-mono text-primary rounded-none transition-all input-focus-ring" value="{{ date('Y-m-d') }}">
                </div>

                <div class="space-y-unit">
                    <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Memo (Description)</label>
                    <input type="text" name="description" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 px-4 font-data-mono text-data-mono text-primary rounded-none transition-all input-focus-ring" placeholder="groceries, server costs...">
                </div>

                <button type="submit" class="w-full h-12 bg-primary-fixed text-on-primary-fixed font-label-caps text-[11px] tracking-widest uppercase hover:brightness-110 active:scale-95 transition-all">
                    Submit Record
                </button>
            </form>
        </div>

        <!-- Ledger View (8 Columns) -->
        <div class="lg:col-span-8 glass-panel p-8 flex flex-col justify-between min-h-[500px]">
            <div>
                <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 mb-8">
                    <h3 class="font-label-caps text-label-caps text-primary tracking-widest uppercase">QUANTUM RECORDS</h3>
                    <div class="relative flex items-center max-w-xs w-full">
                        <span class="material-symbols-outlined absolute left-3 text-outline text-[18px]">search</span>
                        <input type="text" id="ledgerSearch" onkeyup="filterLedgerTable()" class="w-full h-10 bg-surface-container-lowest border border-outline-variant/30 pl-10 pr-4 font-data-mono text-xs text-primary rounded-none transition-all input-focus-ring" placeholder="Search memo or category...">
                    </div>
                </div>

                @if($transactions->count() === 0)
                    <div class="flex flex-col items-center justify-center py-20 text-center space-y-4">
                        <span class="material-symbols-outlined text-[48px] text-outline opacity-40">clinical_notes</span>
                        <h4 class="font-headline-lg text-lg text-primary font-light">No records detected</h4>
                        <p class="font-body-md text-xs text-on-surface-variant max-w-xs">Incoming and outgoing transaction events will be displayed here once generated.</p>
                    </div>
                @else
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse min-w-[500px]">
                            <thead>
                                <tr class="border-b border-white/5 text-[10px] font-label-caps text-outline uppercase tracking-wider">
                                    <th class="pb-3">Date</th>
                                    <th class="pb-3">Details</th>
                                    <th class="pb-3">Category</th>
                                    <th class="pb-3 text-right">Value</th>
                                    <th class="pb-3 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="ledgerTableBody">
                                @foreach($transactions as $tx)
                                    <tr class="ledger-row border-b border-white/5 last:border-0 hover:bg-white/5 transition-all">
                                        <td class="py-4 font-data-mono text-xs text-outline">{{ format_date($tx->date) }}</td>
                                        <td class="py-4">
                                            <div class="font-title-md text-sm font-semibold text-primary truncate max-w-[200px]" title="{{ $tx->description }}">{{ $tx->description ?: 'Unspecified' }}</div>
                                            <div class="font-label-caps text-[9px] text-outline uppercase mt-0.5">{{ $tx->account ? $tx->account->name : 'External Node' }}</div>
                                        </td>
                                        <td class="py-4">
                                            <span class="font-label-caps text-[9px] uppercase tracking-wider py-1 px-2.5 bg-surface-container-high border border-white/5 text-on-surface-variant">
                                                {{ $tx->category }}
                                            </span>
                                        </td>
                                        <td class="py-4 text-right">
                                            <span class="font-data-mono text-sm font-bold {{ $tx->type === 'income' ? 'text-primary-fixed-dim' : 'text-primary' }}">
                                                {{ $tx->type === 'income' ? '+' : '-' }}{{ format_currency($tx->amount, $currency) }}
                                            </span>
                                        </td>
                                        <td class="py-4 text-center">
                                            <form action="/transactions/{{ $tx->id }}" method="POST" class="inline-block" onsubmit="return confirm('Reverse ledger impact and terminate transaction record?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 text-on-surface-variant hover:text-error transition-colors">
                                                    <span class="material-symbols-outlined text-[16px]">delete</span>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const expenseCategories = `
        <option value="Food & Dining" class="bg-surface-container-lowest">Food & Dining</option>
        <option value="Housing & Rent" class="bg-surface-container-lowest">Housing & Rent</option>
        <option value="Utilities" class="bg-surface-container-lowest">Utilities</option>
        <option value="Transportation" class="bg-surface-container-lowest">Transportation</option>
        <option value="Entertainment" class="bg-surface-container-lowest">Entertainment</option>
        <option value="Healthcare" class="bg-surface-container-lowest">Healthcare</option>
        <option value="Shopping" class="bg-surface-container-lowest">Shopping</option>
        <option value="Fees & Charges" class="bg-surface-container-lowest">Fees & Charges</option>
        <option value="Education" class="bg-surface-container-lowest">Education</option>
        <option value="Travel" class="bg-surface-container-lowest">Travel</option>
        <option value="Other Expense" class="bg-surface-container-lowest">Other Expense</option>
    `;

    const incomeCategories = `
        <option value="Salary & Wage" class="bg-surface-container-lowest">Salary & Wage</option>
        <option value="Freelance & Side Business" class="bg-surface-container-lowest">Freelance & Side Business</option>
        <option value="Investments" class="bg-surface-container-lowest">Investments</option>
        <option value="Gifts & Reimbursements" class="bg-surface-container-lowest">Gifts & Reimbursements</option>
        <option value="Other Income" class="bg-surface-container-lowest">Other Income</option>
    `;

    function setTxType(type) {
        document.getElementById('txType').value = type;
        const expBtn = document.getElementById('expenseToggle');
        const incBtn = document.getElementById('incomeToggle');
        const catSelect = document.getElementById('txCategory');

        if (type === 'expense') {
            expBtn.className = "flex-1 h-12 flex items-center justify-center gap-2 font-label-caps text-[10px] uppercase tracking-wider transition-all bg-error-container/20 border border-error/30 text-error font-bold";
            incBtn.className = "flex-1 h-12 flex items-center justify-center gap-2 font-label-caps text-[10px] uppercase tracking-wider transition-all text-on-surface-variant hover:text-white";
            catSelect.innerHTML = expenseCategories;
        } else {
            incBtn.className = "flex-1 h-12 flex items-center justify-center gap-2 font-label-caps text-[10px] uppercase tracking-wider transition-all bg-primary-fixed-dim/20 border border-primary-fixed-dim/30 text-primary-fixed-dim font-bold";
            expBtn.className = "flex-1 h-12 flex items-center justify-center gap-2 font-label-caps text-[10px] uppercase tracking-wider transition-all text-on-surface-variant hover:text-white";
            catSelect.innerHTML = incomeCategories;
        }
    }

    function filterLedgerTable() {
        const query = document.getElementById('ledgerSearch').value.toLowerCase();
        const rows = document.querySelectorAll('.ledger-row');

        rows.forEach(row => {
            const cells = row.innerText.toLowerCase();
            if (cells.includes(query)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
</script>
@endsection
