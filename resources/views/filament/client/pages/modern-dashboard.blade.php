<x-filament-panels::page>
    @php
        $user = auth()->user();
        $clientId = $user?->client_id ?? null;
        $userName = $user?->name ?? 'Gebruiker';
        
        // Core statistics
        $totalDocs = $clientId ? \App\Models\Document::where('client_id', $clientId)->count() : 0;
        $approvedDocs = $clientId ? \App\Models\Document::where('client_id', $clientId)->where('status', 'approved')->count() : 0;
        $pendingDocs = $clientId ? \App\Models\Document::where('client_id', $clientId)->whereIn('status', ['pending', 'ocr_processing', 'review_required'])->count() : 0;
        $openTasks = $clientId ? \App\Models\Task::where('client_id', $clientId)->where('status', 'open')->count() : 0;
        $thisMonthDocs = $clientId ? \App\Models\Document::where('client_id', $clientId)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count() : 0;
        
        // Approval rate
        $approvalRate = $totalDocs > 0 ? round(($approvedDocs / $totalDocs) * 100) : 0;
        
        // Time-based greeting
        $hour = now()->hour;
        $greeting = $hour < 12 ? 'Goedemorgen' : ($hour < 18 ? 'Goedemiddag' : 'Goedenavond');
        
        // Get first name
        $firstName = explode(' ', $userName)[0];
    @endphp

    {{-- Modern Compact Dashboard --}}
    <div class="modern-dashboard">
        {{-- Top Bar with Search --}}
        <div class="top-bar">
            <div class="top-bar-left">
                <h1 class="page-title">{{ $greeting }}, {{ $firstName }}! 👋</h1>
                <p class="page-subtitle">{{ now()->translatedFormat('l, d F Y') }}</p>
            </div>
            <div class="top-bar-right">
                <div class="search-container">
                    <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <input type="text" class="search-input" placeholder="Zoek documenten, taken..." id="dashboardSearch">
                </div>
                <a href="#" onclick="window.location.href='/klanten/smart-upload'; return false;" class="upload-btn">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Upload</span>
                </a>
            </div>
        </div>

        {{-- Enhanced Stats Grid with Analytics --}}
        <div class="enhanced-stats-grid">
            {{-- Row 1: Main Stats (4 cards) --}}
            <div class="compact-stat-card stat-primary">
                <div class="stat-icon-wrapper">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Totaal Documenten</div>
                    <div class="stat-value">{{ $totalDocs }}</div>
                    <div class="stat-change positive">+{{ $thisMonthDocs }} deze maand</div>
                </div>
            </div>

            <div class="compact-stat-card stat-success">
                <div class="stat-icon-wrapper">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Goedgekeurd</div>
                    <div class="stat-value">{{ $approvedDocs }}</div>
                    <div class="stat-change">{{ $approvalRate }}% goedkeuringsratio</div>
                </div>
            </div>

            <div class="compact-stat-card stat-warning">
                <div class="stat-icon-wrapper">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-label">In Behandeling</div>
                    <div class="stat-value">{{ $pendingDocs }}</div>
                    <div class="stat-change">{{ $pendingDocs === 0 ? 'Alles verwerkt' : 'Wordt verwerkt' }}</div>
                </div>
            </div>

            <div class="compact-stat-card stat-info">
                <div class="stat-icon-wrapper">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Open Taken</div>
                    <div class="stat-value">{{ $openTasks }}</div>
                    <div class="stat-change">{{ $openTasks === 0 ? 'Geen open taken' : 'Actieve taken' }}</div>
                </div>
            </div>

            {{-- Row 2: Analytics Cards (3 cards) --}}
            @php
                // Get document types
                $docTypes = $clientId ? \App\Models\Document::where('client_id', $clientId)
                    ->select('document_type', \DB::raw('COUNT(*) as count'))
                    ->groupBy('document_type')
                    ->get() : collect();
                
                $typeLabels = [
                    'receipt' => 'Bonnetjes',
                    'purchase_invoice' => 'Inkoopfacturen',
                    'bank_statement' => 'Bankafschriften',
                    'sales_invoice' => 'Verkoopfacturen',
                    'other' => 'Overig'
                ];
                
                // Get last 7 days uploads
                $last7Days = $clientId ? \App\Models\Document::where('client_id', $clientId)
                    ->where('created_at', '>=', now()->subDays(7))
                    ->count() : 0;
                
                // Get this week vs last week
                $thisWeek = $clientId ? \App\Models\Document::where('client_id', $clientId)
                    ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                    ->count() : 0;
                    
                $lastWeek = $clientId ? \App\Models\Document::where('client_id', $clientId)
                    ->whereBetween('created_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])
                    ->count() : 0;
                    
                $weekTrend = $lastWeek > 0 ? round((($thisWeek - $lastWeek) / $lastWeek) * 100) : ($thisWeek > 0 ? 100 : 0);
            @endphp

            {{-- Uploads Timeline Card --}}
            <div class="analytics-card">
                <div class="analytics-header">
                    <div class="analytics-icon">📈</div>
                    <div>
                        <div class="analytics-title">Uploads Deze Week</div>
                        <div class="analytics-subtitle">Laatste 7 dagen activiteit</div>
                    </div>
                </div>
                <div class="analytics-body">
                    <div class="analytics-main-stat">{{ $thisWeek }}</div>
                    <div class="analytics-trend {{ $weekTrend >= 0 ? 'positive' : 'negative' }}">
                        <svg class="trend-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if($weekTrend >= 0)
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                            @endif
                        </svg>
                        <span>{{ abs($weekTrend) }}% vs vorige week</span>
                    </div>
                    <div class="analytics-detail">
                        <span class="detail-label">Totaal laatste 7 dagen:</span>
                        <span class="detail-value">{{ $last7Days }}</span>
                    </div>
                </div>
            </div>

            {{-- Document Types Card --}}
            <div class="analytics-card">
                <div class="analytics-header">
                    <div class="analytics-icon">📋</div>
                    <div>
                        <div class="analytics-title">Document Types</div>
                        <div class="analytics-subtitle">Verdeling per categorie</div>
                    </div>
                </div>
                <div class="analytics-body">
                    @if($docTypes->count() > 0)
                        <div class="type-list">
                            @foreach($docTypes->take(3) as $type)
                            <div class="type-item">
                                <div class="type-info">
                                    <span class="type-label">{{ $typeLabels[$type->document_type] ?? 'Overig' }}</span>
                                    <span class="type-count">{{ $type->count }}</span>
                                </div>
                                <div class="type-bar">
                                    <div class="type-bar-fill" style="width: {{ $totalDocs > 0 ? ($type->count / $totalDocs * 100) : 0 }}%"></div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state-small">
                            <span class="empty-icon">📄</span>
                            <span class="empty-text">Nog geen documenten</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Auto-Approval Card --}}
            <div class="analytics-card">
                <div class="analytics-header">
                    <div class="analytics-icon">🎯</div>
                    <div>
                        <div class="analytics-title">Automatische Goedkeuring</div>
                        <div class="analytics-subtitle">Verwerkingsefficiëntie</div>
                    </div>
                </div>
                <div class="analytics-body">
                    <div class="approval-circle">
                        <svg class="approval-svg" viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r="45" fill="none" stroke="#e5e7eb" stroke-width="8"/>
                            <circle cx="50" cy="50" r="45" fill="none" stroke="url(#gradient-approval)" stroke-width="8" 
                                    stroke-linecap="round"
                                    style="stroke-dasharray: {{ 283 * ($approvalRate / 100) }} 283; transform: rotate(-90deg); transform-origin: center;"/>
                            <defs>
                                <linearGradient id="gradient-approval" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#10b981;stop-opacity:1" />
                                    <stop offset="100%" style="stop-color:#059669;stop-opacity:1" />
                                </linearGradient>
                            </defs>
                        </svg>
                        <div class="approval-percentage">{{ $approvalRate }}%</div>
                    </div>
                    <div class="approval-stats">
                        <div class="approval-stat">
                            <span class="approval-stat-label">Automatisch:</span>
                            <span class="approval-stat-value">{{ $approvedDocs }}</span>
                        </div>
                        <div class="approval-stat">
                            <span class="approval-stat-label">Handmatig:</span>
                            <span class="approval-stat-value">{{ $totalDocs - $approvedDocs }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Widgets Grid --}}
        <div class="widgets-grid">
            @foreach ($this->getWidgets() as $widget)
                @livewire($widget)
            @endforeach
        </div>
    </div>

    {{-- Include Modern CSS with cache busting --}}
    <link rel="stylesheet" href="{{ asset('css/client-modern.css') }}?v={{ time() }}">

    <style>
        /* Modern Dashboard Styles */
        .modern-dashboard {
            padding: 0;
            max-width: 100%;
        }

        /* Top Bar */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        .top-bar-left {
            flex: 1;
            min-width: 250px;
        }

        .page-title {
            font-size: 1.875rem;
            font-weight: 800;
            color: #111827;
            margin: 0 0 0.25rem 0;
            letter-spacing: -0.025em;
        }

        .dark .page-title {
            color: #f9fafb;
        }

        .page-subtitle {
            font-size: 0.875rem;
            color: #6b7280;
            margin: 0;
        }

        .dark .page-subtitle {
            color: #9ca3af;
        }

        .top-bar-right {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        /* Search Container */
        .search-container {
            position: relative;
            width: 320px;
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            width: 1.25rem;
            height: 1.25rem;
            color: #9ca3af;
            pointer-events: none;
        }

        .search-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 3rem;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            font-size: 0.875rem;
            background: #ffffff;
            transition: all 0.2s;
        }

        .dark .search-input {
            background: #1f2937;
            border-color: #374151;
            color: #f9fafb;
        }

        .search-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .upload-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.875rem;
            transition: all 0.2s;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .upload-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4);
        }

        .upload-btn svg {
            width: 1.25rem;
            height: 1.25rem;
        }

        /* Enhanced Stats Grid with Analytics */
        .enhanced-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        @media (min-width: 768px) {
            .enhanced-stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1024px) {
            .enhanced-stats-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        .compact-stat-card {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.25rem;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .dark .compact-stat-card {
            background: #1f2937;
            border-color: #374151;
        }

        .compact-stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
        }

        .dark .compact-stat-card:hover {
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.3);
        }

        .stat-icon-wrapper {
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .stat-icon-wrapper svg {
            width: 1.75rem;
            height: 1.75rem;
        }

        .stat-primary .stat-icon-wrapper {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            color: #1e40af;
        }

        .dark .stat-primary .stat-icon-wrapper {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
            color: #93c5fd;
        }

        .stat-success .stat-icon-wrapper {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
        }

        .dark .stat-success .stat-icon-wrapper {
            background: linear-gradient(135deg, #064e3b 0%, #065f46 100%);
            color: #6ee7b7;
        }

        .stat-warning .stat-icon-wrapper {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
        }

        .dark .stat-warning .stat-icon-wrapper {
            background: linear-gradient(135deg, #78350f 0%, #92400e 100%);
            color: #fcd34d;
        }

        .stat-info .stat-icon-wrapper {
            background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
            color: #3730a3;
        }

        .dark .stat-info .stat-icon-wrapper {
            background: linear-gradient(135deg, #312e81 0%, #3730a3 100%);
            color: #a5b4fc;
        }

        .stat-content {
            flex: 1;
            min-width: 0;
        }

        .stat-label {
            font-size: 0.8125rem;
            font-weight: 500;
            color: #6b7280;
            margin-bottom: 0.25rem;
        }

        .dark .stat-label {
            color: #9ca3af;
        }

        .stat-value {
            font-size: 1.875rem;
            font-weight: 800;
            color: #111827;
            line-height: 1;
            margin-bottom: 0.375rem;
            letter-spacing: -0.025em;
        }

        .dark .stat-value {
            color: #f9fafb;
        }

        .stat-change {
            font-size: 0.75rem;
            color: #6b7280;
            font-weight: 500;
        }

        .dark .stat-change {
            color: #9ca3af;
        }

        .stat-change.positive {
            color: #059669;
        }

        .dark .stat-change.positive {
            color: #34d399;
        }

        /* Widgets Grid */
        .widgets-grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 1.5rem;
        }

        .widgets-grid > * {
            grid-column: span 12;
        }

        @media (min-width: 768px) {
            .widgets-grid > * {
                grid-column: span 6;
            }
        }

        @media (min-width: 1024px) {
            .widgets-grid > *:nth-child(1),
            .widgets-grid > *:nth-child(2) {
                grid-column: span 6;
            }
            .widgets-grid > *:nth-child(n+3) {
                grid-column: span 4;
            }
        }

        /* Widget Styling */
        .fi-section {
            border-radius: 16px !important;
            border: 1px solid #e5e7eb !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05) !important;
            transition: all 0.2s !important;
        }

        .dark .fi-section {
            border-color: #374151 !important;
        }

        .fi-section:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
        }

        /* Analytics Cards */
        .analytics-card {
            grid-column: span 1;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 1.25rem;
            transition: all 0.3s;
        }

        .dark .analytics-card {
            background: #1f2937;
            border-color: #374151;
        }

        .analytics-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
        }

        .dark .analytics-card:hover {
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.3);
        }

        .analytics-header {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .analytics-icon {
            font-size: 1.75rem;
            flex-shrink: 0;
        }

        .analytics-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 0.125rem;
        }

        .dark .analytics-title {
            color: #f9fafb;
        }

        .analytics-subtitle {
            font-size: 0.75rem;
            color: #6b7280;
        }

        .dark .analytics-subtitle {
            color: #9ca3af;
        }

        .analytics-body {
            margin-top: 1rem;
        }

        .analytics-main-stat {
            font-size: 2.25rem;
            font-weight: 800;
            color: #111827;
            line-height: 1;
            margin-bottom: 0.75rem;
        }

        .dark .analytics-main-stat {
            color: #f9fafb;
        }

        .analytics-trend {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            font-size: 0.8125rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
        }

        .analytics-trend.positive {
            color: #059669;
        }

        .dark .analytics-trend.positive {
            color: #34d399;
        }

        .analytics-trend.negative {
            color: #dc2626;
        }

        .dark .analytics-trend.negative {
            color: #f87171;
        }

        .trend-icon {
            width: 1rem;
            height: 1rem;
        }

        .analytics-detail {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.625rem;
            background: #f9fafb;
            border-radius: 8px;
            font-size: 0.8125rem;
        }

        .dark .analytics-detail {
            background: #111827;
        }

        .detail-label {
            color: #6b7280;
        }

        .dark .detail-label {
            color: #9ca3af;
        }

        .detail-value {
            font-weight: 700;
            color: #111827;
        }

        .dark .detail-value {
            color: #f9fafb;
        }

        /* Type List */
        .type-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .type-item {
            display: flex;
            flex-direction: column;
            gap: 0.375rem;
        }

        .type-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .type-label {
            font-size: 0.8125rem;
            color: #374151;
            font-weight: 500;
        }

        .dark .type-label {
            color: #d1d5db;
        }

        .type-count {
            font-size: 0.875rem;
            font-weight: 700;
            color: #111827;
        }

        .dark .type-count {
            color: #f9fafb;
        }

        .type-bar {
            height: 0.375rem;
            background: #e5e7eb;
            border-radius: 9999px;
            overflow: hidden;
        }

        .dark .type-bar {
            background: #374151;
        }

        .type-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #3b82f6 0%, #10b981 100%);
            transition: width 0.6s ease;
            border-radius: 9999px;
        }

        /* Approval Circle */
        .approval-circle {
            position: relative;
            width: 100px;
            height: 100px;
            margin: 0 auto 1rem;
        }

        .approval-svg {
            width: 100%;
            height: 100%;
        }

        .approval-percentage {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 1.5rem;
            font-weight: 800;
            color: #111827;
        }

        .dark .approval-percentage {
            color: #f9fafb;
        }

        .approval-stats {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .approval-stat {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem;
            background: #f9fafb;
            border-radius: 8px;
            font-size: 0.8125rem;
        }

        .dark .approval-stat {
            background: #111827;
        }

        .approval-stat-label {
            color: #6b7280;
        }

        .dark .approval-stat-label {
            color: #9ca3af;
        }

        .approval-stat-value {
            font-weight: 700;
            color: #111827;
        }

        .dark .approval-stat-value {
            color: #f9fafb;
        }

        /* Empty State Small */
        .empty-state-small {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            text-align: center;
        }

        .empty-icon {
            font-size: 2.5rem;
            opacity: 0.5;
            margin-bottom: 0.5rem;
        }

        .empty-text {
            font-size: 0.875rem;
            color: #6b7280;
        }

        .dark .empty-text {
            color: #9ca3af;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .top-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .top-bar-right {
                flex-direction: column;
            }

            .search-container {
                width: 100%;
            }

            .upload-btn {
                justify-content: center;
            }

            .compact-stats-grid {
                grid-template-columns: 1fr;
            }

            .page-title {
                font-size: 1.5rem;
            }
        }

        /* Search Functionality */
        #dashboardSearch {
            transition: all 0.3s;
        }

        #dashboardSearch:focus {
            width: 100%;
        }
    </style>

    <script>
        // Search functionality
        document.getElementById('dashboardSearch')?.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            // Add search logic here
            console.log('Searching for:', searchTerm);
        });
    </script>
</x-filament-panels::page>

