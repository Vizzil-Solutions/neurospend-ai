@extends('layouts.app')

@section('title', 'NEUROSPEND | Intelligence Radar')
@section('page_title', 'INTELLIGENCE RADAR')

@section('content')
<div class="space-y-gutter animate-fade-in pb-16">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pt-2">
        <div class="space-y-unit">
            <h1 class="font-display-lg text-3xl text-primary font-light flex items-center gap-3 tracking-tight">
                <span class="material-symbols-outlined text-primary-fixed-dim text-[32px]">pie_chart</span>
                <span>Intelligence Radar</span>
            </h1>
            <p class="font-body-md text-on-surface-variant uppercase tracking-wider text-xs">Visualizing resource trajectories, spend profiles, and cumulative flux indices</p>
        </div>
        <div class="h-12 px-6 bg-surface-container-lowest border border-white/5 flex items-center gap-3 font-label-caps text-[11px] tracking-widest uppercase text-on-surface-variant shrink-0">
            <span class="material-symbols-outlined text-[16px] text-primary-fixed-dim">calendar_month</span>
            <span>{{ date('F Y') }}</span>
        </div>
    </div>

    <!-- Stats Bento Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-gutter">
        <div class="glass-panel p-8 relative overflow-hidden group">
            <span class="block font-label-caps text-label-caps text-outline tracking-wider uppercase mb-4">GROSS PAYLOAD (EXPENSES)</span>
            <div class="font-data-mono text-[42px] font-bold text-primary leading-none mb-6">
                {{ format_currency($currentSpendTotal, $currency) }}
            </div>
            <div class="flex items-center gap-2 font-label-caps text-[10px] tracking-wider {{ $isSpendingUp ? 'text-error' : 'text-primary-fixed-dim' }} bg-surface-container-high px-3 py-1.5 border border-white/5">
                <span class="material-symbols-outlined text-[14px]">
                    {{ $isSpendingUp ? 'trending_up' : 'trending_down' }}
                </span>
                <span>{{ number_format(abs($momChange), 1) }}% {{ $isSpendingUp ? 'ACCELERATION' : 'DECELERATION' }}</span>
            </div>
        </div>

        <div class="glass-panel p-8 relative overflow-hidden group">
            <span class="block font-label-caps text-label-caps text-outline tracking-wider uppercase mb-4">BURN VELOCITY</span>
            <div class="font-data-mono text-[42px] font-bold text-primary leading-none mb-6">
                {{ format_currency($dailyAverage, $currency) }}
            </div>
            <div class="font-label-caps text-[10px] text-outline tracking-wider uppercase flex items-center gap-2 pt-2">
                <span class="material-symbols-outlined text-primary-fixed-dim text-[14px]">bolt</span>
                <span>Average daily throughput</span>
            </div>
        </div>

        <div class="bg-primary-fixed text-on-primary-fixed p-8 relative overflow-hidden group flex flex-col justify-between">
            <span class="block font-label-caps text-label-caps text-on-primary-fixed/60 tracking-wider uppercase mb-4">STRATEGIC ANOMALY RADAR</span>
            <div class="font-body-md text-on-primary-fixed text-base font-bold leading-relaxed">
                @if(count($categoryBreakdown) > 0)
                    Primary drain identified in <strong class="underline decoration-wavy underline-offset-4">{{ $categoryBreakdown[0]['name'] }}</strong>, 
                    comprising <strong class="text-white">{{ number_format(($categoryBreakdown[0]['value'] / max(1, $currentSpendTotal)) * 100, 0) }}%</strong> of total payload.
                @else
                    Awaiting sufficient telemetry for category drain analysis.
                @endif
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter">
        <!-- Pace Calibration Line Chart -->
        <div class="lg:col-span-8 glass-panel p-8 h-[450px] flex flex-col justify-between relative overflow-hidden">
            <h3 class="font-label-caps text-label-caps text-secondary tracking-widest uppercase mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary-fixed-dim">analytics</span>
                <span>Pace Calibration (Cumulative)</span>
            </h3>
            <div class="relative flex-1" style="min-height: 0;">
                <canvas id="paceChart" class="w-full h-full"></canvas>
            </div>
        </div>

        <!-- Category Pie Chart -->
        <div class="lg:col-span-4 glass-panel p-8 h-[450px] flex flex-col justify-between relative overflow-hidden">
            <h3 class="font-label-caps text-label-caps text-secondary tracking-widest uppercase mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary-fixed-dim">pie_chart</span>
                <span>Category Distribution</span>
            </h3>
            <div class="relative flex-1" style="min-height: 0;">
                @if(count($categoryBreakdown) === 0)
                    <div class="absolute inset-0 flex items-center justify-center text-center">
                        <div class="space-y-3">
                            <span class="material-symbols-outlined text-[36px] text-outline opacity-40">clinical_notes</span>
                            <p class="font-label-caps text-[10px] text-outline uppercase tracking-wider">No category distribution telemetry available.</p>
                        </div>
                    </div>
                @else
                    <canvas id="categoryChart" class="w-full h-full"></canvas>
                @endif
            </div>
        </div>

        <!-- Monthly Balance Bar Chart -->
        <div class="lg:col-span-12 glass-panel p-8 h-[380px] flex flex-col justify-between relative overflow-hidden">
            <h3 class="font-label-caps text-label-caps text-secondary tracking-widest uppercase mb-6 flex items-center gap-2">
                <span class="material-symbols-outlined text-primary-fixed-dim">query_stats</span>
                <span>Monthly Flux Comparison</span>
            </h3>
            <div class="relative flex-1" style="min-height: 0;">
                <canvas id="flowChart" class="w-full h-full"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const trendData = {!! json_encode($trendData) !!};
    const categoryBreakdown = {!! json_encode($categoryBreakdown) !!};
    const comparisonData = {!! json_encode($comparisonData) !!};

    const chartColors = [
        '#00e290', // Emerald Accent
        '#00e0ff', // Secondary Blue
        '#e9b3ff', // Tertiary Amethyst
        '#ffb4ab', // Error Pink
        '#fffbff', // White Accent
        '#849588', // Outline Gray
        '#3a4a3f'  // Dark outline
    ];

    // 1. Pacing Chart (Cumulative line graph)
    const days = trendData.map(d => `D${d.day}`);
    const thisMonthCumulative = trendData.map(d => d['This Month']);
    const lastMonthCumulative = trendData.map(d => d['Last Month']);

    const ctxPace = document.getElementById('paceChart')?.getContext('2d');
    if (ctxPace) {
        new Chart(ctxPace, {
            type: 'line',
            data: {
                labels: days,
                datasets: [
                    {
                        label: 'This Month',
                        data: thisMonthCumulative,
                        borderColor: '#00e290',
                        borderWidth: 3,
                        pointRadius: 0,
                        pointHoverRadius: 6,
                        tension: 0.3,
                        fill: false
                    },
                    {
                        label: 'Last Month',
                        data: lastMonthCumulative,
                        borderColor: 'rgba(255, 255, 255, 0.15)',
                        borderWidth: 1.5,
                        borderDash: [5, 5],
                        pointRadius: 0,
                        pointHoverRadius: 0,
                        tension: 0.3,
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: '#849588',
                            font: { family: 'JetBrains Mono', size: 10, weight: 'bold' }
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: '#111318',
                        titleColor: '#e2e2e8',
                        bodyColor: '#e2e2e8',
                        borderColor: 'rgba(255, 255, 255, 0.08)',
                        borderWidth: 1,
                        padding: 12,
                        cornerRadius: 0,
                        titleFont: { family: 'JetBrains Mono', size: 11 },
                        bodyFont: { family: 'JetBrains Mono', size: 12 }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            color: '#849588',
                            font: { family: 'JetBrains Mono', size: 10 },
                            callback: function(val, index) {
                                return index % 5 === 0 ? this.getLabelForValue(val) : '';
                            }
                        }
                    },
                    y: {
                        grid: { color: 'rgba(255, 255, 255, 0.04)' },
                        ticks: {
                            color: '#849588',
                            font: { family: 'JetBrains Mono', size: 10 }
                        }
                    }
                }
            }
        });
    }

    // 2. Category Pie Chart
    const ctxCat = document.getElementById('categoryChart')?.getContext('2d');
    if (ctxCat && categoryBreakdown.length > 0) {
        new Chart(ctxCat, {
            type: 'doughnut',
            data: {
                labels: categoryBreakdown.map(c => c.name),
                datasets: [{
                    data: categoryBreakdown.map(c => c.value),
                    backgroundColor: chartColors.slice(0, categoryBreakdown.length),
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#849588',
                            boxWidth: 8,
                            padding: 12,
                            font: { family: 'JetBrains Mono', size: 9, weight: 'bold' }
                        }
                    },
                    tooltip: {
                        backgroundColor: '#111318',
                        titleColor: '#e2e2e8',
                        bodyColor: '#e2e2e8',
                        borderColor: 'rgba(255, 255, 255, 0.08)',
                        borderWidth: 1,
                        padding: 12,
                        cornerRadius: 0,
                        titleFont: { family: 'JetBrains Mono', size: 11 },
                        bodyFont: { family: 'JetBrains Mono', size: 12 }
                    }
                }
            }
        });
    }

    // 3. Flow Chart (Income vs Expense Side-by-Side Bar Chart)
    const ctxFlow = document.getElementById('flowChart')?.getContext('2d');
    if (ctxFlow) {
        new Chart(ctxFlow, {
            type: 'bar',
            data: {
                labels: ['Last Month', 'This Month'],
                datasets: [
                    {
                        label: 'Income',
                        data: [comparisonData[0].Income, comparisonData[1].Income],
                        backgroundColor: '#00e290',
                        borderRadius: 0
                    },
                    {
                        label: 'Expense',
                        data: [comparisonData[0].Expense, comparisonData[1].Expense],
                        backgroundColor: '#e9b3ff',
                        borderRadius: 0
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: '#849588',
                            font: { family: 'JetBrains Mono', size: 10, weight: 'bold' }
                        }
                    },
                    tooltip: {
                        backgroundColor: '#111318',
                        titleColor: '#e2e2e8',
                        bodyColor: '#e2e2e8',
                        borderColor: 'rgba(255, 255, 255, 0.08)',
                        borderWidth: 1,
                        padding: 12,
                        cornerRadius: 0,
                        titleFont: { family: 'JetBrains Mono', size: 11 },
                        bodyFont: { family: 'JetBrains Mono', size: 12 }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            color: '#849588',
                            font: { family: 'JetBrains Mono', size: 10, weight: 'bold' }
                        }
                    },
                    y: {
                        grid: { color: 'rgba(255, 255, 255, 0.04)' },
                        ticks: {
                            color: '#849588',
                            font: { family: 'JetBrains Mono', size: 10 }
                        }
                    }
                }
            }
        });
    }
</script>
@endsection
