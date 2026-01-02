<x-filament-panels::page wire:poll.30s="loadWorkflow">
    <div class="space-y-6" x-data="{ calculating: @js($isCalculating) }">
        <!-- Client & Period Selector -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            {{ $this->form }}
        </div>

        @php
            $workflow = $this->workflow;
            $nextActions = $this->nextActions;
        @endphp

        @if(!empty($workflow) && isset($workflow['client']))
                @php
                    $workflowData = $workflow;
                    $currentStep = $workflowData['current_step'] ?? 'documents_processing';
                    $progress = $workflowData['progress_percentage'] ?? 0;
                    $docStatus = $workflowData['document_status'] ?? [];
                    $taxTotals = $workflowData['tax_totals'] ?? [];
                    $issues = $workflowData['issues'] ?? [];
                    $canSubmit = $workflowData['can_submit'] ?? false;
                    $period = $workflowData['period'] ?? null;
                    $isPeriodLocked = $period && $period->isLocked();
                @endphp

                @if($isPeriodLocked)
                    <!-- Locked Period Warning -->
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 dark:border-yellow-600 p-4 mb-6 rounded">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3 flex-1">
                                <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                    Deze periode is afgesloten
                                </h3>
                                <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                    <p>
                                        De BTW periode <strong>{{ $period->period_string ?? '' }}</strong> is afgesloten op 
                                        {{ $period->closed_at ? $period->closed_at->format('d-m-Y H:i') : '' }}.
                                    </p>
                                    <p class="mt-1">
                                        Je kunt nog steeds nieuwe documenten uploaden (ze worden aan de periode gekoppeld), 
                                        maar voor correcties moet je de periode eerst heropenen.
                                    </p>
                                </div>
                                <div class="mt-4">
                                    <x-filament::button wire:click="unlockPeriod" color="warning" size="sm">
                                        🔓 Periode Heropenen
                                    </x-filament::button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

            <!-- Progress Bar -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="mb-4">
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="text-lg font-semibold">Workflow Voortgang</h3>
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $progress }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                        <div class="bg-primary-600 h-3 rounded-full transition-all duration-500" style="width: {{ $progress }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Step 1: Documents Processing -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center text-2xl
                            {{ $currentStep === 'documents_processing' ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 
                               ($progress > 25 ? 'bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-400') }}">
                            @if($progress > 25)
                                ✓
                            @else
                                📤
                            @endif
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold">Stap 1: Documenten Verwerken</h3>
                            <p class="text-sm text-gray-500">Documenten worden geüpload en verwerkt</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        @if($progress > 25)
                            <span class="px-3 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-full text-sm font-medium">Voltooid</span>
                        @elseif($currentStep === 'documents_processing')
                            <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full text-sm font-medium">Bezig</span>
                            @if(($docStatus['pending'] ?? 0) > 0)
                                <x-filament::button wire:click="processPendingDocuments" size="sm" color="info">
                                    Verwerk {{ $docStatus['pending'] }} Document(en)
                                </x-filament::button>
                            @endif
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mt-4">
                    <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <p class="text-2xl font-bold">{{ $docStatus['total'] ?? 0 }}</p>
                        <p class="text-sm text-gray-500">Totaal</p>
                    </div>
                    <div class="text-center p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                        <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $docStatus['pending'] ?? 0 }}</p>
                        <p class="text-sm text-gray-500">In Wachtrij</p>
                    </div>
                    <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $docStatus['processing'] ?? 0 }}</p>
                        <p class="text-sm text-gray-500">Wordt Verwerkt</p>
                    </div>
                    <div class="text-center p-4 bg-orange-50 dark:bg-orange-900/20 rounded-lg">
                        <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $docStatus['review_required'] ?? 0 }}</p>
                        <p class="text-sm text-gray-500">Review Nodig</p>
                    </div>
                    <div class="text-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $docStatus['approved'] ?? 0 }}</p>
                        <p class="text-sm text-gray-500">Goedgekeurd</p>
                    </div>
                </div>

                <!-- Documenten Tabel -->
                @php
                    $documents = $this->documents;
                    $pendingDocs = $documents->where('status', 'pending');
                    $processingDocs = $documents->where('status', 'ocr_processing');
                    $reviewDocs = $documents->where('status', 'review_required');
                    $approvedDocs = $documents->where('status', 'approved');
                @endphp

                @if($documents->count() > 0)
                    <div class="mt-6">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-lg font-semibold">Documenten Overzicht</h4>
                            <div class="flex gap-2">
                                @if($pendingDocs->count() > 0)
                                    <x-filament::button wire:click="processPendingDocuments" size="sm" color="info">
                                        Verwerk Alle ({{ $pendingDocs->count() }})
                                    </x-filament::button>
                                @endif
                            </div>
                        </div>

                        <!-- Status Tabs -->
                        <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
                            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                                <button wire:click="$set('activeTab', 'all')" 
                                    class="@if(($activeTab ?? 'all') === 'all') border-primary-500 text-primary-600 dark:text-primary-400 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                    Alle ({{ $documents->count() }})
                                </button>
                                @if($pendingDocs->count() > 0)
                                    <button wire:click="$set('activeTab', 'pending')" 
                                        class="@if(($activeTab ?? 'all') === 'pending') border-yellow-500 text-yellow-600 dark:text-yellow-400 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                        In Wachtrij ({{ $pendingDocs->count() }})
                                    </button>
                                @endif
                                @if($processingDocs->count() > 0)
                                    <button wire:click="$set('activeTab', 'processing')" 
                                        class="@if(($activeTab ?? 'all') === 'processing') border-blue-500 text-blue-600 dark:text-blue-400 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                        Wordt Verwerkt ({{ $processingDocs->count() }})
                                    </button>
                                @endif
                                @if($reviewDocs->count() > 0)
                                    <button wire:click="$set('activeTab', 'review')" 
                                        class="@if(($activeTab ?? 'all') === 'review') border-orange-500 text-orange-600 dark:text-orange-400 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                        Review Nodig ({{ $reviewDocs->count() }})
                                    </button>
                                @endif
                                @if($approvedDocs->count() > 0)
                                    <button wire:click="$set('activeTab', 'approved')" 
                                        class="@if(($activeTab ?? 'all') === 'approved') border-green-500 text-green-600 dark:text-green-400 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                        Goedgekeurd ({{ $approvedDocs->count() }})
                                    </button>
                                @endif
                            </nav>
                        </div>

                        <!-- Documents Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Document</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Grootboek</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Datum</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Bedrag</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Acties</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @php
                                        $filteredDocs = match($activeTab ?? 'all') {
                                            'pending' => $pendingDocs,
                                            'processing' => $processingDocs,
                                            'review' => $reviewDocs,
                                            'approved' => $approvedDocs,
                                            default => $documents,
                                        };
                                    @endphp
                                    @forelse($filteredDocs as $doc)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <span class="text-lg mr-2">
                                                        @if($doc->document_type === 'receipt') 🧾
                                                        @elseif($doc->document_type === 'purchase_invoice') 📄
                                                        @elseif($doc->document_type === 'bank_statement') 🏦
                                                        @elseif($doc->document_type === 'sales_invoice') 🧑‍💼
                                                        @else 📁
                                                        @endif
                                                    </span>
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ \Illuminate\Support\Str::limit($doc->original_filename, 40) }}</div>
                                                        @if($doc->supplier_name)
                                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $doc->supplier_name }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-sm text-gray-900 dark:text-gray-100">
                                                        @if($doc->document_type === 'receipt') Bon
                                                        @elseif($doc->document_type === 'purchase_invoice') Inkoop
                                                        @elseif($doc->document_type === 'bank_statement') Bank
                                                        @elseif($doc->document_type === 'sales_invoice') Verkoop
                                                        @else Overig
                                                        @endif
                                                    </span>
                                                    @if($doc->document_type === 'sales_invoice' && !$doc->is_paid)
                                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400" title="Onbetaalde factuur wordt niet meegenomen in BTW berekening">
                                                            ⚠️ Niet Betaald
                                                        </span>
                                                    @elseif($doc->document_type === 'sales_invoice' && $doc->is_paid)
                                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400" title="Betaalde factuur wordt meegenomen in BTW berekening">
                                                            ✓ Betaald
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($doc->ledgerAccount)
                                                    <div class="flex flex-col">
                                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                            {{ $doc->ledgerAccount->code }}
                                                        </span>
                                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                                            {{ \Illuminate\Support\Str::limit($doc->ledgerAccount->description, 30) }}
                                                        </span>
                                                    </div>
                                                @else
                                                    <span class="text-xs text-gray-400 italic">Niet toegewezen</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($doc->status === 'pending')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                                        ⏳ In Wachtrij
                                                    </span>
                                                @elseif($doc->status === 'ocr_processing')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 animate-pulse">
                                                        🔄 Wordt Verwerkt
                                                    </span>
                                                @elseif($doc->status === 'review_required')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                                                        👀 Review Nodig
                                                    </span>
                                                @elseif($doc->status === 'approved')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                        ✅ Goedgekeurd
                                                    </span>
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                                        {{ $doc->status }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                @if($doc->document_date)
                                                    {{ \Carbon\Carbon::parse($doc->document_date)->format('d-m-Y') }}
                                                @else
                                                    {{ $doc->created_at->format('d-m-Y') }}
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                @if($doc->amount_incl)
                                                    €{{ number_format($doc->amount_incl, 2, ',', '.') }}
                                                @else
                                                    <span class="text-gray-400">—</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex justify-end gap-2">
                                                    @if($doc->status === 'pending')
                                                        <x-filament::button wire:click="processDocument({{ $doc->id }})" size="xs" color="info">
                                                            Verwerk
                                                        </x-filament::button>
                                                    @endif
                                                    <a href="{{ \App\Filament\Resources\DocumentResource::getUrl('edit', ['record' => $doc->id]) }}" 
                                                       class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300">
                                                        Bekijk
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                                Geen documenten gevonden
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Step 2: Tax Calculation -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center text-2xl
                            {{ $currentStep === 'tax_calculating' ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' : 
                               ($progress > 50 ? 'bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-400') }}">
                            @if($progress > 50)
                                ✓
                            @else
                                🧮
                            @endif
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold">Stap 2: BTW Berekening</h3>
                            <p class="text-sm text-gray-500">Automatische BTW berekening per rubriek</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        @if($progress > 50)
                            <span class="px-3 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-full text-sm font-medium">Voltooid</span>
                        @elseif($currentStep === 'tax_calculating')
                            <x-filament::button wire:click="calculateTax" :disabled="$isCalculating" size="sm">
                                {{ $isCalculating ? 'Berekenen...' : 'Bereken BTW' }}
                            </x-filament::button>
                        @endif
                    </div>
                </div>

                @if($taxTotals['calculated'] ?? false)
                    <div class="mt-4 space-y-3">
                        @php
                            // Group by BTW type: Verschuldigd vs Aftrekbaar
                            $verschuldigd = ['1a', '1b', '1c'];
                            $aftrekbaar = ['2a', '5b'];
                            $overig = ['2b', '3a', '3b', '4a', '4b'];
                            
                            $verschuldigdTotal = 0;
                            $verschuldigdVat = 0;
                            $aftrekbaarTotal = 0;
                            $aftrekbaarVat = 0;
                        @endphp
                        
                        <!-- BTW Verschuldigd (Verkoop) -->
                        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 border border-green-200 dark:border-green-800">
                            <h4 class="font-bold text-green-900 dark:text-green-100 mb-3 flex items-center gap-2">
                                📤 BTW Verschuldigd (Verkoopfacturen)
                                <span class="text-xs font-normal text-gray-600 dark:text-gray-400">Grootboek: 8000-8999</span>
                            </h4>
                            <div class="mb-3 p-2 bg-blue-50 dark:bg-blue-900/20 rounded border border-blue-200 dark:border-blue-800">
                                <p class="text-xs text-blue-800 dark:text-blue-200">
                                    <strong>ℹ️ Belangrijk:</strong> Alleen <strong>betaalde</strong> verkoopfacturen worden meegenomen in de BTW berekening (kasstelsel). Onbetaalde facturen worden automatisch uitgesloten.
                                </p>
                            </div>
                            <div class="space-y-2">
                                @foreach($verschuldigd as $rubriek)
                                    @if(($taxTotals['totals'][$rubriek]['count'] ?? 0) > 0)
                                        @php
                                            $data = $taxTotals['totals'][$rubriek];
                                            $verschuldigdTotal += $data['amount'];
                                            $verschuldigdVat += $data['vat'];
                                        @endphp
                                        <div class="flex justify-between items-center p-2 bg-white dark:bg-gray-800 rounded">
                                            <div>
                                                <p class="font-medium text-sm">
                                                    Rubriek {{ $rubriek }}: {{ $data['label'] ?? 'Verkoop' }}
                                                    @if($rubriek === '1a')
                                                        <span class="text-xs text-gray-500">(21% - Grootboek 8000)</span>
                                                    @elseif($rubriek === '1b')
                                                        <span class="text-xs text-gray-500">(9% - Grootboek 8100)</span>
                                                    @elseif($rubriek === '1c')
                                                        <span class="text-xs text-gray-500">(0% - Grootboek 8200)</span>
                                                    @endif
                                                </p>
                                                <p class="text-xs text-gray-500">{{ $data['count'] }} factuur(en)</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="font-bold text-sm">€{{ number_format($data['amount'], 2, ',', '.') }}</p>
                                                <p class="text-xs text-green-600 dark:text-green-400">BTW: €{{ number_format($data['vat'], 2, ',', '.') }}</p>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                                @if($verschuldigdVat > 0)
                                    <div class="flex justify-between items-center p-2 bg-green-100 dark:bg-green-900/40 rounded border-t border-green-300 dark:border-green-700 mt-2">
                                        <p class="font-bold">Subtotaal Verschuldigd:</p>
                                        <p class="font-bold text-green-700 dark:text-green-300">€{{ number_format($verschuldigdVat, 2, ',', '.') }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- BTW Aftrekbaar (Inkoop) -->
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
                            <h4 class="font-bold text-blue-900 dark:text-blue-100 mb-3 flex items-center gap-2">
                                📥 BTW Aftrekbaar (Inkoopfacturen & Bonnetjes)
                                <span class="text-xs font-normal text-gray-600 dark:text-gray-400">Grootboek: 5000-5999 (Inkoop) / 4000-4999 (Kosten)</span>
                            </h4>
                            <div class="space-y-2">
                                @foreach($aftrekbaar as $rubriek)
                                    @if(($taxTotals['totals'][$rubriek]['count'] ?? 0) > 0)
                                        @php
                                            $data = $taxTotals['totals'][$rubriek];
                                            $aftrekbaarTotal += $data['amount'];
                                            $aftrekbaarVat += $data['vat'];
                                        @endphp
                                        <div class="flex justify-between items-center p-2 bg-white dark:bg-gray-800 rounded">
                                            <div>
                                                <p class="font-medium text-sm">
                                                    Rubriek {{ $rubriek }}: {{ $data['label'] ?? 'Inkoop' }}
                                                    @if($rubriek === '2a')
                                                        <span class="text-xs text-gray-500">(21%/9% - Grootboek 5000/5100 of 4000-4999)</span>
                                                    @elseif($rubriek === '5b')
                                                        <span class="text-xs text-gray-500">(0%/verlegd - Grootboek 5200/5400)</span>
                                                    @endif
                                                </p>
                                                <p class="text-xs text-gray-500">{{ $data['count'] }} document(en)</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="font-bold text-sm">€{{ number_format($data['amount'], 2, ',', '.') }}</p>
                                                <p class="text-xs text-blue-600 dark:text-blue-400">BTW: €{{ number_format($data['vat'], 2, ',', '.') }}</p>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                                @if($aftrekbaarVat > 0)
                                    <div class="flex justify-between items-center p-2 bg-blue-100 dark:bg-blue-900/40 rounded border-t border-blue-300 dark:border-blue-700 mt-2">
                                        <p class="font-bold">Subtotaal Aftrekbaar:</p>
                                        <p class="font-bold text-blue-700 dark:text-blue-300">€{{ number_format($aftrekbaarVat, 2, ',', '.') }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Totaal Overzicht -->
                        <div class="flex justify-between items-center p-4 bg-primary-50 dark:bg-primary-900/20 rounded-lg border-2 border-primary-200 dark:border-primary-800 mt-4">
                            <div>
                                <p class="font-bold text-lg">Netto BTW Te Betalen</p>
                                <p class="text-sm text-gray-500">{{ $taxTotals['document_count'] ?? 0 }} goedgekeurde document(en)</p>
                            </div>
                            <div class="text-right">
                                @php
                                    $nettoBtw = $verschuldigdVat - $aftrekbaarVat;
                                @endphp
                                <p class="font-bold text-2xl {{ $nettoBtw >= 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                    {{ $nettoBtw >= 0 ? 'Te Betalen' : 'Te Ontvangen' }}: €{{ number_format(abs($nettoBtw), 2, ',', '.') }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    Verschuldigd: €{{ number_format($verschuldigdVat, 2, ',', '.') }} - 
                                    Aftrekbaar: €{{ number_format($aftrekbaarVat, 2, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg text-center">
                        <p class="text-gray-500">Wacht op goedgekeurde documenten om BTW te berekenen</p>
                    </div>
                @endif
            </div>

            <!-- Step 3: Review (only if issues AND documents are processed) -->
            @php
                $hasProcessedDocuments = ($docStatus['pending'] ?? 0) === 0 && ($docStatus['processing'] ?? 0) === 0;
                $shouldShowReview = ($docStatus['review_required'] ?? 0) > 0 || (($issues['has_issues'] ?? false) && $hasProcessedDocuments);
            @endphp
            
            @if($shouldShowReview)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center text-2xl bg-orange-100 dark:bg-orange-900 text-orange-600 dark:text-orange-400">
                                👀
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold">Stap 3: Review</h3>
                                <p class="text-sm text-gray-500">Controleer documenten met problemen</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 bg-orange-100 dark:bg-orange-900 text-orange-800 dark:text-orange-200 rounded-full text-sm font-medium">
                            {{ ($issues['total_count'] ?? 0) }} aandachtspunt(en)
                        </span>
                    </div>

                    @if(($docStatus['review_required'] ?? 0) > 0)
                        <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                            <div class="flex items-start gap-3">
                                <span class="text-2xl">📋</span>
                                <div>
                                    <h4 class="font-medium text-blue-900 dark:text-blue-100 mb-1">
                                        {{ $docStatus['review_required'] }} document(en) wachten op review
                                    </h4>
                                    <p class="text-sm text-blue-700 dark:text-blue-300">
                                        Ga naar <a href="{{ \App\Filament\Pages\DocumentReview::getUrl() }}" class="underline font-medium">Document Review</a> om deze te beoordelen.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(!empty($issues['issues']) && is_array($issues['issues']) && ($docStatus['pending'] ?? 0) === 0)
                        <div class="mt-4 space-y-2">
                            <h4 class="font-medium text-red-600 dark:text-red-400 mb-2">Problemen ({{ count($issues['issues']) }}):</h4>
                            @foreach(array_slice($issues['issues'], 0, 5) as $issue)
                                <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                                    <p class="font-medium">{{ $issue['document_name'] ?? 'Onbekend document' }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $issue['message'] ?? '' }}</p>
                                    @if(isset($issue['document_id']))
                                    <a href="{{ \App\Filament\Pages\DocumentReview::getUrl(['document' => $issue['document_id']]) }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline mt-1 inline-block">
                                        Bekijk document →
                                    </a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if(!empty($issues['warnings']) && is_array($issues['warnings']))
                        <div class="mt-4 space-y-2">
                            <h4 class="font-medium text-yellow-600 dark:text-yellow-400 mb-2">Waarschuwingen ({{ count($issues['warnings']) }}):</h4>
                            @foreach(array_slice($issues['warnings'], 0, 5) as $warning)
                                <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                                    <p class="font-medium">{{ $warning['document_name'] ?? 'Onbekend document' }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $warning['message'] ?? '' }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            <!-- Step 4: Submit -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center text-2xl
                            {{ $currentStep === 'ready_to_submit' ? 'bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400' : 
                               ($progress >= 100 ? 'bg-gray-100 dark:bg-gray-700 text-gray-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-400') }}">
                            @if($progress >= 100)
                                ✓
                            @else
                                📤
                            @endif
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold">Stap 4: Indienen</h3>
                            <p class="text-sm text-gray-500">Dien BTW aangifte in bij de Belastingdienst</p>
                        </div>
                    </div>
                    @if($isPeriodLocked)
                        <span class="px-3 py-1 bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 rounded-full text-sm font-medium">🔒 Afgesloten</span>
                    @elseif($currentStep === 'ready_to_submit')
                        <span class="px-3 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-full text-sm font-medium">Klaar</span>
                    @elseif($progress >= 100)
                        <span class="px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-full text-sm font-medium">Ingediend</span>
                    @endif
                </div>

                @if($isPeriodLocked)
                    <div class="mt-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-yellow-900 dark:text-yellow-100">
                                    Deze periode is afgesloten
                                </p>
                                <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">
                                    Om documenten te bewerken of correcties door te voeren, heropen de periode eerst.
                                </p>
                            </div>
                            <x-filament::button wire:click="unlockPeriod" color="warning" size="sm">
                                🔓 Heropenen
                            </x-filament::button>
                        </div>
                    </div>
                @elseif($canSubmit)
                    <div class="mt-4 flex gap-3">
                        <x-filament::button wire:click="preparePeriod" color="info" size="sm">
                            Voorbereiden
                        </x-filament::button>
                        <x-filament::button wire:click="submitPeriod" color="success" size="sm">
                            Indienen
                        </x-filament::button>
                        <x-filament::button wire:click="exportExcel" color="success" size="sm">
                            📊 Export Excel
                        </x-filament::button>
                        <x-filament::button wire:click="exportPdf" color="gray" size="sm">
                            📄 Export PDF
                        </x-filament::button>
                    </div>
                @else
                    <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <p class="text-gray-500 text-center">
                            @if(!($docStatus['all_approved'] ?? false))
                                Wacht op goedkeuring van alle documenten
                            @elseif($issues['has_issues'] ?? false)
                                Los eerst de problemen op voordat u kunt indienen
                            @else
                                Alle stappen zijn voltooid
                            @endif
                        </p>
                    </div>
                @endif
            </div>

            <!-- Next Actions -->
            @if(!empty($nextActions))
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
                    <h4 class="font-medium mb-2">Volgende Acties:</h4>
                    <ul class="space-y-1">
                        @foreach($nextActions as $action)
                            <li class="text-sm">• {{ $action['label'] ?? '' }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        @else
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-center">
                <p class="text-gray-500">Selecteer een klant om de workflow te bekijken</p>
            </div>
        @endif
    </div>

</x-filament-panels::page>

