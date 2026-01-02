<div class="document-info-card p-4 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
    <div class="flex items-center justify-between mb-3">
        <div>
            <div class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Bestandsnaam</div>
            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $document->original_filename }}</div>
        </div>
        <div class="text-right">
            <div class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Confidence</div>
            <div class="text-lg font-bold text-{{ $color }}-600 dark:text-{{ $color }}-400">
                {{ $icon }} {{ $score }}%
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-2 gap-3 text-sm">
        <div>
            <div class="text-gray-600 dark:text-gray-400">Klant</div>
            <div class="font-semibold text-gray-900 dark:text-white">{{ $document->client->name ?? 'Onbekend' }}</div>
        </div>
        <div>
            <div class="text-gray-600 dark:text-gray-400">Type</div>
            <div class="font-semibold text-gray-900 dark:text-white">
                @match($document->document_type)
                    @case('receipt') 🧾 Bonnetje @break
                    @case('purchase_invoice') 📄 Inkoopfactuur @break
                    @case('bank_statement') 🏦 Bankafschrift @break
                    @case('sales_invoice') 🧑‍💼 Verkoopfactuur @break
                    @default 📁 Overig
                @endmatch
            </div>
        </div>
        @if($document->amount_incl)
        <div class="col-span-2">
            <div class="text-gray-600 dark:text-gray-400">Bedrag</div>
            <div class="text-lg font-bold text-gray-900 dark:text-white">
                €{{ number_format($document->amount_incl, 2, ',', '.') }}
            </div>
        </div>
        @endif
    </div>
</div>

