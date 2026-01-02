<x-filament-widgets::widget>
    <x-filament::section>
        <div class="processing-time-widget">
            {{-- Header --}}
            <div class="widget-header">
                <h3 class="widget-title">
                    <span class="title-icon">⚡</span>
                    Verwerkingstijd Statistieken
                </h3>
                @if($count > 0)
                <span class="widget-subtitle">Laatste 30 dagen • {{ $count }} documenten</span>
                @endif
            </div>
            
            @if($count === 0)
            {{-- Empty State --}}
            <div class="empty-state">
                <div class="empty-icon">📊</div>
                <div class="empty-title">Nog geen verwerkingsdata</div>
                <div class="empty-description">Upload documenten om statistieken te zien</div>
            </div>
            @else
            {{-- Metrics Grid --}}
            <div class="metrics-grid">
                {{-- Total Processing Time --}}
                <div class="metric-card metric-primary">
                    <div class="metric-icon">
                        <span>{{ $performance_status['icon'] }}</span>
                    </div>
                    <div class="metric-content">
                        <div class="metric-label">Gemiddelde Verwerkingstijd</div>
                        <div class="metric-value">
                            @if($total_hours < 1)
                                {{ $total_minutes }} min
                            @else
                                {{ $total_hours }} uur
                            @endif
                        </div>
                        <div class="metric-badge badge-{{ $performance_status['color'] }}">
                            {{ $performance_status['label'] }}
                        </div>
                    </div>
                </div>
                
                {{-- OCR Time --}}
                <div class="metric-card">
                    <div class="metric-icon">
                        <span>🔍</span>
                    </div>
                    <div class="metric-content">
                        <div class="metric-label">OCR Verwerking</div>
                        <div class="metric-value">{{ round($ocr_time['average_minutes'], 1) }} min</div>
                        <div class="metric-description">Automatische herkenning</div>
                    </div>
                </div>
                
                {{-- Approval Time --}}
                <div class="metric-card">
                    <div class="metric-icon">
                        <span>✅</span>
                    </div>
                    <div class="metric-content">
                        <div class="metric-label">Goedkeuringstijd</div>
                        <div class="metric-value">{{ round($approval_time['average_minutes'], 1) }} min</div>
                        <div class="metric-description">Tot definitieve verwerking</div>
                    </div>
                </div>
                
                {{-- Fastest Processing --}}
                <div class="metric-card">
                    <div class="metric-icon">
                        <span>🏆</span>
                    </div>
                    <div class="metric-content">
                        <div class="metric-label">Snelste Verwerking</div>
                        <div class="metric-value">{{ $fastest_minutes }} min</div>
                        <div class="metric-description">Beste prestatie</div>
                    </div>
                </div>
            </div>
            
            {{-- Info Banner --}}
            <div class="info-banner">
                <div class="info-icon">💡</div>
                <div class="info-content">
                    <strong>Tip:</strong> Documenten met duidelijke foto's en goede kwaliteit worden sneller verwerkt. 
                    Upload direct via de camera voor beste resultaten.
                </div>
            </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

<style>
.processing-time-widget {
    padding: 0.5rem 0;
}

.widget-header {
    margin-bottom: 1.25rem;
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

/* Metrics Grid */
.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}

@media (max-width: 768px) {
    .metrics-grid {
        grid-template-columns: 1fr;
    }
}

.metric-card {
    background: linear-gradient(135deg, #f9fafb 0%, #ffffff 100%);
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    padding: 1rem;
    display: flex;
    gap: 0.75rem;
    transition: all 0.2s;
}

.dark .metric-card {
    background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
    border-color: #374151;
}

.metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.dark .metric-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.metric-primary {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border-color: #fcd34d;
}

.dark .metric-primary {
    background: linear-gradient(135deg, #78350f 0%, #451a03 100%);
    border-color: #92400e;
}

.metric-icon {
    font-size: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.metric-content {
    flex: 1;
    min-width: 0;
}

.metric-label {
    font-size: 0.75rem;
    font-weight: 500;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.25rem;
}

.dark .metric-label {
    color: #9ca3af;
}

.metric-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
    line-height: 1.2;
    margin-bottom: 0.25rem;
}

.dark .metric-value {
    color: #f9fafb;
}

.metric-description {
    font-size: 0.75rem;
    color: #6b7280;
}

.dark .metric-description {
    color: #9ca3af;
}

.metric-badge {
    display: inline-block;
    padding: 0.125rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-top: 0.25rem;
}

.badge-success {
    background-color: #d1fae5;
    color: #065f46;
}

.dark .badge-success {
    background-color: #064e3b;
    color: #6ee7b7;
}

.badge-info {
    background-color: #dbeafe;
    color: #1e40af;
}

.dark .badge-info {
    background-color: #1e3a8a;
    color: #93c5fd;
}

.badge-warning {
    background-color: #fef3c7;
    color: #92400e;
}

.dark .badge-warning {
    background-color: #78350f;
    color: #fcd34d;
}

.badge-danger {
    background-color: #fee2e2;
    color: #991b1b;
}

.dark .badge-danger {
    background-color: #7f1d1d;
    color: #fca5a5;
}

.badge-gray {
    background-color: #f3f4f6;
    color: #4b5563;
}

.dark .badge-gray {
    background-color: #374151;
    color: #9ca3af;
}

/* Info Banner */
.info-banner {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    border: 1px solid #93c5fd;
    border-radius: 0.5rem;
    padding: 0.75rem 1rem;
    display: flex;
    gap: 0.75rem;
    align-items: flex-start;
}

.dark .info-banner {
    background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
    border-color: #3b82f6;
}

.info-icon {
    font-size: 1.25rem;
    flex-shrink: 0;
}

.info-content {
    font-size: 0.875rem;
    color: #1e40af;
    line-height: 1.5;
}

.dark .info-content {
    color: #bfdbfe;
}

.info-content strong {
    font-weight: 600;
}
</style>

