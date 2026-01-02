<div class="space-y-2">
    @if($isValid)
        <div class="p-2 bg-green-50 dark:bg-green-900/20 rounded border border-green-200 dark:border-green-800">
            <div class="flex items-center gap-2">
                <span class="text-green-600 dark:text-green-400">✅</span>
                <span class="text-green-800 dark:text-green-200 font-semibold">BTW berekening is correct</span>
            </div>
        </div>
    @else
        <div class="p-2 bg-red-50 dark:bg-red-900/20 rounded border border-red-200 dark:border-red-800">
            <div class="flex items-center gap-2 mb-2">
                <span class="text-red-600 dark:text-red-400">❌</span>
                <span class="text-red-800 dark:text-red-200 font-semibold">Validatiefouten gevonden:</span>
            </div>
            <ul class="list-disc list-inside text-sm text-red-700 dark:text-red-300 space-y-1">
                @foreach($errors as $field => $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    @if(!empty($warnings))
        <div class="p-2 bg-yellow-50 dark:bg-yellow-900/20 rounded border border-yellow-200 dark:border-yellow-800">
            <div class="flex items-center gap-2 mb-2">
                <span class="text-yellow-600 dark:text-yellow-400">⚠️</span>
                <span class="text-yellow-800 dark:text-yellow-200 font-semibold">Waarschuwingen:</span>
            </div>
            <ul class="list-disc list-inside text-sm text-yellow-700 dark:text-yellow-300 space-y-1">
                @foreach($warnings as $field => $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
            @if(isset($calculatedRubriek))
                <div class="mt-2 text-xs text-gray-600 dark:text-gray-400">
                    Berekende BTW Rubriek: <span class="font-medium">{{ $calculatedRubriek }}</span>
                </div>
            @endif
        </div>
    @endif
</div>



