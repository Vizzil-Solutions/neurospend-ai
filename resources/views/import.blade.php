@extends('layouts.app')

@section('title', 'NEUROSPEND | Data Sync')
@section('page_title', 'DATA SYNC')

@section('content')
<div class="space-y-gutter animate-fade-in pb-10">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pt-2">
        <div class="space-y-unit">
            <h1 class="font-display-lg text-3xl text-primary font-light flex items-center gap-3 tracking-tight">
                <span class="material-symbols-outlined text-primary-fixed-dim text-[32px]">database</span>
                <span>Data Sync Workspace</span>
            </h1>
            <p class="font-body-md text-on-surface-variant uppercase tracking-wider text-xs">Ingest financial telemetry from bank CSV reports and calibrate local checking/savings nodes</p>
        </div>
    </div>

    <!-- MAIN IMPORT WIZARD -->
    <div class="glass-panel p-8 relative overflow-hidden">
        <div class="shimmer-bg absolute inset-0 pointer-events-none"></div>
        
        <!-- STEP 1: UPLOAD -->
        <div id="step-upload" class="text-center py-12 relative z-10 space-y-6">
            <div class="w-16 h-16 bg-primary-fixed-dim/10 border border-primary-fixed-dim/20 text-primary-fixed-dim flex items-center justify-center mx-auto shadow-md">
                <span class="material-symbols-outlined text-[32px] animate-bounce">cloud_upload</span>
            </div>
            <div class="space-y-2">
                <h3 class="font-headline-lg text-xl text-white font-light">Upload Telemetry File</h3>
                <p class="font-body-md text-xs text-on-surface-variant max-w-sm mx-auto">Select a comma-separated values (.csv) ledger file exported from your banking platform.</p>
            </div>
            
            <input type="file" id="csvFileInput" accept=".csv" class="hidden" onchange="handleFileSelect(event)">
            <button onclick="document.getElementById('csvFileInput').click()" class="h-12 px-8 bg-primary-fixed text-on-primary-fixed font-label-caps text-[11px] tracking-widest uppercase hover:brightness-110 active:scale-95 transition-all">
                Browse Files
            </button>
        </div>

        <!-- STEP 2: COLUMN MAPPING -->
        <div id="step-mapping" class="hidden space-y-8 animate-scale-in relative z-10">
            <div class="flex items-center gap-4 pb-4 border-b border-white/5">
                <div class="w-10 h-10 bg-primary-fixed-dim/10 border border-primary-fixed-dim/20 text-primary-fixed-dim flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">sync_alt</span>
                </div>
                <div>
                    <h3 class="font-title-md text-lg text-white font-semibold leading-none">Map Column Vectors</h3>
                    <p class="font-body-md text-xs text-on-surface-variant mt-1">Define which CSV columns map to core metrics.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="space-y-unit">
                    <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Destination Node</label>
                    <select id="targetAccountId" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 px-4 font-data-mono text-data-mono text-primary rounded-none focus:ring-2 focus:ring-primary-fixed-dim focus:border-primary-fixed-dim">
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" class="bg-surface-container-lowest">{{ $acc->name }} ({{ format_currency($acc->balance, $currency) }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-unit">
                    <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Date Vector</label>
                    <select id="dateColSelect" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 px-4 font-data-mono text-data-mono text-primary rounded-none focus:ring-2 focus:ring-primary-fixed-dim focus:border-primary-fixed-dim"></select>
                </div>

                <div class="space-y-unit">
                    <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Description Vector</label>
                    <select id="descColSelect" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 px-4 font-data-mono text-data-mono text-primary rounded-none focus:ring-2 focus:ring-primary-fixed-dim focus:border-primary-fixed-dim"></select>
                </div>

                <div class="space-y-unit">
                    <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Value Vector (Amount)</label>
                    <select id="amountColSelect" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 px-4 font-data-mono text-data-mono text-primary rounded-none focus:ring-2 focus:ring-primary-fixed-dim focus:border-primary-fixed-dim"></select>
                </div>
            </div>

            <div class="pt-6 border-t border-white/5 flex justify-between">
                <button onclick="resetImportWizard()" class="h-12 px-6 bg-surface-container-high border border-outline-variant/20 text-on-surface font-label-caps text-[11px] tracking-widest uppercase hover:bg-surface-variant transition-colors">Cancel</button>
                <button onclick="processMapping()" class="h-12 px-8 bg-primary-fixed text-on-primary-fixed font-label-caps text-[11px] tracking-widest uppercase hover:brightness-110 active:scale-95 transition-all">Process Calibration</button>
            </div>
        </div>

        <!-- STEP 3: PREVIEW & REVIEW -->
        <div id="step-review" class="hidden space-y-8 animate-scale-in relative z-10">
            <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4 pb-4 border-b border-white/5">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 bg-primary-fixed-dim/10 border border-primary-fixed-dim/20 text-primary-fixed-dim flex items-center justify-center">
                        <span class="material-symbols-outlined text-[20px]">fact_check</span>
                    </div>
                    <div>
                        <h3 class="font-title-md text-lg text-white font-semibold leading-none">Verify Sync Ledger</h3>
                        <p class="font-body-md text-xs text-on-surface-variant mt-1">Confirm transaction list before committing updates.</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button onclick="toggleSelectAll(true)" class="font-label-caps text-[10px] text-primary-fixed-dim hover:underline uppercase tracking-wider">Select All</button>
                    <span class="text-outline opacity-20">|</span>
                    <button onclick="toggleSelectAll(false)" class="font-label-caps text-[10px] text-primary-fixed-dim hover:underline uppercase tracking-wider">Unselect All</button>
                </div>
            </div>

            <!-- Preview table -->
            <div class="overflow-x-auto max-h-[400px] custom-scrollbar border border-white/5">
                <table class="w-full text-left border-collapse min-w-[600px]">
                    <thead>
                        <tr class="bg-surface-container-high border-b border-white/5 text-[10px] font-label-caps text-outline uppercase tracking-wider">
                            <th class="p-4 w-10">Sync</th>
                            <th class="p-4">Date</th>
                            <th class="p-4">Memo</th>
                            <th class="p-4">Category</th>
                            <th class="p-4 text-right">Value</th>
                            <th class="p-4">Status</th>
                        </tr>
                    </thead>
                    <tbody id="previewTableBody"></tbody>
                </table>
            </div>

            <div class="pt-6 border-t border-white/5 flex justify-between items-center">
                <button onclick="backToMapping()" class="h-12 px-6 bg-surface-container-high border border-outline-variant/20 text-on-surface font-label-caps text-[11px] tracking-widest uppercase hover:bg-surface-variant transition-colors">Back</button>
                <div class="flex items-center gap-4">
                    <span class="font-label-caps text-[11px] text-on-surface-variant uppercase tracking-wider" id="selectedCountLabel">0 records selected</span>
                    <button onclick="executeImport()" class="h-12 px-8 bg-primary-fixed text-on-primary-fixed font-label-caps text-[11px] tracking-widest uppercase hover:brightness-110 active:scale-95 transition-all">Commit Sync</button>
                </div>
            </div>
        </div>

        <!-- STEP 4: IMPORTING & PROGRESS -->
        <div id="step-progress" class="hidden text-center py-16 animate-scale-in relative z-10 space-y-6">
            <div class="w-12 h-12 rounded-full border-4 border-primary-fixed border-t-transparent animate-spin mx-auto"></div>
            <div class="space-y-2">
                <h3 class="font-headline-lg text-lg text-white font-light">Ingesting Records</h3>
                <p class="font-body-md text-xs text-on-surface-variant max-w-xs mx-auto">Writing transactions to database ledgers. Please do not navigate away.</p>
            </div>
        </div>

        <!-- STEP 5: SUCCESS -->
        <div id="step-success" class="hidden text-center py-12 relative z-10 space-y-6">
            <div class="w-16 h-16 bg-primary-fixed-dim/10 border border-primary-fixed-dim/20 text-primary-fixed-dim flex items-center justify-center mx-auto">
                <span class="material-symbols-outlined text-[32px] animate-pulse">check_circle</span>
            </div>
            <div class="space-y-2">
                <h3 class="font-headline-lg text-2xl text-white font-light">Telemetry Synced</h3>
                <p class="font-body-md text-xs text-on-surface-variant max-w-sm mx-auto">Transactions have been logged, and account liquidity pools calibrated.</p>
            </div>
            <a href="/" class="inline-flex h-12 px-8 bg-primary-fixed text-on-primary-fixed font-label-caps text-[11px] tracking-widest uppercase hover:brightness-110 active:scale-95 transition-all items-center justify-center">
                Return to Dashboard
            </a>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
    let csvHeaders = [];
    let csvRows = [];
    let previewData = [];

    const CATEGORY_RULES = {
        "uber": "Transportation",
        "lyft": "Transportation",
        "shell": "Transportation",
        "mcdonalds": "Food & Dining",
        "starbucks": "Food & Dining",
        "doordash": "Food & Dining",
        "walmart": "Shopping",
        "target": "Shopping",
        "whole foods": "Food & Dining",
        "netflix": "Entertainment",
        "spotify": "Entertainment",
        "amazon": "Shopping",
        "apple": "Shopping",
        "pg&e": "Utilities",
        "water": "Utilities",
        "salary": "Salary & Wage",
        "payroll": "Salary & Wage"
    };

    function detectCategory(description) {
        const lowerDesc = description.toLowerCase();
        for (const [key, category] of Object.entries(CATEGORY_RULES)) {
            if (lowerDesc.includes(key)) return category;
        }
        return "Other Expense";
    }

    function parseCSVRow(text) {
        const re = /,"([^"]*)"|([^,]+)/g;
        const result = [];
        let m;
        while ((m = re.exec(text)) !== null) {
            if (m[1] !== undefined) result.push(m[1].trim());
            else if (m[2] !== undefined) result.push(m[2].trim());
        }
        return result;
    }

    function handleFileSelect(event) {
        const file = event.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(e) {
            const text = e.target.result;
            const lines = text.split('\n').map(line => line.trim()).filter(line => line.length > 0);
            
            if (lines.length > 0) {
                csvHeaders = parseCSVRow(lines[0]);
                csvRows = lines.slice(1).map(line => parseCSVRow(line));
                
                populateColumnSelects();
                showStep('mapping');
            }
        };
        reader.readAsText(file);
    }

    function populateColumnSelects() {
        const dateSelect = document.getElementById('dateColSelect');
        const descSelect = document.getElementById('descColSelect');
        const amountSelect = document.getElementById('amountColSelect');

        dateSelect.innerHTML = '';
        descSelect.innerHTML = '';
        amountSelect.innerHTML = '';

        csvHeaders.forEach((header, index) => {
            const dateOpt = new Option(header, index);
            const descOpt = new Option(header, index);
            const amountOpt = new Option(header, index);

            const lowerHeader = header.toLowerCase();
            if (lowerHeader.includes('date')) dateOpt.selected = true;
            if (lowerHeader.includes('desc') || lowerHeader.includes('name') || lowerHeader.includes('memo')) descOpt.selected = true;
            if (lowerHeader.includes('amount') || lowerHeader.includes('value') || lowerHeader.includes('charge')) amountOpt.selected = true;

            // Add classes for styling within option dropdowns
            dateOpt.className = "bg-surface-container-lowest text-primary";
            descOpt.className = "bg-surface-container-lowest text-primary";
            amountOpt.className = "bg-surface-container-lowest text-primary";

            dateSelect.add(dateOpt);
            descSelect.add(descOpt);
            amountSelect.add(amountOpt);
        });
    }

    function showStep(stepName) {
        document.getElementById('step-upload').classList.add('hidden');
        document.getElementById('step-mapping').classList.add('hidden');
        document.getElementById('step-review').classList.add('hidden');
        document.getElementById('step-progress').classList.add('hidden');
        document.getElementById('step-success').classList.add('hidden');

        document.getElementById(`step-${stepName}`).classList.remove('hidden');
    }

    function resetImportWizard() {
        document.getElementById('csvFileInput').value = '';
        showStep('upload');
    }

    function backToMapping() {
        showStep('mapping');
    }

    const existingTransactions = @json($transactions);

    function processMapping() {
        const dateIdx = parseInt(document.getElementById('dateColSelect').value);
        const descIdx = parseInt(document.getElementById('descColSelect').value);
        const amountIdx = parseInt(document.getElementById('amountColSelect').value);
        const targetAccountId = parseInt(document.getElementById('targetAccountId').value);

        if (isNaN(dateIdx) || isNaN(descIdx) || isNaN(amountIdx)) {
            alert('Please configure your column vectors correctly.');
            return;
        }

        previewData = csvRows.map((row, index) => {
            const rawDate = row[dateIdx];
            const rawDesc = row[descIdx] || 'Unassigned Memo';
            const rawAmount = row[amountIdx];

            let amount = parseFloat(rawAmount ? rawAmount.replace(/[^0-9.-]+/g, "") : "0");
            if (isNaN(amount)) amount = 0;

            const type = amount < 0 ? 'expense' : 'income';
            amount = Math.abs(amount);

            let dateStr = new Date().toISOString().split('T')[0];
            try {
                if (rawDate) {
                    const parsedD = new Date(rawDate);
                    if (!isNaN(parsedD.getTime())) {
                        dateStr = parsedD.toISOString().split('T')[0];
                    }
                }
            } catch(e) {}

            const category = detectCategory(rawDesc);

            const isDuplicate = existingTransactions.some(t => {
                return parseInt(t.account_id) === targetAccountId &&
                       parseFloat(t.amount) === amount &&
                       t.type === type &&
                       t.date === dateStr;
            });

            return {
                id: index,
                account_id: targetAccountId,
                amount: amount,
                type: type,
                category: category,
                description: rawDesc,
                date: dateStr,
                selected: !isDuplicate,
                duplicate: isDuplicate
            };
        });

        renderReviewTable();
        showStep('review');
        updateSelectedCount();
    }

    function renderReviewTable() {
        const tbody = document.getElementById('previewTableBody');
        tbody.innerHTML = '';

        previewData.forEach(item => {
            const tr = document.createElement('tr');
            tr.className = `border-b border-white/5 hover:bg-white/5 transition-all ${item.duplicate ? 'opacity-50' : ''}`;
            
            const formattedVal = (item.type === 'income' ? '+' : '-') + '$' + item.amount.toFixed(2);
            const valClass = item.type === 'income' ? 'text-primary-fixed-dim' : 'text-primary';

            tr.innerHTML = `
                <td class="p-4">
                    <input type="checkbox" class="w-5 h-5 bg-surface-container-lowest border border-outline-variant/30 text-primary-fixed-dim focus:ring-primary-fixed-dim rounded-none" ${item.selected ? 'checked' : ''} onchange="toggleItemSelect(${item.id}, this.checked)">
                </td>
                <td class="p-4 font-data-mono text-xs text-outline">${item.date}</td>
                <td class="p-4 font-title-md text-sm text-primary font-semibold max-w-[200px] truncate" title="${item.description}">${item.description}</td>
                <td class="p-4">
                    <span class="font-label-caps text-[9px] uppercase tracking-wider py-1 px-2.5 bg-surface-container-high border border-white/5 text-on-surface-variant">${item.category}</span>
                </td>
                <td class="p-4 text-right font-data-mono font-bold text-sm ${valClass}">${formattedVal}</td>
                <td class="p-4 text-xs font-label-caps">
                    ${item.duplicate ? '<span class="text-warning flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">warning</span> Duplicate</span>' : '<span class="text-primary-fixed-dim">Clear</span>'}
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function toggleItemSelect(id, isChecked) {
        const item = previewData.find(d => d.id === id);
        if (item) {
            item.selected = isChecked;
            updateSelectedCount();
        }
    }

    function toggleSelectAll(checked) {
        previewData.forEach(item => {
            item.selected = checked;
        });
        renderReviewTable();
        updateSelectedCount();
    }

    // Initialize counts on review step load
    function updateSelectedCount() {
        const count = previewData.filter(d => d.selected).length;
        document.getElementById('selectedCountLabel').innerText = `${count} records selected`;
    }

    function executeImport() {
        const selectedTxs = previewData.filter(d => d.selected);
        if (selectedTxs.length === 0) {
            alert('No records selected for sync.');
            return;
        }

        showStep('progress');

        fetch('/import', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                transactions: selectedTxs
            })
        })
        .then(res => {
            if (!res.ok) throw new Error('Data transmission failed.');
            return res.json();
        })
        .then(data => {
            showStep('success');
        })
        .catch(err => {
            alert(err.message);
            showStep('review');
        });
    }
</script>
@endsection
