<x-filament-panels::page>
    <div class="client-dashboard-container">
        {{-- Client Selector --}}
        <div class="client-selector-section mb-6">
            {{ $this->form }}
        </div>

        @php
            $client = $this->getClientProperty();
        @endphp

        @if($client)
            @php
                $stats = $this->getQuickStats();
                $actions = $this->getActionItems();
                $financial = $this->getFinancialSnapshot();
                $recentDocs = $this->getRecentDocuments();
                $deadlines = $this->getUpcomingDeadlines();
                $communication = $this->getRecentCommunication();
            @endphp

            {{-- Quick Stats Cards --}}
            <div class="stats-grid mb-6">
                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-icon">📄</span>
                        <span class="stat-label">Documenten</span>
                    </div>
                    <div class="stat-value">{{ $stats['total_documents'] ?? 0 }}</div>
                    <div class="stat-description">
                        {{ $stats['approved_documents'] ?? 0 }} goedgekeurd
                        @if(($stats['pending_documents'] ?? 0) > 0)
                            · <span class="text-warning-600 dark:text-warning-400">{{ $stats['pending_documents'] }} in behandeling</span>
                        @endif
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-icon">📊</span>
                        <span class="stat-label">BTW Status</span>
                    </div>
                    <div class="stat-value">€{{ number_format($stats['btw_verschuldigd'] ?? 0, 2, ',', '.') }}</div>
                    <div class="stat-description">{{ $stats['current_period'] ?? 'Geen periode' }}</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-icon">✅</span>
                        <span class="stat-label">Open Taken</span>
                    </div>
                    <div class="stat-value">{{ $stats['open_tasks'] ?? 0 }}</div>
                    <div class="stat-description">Taken die aandacht vereisen</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <span class="stat-icon">🕐</span>
                        <span class="stat-label">Laatste Activiteit</span>
                    </div>
                    <div class="stat-value">{{ $stats['last_activity'] ?? 'Geen' }}</div>
                    <div class="stat-description">Laatste document update</div>
                </div>
            </div>

            {{-- Action Items --}}
            @if(!empty($actions))
            <div class="action-items-section mb-6">
                <h2 class="section-title">
                    <span class="title-icon">🚨</span>
                    <span>Actie Items</span>
                </h2>
                <div class="action-items-grid">
                    @foreach($actions as $action)
                    <div class="action-item action-item-{{ $action['type'] }}">
                        <div class="action-header">
                            <span class="action-icon">{{ $action['icon'] }}</span>
                            <span class="action-title">{{ $action['title'] }}</span>
                        </div>
                        <div class="action-description">{{ $action['description'] }}</div>
                        <div class="action-footer">
                            <x-filament::button 
                                size="sm" 
                                color="{{ $action['type'] }}"
                                wire:click="$dispatch('open-modal', { id: '{{ $action['action'] }}' })"
                            >
                                Actie Ondernemen
                            </x-filament::button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Financial Snapshot --}}
            <div class="financial-section mb-6">
                <h2 class="section-title">
                    <span class="title-icon">💰</span>
                    <span>Financial Snapshot</span>
                </h2>
                <div class="financial-grid">
                    <div class="financial-card">
                        <div class="financial-label">Deze Maand</div>
                        <div class="financial-value">€{{ number_format($financial['this_month']['total'] ?? 0, 2, ',', '.') }}</div>
                        <div class="financial-meta">
                            {{ $financial['this_month']['count'] ?? 0 }} document(en)
                            @if(isset($financial['change_percentage']))
                                @php $change = $financial['change_percentage']; @endphp
                                <span class="{{ $change >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                                    {{ $change >= 0 ? '↑' : '↓' }} {{ number_format(abs($change), 1) }}% vs vorige maand
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="financial-card">
                        <div class="financial-label">BTW Aansprakelijkheid</div>
                        <div class="financial-value {{ ($financial['btw_liability'] ?? 0) > 0 ? 'text-warning-600' : 'text-success-600' }}">
                            €{{ number_format(abs($financial['btw_liability'] ?? 0), 2, ',', '.') }}
                        </div>
                        <div class="financial-meta">
                            {{ ($financial['btw_liability'] ?? 0) > 0 ? 'Te betalen' : 'Terug te ontvangen' }}
                        </div>
                    </div>
                </div>

                @if(!empty($financial['top_suppliers']))
                <div class="suppliers-section mt-4">
                    <h3 class="subsection-title">Top Leveranciers</h3>
                    <div class="suppliers-list">
                        @foreach($financial['top_suppliers'] as $supplier)
                        <div class="supplier-item">
                            <span class="supplier-name">{{ $supplier['name'] }}</span>
                            <span class="supplier-amount">€{{ number_format($supplier['total'], 2, ',', '.') }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- Recent Documents & Deadlines Side by Side --}}
            <div class="recent-deadlines-grid mb-6">
                <div class="recent-documents-section">
                    <h2 class="section-title">
                        <span class="title-icon">📄</span>
                        <span>Recente Documenten</span>
                    </h2>
                    <div class="documents-list">
                        @forelse($recentDocs as $doc)
                        <div class="document-item">
                            <div class="document-info">
                                <div class="document-name">{{ $doc['filename'] }}</div>
                                <div class="document-meta">
                                    <span class="badge badge-{{ $doc['status'] }}">{{ $doc['status'] }}</span>
                                    <span>€{{ number_format($doc['amount'] ?? 0, 2, ',', '.') }}</span>
                                    <span>{{ $doc['date'] }}</span>
                                </div>
                            </div>
                            <div class="document-actions">
                                <x-filament::button 
                                    size="xs" 
                                    color="primary"
                                    tag="a"
                                    href="{{ route('filament.admin.pages.document-review', ['document' => $doc['id']]) }}"
                                >
                                    Bekijk
                                </x-filament::button>
                            </div>
                        </div>
                        @empty
                        <div class="empty-state">Geen recente documenten</div>
                        @endforelse
                    </div>
                </div>

                <div class="deadlines-section">
                    <h2 class="section-title">
                        <span class="title-icon">📅</span>
                        <span>Aankomende Deadlines</span>
                    </h2>
                    <div class="deadlines-list">
                        @forelse($deadlines as $deadline)
                        <div class="deadline-item deadline-{{ $deadline['days_remaining'] <= 7 ? 'urgent' : 'normal' }}">
                            <div class="deadline-info">
                                <div class="deadline-title">{{ $deadline['title'] }}</div>
                                <div class="deadline-meta">
                                    <span>{{ $deadline['deadline'] }}</span>
                                    <span class="deadline-days">
                                        {{ $deadline['days_remaining'] }} dagen
                                    </span>
                                </div>
                            </div>
                            <div class="deadline-action">
                                <x-filament::button 
                                    size="xs" 
                                    color="{{ $deadline['days_remaining'] <= 7 ? 'danger' : 'primary' }}"
                                    tag="a"
                                    href="{{ $deadline['url'] }}"
                                >
                                    Open
                                </x-filament::button>
                            </div>
                        </div>
                        @empty
                        <div class="empty-state">Geen aankomende deadlines</div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Recent Communication --}}
            @if(!empty($communication))
            <div class="communication-section">
                <h2 class="section-title">
                    <span class="title-icon">💬</span>
                    <span>Recente Communicatie</span>
                </h2>
                <div class="communication-list">
                    @foreach($communication as $comm)
                    <div class="communication-item">
                        <div class="comm-type">{{ $comm['type'] }}</div>
                        <div class="comm-description">{{ $comm['description'] }}</div>
                        <div class="comm-meta">{{ $comm['created_at'] }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        @else
            <div class="empty-state-large">
                <div class="empty-icon">👤</div>
                <div class="empty-title">Selecteer een klant</div>
                <div class="empty-description">Kies een klant uit de dropdown hierboven om het dashboard te bekijken</div>
            </div>
        @endif
    </div>

    <style>
        .client-dashboard-container {
            max-width: 100%;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .stat-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 1.5rem;
            transition: all 0.2s;
        }

        .dark .stat-card {
            background: #1f2937;
            border-color: #374151;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .stat-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .stat-icon {
            font-size: 1.5rem;
        }

        .stat-label {
            font-size: 0.875rem;
            color: #6b7280;
            font-weight: 500;
        }

        .dark .stat-label {
            color: #9ca3af;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 0.5rem;
        }

        .dark .stat-value {
            color: #f9fafb;
        }

        .stat-description {
            font-size: 0.875rem;
            color: #6b7280;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: #111827;
        }

        .dark .section-title {
            color: #f9fafb;
        }

        .title-icon {
            font-size: 1.5rem;
        }

        .action-items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1rem;
        }

        .action-item {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 1.25rem;
            transition: all 0.2s;
        }

        .dark .action-item {
            background: #1f2937;
            border-color: #374151;
        }

        .action-item-warning {
            border-left: 4px solid #f59e0b;
        }

        .action-item-danger {
            border-left: 4px solid #ef4444;
        }

        .action-item-info {
            border-left: 4px solid #3b82f6;
        }

        .action-item-success {
            border-left: 4px solid #10b981;
        }

        .action-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .action-icon {
            font-size: 1.25rem;
        }

        .action-title {
            font-weight: 600;
            font-size: 1rem;
        }

        .action-description {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 1rem;
        }

        .financial-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .financial-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 1.5rem;
        }

        .dark .financial-card {
            background: #1f2937;
            border-color: #374151;
        }

        .financial-label {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 0.5rem;
        }

        .financial-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .recent-deadlines-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        @media (max-width: 1024px) {
            .recent-deadlines-grid {
                grid-template-columns: 1fr;
            }
        }

        .documents-list, .deadlines-list, .communication-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .document-item, .deadline-item, .communication-item {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .dark .document-item, .dark .deadline-item, .dark .communication-item {
            background: #1f2937;
            border-color: #374151;
        }

        .deadline-urgent {
            border-left: 4px solid #ef4444;
        }

        .empty-state-large {
            text-align: center;
            padding: 4rem 2rem;
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .empty-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .empty-description {
            color: #6b7280;
        }
    </style>
</x-filament-panels::page>

