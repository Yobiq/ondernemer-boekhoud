<x-filament-widgets::widget>
    <x-filament::section>
        <div class="auto-approval-widget-modern">
            {{-- Header --}}
            <div class="widget-header-modern">
                <div class="header-left">
                    <h3 class="widget-title-modern">
                        <span class="title-icon-modern">🎯</span>
                        <span>Automatische Goedkeuring</span>
                    </h3>
                    @if($total > 0)
                    <span class="widget-subtitle-modern">Laatste 30 dagen • {{ $total }} documenten</span>
                    @endif
                </div>
            </div>
            
            @if($total === 0)
            {{-- Empty State --}}
            <div class="empty-state-modern">
                <div class="empty-icon-modern">📊</div>
                <div class="empty-content-modern">
                    <div class="empty-title-modern">Nog geen goedkeuringsdata</div>
                    <div class="empty-description-modern">Upload documenten om statistieken te zien</div>
                </div>
            </div>
            @else
            {{-- Main Rate Display - Redesigned --}}
            <div class="rate-display-modern">
                {{-- Large Circle Progress --}}
                <div class="rate-circle-modern">
                    <svg class="rate-svg-modern" viewBox="0 0 140 140">
                        <!-- Outer glow ring -->
                        <circle
                            cx="70"
                            cy="70"
                            r="64"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            opacity="0.1"
                            class="rate-glow rate-glow-{{ $status['color'] }}"
                        />
                        <!-- Background circle -->
                        <circle
                            cx="70"
                            cy="70"
                            r="60"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="10"
                            class="rate-bg-modern"
                        />
                        <!-- Progress circle with gradient -->
                        <circle
                            cx="70"
                            cy="70"
                            r="60"
                            fill="none"
                            stroke="url(#gradient-{{ $status['color'] }})"
                            stroke-width="10"
                            stroke-linecap="round"
                            class="rate-progress-modern"
                            style="stroke-dasharray: {{ 377 * ($rate / 100) }} 377; transform: rotate(-90deg); transform-origin: center;"
                        />
                        <!-- Gradient Definitions -->
                        <defs>
                            <linearGradient id="gradient-success" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#10b981;stop-opacity:1" />
                                <stop offset="100%" style="stop-color:#059669;stop-opacity:1" />
                            </linearGradient>
                            <linearGradient id="gradient-info" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#3b82f6;stop-opacity:1" />
                                <stop offset="100%" style="stop-color:#2563eb;stop-opacity:1" />
                            </linearGradient>
                            <linearGradient id="gradient-warning" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#f59e0b;stop-opacity:1" />
                                <stop offset="100%" style="stop-color:#d97706;stop-opacity:1" />
                            </linearGradient>
                            <linearGradient id="gradient-danger" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#ef4444;stop-opacity:1" />
                                <stop offset="100%" style="stop-color:#dc2626;stop-opacity:1" />
                            </linearGradient>
                        </defs>
                    </svg>
                    <div class="rate-content-modern">
                        <div class="rate-value-modern">{{ round($rate) }}<span class="rate-percent">%</span></div>
                        <div class="rate-label-modern">Goedkeuring</div>
                    </div>
                </div>
                
                {{-- Stats Grid --}}
                <div class="rate-details-modern">
                    <div class="status-badge-modern badge-modern-{{ $status['color'] }}">
                        <span class="status-icon-modern">{{ $status['icon'] }}</span>
                        <span class="status-label-modern">{{ $status['label'] }}</span>
                    </div>
                    
                    <div class="rate-stats-modern">
                        <div class="stat-card-modern stat-card-success">
                            <div class="stat-card-icon">✅</div>
                            <div class="stat-card-content">
                                <div class="stat-card-value">{{ $auto_approved }}</div>
                                <div class="stat-card-label">Automatisch</div>
                            </div>
                        </div>
                        
                        <div class="stat-card-modern stat-card-manual">
                            <div class="stat-card-icon">👤</div>
                            <div class="stat-card-content">
                                <div class="stat-card-value">{{ $manual_review }}</div>
                                <div class="stat-card-label">Handmatig</div>
                            </div>
                        </div>
                    </div>
                    
                    @if($rate < $goal_rate)
                    <div class="goal-info-modern">
                        <div class="goal-header-modern">
                            <span class="goal-icon-modern">🎯</span>
                            <span class="goal-label-modern">Doel: {{ $goal_rate }}%</span>
                        </div>
                        <div class="goal-progress-bar-modern">
                            <div class="goal-progress-fill-modern" style="width: {{ $progress }}%"></div>
                        </div>
                        <div class="goal-status-modern">{{ round($progress) }}% voltooid</div>
                    </div>
                    @else
                    <div class="goal-achieved-modern">
                        <span class="goal-icon-large">🎉</span>
                        <span class="goal-text-modern">Doel van {{ $goal_rate }}% bereikt!</span>
                    </div>
                    @endif
                </div>
            </div>
            
            {{-- Breakdown by Type - Modern Design --}}
            @if(!empty($by_type))
            <div class="type-breakdown-modern">
                <div class="breakdown-header-modern">
                    <span class="breakdown-header-icon">📊</span>
                    <span>Verdeling per Type</span>
                </div>
                <div class="breakdown-grid-modern">
                    @foreach($by_type as $typeData)
                    @php
                        $typeLabel = match($typeData['type']) {
                            'receipt' => 'Bonnetjes',
                            'purchase_invoice' => 'Inkoopfacturen',
                            'bank_statement' => 'Bankafschriften',
                            'sales_invoice' => 'Verkoopfacturen',
                            default => 'Overig',
                        };
                        $typeIcon = match($typeData['type']) {
                            'receipt' => '🧾',
                            'purchase_invoice' => '📄',
                            'bank_statement' => '🏦',
                            'sales_invoice' => '📊',
                            default => '📁',
                        };
                    @endphp
                    <div class="breakdown-card-modern">
                        <div class="breakdown-card-header">
                            <span class="breakdown-card-icon">{{ $typeIcon }}</span>
                            <div class="breakdown-card-info">
                                <div class="breakdown-card-label">{{ $typeLabel }}</div>
                                <div class="breakdown-card-count">{{ $typeData['total'] }} documenten</div>
                            </div>
                        </div>
                        <div class="breakdown-card-progress">
                            <div class="breakdown-card-bar">
                                <div class="breakdown-card-bar-fill" style="width: {{ $typeData['rate'] }}%"></div>
                            </div>
                            <span class="breakdown-card-percentage">{{ round($typeData['rate']) }}%</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            
            {{-- Tip Banner - Modern --}}
            <div class="tip-banner-modern">
                <div class="tip-banner-icon">💡</div>
                <div class="tip-banner-content">
                    <strong>Tip:</strong> Duidelijke foto's leiden tot hogere automatische goedkeuring
                </div>
            </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

