@php
    $fileExtension = strtolower(pathinfo($document->original_filename, PATHINFO_EXTENSION));
    $fileSize = \Illuminate\Support\Facades\Storage::exists($document->file_path) 
        ? \Illuminate\Support\Facades\Storage::size($document->file_path) 
        : 0;
    $fileSizeFormatted = $fileSize > 0 ? number_format($fileSize / 1024, 2) . ' KB' : 'Onbekend';
    $processingTime = $document->updated_at->diffInMinutes($document->created_at);
@endphp

<div class="document-details-enhanced">
    {{-- Hero Header with Document Info --}}
    <div class="doc-hero-header">
        <div class="doc-hero-content">
            <div class="doc-icon-wrapper">
                @if($document->document_type === 'receipt')
                    <div class="doc-icon doc-icon-receipt">🧾</div>
                @elseif($document->document_type === 'purchase_invoice')
                    <div class="doc-icon doc-icon-invoice">📄</div>
                @elseif($document->document_type === 'bank_statement')
                    <div class="doc-icon doc-icon-bank">🏦</div>
                @elseif($document->document_type === 'sales_invoice')
                    <div class="doc-icon doc-icon-sales">🧑‍💼</div>
                @else
                    <div class="doc-icon doc-icon-default">📁</div>
                @endif
            </div>
            <div class="doc-hero-text">
                <h2 class="doc-title">{{ $document->original_filename }}</h2>
                <div class="doc-meta-badges">
                    <span class="meta-badge">
                        <svg class="badge-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        {{ $document->created_at->format('d-m-Y H:i') }}
                    </span>
                    <span class="meta-badge">
                        <svg class="badge-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        {{ strtoupper($fileExtension) }} • {{ $fileSizeFormatted }}
                    </span>
                    @if($document->document_type)
                    <span class="meta-badge meta-badge-type">
                        {{ match($document->document_type) {
                            'receipt' => '🧾 Bonnetje',
                            'purchase_invoice' => '📄 Inkoopfactuur',
                            'bank_statement' => '🏦 Bankafschrift',
                            'sales_invoice' => '🧑‍💼 Verkoopfactuur',
                            default => '📁 Overig',
                        } }}
                    </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="doc-quick-actions">
            <a href="{{ route('documents.file', $document) }}" target="_blank" class="action-btn action-btn-primary">
                <svg class="action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                <span>Bekijken</span>
            </a>
            <a href="{{ route('documents.download', $document) }}" class="action-btn action-btn-secondary">
                <svg class="action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                <span>Download</span>
            </a>
        </div>
    </div>

    {{-- Status Card with Enhanced Design --}}
    <div class="status-card status-{{ $document->status }}">
        <div class="status-header">
            <div class="status-icon-large">
                @if($document->status === 'approved')
                    <div class="status-icon-approved">✅</div>
                @elseif($document->status === 'ocr_processing')
                    <div class="status-icon-processing">🔄</div>
                @elseif($document->status === 'review_required')
                    <div class="status-icon-review">👀</div>
                @else
                    <div class="status-icon-pending">⏳</div>
                    @endif
                </div>
            <div class="status-content">
                <h3 class="status-title">
                        @if($document->status === 'approved')
                            Goedgekeurd door MARCOFIC!
                        @elseif($document->status === 'ocr_processing')
                            Wordt Verwerkt...
                        @elseif($document->status === 'review_required')
                            In Beoordeling bij Boekhouder
                        @else
                        In Wachtrij voor Verwerking
                        @endif
                </h3>
                <p class="status-description">
                        @if($document->status === 'approved')
                        Uw document is succesvol verwerkt en toegevoegd aan de administratie. Alles is in orde!
                        @elseif($document->status === 'ocr_processing')
                        Ons AI-systeem is bezig met automatische tekstherkenning en verwerking. Dit duurt meestal 1-2 minuten.
                        @elseif($document->status === 'review_required')
                        MARCOFIC controleert uw document handmatig. Dit gebeurt meestal binnen 1 uur tijdens kantooruren.
                        @else
                        Uw document staat in de wachtrij en wordt binnen enkele seconden automatisch verwerkt.
                        @endif
                </p>
                @if($document->confidence_score)
                <div class="confidence-score">
                    <div class="confidence-label">AI Vertrouwensscore</div>
                    <div class="confidence-bar-wrapper">
                        <div class="confidence-bar" style="width: {{ $document->confidence_score }}%"></div>
                    </div>
                    <div class="confidence-value">{{ $document->confidence_score }}%</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Main Content Grid --}}
    <div class="doc-content-grid">
        {{-- Left Column: Document Information --}}
        <div class="doc-info-section">
            <div class="section-card">
                <h3 class="section-title">
                    <span class="section-icon">📄</span>
                    Document Informatie
                </h3>
                <dl class="info-list">
                    <div class="info-item">
                        <dt class="info-label">Bestandsnaam</dt>
                        <dd class="info-value">{{ $document->original_filename }}</dd>
                    </div>
                    <div class="info-item">
                        <dt class="info-label">Geüpload op</dt>
                        <dd class="info-value">{{ $document->created_at->format('d-m-Y om H:i') }}</dd>
                    </div>
                    @if($document->document_date)
                    <div class="info-item">
                        <dt class="info-label">Document Datum</dt>
                        <dd class="info-value">{{ $document->document_date->format('d-m-Y') }}</dd>
                    </div>
                    @endif
                    @if($document->supplier_name)
                    <div class="info-item">
                        <dt class="info-label">Leverancier</dt>
                        <dd class="info-value">{{ $document->supplier_name }}</dd>
                    </div>
                    @endif
                    @if($document->supplier_vat)
                    <div class="info-item">
                        <dt class="info-label">BTW Nummer</dt>
                        <dd class="info-value">{{ $document->supplier_vat }}</dd>
                    </div>
                    @endif
                    <div class="info-item">
                        <dt class="info-label">Bestandstype</dt>
                        <dd class="info-value">{{ strtoupper($fileExtension) }} ({{ $fileSizeFormatted }})</dd>
                    </div>
                    @if($processingTime > 0)
                    <div class="info-item">
                        <dt class="info-label">Verwerkingstijd</dt>
                        <dd class="info-value">
                            @if($processingTime < 60)
                                {{ $processingTime }} minuten
                            @else
                                {{ round($processingTime / 60, 1) }} uur
                            @endif
                        </dd>
                    </div>
                    @endif
                </dl>
            </div>
            
            {{-- Processing Timeline --}}
            @if($document->status !== 'pending')
            <div class="section-card">
                <h3 class="section-title">
                    <span class="section-icon">⏱️</span>
                    Verwerking Timeline
                </h3>
                <div class="timeline">
                    <div class="timeline-item timeline-completed">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <div class="timeline-title">Document Ontvangen</div>
                            <div class="timeline-time">{{ $document->created_at->format('d-m-Y H:i') }}</div>
                        </div>
                    </div>
                    
                    @if($document->status !== 'pending')
                    <div class="timeline-item timeline-completed">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <div class="timeline-title">OCR Verwerking</div>
                            <div class="timeline-desc">Automatische tekstherkenning voltooid</div>
                        </div>
                    </div>
                    @endif
                    
                    @if($document->status === 'approved')
                    <div class="timeline-item timeline-completed">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <div class="timeline-title">Goedgekeurd</div>
                            <div class="timeline-desc">Document verwerkt in administratie</div>
                            <div class="timeline-time">{{ $document->updated_at->format('d-m-Y H:i') }}</div>
                        </div>
                    </div>
                    @elseif($document->status === 'review_required')
                    <div class="timeline-item timeline-active">
                        <div class="timeline-marker timeline-marker-active"></div>
                        <div class="timeline-content">
                            <div class="timeline-title">Handmatige Controle</div>
                            <div class="timeline-desc">MARCOFIC boekhouder controleert (meestal < 1 uur)</div>
                        </div>
                    </div>
                    @elseif($document->status === 'ocr_processing')
                    <div class="timeline-item timeline-active">
                        <div class="timeline-marker timeline-marker-active"></div>
                        <div class="timeline-content">
                            <div class="timeline-title">AI Verwerking</div>
                            <div class="timeline-desc">Automatische verwerking in uitvoering...</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- Right Column: Financial Details --}}
        <div class="doc-financial-section">
            @if($document->amount_incl || $document->amount_excl || $document->amount_vat)
            <div class="section-card financial-card">
                <h3 class="section-title">
                    <span class="section-icon">💰</span>
                    Financiële Details
                </h3>
                <div class="financial-breakdown">
                    @if($document->amount_excl)
                    <div class="financial-item">
                        <span class="financial-label">Excl. BTW</span>
                        <span class="financial-value">€{{ number_format($document->amount_excl, 2, ',', '.') }}</span>
    </div>
                    @endif
                    @if($document->amount_vat)
                    <div class="financial-item">
                        <span class="financial-label">BTW ({{ $document->vat_rate ?? '21' }}%)</span>
                        <span class="financial-value financial-vat">€{{ number_format($document->amount_vat, 2, ',', '.') }}</span>
                </div>
                    @endif
                    @if($document->amount_incl)
                    <div class="financial-total">
                        <span class="financial-label-total">Totaalbedrag (incl. BTW)</span>
                        <span class="financial-value-total">€{{ number_format($document->amount_incl, 2, ',', '.') }}</span>
            </div>
                    @endif
                </div>
            </div>
            @endif
            
            {{-- OCR Data if available --}}
            @if($document->ocr_data && is_array($document->ocr_data))
            <div class="section-card">
                <h3 class="section-title">
                    <span class="section-icon">🤖</span>
                    AI Extractie
                </h3>
                <div class="ocr-data">
                    @foreach($document->ocr_data as $key => $value)
                        @if(!empty($value) && !in_array($key, ['raw_text', 'full_text']))
                        <div class="ocr-item">
                            <span class="ocr-label">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                            <span class="ocr-value">{{ is_array($value) ? json_encode($value) : $value }}</span>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Additional Actions --}}
            <div class="section-card actions-card">
                <h3 class="section-title">
                    <span class="section-icon">⚡</span>
                    Acties
                </h3>
                <div class="actions-list">
                    <a href="{{ route('documents.file', $document) }}" target="_blank" class="action-link">
                        <svg class="link-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        <span>Preview in Nieuw Tabblad</span>
                    </a>
                    <a href="{{ route('documents.download', $document) }}" class="action-link">
                        <svg class="link-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        <span>Download Bestand</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.document-details-enhanced {
    max-width: 1400px;
    margin: 0 auto;
    padding: 1rem;
}

