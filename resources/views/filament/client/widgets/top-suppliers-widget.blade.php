@php
    $data = $this->getViewData();
    $suppliers = $data['suppliers'];
    $hasData = $data['hasData'];
    
    // Calculate max for progress bars
    $maxAmount = !empty($suppliers) ? max(array_column($suppliers, 'amount')) : 0;
@endphp

<x-filament-widgets::widget>
    <div class="top-suppliers-widget p-3 sm:p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="mb-3">
            <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white">
                🏢 Top Leveranciers
            </h3>
            <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">
                Op basis van totaalbedrag
            </p>
        </div>
        
        @if($hasData && !empty($suppliers))
            <div class="space-y-2.5">
                @foreach($suppliers as $index => $supplier)
                    <div class="supplier-item">
                        <div class="flex items-start justify-between mb-1.5">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1.5 mb-0.5">
                                    <span class="text-sm font-semibold text-gray-400 dark:text-gray-600">
                                        #{{ $index + 1 }}
                                    </span>
                                    <h4 class="text-xs sm:text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ $supplier['name'] }}
                                    </h4>
                                </div>
                                <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
                                    <span>{{ $supplier['count'] }} {{ $supplier['count'] === 1 ? 'doc' : 'docs' }}</span>
                                    <span>•</span>
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        €{{ number_format($supplier['amount'], 2, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        {{-- Progress Bar - Thinner --}}
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                            <div 
                                class="h-full bg-gradient-to-r from-blue-500 to-purple-600 rounded-full transition-all duration-500"
                                style="width: {{ $maxAmount > 0 ? ($supplier['amount'] / $maxAmount) * 100 : 0 }}%"
                            ></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-6 text-gray-500 dark:text-gray-400">
                <p class="text-xs sm:text-sm">Nog geen leveranciers gegevens beschikbaar</p>
            </div>
        @endif
    </div>
</x-filament-widgets::widget>

<style>
.supplier-item {
    padding: 0.5rem;
    border-radius: 0.375rem;
    transition: background-color 0.2s;
}

.supplier-item:hover {
    background-color: rgb(249 250 251);
}

.dark .supplier-item:hover {
    background-color: rgb(31 41 55);
}
</style>

