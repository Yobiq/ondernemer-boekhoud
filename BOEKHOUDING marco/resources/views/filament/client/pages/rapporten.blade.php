<x-filament-panels::page>
    <style>
        /* Unified Design System - Same as other pages */
        .rapport-page-container {
            --primary-bg: #ffffff;
            --secondary-bg: #f8fafc;
            --card-bg: #ffffff;
            --border-color: #e2e8f0;
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --text-tertiary: #94a3b8;
            --accent-blue: #3b82f6;
            --accent-green: #10b981;
            --accent-purple: #8b5cf6;
            --accent-red: #ef4444;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --radius-sm: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --radius-xl: 1rem;
            padding: 0.75rem;
            background: var(--secondary-bg);
            min-height: 100vh;
            width: 100%;
            max-width: 100%;
        }

        /* Dark Mode Support */
        .dark .rapport-page-container {
            --primary-bg: #0f172a;
            --secondary-bg: #1e293b;
            --card-bg: #1e293b;
            --border-color: #334155;
            --text-primary: #f1f5f9;
            --text-secondary: #cbd5e1;
            --text-tertiary: #94a3b8;
        }

        @media (min-width: 640px) {
            .rapport-page-container {
                padding: 1rem;
            }
        }

        @media (min-width: 1024px) {
            .rapport-page-container {
                padding: 1.5rem 2rem;
                max-width: 1600px;
                margin: 0 auto;
            }
        }

        /* Hero Section */
        .rapport-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            color: white;
            box-shadow: var(--shadow-lg);
        }

        .rapport-hero-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0 0 0.5rem 0;
        }

        .rapport-hero-subtitle {
            font-size: 0.875rem;
            opacity: 0.9;
            margin: 0;
        }

        /* Form Section */
        .rapport-form-section {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-sm);
        }

        /* Stats Grid */
        .rapport-stats-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        @media (min-width: 640px) {
            .rapport-stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1024px) {
            .rapport-stats-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (min-width: 1280px) {
            .rapport-stats-grid {
                grid-template-columns: repeat(5, 1fr);
            }
        }

        .rapport-stat-card {
            background: var(--card-bg);
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 1.25rem;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .dark .rapport-stat-card {
            background: rgba(30, 41, 59, 0.6);
            border-color: rgba(51, 65, 85, 0.8);
        }

        .rapport-stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: transparent;
            transition: background 0.3s;
        }

        .rapport-stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
            border-color: var(--accent-blue);
        }

        .rapport-stat-card:hover::before {
            background: var(--accent-blue);
        }

        .rapport-stat-icon {
            font-size: 2rem;
            margin-bottom: 0.75rem;
        }

        .rapport-stat-label {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
            letter-spacing: 0.05em;
        }

        .rapport-stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            line-height: 1.2;
        }

        .rapport-stat-value.currency {
            color: var(--accent-blue);
        }

        /* Section Cards */
        .rapport-section-card {
            background: var(--card-bg);
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-sm);
        }
        
        .dark .rapport-section-card {
            background: rgba(30, 41, 59, 0.6);
            border-color: rgba(51, 65, 85, 0.8);
        }

        .rapport-section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0 0 1.25rem 0;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--border-color);
        }

        /* Breakdown List */
        .rapport-breakdown-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .rapport-breakdown-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background: var(--secondary-bg);
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
            transition: all 0.2s ease;
        }

        .rapport-breakdown-item:hover {
            background: #f0f9ff;
            border-color: var(--accent-blue);
            transform: translateX(4px);
        }
        
        .dark .rapport-breakdown-item {
            background: rgba(30, 41, 59, 0.4);
        }
        
        .dark .rapport-breakdown-item:hover {
            background: rgba(30, 41, 59, 0.7);
            border-color: rgba(59, 130, 246, 0.4);
        }

        .rapport-breakdown-label {
            font-weight: 600;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .rapport-breakdown-value {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--accent-blue);
        }

        /* Supplier List */
        .rapport-supplier-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background: var(--secondary-bg);
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
            margin-bottom: 0.75rem;
            transition: all 0.2s ease;
        }

        .rapport-supplier-item:hover {
            background: #f0f9ff;
            border-color: var(--accent-blue);
            transform: translateX(4px);
        }
        
        .dark .rapport-supplier-item {
            background: rgba(30, 41, 59, 0.4);
        }
        
        .dark .rapport-supplier-item:hover {
            background: rgba(30, 41, 59, 0.7);
            border-color: rgba(59, 130, 246, 0.4);
        }

        .rapport-supplier-name {
            font-weight: 600;
            color: var(--text-primary);
            flex: 1;
        }

        .rapport-supplier-meta {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        .rapport-supplier-count {
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        .rapport-supplier-amount {
            font-weight: 700;
            color: var(--accent-green);
            font-size: 1.125rem;
            white-space: nowrap;
        }

        .currency-inline {
            display: inline;
            white-space: nowrap;
        }
    </style>

    <div class="rapport-page-container">
        {{-- Hero Header --}}
        <div class="rapport-hero">
            <h1 class="rapport-hero-title">📊 Rapporten & Analytics</h1>
            <p class="rapport-hero-subtitle">Inzicht in uw documenten en financiën</p>
        </div>

        {{-- Form Section --}}
        <div class="rapport-form-section">
            <form wire:submit.prevent="generateReport">
                {{ $this->form }}
                <div style="margin-top: 1.5rem; display: flex; justify-content: flex-end;">
                    <x-filament::button type="submit" style="background: linear-gradient(135deg, var(--accent-blue) 0%, #2563eb 100%); padding: 0.75rem 1.5rem; font-weight: 600; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);">
                        Rapport Genereren
                    </x-filament::button>
                </div>
            </form>
        </div>

        @if(!empty($reportData))
        {{-- Stats Grid --}}
        <div class="rapport-stats-grid">
            <div class="rapport-stat-card">
                <div class="rapport-stat-icon">📄</div>
                <div class="rapport-stat-label">Totaal Documenten</div>
                <div class="rapport-stat-value">{{ number_format($reportData['total_documents'] ?? 0) }}</div>
            </div>

            <div class="rapport-stat-card">
                <div class="rapport-stat-icon">💰</div>
                <div class="rapport-stat-label">Totaal Bedrag</div>
                <div class="rapport-stat-value currency"><span class="currency-inline">€{{ number_format($reportData['total_amount'] ?? 0, 2, ',', '.') }}</span></div>
            </div>

            <div class="rapport-stat-card">
                <div class="rapport-stat-icon">🧾</div>
                <div class="rapport-stat-label">BTW Totaal</div>
                <div class="rapport-stat-value currency"><span class="currency-inline">€{{ number_format($reportData['total_vat'] ?? 0, 2, ',', '.') }}</span></div>
            </div>

            <div class="rapport-stat-card">
                <div class="rapport-stat-icon">✅</div>
                <div class="rapport-stat-label">Goedgekeurd</div>
                <div class="rapport-stat-value">{{ number_format($reportData['approved_count'] ?? 0) }}</div>
            </div>

            <div class="rapport-stat-card">
                <div class="rapport-stat-icon">⏳</div>
                <div class="rapport-stat-label">In Behandeling</div>
                <div class="rapport-stat-value">{{ number_format($reportData['pending_count'] ?? 0) }}</div>
            </div>
        </div>

        {{-- Breakdown by Type --}}
        @if(!empty($reportData['by_type']))
        <div class="rapport-section-card">
            <h2 class="rapport-section-title">Verdeling per Type</h2>
            <div class="rapport-breakdown-list">
                @foreach($reportData['by_type'] as $type => $count)
                <div class="rapport-breakdown-item">
                    <div class="rapport-breakdown-label">
                        {{ match($type) {
                            'receipt' => '🧾 Bonnetjes',
                            'purchase_invoice' => '📄 Inkoopfacturen',
                            'bank_statement' => '🏦 Bankafschriften',
                            'sales_invoice' => '🧑‍💼 Verkoopfacturen',
                            default => '📁 Overig',
                        } }}
                    </div>
                    <div class="rapport-breakdown-value">{{ $count }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Top Suppliers --}}
        @if(!empty($reportData['top_suppliers']) && $reportData['top_suppliers']->isNotEmpty())
        <div class="rapport-section-card">
            <h2 class="rapport-section-title">Top Leveranciers</h2>
            <div>
                @foreach($reportData['top_suppliers'] as $supplier => $data)
                <div class="rapport-supplier-item">
                    <div class="rapport-supplier-name">{{ $supplier }}</div>
                    <div class="rapport-supplier-meta">
                        <span class="rapport-supplier-count">{{ $data['count'] }} documenten</span>
                        <span class="rapport-supplier-amount"><span class="currency-inline">€{{ number_format($data['amount'], 2, ',', '.') }}</span></span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
        @endif
    </div>
</x-filament-panels::page>