@media (min-width: 1024px) {
    .document-details-enhanced {
        padding: 2rem;
    }
}

/* Hero Header */
.doc-hero-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 1.5rem;
    padding: 2rem;
    margin-bottom: 2rem;
    color: white;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

@media (min-width: 768px) {
    .doc-hero-header {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }
}

.doc-hero-content {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    flex: 1;
}

.doc-icon-wrapper {
    flex-shrink: 0;
}

.doc-icon {
    width: 4rem;
    height: 4rem;
    border-radius: 1rem;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
}

.doc-hero-text {
    flex: 1;
    min-width: 0;
}

.doc-title {
    font-size: 1.5rem;
    font-weight: 800;
    margin: 0 0 0.75rem 0;
    word-break: break-word;
    line-height: 1.3;
}

@media (min-width: 640px) {
    .doc-title {
        font-size: 1.75rem;
    }
}

.doc-meta-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.meta-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    padding: 0.5rem 1rem;
    border-radius: 0.75rem;
    font-size: 0.875rem;
    font-weight: 600;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.badge-icon {
    width: 1rem;
    height: 1rem;
}

/* Quick Actions */
.doc-quick-actions {
    display: flex;
    gap: 1rem;
    flex-shrink: 0;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 0.75rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
    font-size: 0.9375rem;
    min-height: 44px;
}

