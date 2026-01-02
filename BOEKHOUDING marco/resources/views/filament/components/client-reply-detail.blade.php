<div class="client-reply-detail">
    {{-- Original Message --}}
    <div class="message-section message-from-admin">
        <div class="message-header">
            <div class="message-sender">
                <span class="sender-icon">👤</span>
                <span class="sender-name">Uw Bericht</span>
            </div>
            <div class="message-time">{{ $task->created_at->format('d-m-Y H:i') }}</div>
        </div>
        <div class="message-body">
            {!! nl2br(e($task->description)) !!}
        </div>
    </div>

    {{-- Client Reply --}}
    <div class="message-section message-from-client">
        <div class="message-header">
            <div class="message-sender">
                <span class="sender-icon">💬</span>
                <span class="sender-name">{{ $task->client->name ?? 'Klant' }}</span>
            </div>
            <div class="message-time">{{ $task->replied_at->format('d-m-Y H:i') }}</div>
        </div>
        <div class="message-body">
            {!! nl2br(e($task->client_reply)) !!}
        </div>
    </div>

    {{-- Related Documents --}}
    @if($task->document)
    <div class="related-documents">
        <h4 class="section-title">Gerelateerd Document</h4>
        <a href="{{ route('filament.admin.resources.documents.view', $task->document->id) }}" class="document-link">
            <span class="doc-icon">📄</span>
            <span>{{ $task->document->original_filename }}</span>
        </a>
    </div>
    @endif
</div>

<style>
    .client-reply-detail {
        padding: 1rem;
    }

    .message-section {
        padding: 1.25rem;
        border-radius: 0.75rem;
        border: 1px solid #e5e7eb;
        margin-bottom: 1rem;
    }

    .dark .message-section {
        border-color: #374151;
    }

    .message-from-admin {
        background: #eff6ff;
        border-left: 4px solid #3b82f6;
    }

    .dark .message-from-admin {
        background: #1e3a8a;
        border-left-color: #60a5fa;
    }

    .message-from-client {
        background: #d1fae5;
        border-left: 4px solid #10b981;
    }

    .dark .message-from-client {
        background: #064e3b;
        border-left-color: #34d399;
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

    .related-documents {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #e5e7eb;
    }

    .dark .related-documents {
        border-top-color: #374151;
    }

    .section-title {
        font-size: 0.875rem;
        font-weight: 600;
        color: #6b7280;
        margin-bottom: 0.5rem;
    }

    .document-link {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #3b82f6;
        text-decoration: none;
    }

    .document-link:hover {
        text-decoration: underline;
    }
</style>