<style>
/* Modern Auto-Approval Widget Styles */
.auto-approval-widget-modern {
    padding: 0;
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

/* Empty State - Modern */
.empty-state-modern {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem 1.5rem;
    text-align: center;
}

.empty-icon-modern {
    font-size: 4rem;
    margin-bottom: 1.25rem;
    opacity: 0.6;
    filter: grayscale(20%);
}

.empty-content-modern {
    max-width: 280px;
}

.empty-title-modern {
    font-size: 1.125rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
}

.dark .empty-title-modern {
    color: #d1d5db;
}

.empty-description-modern {
    font-size: 0.875rem;
    color: #6b7280;
    line-height: 1.5;
}

.dark .empty-description-modern {
    color: #9ca3af;
}

/* Rate Display */
.rate-display {
    display: flex;
    gap: 1.5rem;
    align-items: center;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}

@media (max-width: 768px) {
    .rate-display {
        flex-direction: column;
        text-align: center;
    }
}

/* Rate Circle */
.rate-circle {
    position: relative;
    width: 120px;
    height: 120px;
    flex-shrink: 0;
}

.rate-svg {
    width: 100%;
    height: 100%;
    transform: rotate(-90deg);
}

.rate-bg {
    color: #e5e7eb;
}

.dark .rate-bg {
    color: #374151;
}

.rate-progress {
    transition: stroke-dasharray 0.5s ease;
}

.rate-progress-success {
    color: #10b981;
}

.rate-progress-info {
    color: #3b82f6;
}

.rate-progress-warning {
    color: #f59e0b;
}

.rate-progress-danger {
    color: #ef4444;
}

.rate-progress-gray {
    color: #9ca3af;
}

.rate-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
}

