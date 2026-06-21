@extends('layouts.app')

@section('title', 'NEUROSPEND | Settings')
@section('page_title', 'SYSTEM SETTINGS')

@section('content')
<div class="space-y-gutter animate-fade-in pb-16">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pt-2">
        <div class="space-y-unit">
            <h1 class="font-display-lg text-3xl text-primary font-light flex items-center gap-3 tracking-tight">
                <span class="material-symbols-outlined text-primary-fixed-dim text-[32px]">settings</span>
                <span>System Settings</span>
            </h1>
            <p class="font-body-md text-on-surface-variant uppercase tracking-wider text-xs">Configure baseline target metrics, export telemetry backup bundles, or reset database state</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter">
        <!-- Settings Form Group (8 Columns) -->
        <div class="lg:col-span-8 space-y-6">
            <!-- Parameters Configuration -->
            <div class="glass-panel p-8">
                <h3 class="font-headline-lg text-lg mb-6 flex items-center gap-2 text-white font-light">
                    <span class="material-symbols-outlined text-primary-fixed-dim">tune</span>
                    <span>Base Parameters</span>
                </h3>
                
                <form method="POST" action="/settings" class="space-y-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-unit">
                            <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Active Currency</label>
                            <select name="currency" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 px-4 font-data-mono text-data-mono text-primary rounded-none focus:ring-2 focus:ring-primary-fixed-dim focus:border-primary-fixed-dim">
                                @foreach($currencies as $c)
                                    <option value="{{ $c['code'] }}" {{ $settings->currency === $c['code'] ? 'selected' : '' }} class="bg-surface-container-lowest">
                                        {{ $c['name'] }} ({{ $c['symbol'] }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-unit">
                            <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Income Velocity (Payday Frequency)</label>
                            <select name="payday_freq" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 px-4 font-data-mono text-data-mono text-primary rounded-none focus:ring-2 focus:ring-primary-fixed-dim focus:border-primary-fixed-dim">
                                <option value="monthly" {{ $settings->payday_freq === 'monthly' ? 'selected' : '' }} class="bg-surface-container-lowest">Monthly</option>
                                <option value="weekly" {{ $settings->payday_freq === 'weekly' ? 'selected' : '' }} class="bg-surface-container-lowest">Weekly</option>
                                <option value="15_days" {{ $settings->payday_freq === '15_days' ? 'selected' : '' }} class="bg-surface-container-lowest">Bi-Weekly (15 days)</option>
                            </select>
                        </div>

                        <div class="space-y-unit">
                            <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Base Payday Date of Month</label>
                            <input type="number" min="1" max="31" name="payday_date" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 px-4 font-data-mono text-data-mono text-primary rounded-none transition-all input-focus-ring" value="{{ $settings->payday_date }}" placeholder="1">
                        </div>

                        <div class="space-y-unit">
                            <label class="font-label-caps text-label-caps text-on-surface-variant ml-1">Standard Payday Capital Amount</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 font-data-mono text-primary-fixed-dim font-bold">$</span>
                                <input type="number" step="0.01" min="0" name="payday_amount" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 pl-10 pr-4 font-data-mono text-data-mono text-primary rounded-none transition-all input-focus-ring" value="{{ $settings->payday_amount }}" placeholder="0.00">
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-white/5 flex justify-end">
                        <button type="submit" class="h-12 px-8 bg-primary-fixed text-on-primary-fixed font-label-caps text-[11px] tracking-widest uppercase hover:brightness-110 active:scale-95 transition-all">Save Base Configuration</button>
                    </div>
                </form>
            </div>

            <!-- Recovery Workspace -->
            <div class="glass-panel p-8">
                <h3 class="font-headline-lg text-lg mb-4 flex items-center gap-2 text-white font-light">
                    <span class="material-symbols-outlined text-primary-fixed-dim">download</span>
                    <span>Data Backup & Recovery</span>
                </h3>
                <p class="font-body-md text-xs text-on-surface-variant mb-6 leading-relaxed">
                    Export your database ledger configurations into a readable JSON file. Uploading a prior sync dump overwrites all current metrics.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-6 border-t border-white/5">
                    <!-- Export -->
                    <div class="space-y-4">
                        <h4 class="font-label-caps text-[10px] text-outline uppercase tracking-wider">Export Ledgers</h4>
                        <p class="font-body-md text-xs text-on-surface-variant leading-relaxed">Download complete history (accounts, logs, bills, liabilities) in one structured backup snapshot.</p>
                        <a href="/settings/export" class="h-12 bg-surface-container-high border border-outline-variant/20 text-on-surface font-label-caps text-[11px] tracking-widest uppercase hover:bg-surface-variant transition-colors flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[16px]">download</span>
                            <span>Export Backup Node</span>
                        </a>
                    </div>

                    <!-- Import -->
                    <div class="space-y-4">
                        <h4 class="font-label-caps text-[10px] text-outline uppercase tracking-wider">Restore Ledgers</h4>
                        <p class="font-body-md text-xs text-on-surface-variant leading-relaxed font-light">Select and upload a previously exported JSON backup file to overwrite current metrics.</p>
                        <form method="POST" action="/settings/import" enctype="multipart/form-data" class="space-y-3">
                            @csrf
                            <input type="file" name="backup_file" required accept=".json,.txt" class="block w-full text-xs text-on-surface-variant file:mr-4 file:py-2.5 file:px-4 file:bg-surface-container-high file:text-primary file:font-label-caps file:text-[10px] file:tracking-wider file:uppercase file:border-0 hover:file:bg-surface-variant cursor-pointer">
                            <button type="submit" class="w-full h-12 bg-primary-fixed text-on-primary-fixed font-label-caps text-[11px] tracking-widest uppercase hover:brightness-110 active:scale-95 transition-all flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined text-[16px]">upload</span>
                                <span>Import Backup Node</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dangerous Column (4 Columns) -->
        <div class="lg:col-span-4 space-y-6">
            <!-- Dangerous Operations -->
            <div class="glass-panel p-8 border-error/20 bg-error/5 relative overflow-hidden">
                <h3 class="font-headline-lg text-lg mb-3 flex items-center gap-2 text-error font-light">
                    <span class="material-symbols-outlined text-error">warning</span>
                    <span>System Purge</span>
                </h3>
                <p class="font-body-md text-xs text-on-surface-variant leading-relaxed mb-6">
                    Wipes all local metrics including transactions, accounts, and tracked liabilities. This action is final and cannot be undone.
                </p>

                <form method="POST" action="/settings/nuke" onsubmit="return confirm('WARNING: You are about to wipe the entire database. Proceed?');">
                    @csrf
                    <div class="space-y-4">
                        <div class="space-y-unit">
                            <label class="font-label-caps text-[9px] text-outline uppercase tracking-wider mb-2 block">Type "NUKE" to confirm</label>
                            <input type="text" name="confirm" required class="w-full h-12 bg-error/10 border border-error/30 px-4 font-data-mono text-data-mono text-center text-error font-bold tracking-widest rounded-none transition-all focus:ring-1 focus:ring-error" placeholder="NUKE">
                        </div>
                        <button type="submit" class="w-full h-12 bg-error text-white font-label-caps text-[11px] tracking-widest uppercase hover:bg-error/85 transition-all">
                            Wipe System Node
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
