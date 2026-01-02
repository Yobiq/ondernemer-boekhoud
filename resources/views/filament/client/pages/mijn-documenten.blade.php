<x-filament-panels::page>
    <style>
        /* Modern White Theme - Primary Design with Better Contrast */
        :root {
            --primary-bg: #ffffff;
            --secondary-bg: #f8fafc;
            --card-bg: #ffffff;
            --border-color: #e2e8f0;
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --text-tertiary: #94a3b8;
            --accent-blue: #3b82f6;
            --accent-green: #10b981;
            --accent-amber: #f59e0b;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
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

        /* Base Container */
        .documents-page-container {
            background: var(--secondary-bg);
            min-height: 100vh;
            padding: 0.75rem;
        }

        @media (min-width: 640px) {
            .documents-page-container {
                padding: 1rem;
            }
        }

        @media (min-width: 1024px) {
            .documents-page-container {
                padding: 1.5rem;
            }
        }

        /* Hero Section - Smaller & More Compact */
        .doc-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 0.875rem;
            padding: 1.25rem;
            color: white;
            margin-bottom: 1.25rem;
            box-shadow: var(--shadow-lg);
        }

        @media (min-width: 640px) {
            .doc-hero {
                padding: 1.5rem;
            }
        }

        .doc-hero-content {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        @media (min-width: 768px) {
            .doc-hero-content {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
        }

        .doc-hero-title {
            font-size: 1.25rem;
            font-weight: 700;
            line-height: 1.3;
            margin: 0 0 0.375rem 0;
        }

        @media (min-width: 640px) {
            .doc-hero-title {
                font-size: 1.5rem;
            }
        }

        @media (min-width: 1024px) {
            .doc-hero-title {
                font-size: 1.75rem;
            }
        }

        .doc-hero-subtitle {
            font-size: 0.8125rem;
            opacity: 0.95;
            line-height: 1.5;
        }

        @media (min-width: 640px) {
            .doc-hero-subtitle {
                font-size: 0.875rem;
            }
        }

        .doc-hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        /* Stats Grid - Compact */
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        @media (min-width: 640px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1.25rem;
            }
        }

        @media (min-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 1.5rem;
            }
        }

        .stat-card {
            background: var(--card-bg);
            border: 1.5px solid var(--border-color);
            border-radius: 0.875rem;
            padding: 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            box-shadow: var(--shadow-sm);
        }

        @media (min-width: 640px) {
            .stat-card {
                padding: 1.25rem;
            }
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            border-color: var(--accent-blue);
        }

        .stat-card-content {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stat-icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.625rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        @media (min-width: 640px) {
            .stat-icon {
                width: 3rem;
                height: 3rem;
                font-size: 1.5rem;
            }
        }

        .stat-icon-green {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
        }

        .dark .stat-icon-green {
            background: linear-gradient(135deg, #064e3b 0%, #065f46 100%);
            color: #a7f3d0;
        }

        .stat-icon-amber {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
        }

        .dark .stat-icon-amber {
            background: linear-gradient(135deg, #78350f 0%, #92400e 100%);
            color: #fef3c7;
        }

        .stat-icon-blue {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e40af;
        }

        .dark .stat-icon-blue {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
            color: #bfdbfe;
        }

        .stat-info {
            flex: 1;
            min-width: 0;
        }

        .stat-value {
            font-size: 1.25rem;
            font-weight: 700;
            line-height: 1.2;
            color: var(--text-primary);
            margin-bottom: 0.125rem;
        }

        @media (min-width: 640px) {
            .stat-value {
                font-size: 1.5rem;
            }
        }

        @media (min-width: 1024px) {
            .stat-value {
                font-size: 1.75rem;
            }
        }

        .stat-label {
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--text-secondary);
            line-height: 1.4;
        }

        @media (min-width: 640px) {
            .stat-label {
                font-size: 0.875rem;
            }
        }

        .stat-subtext {
            font-size: 0.6875rem;
            color: var(--text-tertiary);
            margin-top: 0.125rem;
            line-height: 1.4;
        }

        @media (min-width: 640px) {
            .stat-subtext {
                font-size: 0.75rem;
            }
        }

        /* Info Banner - Compact */
        .info-banner {
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            border-radius: 0.875rem;
            padding: 1.25rem;
            color: white;
            margin-bottom: 1.25rem;
            box-shadow: var(--shadow-md);
        }

        @media (min-width: 640px) {
            .info-banner {
                padding: 1.5rem;
            }
        }

        .info-banner-content {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        .info-banner-icon {
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        @media (min-width: 640px) {
            .info-banner-icon {
                font-size: 1.75rem;
            }
        }

        .info-banner-text {
            flex: 1;
        }

        .info-banner-title {
            font-size: 0.9375rem;
            font-weight: 700;
            margin: 0 0 0.375rem 0;
            line-height: 1.3;
        }

        @media (min-width: 640px) {
            .info-banner-title {
                font-size: 1rem;
            }
        }

        .info-banner-description {
            font-size: 0.8125rem;
            line-height: 1.5;
            opacity: 0.95;
        }

        @media (min-width: 640px) {
            .info-banner-description {
                font-size: 0.875rem;
            }
        }

        /* Summary Cards Grid - Compact */
        .summary-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        @media (min-width: 768px) {
            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1.25rem;
            }
        }

        .summary-card {
            background: var(--card-bg);
            border: 1.5px solid var(--border-color);
            border-radius: 0.875rem;
            padding: 1rem;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s;
        }

        @media (min-width: 640px) {
            .summary-card {
                padding: 1.25rem;
            }
        }

        .summary-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        .summary-card-green {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border-color: #10b981;
        }

        .dark .summary-card-green {
            background: linear-gradient(135deg, #064e3b 0%, #065f46 100%);
            border-color: #34d399;
        }

        .summary-card-blue {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border-color: #3b82f6;
        }

        .dark .summary-card-blue {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
            border-color: #60a5fa;
        }

        .summary-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .summary-title {
            font-size: 0.8125rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
            line-height: 1.3;
        }

        @media (min-width: 640px) {
            .summary-title {
                font-size: 0.875rem;
            }
        }

        .summary-icon {
            font-size: 1.5rem;
        }

        @media (min-width: 640px) {
            .summary-icon {
                font-size: 1.75rem;
            }
        }

        .summary-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
            line-height: 1.2;
        }

        @media (min-width: 640px) {
            .summary-value {
                font-size: 1.5rem;
            }
        }

        @media (min-width: 1024px) {
            .summary-value {
                font-size: 1.75rem;
            }
        }

        .summary-subtext {
            font-size: 0.75rem;
            color: var(--text-secondary);
            line-height: 1.4;
        }

        @media (min-width: 640px) {
            .summary-subtext {
                font-size: 0.8125rem;
            }
        }

        /* Quick Actions - Compact */
        .quick-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 1.25rem;
        }

        .quick-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1rem;
            border-radius: 0.625rem;
            font-weight: 600;
            font-size: 0.8125rem;
            text-decoration: none;
            transition: all 0.2s;
            box-shadow: var(--shadow-sm);
            min-height: 36px;
        }

        @media (min-width: 640px) {
            .quick-action-btn {
                padding: 0.75rem 1.25rem;
                font-size: 0.875rem;
                min-height: 40px;
            }
        }

        .quick-action-btn svg {
            width: 1rem;
            height: 1rem;
            flex-shrink: 0;
        }

        @media (min-width: 640px) {
            .quick-action-btn svg {
                width: 1.125rem;
                height: 1.125rem;
            }
        }

        .quick-action-btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
        }

        .quick-action-btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        }

        .quick-action-btn-secondary {
            background: var(--card-bg);
            color: var(--text-primary);
            border: 1.5px solid var(--border-color);
        }

        .dark .quick-action-btn-secondary {
            background: var(--card-bg);
            color: var(--text-primary);
        }

        .quick-action-btn-secondary:hover {
            background: var(--secondary-bg);
            border-color: var(--accent-blue);
            transform: translateY(-1px);
        }

        /* Table Container - Compact */
        .table-container {
            background: var(--card-bg);
            border: 1.5px solid var(--border-color);
            border-radius: 0.875rem;
            padding: 1rem;
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }

        @media (min-width: 640px) {
            .table-container {
                padding: 1.25rem;
            }
        }

        /* Enhanced Table Styling - Better Readability */
        .fi-ta-table {
            background: var(--card-bg) !important;
        }

        .fi-ta-header-cell {
            background: var(--secondary-bg) !important;
            color: var(--text-primary) !important;
            font-weight: 600 !important;
            font-size: 0.8125rem !important;
            padding: 0.75rem 0.875rem !important;
            border-bottom: 1.5px solid var(--border-color) !important;
        }

        @media (min-width: 640px) {
            .fi-ta-header-cell {
                font-size: 0.875rem !important;
                padding: 0.875rem 1rem !important;
            }
        }

        .fi-ta-cell {
            color: var(--text-primary) !important;
            font-size: 0.8125rem !important;
            padding: 0.75rem 0.875rem !important;
            border-bottom: 1px solid var(--border-color) !important;
            line-height: 1.5 !important;
        }

        @media (min-width: 640px) {
            .fi-ta-cell {
                font-size: 0.875rem !important;
                padding: 0.875rem 1rem !important;
            }
        }

        .fi-ta-row {
            transition: all 0.2s !important;
        }

        .fi-ta-row:hover {
            background: rgba(59, 130, 246, 0.05) !important;
        }

        .dark .fi-ta-row:hover {
            background: rgba(59, 130, 246, 0.1) !important;
        }

        .fi-badge {
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            padding: 0.25rem 0.625rem !important;
            line-height: 1.4 !important;
        }

        /* Search and Filter - Better Styling */
        .fi-ta-search-field input {
            background: var(--card-bg) !important;
            border: 1.5px solid var(--border-color) !important;
            border-radius: 0.625rem !important;
            padding: 0.625rem 0.875rem !important;
            font-size: 0.875rem !important;
            color: var(--text-primary) !important;
        }

        .fi-ta-search-field input:focus {
            border-color: var(--accent-blue) !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
            outline: none !important;
        }

        .dark .fi-ta-search-field input:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2) !important;
        }

        /* Mobile Optimizations - Super Responsive */
        @media (max-width: 639px) {
            .documents-page-container {
                padding: 0.75rem;
            }

            .doc-hero {
                padding: 1rem;
                margin-bottom: 1rem;
            }

            .doc-hero-title {
                font-size: 1.125rem;
            }

            .doc-hero-subtitle {
                font-size: 0.75rem;
            }

            .stats-grid {
                gap: 0.875rem;
                margin-bottom: 1rem;
            }

            .stat-card {
                padding: 0.875rem;
            }

            .stat-card-content {
                gap: 0.875rem;
            }

            .stat-icon {
                width: 2.25rem;
                height: 2.25rem;
                font-size: 1.125rem;
            }

            .stat-value {
                font-size: 1.125rem;
            }

            .stat-label {
                font-size: 0.75rem;
            }

            .stat-subtext {
                font-size: 0.625rem;
            }

            .info-banner {
                padding: 1rem;
                margin-bottom: 1rem;
            }

            .info-banner-icon {
                font-size: 1.25rem;
            }

            .info-banner-title {
                font-size: 0.875rem;
            }

            .info-banner-description {
                font-size: 0.75rem;
            }

            .summary-grid {
                gap: 0.875rem;
                margin-bottom: 1rem;
            }

            .summary-card {
                padding: 0.875rem;
            }

            .summary-value {
                font-size: 1.125rem;
            }

            .summary-title {
                font-size: 0.75rem;
            }

            .summary-subtext {
                font-size: 0.6875rem;
            }

            .quick-actions {
                gap: 0.625rem;
                margin-bottom: 1rem;
            }

            .quick-action-btn {
                padding: 0.5625rem 0.875rem;
                font-size: 0.75rem;
                min-height: 34px;
            }

            .quick-action-btn svg {
                width: 0.875rem;
                height: 0.875rem;
            }

            .table-container {
                padding: 0.875rem;
                border-radius: 0.75rem;
            }

            .fi-ta-cell,
            .fi-ta-header-cell {
                padding: 0.625rem 0.5rem !important;
                font-size: 0.75rem !important;
            }

            .fi-ta-header-cell { 
                font-size: 0.75rem !important;
            }
        }

        /* Animations */
        @keyframes pulse-glow {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4);
            }
            50% {
                box-shadow: 0 0 0 6px rgba(245, 158, 11, 0);
            }
        }

        .stat-card-processing {
            animation: pulse-glow 2s infinite;
        }

        /* Fix for better text contrast in dark mode */
        .dark .stat-value,
        .dark .summary-value {
            color: #f1f5f9 !important;
        }

        .dark .stat-label,
        .dark .summary-title {
            color: #cbd5e1 !important;
            }

        /* Better spacing between sections */
        .stats-grid + .info-banner {
            margin-top: 0;
        }
    </style>

    <div class="documents-page-container">
        {{-- Hero Section --}}
        <div class="doc-hero">
            <div class="doc-hero-content">
                <div class="flex-1">
                    <h1 class="doc-hero-title">📄 Mijn Documenten</h1>
                    <p class="doc-hero-subtitle">
                        Overzicht van al uw uploads met realtime status updates en slimme filters
                    </p>
                </div>
                <div class="doc-hero-actions">
                    <a href="{{ \App\Filament\Client\Pages\SmartUpload::getUrl() }}" class="quick-action-btn quick-action-btn-primary">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Nieuw Uploaden</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Stats Grid --}}
        @php
            $clientId = Auth::user()->client_id;
            $approvedCount = \App\Models\Document::where('client_id', $clientId)->where('status', 'approved')->count();
            $pendingCount = \App\Models\Document::where('client_id', $clientId)->whereIn('status', ['pending', 'ocr_processing', 'review_required'])->count();
            $totalCount = \App\Models\Document::where('client_id', $clientId)->count();
            $approvalRate = $totalCount > 0 ? round(($approvedCount / $totalCount) * 100) : 0;
        @endphp

        <div class="stats-grid">
            <div class="stat-card" onclick="document.querySelector('[data-status-filter=\"approved\"]')?.click()">
                <div class="stat-card-content">
                    <div class="stat-icon stat-icon-green">✅</div>
                    <div class="stat-info">
                        <div class="stat-value">{{ number_format($approvedCount) }}</div>
                        <div class="stat-label">Goedgekeurd</div>
                        @if($approvalRate > 0)
                        <div class="stat-subtext">{{ $approvalRate }}% goedkeuringsgraad</div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="stat-card stat-card-processing" onclick="document.querySelector('[data-status-filter=\"pending\"]')?.click()">
                <div class="stat-card-content">
                    <div class="stat-icon stat-icon-amber">🔄</div>
                    <div class="stat-info">
                        <div class="stat-value">{{ number_format($pendingCount) }}</div>
                        <div class="stat-label">In Behandeling</div>
                        @if($pendingCount > 0)
                        <div class="stat-subtext">Wordt verwerkt...</div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-card-content">
                    <div class="stat-icon stat-icon-blue">📊</div>
                    <div class="stat-info">
                        <div class="stat-value">{{ number_format($totalCount) }}</div>
                        <div class="stat-label">Totaal Documenten</div>
                        <div class="stat-subtext">Alle uploads</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Workflow Visualization --}}
        <div class="info-banner" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
            <div class="info-banner-content">
                <div class="info-banner-icon">📋</div>
                <div class="info-banner-text">
                    <h3 class="info-banner-title">Document Workflow</h3>
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-top: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem; flex: 1; min-width: 120px;">
                            <span style="font-size: 1.5rem;">📤</span>
                            <div>
                                <div style="font-weight: 600; font-size: 0.875rem;">Upload</div>
                                <div style="font-size: 0.75rem; opacity: 0.9;">Document wordt geüpload</div>
                            </div>
                        </div>
                        <div style="flex: 0 0 20px; height: 2px; background: rgba(255,255,255,0.3);"></div>
                        <div style="display: flex; align-items: center; gap: 0.5rem; flex: 1; min-width: 120px;">
                            <span style="font-size: 1.5rem;">🔍</span>
                            <div>
                                <div style="font-weight: 600; font-size: 0.875rem;">Smart OCR</div>
                                <div style="font-size: 0.75rem; opacity: 0.9;">Automatische extractie</div>
                            </div>
                        </div>
                        <div style="flex: 0 0 20px; height: 2px; background: rgba(255,255,255,0.3);"></div>
                        <div style="display: flex; align-items: center; gap: 0.5rem; flex: 1; min-width: 120px;">
                            <span style="font-size: 1.5rem;">👀</span>
                            <div>
                                <div style="font-weight: 600; font-size: 0.875rem;">Review</div>
                                <div style="font-size: 0.75rem; opacity: 0.9;">Handmatige controle</div>
                            </div>
                        </div>
                        <div style="flex: 0 0 20px; height: 2px; background: rgba(255,255,255,0.3);"></div>
                        <div style="display: flex; align-items: center; gap: 0.5rem; flex: 1; min-width: 120px;">
                            <span style="font-size: 1.5rem;">✅</span>
                            <div>
                                <div style="font-weight: 600; font-size: 0.875rem;">Goedgekeurd</div>
                                <div style="font-size: 0.75rem; opacity: 0.9;">Document afgerond</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Info Banner --}}
        <div class="info-banner">
            <div class="info-banner-content">
                <div class="info-banner-icon">🔄</div>
                <div class="info-banner-text">
                    <h3 class="info-banner-title">Automatische Updates</h3>
                    <p class="info-banner-description">
                        Deze tabel ververst <strong>elke 30 seconden</strong> automatisch. 
                        U ziet realtime wanneer documenten zijn verwerkt en goedgekeurd!
                    </p>
                </div>
            </div>
        </div>

        {{-- Summary Cards --}}
        @php
            $totalAmount = \App\Models\Document::where('client_id', $clientId)
                ->whereNotNull('amount_incl')
                ->sum('amount_incl') ?? 0;
            $thisMonthAmount = \App\Models\Document::where('client_id', $clientId)
                ->whereNotNull('amount_incl')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount_incl') ?? 0;
            $thisWeekCount = \App\Models\Document::where('client_id', $clientId)
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count();
        @endphp

        @if($totalAmount > 0 || $thisWeekCount > 0)
        <div class="summary-grid">
            @if($totalAmount > 0)
            <div class="summary-card summary-card-green">
                <div class="summary-header">
                    <h3 class="summary-title">💰 Totaal Bedrag</h3>
                    <div class="summary-icon">📊</div>
                </div>
                <div class="summary-value">€{{ number_format($totalAmount, 2, ',', '.') }}</div>
                @if($thisMonthAmount > 0)
                <div class="summary-subtext">Deze maand: €{{ number_format($thisMonthAmount, 2, ',', '.') }}</div>
                @endif
            </div>
            @endif

            @if($thisWeekCount > 0)
            <div class="summary-card summary-card-blue">
                <div class="summary-header">
                    <h3 class="summary-title">📅 Deze Week</h3>
                    <div class="summary-icon">📅</div>
                </div>
                <div class="summary-value">{{ $thisWeekCount }} {{ $thisWeekCount === 1 ? 'document' : 'documenten' }}</div>
                <div class="summary-subtext">Geüpload deze week</div>
            </div>
            @endif
        </div>
        @endif

        {{-- Quick Actions --}}
        <div class="quick-actions">
            <a href="{{ \App\Filament\Client\Pages\SmartUpload::getUrl() }}" class="quick-action-btn quick-action-btn-primary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>📸 Nieuw Document Uploaden</span>
            </a>
            <button onclick="document.querySelector('.fi-ta-search-field input')?.focus()" class="quick-action-btn quick-action-btn-secondary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <span>🔍 Zoeken</span>
            </button>
        </div>

        {{-- Table Container --}}
        <div class="table-container">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
