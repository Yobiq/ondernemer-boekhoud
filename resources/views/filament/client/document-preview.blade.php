@php
    $filePath = $document->file_path;
    $fileExtension = strtolower(pathinfo($document->original_filename, PATHINFO_EXTENSION));
    $isImage = in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    $isPdf = $fileExtension === 'pdf';
    $fileSize = \Illuminate\Support\Facades\Storage::exists($filePath) 
        ? \Illuminate\Support\Facades\Storage::size($filePath) 
        : 0;
    $fileSizeFormatted = $fileSize > 0 ? number_format($fileSize / 1024, 2) . ' KB' : 'Onbekend';
@endphp

<div class="document-preview-enhanced">
    {{-- Enhanced Header with Actions --}}
    <div class="preview-header-enhanced">
        <div class="preview-header-content">
            <div class="preview-title-section">
                <h2 class="preview-title-main">{{ $document->original_filename }}</h2>
                <div class="preview-meta-enhanced">
                    <span class="meta-badge-enhanced">
                        <svg class="meta-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        {{ $document->created_at->format('d-m-Y H:i') }}
                    </span>
                    <span class="meta-badge-enhanced">
                        <svg class="meta-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        {{ strtoupper($fileExtension) }} • {{ $fileSizeFormatted }}
                    </span>
                    @if($document->amount_incl)
                    <span class="meta-badge-enhanced meta-badge-amount">
                        <svg class="meta-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        €{{ number_format($document->amount_incl, 2, ',', '.') }}
                    </span>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="preview-actions-enhanced">
            <a href="{{ route('documents.download', $document) }}" class="preview-action-btn preview-action-download">
                <svg class="action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                <span>Download</span>
            </a>
            <button onclick="window.print()" class="preview-action-btn preview-action-print">
                <svg class="action-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                <span>Print</span>
            </button>
        </div>
    </div>

    {{-- Document Preview Content --}}
    <div class="preview-content-enhanced">
        @if($isImage)
            <div class="preview-image-container">
                <img 
                    src="{{ route('documents.file', $document) }}" 
                    alt="{{ $document->original_filename }}" 
                    class="preview-image-enhanced"
                    id="previewImage"
                    onclick="toggleImageZoom()"
                    onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'400\' height=\'300\'%3E%3Crect fill=\'%23f3f4f6\' width=\'400\' height=\'300\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%239ca3af\' font-family=\'sans-serif\' font-size=\'16\'%3EAfbeelding niet beschikbaar%3C/text%3E%3C/svg%3E';"
                />
                <div class="image-zoom-hint">
                    <svg class="hint-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"></path>
                    </svg>
                    Klik om in/uit te zoomen
                </div>
            </div>
        @elseif($isPdf)
            <div class="preview-pdf-container">
                <iframe 
                    src="{{ route('documents.file', $document) }}#toolbar=1&navpanes=1&scrollbar=1" 
                    class="preview-pdf-enhanced" 
                    frameborder="0"
                    title="PDF Preview"
                ></iframe>
                <div class="pdf-controls">
                    <a href="{{ route('documents.file', $document) }}" target="_blank" class="pdf-control-link">
                        <svg class="control-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                        Open in Nieuw Tabblad
                    </a>
                </div>
            </div>
        @else
            <div class="preview-placeholder-enhanced">
                <div class="placeholder-icon-large">📄</div>
                <h3 class="placeholder-title">Preview niet beschikbaar</h3>
                <p class="placeholder-description">
                    Dit bestandstype ({{ strtoupper($fileExtension) }}) kan niet worden gepreviewd in de browser.
                </p>
                <div class="placeholder-actions">
                    <a href="{{ route('documents.download', $document) }}" class="placeholder-btn placeholder-btn-primary">
                        <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Download Bestand
                    </a>
                    <a href="{{ route('documents.file', $document) }}" target="_blank" class="placeholder-btn placeholder-btn-secondary">
                        <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                        Open in Nieuw Tabblad
                    </a>
                </div>
            </div>
        @endif
    </div>

    {{-- Document Details Sidebar --}}
    <div class="preview-details-sidebar">
        <div class="details-card">
            <h3 class="details-title">
                <svg class="title-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Document Details
            </h3>
            <dl class="details-list">
                @if($document->supplier_name)
                <div class="detail-row">
                    <dt class="detail-label">Leverancier</dt>
                    <dd class="detail-value">{{ $document->supplier_name }}</dd>
                </div>
                @endif
                @if($document->document_date)
                <div class="detail-row">
                    <dt class="detail-label">Document Datum</dt>
                    <dd class="detail-value">{{ $document->document_date->format('d-m-Y') }}</dd>
                </div>
                @endif
                <div class="detail-row">
                    <dt class="detail-label">Status</dt>
                    <dd class="detail-value">
                        <span class="status-badge-enhanced status-{{ $document->status }}">
                            {{ match($document->status) {
                                'pending' => '⏳ In Wachtrij',
                                'ocr_processing' => '🔄 Wordt Verwerkt',
                                'review_required' => '👀 In Beoordeling',
                                'approved' => '✅ Goedgekeurd',
                                'archived' => '📦 Gearchiveerd',
                                default => $document->status,
                            } }}
                        </span>
                    </dd>
                </div>
                @if($document->document_type)
                <div class="detail-row">
                    <dt class="detail-label">Type</dt>
                    <dd class="detail-value">
                        {{ match($document->document_type) {
                            'receipt' => '🧾 Bonnetje',
                            'purchase_invoice' => '📄 Inkoopfactuur',
                            'bank_statement' => '🏦 Bankafschrift',
                            'sales_invoice' => '🧑‍💼 Verkoopfactuur',
                            default => '📁 Overig',
                        } }}
                    </dd>
                </div>
                @endif
                @if($document->amount_incl)
                <div class="detail-row detail-row-highlight">
                    <dt class="detail-label">Totaalbedrag</dt>
                    <dd class="detail-value detail-value-amount">€{{ number_format($document->amount_incl, 2, ',', '.') }}</dd>
                </div>
                @endif
            </dl>
        </div>
    </div>
