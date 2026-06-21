@extends('layouts.app')

@section('title', 'NEUROSPEND | Heuristic Engine')
@section('page_title', 'NEURAL HEURISTICS')

@php
    function getInsightIcon($type) {
        switch ($type) {
            case 'danger':
                return 'warning';
            case 'warning':
                return 'error';
            case 'success':
                return 'check_circle';
            default:
                return 'info';
        }
    }

    function getInsightColorClasses($type) {
        switch ($type) {
            case 'danger':
                return [
                    'bg' => 'bg-error/5',
                    'border' => 'border-error/20 text-error',
                    'title' => 'text-error'
                ];
            case 'warning':
                return [
                    'bg' => 'bg-warning/5',
                    'border' => 'border-warning/20 text-warning',
                    'title' => 'text-warning'
                ];
            case 'success':
                return [
                    'bg' => 'bg-primary-fixed-dim/5',
                    'border' => 'border-primary-fixed-dim/20 text-primary-fixed-dim',
                    'title' => 'text-primary-fixed-dim'
                ];
            default:
                return [
                    'bg' => 'bg-secondary-container/5',
                    'border' => 'border-secondary-container/20 text-secondary-fixed-dim',
                    'title' => 'text-secondary-fixed-dim'
                ];
        }
    }
@endphp