.rate-value {
    font-size: 1.875rem;
    font-weight: 700;
    color: #111827;
    line-height: 1;
}

.dark .rate-value {
    color: #f9fafb;
}

.rate-label {
    font-size: 0.75rem;
    color: #6b7280;
    margin-top: 0.25rem;
}

.dark .rate-label {
    color: #9ca3af;
}

/* Rate Details */
.rate-details {
    flex: 1;
    min-width: 200px;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 9999px;
    font-weight: 600;
    margin-bottom: 1rem;
}

.status-icon {
    font-size: 1.25rem;
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

.rate-stats {
    display: flex;
    gap: 1rem;
    align-items: center;
    margin-bottom: 1rem;
}

.stat-item {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}

@media (max-width: 768px) {
    .rate-stats {
        justify-content: center;
    }
    
    .stat-item {
        align-items: center;
    }
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
}

.dark .stat-value {
    color: #f9fafb;
}

.stat-label {
    font-size: 0.75rem;
    color: #6b7280;
}

.dark .stat-label {
    color: #9ca3af;
}

.stat-divider {
    width: 1px;
    height: 2rem;
    background-color: #e5e7eb;
}

.dark .stat-divider {
    background-color: #374151;
}

/* Goal Info */
.goal-info {
    background: #f3f4f6;
    border-radius: 0.5rem;
    padding: 0.75rem;
}

.dark .goal-info {
    background: #1f2937;
}

.goal-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #6b7280;
    margin-bottom: 0.5rem;
}

.dark .goal-label {
    color: #9ca3af;
}

.goal-progress-bar {
    height: 0.5rem;
    background-color: #e5e7eb;
    border-radius: 9999px;
    overflow: hidden;
    margin-bottom: 0.5rem;
}

.dark .goal-progress-bar {
    background-color: #374151;
}

.goal-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #3b82f6 0%, #10b981 100%);
    transition: width 0.5s ease;
}

.goal-status {
    font-size: 0.75rem;
    color: #6b7280;
}

.dark .goal-status {
    color: #9ca3af;
}

.goal-achieved {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem;
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    border-radius: 0.5rem;
}

.dark .goal-achieved {
    background: linear-gradient(135deg, #064e3b 0%, #065f46 100%);
}

.goal-icon {
    font-size: 1.25rem;
}

.goal-text {
    font-size: 0.875rem;
    font-weight: 600;
    color: #065f46;
}

.dark .goal-text {
    color: #6ee7b7;
}

/* Type Breakdown */
.type-breakdown {
    margin-bottom: 1rem;
    background: #f9fafb;
    border-radius: 0.5rem;
    padding: 1rem;
}

.dark .type-breakdown {
    background: #1f2937;
}

.breakdown-header {
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.75rem;
}

.dark .breakdown-header {
    color: #d1d5db;
}

.breakdown-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.breakdown-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
}

.breakdown-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex: 1;
}

.breakdown-icon {
    font-size: 1.25rem;
}

.breakdown-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
}

.dark .breakdown-label {
    color: #d1d5db;
}

.breakdown-count {
    font-size: 0.75rem;
    color: #6b7280;
}

.dark .breakdown-count {
    color: #9ca3af;
}

.breakdown-rate {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    min-width: 120px;
}

.breakdown-percentage {
    font-size: 0.875rem;
    font-weight: 600;
    color: #111827;
    min-width: 3rem;
    text-align: right;
}

.dark .breakdown-percentage {
    color: #f9fafb;
}

.breakdown-bar {
    flex: 1;
    height: 0.5rem;
    background-color: #e5e7eb;
    border-radius: 9999px;
    overflow: hidden;
}

.dark .breakdown-bar {
    background-color: #374151;
}

.breakdown-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #3b82f6 0%, #10b981 100%);
    transition: width 0.3s ease;
}

