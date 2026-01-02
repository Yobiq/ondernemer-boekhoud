<x-filament-panels::page>
    @php
        $user = auth()->user();
        $clientId = $user?->client_id ?? null;
        $userName = $user?->name ?? 'Gebruiker';
        $firstName = explode(' ', $userName)[0];
        
        // Core statistics
        $totalDocs = $clientId ? \App\Models\Document::where('client_id', $clientId)->count() : 0;
        $approvedDocs = $clientId ? \App\Models\Document::where('client_id', $clientId)->where('status', 'approved')->count() : 0;
        $pendingDocs = $clientId ? \App\Models\Document::where('client_id', $clientId)->whereIn('status', ['pending', 'ocr_processing', 'review_required'])->count() : 0;
        $openTasks = $clientId ? \App\Models\Task::where('client_id', $clientId)->where('status', 'open')->count() : 0;
        
        // This month
        $thisMonth = $clientId ? \App\Models\Document::where('client_id', $clientId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count() : 0;
        
        // Recent documents (last 5)
        $recentDocs = $clientId ? \App\Models\Document::where('client_id', $clientId)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get() : collect();
        
        // Approval rate
        $approvalRate = $totalDocs > 0 ? round(($approvedDocs / $totalDocs) * 100) : 0;
        
        // Time greeting
        $hour = now()->hour;
        $greeting = $hour < 12 ? 'Goedemorgen' : ($hour < 18 ? 'Goedemiddag' : 'Goedenavond');
    @endphp

    {{-- DEBUG: New Dashboard Loaded Successfully! --}}
    <div class="clean-dashboard" style="background: transparent;">
        {{-- Header --}}
        <div class="dashboard-header">
            <div class="header-content">
                <div class="greeting-section">
                    <h1 class="main-greeting" style="color: #111827 !important; font-size: 1.75rem !important; font-weight: 800 !important;">
                        {{ $greeting }}, {{ $firstName }}! 👋
                    </h1>
                    <p class="date-text">{{ now()->translatedFormat('l, d F Y') }} • Nieuwe Dashboard v2.0</p>
                </div>
                <div class="header-actions">
                    <button onclick="window.location.href='/klanten/smart-upload'" class="btn-upload">
                        <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Upload Document</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="kpi-grid">
            <div class="kpi-card kpi-blue">
                <div class="kpi-icon">📄</div>
                <div class="kpi-content">
                    <div class="kpi-value">{{ $totalDocs }}</div>
                    <div class="kpi-label">Totaal Documenten</div>
                    <div class="kpi-badge badge-blue">+{{ $thisMonth }} deze maand</div>
                </div>
            </div>

            <div class="kpi-card kpi-green">
                <div class="kpi-icon">✅</div>
                <div class="kpi-content">
                    <div class="kpi-value">{{ $approvedDocs }}</div>
                    <div class="kpi-label">Goedgekeurd</div>
                    <div class="kpi-badge badge-green">{{ $approvalRate }}%</div>
                </div>
            </div>

            <div class="kpi-card kpi-orange">
                <div class="kpi-icon">⏳</div>
                <div class="kpi-content">
                    <div class="kpi-value">{{ $pendingDocs }}</div>
                    <div class="kpi-label">In Behandeling</div>
                    <div class="kpi-badge badge-orange">{{ $pendingDocs === 0 ? 'Up-to-date' : 'Wordt verwerkt' }}</div>
                </div>
            </div>

            <div class="kpi-card kpi-purple">
                <div class="kpi-icon">📋</div>
                <div class="kpi-content">
                    <div class="kpi-value">{{ $openTasks }}</div>
                    <div class="kpi-label">Open Taken</div>
                    <div class="kpi-badge badge-purple">{{ $openTasks === 0 ? 'Geen taken' : 'Actief' }}</div>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="quick-actions">
            <h2 class="section-title">Snelle Acties</h2>
            <div class="actions-grid">
                <a href="/klanten/smart-upload" class="action-card">
                    <div class="action-icon action-blue">📸</div>
                    <div class="action-text">
                        <div class="action-title">Document Uploaden</div>
                        <div class="action-desc">Maak foto of upload bestand</div>
                    </div>
                    <div class="action-arrow">→</div>
                </a>

                <a href="/klanten/mijn-documenten" class="action-card">
                    <div class="action-icon action-green">📂</div>
                    <div class="action-text">
                        <div class="action-title">Mijn Documenten</div>
                        <div class="action-desc">Bekijk alle uploads</div>
                    </div>
                    <div class="action-arrow">→</div>
                </a>

                <a href="/klanten/hulp-faq" class="action-card">
                    <div class="action-icon action-purple">💬</div>
                    <div class="action-text">
                        <div class="action-title">Hulp & Support</div>
                        <div class="action-desc">Veelgestelde vragen</div>
                    </div>
                    <div class="action-arrow">→</div>
                </a>

                <a href="/klanten/handleiding" class="action-card">
                    <div class="action-icon action-orange">📖</div>
                    <div class="action-text">
                        <div class="action-title">Handleiding</div>
                        <div class="action-desc">Leer hoe het werkt</div>
                    </div>
                    <div class="action-arrow">→</div>
                </a>
            </div>
        </div>

        {{-- Recent Activity --}}
        <div class="recent-activity">
            <h2 class="section-title">Recente Activiteit</h2>
            @if($recentDocs->count() > 0)
                <div class="activity-list">
                    @foreach($recentDocs as $doc)
                    <div class="activity-item">
                        <div class="activity-icon">
                            @if($doc->status === 'approved')
                                <div class="status-dot status-green"></div>
                            @elseif($doc->status === 'review_required')
                                <div class="status-dot status-orange"></div>
                            @else
                                <div class="status-dot status-blue"></div>
                            @endif
                        </div>
                        <div class="activity-content">
                            <div class="activity-title">{{ $doc->original_filename }}</div>
                            <div class="activity-meta">
                                <span class="status-badge status-{{ $doc->status }}">
                                    @if($doc->status === 'approved') Goedgekeurd
                                    @elseif($doc->status === 'review_required') In Review
                                    @elseif($doc->status === 'pending') In Wachtrij
                                    @else {{ ucfirst($doc->status) }}
                                    @endif
                                </span>
                                <span class="activity-time">{{ $doc->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        <a href="/klanten/mijn-documenten" class="activity-link">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <div class="empty-title">Nog geen documenten</div>
                    <div class="empty-desc">Upload uw eerste document om te beginnen</div>
                    <button onclick="window.location.href='/klanten/smart-upload'" class="btn-primary">
                        Upload Nu
                    </button>
                </div>
            @endif
        </div>

        {{-- Widgets Section --}}
        <div class="widgets-section">
            <x-filament-widgets::widgets
                :widgets="$this->getVisibleWidgets()"
                :columns="$this->getColumns()"
            />
        </div>
    </div>

    <style>
        /* Clean Modern Dashboard Styles */
        .clean-dashboard {
            padding: 0;
            max-width: 100%;
        }

        /* Header */
        .dashboard-header {
            margin-bottom: 2rem;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .greeting-section {
            flex: 1;
            min-width: 200px;
        }

        .main-greeting {
            font-size: 1.75rem;
            font-weight: 800;
            color: #111827;
            margin: 0 0 0.25rem 0;
            letter-spacing: -0.025em;
        }

        .dark .main-greeting {
            color: #f9fafb;
        }

        .date-text {
            font-size: 0.875rem;
            color: #6b7280;
            margin: 0;
        }

        .dark .date-text {
            color: #9ca3af;
        }

        .btn-upload {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.9375rem;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn-upload:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4);
        }

        .btn-icon {
            width: 1.25rem;
            height: 1.25rem;
        }

        /* KPI Grid */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        @media (min-width: 640px) {
            .kpi-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1024px) {
            .kpi-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        .kpi-card {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.25rem;
            background: white;
            border-radius: 16px;
            border: 1px solid #e5e7eb;
            transition: all 0.3s;
        }

        .dark .kpi-card {
            background: #1f2937;
            border-color: #374151;
        }

        .kpi-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
        }

        .kpi-icon {
            font-size: 2.5rem;
            flex-shrink: 0;
        }

        .kpi-content {
            flex: 1;
        }

        .kpi-value {
            font-size: 2rem;
            font-weight: 800;
            color: #111827;
            line-height: 1;
            margin-bottom: 0.375rem;
        }

        .dark .kpi-value {
            color: #f9fafb;
        }

        .kpi-label {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .dark .kpi-label {
            color: #9ca3af;
        }

        .kpi-badge {
            display: inline-block;
            padding: 0.25rem 0.625rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-blue {
            background: #eff6ff;
            color: #1e40af;
        }

        .dark .badge-blue {
            background: #1e3a8a;
            color: #93c5fd;
        }

        .badge-green {
            background: #f0fdf4;
            color: #166534;
        }

        .dark .badge-green {
            background: #14532d;
            color: #86efac;
        }

        .badge-orange {
            background: #fff7ed;
            color: #9a3412;
        }

        .dark .badge-orange {
            background: #7c2d12;
            color: #fdba74;
        }

        .badge-purple {
            background: #faf5ff;
            color: #6b21a8;
        }

        .dark .badge-purple {
            background: #581c87;
            color: #d8b4fe;
        }

        /* Section Title */
        .section-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: #111827;
            margin: 0 0 1rem 0;
        }

        .dark .section-title {
            color: #f9fafb;
        }

        /* Quick Actions */
        .quick-actions {
            margin-bottom: 2rem;
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1rem;
        }

        @media (min-width: 768px) {
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
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            transition: all 0.2s;
            text-decoration: none;
            cursor: pointer;
        }

        .dark .action-card {
            background: #1f2937;
            border-color: #374151;
        }

        .action-card:hover {
            transform: translateX(4px);
            border-color: #3b82f6;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .action-icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .action-blue {
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
        }

        .dark .action-blue {
            background: linear-gradient(135deg, #1e3a8a, #1e40af);
        }

        .action-green {
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
        }

        .dark .action-green {
            background: linear-gradient(135deg, #064e3b, #065f46);
        }

        .action-purple {
            background: linear-gradient(135deg, #e9d5ff, #d8b4fe);
        }

        .dark .action-purple {
            background: linear-gradient(135deg, #581c87, #6b21a8);
        }

        .action-orange {
            background: linear-gradient(135deg, #fed7aa, #fdba74);
        }

        .dark .action-orange {
            background: linear-gradient(135deg, #7c2d12, #9a3412);
        }

        .action-text {
            flex: 1;
        }

        .action-title {
            font-size: 0.9375rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 0.125rem;
        }

        .dark .action-title {
            color: #f9fafb;
        }

        .action-desc {
            font-size: 0.75rem;
            color: #6b7280;
        }

        .dark .action-desc {
            color: #9ca3af;
        }

        .action-arrow {
            font-size: 1.25rem;
            color: #9ca3af;
            transition: all 0.2s;
        }

        .action-card:hover .action-arrow {
            color: #3b82f6;
            transform: translateX(4px);
        }

        /* Recent Activity */
        .recent-activity {
            margin-bottom: 2rem;
        }

        .activity-list {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            overflow: hidden;
        }

        .dark .activity-list {
            background: #1f2937;
            border-color: #374151;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border-bottom: 1px solid #f3f4f6;
            transition: all 0.2s;
        }

        .dark .activity-item {
            border-bottom-color: #374151;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-item:hover {
            background: #f9fafb;
        }

        .dark .activity-item:hover {
            background: #111827;
        }

        .activity-icon {
            flex-shrink: 0;
        }

        .status-dot {
            width: 0.75rem;
            height: 0.75rem;
            border-radius: 50%;
        }

        .status-green {
            background: #10b981;
        }

        .status-orange {
            background: #f59e0b;
        }

        .status-blue {
            background: #3b82f6;
        }

        .activity-content {
            flex: 1;
            min-width: 0;
        }

        .activity-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 0.25rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .dark .activity-title {
            color: #f9fafb;
        }

        .activity-meta {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .status-badge {
            display: inline-block;
            padding: 0.125rem 0.5rem;
            border-radius: 6px;
            font-size: 0.6875rem;
            font-weight: 600;
        }

        .status-badge.status-approved {
            background: #d1fae5;
            color: #065f46;
        }

        .dark .status-badge.status-approved {
            background: #064e3b;
            color: #6ee7b7;
        }

        .status-badge.status-review_required {
            background: #fef3c7;
            color: #92400e;
        }

        .dark .status-badge.status-review_required {
            background: #78350f;
            color: #fcd34d;
        }

        .status-badge.status-pending {
            background: #dbeafe;
            color: #1e40af;
        }

        .dark .status-badge.status-pending {
            background: #1e3a8a;
            color: #93c5fd;
        }

        .activity-time {
            font-size: 0.75rem;
            color: #9ca3af;
        }

        .activity-link {
            flex-shrink: 0;
            color: #9ca3af;
            transition: all 0.2s;
        }

        .activity-link svg {
            width: 1.25rem;
            height: 1.25rem;
        }

        .activity-link:hover {
            color: #3b82f6;
        }

        /* Empty State */
        .empty-state {
            padding: 3rem 1.5rem;
            text-align: center;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
        }

        .dark .empty-state {
            background: #1f2937;
            border-color: #374151;
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.6;
        }

        .empty-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 0.5rem;
        }

        .dark .empty-title {
            color: #f9fafb;
        }

        .empty-desc {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 1.5rem;
        }

        .dark .empty-desc {
            color: #9ca3af;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            padding: 0.625rem 1.25rem;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        /* Widgets Section */
        .widgets-section {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            margin-top: 2rem;
        }

        @media (min-width: 768px) {
            .widgets-section {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                align-items: stretch;
            }

            .btn-upload {
                justify-content: center;
            }

            .main-greeting {
                font-size: 1.5rem;
            }
        }
    </style>
</x-filament-panels::page>

