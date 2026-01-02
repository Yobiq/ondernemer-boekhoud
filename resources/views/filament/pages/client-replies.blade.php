<x-filament-panels::page>
    <div class="client-replies-container">
        {{-- Summary Stats --}}
        @php
            $totalReplies = \App\Models\Task::whereNotNull('client_reply')->count();
            $unresolvedReplies = \App\Models\Task::whereNotNull('client_reply')->where('status', 'open')->count();
            $recentReplies = \App\Models\Task::whereNotNull('client_reply')
                ->where('replied_at', '>=', now()->subDays(7))
                ->count();
        @endphp

        <div class="stats-grid mb-6">
            <div class="stat-card stat-primary">
                <div class="stat-icon">💬</div>
                <div class="stat-content">
                    <div class="stat-label">Totaal Reacties</div>
                    <div class="stat-value">{{ $totalReplies }}</div>
                </div>
            </div>

            <div class="stat-card stat-warning {{ $unresolvedReplies > 0 ? 'has-alerts' : '' }}">
                <div class="stat-icon">📋</div>
                <div class="stat-content">
                    <div class="stat-label">Nog Open</div>
                    <div class="stat-value">{{ $unresolvedReplies }}</div>
                </div>
            </div>

            <div class="stat-card stat-info">
                <div class="stat-icon">🕐</div>
                <div class="stat-content">
                    <div class="stat-label">Laatste 7 Dagen</div>
                    <div class="stat-value">{{ $recentReplies }}</div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-wrapper">
            {{ $this->table }}
        </div>
    </div>

    <style>
        .client-replies-container {
            max-width: 100%;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
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

        .stat-card.has-alerts {
            border-left: 3px solid #f59e0b;
        }

        .stat-icon {
            font-size: 2rem;
        }

        .stat-content {
            flex: 1;
        }

        .stat-label {
            font-size: 0.75rem;
            color: #6b7280;
            margin-bottom: 0.25rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .dark .stat-label {
            color: #9ca3af;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #111827;
        }

        .dark .stat-value {
            color: #f9fafb;
        }

        .table-wrapper {
            background: white;
            border-radius: 0.75rem;
            border: 1px solid #e5e7eb;
            overflow: hidden;
        }

        .dark .table-wrapper {
            background: #1f2937;
            border-color: #374151;
        }
    </style>
</x-filament-panels::page>
