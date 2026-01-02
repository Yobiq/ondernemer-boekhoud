<div class="task-detail-enhanced">
    {{-- Header with Type and Status --}}
    <div class="task-header">
        <div class="task-badges">
            <div class="task-type-badge task-type-{{ $task->type }}">
                @switch($task->type)
                    @case('missing_document')
                        📋 Ontbrekend Document
                        @break
                    @case('clarification')
                        ❓ Vraag
                        @break
                    @case('approval')
                        ✅ Goedkeuring
                        @break
                    @case('info')
                        ℹ️ Informatie
                        @break
                    @case('reminder')
                        ⏰ Herinnering
                        @break
                    @default
                        {{ ucfirst($task->type) }}
                @endswitch
            </div>
            @if($task->priority && $task->priority !== 'normal')
            <div class="task-priority-badge task-priority-{{ $task->priority }}">
                @if($task->priority === 'urgent')
                    ⚠️ Urgent
                @elseif($task->priority === 'high')
                    Hoog
                @else
                    {{ ucfirst($task->priority) }}
                @endif
            </div>
            @endif
            <div class="task-status-badge task-status-{{ $task->status }}">
                {{ $task->status === 'open' ? 'Open' : ($task->status === 'resolved' ? '✅ Afgehandeld' : 'Gesloten') }}
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="task-content">
        {{-- Message from Bookkeeper --}}
        <div class="message-section message-from-bookkeeper">
            <div class="message-header">
                <div class="message-sender">
                    <span class="sender-icon">👤</span>
                    <span class="sender-name">MARCOFIC Boekhouder</span>
                </div>
                <div class="message-time">{{ $task->created_at->format('d-m-Y H:i') }}</div>
            </div>
            <div class="message-body">
                {!! nl2br(e($task->description)) !!}
            </div>
        </div>

        {{-- Related Documents --}}
        @php
            $relatedDocs = [];
            if ($task->document_id) {
                $relatedDocs[] = $task->document;
            }
            // Extract from description
            $description = $task->description ?? '';
            preg_match_all('/Gerelateerde documenten:?\s*([^\n]+)/i', $description, $matches);
            if (!empty($matches[1])) {
                $docNames = array_map('trim', explode(',', $matches[1][0]));
                $clientId = auth()->user()->client_id;
                $foundDocs = \App\Models\Document::where('client_id', $clientId)
                    ->whereIn('original_filename', $docNames)
                    ->get();
                $relatedDocs = array_merge($relatedDocs, $foundDocs->toArray());
            }
            $relatedDocs = array_filter(array_unique($relatedDocs, SORT_REGULAR));
        @endphp

        @if(!empty($relatedDocs))
        <div class="related-documents-section">
            <h3 class="section-title">
                <span class="title-icon">📄</span>
                <span>Gerelateerde Documenten</span>
            </h3>
            <div class="documents-list">
                @foreach($relatedDocs as $doc)
                    @if($doc)
                    <a href="{{ route('filament.client.pages.mijn-documenten') }}?document={{ $doc->id }}" 
                       class="document-link">
                        <div class="document-item">
                            <div class="document-icon">📄</div>
                            <div class="document-info">
                                <div class="document-name">{{ $doc->original_filename ?? 'Onbekend' }}</div>
                                @if($doc->document_date)
                                <div class="document-meta">{{ $doc->document_date->format('d-m-Y') }}</div>
                                @endif
                            </div>
                            <div class="document-arrow">→</div>
                        </div>
                    </a>
                    @endif
                @endforeach
            </div>
        </div>
        @endif

        {{-- Deadline --}}
        @if($task->deadline)
        <div class="deadline-section">
            <h3 class="section-title">
                <span class="title-icon">📅</span>
                <span>Deadline</span>
            </h3>
            <div class="deadline-content">
                <div class="deadline-date">
                    <strong>{{ $task->deadline->format('d-m-Y') }}</strong>
                </div>
                @php
                    $daysUntil = now()->diffInDays($task->deadline, false);
                @endphp
                @if($daysUntil < 0)
                    <div class="deadline-status overdue">
                        ⚠️ {{ abs($daysUntil) }} dag(en) te laat
                    </div>
                @elseif($daysUntil <= 3)
                    <div class="deadline-status urgent">
                        ⏰ {{ $daysUntil }} dag(en) resterend
                    </div>
                @else
                    <div class="deadline-status ok">
                        ✅ {{ $daysUntil }} dag(en) resterend
                    </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Client Reply (if exists) --}}
        @if($task->hasReply())
        <div class="message-section message-from-client">
            <div class="message-header">
                <div class="message-sender">
                    <span class="sender-icon">💬</span>
                    <span class="sender-name">Uw Reactie</span>
                </div>
                <div class="message-time">{{ $task->replied_at->format('d-m-Y H:i') }}</div>
            </div>
            <div class="message-body">
                {!! nl2br(e($task->client_reply)) !!}
            </div>
        </div>
        @endif

        {{-- Admin Reply (if exists) --}}
        @if($task->hasAdminReply())
        <div class="message-section message-from-admin-reply">
            <div class="message-header">
                <div class="message-sender">
                    <span class="sender-icon">👤</span>
                    <span class="sender-name">Antwoord van Boekhouder</span>
                    <span class="new-badge">Nieuw</span>
                </div>
                <div class="message-time">{{ $task->admin_replied_at->format('d-m-Y H:i') }}</div>
            </div>
            <div class="message-body">
                {!! nl2br(e($task->admin_reply)) !!}
            </div>
        </div>
        @endif
    </div>

    {{-- Metadata --}}
    <div class="task-meta">
        <div class="meta-item">
            <span class="meta-label">Ontvangen:</span>
            <span class="meta-value">{{ $task->created_at->format('d-m-Y H:i') }}</span>
        </div>
        @if($task->read_at)
        <div class="meta-item">
            <span class="meta-label">Gelezen:</span>
            <span class="meta-value">{{ $task->read_at->format('d-m-Y H:i') }}</span>
        </div>
        @endif
        @if($task->updated_at && $task->updated_at->ne($task->created_at))
        <div class="meta-item">
            <span class="meta-label">Laatst bijgewerkt:</span>
            <span class="meta-value">{{ $task->updated_at->format('d-m-Y H:i') }}</span>
        </div>
        @endif
    </div>
