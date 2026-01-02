<x-filament-panels::page>
    @php
        $summary = $this->getSummary();
    @endphp

    <div class="btw-unified-container">
        {{-- Summary Cards --}}
        <div class="summary-grid">
            <div class="metric-card info">
                <div class="metric-header">
                    <span class="metric-icon">📤</span>
                    <span class="metric-label">BTW Verschuldigd</span>
                </div>
                <div class="metric-value">€{{ number_format($summary['verschuldigd'], 2, ',', '.') }}</div>
                <div class="metric-subtext">Verkoopfacturen (betaald)</div>
            </div>

            <div class="metric-card success">
                <div class="metric-header">
                    <span class="metric-icon">📥</span>
                    <span class="metric-label">BTW Aftrekbaar</span>
                </div>
                <div class="metric-value">€{{ number_format($summary['aftrekbaar'], 2, ',', '.') }}</div>
                <div class="metric-subtext">Inkoopfacturen & bonnetjes</div>
            </div>

            <div class="metric-card {{ $summary['is_refund'] ? 'success' : 'warning' }}">
                <div class="metric-header">
                    <span class="metric-icon">{{ $summary['is_refund'] ? '💚' : '💸' }}</span>
                    <span class="metric-label">Netto BTW</span>
                </div>
                <div class="metric-value">€{{ number_format(abs($summary['netto']), 2, ',', '.') }}</div>
                <div class="metric-subtext">
                    {{ $summary['is_refund'] ? 'Terug te ontvangen' : 'Te betalen' }}
                </div>
            </div>
        </div>

        {{-- Table Content --}}
        <div class="table-container">
            {{ $this->table }}
        </div>
    </div>

    <style>
        .btw-unified-container {
            max-width: 100%;
        }

        .summary-grid {
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

        .metric-card.info .metric-value {
            color: #06b6d4;
        }

        .metric-card.success .metric-value {
            color: #10b981;
        }

        .metric-card.warning .metric-value {
            color: #f59e0b;
        }

        .metric-subtext {
            font-size: 0.875rem;
            color: #6b7280;
        }

        .dark .metric-subtext {
            color: #9ca3af;
        }

        .table-container {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 1rem;
            padding: 1.5rem;
            overflow: hidden;
        }

        .dark .table-container {
            background: #1f2937;
            border-color: #374151;
        }

        @media (max-width: 768px) {
            .summary-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</x-filament-panels::page>