.action-btn-primary {
    background: white;
    color: #667eea;
    border: 2px solid white;
}

.action-btn-primary:hover {
    background: rgba(255, 255, 255, 0.9);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.action-btn-secondary {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.action-btn-secondary:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-2px);
}

.action-icon {
    width: 1.25rem;
    height: 1.25rem;
}

/* Status Card */
.status-card {
    border-radius: 1.5rem;
    padding: 2rem;
    margin-bottom: 2rem;
    border: 2px solid;
}

.status-approved {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    border-color: #10b981;
    color: #065f46;
}

.dark .status-approved {
    background: linear-gradient(135deg, #064e3b 0%, #065f46 100%);
    border-color: #34d399;
    color: #a7f3d0;
}

.status-ocr_processing {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    border-color: #3b82f6;
    color: #1e40af;
}

.dark .status-ocr_processing {
    background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
    border-color: #60a5fa;
    color: #93c5fd;
}

.status-review_required {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border-color: #f59e0b;
    color: #92400e;
}

.dark .status-review_required {
    background: linear-gradient(135deg, #78350f 0%, #92400e 100%);
    border-color: #fbbf24;
    color: #fef3c7;
}

.status-pending {
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
    border-color: #6b7280;
    color: #374151;
}

.dark .status-pending {
    background: linear-gradient(135deg, #374151 0%, #4b5563 100%);
    border-color: #9ca3af;
    color: #d1d5db;
}

.status-header {
    display: flex;
    align-items: flex-start;
    gap: 1.5rem;
}

.status-icon-large {
    flex-shrink: 0;
    font-size: 3rem;
    line-height: 1;
}

.status-content {
    flex: 1;
}

.status-title {
    font-size: 1.5rem;
    font-weight: 800;
    margin: 0 0 0.75rem 0;
}

.status-description {
    font-size: 1rem;
    line-height: 1.6;
    margin: 0 0 1rem 0;
    opacity: 0.9;
}

.confidence-score {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 2px solid rgba(0, 0, 0, 0.1);
}

.dark .confidence-score {
    border-color: rgba(255, 255, 255, 0.1);
}

.confidence-label {
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    opacity: 0.8;
}

.confidence-bar-wrapper {
    background: rgba(0, 0, 0, 0.1);
    border-radius: 9999px;
    height: 0.75rem;
    overflow: hidden;
    margin-bottom: 0.5rem;
}

.dark .confidence-bar-wrapper {
    background: rgba(255, 255, 255, 0.1);
}

.confidence-bar {
    background: linear-gradient(90deg, #3b82f6, #10b981);
    height: 100%;
    border-radius: 9999px;
    transition: width 0.5s ease;
}

.confidence-value {
    font-size: 1rem;
    font-weight: 700;
}

/* Content Grid */
.doc-content-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
}

@media (min-width: 1024px) {
    .doc-content-grid {
        grid-template-columns: 1fr 1fr;
    }
}

.section-card {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 1.5rem;
    padding: 2rem;
    transition: all 0.3s;
}

.dark .section-card {
    background: #1f2937;
    border-color: #374151;
}

.section-card:hover {
    box-shadow: 0 12px 24px -8px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

.section-title {
    font-size: 1.25rem;
    font-weight: 700;
    margin: 0 0 1.5rem 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: #111827;
}

.dark .section-title {
    color: #f9fafb;
}

.section-icon {
    font-size: 1.5rem;
}

/* Info List */
.info-list {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    margin: 0;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    padding-bottom: 1.25rem;
    border-bottom: 1px solid #e5e7eb;
}

.dark .info-item {
    border-color: #374151;
}

.info-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.info-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.dark .info-label {
    color: #9ca3af;
}

.info-value {
    font-size: 1rem;
    font-weight: 600;
    color: #111827;
    word-break: break-word;
}

.dark .info-value {
    color: #f9fafb;
}

/* Financial Breakdown */
.financial-card {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    border-color: #3b82f6;
}

.dark .financial-card {
    background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
    border-color: #60a5fa;
}

.financial-breakdown {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.financial-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: white;
    border-radius: 0.75rem;
    border: 1px solid #e5e7eb;
}

.dark .financial-item {
    background: rgba(0, 0, 0, 0.2);
    border-color: rgba(255, 255, 255, 0.1);
}

.financial-label {
    font-size: 0.9375rem;
    font-weight: 600;
    color: #6b7280;
}

.dark .financial-label {
    color: #9ca3af;
}

.financial-value {
    font-size: 1.125rem;
    font-weight: 700;
    color: #111827;
}

.dark .financial-value {
    color: #f9fafb;
}

.financial-vat {
    color: #3b82f6;
}

.dark .financial-vat {
    color: #60a5fa;
}

.financial-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    border-radius: 1rem;
    margin-top: 0.5rem;
}

.financial-label-total {
    font-size: 1.125rem;
    font-weight: 700;
    color: white;
}

.financial-value-total {
    font-size: 1.75rem;
    font-weight: 800;
    color: white;
}

/* Timeline */
.timeline {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    position: relative;
    padding-left: 2rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 0.75rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e5e7eb;
}

.dark .timeline::before {
    background: #374151;
}

.timeline-item {
    display: flex;
    gap: 1rem;
    position: relative;
}

.timeline-marker {
    width: 1.5rem;
    height: 1.5rem;
    border-radius: 50%;
    background: #10b981;
    border: 3px solid white;
    flex-shrink: 0;
    position: absolute;
    left: -2rem;
    top: 0.25rem;
    z-index: 1;
}

.dark .timeline-marker {
    border-color: #1f2937;
}

.timeline-marker-active {
    background: #f59e0b;
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

.timeline-content {
    flex: 1;
    padding-top: 0.25rem;
}

.timeline-title {
    font-size: 1rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 0.25rem;
}

.dark .timeline-title {
    color: #f9fafb;
}

.timeline-desc {
    font-size: 0.875rem;
    color: #6b7280;
    margin-bottom: 0.25rem;
}

.dark .timeline-desc {
    color: #9ca3af;
}

.timeline-time {
    font-size: 0.8125rem;
    color: #9ca3af;
    font-weight: 600;
}

.dark .timeline-time {
    color: #6b7280;
}

/* OCR Data */
.ocr-data {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.ocr-item {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 0.75rem;
    border: 1px solid #e5e7eb;
}

.dark .ocr-item {
    background: #374151;
    border-color: #4b5563;
}

.ocr-label {
    font-size: 0.8125rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.dark .ocr-label {
    color: #9ca3af;
}

.ocr-value {
    font-size: 0.9375rem;
    font-weight: 600;
    color: #111827;
    word-break: break-word;
}

.dark .ocr-value {
    color: #f9fafb;
}

/* Actions Card */
.actions-card {
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    border-color: #10b981;
}

.dark .actions-card {
    background: linear-gradient(135deg, #064e3b 0%, #065f46 100%);
    border-color: #34d399;
}

.actions-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.action-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    background: white;
    border-radius: 0.75rem;
    text-decoration: none;
    color: #111827;
    font-weight: 600;
    transition: all 0.2s;
    border: 2px solid #e5e7eb;
}

.dark .action-link {
    background: rgba(0, 0, 0, 0.2);
    border-color: rgba(255, 255, 255, 0.1);
    color: #f9fafb;
}

.action-link:hover {
    background: #f9fafb;
    border-color: #10b981;
    transform: translateX(4px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.dark .action-link:hover {
    background: rgba(0, 0, 0, 0.3);
    border-color: #34d399;
}

.link-icon {
    width: 1.25rem;
    height: 1.25rem;
    flex-shrink: 0;
}

/* Mobile Optimizations */
@media (max-width: 640px) {
    .doc-hero-header {
        padding: 1.5rem;
    }
    
    .doc-hero-content {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .doc-quick-actions {
        flex-direction: column;
        width: 100%;
    }
    
    .action-btn {
        width: 100%;
        justify-content: center;
    }
    
    .section-card {
        padding: 1.5rem;
    }
    
    .status-card {
        padding: 1.5rem;
    }
}
</style>
