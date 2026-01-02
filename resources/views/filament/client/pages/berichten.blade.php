<x-filament-panels::page>
    <div class="berichten-container">
        {{-- Enhanced Summary Cards --}}
        @php
            $clientId = auth()->user()->client_id;
            $totalTasks = \App\Models\Task::where('client_id', $clientId)->count();
            $openTasks = \App\Models\Task::where('client_id', $clientId)->where('status', 'open')->count();
            $unreadTasks = \App\Models\Task::where('client_id', $clientId)->whereNull('read_at')->count();
            $urgentTasks = \App\Models\Task::where('client_id', $clientId)
                ->where('status', 'open')
                ->whereNotNull('deadline')
                ->where('deadline', '<=', now()->addDays(3))
                ->count();
            $overdueTasks = \App\Models\Task::where('client_id', $clientId)
                ->where('status', 'open')
                ->whereNotNull('deadline')
                ->where('deadline', '<', now())
                ->count();
            $repliedTasks = \App\Models\Task::where('client_id', $clientId)
                ->whereNotNull('client_reply')
                ->count();
        @endphp

        {{-- Modern Header Section --}}
        <div class="page-header">
            <div class="header-content">
                <div class="header-main">
                    <div class="header-icon-wrapper">
                        <span class="header-icon">💬</span>
                    </div>
                    <div class="header-text">
                        <h1 class="page-title">Berichten</h1>
                        <div class="header-stats">
                            @if($unreadTasks > 0)
                            <span class="stat-badge stat-new">{{ $unreadTasks }} nieuw</span>
                            @endif
                            @if($openTasks > 0)
                            <span class="stat-badge stat-open">{{ $openTasks }} open</span>
                            @endif
                        </div>
                    </div>
                </div>
                <p class="page-subtitle">Communicatie, taken en vragen van uw boekhouder</p>
            </div>
        </div>

        {{-- Tabs Navigation --}}
        @php
            $adminRepliesCount = \App\Models\Task::where('client_id', auth()->user()->client_id)
                ->whereNotNull('admin_reply')
                ->count();
        @endphp
        <div class="tabs-navigation">
            <button 
                type="button"
                wire:click="setActiveTab('all')"
                class="tab-button {{ $activeTab === 'all' ? 'active' : '' }}"
            >
                <span class="tab-icon">📋</span>
                <span class="tab-label">Alle Berichten</span>
            </button>
            <button 
                type="button"
                wire:click="setActiveTab('unread')"
                class="tab-button {{ $activeTab === 'unread' ? 'active' : '' }}"
            >
                <span class="tab-icon">📬</span>
                <span class="tab-label">Ongelezen</span>
                @if($unreadTasks > 0)
                <span class="tab-badge">{{ $unreadTasks }}</span>
                @endif
            </button>
            <button 
                type="button"
                wire:click="setActiveTab('replied')"
                class="tab-button {{ $activeTab === 'replied' ? 'active' : '' }}"
            >
                <span class="tab-icon">💬</span>
                <span class="tab-label">Beantwoord</span>
            </button>
            <button 
                type="button"
                wire:click="setActiveTab('admin_replies')"
                class="tab-button {{ $activeTab === 'admin_replies' ? 'active' : '' }}"
            >
                <span class="tab-icon">✉️</span>
                <span class="tab-label">Antwoord Ontvangen</span>
                @if($adminRepliesCount > 0)
                <span class="tab-badge">{{ $adminRepliesCount }}</span>
                @endif
            </button>
        </div>

        {{-- Enhanced Summary Cards Grid --}}
        <div class="summary-cards-grid">
            <div class="summary-card card-primary {{ $unreadTasks > 0 ? 'has-unread' : '' }}">
                <div class="card-icon-wrapper">
                    <div class="card-icon">📬</div>
                </div>
                <div class="card-content">
                    <div class="card-label">Totaal</div>
                    <div class="card-value">{{ $totalTasks }}</div>
                    @if($unreadTasks > 0)
                    <div class="card-badge badge-primary">{{ $unreadTasks }} nieuw</div>
                    @endif
                </div>
            </div>

            <div class="summary-card card-warning {{ $openTasks > 0 ? 'has-alerts' : '' }}">
                <div class="card-icon-wrapper">
                    <div class="card-icon">📋</div>
                </div>
                <div class="card-content">
                    <div class="card-label">Open</div>
                    <div class="card-value">{{ $openTasks }}</div>
                </div>
            </div>

            @if($urgentTasks > 0 || $overdueTasks > 0)
            <div class="summary-card card-danger urgent">
                <div class="card-icon-wrapper">
                    <div class="card-icon">⚠️</div>
                </div>
                <div class="card-content">
                    <div class="card-label">{{ $overdueTasks > 0 ? 'Verlopen' : 'Urgent' }}</div>
                    <div class="card-value">{{ $overdueTasks > 0 ? $overdueTasks : $urgentTasks }}</div>
                </div>
            </div>
            @endif

            @if($repliedTasks > 0)
            <div class="summary-card card-success replied">
                <div class="card-icon-wrapper">
                    <div class="card-icon">💬</div>
                </div>
                <div class="card-content">
                    <div class="card-label">Beantwoord</div>
                    <div class="card-value">{{ $repliedTasks }}</div>
                </div>
            </div>
            @endif
        </div>

        {{-- Modern Quick Actions Bar --}}
        <div class="quick-actions-section">
            <div class="actions-header">
                <h3 class="actions-title">Snelle Acties</h3>
            </div>
            <div class="quick-actions-grid">
                <button 
                    type="button"
                    wire:click="$dispatch('mark-all-read')"
                    class="action-btn action-primary"
                >
                    <span class="action-icon">📬</span>
                    <span class="action-text">Alles Gelezen</span>
                </button>
                <button 
                    type="button"
                    wire:click="$set('tableFilters.status.value', ['open'])"
                    class="action-btn action-warning"
                >
                    <span class="action-icon">📋</span>
                    <span class="action-text">Alleen Open</span>
                </button>
                <button 
                    type="button"
                    wire:click="$set('tableFilters.unread.value', true)"
                    class="action-btn action-info"
                >
                    <span class="action-icon">🔵</span>
                    <span class="action-text">Alleen Ongelezen</span>
                </button>
            </div>
        </div>

        {{-- Modern Card-Based Messages View --}}
        <div class="messages-container">
            @php
                $tasks = $this->getTasksProperty();
            @endphp

            @if($tasks->count() > 0)
                <div class="messages-grid">
                    @foreach($tasks as $task)
                        <div class="message-card {{ $task->isUnread() ? 'unread' : '' }} {{ $task->priority === 'urgent' ? 'urgent' : '' }}">
                            {{-- Unread Indicator --}}
                            @if($task->isUnread())
                            <div class="unread-indicator"></div>
                            @endif

                            {{-- Card Header --}}
                            <div class="message-header">
                                <div class="message-type-badge type-{{ $task->type }}">
                                    @switch($task->type)
                                        @case('missing_document')
                                            📋 Document
                                            @break
                                        @case('clarification')
                                            ❓ Vraag
                                            @break
                                        @case('approval')
                                            ✅ Goedkeuring
                                            @break
                                        @case('info')
                                            ℹ️ Info
                                            @break
                                        @case('reminder')
                                            ⏰ Herinnering
                                            @break
                                        @default
                                            {{ ucfirst($task->type) }}
                                    @endswitch
                                </div>
                                
                                <div class="message-meta">
                                    @if($task->priority && $task->priority !== 'normal')
                                    <span class="priority-badge priority-{{ $task->priority }}">
                                        @if($task->priority === 'urgent')
                                            ⚠️ Urgent
                                        @elseif($task->priority === 'high')
                                            Hoog
                                        @else
                                            {{ ucfirst($task->priority) }}
                                        @endif
                                    </span>
                                    @endif
                                    <span class="time-ago">{{ $task->created_at->diffForHumans() }}</span>
                                </div>
                            </div>

                            {{-- Message Content --}}
                            <div class="message-body">
                                <p class="message-text">{{ Str::limit(strip_tags($task->description), 120) }}</p>
                                
                                {{-- Admin Reply Preview --}}
                                @if($task->hasAdminReply())
                                <div class="admin-reply-preview">
                                    <div class="admin-reply-header">
                                        <span class="admin-reply-icon">✉️</span>
                                        <span class="admin-reply-label">Antwoord van Boekhouder:</span>
                                        <span class="admin-reply-time">{{ $task->admin_replied_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="admin-reply-content">
                                        {{ Str::limit(strip_tags($task->admin_reply), 150) }}
                                    </div>
                                </div>
                                @endif
                            </div>

                            {{-- Message Footer --}}
                            <div class="message-footer">
                                <div class="message-info">
                                    @if($task->deadline)
                                        @php
                                            $daysUntil = now()->diffInDays($task->deadline, false);
                                        @endphp
                                        <div class="deadline-info {{ $daysUntil < 0 ? 'overdue' : ($daysUntil <= 3 ? 'urgent' : '') }}">
                                            <span class="deadline-icon">📅</span>
                                            <span class="deadline-text">
                                                @if($daysUntil < 0)
                                                    {{ abs($daysUntil) }} dag(en) te laat
                                                @elseif($daysUntil <= 3)
                                                    {{ $daysUntil }} dag(en) resterend
                                                @else
                                                    {{ $task->deadline->format('d-m-Y') }}
                                                @endif
                                            </span>
                                        </div>
                                    @endif

                                    @if($task->hasReply())
                                    <div class="reply-indicator">
                                        <span class="reply-icon">💬</span>
                                        <span class="reply-text">Beantwoord</span>
                                    </div>
                                    @endif

                                    @if($task->hasAdminReply())
                                    <div class="reply-indicator admin-reply-indicator">
                                        <span class="reply-icon">✉️</span>
                                        <span class="reply-text">Antwoord Ontvangen</span>
                                    </div>
                                    @endif

                                    @php
                                        $docCount = $this->getRelatedDocumentsCount($task);
                                    @endphp
                                    @if($docCount > 0)
                                    <div class="documents-indicator">
                                        <span class="doc-icon">📄</span>
                                        <span class="doc-text">{{ $docCount }} doc.</span>
                                    </div>
                                    @endif
                                </div>

                                <div class="message-status">
                                    <span class="status-badge status-{{ $task->status }}">
                                        {{ $task->status === 'open' ? 'Open' : ($task->status === 'resolved' ? '✅ Afgehandeld' : 'Gesloten') }}
                                    </span>
                                </div>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="message-actions">
                                <button 
                                    type="button"
                                    wire:click="mountTableAction('view', {{ $task->id }})"
                                    class="action-button action-view"
                                >
                                    <span class="action-icon">👁️</span>
                                    <span>Bekijk</span>
                                </button>

                                @if($task->status === 'open')
                                <button 
                                    type="button"
                                    wire:click="mountTableAction('reply', {{ $task->id }})"
                                    class="action-button action-reply"
                                >
                                    <span class="action-icon">💬</span>
                                    <span>Beantwoorden</span>
                                </button>
                                @endif

                                @if($task->isUnread())
                                <button 
                                    type="button"
                                    wire:click="markAsRead({{ $task->id }})"
                                    class="action-button action-mark-read"
                                >
                                    <span class="action-icon">✓</span>
                                    <span>Gelezen</span>
                                </button>
                                @endif

                                @if($task->status === 'open')
                                <button 
                                    type="button"
                                    wire:click="resolveTask({{ $task->id }})"
                                    wire:confirm="Weet u zeker dat u deze taak als afgehandeld wilt markeren?"
                                    class="action-button action-resolve"
                                >
                                    <span class="action-icon">✅</span>
                                    <span>Afhandelen</span>
                                </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                @if($tasks->hasPages())
                <div class="pagination-wrapper">
                    {{ $tasks->links() }}
                </div>
                @endif
            @else
                <div class="empty-state">
                    <div class="empty-icon">💬</div>
                    <h3 class="empty-title">Geen berichten</h3>
                    <p class="empty-description">U heeft nog geen berichten ontvangen van uw boekhouder.</p>
                </div>
            @endif
        </div>
    </div>

    <style>
        .berichten-container {
            max-width: 100%;
            padding: 0;
        }

        /* Modern Header */
        .page-header {
            margin-bottom: 1.25rem;
            padding: 1rem 1.25rem;
            background: white;
            border-radius: 0.625rem;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .dark .page-header {
            background: #1f2937;
            border-color: #374151;
        }

        .header-content {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .header-main {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-icon-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 0.625rem;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.2);
        }

        .header-icon {
            font-size: 1.375rem;
            filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.1));
        }

        .header-text {
            flex: 1;
            min-width: 0;
        }

        .page-title {
            font-size: 1.375rem;
            font-weight: 700;
            color: #111827;
            margin: 0 0 0.375rem 0;
            line-height: 1.3;
        }

        .dark .page-title {
            color: #f9fafb;
        }

        .header-stats {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .stat-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.625rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .stat-new {
            background: #dbeafe;
            color: #1e40af;
        }

        .dark .stat-new {
            background: #1e3a8a;
            color: #93c5fd;
        }

        .stat-open {
            background: #fef3c7;
            color: #92400e;
        }

        .dark .stat-open {
            background: #78350f;
            color: #fde68a;
        }

        .page-subtitle {
            font-size: 0.8125rem;
            color: #6b7280;
            margin: 0;
            line-height: 1.5;
        }

        .dark .page-subtitle {
            color: #9ca3af;
        }

        /* Tabs Navigation */
        .tabs-navigation {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 0;
            overflow-x: auto;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .tabs-navigation::-webkit-scrollbar {
            display: none;
        }

        .dark .tabs-navigation {
            border-bottom-color: #374151;
        }

        .tab-button {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            background: transparent;
            border: none;
            border-bottom: 3px solid transparent;
            color: #6b7280;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
            bottom: -2px;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .dark .tab-button {
            color: #9ca3af;
        }

        .tab-button:hover {
            color: #3b82f6;
            background: #f3f4f6;
        }

        .dark .tab-button:hover {
            background: #374151;
            color: #60a5fa;
        }

        .tab-button.active {
            color: #3b82f6;
            border-bottom-color: #3b82f6;
            font-weight: 600;
        }

        .dark .tab-button.active {
            color: #60a5fa;
            border-bottom-color: #60a5fa;
        }

        .tab-icon {
            font-size: 1.125rem;
        }

        .tab-label {
            font-size: 0.9375rem;
        }

        .tab-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 1.25rem;
            height: 1.25rem;
            padding: 0 0.375rem;
            background: #ef4444;
            color: white;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
            margin-left: 0.25rem;
        }

        /* Enhanced Summary Cards */
        .summary-cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 0.875rem;
            margin-bottom: 1.5rem;
        }

        .summary-card {
            position: relative;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.625rem;
            padding: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.625rem;
            transition: all 0.2s;
            overflow: hidden;
        }

        .dark .summary-card {
            background: #1f2937;
            border-color: #374151;
        }

        .summary-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .card-primary {
            color: #3b82f6;
        }

        .card-primary.has-unread {
            border-left: 3px solid #3b82f6;
            background: linear-gradient(90deg, #eff6ff 0%, #ffffff 100%);
        }

        .dark .card-primary.has-unread {
            background: linear-gradient(90deg, #1e3a8a 0%, #1f2937 100%);
        }

        .card-warning {
            color: #f59e0b;
        }

        .card-warning.has-alerts {
            border-left: 3px solid #f59e0b;
        }

        .card-danger {
            color: #ef4444;
        }

        .card-danger.urgent {
            border-left: 3px solid #ef4444;
        }

        .card-success {
            color: #10b981;
        }

        .card-success.replied {
            border-left: 3px solid #10b981;
        }

        .card-icon-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 0.5rem;
            background: linear-gradient(135deg, currentColor 0%, color-mix(in srgb, currentColor 80%, transparent) 100%);
            opacity: 0.1;
            flex-shrink: 0;
        }

        .card-icon {
            font-size: 1.25rem;
        }

        .card-content {
            flex: 1;
            min-width: 0;
        }

        .card-label {
            font-size: 0.75rem;
            font-weight: 500;
            color: #6b7280;
            margin-bottom: 0.25rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .dark .card-label {
            color: #9ca3af;
        }

        .card-value {
            font-size: 1.125rem;
            font-weight: 700;
            color: #111827;
            line-height: 1.2;
        }

        .dark .card-value {
            color: #f9fafb;
        }

        .card-badge {
            display: inline-block;
            margin-top: 0.25rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.625rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .badge-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }

        /* Quick Actions Section */
        .quick-actions-section {
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: linear-gradient(135deg, #f9fafb 0%, #ffffff 100%);
            border-radius: 0.75rem;
            border: 1px solid #e5e7eb;
        }

        .dark .quick-actions-section {
            background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
            border-color: #374151;
        }

        .actions-header {
            margin-bottom: 0.75rem;
        }

        .actions-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: #111827;
            margin: 0;
        }

        .dark .actions-title {
            color: #f9fafb;
        }

        .quick-actions-grid {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.5rem 0.875rem;
            border-radius: 0.5rem;
            font-size: 0.8125rem;
            font-weight: 500;
            border: 1px solid;
            transition: all 0.2s;
            cursor: pointer;
            background: white;
        }

        .dark .action-btn {
            background: #1f2937;
        }

        .action-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .action-primary {
            color: #3b82f6;
            border-color: #3b82f6;
        }

        .action-primary:hover {
            background: #3b82f6;
            color: white;
        }

        .action-warning {
            color: #f59e0b;
            border-color: #f59e0b;
        }

        .action-warning:hover {
            background: #f59e0b;
            color: white;
        }

        .action-info {
            color: #06b6d4;
            border-color: #06b6d4;
        }

        .action-info:hover {
            background: #06b6d4;
            color: white;
        }

        .action-icon {
            font-size: 1rem;
        }

        /* Messages Container */
        .messages-container {
            margin-top: 1.5rem;
        }

        .messages-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        /* Message Card */
        .message-card {
            position: relative;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 1.25rem;
            transition: all 0.2s;
            overflow: hidden;
        }

        .dark .message-card {
            background: #1f2937;
            border-color: #374151;
        }

        .message-card.unread {
            border-left: 3px solid #3b82f6;
            background: linear-gradient(90deg, #eff6ff 0%, #ffffff 100%);
        }

        .dark .message-card.unread {
            background: linear-gradient(90deg, #1e3a8a 0%, #1f2937 100%);
        }

        .message-card.urgent {
            border-left: 3px solid #ef4444;
        }

        .message-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .unread-indicator {
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: 8px;
            height: 8px;
            background: #3b82f6;
            border-radius: 50%;
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.75rem;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .message-type-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.375rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .type-missing_document {
            background: #fef3c7;
            color: #92400e;
        }

        .dark .type-missing_document {
            background: #78350f;
            color: #fde68a;
        }

        .type-clarification {
            background: #dbeafe;
            color: #1e40af;
        }

        .dark .type-clarification {
            background: #1e3a8a;
            color: #93c5fd;
        }

        .message-meta {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .priority-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.6875rem;
            font-weight: 600;
        }

        .priority-urgent {
            background: #fee2e2;
            color: #991b1b;
        }

        .dark .priority-urgent {
            background: #7f1d1d;
            color: #fca5a5;
        }

        .priority-high {
            background: #fef3c7;
            color: #92400e;
        }

        .time-ago {
            font-size: 0.75rem;
            color: #6b7280;
        }

        .dark .time-ago {
            color: #9ca3af;
        }

        .message-body {
            margin-bottom: 0.75rem;
        }

        .message-text {
            color: #111827;
            line-height: 1.5;
            margin: 0;
            font-size: 0.875rem;
        }

        .dark .message-text {
            color: #f9fafb;
        }

        /* Admin Reply Preview */
        .admin-reply-preview {
            margin-top: 1rem;
            padding: 0.875rem;
            background: #fef3c7;
            border-left: 3px solid #f59e0b;
            border-radius: 0.5rem;
        }

        .dark .admin-reply-preview {
            background: #78350f;
            border-left-color: #fbbf24;
        }

        .admin-reply-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            font-size: 0.8125rem;
            font-weight: 600;
            color: #92400e;
        }

        .dark .admin-reply-header {
            color: #fde68a;
        }

        .admin-reply-icon {
            font-size: 1rem;
        }

        .admin-reply-label {
            flex: 1;
        }

        .admin-reply-time {
            font-size: 0.75rem;
            opacity: 0.8;
        }

        .admin-reply-content {
            color: #111827;
            line-height: 1.5;
            font-size: 0.875rem;
        }

        .dark .admin-reply-content {
            color: #f9fafb;
        }

        .message-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
            padding-top: 0.75rem;
            border-top: 1px solid #e5e7eb;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .dark .message-footer {
            border-top-color: #374151;
        }

        .message-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .deadline-info {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            font-size: 0.75rem;
        }

        .deadline-info.overdue {
            color: #dc2626;
            font-weight: 600;
        }

        .deadline-info.urgent {
            color: #d97706;
            font-weight: 600;
        }

        .reply-indicator,
        .documents-indicator {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: #6b7280;
        }

        .reply-indicator {
            background: #d1fae5;
            color: #065f46;
        }

        .dark .reply-indicator {
            background: #064e3b;
            color: #6ee7b7;
        }

        .admin-reply-indicator {
            background: #fef3c7;
            color: #92400e;
            animation: pulse 2s infinite;
        }

        .dark .admin-reply-indicator {
            background: #78350f;
            color: #fde68a;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.7;
            }
        }

        .dark .documents-indicator {
            color: #9ca3af;
        }

        .status-badge {
            padding: 0.25rem 0.625rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .status-open {
            background: #fef3c7;
            color: #92400e;
        }

        .status-resolved {
            background: #d1fae5;
            color: #065f46;
        }

        .dark .status-resolved {
            background: #064e3b;
            color: #6ee7b7;
        }

        .message-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .action-button {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.5rem 0.875rem;
            border-radius: 0.5rem;
            font-size: 0.8125rem;
            font-weight: 500;
            border: 1px solid #e5e7eb;
            background: white;
            color: #111827;
            cursor: pointer;
            transition: all 0.2s;
        }

        .dark .action-button {
            background: #1f2937;
            border-color: #374151;
            color: #f9fafb;
        }

        .action-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .action-view {
            border-color: #3b82f6;
            color: #3b82f6;
        }

        .action-view:hover {
            background: #3b82f6;
            color: white;
        }

        .action-reply {
            border-color: #06b6d4;
            color: #06b6d4;
        }

        .action-reply:hover {
            background: #06b6d4;
            color: white;
        }

        .action-resolve {
            border-color: #10b981;
            color: #10b981;
        }

        .action-resolve:hover {
            background: #10b981;
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1.5rem;
        }

        .empty-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .empty-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #111827;
            margin: 0 0 0.5rem 0;
        }

        .dark .empty-title {
            color: #f9fafb;
        }

        .empty-description {
            color: #6b7280;
            margin: 0;
            font-size: 0.875rem;
        }

        .dark .empty-description {
            color: #9ca3af;
        }

        .pagination-wrapper {
            margin-top: 1.5rem;
            display: flex;
            justify-content: center;
        }

        /* Responsive Design */
        @media (min-width: 640px) {
            .summary-cards-grid {
                grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
                gap: 1.25rem;
            }

            .summary-card {
                padding: 1.25rem;
            }

            .card-value {
                font-size: 1.5rem;
            }

            .messages-grid {
                gap: 1.25rem;
            }

            .message-card {
                padding: 1.5rem;
            }
        }

        @media (min-width: 1024px) {
            .page-title {
                font-size: 1.75rem;
            }

            .title-icon {
                font-size: 2rem;
            }

            .summary-cards-grid {
                grid-template-columns: repeat(4, 1fr);
            }

            .messages-grid {
                gap: 1.5rem;
            }
        }

        @media (max-width: 640px) {
            .page-title {
                font-size: 1.25rem;
            }

            .title-icon {
                font-size: 1.5rem;
            }

            .summary-cards-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .quick-actions-grid {
                flex-direction: column;
            }

            .action-btn {
                width: 100%;
                justify-content: center;
            }

            .message-actions {
                flex-direction: column;
            }

            .action-button {
                width: 100%;
                justify-content: center;
            }

            .message-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('mark-all-read', () => {
                // This will be handled by a bulk action
            });
        });
    </script>
</x-filament-panels::page>
