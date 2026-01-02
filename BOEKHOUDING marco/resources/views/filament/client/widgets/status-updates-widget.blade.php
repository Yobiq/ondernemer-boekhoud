<x-filament-widgets::widget>
    <x-filament::section>
        <div class="status-updates-widget">
            {{-- Header --}}
            <div class="widget-header">
                <h3 class="widget-title">
                    <span class="title-icon">🔔</span>
                    Recente Updates
                </h3>
                <span class="widget-subtitle">Laatste 24 uur</span>
            </div>
            
            @if(!$has_data)
            {{-- Empty State --}}
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <div class="empty-title">Geen recente updates</div>
                <div class="empty-description">Updates verschijnen hier zodra documenten worden verwerkt</div>
            </div>
            @else
            {{-- Updates Timeline --}}
            <div class="updates-timeline">
                @foreach($updates as $update)
                <div class="update-item">
                    <div class="update-icon-wrapper update-color-{{ $update['color'] }}">
                        <span class="update-icon">{{ $update['icon'] }}</span>
                    </div>
                    <div class="update-content">
                        <div class="update-title">{{ $update['title'] }}</div>
                        <div class="update-description">{{ $update['description'] }}</div>
                        <div class="update-time">{{ $update['time_human'] }}</div>
                    </div>
                    @if($update['document_id'])
                    <a href="{{ \App\Filament\Client\Pages\MijnDocumenten::getUrl() }}" class="update-action" title="Bekijk documenten">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

<style>
.status-updates-widget {
    padding: 0.5rem 0;
}

.widget-header {
    margin-bottom: 1rem;
}

.widget-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
    margin: 0 0 0.25rem 0;
}

.dark .widget-title {
    color: #f9fafb;
}

.title-icon {
    font-size: 1.25rem;
}

.widget-subtitle {
    font-size: 0.875rem;
    color: #6b7280;
}

.dark .widget-subtitle {
    color: #9ca3af;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 2rem 1rem;
}

.empty-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.empty-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
}

.dark .empty-title {
    color: #d1d5db;
}

.empty-description {
    font-size: 0.875rem;
    color: #6b7280;
}

.dark .empty-description {
    color: #9ca3af;
}

/* Updates Timeline */
.updates-timeline {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.update-item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.75rem;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    transition: all 0.2s;
}

.dark .update-item {
    background: #1f2937;
    border-color: #374151;
}

.update-item:hover {
    transform: translateX(2px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.dark .update-item:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.update-icon-wrapper {
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.update-color-success {
    background-color: #d1fae5;
}

.dark .update-color-success {
    background-color: #064e3b;
}

.update-color-info {
    background-color: #dbeafe;
}

.dark .update-color-info {
    background-color: #1e3a8a;
}

.update-color-warning {
    background-color: #fef3c7;
}

.dark .update-color-warning {
    background-color: #78350f;
}

.update-color-danger {
    background-color: #fee2e2;
}

.dark .update-color-danger {
    background-color: #7f1d1d;
}

.update-color-gray {
    background-color: #f3f4f6;
}

.dark .update-color-gray {
    background-color: #374151;
}

.update-icon {
    font-size: 1rem;
}

.update-content {
    flex: 1;
    min-width: 0;
}

.update-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.25rem;
}

.dark .update-title {
    color: #f9fafb;
}

.update-description {
    font-size: 0.75rem;
    color: #6b7280;
    margin-bottom: 0.25rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.dark .update-description {
    color: #9ca3af;
}

.update-time {
    font-size: 0.75rem;
    color: #9ca3af;
}

.dark .update-time {
    color: #6b7280;
}

.update-action {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 1.5rem;
    height: 1.5rem;
    color: #6b7280;
    transition: all 0.2s;
}

.dark .update-action {
    color: #9ca3af;
}

.update-action:hover {
    color: #3b82f6;
}

.dark .update-action:hover {
    color: #60a5fa;
}

.update-action svg {
    width: 1rem;
    height: 1rem;
}
</style>

