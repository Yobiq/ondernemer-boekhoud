<x-filament-panels::page>
    @php
        $summary = $this->getFinancialSummary();
        $vatSummary = $this->getVatSummary();
    @endphp

    <div class="financial-overview-container">
        {{-- Key Metrics Cards --}}
        <div class="metrics-grid">
            <div class="metric-card primary">
                <div class="metric-header">
                    <span class="metric-icon">💰</span>
                    <span class="metric-label">Deze Maand</span>
                </div>
                <div class="metric-value">€{{ number_format($summary['this_month'] ?? 0, 2, ',', '.') }}</div>
                @if(isset($summary['change']))
                <div class="metric-change {{ $summary['change'] >= 0 ? 'positive' : 'negative' }}">
                    <span class="change-icon">{{ $summary['change'] >= 0 ? '↑' : '↓' }}</span>
                    <span>{{ abs($summary['change']) }}% vs vorige maand</span>
                </div>
                @endif
            </div>

            <div class="metric-card success">
                <div class="metric-header">
                    <span class="metric-icon">📊</span>
                    <span class="metric-label">Dit Jaar</span>
                </div>
                <div class="metric-value">€{{ number_format($summary['this_year'] ?? 0, 2, ',', '.') }}</div>
                <div class="metric-subtext">
                    Gemiddeld: €{{ number_format($summary['monthly_average'] ?? 0, 2, ',', '.') }}/maand
                </div>
            </div>

            <div class="metric-card info">
                <div class="metric-header">
                    <span class="metric-icon">📈</span>
                    <span class="metric-label">BTW Verschuldigd</span>
                </div>
                <div class="metric-value">€{{ number_format($vatSummary['vat_collected'] ?? 0, 2, ',', '.') }}</div>
                <div class="metric-subtext">Verkoopfacturen (betaald)</div>
            </div>

            <div class="metric-card warning">
                <div class="metric-header">
                    <span class="metric-icon">📉</span>
                    <span class="metric-label">BTW Aftrekbaar</span>
                </div>
                <div class="metric-value">€{{ number_format($vatSummary['vat_paid'] ?? 0, 2, ',', '.') }}</div>
                <div class="metric-subtext">Inkoopfacturen & bonnetjes</div>
            </div>

            <div class="metric-card {{ ($vatSummary['net_vat'] ?? 0) < 0 ? 'success' : 'danger' }}">
                <div class="metric-header">
                    <span class="metric-icon">{{ ($vatSummary['net_vat'] ?? 0) < 0 ? '💚' : '💸' }}</span>
                    <span class="metric-label">Netto BTW</span>
                </div>
                <div class="metric-value">€{{ number_format(abs($vatSummary['net_vat'] ?? 0), 2, ',', '.') }}</div>
                <div class="metric-subtext">
                    {{ ($vatSummary['is_refund'] ?? false) ? 'Terug te ontvangen' : 'Te betalen' }}
                </div>
            </div>
        </div>

        {{-- Monthly Trend Chart --}}
        <div class="chart-card">
            <h3 class="chart-title">📈 Maandelijks Trend (Laatste 12 Maanden)</h3>
            <div class="chart-container">
                <canvas id="monthlyTrendChart"></canvas>
            </div>
        </div>

        {{-- Spending by Category --}}
        <div class="category-card">
            <h3 class="card-title">📊 Uitgaven per Categorie</h3>
            <div class="category-list">
                @if(!empty($summary['by_category']))
                    @foreach(array_slice($summary['by_category'], 0, 10) as $category)
                    <div class="category-item">
                        <div class="category-name">{{ $category['category'] }}</div>
                        <div class="category-amount">€{{ number_format($category['amount'], 2, ',', '.') }}</div>
                    </div>
                    @endforeach
                @else
                    <p class="empty-state">Nog geen uitgaven per categorie beschikbaar</p>
                @endif
            </div>
        </div>

        {{-- Top Suppliers --}}
        <div class="suppliers-card">
            <h3 class="card-title">🏢 Top Leveranciers</h3>
            <div class="suppliers-list">
                @if(!empty($summary['top_suppliers']))
                    @foreach($summary['top_suppliers'] as $index => $supplier)
                    <div class="supplier-item">
                        <div class="supplier-rank">{{ $index + 1 }}</div>
                        <div class="supplier-info">
                            <div class="supplier-name">{{ $supplier['name'] }}</div>
                            <div class="supplier-meta">
                                <span>{{ $supplier['count'] }} {{ $supplier['count'] === 1 ? 'document' : 'documenten' }}</span>
                            </div>
                        </div>
                        <div class="supplier-amount">€{{ number_format($supplier['amount'], 2, ',', '.') }}</div>
                    </div>
                    @endforeach
                @else
                    <p class="empty-state">Nog geen leveranciers beschikbaar</p>
                @endif
            </div>
        </div>
    </div>

    <style>
        .financial-overview-container {
            max-width: 100%;
        }

        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .metric-card {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 1rem;
            padding: 1.5rem;
            transition: all 0.2s;
        }

        .dark .metric-card {
            background: #1f2937;
            border-color: #374151;
        }

        .metric-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .metric-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .metric-icon {
            font-size: 1.5rem;
        }

        .metric-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .dark .metric-label {
            color: #9ca3af;
        }

        .metric-value {
            font-size: 2rem;
            font-weight: 800;
            color: #111827;
            margin-bottom: 0.5rem;
        }

        .dark .metric-value {
            color: #f9fafb;
        }

        .metric-card.primary .metric-value {
            color: #3b82f6;
        }

        .metric-card.success .metric-value {
            color: #10b981;
        }

        .metric-card.info .metric-value {
            color: #06b6d4;
        }

        .metric-card.warning .metric-value {
            color: #f59e0b;
        }

        .metric-card.danger .metric-value {
            color: #ef4444;
        }

        .metric-subtext {
            font-size: 0.875rem;
            color: #6b7280;
        }

        .dark .metric-subtext {
            color: #9ca3af;
        }

        .metric-change {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .metric-change.positive {
            color: #10b981;
        }

        .metric-change.negative {
            color: #ef4444;
        }

        .chart-card, .category-card, .suppliers-card {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .dark .chart-card, .dark .category-card, .dark .suppliers-card {
            background: #1f2937;
            border-color: #374151;
        }

        .chart-title, .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #111827;
            margin: 0 0 1.5rem 0;
        }

        .dark .chart-title, .dark .card-title {
            color: #f9fafb;
        }

        .chart-container {
            position: relative;
            height: 300px;
        }

        .category-list, .suppliers-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .category-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background: #f9fafb;
            border-radius: 0.75rem;
            border: 1px solid #e5e7eb;
        }

        .dark .category-item {
            background: #374151;
            border-color: #4b5563;
        }

        .category-name {
            font-weight: 600;
            color: #111827;
        }

        .dark .category-name {
            color: #f9fafb;
        }

        .category-amount {
            font-weight: 700;
            font-size: 1.125rem;
            color: #3b82f6;
        }

        .supplier-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #f9fafb;
            border-radius: 0.75rem;
            border: 1px solid #e5e7eb;
        }

        .dark .supplier-item {
            background: #374151;
            border-color: #4b5563;
        }

        .supplier-rank {
            width: 2rem;
            height: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #3b82f6;
            color: white;
            border-radius: 50%;
            font-weight: 700;
            flex-shrink: 0;
        }

        .supplier-info {
            flex: 1;
        }

        .supplier-name {
            font-weight: 600;
            color: #111827;
            margin-bottom: 0.25rem;
        }

        .dark .supplier-name {
            color: #f9fafb;
        }

        .supplier-meta {
            font-size: 0.875rem;
            color: #6b7280;
        }

        .dark .supplier-meta {
            color: #9ca3af;
        }

        .supplier-amount {
            font-weight: 700;
            font-size: 1.125rem;
            color: #10b981;
        }

        .empty-state {
            text-align: center;
            color: #6b7280;
            padding: 2rem;
        }

        .dark .empty-state {
            color: #9ca3af;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const monthlyData = @json($summary['monthly_trend'] ?? []);
            
            if (monthlyData.length > 0) {
                const ctx = document.getElementById('monthlyTrendChart');
                if (ctx) {
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: monthlyData.map(item => item.short),
                            datasets: [{
                                label: 'Uitgaven (€)',
                                data: monthlyData.map(item => item.amount),
                                borderColor: '#3b82f6',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.4,
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
                                    callbacks: {
                                        label: function(context) {
                                            return '€' + context.parsed.y.toLocaleString('nl-NL', {
                                                minimumFractionDigits: 2,
                                                maximumFractionDigits: 2
                                            });
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return '€' + value.toLocaleString('nl-NL');
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            }
        });
    </script>
</x-filament-panels::page>


