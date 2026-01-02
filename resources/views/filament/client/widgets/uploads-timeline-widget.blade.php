@php
    $data = $this->getViewData();
    $timeline = $data['timeline'];
    $total = $data['total'];
    $avg = $data['avg'];
    
    // Prepare chart data
    $labels = array_column($timeline, 'label');
    $counts = array_column($timeline, 'count');
@endphp

<x-filament-widgets::widget>
    <div class="uploads-timeline-widget p-3 sm:p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="mb-3">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white">
                        📈 Uploads Laatste 30 Dagen
                    </h3>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">
                        Dagelijkse uploads overzicht
                    </p>
                </div>
                <div class="flex items-center gap-3 text-xs sm:text-sm">
                    <div>
                        <span class="text-gray-600 dark:text-gray-400">Totaal:</span>
                        <span class="ml-1 font-semibold text-gray-900 dark:text-white">{{ $total }}</span>
                    </div>
                    <div>
                        <span class="text-gray-600 dark:text-gray-400">Gemiddeld:</span>
                        <span class="ml-1 font-semibold text-gray-900 dark:text-white">{{ $avg }}/dag</span>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Chart Container - Slimmer --}}
        <div class="relative" style="height: 180px;">
            <canvas id="uploadsTimelineChart-{{ $this->getId() }}" wire:ignore></canvas>
        </div>
    </div>
</x-filament-widgets::widget>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function() {
    const chartId = 'uploadsTimelineChart-{{ $this->getId() }}';
    
    function initChart() {
        const ctx = document.getElementById(chartId);
        if (!ctx || ctx.chartInstance) return;
        
        const labels = @json($labels);
        const counts = @json($counts);
        
        ctx.chartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Uploads',
                    data: counts,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    pointBackgroundColor: 'rgb(59, 130, 246)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
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
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 10,
                        titleFont: { size: 12 },
                        bodyFont: { size: 12 },
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y + ' documenten';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            font: { size: 11 }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45,
                            font: { size: 10 }
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initChart);
    } else {
        setTimeout(initChart, 100);
    }
    
    // Reinitialize on Livewire updates
    document.addEventListener('livewire:load', initChart);
    document.addEventListener('livewire:update', initChart);
})();
</script>