</div>

<style>
.document-preview-enhanced {
    max-width: 100%;
}

/* Enhanced Header */
.preview-header-enhanced {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    border: 2px solid #e5e7eb;
    border-radius: 1rem;
    padding: 1.5rem;
    margin-bottom: 2rem;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.dark .preview-header-enhanced {
    background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
    border-color: #374151;
}

@media (min-width: 768px) {
    .preview-header-enhanced {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }
}

.preview-header-content {
    flex: 1;
    min-width: 0;
}

.preview-title-section {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.preview-title-main {
    font-size: 1.5rem;
    font-weight: 800;
    color: #111827;
    margin: 0;
    word-break: break-word;
    line-height: 1.3;
}

.dark .preview-title-main {
    color: #f9fafb;
}

@media (min-width: 640px) {
    .preview-title-main {
        font-size: 1.75rem;
    }
}

.preview-meta-enhanced {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
}

.meta-badge-enhanced {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: white;
    border: 1px solid #e5e7eb;
    padding: 0.5rem 1rem;
    border-radius: 0.75rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
}

.dark .meta-badge-enhanced {
    background: #374151;
    border-color: #4b5563;
    color: #d1d5db;
}

.meta-badge-amount {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    border-color: #3b82f6;
    color: #1e40af;
}

.dark .meta-badge-amount {
    background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
    border-color: #60a5fa;
    color: #93c5fd;
}

.meta-icon {
    width: 1rem;
    height: 1rem;
    flex-shrink: 0;
}

.preview-actions-enhanced {
    display: flex;
    gap: 0.75rem;
    flex-shrink: 0;
}

.preview-action-btn {
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
    border: 2px solid;
    cursor: pointer;
}

.preview-action-download {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
}

.preview-action-download:hover {
    background: #2563eb;
    border-color: #2563eb;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.preview-action-print {
    background: white;
    color: #374151;
    border-color: #e5e7eb;
}

.dark .preview-action-print {
    background: #374151;
    color: #f9fafb;
    border-color: #4b5563;
}

.preview-action-print:hover {
    background: #f9fafb;
    border-color: #d1d5db;
    transform: translateY(-2px);
}

.dark .preview-action-print:hover {
    background: #4b5563;
    border-color: #6b7280;
}

.action-icon {
    width: 1.25rem;
    height: 1.25rem;
}

/* Enhanced Preview Content */
.preview-content-enhanced {
    margin-bottom: 2rem;
}

.preview-image-container {
    position: relative;
    background: #f9fafb;
    border-radius: 1rem;
    padding: 2rem;
    text-align: center;
    border: 2px solid #e5e7eb;
    overflow: hidden;
}

.dark .preview-image-container {
    background: #1f2937;
    border-color: #374151;
}

.preview-image-enhanced {
    max-width: 100%;
    height: auto;
    border-radius: 0.75rem;
    box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.3);
    cursor: zoom-in;
    transition: transform 0.3s ease;
}

.preview-image-enhanced.zoomed {
    cursor: zoom-out;
    transform: scale(1.5);
}

.image-zoom-hint {
    position: absolute;
    bottom: 1rem;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    opacity: 0;
    transition: opacity 0.3s;
    pointer-events: none;
}

.preview-image-container:hover .image-zoom-hint {
    opacity: 1;
}

.hint-icon {
    width: 1rem;
    height: 1rem;
}

.preview-pdf-container {
    background: #f9fafb;
    border-radius: 1rem;
    padding: 1rem;
    border: 2px solid #e5e7eb;
    overflow: hidden;
}

.dark .preview-pdf-container {
    background: #1f2937;
    border-color: #374151;
}

.preview-pdf-enhanced {
    width: 100%;
    min-height: 70vh;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.pdf-controls {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
    text-align: center;
}

.dark .pdf-controls {
    border-color: #374151;
}

.pdf-control-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: #3b82f6;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9375rem;
    transition: color 0.2s;
}

.pdf-control-link:hover {
    color: #2563eb;
    text-decoration: underline;
}

.control-icon {
    width: 1.125rem;
    height: 1.125rem;
}

/* Enhanced Placeholder */
.preview-placeholder-enhanced {
    text-align: center;
    padding: 4rem 2rem;
    background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
    border-radius: 1rem;
    border: 2px dashed #d1d5db;
}

.dark .preview-placeholder-enhanced {
    background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
    border-color: #4b5563;
}

.placeholder-icon-large {
    font-size: 5rem;
    margin-bottom: 1.5rem;
    opacity: 0.5;
}

.placeholder-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
    margin: 0 0 0.75rem 0;
}

