<x-filament-widgets::widget>
    <x-filament::section>
        <div class="notification-center-widget">
            {{-- Header --}}
            <div class="widget-header">
                <h3 class="widget-title">
                    <span class="title-icon">🔔</span>
                    Meldingen
                    @if($unread_count > 0)
                    <span class="unread-badge">{{ $unread_count }}</span>
                    @endif
                </h3>
            </div>
            
            @if(!$has_notifications)
            {{-- Empty State --}}
            <div class="empty-state">
                <div class="empty-icon">✨</div>
                <div class="empty-title">Geen nieuwe meldingen</div>
                <div class="empty-description">U bent helemaal bij!</div>
            </div>
            @else
            {{-- Notifications List --}}
            <div class="notifications-list">
                @foreach($recent_notifications as $notification)
                <div class="notification-item {{ $notification['read'] ? 'notification-read' : 'notification-unread' }}">
                    <div class="notification-icon-wrapper notification-color-{{ $notification['color'] }}">
                        <span class="notification-icon">{{ $notification['icon'] }}</span>
                    </div>
                    <div class="notification-content">
                        <div class="notification-title">{{ $notification['title'] }}</div>
                        <div class="notification-message">{{ $notification['message'] }}</div>
                        <div class="notification-time">{{ $notification['time_human'] }}</div>
                    </div>
                    @if(!$notification['read'])
                    <div class="unread-indicator"></div>
                    @endif
                </div>
                @endforeach
            </div>
            
            {{-- View All Link --}}
            <div class="view-all">
                <a href="#" class="view-all-link">
                    Bekijk alle meldingen
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

<style>
.notification-center-widget {
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
    margin: 0;
}

.dark .widget-title {
    color: #f9fafb;
}

.title-icon {
    font-size: 1.25rem;
}

.unread-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 1.5rem;
    height: 1.5rem;
    padding: 0 0.375rem;
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: #ffffff;
    font-size: 0.75rem;
    font-weight: 700;
    border-radius: 9999px;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 2rem 1rem;
}

.empty-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
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

/* Notifications List */
.notifications-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.notification-item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.75rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    transition: all 0.2s;
    position: relative;
}

.dark .notification-item {
    border-color: #374151;
}

.notification-unread {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    border-color: #93c5fd;
}

.dark .notification-unread {
    background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
    border-color: #3b82f6;
}

.notification-read {
    background: #f9fafb;
}

.dark .notification-read {
    background: #1f2937;
}

.notification-item:hover {
    transform: translateX(2px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.dark .notification-item:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.notification-icon-wrapper {
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.notification-color-success {
    background-color: #d1fae5;
}

.dark .notification-color-success {
    background-color: #064e3b;
}

.notification-color-info {
    background-color: #dbeafe;
}

.dark .notification-color-info {
    background-color: #1e3a8a;
}

.notification-color-warning {
    background-color: #fef3c7;
}

.dark .notification-color-warning {
    background-color: #78350f;
}

.notification-color-danger {
    background-color: #fee2e2;
}

.dark .notification-color-danger {
    background-color: #7f1d1d;
}

.notification-icon {
    font-size: 1rem;
}

.notification-content {
    flex: 1;
    min-width: 0;
}

.notification-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.25rem;
}

.dark .notification-title {
    color: #f9fafb;
}

.notification-message {
    font-size: 0.75rem;
    color: #6b7280;
    margin-bottom: 0.25rem;
    line-height: 1.4;
}

.dark .notification-message {
    color: #9ca3af;
}

.notification-time {
    font-size: 0.75rem;
    color: #9ca3af;
}

.dark .notification-time {
    color: #6b7280;
}

.unread-indicator {
    width: 0.5rem;
    height: 0.5rem;
    background-color: #3b82f6;
    border-radius: 50%;
    flex-shrink: 0;
    margin-top: 0.375rem;
}

/* View All Link */
.view-all {
    padding-top: 0.75rem;
    border-top: 1px solid #e5e7eb;
}

.dark .view-all {
    border-color: #374151;
}

.view-all-link {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: #3b82f6;
    text-decoration: none;
    transition: all 0.2s;
}

.dark .view-all-link {
    color: #60a5fa;
}

.view-all-link:hover {
    color: #2563eb;
}

.dark .view-all-link:hover {
    color: #93c5fd;
}

.view-all-link svg {
    width: 1rem;
    height: 1rem;
}
</style>

