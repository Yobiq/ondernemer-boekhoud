<div class="task-detail">
    <div class="task-header">
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
        <div class="task-status-badge task-status-{{ $task->status }}">
            {{ $task->status === 'open' ? 'Open' : ($task->status === 'resolved' ? 'Afgehandeld' : 'Gesloten') }}
        </div>
    </div>

    <div class="task-content">
        <div class="task-description">
            <h3 class="task-label">Bericht:</h3>
            <p class="task-text">{{ nl2br(e($task->description)) }}</p>
        </div>

        @if($task->deadline)
        <div class="task-deadline">
            <h3 class="task-label">Deadline:</h3>
            <p class="task-text">
                <strong>{{ $task->deadline->format('d-m-Y') }}</strong>
                @php
                    $daysUntil = now()->diffInDays($task->deadline, false);
                @endphp
                @if($daysUntil < 0)
                    <span class="deadline-overdue">({{ abs($daysUntil) }} dag(en) te laat)</span>
                @elseif($daysUntil <= 3)
                    <span class="deadline-urgent">({{ $daysUntil }} dag(en) resterend)</span>
                @else
                    <span class="deadline-ok">({{ $daysUntil }} dag(en) resterend)</span>
                @endif
            </p>
        </div>
        @endif

        @if($task->document)
        <div class="task-document">
            <h3 class="task-label">Gerelateerd Document:</h3>
            <p class="task-text">
                <a href="{{ route('filament.client.pages.mijn-documenten', ['document' => $task->document->id]) }}" 
                   class="document-link">
                    📄 {{ $task->document->original_filename }}
                </a>
            </p>
        </div>
        @endif

        <div class="task-meta">
            <div class="meta-item">
                <span class="meta-label">Ontvangen:</span>
                <span class="meta-value">{{ $task->created_at->format('d-m-Y H:i') }}</span>
            </div>
            @if($task->updated_at && $task->updated_at->ne($task->created_at))
            <div class="meta-item">
                <span class="meta-label">Laatst bijgewerkt:</span>
                <span class="meta-value">{{ $task->updated_at->format('d-m-Y H:i') }}</span>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
    .task-detail {
        padding: 1rem;
    }

    .task-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .dark .task-header {
        border-bottom-color: #374151;
    }

    .task-type-badge {
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

    .task-status-badge {
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .task-status-open {
        background: #fef3c7;
        color: #92400e;
    }

    .dark .task-status-open {
        background: #78350f;
        color: #fde68a;
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

    .task-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: #6b7280;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .dark .task-label {
        color: #9ca3af;
    }

    .task-text {
        font-size: 1rem;
        color: #111827;
        line-height: 1.6;
    }

    .dark .task-text {
        color: #f9fafb;
    }

    .document-link {
        color: #3b82f6;
        text-decoration: none;
        font-weight: 500;
    }

    .document-link:hover {
        text-decoration: underline;
    }

    .deadline-overdue {
        color: #ef4444;
        font-weight: 600;
    }

    .deadline-urgent {
        color: #f59e0b;
        font-weight: 600;
    }

    .deadline-ok {
        color: #10b981;
    }

    .task-meta {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        padding-top: 1rem;
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

    .dark .meta-label {
        color: #9ca3af;
    }

    .meta-value {
        color: #111827;
        font-weight: 500;
    }

    .dark .meta-value {
        color: #f9fafb;
    }
</style>