@section('content')
<div class="space-y-gutter animate-fade-in pb-10">
    <!-- Header Hero Banner -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pt-2">
        <div class="space-y-unit">
            <h1 class="font-display-lg text-3xl text-primary font-light flex items-center gap-3 tracking-tight">
                <span class="material-symbols-outlined text-primary-fixed-dim text-[32px] animate-pulse">psychology</span>
                <span>NOVA Intelligence Core</span>
            </h1>
            <p class="font-body-md text-on-surface-variant uppercase tracking-wider text-xs">Deterministic, local-first diagnostics calculated directly from your ledger memory nodes</p>
        </div>

        <!-- Personality Switcher -->
        <div class="bg-surface-container-lowest border border-white/5 p-1 rounded-none flex items-center shrink-0 w-full md:w-auto">
            <a href="/coach?personality=encouraging" class="flex-1 md:flex-none h-10 px-4 flex items-center justify-center font-label-caps text-[10px] uppercase tracking-wider transition-all {{ $personality === 'encouraging' ? 'bg-primary-fixed text-on-primary-fixed font-bold' : 'text-on-surface-variant hover:text-white' }}">
                Encouraging
            </a>
            <a href="/coach?personality=tough_love" class="flex-1 md:flex-none h-10 px-4 flex items-center justify-center font-label-caps text-[10px] uppercase tracking-wider transition-all {{ $personality === 'tough_love' ? 'bg-primary-fixed text-on-primary-fixed font-bold' : 'text-on-surface-variant hover:text-white' }}">
                Tough Love
            </a>
        </div>
    </div>

    <!-- Ask Nova Interface -->
    <div class="glass-panel p-6 mb-6 border-primary-fixed-dim/20 relative overflow-hidden animate-scale-in" style="animation-delay: 50ms;">
        <div class="absolute -right-20 -top-20 w-64 h-64 rounded-full blur-[80px] bg-primary-fixed-dim/10 pointer-events-none"></div>
        <div class="relative z-10">
            <h3 class="font-headline-lg text-lg flex items-center gap-2 mb-3 text-primary font-light">
                <span class="material-symbols-outlined text-primary-fixed-dim">chat_spark</span>
                <span>Ask Nova</span>
            </h3>
            <p class="font-body-md text-on-surface-variant text-xs mb-4">
                Ask questions about your spending, runway, or debt. Nova analyzes your ledger instantly without sending data externally.
            </p>
            <form id="askNovaForm" onsubmit="handleAskNova(event)" class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline-variant text-[18px]">search</span>
                    <input type="text" id="novaQuestion" class="w-full h-12 bg-surface-container-lowest border border-outline-variant/30 pl-11 pr-4 font-data-mono text-data-mono text-primary transition-all focus:border-primary-fixed-dim focus:ring-1 focus:ring-primary-fixed-dim outline-none" placeholder="e.g. How much safe runway do I have left?" required>
                </div>
                <button type="submit" id="novaSubmitBtn" class="h-12 px-6 bg-primary-fixed text-on-primary-fixed font-label-caps text-[11px] tracking-widest uppercase hover:brightness-110 active:scale-95 transition-all flex items-center justify-center gap-2 shrink-0">
                    <span id="novaBtnText">Ask</span>
                    <span class="material-symbols-outlined text-[16px]" id="novaBtnIcon">send</span>
                </button>
            </form>
            
            <div id="novaResponseArea" class="hidden mt-5 p-5 border bg-surface-container-lowest/50 backdrop-blur-sm relative overflow-hidden">
                <div class="flex gap-4">
                    <div class="mt-0.5 flex-shrink-0 bg-surface-container-high p-2 border flex items-center justify-center" id="novaResponseIconWrapper">
                        <span class="material-symbols-outlined text-[20px]" id="novaResponseIcon">auto_awesome</span>
                    </div>
                    <div class="flex-1 space-y-2">
                        <div id="novaResponseText" class="font-body-md text-[14px] text-on-surface-variant leading-relaxed whitespace-pre-wrap markdown-body"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Diagnostic Bento Grid -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-gutter">
        
        <!-- Advisor feed (8 Columns) -->
        <div class="col-span-1 md:col-span-8 space-y-4">
            <div class="flex items-center gap-2 mb-2">
                <span class="w-2 h-2 rounded-full bg-primary-fixed-dim animate-pulse"></span>
                <h3 class="font-label-caps text-label-caps text-secondary tracking-widest uppercase">
                    DIAGNOSTIC PIPELINES ({{ count($insights) }})
                </h3>
            </div>

            @if(count($insights) === 0)
                <div class="glass-panel p-16 flex flex-col items-center justify-center text-center space-y-4">
                    <span class="material-symbols-outlined text-[48px] text-outline opacity-40">clinical_notes</span>
                    <h4 class="font-headline-lg text-lg text-primary font-light">Telemetry Required</h4>
                    <p class="font-body-md text-xs text-on-surface-variant max-w-sm">No diagnostic insight could be extracted. Log more transactions or debt targets to begin scanning.</p>
                </div>
            @else
                @foreach($insights as $index => $insight)
                    @php
                        $colors = getInsightColorClasses($insight['type']);
                    @endphp
                    <div class="glass-panel border relative overflow-hidden group transition-all duration-300 hover:border-primary-fixed-dim/30 {{ $colors['border'] }} p-6 animate-scale-in" style="animation-delay: {{ $index * 60 }}ms;">
                        <!-- Backdrop Glow -->
                        <div class="absolute -right-10 -top-10 w-32 h-32 rounded-full blur-3xl opacity-10 {{ $colors['bg'] }}"></div>

                        <div class="relative z-10 flex gap-4 items-start">
                            <div class="mt-1 bg-surface-container-lowest p-2 border {{ $colors['border'] }} flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-[20px]">{{ getInsightIcon($insight['type']) }}</span>
                            </div>
                            
                            <div class="flex-1 space-y-3">
                                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2">
                                    <h4 class="font-title-md text-base leading-tight font-semibold {{ $colors['title'] }}">{{ $insight['title'] }}</h4>
                                    <span class="font-label-caps text-[9px] uppercase tracking-wider text-outline border border-white/5 bg-surface-container-high px-2.5 py-0.5 self-start sm:self-auto">
                                        {{ $insight['category'] }}
                                    </span>
                                </div>
                                <p class="font-body-md text-[14px] text-on-surface-variant leading-relaxed italic">
                                    "{{ $insight['message'] }}"
                                </p>

                                @if(isset($insight['actionableStep']))
                                    <div class="bg-surface-container-lowest/80 p-3 border border-outline-variant/10 flex gap-3 items-start mt-2">
                                        <span class="material-symbols-outlined text-primary-fixed-dim text-[16px] shrink-0 mt-0.5">subdirectory_arrow_right</span>
                                        <p class="font-body-md text-xs text-on-surface-variant leading-relaxed">
                                            <strong class="text-primary font-semibold">ACTION SCHEMATIC:</strong> {{ $insight['actionableStep'] }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        <!-- Telemetry Integrity Panel (4 Columns) -->
        <div class="col-span-1 md:col-span-4 space-y-6">
            <div class="glass-panel p-8 space-y-6">
                <h3 class="font-headline-lg text-base text-primary font-light flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary-fixed-dim">analytics</span>
                    <span>System Integrity</span>
                </h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center pb-2 border-b border-white/5">
                        <span class="font-label-caps text-[10px] text-outline uppercase tracking-wider">Safe Runway</span>
                        <span class="font-data-mono text-sm font-bold {{ $summary['safeToSpend'] > 0 ? 'text-primary-fixed-dim' : 'text-error' }}">
                            {{ format_currency($summary['safeToSpend'], $currency) }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center pb-2 border-b border-white/5">
                        <span class="font-label-caps text-[10px] text-outline uppercase tracking-wider">Gross Liabilities</span>
                        <span class="font-data-mono text-sm font-bold text-primary">
                            {{ format_currency($debts->where('is_paid_off', false)->sum('current_balance'), $currency) }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="font-label-caps text-[10px] text-outline uppercase tracking-wider">Runway Days</span>
                        <span class="font-data-mono text-sm font-bold text-primary">{{ $summary['daysUntilPayday'] ?? 0 }} Days</span>
                    </div>
                </div>
            </div>

            <!-- Tactical Links -->
            <div class="glass-panel p-6 space-y-4">
                <h4 class="font-label-caps text-[10px] text-outline tracking-wider uppercase">Quick Operations</h4>
                <div class="space-y-2">
                    <a href="/survival" class="flex items-center justify-between p-3 bg-surface-container-high hover:bg-surface-variant border border-white/5 font-label-caps text-[10px] text-primary uppercase tracking-wider transition-all">
                        <span>Optimize Runway</span>
                        <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                    </a>
                    <a href="/debt" class="flex items-center justify-between p-3 bg-surface-container-high hover:bg-surface-variant border border-white/5 font-label-caps text-[10px] text-primary uppercase tracking-wider transition-all">
                        <span>Simulate Paydowns</span>
                        <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
async function handleAskNova(event) {
    event.preventDefault();
    
    const questionInput = document.getElementById('novaQuestion');
    const submitBtn = document.getElementById('novaSubmitBtn');
    const btnText = document.getElementById('novaBtnText');
    const responseArea = document.getElementById('novaResponseArea');
    const responseText = document.getElementById('novaResponseText');
    const responseIcon = document.getElementById('novaResponseIcon');
    const responseIconWrapper = document.getElementById('novaResponseIconWrapper');
    
    const question = questionInput.value.trim();
    if (!question) return;

    // Loading state
    questionInput.disabled = true;
    submitBtn.disabled = true;
    btnText.innerText = 'Thinking...';
    responseArea.classList.add('hidden');
    
    try {
        const personality = new URLSearchParams(window.location.search).get('personality') || 'tough_love';
        
        const response = await fetch('/coach/ask', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ question, personality })
        });

        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        const data = await response.json();
        
        // Setup colors based on response type
        let iconColorClass = 'text-primary-fixed-dim';
        let borderColorClass = 'border-primary-fixed-dim/20';
        let bgColorClass = 'bg-primary-fixed-dim/5';

        if (data.type === 'danger') {
            iconColorClass = 'text-error';
            borderColorClass = 'border-error/20';
            bgColorClass = 'bg-error/5';
        } else if (data.type === 'warning') {
            iconColorClass = 'text-warning';
            borderColorClass = 'border-warning/20';
            bgColorClass = 'bg-warning/5';
        } else if (data.type === 'success') {
            iconColorClass = 'text-primary-fixed-dim';
            borderColorClass = 'border-primary-fixed-dim/20';
            bgColorClass = 'bg-primary-fixed-dim/5';
        } else {
            iconColorClass = 'text-secondary-fixed-dim';
            borderColorClass = 'border-secondary-container/20';
            bgColorClass = 'bg-secondary-container/5';
        }

        // Apply styles
        responseIconWrapper.className = `mt-0.5 flex-shrink-0 bg-surface-container-lowest p-2 border ${borderColorClass} flex items-center justify-center`;
        responseArea.className = `mt-5 p-5 border ${borderColorClass} ${bgColorClass} backdrop-blur-sm relative overflow-hidden animate-fade-in`;
        responseIcon.className = `material-symbols-outlined text-[20px] ${iconColorClass}`;
        
        // Convert simple markdown (bold)
        let formattedAnswer = data.answer.replace(/\*\*(.*?)\*\*/g, '<strong class="text-primary font-semibold">$1</strong>');
        
        responseIcon.innerText = data.icon || 'auto_awesome';
        responseText.innerHTML = formattedAnswer;
        responseArea.classList.remove('hidden');

    } catch (error) {
        console.error('Error asking Nova:', error);
        responseIconWrapper.className = `mt-0.5 flex-shrink-0 bg-surface-container-lowest p-2 border border-error/20 flex items-center justify-center`;
        responseArea.className = `mt-5 p-5 border border-error/20 bg-error/5 backdrop-blur-sm relative overflow-hidden animate-fade-in`;
        responseIcon.className = `material-symbols-outlined text-[20px] text-error`;
        responseIcon.innerText = 'error';
        responseText.innerHTML = '<span class="text-error">Connection to Nova Core interrupted. Please try again.</span>';
        responseArea.classList.remove('hidden');
    } finally {
        questionInput.disabled = false;
        submitBtn.disabled = false;
        btnText.innerText = 'Ask';
        questionInput.focus();
    }
}
</script>
@endsection