</div>

<style>
    .task-detail-enhanced {
        padding: 1.5rem;
    }

    .task-header {
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #e5e7eb;
    }

    .dark .task-header {
        border-bottom-color: #374151;
    }

    .task-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .task-type-badge,
    .task-priority-badge,
    .task-status-badge {
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .task-type-missing_document {
        background: #fef3c7;
        color: #92400e;
    }

    .dark .task-type-missing_document {
        background: #78350f;
        color: #fde68a;
    }

    .task-type-clarification {
        background: #dbeafe;
        color: #1e40af;
    }

    .dark .task-type-clarification {
        background: #1e3a8a;
        color: #93c5fd;
    }

    .task-priority-urgent {
        background: #fee2e2;
        color: #991b1b;
    }

    .dark .task-priority-urgent {
        background: #7f1d1d;
        color: #fca5a5;
    }

    .task-priority-high {
        background: #fef3c7;
        color: #92400e;
    }

    .task-status-open {
        background: #fef3c7;
        color: #92400e;
    }

    .task-status-resolved {
        background: #d1fae5;
        color: #065f46;
    }

    .dark .task-status-resolved {
        background: #064e3b;
        color: #6ee7b7;
    }

    .task-content {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .message-section {
        padding: 1.25rem;
        border-radius: 0.75rem;
        border: 1px solid #e5e7eb;
    }

    .dark .message-section {
        border-color: #374151;
    }

    .message-from-bookkeeper {
        background: #f9fafb;
        border-left: 4px solid #3b82f6;
    }

    .dark .message-from-bookkeeper {
        background: #1f2937;
        border-left-color: #60a5fa;
    }

    .message-from-client {
        background: #eff6ff;
        border-left: 4px solid #10b981;
    }

    .dark .message-from-client {
        background: #064e3b;
        border-left-color: #34d399;
    }

    .message-from-admin-reply {
        background: #fef3c7;
        border-left: 4px solid #f59e0b;
        animation: slideIn 0.3s ease-out;
    }

    .dark .message-from-admin-reply {
        background: #78350f;
        border-left-color: #fbbf24;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-10px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .new-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.125rem 0.5rem;
        background: #ef4444;
        color: white;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 700;
        margin-left: 0.5rem;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.7;
        }
    }

    .message-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
    }

    .message-sender {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
        color: #111827;
    }

    .dark .message-sender {
        color: #f9fafb;
    }

    .sender-icon {
        font-size: 1.25rem;
    }

    .message-time {
        font-size: 0.875rem;
        color: #6b7280;
    }

    .message-body {
        color: #111827;
        line-height: 1.6;
        white-space: pre-wrap;
    }

    .dark .message-body {
        color: #f9fafb;
    }

    .section-title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 0.75rem;
        color: #111827;
    }

    .dark .section-title {
        color: #f9fafb;
    }

    .title-icon {
        font-size: 1.25rem;
    }

    .related-documents-section {
        margin-top: 1rem;
    }

    .documents-list {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .document-link {
        text-decoration: none;
        color: inherit;
    }

    .document-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.75rem;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        transition: all 0.2s;
    }

    .dark .document-item {
        background: #1f2937;
        border-color: #374151;
    }

    .document-item:hover {
        transform: translateX(4px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        border-color: #3b82f6;
    }

    .document-icon {
        font-size: 1.5rem;
    }

    .document-info {
        flex: 1;
    }

    .document-name {
        font-weight: 500;
        color: #111827;
    }

    .dark .document-name {
        color: #f9fafb;
    }

    .document-meta {
        font-size: 0.875rem;
        color: #6b7280;
    }

    .document-arrow {
        color: #9ca3af;
        font-size: 1.25rem;
    }

    .deadline-section {
        padding: 1rem;
        background: #fef3c7;
        border-radius: 0.5rem;
        border: 1px solid #fbbf24;
    }

    .dark .deadline-section {
        background: #78350f;
        border-color: #f59e0b;
    }

    .deadline-content {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .deadline-date {
        font-size: 1.125rem;
        font-weight: 600;
    }

    .deadline-status {
        font-size: 0.875rem;
        font-weight: 500;
    }

    .deadline-status.overdue {
        color: #dc2626;
    }

    .deadline-status.urgent {
        color: #d97706;
    }

    .deadline-status.ok {
        color: #059669;
    }

    .task-meta {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        padding-top: 1rem;
        margin-top: 1rem;
        border-top: 1px solid #e5e7eb;
    }

    .dark .task-meta {
        border-top-color: #374151;
    }

    .meta-item {
        display: flex;
        justify-content: space-between;
        font-size: 0.875rem;
    }

    .meta-label {
        color: #6b7280;
    }

    .meta-value {
        color: #111827;
        font-weight: 500;
    }

    .dark .meta-value {
        color: #f9fafb;
    }
</style>