/* Tip Banner */
.tip-banner {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    border: 1px solid #93c5fd;
    border-radius: 0.5rem;
    padding: 0.75rem 1rem;
    display: flex;
    gap: 0.75rem;
    align-items: flex-start;
}

.dark .tip-banner {
    background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
    border-color: #3b82f6;
}

.tip-icon {
    font-size: 1.25rem;
    flex-shrink: 0;
}

.tip-content {
    font-size: 0.875rem;
    color: #1e40af;
    line-height: 1.5;
}

.dark .tip-content {
    color: #bfdbfe;
}

.tip-content strong {
    font-weight: 600;
}

/* Modern Styles - Overrides */
@import url('auto-approval-rate-widget-modern-styles.css');

/* Header - Modern */
.widget-header-modern {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
}

.header-left {
    flex: 1;
}

.widget-title-modern {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    font-size: 1.125rem;
    font-weight: 700;
    color: #111827;
    margin: 0 0 0.375rem 0;
    letter-spacing: -0.025em;
}

.dark .widget-title-modern {
    color: #f9fafb;
}

.title-icon-modern {
    font-size: 1.5rem;
}

.widget-subtitle-modern {
    font-size: 0.8125rem;
    color: #6b7280;
    font-weight: 500;
}

.dark .widget-subtitle-modern {
    color: #9ca3af;
}

/* Rate Display - Modern Responsive Grid */
.rate-display-modern {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

@media (min-width: 768px) {
    .rate-display-modern {
        grid-template-columns: auto 1fr;
        align-items: center;
    }
}

/* Circle - Modern with Gradient */
.rate-circle-modern {
    position: relative;
    width: 160px;
    height: 160px;
    margin: 0 auto;
}

@media (min-width: 768px) {
    .rate-circle-modern {
        margin: 0;
    }
}

.rate-svg-modern {
    width: 100%;
    height: 100%;
    filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.08));
}

.rate-bg-modern {
    color: #e5e7eb;
}

.dark .rate-bg-modern {
    color: #374151;
}

.rate-progress-modern {
    transition: stroke-dasharray 1s cubic-bezier(0.4, 0, 0.2, 1);
}

.rate-content-modern {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
}

.rate-value-modern {
    font-size: 2.5rem;
    font-weight: 800;
    color: #111827;
    line-height: 1;
    letter-spacing: -0.05em;
}

.dark .rate-value-modern {
    color: #f9fafb;
}

.rate-percent {
    font-size: 1.5rem;
    opacity: 0.7;
}

