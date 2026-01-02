<x-filament-widgets::widget>
    <x-filament::section>
        <div class="processing-timeline-widget">
            {{-- Header --}}
            <div class="widget-header">
                <h3 class="widget-title">
                    <span class="title-icon">📈</span>
                    Verwerkingstijd Trend
                </h3>
                @if($has_data)
                <span class="widget-subtitle">Laatste 30 dagen • Gemiddeld: {{ $avg_minutes }} min</span>
                @endif
            </div>
            
            @if(!$has_data)
            {{-- Empty State --}}
            <div class="empty-state">
                <div class="empty-icon">📊</div>
                <div class="empty-title">Nog geen trenddata</div>
                <div class="empty-description">Upload documenten om trends te zien</div>
            </div>
            @else
            {{-- Trend Indicator --}}
            @if($trend_direction !== 'stable')
            <div class="trend-indicator trend-{{ $trend_direction }}">
                @if($trend_direction === 'improving')
                <span class="trend-icon">📉</span>
                <span class="trend-text">
                    <strong>Verbetering!</strong> Verwerkingstijd is {{ $trend_percentage }}% sneller geworden
                </span>
                @else
                <span class="trend-icon">📈</span>
                <span class="trend-text">
                    <strong>Let op:</strong> Verwerkingstijd is {{ $trend_percentage }}% toegenomen
                </span>
                @endif
            </div>
            @endif
            
            {{-- Chart Container --}}
            <div class="chart-container" wire:ignore>
                <canvas id="processing-timeline-chart-{{ $this->getId() }}"></canvas>
            </div>
            
            {{-- Legend --}}
            <div class="chart-legend">
                <div class="legend-item">
                    <span class="legend-color" style="background-color: #3b82f6;"></span>
                    <span class="legend-label">Verwerkingstijd (minuten)</span>
                </div>
                <div class="legend-item">
                    <span class="legend-color" style="background-color: #10b981;"></span>
                    <span class="legend-label">Aantal documenten</span>
                </div>
            </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

@if($has_data)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartId = 'processing-timeline-chart-{{ $this->getId() }}';
    const ctx = document.getElementById(chartId);
    
    if (!ctx) return;
    
    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#d1d5db' : '#374151';
    const gridColor = isDark ? '#374151' : '#e5e7eb';
    
    const data = {
        labels: @js(collect($trend)->pluck('label')->toArray()),
        datasets: [
            {
                label: 'Verwerkingstijd (min)',
                data: @js(collect($trend)->pluck('avg_minutes')->toArray()),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true,
                yAxisID: 'y',
            },
            {
                label: 'Aantal docs',
                data: @js(collect($trend)->pluck('count')->toArray()),
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4,
                fill: false,
                yAxisID: 'y1',
            }
        ]
    };
    
    new Chart(ctx, {
        type: 'line',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    backgroundColor: isDark ? '#1f2937' : '#ffffff',
                    titleColor: textColor,
                    bodyColor: textColor,
                    borderColor: gridColor,
                    borderWidth: 1,
                    padding: 12,
                    displayColors: true,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += context.parsed.y.toFixed(1);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        color: gridColor,
                        drawBorder: false,
                    },
                    ticks: {
                        color: textColor,
                        maxRotation: 45,
                        minRotation: 0,
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Minuten',
                        color: textColor,
                    },
                    grid: {
                        color: gridColor,
                        drawBorder: false,
                    },
                    ticks: {
                        color: textColor,
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Aantal',
                        color: textColor,
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                    ticks: {
                        color: textColor,
                    }
                },
            }
        }
    });
});
</script>
@endif

<style>
.processing-timeline-widget {
    padding: 0.5rem 0;
}

.widget-header {
    margin-bottom: 1rem;
}

.widget-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
    margin: 0 0 0.25rem 0;
}

.dark .widget-title {
    color: #f9fafb;
}

.title-icon {
    font-size: 1.25rem;
}

.widget-subtitle {
    font-size: 0.875rem;
    color: #6b7280;
}

.dark .widget-subtitle {
    color: #9ca3af;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 2rem 1rem;
}

.empty-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.empty-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
}

.dark .empty-title {
    color: #d1d5db;
}

.empty-description {
    font-size: 0.875rem;
    color: #6b7280;
}

.dark .empty-description {
    color: #9ca3af;
}

/* Trend Indicator */
.trend-indicator {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
}

.trend-improving {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    border: 1px solid #6ee7b7;
}

.dark .trend-improving {
    background: linear-gradient(135deg, #064e3b 0%, #065f46 100%);
    border-color: #059669;
}

.trend-declining {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border: 1px solid #fcd34d;
}

.dark .trend-declining {
    background: linear-gradient(135deg, #78350f 0%, #92400e 100%);
    border-color: #f59e0b;
}

.trend-icon {
    font-size: 1.5rem;
}

.trend-text {
    font-size: 0.875rem;
    line-height: 1.5;
}

.trend-improving .trend-text {
    color: #065f46;
}

.dark .trend-improving .trend-text {
    color: #6ee7b7;
}

.trend-declining .trend-text {
    color: #92400e;
}

.dark .trend-declining .trend-text {
    color: #fcd34d;
}

.trend-text strong {
    font-weight: 600;
}

/* Chart Container */
.chart-container {
    position: relative;
    height: 220px;
    margin-bottom: 1rem;
}

/* Legend */
.chart-legend {
    display: flex;
    gap: 1.5rem;
    justify-content: center;
    flex-wrap: wrap;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.legend-color {
    width: 1rem;
    height: 1rem;
    border-radius: 0.25rem;
}

.legend-label {
    font-size: 0.875rem;
    color: #6b7280;
}

.dark .legend-label {
    color: #9ca3af;
}
</style>

