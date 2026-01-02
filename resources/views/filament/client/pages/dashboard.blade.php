<x-filament-panels::page>
    @php
        $user = auth()->user();
        $clientId = $user->client_id ?? null;
        $userName = $user->name ?? 'Gebruiker';
        $firstName = explode(' ', $userName)[0];
        
        // Time-based greeting
        $hour = now()->hour;
        $greeting = $hour < 12 ? 'Goedemorgen' : ($hour < 18 ? 'Goedemiddag' : 'Goedenavond');
        
        // Get dashboard data from component (with error handling)
        try {
            $metrics = $this->getMetrics();
            $recentActivity = $this->getRecentActivity();
            $insights = $this->getInsights();
            $trends = $this->getTrends();
            $spendingAnalytics = $this->getSpendingAnalytics();
            $topSuppliers = $this->getTopSuppliers();
            $uploadsTimeline = $this->getUploadsTimeline();
            $monthlyComparison = $this->getMonthlyComparison();
            $documentTypes = $this->getDocumentTypeBreakdown();
            $openTasks = \App\Models\Task::where('client_id', $clientId)->where('status', 'open')->count();
        } catch (\Exception $e) {
            // Fallback if methods fail
            $metrics = [];
            $recentActivity = [];
            $insights = [];
            $trends = [];
            $spendingAnalytics = [];
            $topSuppliers = [];
            $uploadsTimeline = [];
            $monthlyComparison = [];
            $documentTypes = [];
            $openTasks = 0;
        }
    @endphp

    <div class="dashboard-page-container" style="display: block !important; visibility: visible !important; opacity: 1 !important; position: relative !important; z-index: 9999 !important;">
        {{-- Hero Header Section --}}
        <div class="dashboard-hero" style="display: block !important; visibility: visible !important;">
            <div class="hero-content">
                <div class="hero-text">
                    <h1 class="hero-greeting">{{ $greeting }}, {{ $firstName }}! 👋</h1>
                    <p class="hero-date">{{ now()->translatedFormat('l, d F Y') }}</p>
                </div>
                <div class="hero-actions">
                    <a href="{{ \App\Filament\Client\Pages\SmartUpload::getUrl() }}" class="hero-upload-btn">
                        <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span class="btn-text">Upload</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Alert Banner - Conditional (Tasks) --}}
        @if($openTasks > 0)
        <div class="alert-banner alert-warning">
            <div class="alert-content">
                <svg class="alert-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div class="alert-text">
                    <strong>{{ $openTasks }} openstaande {{ $openTasks === 1 ? 'taak' : 'taken' }}</strong> vereisen uw aandacht.
                </div>
                <a href="{{ \App\Filament\Client\Pages\MijnDocumenten::getUrl() }}" class="alert-action">
                    Bekijk Taken
                </a>
            </div>
        </div>
        @endif

        {{-- Key Metrics Grid - Responsive --}}
        <div class="metrics-grid">
            @foreach($metrics as $metric)
            <div class="metric-card" data-type="{{ $metric['type'] ?? 'primary' }}">
                <div class="metric-icon">{{ $metric['icon'] }}</div>
                <div class="metric-content">
                    <div class="metric-label">{{ $metric['label'] }}</div>
                    <div class="metric-value">{{ $metric['value'] }}</div>
                    @if(isset($metric['trend']))
                    <div class="metric-trend trend-{{ $metric['trend']['direction'] }}">
                        <span class="trend-icon">{{ $metric['trend']['icon'] }}</span>
                        <span class="trend-text">{{ $metric['trend']['text'] }}</span>
                    </div>
                    @endif
                    @if(isset($metric['subtext']))
                    <div class="metric-subtext">{{ $metric['subtext'] }}</div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        {{-- Quick Actions Section --}}
        <div class="actions-section">
            <h2 class="section-title">
                <span class="title-icon">⚡</span>
                <span>Snelle Acties</span>
            </h2>
            <div class="actions-grid">
                <a href="{{ \App\Filament\Client\Pages\SmartUpload::getUrl() }}" class="action-card action-primary">
                    <div class="action-icon">📸</div>
                    <div class="action-content">
                        <div class="action-title">Document Uploaden</div>
                        <div class="action-desc">Maak foto of upload bestand</div>
                    </div>
                    <svg class="action-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
                
                <a href="{{ \App\Filament\Client\Pages\MijnDocumenten::getUrl() }}" class="action-card">
                    <div class="action-icon">📄</div>
                    <div class="action-content">
                        <div class="action-title">Mijn Documenten</div>
                        <div class="action-desc">Bekijk alle uploads</div>
                    </div>
                    <svg class="action-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
                
                <a href="{{ \App\Filament\Client\Pages\FactuurMaken::getUrl() }}" class="action-card">
                    <div class="action-icon">🧾</div>
                    <div class="action-content">
                        <div class="action-title">Factuur Maken</div>
                        <div class="action-desc">Verkoopfactuur aanmaken</div>
                    </div>
                    <svg class="action-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
                
                <a href="{{ \App\Filament\Client\Pages\Rapporten::getUrl() }}" class="action-card">
                    <div class="action-icon">📊</div>
                    <div class="action-content">
                        <div class="action-title">Rapporten</div>
                        <div class="action-desc">Analytics & inzichten</div>
                    </div>
                    <svg class="action-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        </div>

        {{-- Enhanced Analytics Section --}}
        <div class="analytics-section">
            <h2 class="section-title">
                <span class="title-icon">📊</span>
                <span>Analytics & Inzichten</span>
            </h2>
            
            <div class="analytics-grid">
                {{-- Spending Analytics Card --}}
                <div class="analytics-card spending-card">
                    <div class="analytics-card-header">
                        <h3 class="analytics-card-title">💰 Uitgaven Deze Maand</h3>
                        <div class="analytics-card-value">
                            @if(!empty($spendingAnalytics))
                                €{{ number_format($spendingAnalytics['thisMonth'] ?? 0, 2, ',', '.') }}
                            @else
                                €0,00
                            @endif
                        </div>
                    </div>
                    <div class="analytics-card-body">
                        @if(!empty($spendingAnalytics) && isset($spendingAnalytics['change']) && $spendingAnalytics['change'] != 0)
                        <div class="analytics-trend trend-{{ $spendingAnalytics['change'] > 0 ? 'positive' : 'negative' }}">
                            <span class="trend-icon">{{ $spendingAnalytics['change'] > 0 ? '↑' : '↓' }}</span>
                            <span class="trend-text">{{ abs($spendingAnalytics['change']) }}% vs vorige maand</span>
                        </div>
                        @endif
                        <div class="analytics-stats">
                            <div class="stat-item">
                                <span class="stat-label">Dit jaar:</span>
                                <span class="stat-value">
                                    @if(!empty($spendingAnalytics))
                                        €{{ number_format($spendingAnalytics['thisYear'] ?? 0, 2, ',', '.') }}
                                    @else
                                        €0,00
                                    @endif
                                </span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Gemiddeld/maand:</span>
                                <span class="stat-value">
                                    @if(!empty($spendingAnalytics))
                                        €{{ number_format($spendingAnalytics['avgPerMonth'] ?? 0, 2, ',', '.') }}
                                    @else
                                        €0,00
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Top Suppliers Card --}}
                <div class="analytics-card suppliers-card">
                    <div class="analytics-card-header">
                        <h3 class="analytics-card-title">🏢 Top Leveranciers</h3>
                    </div>
                    <div class="analytics-card-body">
                        @if(!empty($topSuppliers) && count($topSuppliers) > 0)
                        <div class="suppliers-list">
                            @foreach(array_slice($topSuppliers, 0, 5) as $index => $supplier)
                            <div class="supplier-item">
                                <div class="supplier-rank">{{ $index + 1 }}</div>
                                <div class="supplier-info">
                                    <div class="supplier-name">{{ $supplier['name'] }}</div>
                                    <div class="supplier-meta">
                                        <span>{{ $supplier['count'] }} {{ $supplier['count'] === 1 ? 'document' : 'documenten' }}</span>
                                        <span class="supplier-amount">€{{ number_format($supplier['amount'], 2, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="empty-state">
                            <p>Nog geen leveranciers data beschikbaar</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Charts Section --}}
        <div class="charts-section">
            <h2 class="section-title">
                <span class="title-icon">📈</span>
                <span>Grafieken & Trends</span>
            </h2>
            
            <div class="charts-grid">
                {{-- Uploads Timeline Chart --}}
                <div class="chart-card">
                    <div class="chart-header">
                        <h3 class="chart-title">📤 Uploads Laatste 30 Dagen</h3>
                        @if(!empty($uploadsTimeline) && !empty($uploadsTimeline['timeline']))
                        <div class="chart-stats">
                            <span class="chart-stat">Totaal: {{ $uploadsTimeline['total'] }}</span>
                            <span class="chart-stat">Gemiddeld: {{ $uploadsTimeline['avg'] }}/dag</span>
                        </div>
                        @endif
                    </div>
                    <div class="chart-body">
                        @if(!empty($uploadsTimeline) && !empty($uploadsTimeline['timeline']))
                        <canvas id="uploadsTimelineChart" height="80"></canvas>
                        @else
                        <div class="empty-state">
                            <p>Nog geen upload data beschikbaar</p>
                        </div>
                        @endif
                    </div>
                </div>
                
                {{-- Monthly Comparison Chart --}}
                <div class="chart-card">
                    <div class="chart-header">
                        <h3 class="chart-title">📅 Maandelijkse Vergelijking</h3>
                    </div>
                    <div class="chart-body">
                        @if(!empty($monthlyComparison))
                        <canvas id="monthlyComparisonChart" height="80"></canvas>
                        @else
                        <div class="empty-state">
                            <p>Nog geen maandelijkse data beschikbaar</p>
                        </div>
                        @endif
                    </div>
                </div>
                
                {{-- Document Types Chart --}}
                <div class="chart-card">
                    <div class="chart-header">
                        <h3 class="chart-title">📄 Document Types</h3>
                    </div>
                    <div class="chart-body">
                        @if(!empty($documentTypes))
                        <canvas id="documentTypesChart" height="80"></canvas>
                        @else
                        <div class="empty-state">
                            <p>Nog geen document type data beschikbaar</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Activity Timeline & Insights Side by Side --}}
        <div class="insights-activity-grid">
            {{-- Recent Activity Timeline --}}
            <div class="activity-section">
                <h2 class="section-title">
                    <span class="title-icon">🕐</span>
                    <span>Recente Activiteit</span>
                </h2>
                <div class="activity-timeline">
                    @forelse($recentActivity as $activity)
                    <div class="activity-item">
                        <div class="activity-icon activity-{{ $activity['type'] }}">
                            {{ $activity['icon'] }}
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">{{ $activity['title'] }}</div>
                            <div class="activity-time">{{ $activity['time'] }}</div>
                        </div>
                    </div>
                    @empty
                    <div class="activity-empty">
                        <p>Nog geen recente activiteit</p>
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Insights Panel --}}
            <div class="insights-section">
                <h2 class="section-title">
                    <span class="title-icon">💡</span>
                    <span>Inzichten & Aanbevelingen</span>
                </h2>
                <div class="insights-list">
                    @forelse($insights as $insight)
                    <div class="insight-item insight-{{ $insight['type'] }}">
                        <div class="insight-icon">{{ $insight['icon'] }}</div>
                        <div class="insight-content">
                            <div class="insight-title">{{ $insight['title'] }}</div>
                            <div class="insight-text">{{ $insight['text'] }}</div>
                        </div>
                    </div>
                    @empty
                    <div class="insights-empty">
                        <p>Geen nieuwe inzichten</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
        
        {{-- Widgets Section - Render widgets normally but ensure JS is loaded --}}
        <div class="widgets-section">
            @php
                $user = auth()->user();
                $clientId = $user->client_id ?? null;
                $openTasks = \App\Models\Task::where('client_id', $clientId)->where('status', 'open')->count();
            @endphp
            
            {{-- My Documents Widget --}}
            <div class="widget-wrapper">
                @livewire(\App\Filament\Client\Widgets\MyDocumentsWidget::class)
            </div>
            
            {{-- My Tasks Widget (only if there are open tasks) --}}
            @if($openTasks > 0)
            <div class="widget-wrapper">
                @livewire(\App\Filament\Client\Widgets\MyTasksWidget::class)
            </div>
            @endif
        </div>
        
        @push('scripts')
        {{-- Chart.js Library --}}
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js" defer></script>
        
        <script>
            // Global chart instances storage
            window.dashboardCharts = {};
            
            // Wait for Chart.js to load
            function waitForChartJS(callback, maxAttempts = 50) {
                let attempts = 0;
                const checkChart = () => {
                    attempts++;
                    if (typeof Chart !== 'undefined' && Chart.Chart) {
                        callback();
                    } else if (attempts < maxAttempts) {
                        setTimeout(checkChart, 100);
                    } else {
                        console.error('Chart.js failed to load after', maxAttempts, 'attempts');
                    }
                };
                checkChart();
            }
            
            // Initialize charts when DOM is ready
            function initDashboard() {
                // Wait for Chart.js
                waitForChartJS(() => {
                    console.log('Chart.js loaded, initializing charts...');
                    setTimeout(() => initializeCharts(), 300); // Small delay to ensure DOM is ready
                });
                
                // Filament components
                if (typeof window.Alpine !== 'undefined') {
                    Alpine.nextTick(() => {
                        // Force load table component if not already loaded
                        if (typeof window.table === 'undefined') {
                            const script = document.createElement('script');
                            script.src = '{{ asset('js/filament/tables/components/table.js') }}';
                            document.head.appendChild(script);
                        }
                        
                        // Force load select component if not already loaded
                        if (typeof window.selectFormComponent === 'undefined') {
                            const script = document.createElement('script');
                            script.src = '{{ asset('js/filament/forms/components/select.js') }}';
                            document.head.appendChild(script);
                        }
                    });
                }
            }
            
            // Run initialization
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initDashboard);
            } else {
                initDashboard();
            }
            
            // Stop animations after they complete to prevent shaking
            function stopCardAnimations() {
                const cards = document.querySelectorAll('.metrics-grid .metric-card');
                cards.forEach((card, index) => {
                    const delay = (index + 1) * 50 + 600; // Animation duration + delay
                    setTimeout(() => {
                        card.classList.add('animation-complete');
                        card.style.animation = 'none';
                        card.style.transform = '';
                    }, delay);
                });
            }
            
            // Stop animations after page load
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => {
                    setTimeout(stopCardAnimations, 1000);
                });
            } else {
                setTimeout(stopCardAnimations, 1000);
            }
            
            function initializeCharts() {
                console.log('Initializing charts...', typeof Chart);
                
                // Check if dark mode is active
                const isDarkMode = document.documentElement.classList.contains('dark') || 
                                 window.matchMedia('(prefers-color-scheme: dark)').matches;
                
                const chartColors = {
                    text: isDarkMode ? '#cbd5e1' : '#64748b',
                    grid: isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.05)',
                    tooltipBg: isDarkMode ? 'rgba(30, 41, 59, 0.95)' : 'rgba(0, 0, 0, 0.8)',
                    tooltipText: '#f1f5f9'
                };
                
                // Uploads Timeline Chart
                @if(!empty($uploadsTimeline) && !empty($uploadsTimeline['timeline']))
                const timelineCtx = document.getElementById('uploadsTimelineChart');
                if (timelineCtx && typeof Chart !== 'undefined') {
                    // Destroy existing chart if it exists
                    if (window.dashboardCharts.timeline) {
                        window.dashboardCharts.timeline.destroy();
                    }
                    
                    const timelineData = @json($uploadsTimeline['timeline']);
                    window.dashboardCharts.timeline = new Chart(timelineCtx, {
                        type: 'line',
                        data: {
                            labels: timelineData.map(item => item.label),
                            datasets: [{
                                label: 'Uploads',
                                data: timelineData.map(item => item.count),
                                borderColor: '#3b82f6',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.4,
                                pointRadius: 3,
                                pointHoverRadius: 5
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
                                    backgroundColor: chartColors.tooltipBg,
                                    padding: 12,
                                    titleFont: { size: 14, weight: 'bold' },
                                    bodyFont: { size: 13 },
                                    titleColor: chartColors.tooltipText,
                                    bodyColor: chartColors.tooltipText
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        color: chartColors.text
                                    },
                                    grid: {
                                        color: chartColors.grid
                                    }
                                },
                                x: {
                                    ticks: {
                                        color: chartColors.text
                                    },
                                    grid: {
                                        display: false
                                    }
                                }
                            }
                        }
                    });
                }
                @endif
                
                // Monthly Comparison Chart
                @if(!empty($monthlyComparison))
                const monthlyCtx = document.getElementById('monthlyComparisonChart');
                if (monthlyCtx && typeof Chart !== 'undefined') {
                    // Destroy existing chart if it exists
                    if (window.dashboardCharts.monthly) {
                        window.dashboardCharts.monthly.destroy();
                    }
                    
                    const monthlyData = @json($monthlyComparison);
                    window.dashboardCharts.monthly = new Chart(monthlyCtx, {
                        type: 'bar',
                        data: {
                            labels: monthlyData.map(item => item.short),
                            datasets: [
                                {
                                    label: 'Documenten',
                                    data: monthlyData.map(item => item.count),
                                    backgroundColor: 'rgba(59, 130, 246, 0.7)',
                                    borderColor: '#3b82f6',
                                    borderWidth: 2
                                },
                                {
                                    label: 'Bedrag (€)',
                                    data: monthlyData.map(item => Math.round(item.amount)),
                                    backgroundColor: 'rgba(16, 185, 129, 0.7)',
                                    borderColor: '#10b981',
                                    borderWidth: 2,
                                    yAxisID: 'y1'
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top',
                                    labels: {
                                        color: chartColors.text
                                    }
                                },
                                tooltip: {
                                    backgroundColor: chartColors.tooltipBg,
                                    padding: 12,
                                    titleColor: chartColors.tooltipText,
                                    bodyColor: chartColors.tooltipText,
                                    callbacks: {
                                        label: function(context) {
                                            if (context.datasetIndex === 1) {
                                                return 'Bedrag: €' + context.parsed.y.toLocaleString('nl-NL', {minimumFractionDigits: 2});
                                            }
                                            return context.dataset.label + ': ' + context.parsed.y;
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    position: 'left',
                                    ticks: {
                                        color: chartColors.text
                                    },
                                    grid: {
                                        color: chartColors.grid
                                    }
                                },
                                y1: {
                                    beginAtZero: true,
                                    position: 'right',
                                    ticks: {
                                        color: chartColors.text
                                    },
                                    grid: {
                                        drawOnChartArea: false
                                    }
                                },
                                x: {
                                    ticks: {
                                        color: chartColors.text
                                    },
                                    grid: {
                                        display: false
                                    }
                                }
                            }
                        }
                    });
                }
                @endif
                
                // Document Types Chart
                @if(!empty($documentTypes))
                const typesCtx = document.getElementById('documentTypesChart');
                if (typesCtx && typeof Chart !== 'undefined') {
                    // Destroy existing chart if it exists
                    if (window.dashboardCharts.types) {
                        window.dashboardCharts.types.destroy();
                    }
                    
                    const typesData = @json($documentTypes);
                    const colors = [
                        'rgba(59, 130, 246, 0.85)',
                        'rgba(16, 185, 129, 0.85)',
                        'rgba(245, 158, 11, 0.85)',
                        'rgba(239, 68, 68, 0.85)',
                        'rgba(139, 92, 246, 0.85)',
                        'rgba(236, 72, 153, 0.85)',
                        'rgba(14, 165, 233, 0.85)',
                        'rgba(251, 146, 60, 0.85)'
                    ];
                    window.dashboardCharts.types = new Chart(typesCtx, {
                        type: 'doughnut',
                        data: {
                            labels: typesData.map(item => item.type),
                            datasets: [{
                                data: typesData.map(item => item.count),
                                backgroundColor: colors.slice(0, typesData.length),
                                borderWidth: 2,
                                borderColor: '#ffffff'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        padding: 15,
                                        font: {
                                            size: 12
                                        },
                                        color: chartColors.text
                                    }
                                },
                                tooltip: {
                                    backgroundColor: chartColors.tooltipBg,
                                    padding: 12,
                                    titleColor: chartColors.tooltipText,
                                    bodyColor: chartColors.tooltipText,
                                    callbacks: {
                                        label: function(context) {
                                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                                            return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
                @endif
            }
        </script>
        @endpush
    </div>

    <style>
        /* 🎨 MODERN SLICK DASHBOARD DESIGN SYSTEM - {{ now()->format('Y-m-d H:i:s') }} */
        :root {
            --primary-bg: #ffffff;
            --secondary-bg: #f1f5f9;
            --card-bg: #ffffff;
            --border-color: #e2e8f0;
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --text-tertiary: #94a3b8;
            
            /* Modern Gradient Colors */
            --accent-blue: #3b82f6;
            --accent-blue-light: #60a5fa;
            --accent-green: #10b981;
            --accent-green-light: #34d399;
            --accent-amber: #f59e0b;
            --accent-purple: #8b5cf6;
            --accent-pink: #ec4899;
            --accent-indigo: #6366f1;
            
            /* Gradients */
            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-blue: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            --gradient-green: linear-gradient(135deg, #10b981 0%, #059669 100%);
            --gradient-purple: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            --gradient-card: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.7) 100%);
            
            /* Shadows - Enhanced */
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --shadow-2xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            --shadow-colored: 0 10px 30px -10px rgba(59, 130, 246, 0.3);
            
            /* Spacing */
            --spacing-xs: 0.5rem;
            --spacing-sm: 0.75rem;
            --spacing-md: 1rem;
            --spacing-lg: 1.5rem;
            --spacing-xl: 2rem;
            --spacing-2xl: 3rem;
            
            /* Border Radius */
            --radius-sm: 0.5rem;
            --radius-md: 0.75rem;
            --radius-lg: 1rem;
            --radius-xl: 1.25rem;
            --radius-2xl: 1.5rem;
        }

        .dark {
            --primary-bg: #0f172a;
            --secondary-bg: #1e293b;
            --card-bg: #1e293b;
            --border-color: #334155;
            --text-primary: #f1f5f9;
            --text-secondary: #cbd5e1;
            --text-tertiary: #94a3b8;
        }
        
        /* Dark mode card adjustments - Less bright */
        .dark .metric-card {
            background: rgba(30, 41, 59, 0.6) !important;
            border-color: rgba(51, 65, 85, 0.8) !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2) !important;
        }
        
        .dark .metric-card:hover {
            background: rgba(30, 41, 59, 0.8) !important;
            border-color: rgba(59, 130, 246, 0.4) !important;
        }
        
        /* Light mode - also less bright */
        .metric-card {
            background: rgba(255, 255, 255, 0.85) !important;
        }
        
        @media (prefers-color-scheme: dark) {
            .metric-card {
                background: rgba(30, 41, 59, 0.6) !important;
            }
        }

        /* Hide Filament's auto-rendered widgets - Maximum coverage with aggressive selectors */
        .fi-page > .fi-main > .fi-section > .fi-widgets-grid,
        .fi-page > .fi-main > .fi-section > [class*="widget"],
        .fi-page > .fi-main > .fi-section > .fi-section-content-ctn > .fi-widgets-grid,
        .fi-page .fi-widgets-grid,
        .fi-section .fi-widgets-grid,
        [class*="fi-widget"]:not(.widget-wrapper):not(.widgets-section),
        [class*="widget"]:not(.widget-wrapper):not(.widgets-section):not(.widget-wrapper),
        .fi-page .fi-section-content-ctn > .fi-widgets-grid,
        .fi-main .fi-widgets-grid,
        .fi-section-content-ctn .fi-widgets-grid {
            display: none !important;
            visibility: hidden !important;
            height: 0 !important;
            overflow: hidden !important;
            margin: 0 !important;
            padding: 0 !important;
            opacity: 0 !important;
        }
        
        /* Hide any default Filament dashboard content */
        .fi-page > .fi-main > .fi-section:not(:has(.dashboard-page-container)) {
            display: none !important;
        }
        
        /* Ensure our custom dashboard is visible and on top - Maximum specificity */
        x-filament-panels::page .dashboard-page-container,
        .fi-page .dashboard-page-container,
        .fi-main-content .dashboard-page-container,
        .fi-section .dashboard-page-container,
        .dashboard-page-container {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            position: relative !important;
            z-index: 9999 !important;
            width: 100% !important;
            min-height: 100vh !important;
            background: var(--secondary-bg) !important;
        }
        
        /* Override any Filament page styles that might hide content */
        x-filament-panels::page .fi-main-content,
        .fi-page .fi-main-content,
        .fi-page > .fi-main,
        .fi-page > .fi-main > .fi-section {
            overflow: visible !important;
            position: relative !important;
        }
        
        /* Force show our dashboard content */
        .dashboard-hero,
        .metrics-grid,
        .actions-section,
        .insights-activity-grid,
        .widgets-section {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        
        /* Dashboard Container - Modern Layout */
        x-filament-panels::page .dashboard-page-container,
        .fi-page .dashboard-page-container,
        .fi-main-content .dashboard-page-container,
        .dashboard-page-container {
            background: linear-gradient(to bottom, #f8fafc 0%, #f1f5f9 100%) !important;
            min-height: 100vh;
            padding: 1rem !important;
            position: relative;
            z-index: 1;
            animation: fadeIn 0.6s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (min-width: 640px) {
            x-filament-panels::page .dashboard-page-container,
            .fi-page .dashboard-page-container,
            .dashboard-page-container {
                padding: 1.5rem !important;
            }
        }

        @media (min-width: 1024px) {
            x-filament-panels::page .dashboard-page-container,
            .fi-page .dashboard-page-container,
            .dashboard-page-container {
                padding: 2rem !important;
                max-width: 1600px;
                margin: 0 auto;
            }
        }

        /* Hero Header - Ultra Modern Design */
        .dashboard-hero {
            background: var(--gradient-primary);
            border-radius: var(--radius-2xl);
            padding: 1.5rem;
            margin-bottom: 2rem;
            color: white;
            box-shadow: var(--shadow-xl), 0 0 0 1px rgba(255, 255, 255, 0.1) inset;
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
            animation: slideDown 0.6s ease-out;
        }
        
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @media (min-width: 640px) {
            .dashboard-hero {
                padding: 2rem;
                border-radius: var(--radius-2xl);
            }
        }
        
        @media (min-width: 1024px) {
            .dashboard-hero {
                padding: 2.5rem;
                margin-bottom: 2.5rem;
            }
        }
        
        .dashboard-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, transparent 70%);
            animation: pulse 8s ease-in-out infinite;
            pointer-events: none;
        }
        
        .dashboard-hero::after {
            content: '';
            position: absolute;
            bottom: -20%;
            left: -20%;
            width: 40%;
            height: 40%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: float 6s ease-in-out infinite;
            pointer-events: none;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1) rotate(0deg); opacity: 0.5; }
            50% { transform: scale(1.2) rotate(180deg); opacity: 0.8; }
        }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(20px, -20px) scale(1.1); }
        }
        
        .hero-text {
            position: relative;
            z-index: 1;
        }

        @media (min-width: 640px) {
            .dashboard-hero {
                padding: var(--spacing-xl);
                border-radius: 1rem;
            }
        }

        .hero-content {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-md);
        }

        @media (min-width: 768px) {
            .hero-content {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
            }
        }

        x-filament-panels::page .hero-greeting,
        .fi-page .hero-greeting,
        .dashboard-page-container .hero-greeting,
        .hero-greeting {
            font-size: 1.125rem !important;
            font-weight: 700 !important;
            margin: 0 !important;
            line-height: 1.3 !important;
        }

        @media (min-width: 640px) {
            x-filament-panels::page .hero-greeting,
            .fi-page .hero-greeting,
            .dashboard-page-container .hero-greeting,
            .hero-greeting {
                font-size: 1.375rem !important;
            }
        }

        @media (min-width: 1024px) {
            x-filament-panels::page .hero-greeting,
            .fi-page .hero-greeting,
            .dashboard-page-container .hero-greeting,
            .hero-greeting {
                font-size: 1.5rem !important;
            }
        }

        .hero-date {
            font-size: 0.8125rem;
            opacity: 0.95;
            margin: 0.375rem 0 0 0;
        }

        @media (min-width: 640px) {
            .hero-date {
                font-size: 0.875rem;
            }
        }

        .hero-actions {
            position: relative;
            z-index: 1;
        }
        
        .hero-upload-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            color: white;
            padding: 0.625rem 1.25rem;
            border-radius: var(--radius-lg);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid rgba(255, 255, 255, 0.4);
            min-height: 36px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        @media (min-width: 640px) {
            .hero-upload-btn {
                padding: 0.75rem 1.5rem;
                font-size: 0.9375rem;
                min-height: 40px;
            }
        }

        .hero-upload-btn:hover {
            background: rgba(255, 255, 255, 0.35);
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
            border-color: rgba(255, 255, 255, 0.6);
        }

        .btn-icon {
            width: 1.125rem;
            height: 1.125rem;
        }

        /* Alert Banner */
        .alert-banner {
            border-radius: var(--radius-lg);
            padding: var(--spacing-md);
            margin-bottom: var(--spacing-lg);
            border: 1.5px solid;
        }

        .alert-warning {
            background: #fef3c7;
            border-color: #f59e0b;
            color: #92400e;
        }

        .dark .alert-warning {
            background: #78350f;
            border-color: #f59e0b;
            color: #fef3c7;
        }

        .alert-content {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            flex-wrap: wrap;
        }

        .alert-icon {
            width: 1.25rem;
            height: 1.25rem;
            flex-shrink: 0;
        }

        .alert-text {
            flex: 1;
            min-width: 200px;
            font-size: 0.875rem;
        }

        .alert-action {
            padding: 0.5rem 1rem;
            background: currentColor;
            color: white;
            border-radius: var(--radius-md);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.8125rem;
            white-space: nowrap;
            min-height: 36px;
            display: inline-flex;
            align-items: center;
        }

        /* Metrics Grid - Cool Modern Responsive Grid */
        .metrics-grid {
            display: grid !important;
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 1rem !important;
            margin-bottom: 2.5rem !important;
            width: 100% !important;
        }

        @media (min-width: 640px) {
            .metrics-grid {
                grid-template-columns: repeat(3, 1fr) !important;
                gap: 1.25rem !important;
            }
        }

        @media (min-width: 1024px) {
            .metrics-grid {
                grid-template-columns: repeat(3, 1fr) !important;
                gap: 1.5rem !important;
            }
        }

        @media (min-width: 1280px) {
            .metrics-grid {
                grid-template-columns: repeat(3, 1fr) !important;
                gap: 1.75rem !important;
            }
        }
        
        @media (min-width: 1536px) {
            .metrics-grid {
                grid-template-columns: repeat(6, 1fr) !important;
                gap: 2rem !important;
            }
        }
        
        /* Ensure metric cards are visible and properly sized in grid */
        .metrics-grid .metric-card {
            min-width: 0 !important;
            width: 100% !important;
            display: flex !important;
            visibility: visible !important;
            opacity: 1 !important;
            animation: fadeInUp 0.6s ease-out forwards;
            animation-fill-mode: both;
        }
        
        /* Staggered animation for grid items - only run once */
        .metrics-grid .metric-card:nth-child(1) { 
            animation-delay: 0.1s;
            animation-iteration-count: 1;
        }
        .metrics-grid .metric-card:nth-child(2) { 
            animation-delay: 0.15s;
            animation-iteration-count: 1;
        }
        .metrics-grid .metric-card:nth-child(3) { 
            animation-delay: 0.2s;
            animation-iteration-count: 1;
            }
        .metrics-grid .metric-card:nth-child(4) { 
            animation-delay: 0.25s;
            animation-iteration-count: 1;
        }
        .metrics-grid .metric-card:nth-child(5) { 
            animation-delay: 0.3s;
            animation-iteration-count: 1;
        }
        .metrics-grid .metric-card:nth-child(6) { 
            animation-delay: 0.35s;
            animation-iteration-count: 1;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        /* Prevent animation from interfering with hover */
        .metrics-grid .metric-card:hover {
            animation: none !important;
        }

        x-filament-panels::page .metric-card,
        .fi-page .metric-card,
        .dashboard-page-container .metric-card,
        .metrics-grid .metric-card,
        .metric-card {
            background: var(--card-bg) !important;
            border: 1px solid var(--border-color) !important;
            border-radius: var(--radius-2xl) !important;
            padding: 1.5rem !important;
            display: flex !important;
            align-items: flex-start !important;
            gap: 1.25rem !important;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
            position: relative !important;
            overflow: hidden !important;
            cursor: pointer !important;
            touch-action: manipulation !important;
            backdrop-filter: blur(20px);
            min-height: 140px;
        }
        
        @media (min-width: 640px) {
            x-filament-panels::page .metric-card,
            .fi-page .metric-card,
            .dashboard-page-container .metric-card,
            .metrics-grid .metric-card,
            .metric-card {
                padding: 1.75rem !important;
                gap: 1.5rem !important;
                min-height: 160px;
            }
        }
        
        @media (min-width: 1024px) {
            x-filament-panels::page .metric-card,
            .fi-page .metric-card,
            .dashboard-page-container .metric-card,
            .metrics-grid .metric-card,
            .metric-card {
                padding: 2rem !important;
                gap: 1.5rem !important;
                min-height: 180px;
            }
        }
        
        /* Premium card accent bar */
        .metric-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(180deg, var(--accent-blue) 0%, var(--accent-blue-light) 100%);
            transform: scaleY(0);
            transform-origin: top;
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: var(--radius-2xl) 0 0 var(--radius-2xl);
        }
        
        /* Subtle background pattern */
        .metric-card::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.03) 0%, transparent 70%);
            opacity: 0;
            transition: opacity 0.4s ease;
            pointer-events: none;
        }

        @media (min-width: 640px) {
            x-filament-panels::page .metric-card,
            .fi-page .metric-card,
            .dashboard-page-container .metric-card,
            .metrics-grid .metric-card,
            .metric-card {
                padding: 1rem !important;
            }
        }

        .metric-card:hover,
        .metric-card:active {
            animation: none !important;
            transform: translateY(-8px) scale(1.02) !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04), 0 0 0 1px rgba(59, 130, 246, 0.2), 0 0 40px rgba(59, 130, 246, 0.15) !important;
            border-color: rgba(59, 130, 246, 0.3) !important;
            background: var(--card-bg) !important;
            will-change: transform;
        }
        
        .dark .metric-card:hover,
        .dark .metric-card:active {
            animation: none !important;
            background: rgba(30, 41, 59, 0.8) !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2), 0 0 0 1px rgba(59, 130, 246, 0.3), 0 0 40px rgba(59, 130, 246, 0.2) !important;
        }
        
        /* Ensure cards stop animating after initial load */
        .metrics-grid .metric-card {
            animation-play-state: running;
        }
        
        .metrics-grid .metric-card.animation-complete {
            animation: none !important;
        }
        
        .metric-card:hover::before {
            transform: scaleY(1);
        }
        
        .metric-card:hover::after {
            opacity: 1;
        }
        
        /* Additional hover glow effect */
        .metric-card:hover {
            position: relative;
        }
        
        .metric-card:hover .metric-icon {
            transform: scale(1.1) rotate(5deg);
        }
        
        /* Touch feedback for mobile */
        @media (hover: none) and (pointer: coarse) {
            .metric-card:active {
                transform: scale(0.98) !important;
                box-shadow: var(--shadow-sm) !important;
            }
        }
        
        /* Metric card type colors with gradients */
        .metric-card[data-type="primary"]::before {
            background: linear-gradient(180deg, #3b82f6 0%, #2563eb 100%);
        }
        
        .metric-card[data-type="success"]::before {
            background: linear-gradient(180deg, #10b981 0%, #059669 100%);
        }
        
        .metric-card[data-type="warning"]::before {
            background: linear-gradient(180deg, #f59e0b 0%, #d97706 100%);
        }
        
        .metric-card[data-type="info"]::before {
            background: linear-gradient(180deg, #06b6d4 0%, #0891b2 100%);
        }
        
        .metric-card[data-type="purple"]::before {
            background: linear-gradient(180deg, #8b5cf6 0%, #7c3aed 100%);
        }
        
        .metric-card[data-type="blue"]::before {
            background: linear-gradient(180deg, #3b82f6 0%, #2563eb 100%);
        }

        /* Metric Icon - Premium Card Style */
        x-filament-panels::page .metric-icon,
        .fi-page .metric-icon,
        .dashboard-page-container .metric-icon,
        .metrics-grid .metric-icon,
        .metric-card .metric-icon,
        .metric-icon {
            font-size: 1.5rem !important;
            flex-shrink: 0 !important;
            line-height: 1 !important;
            width: 3.5rem !important;
            height: 3.5rem !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: var(--radius-lg) !important;
            background: var(--secondary-bg) !important;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
            max-width: 3.5rem !important;
            max-height: 3.5rem !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            position: relative;
        }
        
        .dark .metric-icon {
            background: rgba(30, 41, 59, 0.5) !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2) !important;
        }
        
        .metric-icon::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: var(--radius-lg);
            padding: 1px;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(59, 130, 246, 0.05));
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            opacity: 0;
            transition: opacity 0.4s ease;
        }
        
        .metric-card:hover .metric-icon::before {
            opacity: 1;
        }

        @media (min-width: 640px) {
            x-filament-panels::page .metric-icon,
            .fi-page .metric-icon,
            .dashboard-page-container .metric-icon,
            .metrics-grid .metric-icon,
            .metric-card .metric-icon,
            .metric-icon {
                font-size: 1.75rem !important;
                width: 4rem !important;
                height: 4rem !important;
                max-width: 4rem !important;
                max-height: 4rem !important;
            }
        }
        
        @media (min-width: 1024px) {
            x-filament-panels::page .metric-icon,
            .fi-page .metric-icon,
            .dashboard-page-container .metric-icon,
            .metrics-grid .metric-icon,
            .metric-card .metric-icon,
            .metric-icon {
                font-size: 2rem !important;
                width: 4.5rem !important;
                height: 4.5rem !important;
                max-width: 4.5rem !important;
                max-height: 4.5rem !important;
            }
        }
        
        /* Fix large checkmark specifically - Maximum Specificity */
        x-filament-panels::page .metric-card[data-type="success"] .metric-icon,
        .fi-page .metric-card[data-type="success"] .metric-icon,
        .dashboard-page-container .metric-card[data-type="success"] .metric-icon,
        .metrics-grid .metric-card[data-type="success"] .metric-icon,
        .metric-card[data-type="success"] .metric-icon {
            font-size: 0.875rem !important; /* Even smaller for checkmark */
            width: 1.75rem !important;
            height: 1.75rem !important;
            max-width: 1.75rem !important;
            max-height: 1.75rem !important;
        }
        
        @media (min-width: 640px) {
            x-filament-panels::page .metric-card[data-type="success"] .metric-icon,
            .fi-page .metric-card[data-type="success"] .metric-icon,
            .dashboard-page-container .metric-card[data-type="success"] .metric-icon,
            .metrics-grid .metric-card[data-type="success"] .metric-icon,
            .metric-card[data-type="success"] .metric-icon {
                font-size: 1rem !important;
                width: 2rem !important;
                height: 2rem !important;
                max-width: 2rem !important;
                max-height: 2rem !important;
            }
        }
        
        @media (min-width: 1024px) {
            x-filament-panels::page .metric-card[data-type="success"] .metric-icon,
            .fi-page .metric-card[data-type="success"] .metric-icon,
            .dashboard-page-container .metric-card[data-type="success"] .metric-icon,
            .metrics-grid .metric-card[data-type="success"] .metric-icon,
            .metric-card[data-type="success"] .metric-icon {
                font-size: 1.125rem !important;
                width: 2.25rem !important;
                height: 2.25rem !important;
                max-width: 2.25rem !important;
                max-height: 2.25rem !important;
            }
        }

        .metric-content {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .metric-label {
            font-size: 0.8125rem !important;
            color: var(--text-secondary) !important;
            font-weight: 600 !important;
            margin-bottom: 0 !important;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            opacity: 0.8;
        }

        @media (min-width: 640px) {
            .metric-label {
                font-size: 0.875rem !important;
            }
        }

        x-filament-panels::page .metric-value,
        .fi-page .metric-value,
        .dashboard-page-container .metric-value,
        .metric-card .metric-value,
        .metric-value {
            font-size: 1.75rem !important;
            font-weight: 800 !important;
            color: var(--text-primary) !important;
            line-height: 1.1 !important;
            margin-bottom: 0 !important;
            letter-spacing: -0.02em;
            background: linear-gradient(135deg, var(--text-primary) 0%, var(--text-secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        @media (min-width: 640px) {
            x-filament-panels::page .metric-value,
            .fi-page .metric-value,
            .dashboard-page-container .metric-value,
            .metric-card .metric-value,
            .metric-value {
                font-size: 2rem !important;
            }
        }
        
        @media (min-width: 1024px) {
            x-filament-panels::page .metric-value,
            .fi-page .metric-value,
            .dashboard-page-container .metric-value,
            .metric-card .metric-value,
            .metric-value {
                font-size: 2.25rem !important;
            }
        }

        .metric-trend {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            font-size: 0.8125rem;
            font-weight: 700;
            margin-top: 0.25rem;
            padding: 0.25rem 0.5rem;
            border-radius: var(--radius-sm);
            background: rgba(16, 185, 129, 0.1);
            width: fit-content;
        }

        @media (min-width: 640px) {
            .metric-trend {
                font-size: 0.875rem;
                padding: 0.375rem 0.625rem;
            }
        }

        .trend-positive {
            color: var(--accent-green);
            background: rgba(16, 185, 129, 0.1);
        }

        .trend-negative {
            color: #ef4444;
            background: rgba(239, 68, 68, 0.1);
        }
        
        .trend-neutral {
            color: var(--text-secondary);
            background: rgba(100, 116, 139, 0.1);
        }

        .trend-icon {
            font-size: 0.875rem;
            font-weight: 800;
        }

        .metric-subtext {
            font-size: 0.8125rem;
            color: var(--text-secondary);
            margin-top: 0;
            opacity: 0.7;
            font-weight: 500;
        }
        
        @media (min-width: 640px) {
            .metric-subtext {
                font-size: 0.875rem;
            }
        }

        /* Actions Section - Enhanced UX */
        .actions-section {
            margin-bottom: 3rem;
            margin-top: 1rem;
        }
        
        @media (min-width: 1024px) {
            .actions-section {
                margin-bottom: 4rem;
            }
        }

        .section-title {
            font-size: 1.125rem !important;
            font-weight: 800 !important;
            color: var(--text-primary) !important;
            margin-bottom: 1.5rem !important;
            margin-top: 0 !important;
            display: flex !important;
            align-items: center !important;
            gap: 0.75rem !important;
            letter-spacing: -0.02em;
            position: relative;
            padding-bottom: 0.75rem;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background: var(--gradient-primary);
            border-radius: 2px;
        }

        @media (min-width: 640px) {
            .section-title {
                font-size: 1.25rem !important;
                margin-bottom: 2rem !important;
            }
        }
        
        @media (min-width: 1024px) {
            .section-title {
                font-size: 1.5rem !important;
            }
        }

        .title-icon {
            font-size: 1.5rem;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
        }

        @media (min-width: 640px) {
            .title-icon {
                font-size: 1.75rem;
            }
        }
        
        @media (min-width: 1024px) {
            .title-icon {
                font-size: 2rem;
            }
        }

        .actions-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: var(--spacing-md);
        }

        @media (min-width: 640px) {
            .actions-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1024px) {
            .actions-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        .action-card {
            background: var(--card-bg);
            border: 1.5px solid var(--border-color);
            border-radius: 0.875rem;
            padding: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 56px; /* Better touch target (44px minimum + padding) */
            box-shadow: var(--shadow-sm);
            position: relative;
            overflow: hidden;
            touch-action: manipulation;
            cursor: pointer;
        }
        
        @media (min-width: 640px) {
            .action-card {
                padding: 1rem;
                gap: 1rem;
                border-radius: 1rem;
                min-height: 64px;
            }
        }
        
        @media (min-width: 1024px) {
            .action-card {
                padding: 1.125rem;
                gap: 1.125rem;
                border-radius: var(--radius-xl);
                min-height: 72px;
            }
        }
        
        .action-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .action-card:hover::after {
            left: 100%;
        }

        .action-card:hover,
        .action-card:active {
            transform: translateY(-2px) scale(1.01);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
            border-color: var(--accent-blue);
        }
        
        /* Touch feedback for mobile */
        @media (hover: none) and (pointer: coarse) {
            .action-card:active {
                transform: scale(0.98);
                box-shadow: var(--shadow-sm);
            }
        }

        .action-primary {
            background: linear-gradient(135deg, var(--accent-blue) 0%, #2563eb 100%);
            color: white;
            border-color: #2563eb;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }
        
        .action-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
        }

        .action-icon {
            font-size: 1.5rem; /* Reduced for better mobile */
            flex-shrink: 0;
            width: 2.5rem;
            height: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            background: var(--secondary-bg);
        }

        @media (min-width: 640px) {
            .action-icon {
                font-size: 1.75rem;
                width: 3rem;
                height: 3rem;
            }
        }
        
        @media (min-width: 1024px) {
            .action-icon {
                font-size: 2rem;
                width: 3.5rem;
                height: 3.5rem;
            }
        }

        .action-content {
            flex: 1;
        }

        .action-title {
            font-weight: 700 !important;
            font-size: 0.8125rem !important;
            margin-bottom: 0.25rem !important;
        }

        @media (min-width: 640px) {
            .action-title {
                font-size: 0.875rem !important;
            }
        }

        .action-desc {
            font-size: 0.6875rem;
            opacity: 0.8;
        }

        @media (min-width: 640px) {
            .action-desc {
                font-size: 0.75rem;
            }
        }

        .action-arrow {
            width: 1.125rem;
            height: 1.125rem;
            flex-shrink: 0;
            opacity: 0.5;
        }

        /* Insights & Activity Grid - Enhanced Responsive */
        .insights-activity-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            margin-bottom: 3rem;
            margin-top: 1rem;
        }
        
        @media (min-width: 1024px) {
            .insights-activity-grid {
                margin-bottom: 4rem;
            }
        }

        @media (min-width: 768px) {
            .insights-activity-grid {
                gap: 1.25rem;
            }
        }

        @media (min-width: 1024px) {
            .insights-activity-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: var(--spacing-lg);
            }
        }

        .activity-section,
        .insights-section {
            background: var(--card-bg);
            border: 1.5px solid var(--border-color);
            border-radius: 0.875rem; /* Smaller on mobile */
            padding: 1rem; /* Reduced on mobile */
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
        }
        
        .activity-section:hover,
        .insights-section:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }

        @media (min-width: 640px) {
            .activity-section,
            .insights-section {
                padding: 1.25rem;
                border-radius: 1rem;
            }
        }
        
        @media (min-width: 1024px) {
            .activity-section,
            .insights-section {
                padding: var(--spacing-lg);
                border-radius: var(--radius-xl);
            }
        }

        .activity-timeline {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-md);
        }

        .activity-item {
            display: flex;
            align-items: flex-start;
            gap: var(--spacing-md);
            padding-bottom: var(--spacing-md);
            border-bottom: 1px solid var(--border-color);
        }

        .activity-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .activity-icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
            transition: all 0.3s ease;
        }
        
        .activity-item:hover .activity-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .activity-upload {
            background: #dbeafe;
            color: #1e40af;
        }

        .dark .activity-upload {
            background: #1e3a8a;
            color: #93c5fd;
        }

        .activity-status {
            background: #fef3c7;
            color: #92400e;
        }

        .dark .activity-status {
            background: #78350f;
            color: #fef3c7;
        }

        .activity-content {
            flex: 1;
        }

        .activity-title {
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
            color: var(--text-primary);
        }

        .activity-time {
            font-size: 0.75rem;
            color: var(--text-secondary);
        }

        .activity-empty,
        .insights-empty {
            text-align: center;
            padding: var(--spacing-xl);
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        .insights-list {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-md);
        }

        .insight-item {
            display: flex;
            align-items: flex-start;
            gap: var(--spacing-md);
            padding: var(--spacing-md);
            border-radius: var(--radius-lg);
            border: 1.5px solid;
            transition: all 0.3s ease;
        }
        
        .insight-item:hover {
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .insight-info {
            background: #eff6ff;
            border-color: var(--accent-blue);
            color: #1e40af;
        }

        .dark .insight-info {
            background: #1e3a8a;
            border-color: #60a5fa;
            color: #93c5fd;
        }

        .insight-warning {
            background: #fef3c7;
            border-color: var(--accent-amber);
            color: #92400e;
        }

        .dark .insight-warning {
            background: #78350f;
            border-color: #f59e0b;
            color: #fef3c7;
        }

        .insight-success {
            background: #d1fae5;
            border-color: var(--accent-green);
            color: #065f46;
        }

        .dark .insight-success {
            background: #064e3b;
            border-color: #34d399;
            color: #a7f3d0;
        }

        .insight-icon {
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        @media (min-width: 640px) {
            .insight-icon {
                font-size: 1.5rem;
            }
        }

        .insight-content {
            flex: 1;
        }

        .insight-title {
            font-weight: 700;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        @media (min-width: 640px) {
            .insight-title {
                font-size: 0.9375rem;
            }
        }

        .insight-text {
            font-size: 0.75rem;
            opacity: 0.9;
        }

        @media (min-width: 640px) {
            .insight-text {
                font-size: 0.8125rem;
            }
        }

        /* Widgets Section */
        .widgets-section {
            display: grid;
            grid-template-columns: 1fr;
            gap: var(--spacing-lg);
        }

        @media (min-width: 1024px) {
            .widgets-section {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .widget-wrapper {
            min-width: 0;
        }

        /* Analytics Section - Enhanced UX */
        .analytics-section {
            margin-bottom: 3rem;
            margin-top: 1rem;
        }
        
        @media (min-width: 1024px) {
            .analytics-section {
                margin-bottom: 4rem;
            }
        }
        
        .analytics-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        @media (min-width: 768px) {
            .analytics-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1.25rem;
            }
        }
        
        @media (min-width: 1024px) {
            .analytics-grid {
                gap: 1.5rem;
            }
        }
        
        .analytics-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-2xl);
            padding: 1.5rem;
            box-shadow: var(--shadow-lg);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
        }
        
        @media (min-width: 640px) {
            .analytics-card {
                padding: 2rem;
            }
        }
        
        @media (min-width: 1024px) {
            .analytics-card {
                padding: 2.5rem;
            }
        }
        
        .analytics-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-blue);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.4s ease;
        }
        
        .analytics-card:hover {
            box-shadow: var(--shadow-2xl), var(--shadow-colored);
            transform: translateY(-6px) scale(1.01);
            border-color: var(--accent-blue);
        }
        
        .analytics-card:hover::before {
            transform: scaleX(1);
        }
        
        .spending-card::before {
            background: var(--gradient-green);
        }
        
        .suppliers-card::before {
            background: var(--gradient-purple);
        }
        
        .analytics-card-header {
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .analytics-card-title {
            font-size: 0.9375rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0 0 0.5rem 0;
        }
        
        @media (min-width: 640px) {
            .analytics-card-title {
                font-size: 1rem;
            }
        }
        
        .analytics-card-value {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--accent-blue);
            line-height: 1.2;
        }
        
        @media (min-width: 640px) {
            .analytics-card-value {
                font-size: 2rem;
            }
        }
        
        .analytics-card-body {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .analytics-stats {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.8125rem;
        }
        
        .stat-label {
            color: var(--text-secondary);
        }
        
        .stat-value {
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .suppliers-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .supplier-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            background: var(--secondary-bg);
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }
        
        .supplier-item:hover {
            background: var(--border-color);
            transform: translateX(4px);
        }
        
        .supplier-rank {
            width: 2rem;
            height: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--accent-blue);
            color: white;
            border-radius: 50%;
            font-weight: 700;
            font-size: 0.875rem;
            flex-shrink: 0;
        }
        
        .supplier-info {
            flex: 1;
        }
        
        .supplier-name {
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }
        
        .supplier-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.75rem;
            color: var(--text-secondary);
        }
        
        .supplier-amount {
            font-weight: 600;
            color: var(--accent-green);
        }
        
        /* Charts Section - Enhanced UX */
        .charts-section {
            margin-bottom: 3rem;
            margin-top: 1rem;
        }
        
        @media (min-width: 1024px) {
            .charts-section {
                margin-bottom: 4rem;
            }
        }
        
        .charts-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        @media (min-width: 768px) {
            .charts-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1.25rem;
            }
        }
        
        @media (min-width: 1024px) {
            .charts-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 1.5rem;
            }
        }
        
        .chart-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-2xl);
            padding: 1.5rem;
            box-shadow: var(--shadow-lg);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            backdrop-filter: blur(10px);
        }
        
        @media (min-width: 640px) {
            .chart-card {
                padding: 2rem;
            }
        }
        
        @media (min-width: 1024px) {
            .chart-card {
                padding: 2.5rem;
            }
        }
        
        .chart-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-primary);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.4s ease;
        }
        
        .chart-card:hover {
            box-shadow: var(--shadow-2xl), 0 0 40px rgba(59, 130, 246, 0.15);
            transform: translateY(-6px) scale(1.01);
            border-color: var(--accent-blue);
        }
        
        .chart-card:hover::before {
            transform: scaleX(1);
        }
        
        .chart-header {
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .chart-title {
            font-size: 0.9375rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0 0 0.5rem 0;
        }
        
        @media (min-width: 640px) {
            .chart-title {
                font-size: 1rem;
            }
        }
        
        .chart-stats {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .chart-stat {
            font-size: 0.75rem;
            color: var(--text-secondary);
            font-weight: 600;
        }
        
        .chart-body {
            position: relative;
            height: 280px;
            margin-top: 1rem;
        }
        
        @media (min-width: 640px) {
            .chart-body {
                height: 320px;
            }
        }
        
        @media (min-width: 1024px) {
            .chart-body {
                height: 350px;
            }
        }
        
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: var(--text-secondary);
            font-size: 0.875rem;
            text-align: center;
            padding: 3rem 2rem;
            min-height: 200px;
        }
        
        .empty-state::before {
            content: '📊';
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        /* Mobile Optimizations */
        @media (max-width: 639px) {
            .dashboard-page-container {
                padding: 0.5rem;
            }
            
            .metric-card,
            .action-card {
                padding: var(--spacing-md);
            }
            
            .metric-value {
                font-size: 1.25rem;
            }
            
            .hero-greeting {
                font-size: 1.125rem;
            }
            
            .chart-body {
                height: 200px;
            }
            
            .analytics-card-value {
                font-size: 1.5rem;
            }
        }
    </style>
</x-filament-panels::page>
