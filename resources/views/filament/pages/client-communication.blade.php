<x-filament-panels::page>
    <div class="client-communication-container">
        {{-- Tabs Navigation --}}
        <div class="tabs-navigation">
            <button 
                type="button"
                wire:click="$set('activeTab', 'send')"
                class="tab-button {{ $activeTab === 'send' ? 'active' : '' }}"
            >
                <span class="tab-icon">✉️</span>
                <span class="tab-label">Bericht Versturen</span>
            </button>
            <button 
                type="button"
                wire:click="$set('activeTab', 'replies')"
                class="tab-button {{ $activeTab === 'replies' ? 'active' : '' }}"
            >
                <span class="tab-icon">💬</span>
                <span class="tab-label">Client Reacties</span>
                @php
                    $unresolvedCount = \App\Models\Task::whereNotNull('client_reply')
                        ->where('status', 'open')
                        ->count();
                @endphp
                @if($unresolvedCount > 0)
                <span class="tab-badge">{{ $unresolvedCount }}</span>
                @endif
            </button>
        </div>

        {{-- Tab Content: Send Message --}}
        @if($activeTab === 'send')
        <div class="tab-content">
            {{-- Summary Stats --}}
            @php
                $totalTasks = \App\Models\Task::count();
                $openTasks = \App\Models\Task::where('status', 'open')->count();
                $unreadTasks = \App\Models\Task::whereNull('read_at')->count();
            @endphp

            <div class="stats-grid mb-6">
                <div class="stat-card">
                    <div class="stat-icon">📋</div>
                    <div class="stat-content">
                        <div class="stat-label">Totaal Taken</div>
                        <div class="stat-value">{{ $totalTasks }}</div>
                    </div>
                </div>

                <div class="stat-card stat-warning {{ $openTasks > 0 ? 'has-alerts' : '' }}">
                    <div class="stat-icon">📝</div>
                    <div class="stat-content">
                        <div class="stat-label">Open Taken</div>
                        <div class="stat-value">{{ $openTasks }}</div>
                    </div>
                </div>

                <div class="stat-card stat-info {{ $unreadTasks > 0 ? 'has-alerts' : '' }}">
                    <div class="stat-icon">📬</div>
                    <div class="stat-content">
                        <div class="stat-label">Ongelezen</div>
                        <div class="stat-value">{{ $unreadTasks }}</div>
                    </div>
                </div>
            </div>

            {{-- Form Card --}}
            <div class="form-card">
                <div class="form-header">
                    <h2 class="form-title">📝 Nieuw Bericht Versturen</h2>
                    <p class="form-subtitle">Stuur een bericht of taak naar een klant</p>
                </div>

                <form wire:submit="send">
                    {{ $this->form }}

                    <div class="form-actions">
                        <x-filament::button 
                            type="submit" 
                            size="lg"
                            icon="heroicon-o-paper-airplane"
                        >
                            Verstuur Bericht
                        </x-filament::button>
                    </div>
                </form>

                {{-- Quick Templates Section --}}
                <div class="templates-section">
                    <div class="templates-header">
                        <h3 class="section-title">
                            <span class="section-icon">📝</span>
                            <span>Snelle Templates</span>
                        </h3>
                        <p class="section-description">Klik op een template om het formulier automatisch in te vullen</p>
                    </div>
                    <div class="templates-grid">
                        <button 
                            type="button"
                            wire:click="$set('data.messageType', 'task')"
                            wire:click="$set('data.subject', 'Ontbrekende Documenten')"
                            wire:click="$set('data.message', 'Beste klant,\n\nWe hebben de volgende documenten nog niet ontvangen:\n\n- [Document 1]\n- [Document 2]\n\nKunt u deze zo spoedig mogelijk uploaden?\n\nMet vriendelijke groet,\nMARCOFIC')"
                            class="template-card template-task"
                        >
                            <div class="template-icon-wrapper">
                                <span class="template-icon">📋</span>
                            </div>
                            <span class="template-label">Ontbrekende Documenten</span>
                            <span class="template-hint">Taak aanmaken</span>
                        </button>

                        <button 
                            type="button"
                            wire:click="$set('data.messageType', 'reminder')"
                            wire:click="$set('data.subject', 'Herinnering: BTW Aangifte')"
                            wire:click="$set('data.message', 'Beste klant,\n\nDit is een vriendelijke herinnering dat de BTW aangifte voor [periode] binnenkort ingediend moet worden.\n\nZorg ervoor dat alle documenten zijn geüpload en goedgekeurd.\n\nMet vriendelijke groet,\nMARCOFIC')"
                            class="template-card template-reminder"
                        >
                            <div class="template-icon-wrapper">
                                <span class="template-icon">⏰</span>
                            </div>
                            <span class="template-label">BTW Herinnering</span>
                            <span class="template-hint">Herinnering</span>
                        </button>

                        <button 
                            type="button"
                            wire:click="$set('data.messageType', 'question')"
                            wire:click="$set('data.subject', 'Vraag over Document')"
                            wire:click="$set('data.message', 'Beste klant,\n\nWe hebben een vraag over het volgende document:\n\n[Document naam]\n\n[Uw vraag hier]\n\nKunt u hierop reageren?\n\nMet vriendelijke groet,\nMARCOFIC')"
                            class="template-card template-question"
                        >
                            <div class="template-icon-wrapper">
                                <span class="template-icon">❓</span>
                            </div>
                            <span class="template-label">Document Vraag</span>
                            <span class="template-hint">Vraag stellen</span>
                        </button>

                        <button 
                            type="button"
                            wire:click="$set('data.messageType', 'info')"
                            wire:click="$set('data.subject', 'Informatie')"
                            wire:click="$set('data.message', 'Beste klant,\n\n[Uw informatie hier]\n\nMet vriendelijke groet,\nMARCOFIC')"
                            class="template-card template-info"
                        >
                            <div class="template-icon-wrapper">
                                <span class="template-icon">ℹ️</span>
                            </div>
                            <span class="template-label">Algemene Informatie</span>
                            <span class="template-hint">Informatie delen</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Tab Content: Client Replies --}}
        @if($activeTab === 'replies')
        <div class="tab-content">
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
        @endif
    </div>

    <style>
        .client-communication-container {
            max-width: 100%;
        }

        /* Tabs Navigation */
        .tabs-navigation {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 0;
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
        }

        /* Tab Content */
        .tab-content {
            animation: fadeIn 0.2s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(4px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
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

        .stat-card.stat-primary {
            border-left: 3px solid #3b82f6;
        }

        .stat-card.stat-warning {
            border-left: 3px solid #f59e0b;
        }

        .stat-card.stat-info {
            border-left: 3px solid #06b6d4;
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

        /* Form Card */
        .form-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 1.5rem;
        }

        .dark .form-card {
            background: #1f2937;
            border-color: #374151;
        }

        .form-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .dark .form-header {
            border-bottom-color: #374151;
        }

        .form-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #111827;
            margin: 0 0 0.25rem 0;
        }

        .dark .form-title {
            color: #f9fafb;
        }

        .form-subtitle {
            font-size: 0.875rem;
            color: #6b7280;
            margin: 0;
        }

        .dark .form-subtitle {
            color: #9ca3af;
        }

        .form-actions {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
        }

        .dark .form-actions {
            border-top-color: #374151;
        }

        /* Table Wrapper */
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

        /* Templates Section */
        .templates-section {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e5e7eb;
        }

        .dark .templates-section {
            border-color: #374151;
        }

        .templates-header {
            margin-bottom: 1.5rem;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.25rem;
            font-weight: 700;
            color: #111827;
            margin: 0 0 0.5rem 0;
        }

        .dark .section-title {
            color: #f9fafb;
        }

        .section-icon {
            font-size: 1.5rem;
        }

        .section-description {
            font-size: 0.875rem;
            color: #6b7280;
            margin: 0;
        }

        .dark .section-description {
            color: #9ca3af;
        }

        .templates-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .template-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            padding: 1.5rem 1rem;
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 0.75rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .dark .template-card {
            background: #1f2937;
            border-color: #374151;
        }

        .template-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-color: currentColor;
        }

        .template-task {
            color: #f59e0b;
        }

        .template-reminder {
            color: #ef4444;
        }

        .template-question {
            color: #3b82f6;
        }

        .template-info {
            color: #10b981;
        }

        .template-icon-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 3rem;
            height: 3rem;
            border-radius: 0.75rem;
            background: linear-gradient(135deg, currentColor 0%, color-mix(in srgb, currentColor 80%, transparent) 100%);
            opacity: 0.1;
        }

        .template-icon {
            font-size: 1.75rem;
        }

        .template-label {
            font-weight: 600;
            font-size: 0.9375rem;
            color: #111827;
            text-align: center;
        }

        .dark .template-label {
            color: #f9fafb;
        }

        .template-hint {
            font-size: 0.75rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .dark .template-hint {
            color: #9ca3af;
        }
    </style>
</x-filament-panels::page>