.rate-label-modern {
    font-size: 0.75rem;
    color: #6b7280;
    margin-top: 0.375rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.dark .rate-label-modern {
    color: #9ca3af;
}

/* Stats Cards - Modern */
.rate-details-modern {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.status-badge-modern {
    display: inline-flex;
    align-items: center;
    gap: 0.625rem;
    padding: 0.625rem 1.125rem;
    border-radius: 12px;
    font-weight: 600;
    align-self: flex-start;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.badge-modern-success {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    color: #065f46;
}

.dark .badge-modern-success {
    background: linear-gradient(135deg, #064e3b 0%, #065f46 100%);
    color: #6ee7b7;
}

.badge-modern-info {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    color: #1e40af;
}

.dark .badge-modern-info {
    background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
    color: #93c5fd;
}

.badge-modern-warning {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    color: #92400e;
}

.dark .badge-modern-warning {
    background: linear-gradient(135deg, #78350f 0%, #92400e 100%);
    color: #fcd34d;
}

.badge-modern-danger {
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    color: #991b1b;
}

.dark .badge-modern-danger {
    background: linear-gradient(135deg, #7f1d1d 0%, #991b1b 100%);
    color: #fca5a5;
}

.rate-stats-modern {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.75rem;
}

.stat-card-modern {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    border-radius: 12px;
    transition: all 0.2s;
}

.stat-card-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.stat-card-success {
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    border: 1px solid #86efac;
}

.dark .stat-card-success {
    background: linear-gradient(135deg, #052e16 0%, #064e3b 100%);
    border-color: #059669;
}

.stat-card-manual {
    background: linear-gradient(135deg, #fef9c3 0%, #fef08a 100%);
    border: 1px solid #fde047;
}

.dark .stat-card-manual {
    background: linear-gradient(135deg, #713f12 0%, #854d0e 100%);
    border-color: #ca8a04;
}

.stat-card-icon {
    font-size: 1.75rem;
}

.stat-card-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
}

.dark .stat-card-value {
    color: #f9fafb;
}

.stat-card-label {
    font-size: 0.75rem;
    color: #6b7280;
    margin-top: 0.25rem;
}

.dark .stat-card-label {
    color: #9ca3af;
}

/* Goal - Modern */
.goal-info-modern {
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
    border-radius: 12px;
    padding: 1rem;
}

.dark .goal-info-modern {
    background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
}

.goal-header-modern {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}

.goal-label-modern {
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
}

.dark .goal-label-modern {
    color: #d1d5db;
}

.goal-progress-bar-modern {
    height: 0.625rem;
    background-color: #e5e7eb;
    border-radius: 9999px;
    overflow: hidden;
    margin-bottom: 0.625rem;
}

.dark .goal-progress-bar-modern {
    background-color: #374151;
}

.goal-progress-fill-modern {
    height: 100%;
    background: linear-gradient(90deg, #3b82f6 0%, #10b981 100%);
    transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
}

.goal-status-modern {
    font-size: 0.75rem;
    color: #6b7280;
}

.dark .goal-status-modern {
    color: #9ca3af;
}

.goal-achieved-modern {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    border-radius: 12px;
}

.dark .goal-achieved-modern {
    background: linear-gradient(135deg, #064e3b 0%, #065f46 100%);
}

.goal-text-modern {
    font-size: 0.9375rem;
    font-weight: 600;
    color: #065f46;
}

.dark .goal-text-modern {
    color: #6ee7b7;
}

/* Breakdown - Modern Grid */
.type-breakdown-modern {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 2px solid #e5e7eb;
}

.dark .type-breakdown-modern {
    border-color: #374151;
}

.breakdown-header-modern {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 1rem;
}

.dark .breakdown-header-modern {
    color: #f9fafb;
}

.breakdown-grid-modern {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.875rem;
}

@media (min-width: 640px) {
    .breakdown-grid-modern {
        grid-template-columns: repeat(2, 1fr);
    }
}

.breakdown-card-modern {
    background: linear-gradient(135deg, #f9fafb 0%, #ffffff 100%);
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 0.875rem;
    transition: all 0.2s;
}

.dark .breakdown-card-modern {
    background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
    border-color: #374151;
}

.breakdown-card-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    border-color: #3b82f6;
}

.breakdown-card-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}

.breakdown-card-icon {
    font-size: 1.5rem;
}

.breakdown-card-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #111827;
}

.dark .breakdown-card-label {
    color: #f9fafb;
}

.breakdown-card-count {
    font-size: 0.75rem;
    color: #6b7280;
}

.dark .breakdown-card-count {
    color: #9ca3af;
}

.breakdown-card-progress {
    display: flex;
    align-items: center;
    gap: 0.625rem;
}

.breakdown-card-bar {
    flex: 1;
    height: 0.5rem;
    background-color: #e5e7eb;
    border-radius: 9999px;
    overflow: hidden;
}

.dark .breakdown-card-bar {
    background-color: #374151;
}

.breakdown-card-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #3b82f6 0%, #10b981 100%);
    transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.breakdown-card-percentage {
    font-size: 0.875rem;
    font-weight: 700;
    color: #111827;
    min-width: 3rem;
    text-align: right;
}

.dark .breakdown-card-percentage {
    color: #f9fafb;
}

/* Tip - Modern */
.tip-banner-modern {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    border: 1px solid #93c5fd;
    border-radius: 10px;
    padding: 0.875rem 1rem;
    display: flex;
    gap: 0.75rem;
    margin-top: 1.5rem;
}

.dark .tip-banner-modern {
    background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
    border-color: #3b82f6;
}

.tip-banner-icon {
    font-size: 1.25rem;
}

.tip-banner-content {
    font-size: 0.8125rem;
    color: #1e40af;
    line-height: 1.5;
}

.dark .tip-banner-content {
    color: #bfdbfe;
}

.tip-banner-content strong {
    font-weight: 600;
}
</style>

