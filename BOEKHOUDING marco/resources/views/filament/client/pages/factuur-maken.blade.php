<x-filament-panels::page>
    @php
        $user = auth()->user();
        $client = $user->client ?? null;
    @endphp

    <div class="invoice-page-container">
        {{-- Hero Header --}}
        <div class="invoice-hero">
            <div class="invoice-hero-content">
                <div>
                    <h1 class="invoice-hero-title">📄 Factuur Beheer</h1>
                    <p class="invoice-hero-subtitle">Maak en beheer uw verkoopfacturen</p>
                </div>
                <div class="invoice-hero-badge">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>Nederlandse BTW</span>
                </div>
            </div>
        </div>

        {{-- Tabs Navigation --}}
        <div class="invoice-tabs">
            <button 
                wire:click="switchTab('create')" 
                class="invoice-tab {{ $activeTab === 'create' ? 'active' : '' }}"
                type="button">
                <svg class="tab-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Nieuwe Factuur</span>
            </button>
            <button 
                wire:click="switchTab('list')" 
                class="invoice-tab {{ $activeTab === 'list' ? 'active' : '' }}"
                type="button">
                <svg class="tab-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span>Mijn Facturen</span>
                @php
                    $invoiceCount = $this->getInvoices()->count();
                @endphp
                @if($invoiceCount > 0)
                <span class="tab-badge">{{ $invoiceCount }}</span>
                @endif
            </button>
        </div>

        {{-- Tab Content: Create Invoice --}}
        @if($activeTab === 'create')
        <div class="invoice-tab-content" wire:key="invoice-create-tab">
            {{-- Created Invoice Preview (Shows when viewing existing invoice) --}}
            @if($this->createdInvoice)
            <div class="invoice-created-preview-section">
            <div class="invoice-created-preview-card">
                <div class="invoice-created-preview-header">
                    <div>
                        <h2 class="created-preview-title">📄 Factuur Bekijken</h2>
                        <p class="created-preview-subtitle">Factuurnummer: <strong>{{ $this->createdInvoice['invoice_number'] ?? 'N/A' }}</strong></p>
                    </div>
                    <button wire:click="clearPreview" type="button" class="preview-close-btn">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Sluiten
                    </button>
                </div>
                
                <div class="invoice-created-preview-content">
                    @include('filament.client.components.invoice-preview-full', ['data' => $this->createdInvoice])
                </div>
            </div>
            @else
            {{-- Smart Wizard Form --}}
            <div class="invoice-wizard-section" wire:key="invoice-wizard-section">
                <div class="invoice-wizard-form">
                {{ $this->form }}
                </div>
        </div>
            @endif
        </div>
        @endif

        {{-- Tab Content: Invoice List --}}
        @if($activeTab === 'list')
        <div class="invoice-tab-content">
            <div class="invoice-list-section">
                @php
                    $invoices = $this->getInvoices();
                @endphp
                
                @if($invoices->isEmpty())
                <div class="invoice-empty-state">
                    <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="empty-title">Nog geen facturen</h3>
                    <p class="empty-text">Maak uw eerste factuur om te beginnen</p>
                    <button wire:click="switchTab('create')" class="empty-action-btn" type="button">
                        Nieuwe Factuur Maken
                    </button>
                </div>
                @else
                <div class="invoice-list-header">
                    <div class="header-left">
                        <div>
                            <h2 class="list-title">Mijn Facturen</h2>
                            <p class="list-subtitle">{{ $invoices->count() }} {{ $invoices->count() === 1 ? 'factuur' : 'facturen' }} gevonden</p>
                        </div>
                    </div>
                    <div class="header-right">
                        <div class="view-mode-toggle">
                            <button 
                                wire:click="switchViewMode('table')" 
                                class="view-mode-btn {{ $viewMode === 'table' ? 'active' : '' }}"
                                type="button"
                                title="Tabel weergave">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                            </button>
                            <button 
                                wire:click="switchViewMode('grid')" 
                                class="view-mode-btn {{ $viewMode === 'grid' ? 'active' : '' }}"
                                type="button"
                                title="Grid weergave">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                                </svg>
                            </button>
                        </div>
                        <button wire:click="switchTab('create')" class="btn-new-invoice" type="button">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Nieuwe Factuur
                        </button>
                    </div>
                </div>

                {{-- Filters and Search --}}
                <div class="invoice-filters">
                    <div class="filter-search">
                        <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <input 
                            type="text" 
                            wire:model.live.debounce.300ms="search"
                            placeholder="Zoek op factuurnummer, klant..." 
                            class="search-input">
                    </div>
                    <select wire:model.live="statusFilter" class="filter-select">
                        <option value="all">Alle statussen</option>
                        <option value="pending">In behandeling</option>
                        <option value="approved">Goedgekeurd</option>
                        <option value="review_required">Review nodig</option>
                    </select>
                </div>
                
                @if($viewMode === 'table')
                {{-- Table View (Default) --}}
                <div class="invoice-table-wrapper">
                    <div class="invoice-table-container">
                        <table class="invoice-table">
                            <thead>
                                <tr>
                                    <th class="col-number">Factuurnummer</th>
                                    <th class="col-customer">Klant</th>
                                    <th class="col-date">Datum</th>
                                    <th class="col-status">Status</th>
                                    <th class="col-amount">Subtotaal</th>
                                    <th class="col-amount">BTW</th>
                                    <th class="col-total">Totaal</th>
                                    <th class="col-actions">Acties</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoices as $invoice)
                                    @php
                                        $invoiceData = $invoice->ocr_data ?? [];
                                        $invoiceNumber = $invoiceData['invoice_number'] ?? ($invoice->original_filename ? str_replace(['Factuur_', '.pdf', '.png', '.jpg', '.jpeg'], '', $invoice->original_filename) : 'INV-' . $invoice->id);
                                        $invoiceNumberFull = $invoiceNumber; // Keep full version for tooltip
                                        $invoiceNumberDisplay = strlen($invoiceNumber) > 20 ? substr($invoiceNumber, 0, 17) . '...' : $invoiceNumber;
                                        $customerName = $invoiceData['customer_name'] ?? ($invoice->supplier_name ?? 'Onbekend');
                                        $customerNameFull = $customerName; // Keep full version for tooltip
                                        $customerNameDisplay = strlen($customerName) > 30 ? substr($customerName, 0, 27) . '...' : $customerName;
                                        $invoiceDate = $invoice->document_date ? $invoice->document_date->format('d-m-Y') : ($invoice->created_at ? $invoice->created_at->format('d-m-Y') : 'N/A');
                                        $total = $invoice->amount_incl ?? 0;
                                        $subtotal = $invoice->amount_excl ?? 0;
                                        $vat = $invoice->amount_vat ?? 0;
                                    @endphp
                                    <tr class="table-row">
                                        <td class="col-number">
                                            <div class="table-cell-content">
                                                <div class="invoice-number-badge" title="{{ $invoiceNumberFull }}">
                                                    <svg class="invoice-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                    <span class="table-invoice-number" title="{{ $invoiceNumberFull }}">{{ $invoiceNumberDisplay }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="col-customer">
                                            <div class="table-cell-content">
                                                <div class="table-customer" title="{{ $customerNameFull }}">{{ $customerNameDisplay }}</div>
                                            </div>
                                        </td>
                                        <td class="col-date">
                                            <div class="table-cell-content">
                                                <div class="table-date">{{ $invoiceDate }}</div>
                                            </div>
                                        </td>
                                        <td class="col-status">
                                            <div class="table-cell-content">
                                                <div class="flex flex-col gap-1">
                                                @switch($invoice->status)
                                                    @case('approved')
                                                        <span class="status-badge status-approved">
                                                            <svg class="status-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                            </svg>
                                                            Goedgekeurd
                                                        </span>
                                                        @break
                                                    @case('pending')
                                                        <span class="status-badge status-pending">
                                                            <svg class="status-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                            </svg>
                                                            In behandeling
                                                        </span>
                                                        @break
                                                    @case('review_required')
                                                        <span class="status-badge status-review">
                                                            <svg class="status-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                                            </svg>
                                                            Review nodig
                                                        </span>
                                                        @break
                                                    @default
                                                        <span class="status-badge">{{ ucfirst($invoice->status) }}</span>
                                                @endswitch
                                                    
                                                    @if($invoice->is_paid)
                                                        <span class="status-badge status-paid" style="background: #10b981; color: white; font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                                            <svg class="status-icon" style="width: 0.875rem; height: 0.875rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                            </svg>
                                                            Betaald
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="col-amount">
                                            <div class="table-cell-content justify-end">
                                                <div class="table-amount"><span class="currency-inline">€{{ number_format($subtotal, 2, ',', '.') }}</span></div>
                                            </div>
                                        </td>
                                        <td class="col-amount">
                                            <div class="table-cell-content justify-end">
                                                <div class="table-amount"><span class="currency-inline">€{{ number_format($vat, 2, ',', '.') }}</span></div>
                                            </div>
                                        </td>
                                        <td class="col-total">
                                            <div class="table-cell-content justify-end">
                                                <div class="table-amount table-amount-total"><span class="currency-inline">€{{ number_format($total, 2, ',', '.') }}</span></div>
                                            </div>
                                        </td>
                                        <td class="col-actions">
                                            <div class="table-cell-content">
                                                <div class="table-actions">
                                                    <a 
                                                        href="{{ route('invoices.pdf', $invoice) }}" 
                                                        class="table-btn table-btn-download" 
                                                        download
                                                        title="Download PDF"
                                                        onclick="event.stopPropagation();">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                        </svg>
                                                        <span class="btn-label">PDF</span>
                                                    </a>
                                                    <button 
                                                        wire:click="viewInvoice({{ $invoice->id }})" 
                                                        class="table-btn table-btn-view" 
                                                        type="button" 
                                                        title="Bekijken">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                        </svg>
                                                        <span class="btn-label">Bekijk</span>
                                                    </button>
                                                    @if($invoice->is_paid)
                                                        <button 
                                                            wire:click="markAsUnpaid({{ $invoice->id }})" 
                                                            class="table-btn table-btn-warning" 
                                                            type="button" 
                                                            title="Markeer als onbetaald"
                                                            onclick="return confirm('Weet u zeker dat u deze factuur als onbetaald wilt markeren?')">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                            </svg>
                                                            <span class="btn-label">Onbetaald</span>
                                                        </button>
                                                    @else
                                                        <button 
                                                            wire:click="markAsPaid({{ $invoice->id }})" 
                                                            class="table-btn table-btn-success" 
                                                            type="button" 
                                                            title="Markeer als betaald"
                                                            onclick="return confirm('Weet u zeker dat u deze factuur als betaald wilt markeren?')">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                            </svg>
                                                            <span class="btn-label">Betaald</span>
                                                        </button>
                                                    @endif
                                                    <button 
                                                        wire:click="deleteInvoice({{ $invoice->id }})" 
                                                        class="table-btn table-btn-danger" 
                                                        type="button" 
                                                        title="Verwijderen"
                                                        onclick="return confirm('Weet u zeker dat u deze factuur wilt verwijderen? Deze actie kan niet ongedaan worden gemaakt.')">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                        <span class="btn-label">Verwijder</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="table-empty">
                                            <div class="invoice-empty-state">
                                                <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                @if($search || $statusFilter !== 'all')
                                                    <h3 class="empty-title">Geen facturen gevonden</h3>
                                                    <p class="empty-text">Probeer andere zoektermen of filters</p>
                                                    <button wire:click="$set('search', ''); $set('statusFilter', 'all')" class="empty-action-btn" type="button">
                                                        Filters wissen
                                                    </button>
                                                @else
                                                    <h3 class="empty-title">Nog geen facturen aangemaakt</h3>
                                                    <p class="empty-text">Maak uw eerste verkoopfactuur aan om te beginnen</p>
                                                    <button wire:click="switchTab('create')" class="empty-action-btn" type="button">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                        </svg>
                                                        Nieuwe Factuur Maken
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @else
                {{-- Grid/List Card View --}}
                <div class="invoice-list-container {{ $viewMode === 'list' ? 'list-view' : 'grid-view' }}">
                    @forelse($invoices as $invoice)
                        @php
                            $invoiceData = $invoice->ocr_data ?? [];
                            $invoiceNumber = $invoiceData['invoice_number'] ?? ($invoice->original_filename ? str_replace(['Factuur_', '.pdf'], '', $invoice->original_filename) : 'INV-' . $invoice->id);
                            $customerName = $invoiceData['customer_name'] ?? ($invoice->supplier_name ?? 'Onbekend');
                            $invoiceDate = $invoice->document_date ? $invoice->document_date->format('d-m-Y') : ($invoice->created_at ? $invoice->created_at->format('d-m-Y') : 'N/A');
                            $total = $invoice->amount_incl ?? 0;
                            $subtotal = $invoice->amount_excl ?? 0;
                            $vat = $invoice->amount_vat ?? 0;
                            $itemCount = count($invoiceData['items'] ?? []);
                        @endphp
                        <div class="invoice-card">
                            {{-- Header: Invoice Number & Status --}}
                            <div class="invoice-card-top">
                                <div class="invoice-number-block">
                                    <span class="invoice-number">{{ $invoiceNumber }}</span>
                                    <span class="invoice-date">{{ $invoiceDate }}</span>
                                </div>
                                <div class="invoice-status">
                                    @switch($invoice->status)
                                        @case('approved')
                                            <span class="status-badge status-approved">Goedgekeurd</span>
                                            @break
                                        @case('pending')
                                            <span class="status-badge status-pending">In behandeling</span>
                                            @break
                                        @case('review_required')
                                            <span class="status-badge status-review">Review nodig</span>
                                            @break
                                        @default
                                            <span class="status-badge">{{ ucfirst($invoice->status) }}</span>
                                    @endswitch
                                    
                                    @if($invoice->is_paid)
                                        <span class="status-badge" style="background: #10b981; color: white; margin-top: 0.25rem;">
                                            <svg class="status-icon" style="width: 0.875rem; height: 0.875rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Betaald
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Main Content --}}
                            <div class="invoice-card-content">
                                <div class="invoice-info-row">
                                    <span class="info-label">Klant:</span>
                                    <span class="info-value">{{ $customerName }}</span>
                                </div>
                                @if($itemCount > 0)
                                <div class="invoice-info-row">
                                    <span class="info-label">Items:</span>
                                    <span class="info-value">{{ $itemCount }} {{ $itemCount === 1 ? 'item' : 'items' }}</span>
                                </div>
                                @endif
                            </div>

                            {{-- Financial Summary --}}
                            <div class="invoice-card-amounts">
                                @if($subtotal > 0 || $vat > 0)
                                <div class="amount-row">
                                    <span>Subtotaal</span>
                                    <span>€ {{ number_format($subtotal, 2, ',', '.') }}</span>
                                </div>
                                @if($vat > 0)
                                <div class="amount-row">
                                    <span>BTW</span>
                                    <span>€ {{ number_format($vat, 2, ',', '.') }}</span>
                                </div>
                                @endif
                                @endif
                                <div class="amount-row amount-total">
                                    <span>Totaal</span>
                                    <span>€ {{ number_format($total, 2, ',', '.') }}</span>
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="invoice-card-footer">
                                <a href="{{ route('invoices.pdf', $invoice) }}" class="btn-download" download>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Download PDF
                                </a>
                                <button wire:click="viewInvoice({{ $invoice->id }})" class="btn-view" type="button">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    Bekijken
                                </button>
                                @if($invoice->is_paid)
                                    <button wire:click="markAsUnpaid({{ $invoice->id }})" class="btn-warning" type="button" style="background: #f59e0b;" onclick="return confirm('Weet u zeker dat u deze factuur als onbetaald wilt markeren?')">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        Onbetaald
                                    </button>
                                @else
                                    <button wire:click="markAsPaid({{ $invoice->id }})" class="btn-success" type="button" style="background: #10b981;" onclick="return confirm('Weet u zeker dat u deze factuur als betaald wilt markeren?')">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Betaald
                                    </button>
                                @endif
                                <button wire:click="deleteInvoice({{ $invoice->id }})" class="btn-danger" type="button" style="background: #ef4444;" onclick="return confirm('Weet u zeker dat u deze factuur wilt verwijderen?')">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Verwijder
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="invoice-empty-state">
                            <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <h3 class="empty-title">Geen facturen gevonden</h3>
                            <p class="empty-text">Probeer andere zoektermen of filters</p>
                            @if($search || $statusFilter !== 'all')
                            <button wire:click="$set('search', ''); $set('statusFilter', 'all')" class="empty-action-btn" type="button">
                                Filters wissen
                            </button>
                            @endif
                        </div>
                    @endforelse
                </div>
                @endif
                @endif
            </div>
        </div>
        @endif
    </div>

    <style>
        /* Prevent horizontal overflow globally */
        body {
            overflow-x: hidden !important;
            max-width: 100vw !important;
        }

        .fi-main {
            overflow-x: hidden !important;
            max-width: 100% !important;
        }

        .fi-body {
            overflow-x: hidden !important;
            max-width: 100% !important;
        }

        /* Invoice Page Styles - Unified Design System */
        .invoice-page-container {
            --primary-bg: #ffffff;
            --secondary-bg: #f8fafc;
            --card-bg: #ffffff;
            --border-color: #e2e8f0;
            --text-primary: #0f172a;
            --text-secondary: #64748b;
            --text-tertiary: #94a3b8;
            --accent-blue: #3b82f6;
            --accent-green: #10b981;
            --accent-purple: #8b5cf6;
            --accent-red: #ef4444;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --radius-sm: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --radius-xl: 1rem;
            --radius-2xl: 1.5rem;
            padding: 0.75rem;
            background: var(--secondary-bg);
            min-height: 100vh;
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            box-sizing: border-box;
            overflow-x: hidden;
            position: relative;
        }

        @media (min-width: 640px) {
            .invoice-page-container {
                padding: 1rem;
            }
        }

        @media (min-width: 1024px) {
            .invoice-page-container {
                padding: 1.5rem 2rem;
                max-width: 1600px;
            }
        }

        @media (min-width: 1280px) {
            .invoice-page-container {
                padding: 2rem 3rem;
            }
        }

        .dark .invoice-page-container {
            --primary-bg: #0f172a;
            --secondary-bg: #1e293b;
            --card-bg: #1e293b;
            --border-color: #334155;
            --text-primary: #f1f5f9;
            --text-secondary: #cbd5e1;
            --text-tertiary: #94a3b8;
        }

        /* Hero Section */
        .invoice-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            color: white;
            box-shadow: var(--shadow-lg);
        }

        @media (min-width: 640px) {
            .invoice-hero {
                padding: 2rem;
            }
        }

        .invoice-hero-content {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        @media (min-width: 768px) {
            .invoice-hero-content {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
            }
        }

        .invoice-hero-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
            line-height: 1.2;
        }

        @media (min-width: 640px) {
            .invoice-hero-title {
                font-size: 1.875rem;
            }
        }

        .invoice-hero-subtitle {
            font-size: 0.875rem;
            opacity: 0.9;
            margin: 0.5rem 0 0 0;
        }

        @media (min-width: 640px) {
            .invoice-hero-subtitle {
                font-size: 1rem;
            }
        }

        .invoice-hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            padding: 0.5rem 1rem;
            border-radius: var(--radius-md);
            font-size: 0.875rem;
            font-weight: 600;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        /* Tabs Navigation */
        .invoice-tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 2rem;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 0;
            background: var(--card-bg);
            padding: 0.5rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
        }

        .invoice-tab {
            display: inline-flex;
            align-items: center;
            gap: 0.625rem;
            padding: 0.875rem 1.75rem;
            background: transparent;
            border: none;
            border-bottom: 3px solid transparent;
            color: var(--text-secondary);
            font-weight: 600;
            font-size: 0.9375rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            margin-bottom: -2px;
            border-radius: var(--radius-md) var(--radius-md) 0 0;
        }

        .invoice-tab:hover {
            color: var(--accent-blue);
            background: var(--secondary-bg);
            transform: translateY(-2px);
        }

        .invoice-tab.active {
            color: var(--accent-blue);
            border-bottom-color: var(--accent-blue);
            background: var(--secondary-bg);
            box-shadow: var(--shadow-sm);
        }

        .invoice-tab.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--accent-blue);
            border-radius: 2px 2px 0 0;
        }

        .tab-icon {
            width: 1.25rem;
            height: 1.25rem;
        }

        .tab-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 1.5rem;
            height: 1.5rem;
            padding: 0 0.5rem;
            background: var(--accent-blue);
            color: white;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .invoice-tab-content {
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        /* Form Section */
        .invoice-form-section {
            width: 100%;
            margin-bottom: 2rem;
        }

        .invoice-form {
            background: var(--card-bg);
            border: 2px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
        }

        /* Wizard Section */
        .invoice-wizard-section {
            width: 100%;
            margin-bottom: 2rem;
        }

        .invoice-wizard-form {
            background: var(--card-bg);
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
        }

        @media (min-width: 640px) {
            .invoice-wizard-form {
                padding: 2rem;
            }
        }

        /* Wizard Step Styling */
        .invoice-wizard-form :deep(.fi-wizard) {
            background: transparent;
        }

        .invoice-wizard-form :deep(.fi-wizard-step) {
            padding: 1.5rem 0;
        }

        .invoice-wizard-form :deep(.fi-wizard-step-header) {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
        }

        .invoice-wizard-form :deep(.fi-wizard-step-label) {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .invoice-wizard-form :deep(.fi-wizard-step-description) {
            font-size: 0.9375rem;
            color: var(--text-secondary);
            margin-top: 0.25rem;
        }

        .invoice-wizard-form :deep(.fi-wizard-step-icon) {
            width: 2rem;
            height: 2rem;
            color: var(--accent-blue);
        }

        /* Wizard Navigation */
        .invoice-wizard-form :deep(.fi-wizard-actions) {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 2px solid var(--border-color);
        }

        .invoice-wizard-form :deep(.fi-btn) {
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border-radius: var(--radius-md);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .invoice-wizard-form :deep(.fi-btn-primary) {
            background: linear-gradient(135deg, var(--accent-blue) 0%, var(--accent-purple) 100%);
            border: none;
            box-shadow: var(--shadow-md);
        }

        .invoice-wizard-form :deep(.fi-btn-primary:hover) {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        /* Section Styling in Wizard */
        .invoice-wizard-form :deep(.fi-section) {
            background: var(--secondary-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s;
        }

        .invoice-wizard-form :deep(.fi-section:hover) {
            border-color: var(--accent-blue);
            box-shadow: 0 4px 12px -4px rgba(59, 130, 246, 0.1);
        }

        .invoice-wizard-form :deep(.fi-section-header) {
            margin-bottom: 1.25rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
            position: relative;
        }

        .invoice-wizard-form :deep(.fi-section-header)::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 60px;
            height: 2px;
            background: linear-gradient(90deg, var(--accent-blue), var(--accent-purple));
            border-radius: 2px;
        }

        .invoice-wizard-form :deep(.fi-section-header-label) {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .invoice-wizard-form :deep(.fi-section-header-icon) {
            width: 1.5rem;
            height: 1.5rem;
            color: var(--accent-blue);
        }

        .invoice-wizard-form :deep(.fi-section-header-description) {
            font-size: 0.9375rem;
            color: var(--text-secondary);
            margin-top: 0.5rem;
            line-height: 1.5;
        }

        /* Form Enhancements */
        .invoice-form :deep(.fi-section) {
            margin-bottom: 1.5rem;
        }

        .invoice-form :deep(.fi-section-header) {
            padding-bottom: 0.75rem;
            margin-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
        }

        .invoice-form :deep(.fi-section-header-label) {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .invoice-form :deep(.fi-input-wrp) {
            margin-bottom: 1rem;
        }

        /* Enhanced Repeater/Items Styling */
        .invoice-wizard-form :deep(.fi-repeater),
        .invoice-form :deep(.fi-repeater) {
            margin-top: 1.5rem;
        }

        .invoice-wizard-form :deep(.fi-repeater-item),
        .invoice-form :deep(.fi-repeater-item) {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.9) 100%);
            border: 2px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            margin-bottom: 1.25rem;
            box-shadow: 0 2px 8px -2px rgba(0, 0, 0, 0.08), 0 0 0 1px rgba(0, 0, 0, 0.04) inset;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .dark .invoice-wizard-form :deep(.fi-repeater-item),
        .dark .invoice-form :deep(.fi-repeater-item) {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.8) 0%, rgba(30, 41, 59, 0.7) 100%);
            border-color: rgba(51, 65, 85, 0.8);
            box-shadow: 0 2px 8px -2px rgba(0, 0, 0, 0.2), 0 0 0 1px rgba(0, 0, 0, 0.1) inset;
        }

        .invoice-wizard-form :deep(.fi-repeater-item:hover),
        .invoice-form :deep(.fi-repeater-item:hover) {
            transform: translateY(-2px);
            border-color: var(--accent-blue);
            box-shadow: 0 8px 16px -4px rgba(59, 130, 246, 0.2), 0 0 0 1px rgba(59, 130, 246, 0.1) inset;
        }

        .dark .invoice-wizard-form :deep(.fi-repeater-item:hover),
        .dark .invoice-form :deep(.fi-repeater-item:hover) {
            border-color: #60a5fa;
            box-shadow: 0 8px 16px -4px rgba(96, 165, 250, 0.3), 0 0 0 1px rgba(96, 165, 250, 0.15) inset;
        }

        /* Repeater Item Header */
        .invoice-wizard-form :deep(.fi-repeater-item-header),
        .invoice-form :deep(.fi-repeater-item-header) {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.25rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
        }

        /* Repeater Item Actions */
        .invoice-wizard-form :deep(.fi-repeater-item-actions),
        .invoice-form :deep(.fi-repeater-item-actions) {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .invoice-wizard-form :deep(.fi-repeater-item-action),
        .invoice-form :deep(.fi-repeater-item-action) {
            width: 2rem;
            height: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius-md);
            transition: all 0.2s;
            color: var(--text-secondary);
        }

        .invoice-wizard-form :deep(.fi-repeater-item-action:hover),
        .invoice-form :deep(.fi-repeater-item-action:hover) {
            background: var(--secondary-bg);
            color: var(--accent-blue);
            transform: scale(1.1);
        }

        .invoice-wizard-form :deep(.fi-repeater-item-action[data-delete-button]),
        .invoice-form :deep(.fi-repeater-item-action[data-delete-button]) {
            color: #ef4444;
        }

        .invoice-wizard-form :deep(.fi-repeater-item-action[data-delete-button]:hover),
        .invoice-form :deep(.fi-repeater-item-action[data-delete-button]:hover) {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
        }

        /* Enhanced Input Fields in Repeater */
        .invoice-wizard-form :deep(.fi-repeater-item .fi-input-wrp),
        .invoice-form :deep(.fi-repeater-item .fi-input-wrp) {
            margin-bottom: 1.25rem;
        }

        .invoice-wizard-form :deep(.fi-repeater-item .fi-input-label),
        .invoice-form :deep(.fi-repeater-item .fi-input-label) {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            display: block;
            letter-spacing: 0.01em;
        }

        .invoice-wizard-form :deep(.fi-repeater-item input),
        .invoice-wizard-form :deep(.fi-repeater-item textarea),
        .invoice-wizard-form :deep(.fi-repeater-item select),
        .invoice-form :deep(.fi-repeater-item input),
        .invoice-form :deep(.fi-repeater-item textarea),
        .invoice-form :deep(.fi-repeater-item select) {
            background: var(--card-bg);
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 0.75rem 1rem;
            font-size: 0.9375rem;
            color: var(--text-primary);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            width: 100%;
            font-weight: 500;
        }

        .invoice-wizard-form :deep(.fi-repeater-item input:hover),
        .invoice-wizard-form :deep(.fi-repeater-item textarea:hover),
        .invoice-wizard-form :deep(.fi-repeater-item select:hover),
        .invoice-form :deep(.fi-repeater-item input:hover),
        .invoice-form :deep(.fi-repeater-item textarea:hover),
        .invoice-form :deep(.fi-repeater-item select:hover) {
            border-color: rgba(59, 130, 246, 0.5);
        }

        .invoice-wizard-form :deep(.fi-repeater-item input:focus),
        .invoice-wizard-form :deep(.fi-repeater-item textarea:focus),
        .invoice-wizard-form :deep(.fi-repeater-item select:focus),
        .invoice-form :deep(.fi-repeater-item input:focus),
        .invoice-form :deep(.fi-repeater-item textarea:focus),
        .invoice-form :deep(.fi-repeater-item select:focus) {
            outline: none;
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1), 0 2px 4px -2px rgba(59, 130, 246, 0.2);
            transform: translateY(-1px);
        }

        /* Currency Input Prefix/Suffix Styling */
        .invoice-wizard-form :deep(.fi-repeater-item .fi-input-prefix),
        .invoice-wizard-form :deep(.fi-repeater-item .fi-input-suffix),
        .invoice-form :deep(.fi-repeater-item .fi-input-prefix),
        .invoice-form :deep(.fi-repeater-item .fi-input-suffix) {
            color: var(--text-secondary);
            font-weight: 600;
            font-size: 0.875rem;
        }

        /* Textarea Specific */
        .invoice-wizard-form :deep(.fi-repeater-item textarea),
        .invoice-form :deep(.fi-repeater-item textarea) {
            resize: vertical;
            min-height: 80px;
            line-height: 1.6;
        }

        /* Item Total Display - Enhanced */
        .invoice-wizard-form :deep(.item-total-display),
        .invoice-form :deep(.item-total-display) {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
            border: 2px solid rgba(59, 130, 246, 0.2);
            font-weight: 700;
            color: var(--accent-blue);
        }

        .dark .invoice-wizard-form :deep(.item-total-display),
        .dark .invoice-form :deep(.item-total-display) {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.15) 0%, rgba(139, 92, 246, 0.15) 100%);
            border-color: rgba(96, 165, 250, 0.3);
            color: #60a5fa;
        }

        /* Add Item Button - Enhanced */
        .invoice-wizard-form :deep(.fi-repeater-add-action),
        .invoice-form :deep(.fi-repeater-add-action) {
            width: 100%;
            margin-top: 1rem;
        }

        .invoice-wizard-form :deep(.fi-repeater-add-action button),
        .invoice-form :deep(.fi-repeater-add-action button) {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 1rem 1.5rem;
            background: linear-gradient(135deg, var(--accent-blue) 0%, var(--accent-purple) 100%);
            color: white;
            border: none;
            border-radius: var(--radius-lg);
            font-weight: 600;
            font-size: 0.9375rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px -4px rgba(59, 130, 246, 0.4);
        }

        .invoice-wizard-form :deep(.fi-repeater-add-action button:hover),
        .invoice-form :deep(.fi-repeater-add-action button:hover) {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px -4px rgba(59, 130, 246, 0.5);
        }

        .invoice-wizard-form :deep(.fi-repeater-add-action button:active),
        .invoice-form :deep(.fi-repeater-add-action button:active) {
            transform: translateY(0);
        }

        /* Repeater Grid Layout */
        .invoice-wizard-form :deep(.fi-repeater-item .fi-fo-grid),
        .invoice-form :deep(.fi-repeater-item .fi-fo-grid) {
            gap: 1rem;
        }

        /* Description Field - Full Width */
        .invoice-wizard-form :deep(.fi-repeater-item [data-field-wrapper="description"]),
        .invoice-form :deep(.fi-repeater-item [data-field-wrapper="description"]) {
            grid-column: 1 / -1;
        }

        /* Enhanced Select/Dropdown Styling */
        .invoice-wizard-form :deep(.fi-repeater-item select),
        .invoice-form :deep(.fi-repeater-item select) {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
            background-position: right 0.75rem center;
            background-repeat: no-repeat;
            background-size: 1.25rem;
            padding-right: 2.5rem;
        }

        .dark .invoice-wizard-form :deep(.fi-repeater-item select),
        .dark .invoice-form :deep(.fi-repeater-item select) {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%9ca3af' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
        }

        /* Number Input Spinner Enhancement */
        .invoice-wizard-form :deep(.fi-repeater-item input[type='number']::-webkit-inner-spin-button),
        .invoice-form :deep(.fi-repeater-item input[type='number']::-webkit-inner-spin-button) {
            opacity: 1;
            width: 1.5rem;
            height: 1.5rem;
        }

        /* Placeholder Styling */
        .invoice-wizard-form :deep(.fi-repeater-item input::placeholder),
        .invoice-wizard-form :deep(.fi-repeater-item textarea::placeholder),
        .invoice-form :deep(.fi-repeater-item input::placeholder),
        .invoice-form :deep(.fi-repeater-item textarea::placeholder) {
            color: var(--text-tertiary);
            opacity: 0.7;
        }

        /* Disabled Field Styling */
        .invoice-wizard-form :deep(.fi-repeater-item input:disabled),
        .invoice-form :deep(.fi-repeater-item input:disabled) {
            background: var(--secondary-bg);
            opacity: 0.8;
            cursor: not-allowed;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .invoice-wizard-form :deep(.fi-repeater-item),
            .invoice-form :deep(.fi-repeater-item) {
                padding: 1.25rem;
            }

            .invoice-wizard-form :deep(.fi-repeater-item .fi-fo-grid),
            .invoice-form :deep(.fi-repeater-item .fi-fo-grid) {
                grid-template-columns: 1fr !important;
            }

            .invoice-wizard-form :deep(.fi-section-header-label) {
                font-size: 1.125rem;
            }

            /* Mobile: Stack all fields vertically in items */
            .invoice-wizard-form :deep(.fi-repeater-item [class*="fi-fo-grid"]),
            .invoice-form :deep(.fi-repeater-item [class*="fi-fo-grid"]) {
                display: flex !important;
                flex-direction: column !important;
                gap: 1rem !important;
            }

            .invoice-wizard-form :deep(.fi-repeater-item [class*="fi-fo-grid"] > *),
            .invoice-form :deep(.fi-repeater-item [class*="fi-fo-grid"] > *) {
                width: 100% !important;
                grid-column: 1 / -1 !important;
            }
        }

        /* Tablet adjustments */
        @media (min-width: 769px) and (max-width: 1024px) {
            .invoice-wizard-form :deep(.fi-repeater-item [class*="fi-fo-grid"]),
            .invoice-form :deep(.fi-repeater-item [class*="fi-fo-grid"]) {
                grid-template-columns: repeat(2, 1fr) !important;
            }

            .invoice-wizard-form :deep(.fi-repeater-item [class*="fi-fo-grid"] > [data-column-span="3"]),
            .invoice-form :deep(.fi-repeater-item [class*="fi-fo-grid"] > [data-column-span="3"]) {
                grid-column: span 2 !important;
            }

            .invoice-wizard-form :deep(.fi-repeater-item [class*="fi-fo-grid"] > [data-column-span="4"]),
            .invoice-form :deep(.fi-repeater-item [class*="fi-fo-grid"] > [data-column-span="4"]) {
                grid-column: span 2 !important;
            }
        }

        /* Preview Toggle */
        .invoice-preview-toggle {
            margin: 1.5rem 0;
            padding: 1rem;
            background: var(--secondary-bg);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .toggle-switch {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
        }

        .toggle-switch input {
            display: none;
        }

        .toggle-slider {
            position: relative;
            width: 3rem;
            height: 1.5rem;
            background: var(--border-color);
            border-radius: 9999px;
            transition: all 0.3s;
        }

        .toggle-slider::before {
            content: '';
            position: absolute;
            width: 1.25rem;
            height: 1.25rem;
            background: white;
            border-radius: 50%;
            top: 0.125rem;
            left: 0.125rem;
            transition: all 0.3s;
            box-shadow: var(--shadow-sm);
        }

        .toggle-switch input:checked + .toggle-slider {
            background: var(--accent-blue);
        }

        .toggle-switch input:checked + .toggle-slider::before {
            transform: translateX(1.5rem);
        }

        .toggle-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        /* Submit Button */
        .invoice-form-actions {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 2px solid var(--border-color);
        }

        .invoice-submit-btn {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            background: linear-gradient(135deg, var(--accent-blue), #2563eb);
            color: white;
            padding: 1rem 2rem;
            border-radius: var(--radius-lg);
            font-weight: 700;
            font-size: 1rem;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: var(--shadow-md);
        }

        .invoice-submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .invoice-submit-btn:active {
            transform: translateY(0);
        }

        .btn-icon {
            width: 1.25rem;
            height: 1.25rem;
        }

        /* Created Invoice Preview Section */
        .invoice-created-preview-section {
            width: 100%;
            margin-top: 2rem;
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .invoice-created-preview-card {
            background: var(--card-bg);
            border: 2px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 2rem;
            box-shadow: var(--shadow-lg);
        }

        .invoice-created-preview-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid var(--border-color);
        }

        .created-preview-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--accent-green);
            margin: 0 0 0.5rem 0;
        }

        .created-preview-subtitle {
            font-size: 1rem;
            color: var(--text-secondary);
            margin: 0;
        }

        .preview-close-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: var(--secondary-bg);
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            color: var(--text-primary);
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .preview-close-btn:hover {
            background: var(--accent-red);
            color: white;
            border-color: var(--accent-red);
        }

        .invoice-created-preview-content {
            width: 100%;
        }

        .invoice-preview-card {
            background: var(--card-bg);
            border: 2px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 1rem;
        }

        .invoice-preview-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
        }

        .preview-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
        }

        .preview-refresh-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: var(--secondary-bg);
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            color: var(--text-primary);
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .preview-refresh-btn:hover {
            background: var(--accent-blue);
            color: white;
            border-color: var(--accent-blue);
        }

        .invoice-preview-content {
            min-height: 400px;
        }

        .preview-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 400px;
            color: var(--text-secondary);
            text-align: center;
        }

        .preview-icon {
            width: 4rem;
            height: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        /* Invoice Preview Template */
        .invoice-preview {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: var(--text-primary);
        }

        .invoice-preview-header-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid var(--border-color);
        }

        .invoice-preview-company {
            flex: 1;
        }

        .invoice-preview-company-name {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }

        .invoice-preview-company-details {
            font-size: 0.875rem;
            color: var(--text-secondary);
            line-height: 1.6;
        }

        .invoice-preview-invoice-info {
            text-align: right;
        }

        .invoice-preview-invoice-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--accent-blue);
        }

        .invoice-preview-invoice-details {
            font-size: 0.875rem;
            color: var(--text-secondary);
            line-height: 1.8;
        }

        .invoice-preview-customer-section {
            margin-bottom: 2rem;
            padding: 1rem;
            background: var(--secondary-bg);
            border-radius: var(--radius-md);
        }

        .invoice-preview-customer-label {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--text-tertiary);
            margin-bottom: 0.5rem;
        }

        .invoice-preview-customer-name {
            font-size: 1.125rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .invoice-preview-customer-details {
            font-size: 0.875rem;
            color: var(--text-secondary);
            line-height: 1.6;
        }

        .invoice-preview-items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5rem;
        }

        .invoice-preview-items-table thead {
            background: var(--secondary-bg);
        }

        .invoice-preview-items-table th {
            padding: 0.75rem;
            text-align: left;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-tertiary);
            border-bottom: 2px solid var(--border-color);
        }

        .invoice-preview-items-table td {
            padding: 1rem 0.75rem;
            border-bottom: 1px solid var(--border-color);
            font-size: 0.875rem;
        }

        .invoice-preview-items-table tbody tr:hover {
            background: var(--secondary-bg);
        }

        .invoice-preview-items-table .text-right {
            text-align: right;
        }

        .invoice-preview-totals {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 2px solid var(--border-color);
        }

        .invoice-preview-total-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            font-size: 0.875rem;
        }

        .invoice-preview-total-row.total {
            font-size: 1.25rem;
            font-weight: 700;
            padding: 1rem 0;
            border-top: 2px solid var(--border-color);
            margin-top: 0.5rem;
            color: var(--accent-blue);
        }

        .invoice-preview-notes {
            margin-top: 1.5rem;
            padding: 1rem;
            background: var(--secondary-bg);
            border-radius: var(--radius-md);
            border-left: 4px solid var(--accent-blue);
        }

        .invoice-preview-notes-label {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-tertiary);
            margin-bottom: 0.5rem;
        }

        .invoice-preview-notes-text {
            font-size: 0.875rem;
            color: var(--text-primary);
            line-height: 1.6;
            white-space: pre-wrap;
        }

        .invoice-preview-footer {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 2px solid var(--border-color);
            font-size: 0.75rem;
            color: var(--text-tertiary);
            text-align: center;
        }

        /* Invoice List Section */
        .invoice-list-section {
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
            box-sizing: border-box;
        }

        .invoice-list-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        /* Enhanced View Mode Toggle */
        .view-mode-toggle {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--card-bg);
            border: 2px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 0.25rem;
            box-shadow: var(--shadow-sm);
        }

        .view-mode-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            padding: 0;
            background: transparent;
            border: none;
            border-radius: var(--radius-md);
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .view-mode-btn svg {
            width: 1.25rem;
            height: 1.25rem;
            transition: all 0.3s;
        }

        .view-mode-btn:hover {
            background: var(--secondary-bg);
            color: var(--accent-blue);
            transform: scale(1.05);
        }

        .view-mode-btn.active {
            background: var(--accent-blue);
            color: white;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
        }

        .view-mode-btn.active:hover {
            background: #2563eb;
            transform: scale(1.05);
        }

        /* Enhanced New Invoice Button */
        .btn-new-invoice {
            display: inline-flex;
            align-items: center;
            gap: 0.625rem;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, var(--accent-blue) 0%, #2563eb 100%);
            color: white;
            border: none;
            border-radius: var(--radius-lg);
            font-size: 0.9375rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            position: relative;
            overflow: hidden;
        }

        .btn-new-invoice::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-new-invoice:hover::before {
            left: 100%;
        }

        .btn-new-invoice:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
        }

        .btn-new-invoice:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
        }

        .btn-new-invoice svg {
            width: 1.125rem;
            height: 1.125rem;
            transition: transform 0.3s;
        }

        .btn-new-invoice:hover svg {
            transform: rotate(90deg);
        }

        .list-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0 0 0.25rem 0;
        }

        .list-subtitle {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin: 0;
        }

        /* Filters and Search */
        .invoice-filters {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .filter-search {
            flex: 1;
            min-width: 250px;
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            width: 1.25rem;
            height: 1.25rem;
            color: var(--text-tertiary);
            pointer-events: none;
        }

        .search-input {
            width: 100%;
            padding: 0.875rem 1.25rem 0.875rem 3.5rem;
            background: var(--card-bg);
            border: 2px solid var(--border-color);
            border-radius: var(--radius-lg);
            font-size: 0.9375rem;
            color: var(--text-primary);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-sm);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1), var(--shadow-md);
            transform: translateY(-2px);
        }

        .search-input::placeholder {
            color: var(--text-tertiary);
        }

        .filter-select {
            padding: 0.875rem 1.25rem;
            background: var(--card-bg);
            border: 2px solid var(--border-color);
            border-radius: var(--radius-lg);
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--text-primary);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            min-width: 200px;
            box-shadow: var(--shadow-sm);
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1.5em 1.5em;
            padding-right: 3rem;
        }

        .dark .filter-select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%9ca3af' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1), var(--shadow-md);
            transform: translateY(-2px);
        }

        .filter-select:hover {
            border-color: var(--accent-blue);
        }

        .invoice-list-container {
            display: grid;
            gap: 1rem;
        }

        .invoice-list-container.grid-view {
            grid-template-columns: 1fr;
        }

        @media (min-width: 640px) {
            .invoice-list-container.grid-view {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1024px) {
            .invoice-list-container.grid-view {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (min-width: 1280px) {
            .invoice-list-container.grid-view {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        .invoice-list-container.list-view {
            grid-template-columns: 1fr;
        }

        /* Enhanced Table View Styles */
        .invoice-table-wrapper {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            overflow: hidden;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px -1px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 100%;
        }

        .dark .invoice-table-wrapper {
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.3), 0 1px 2px -1px rgba(0, 0, 0, 0.2);
        }

        .invoice-table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            width: 100%;
            max-width: 100%;
        }

        .invoice-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 1000px;
            max-width: 100%;
        }

        .invoice-table thead {
            background: linear-gradient(135deg, var(--secondary-bg) 0%, var(--card-bg) 100%);
            position: sticky;
            top: 0;
            z-index: 10;
            box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .dark .invoice-table thead {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.8) 0%, rgba(15, 23, 42, 0.9) 100%);
            box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.3);
        }

        .invoice-table th {
            padding: 1.125rem 1.5rem;
            text-align: left;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-secondary);
            border-bottom: 2px solid var(--border-color);
            white-space: nowrap;
            position: relative;
        }

        .invoice-table th:not(:last-child)::after {
            content: '';
            position: absolute;
            right: 0;
            top: 25%;
            height: 50%;
            width: 1px;
            background: var(--border-color);
        }

        .invoice-table th.col-number {
            min-width: 150px;
            max-width: 200px;
        }

        .invoice-table th.col-customer {
            min-width: 150px;
            max-width: 250px;
        }

        .invoice-table th.col-date {
            min-width: 100px;
            max-width: 120px;
        }

        .invoice-table th.col-status {
            min-width: 120px;
            max-width: 150px;
        }

        .invoice-table th.col-amount {
            min-width: 100px;
            max-width: 130px;
            text-align: right;
        }

        .invoice-table th.col-total {
            min-width: 120px;
            max-width: 150px;
            text-align: right;
        }

        .invoice-table th.col-actions {
            min-width: 120px;
            max-width: 150px;
            text-align: center;
        }

        .invoice-table tbody tr.table-row {
            border-bottom: 1px solid var(--border-color);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            background: var(--card-bg);
            position: relative;
        }

        .invoice-table tbody tr.table-row:nth-child(even) {
            background: var(--secondary-bg);
        }

        .dark .invoice-table tbody tr.table-row:nth-child(even) {
            background: rgba(30, 41, 59, 0.5);
        }

        .invoice-table tbody tr.table-row:hover {
            background: rgba(59, 130, 246, 0.05);
            box-shadow: inset 4px 0 0 0 var(--accent-blue);
        }

        .dark .invoice-table tbody tr.table-row:hover {
            background: rgba(59, 130, 246, 0.1);
        }

        .invoice-table tbody tr:last-child {
            border-bottom: none;
        }

        .invoice-table td {
            padding: 1.25rem 1.5rem;
            font-size: 0.875rem;
            color: var(--text-primary);
            vertical-align: middle;
            position: relative;
            white-space: nowrap;
        }

        .invoice-table td.col-amount,
        .invoice-table td.col-total {
            white-space: nowrap;
        }

        .table-cell-content.justify-end {
            justify-content: flex-end !important;
        }

        .invoice-table td:not(:last-child)::after {
            content: '';
            position: absolute;
            right: 0;
            top: 20%;
            height: 60%;
            width: 1px;
            background: var(--border-color);
        }

        .table-cell-content {
            display: flex;
            align-items: center;
            min-height: 2.5rem;
        }

        .invoice-number-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .invoice-icon {
            width: 1.125rem;
            height: 1.125rem;
            color: var(--accent-blue);
            flex-shrink: 0;
        }

        .table-invoice-number {
            font-weight: 700;
            color: var(--accent-blue);
            font-size: 0.9375rem;
            word-break: break-word;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 100%;
            display: inline-block;
        }

        .table-customer {
            word-break: break-word;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 100%;
            display: inline-block;
            font-weight: 600;
            color: var(--text-primary);
        }

        .table-date {
            color: var(--text-secondary);
            font-size: 0.8125rem;
        }

        .table-amount {
            font-weight: 600;
            text-align: right;
            color: var(--text-primary);
            white-space: nowrap;
            line-height: 1.5;
        }

        .currency-inline {
            display: inline !important;
            white-space: nowrap !important;
            line-height: 1.5 !important;
        }

        .justify-end {
            justify-content: flex-end;
        }

        .table-amount-total {
            font-size: 1rem;
            font-weight: 700;
            color: var(--accent-blue);
            white-space: nowrap;
        }

        .table-actions {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            align-items: center;
        }

        .table-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.625rem 1rem;
            background: var(--card-bg);
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius-md);
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 0.8125rem;
            font-weight: 600;
            text-decoration: none;
            min-width: 4rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .table-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .table-btn:active {
            transform: translateY(0);
        }

        .table-btn-download {
            background: linear-gradient(135deg, var(--accent-blue) 0%, #2563eb 100%);
            border-color: var(--accent-blue);
            color: white;
            box-shadow: 0 2px 4px 0 rgba(59, 130, 246, 0.3);
        }

        .table-btn-download:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            border-color: #2563eb;
            color: white;
            box-shadow: 0 4px 8px 0 rgba(59, 130, 246, 0.4);
        }

        .table-btn-view {
            background: var(--card-bg);
            border-color: var(--border-color);
            color: var(--text-primary);
        }

        .table-btn-view:hover {
            background: var(--secondary-bg);
            border-color: var(--accent-blue);
            color: var(--accent-blue);
        }

        .dark .table-btn-view:hover {
            background: rgba(30, 41, 59, 0.8);
        }

        .table-btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-color: #10b981;
            color: white;
            box-shadow: 0 2px 4px 0 rgba(16, 185, 129, 0.3);
        }

        .table-btn-success:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            border-color: #059669;
            color: white;
            box-shadow: 0 4px 8px 0 rgba(16, 185, 129, 0.4);
        }

        .table-btn-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            border-color: #f59e0b;
            color: white;
            box-shadow: 0 2px 4px 0 rgba(245, 158, 11, 0.3);
        }

        .table-btn-warning:hover {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            border-color: #d97706;
            color: white;
            box-shadow: 0 4px 8px 0 rgba(245, 158, 11, 0.4);
        }

        .table-btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border-color: #ef4444;
            color: white;
            box-shadow: 0 2px 4px 0 rgba(239, 68, 68, 0.3);
        }

        .table-btn-danger:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            border-color: #dc2626;
            color: white;
            box-shadow: 0 4px 8px 0 rgba(239, 68, 68, 0.4);
        }

        .btn-label {
            font-size: 0.75rem;
        }

        .table-empty {
            padding: 4rem 2rem !important;
            text-align: center;
        }

        .status-icon {
            width: 0.875rem;
            height: 0.875rem;
        }

        /* Clean Invoice Card Design */
        .invoice-card {
            background: var(--card-bg);
            border: 1.5px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px -1px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        .invoice-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: transparent;
            transition: background 0.3s;
        }

        .invoice-card:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            border-color: var(--accent-blue);
            transform: translateY(-4px);
        }

        .invoice-card:hover::before {
            background: var(--accent-blue);
        }

        /* Card Top Section */
        .invoice-card-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 1.25rem;
            border-bottom: 2px solid var(--border-color);
            margin-bottom: 0.25rem;
        }

        .invoice-number-block {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .invoice-number {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--accent-blue);
            line-height: 1.3;
            letter-spacing: -0.01em;
        }

        .invoice-date {
            font-size: 0.8125rem;
            color: var(--text-secondary);
            font-weight: 500;
            margin-top: 0.25rem;
        }

        .invoice-status {
            flex-shrink: 0;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.5rem 0.875rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            white-space: nowrap;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            border: 1px solid transparent;
        }

        .status-approved {
            background: #d1fae5;
            color: #065f46;
        }

        .dark .status-approved {
            background: #064e3b;
            color: #a7f3d0;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .dark .status-pending {
            background: #78350f;
            color: #fef3c7;
        }

        .status-review {
            background: #fee2e2;
            color: #991b1b;
        }

        .dark .status-review {
            background: #7f1d1d;
            color: #fecaca;
        }

        /* Card Content */
        .invoice-card-content {
            display: flex;
            flex-direction: column;
            gap: 0.875rem;
            padding: 0.5rem 0;
        }

        .invoice-info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.875rem;
            padding: 0.5rem 0;
        }

        .invoice-info-row:not(:last-child) {
            border-bottom: 1px solid var(--border-color);
        }

        .info-label {
            color: var(--text-secondary);
            font-weight: 500;
        }

        .info-value {
            color: var(--text-primary);
            font-weight: 600;
            text-align: right;
        }

        /* Amounts Section */
        .invoice-card-amounts {
            background: linear-gradient(135deg, var(--secondary-bg) 0%, var(--card-bg) 100%);
            border-radius: var(--radius-lg);
            padding: 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 0.625rem;
            border: 1.5px solid var(--border-color);
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.03);
            margin-top: 0.5rem;
        }

        .dark .invoice-card-amounts {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.6) 0%, rgba(15, 23, 42, 0.8) 100%);
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .dark .invoice-card-totals {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.6) 0%, rgba(15, 23, 42, 0.8) 100%);
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .amount-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .amount-row span:last-child {
            font-weight: 600;
            color: var(--text-primary);
        }

        .amount-total {
            padding-top: 0.875rem;
            margin-top: 0.75rem;
            border-top: 2px solid var(--border-color);
            font-size: 1rem;
        }

        .amount-total span:first-child {
            font-weight: 600;
            color: var(--text-primary);
        }

        .amount-total span:last-child {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--accent-blue);
        }

        /* Card Footer */
        .invoice-card-footer {
            display: flex;
            gap: 0.75rem;
            padding-top: 1.25rem;
            border-top: 2px solid var(--border-color);
            margin-top: 0.5rem;
        }

        .btn-download,
        .btn-view {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            border-radius: var(--radius-lg);
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            border: 1.5px solid transparent;
            flex: 1;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .btn-download {
            background: linear-gradient(135deg, var(--accent-blue) 0%, #2563eb 100%);
            color: white;
            border-color: var(--accent-blue);
            box-shadow: 0 2px 4px 0 rgba(59, 130, 246, 0.3);
        }

        .btn-download:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px 0 rgba(59, 130, 246, 0.4);
            color: white;
        }

        .btn-view {
            background: var(--card-bg);
            color: var(--text-primary);
            border-color: var(--border-color);
        }

        .btn-view:hover {
            background: var(--secondary-bg);
            border-color: var(--accent-blue);
            color: var(--accent-blue);
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .dark .btn-view:hover {
            background: rgba(30, 41, 59, 0.8);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2);
        }

        .btn-download:active,
        .btn-view:active {
            transform: translateY(0);
        }

        .info-icon {
            width: 1.5rem;
            height: 1.5rem;
            color: var(--accent-blue);
            flex-shrink: 0;
        }

        .info-content {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            flex: 1;
            min-width: 0;
        }

        .info-label {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--text-tertiary);
        }

        .info-value {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--text-primary);
            word-break: break-word;
        }

        .invoice-card-totals {
            padding: 1rem;
            background: linear-gradient(135deg, var(--secondary-bg) 0%, var(--card-bg) 100%);
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .invoice-card-list .invoice-card-totals {
            padding: 0.75rem 1rem;
            background: transparent;
            border: none;
            box-shadow: none;
            min-width: 150px;
        }

        .total-line {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .total-line:not(:last-child) {
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 0.75rem;
            margin-bottom: 0.5rem;
        }

        .total-main {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--accent-blue);
            padding-top: 0.75rem;
            margin-top: 0.5rem;
            border-top: 2px solid var(--border-color) !important;
            border-bottom: none !important;
        }


        /* Empty State */
        .invoice-empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: var(--card-bg);
            border: 2px dashed var(--border-color);
            border-radius: var(--radius-xl);
        }

        .empty-icon {
            width: 4rem;
            height: 4rem;
            margin: 0 auto 1.5rem;
            color: var(--text-tertiary);
            opacity: 0.5;
        }

        .empty-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0 0 0.5rem 0;
        }

        .empty-text {
            font-size: 1rem;
            color: var(--text-secondary);
            margin: 0 0 2rem 0;
        }

        .empty-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.875rem 2rem;
            background: var(--accent-blue);
            color: white;
            border: none;
            border-radius: var(--radius-lg);
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        .empty-action-btn:hover {
            background: #2563eb;
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        /* Loading States */
        .invoice-list-card.loading {
            opacity: 0.6;
            pointer-events: none;
        }

        .invoice-list-card.loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 2rem;
            height: 2rem;
            margin: -1rem 0 0 -1rem;
            border: 3px solid var(--border-color);
            border-top-color: var(--accent-blue);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Smooth Scroll */
        .invoice-tab-content {
            scroll-behavior: smooth;
        }

        /* Mobile Responsive */
        @media (max-width: 640px) {
            .invoice-page-container {
                padding: 0.5rem;
                overflow-x: hidden;
                width: 100%;
                max-width: 100vw;
            }

            .invoice-list-section {
                width: 100%;
                max-width: 100%;
                overflow-x: hidden;
            }

            .invoice-table-wrapper {
                width: 100%;
                max-width: 100%;
                margin: 0;
                border-radius: var(--radius-md);
            }

            .invoice-table-container {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                width: 100%;
                max-width: 100%;
            }

            .invoice-table {
                min-width: 700px;
                width: 100%;
            }

            .invoice-table th,
            .invoice-table td {
                padding: 0.75rem 0.5rem;
                font-size: 0.8125rem;
            }

            .invoice-table th.col-number,
            .invoice-table td.col-number {
                min-width: 120px;
                max-width: 150px;
            }

            .invoice-table th.col-customer,
            .invoice-table td.col-customer {
                min-width: 120px;
                max-width: 180px;
            }

            .invoice-table th.col-date,
            .invoice-table td.col-date {
                min-width: 90px;
                max-width: 110px;
            }

            .invoice-table th.col-status,
            .invoice-table td.col-status {
                min-width: 100px;
                max-width: 130px;
            }

            .invoice-table th.col-amount,
            .invoice-table td.col-amount {
                min-width: 80px;
                max-width: 110px;
            }

            .invoice-table th.col-total,
            .invoice-table td.col-total {
                min-width: 100px;
                max-width: 130px;
            }

            .invoice-table th.col-actions,
            .invoice-table td.col-actions {
                min-width: 100px;
                max-width: 130px;
            }

            .table-btn {
                padding: 0.5rem;
                gap: 0.25rem;
            }

            .btn-label {
                display: none;
            }

            .table-invoice-number {
                font-size: 0.75rem;
                word-break: break-word;
            }

            .invoice-hero {
                padding: 1.25rem;
                margin-bottom: 1.5rem;
            }

            .invoice-hero-title {
                font-size: 1.25rem;
            }

            .invoice-hero-subtitle {
                font-size: 0.875rem;
            }

            .invoice-tabs {
                flex-direction: column;
                gap: 0.25rem;
                padding: 0.5rem;
            }

            .invoice-tab {
                width: 100%;
                justify-content: flex-start;
                padding: 0.75rem 1rem;
                border-bottom: 2px solid var(--border-color);
                border-left: 4px solid transparent;
                margin-bottom: 0;
                border-radius: var(--radius-md);
            }

            .invoice-tab.active {
                border-left-color: var(--accent-blue);
                border-bottom-color: var(--border-color);
                background: var(--secondary-bg);
            }

            .invoice-tab.active::after {
                display: none;
            }

            .invoice-form {
                padding: 1rem;
            }

            .invoice-filters {
                flex-direction: column;
                gap: 0.75rem;
            }

            .filter-search,
            .filter-select {
                width: 100%;
                min-width: 100%;
            }

            .invoice-list-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .invoice-list-card {
                padding: 1.25rem;
            }

            .invoice-card-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }

            .invoice-card-info-grid {
                grid-template-columns: 1fr;
            }

            .invoice-card-actions {
                flex-direction: column;
                gap: 0.75rem;
            }

            .invoice-action-btn {
                width: 100%;
            }

            .invoice-preview-card {
                padding: 1rem;
            }

            .invoice-preview-items-table {
                font-size: 0.75rem;
            }

            .invoice-preview-items-table th,
            .invoice-preview-items-table td {
                padding: 0.5rem;
            }
        }

        @media (max-width: 480px) {
            .invoice-page-container {
                padding: 0.5rem;
                overflow-x: hidden;
                width: 100%;
                max-width: 100vw;
            }

            .invoice-table-wrapper {
                border-radius: var(--radius-sm);
            }

            .invoice-table {
                min-width: 650px;
            }

            .invoice-table th,
            .invoice-table td {
                padding: 0.625rem 0.375rem;
                font-size: 0.75rem;
            }

            .invoice-hero {
                padding: 1rem;
            }

            .invoice-list-card {
                padding: 1rem;
            }

            .invoice-number-icon {
                width: 2.5rem;
                height: 2.5rem;
            }

            .invoice-number-value {
                font-size: 1.125rem;
            }
        }

        /* Tablet Responsive */
        @media (min-width: 641px) and (max-width: 1023px) {
            .invoice-page-container {
                overflow-x: hidden;
                width: 100%;
                max-width: 100%;
            }

            .invoice-table-wrapper {
                width: 100%;
                max-width: 100%;
            }

            .invoice-table-container {
                overflow-x: auto;
                width: 100%;
            }

            .invoice-table {
                min-width: 900px;
            }

            .invoice-list-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .invoice-tabs {
                gap: 0.5rem;
            }

            .invoice-tab {
                padding: 0.75rem 1.25rem;
            }
        }

        /* Large Desktop */
        @media (min-width: 1536px) {
            .invoice-list-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }
        }
    </style>

    @push('scripts')
    <script>
        // Live preview update
        function updatePreview() {
            const form = document.querySelector('.invoice-form');
            if (!form) return;

            const formData = new FormData(form);
            const data = {};
            
            // Get form values (simplified - in real implementation, use Livewire wire:model)
            const customerName = document.querySelector('input[name*="customer_name"]')?.value || '';
            const customerEmail = document.querySelector('input[name*="customer_email"]')?.value || '';
            const customerPhone = document.querySelector('input[name*="customer_phone"]')?.value || '';
            const customerAddress = document.querySelector('textarea[name*="customer_address"]')?.value || '';
            const customerVat = document.querySelector('input[name*="customer_vat"]')?.value || '';
            const customerKvk = document.querySelector('input[name*="customer_kvk"]')?.value || '';
            const invoiceDate = document.querySelector('input[name*="invoice_date"]')?.value || '';
            const dueDate = document.querySelector('input[name*="due_date"]')?.value || '';
            const invoiceNumber = document.querySelector('input[name*="invoice_number"]')?.value || 'AUTO';
            const notes = document.querySelector('textarea[name*="notes"]')?.value || '';

            // Get items from repeater (simplified)
            const items = [];
            const itemRows = document.querySelectorAll('.fi-repeater-item');
            itemRows.forEach(row => {
                const description = row.querySelector('input[name*="description"]')?.value || '';
                const quantity = parseFloat(row.querySelector('input[name*="quantity"]')?.value || 1);
                const price = parseFloat(row.querySelector('input[name*="price"]')?.value || 0);
                const vatRate = row.querySelector('select[name*="vat_rate"]')?.value || '21';
                
                if (description) {
                    items.push({ description, quantity, price, vatRate });
                }
            });

            // Calculate totals
            let subtotal = 0;
            let vatTotal = 0;
            items.forEach(item => {
                const itemSubtotal = item.quantity * item.price;
                const vatRate = parseFloat(item.vatRate) / 100;
                const itemVat = itemSubtotal * vatRate;
                subtotal += itemSubtotal;
                vatTotal += itemVat;
            });
            const total = subtotal + vatTotal;

            // Generate preview HTML
            const previewHTML = generateInvoicePreview({
                customerName,
                customerEmail,
                customerPhone,
                customerAddress,
                customerVat,
                customerKvk,
                invoiceDate,
                dueDate,
                invoiceNumber,
                notes,
                items,
                subtotal,
                vatTotal,
                total
            });

            document.getElementById('preview-content').innerHTML = previewHTML;
        }

        function generateInvoicePreview(data) {
            const formatDate = (dateStr) => {
                if (!dateStr) return '';
                const date = new Date(dateStr);
                return date.toLocaleDateString('nl-NL', { day: '2-digit', month: '2-digit', year: 'numeric' });
            };

            const formatCurrency = (amount) => {
                return new Intl.NumberFormat('nl-NL', { style: 'currency', currency: 'EUR' }).format(amount);
            };

            let itemsHTML = '';
            if (data.items && data.items.length > 0) {
                itemsHTML = data.items.map(item => {
                    const itemSubtotal = item.quantity * item.price;
                    const vatRate = parseFloat(item.vatRate) / 100;
                    const itemVat = itemSubtotal * vatRate;
                    const itemTotal = itemSubtotal + itemVat;
                    
                    return `
                        <tr>
                            <td>${item.description}</td>
                            <td class="text-right">${item.quantity}</td>
                            <td class="text-right">${formatCurrency(item.price)}</td>
                            <td class="text-right">${item.vatRate}%</td>
                            <td class="text-right">${formatCurrency(itemTotal)}</td>
                        </tr>
                    `;
                }).join('');
            } else {
                itemsHTML = '<tr><td colspan="5" style="text-align: center; color: var(--text-tertiary); padding: 2rem;">Voeg items toe aan de factuur</td></tr>';
            }

            return `
                <div class="invoice-preview">
                    <div class="invoice-preview-header-section">
                        <div class="invoice-preview-company">
                            <div class="invoice-preview-company-name">${data.customerName || 'Uw Bedrijf'}</div>
                            <div class="invoice-preview-company-details">
                                ${data.customerAddress || 'Adresgegevens'}<br>
                                ${data.customerEmail || 'email@voorbeeld.nl'}<br>
                                ${data.customerPhone ? `Tel: ${data.customerPhone}` : ''}
                                ${data.customerVat ? `<br>BTW: ${data.customerVat}` : ''}
                                ${data.customerKvk ? `<br>KVK: ${data.customerKvk}` : ''}
                            </div>
                        </div>
                        <div class="invoice-preview-invoice-info">
                            <div class="invoice-preview-invoice-title">FACTUUR</div>
                            <div class="invoice-preview-invoice-details">
                                <strong>Factuurnummer:</strong> ${data.invoiceNumber || 'AUTO'}<br>
                                <strong>Factuurdatum:</strong> ${formatDate(data.invoiceDate)}<br>
                                <strong>Vervaldatum:</strong> ${formatDate(data.dueDate)}
                            </div>
                        </div>
                    </div>

                    <div class="invoice-preview-customer-section">
                        <div class="invoice-preview-customer-label">Factureren aan</div>
                        <div class="invoice-preview-customer-name">${data.customerName || 'Klant Naam'}</div>
                        <div class="invoice-preview-customer-details">
                            ${data.customerAddress || 'Klant Adres'}<br>
                            ${data.customerEmail || 'klant@voorbeeld.nl'}<br>
                            ${data.customerPhone ? `Tel: ${data.customerPhone}` : ''}
                            ${data.customerVat ? `<br>BTW-nummer: ${data.customerVat}` : ''}
                            ${data.customerKvk ? `<br>KVK-nummer: ${data.customerKvk}` : ''}
                        </div>
                    </div>

                    <table class="invoice-preview-items-table">
                        <thead>
                            <tr>
                                <th>Omschrijving</th>
                                <th class="text-right">Aantal</th>
                                <th class="text-right">Prijs</th>
                                <th class="text-right">BTW</th>
                                <th class="text-right">Totaal</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${itemsHTML}
                        </tbody>
                    </table>

                    <div class="invoice-preview-totals">
                        <div class="invoice-preview-total-row">
                            <span>Subtotaal (excl. BTW)</span>
                            <span>${formatCurrency(data.subtotal || 0)}</span>
                        </div>
                        <div class="invoice-preview-total-row">
                            <span>BTW</span>
                            <span>${formatCurrency(data.vatTotal || 0)}</span>
                        </div>
                        <div class="invoice-preview-total-row total">
                            <span>Totaal (incl. BTW)</span>
                            <span>${formatCurrency(data.total || 0)}</span>
                        </div>
                    </div>

                    ${data.notes ? `
                    <div class="invoice-preview-notes">
                        <div class="invoice-preview-notes-label">Opmerkingen:</div>
                        <div class="invoice-preview-notes-text">${data.notes}</div>
                    </div>
                    ` : ''}
                    <div class="invoice-preview-footer">
                        Bedankt voor uw vertrouwen!<br>
                        Deze factuur is gegenereerd via MARCOFIC Klanten Portaal
                    </div>
                </div>
            `;
        }

        // Update preview on form changes
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.invoice-form');
            if (form) {
                // Listen for input changes
                form.addEventListener('input', debounce(updatePreview, 300));
                form.addEventListener('change', debounce(updatePreview, 300));
                
                // Initial preview
                setTimeout(updatePreview, 500);
            }

            // Toggle preview visibility
            const previewToggle = document.getElementById('preview-toggle');
            const previewSection = document.getElementById('invoice-preview');
            
            if (previewToggle && previewSection) {
                previewToggle.addEventListener('change', function() {
                    if (window.innerWidth < 1024) {
                        previewSection.classList.toggle('show', this.checked);
                    }
                });
            }
        });

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
    </script>
    @endpush
</x-filament-panels::page>
