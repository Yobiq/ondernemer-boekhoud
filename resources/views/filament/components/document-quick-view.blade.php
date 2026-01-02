<div class="space-y-4">
    <!-- Document Info -->
    <div class="grid grid-cols-2 gap-4">
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Type</p>
            <p class="text-base font-semibold">
                @php
                    $typeLabel = match($document->document_type) {
                        'receipt' => '🧾 Bonnetje',
                        'purchase_invoice' => '📄 Inkoopfactuur',
                        'bank_statement' => '🏦 Bankafschrift',
                        'sales_invoice' => '🧑‍💼 Verkoopfactuur',
                        default => '📁 Overig',
                    };
                @endphp
                {{ $typeLabel }}
            </p>
        </div>
        
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</p>
            <p class="text-base font-semibold">
                @php
                    $statusLabel = match($document->status) {
                        'pending' => '⏳ In Wachtrij',
                        'ocr_processing' => '🔄 OCR Bezig',
                        'review_required' => '👀 Review Nodig',
                        'approved' => '✅ Goedgekeurd',
                        default => $document->status,
                    };
                @endphp
                {{ $statusLabel }}
            </p>
        </div>
        
        @if($document->supplier_name)
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Leverancier</p>
            <p class="text-base font-semibold">{{ $document->supplier_name }}</p>
        </div>
        @endif
        
        @if($document->document_date)
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Datum</p>
            <p class="text-base font-semibold">{{ $document->document_date->format('d-m-Y') }}</p>
        </div>
        @endif
        
        @if($document->amount_incl)
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Bedrag (Incl. BTW)</p>
            <p class="text-base font-semibold text-green-600 dark:text-green-400">€{{ number_format($document->amount_incl, 2, ',', '.') }}</p>
        </div>
        @endif
        
        @if($document->confidence_score)
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">OCR Confidence</p>
            <p class="text-base font-semibold 
                @if($document->confidence_score >= 90) text-green-600 dark:text-green-400
                @elseif($document->confidence_score >= 70) text-yellow-600 dark:text-yellow-400
                @else text-red-600 dark:text-red-400
                @endif">
                {{ $document->confidence_score }}%
            </p>
        </div>
        @endif
    </div>
    
    <!-- Actions -->
    <div class="flex gap-2 pt-4 border-t border-gray-200 dark:border-gray-700">
        <a href="{{ route('documents.file', $document) }}" target="_blank" 
           class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-center text-sm font-medium">
            📄 Bekijk Document
        </a>
        @if($document->status === 'review_required')
        <a href="{{ \App\Filament\Pages\DocumentReview::getUrl(['document' => $document->id]) }}" 
           class="flex-1 px-4 py-2 bg-warning-600 text-white rounded-lg hover:bg-warning-700 text-center text-sm font-medium">
            👀 Beoordelen
        </a>
        @endif
    </div>
</div>