.dark .placeholder-title {
    color: #f9fafb;
}

.placeholder-description {
    font-size: 1rem;
    color: #6b7280;
    margin: 0 0 2rem 0;
    line-height: 1.6;
}

.dark .placeholder-description {
    color: #9ca3af;
}

.placeholder-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    justify-content: center;
}

.placeholder-btn {
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
    border: 2px solid;
}

.placeholder-btn-primary {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
}

.placeholder-btn-primary:hover {
    background: #2563eb;
    border-color: #2563eb;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.placeholder-btn-secondary {
    background: white;
    color: #374151;
    border-color: #e5e7eb;
}

.dark .placeholder-btn-secondary {
    background: #374151;
    color: #f9fafb;
    border-color: #4b5563;
}

.placeholder-btn-secondary:hover {
    background: #f9fafb;
    border-color: #d1d5db;
    transform: translateY(-2px);
}

.dark .placeholder-btn-secondary:hover {
    background: #4b5563;
    border-color: #6b7280;
}

.btn-icon {
    width: 1.25rem;
    height: 1.25rem;
}

/* Details Sidebar */
.preview-details-sidebar {
    margin-top: 2rem;
}

.details-card {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 1rem;
    padding: 1.5rem;
}

.dark .details-card {
    background: #1f2937;
    border-color: #374151;
}

.details-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: #111827;
    margin: 0 0 1.5rem 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.dark .details-title {
    color: #f9fafb;
}

.title-icon {
    width: 1.25rem;
    height: 1.25rem;
}

.details-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin: 0;
}

.detail-row {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.dark .detail-row {
    border-color: #374151;
}

.detail-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.detail-row-highlight {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    padding: 1rem;
    border-radius: 0.75rem;
    border: 1px solid #3b82f6;
    margin-top: 0.5rem;
}

.dark .detail-row-highlight {
    background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
    border-color: #60a5fa;
}

.detail-label {
    font-size: 0.8125rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.dark .detail-label {
    color: #9ca3af;
}

.detail-value {
    font-size: 1rem;
    font-weight: 600;
    color: #111827;
    word-break: break-word;
}

.dark .detail-value {
    color: #f9fafb;
}

.detail-value-amount {
    font-size: 1.5rem;
    font-weight: 800;
    color: #3b82f6;
}

.dark .detail-value-amount {
    color: #60a5fa;
}

.status-badge-enhanced {
    display: inline-block;
    padding: 0.375rem 0.875rem;
    border-radius: 9999px;
    font-size: 0.875rem;
    font-weight: 600;
}

.status-pending {
    background: #f3f4f6;
    color: #6b7280;
}

.status-ocr_processing {
    background: #dbeafe;
    color: #1e40af;
}

.status-review_required {
    background: #fef3c7;
    color: #92400e;
}

.status-approved {
    background: #d1fae5;
    color: #065f46;
}

.status-archived {
    background: #fee2e2;
    color: #991b1b;
}

/* Mobile Optimizations */
@media (max-width: 640px) {
    .preview-header-enhanced {
        padding: 1rem;
    }
    
    .preview-actions-enhanced {
        flex-direction: column;
        width: 100%;
    }
    
    .preview-action-btn {
        width: 100%;
        justify-content: center;
    }
    
    .preview-image-container {
        padding: 1rem;
    }
    
    .preview-pdf-enhanced {
        min-height: 60vh;
    }
}
</style>

<script>
function toggleImageZoom() {
    const img = document.getElementById('previewImage');
    if (img) {
        img.classList.toggle('zoomed');
    }
}
</script>
