@php
    $ocrData = $document->ocr_data ?? [];
    $hasOcrData = !empty($ocrData) && is_array($ocrData);
    $rawText = $ocrData['raw_text'] ?? '';
    $supplier = $ocrData['supplier'] ?? [];
    $invoice = $ocrData['invoice'] ?? [];
    $amounts = $ocrData['amounts'] ?? [];
    $confidenceScores = $ocrData['confidence_scores'] ?? null;
@endphp

<div class="space-y-4">
    @if(!$hasOcrData)
        <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
            <div class="flex items-center gap-2">
                <span class="text-yellow-600 dark:text-yellow-400">⚠️</span>
                <div>
                    <p class="font-semibold text-yellow-900 dark:text-yellow-100">Geen OCR Data Beschikbaar</p>
                    <p class="text-sm text-yellow-700 dark:text-yellow-300">
                        @if($document->status === 'pending')
                            Document wacht op OCR verwerking...
                        @elseif($document->status === 'ocr_processing')
                            OCR verwerking is bezig...
                        @else
                            OCR data is niet beschikbaar voor dit document.
                        @endif
                    </p>
                </div>
            </div>
        </div>
    @else
        <!-- OCR Status Badge -->
        <div class="flex items-center justify-between p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
            <div class="flex items-center gap-2">
                <span class="text-blue-600 dark:text-blue-400">✅</span>
                <span class="font-semibold text-blue-900 dark:text-blue-100">OCR Data Geëxtraheerd</span>
            </div>
            @if($confidenceScores && isset($confidenceScores['average']))
                <span class="px-2 py-1 text-xs font-bold rounded
                    @if($confidenceScores['average'] >= 90) bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-200
                    @elseif($confidenceScores['average'] >= 70) bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-200
                    @else bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200
                    @endif">
                    {{ number_format($confidenceScores['average'], 1) }}% Confidence
                </span>
            @endif
        </div>

        <!-- Extracted Data Summary -->
        <div class="grid grid-cols-2 gap-4">
            @if(!empty($supplier['name']))
                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Leverancier</p>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $supplier['name'] }}</p>
                </div>
            @endif
            
            @if(!empty($invoice['date']))
                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Datum</p>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $invoice['date'] }}</p>
                </div>
            @endif
            
            @if(!empty($amounts['incl']))
                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Bedrag (Incl. BTW)</p>
                    <p class="font-semibold text-green-600 dark:text-green-400">€{{ number_format($amounts['incl'], 2, ',', '.') }}</p>
                </div>
            @endif
            
            @if(!empty($amounts['vat_rate']))
                <div class="p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">BTW Tarief</p>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $amounts['vat_rate'] }}%</p>
                </div>
            @endif
        </div>

        <!-- Raw OCR Text (Collapsible) -->
        @if(!empty($rawText))
            <details class="group">
                <summary class="cursor-pointer p-3 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                    <div class="flex items-center justify-between">
                        <span class="font-medium text-gray-900 dark:text-white">📄 OCR Raw Text</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400 group-open:hidden">Klik om te tonen</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400 hidden group-open:inline">Klik om te verbergen</span>
                    </div>
                </summary>
                <div class="mt-2 p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 max-h-64 overflow-y-auto">
                    <pre class="text-xs text-gray-700 dark:text-gray-300 whitespace-pre-wrap font-mono">{{ $rawText }}</pre>
                </div>
            </details>
        @endif

        <!-- Full OCR Data (JSON - Collapsible) -->
        <details class="group">
            <summary class="cursor-pointer p-3 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                <div class="flex items-center justify-between">
                    <span class="font-medium text-gray-900 dark:text-white">🔍 Volledige OCR Data (JSON)</span>
                    <span class="text-sm text-gray-500 dark:text-gray-400 group-open:hidden">Klik om te tonen</span>
                    <span class="text-sm text-gray-500 dark:text-gray-400 hidden group-open:inline">Klik om te verbergen</span>
                </div>
            </summary>
            <div class="mt-2 p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 max-h-96 overflow-y-auto">
                <pre class="text-xs text-gray-700 dark:text-gray-300 whitespace-pre-wrap font-mono">{{ json_encode($ocrData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        </details>
    @endif
</div>


