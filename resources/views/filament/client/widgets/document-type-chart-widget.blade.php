@php
    $data = $this->getViewData();
    $types = $data['types'];
    $total = $data['total'];
    
    // Prepare chart data
    $labels = array_column($types, 'label');
    $counts = array_column($types, 'count');
    $colors = [
        'rgb(59, 130, 246)',   // Blue
        'rgb(34, 197, 94)',    // Green
        'rgb(245, 158, 11)',   // Amber
        'rgb(147, 51, 234)',   // Purple
        'rgb(239, 68, 68)',    // Red
    ];
@endphp

<x-filament-widgets::widget>
    <div class="document-type-chart-widget p-3 sm:p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="mb-3">
            <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white">
                📋 Document Types
            </h3>
            <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">
                Verdeling per type
            </p>
        </div>
        
        @if($total > 0)
            {{-- Chart Container - Slimmer --}}
            <div class="relative mb-3" style="height: 140px;">
                <canvas id="documentTypeChart-{{ $this->getId() }}" wire:ignore></canvas>
            </div>
            
            {{-- Legend - Compact --}}
            <div class="space-y-1.5">
                @foreach($types as $index => $type)
                    <div class="flex items-center justify-between text-xs sm:text-sm">
                        <div class="flex items-center gap-1.5">
                            <div class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: {{ $colors[$index % count($colors)] }};"></div>
                            <span class="text-gray-700 dark:text-gray-300 truncate">{{ $type['label'] }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 flex-shrink-0">
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $type['count'] }}</span>
                            <span class="text-gray-500 dark:text-gray-400 text-xs">
                                ({{ $total > 0 ? round(($type['count'] / $total) * 100, 1) : 0 }}%)
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-6 text-gray-500 dark:text-gray-400">
                <p class="text-sm">Nog geen documenten geüpload</p>
            </div>
        @endif
    </div>
</x-filament-widgets::widget>

@if($total > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function() {
    const chartId = 'documentTypeChart-{{ $this->getId() }}';
    
    function initChart() {
        const ctx = document.getElementById(chartId);
        if (!ctx || ctx.chartInstance) return;
        
        const labels = @json($labels);
        const counts = @json($counts);
        const colors = @json($colors);
        
        ctx.chartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: counts,
                    backgroundColor: colors.slice(0, labels.length),
                    borderWidth: 2,
                    borderColor: '#fff'
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
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                return label + ': ' + value + ' (' + percentage + '%)';
                            }
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
    
    document.addEventListener('livewire:load', initChart);
    document.addEventListener('livewire:update', initChart);
})();
</script>
@endif

