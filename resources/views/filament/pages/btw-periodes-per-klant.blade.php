<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @php
                $openCount = \App\Models\VatPeriod::where('status', 'open')->count();
                $voorbereidCount = \App\Models\VatPeriod::where('status', 'voorbereid')->count();
                $ingediendCount = \App\Models\VatPeriod::where('status', 'ingediend')->count();
                $afgeslotenCount = \App\Models\VatPeriod::where('status', 'afgesloten')->count();
            @endphp
            
            <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4 border border-yellow-200 dark:border-yellow-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">⏳ Open</p>
                        <p class="text-2xl font-bold text-yellow-900 dark:text-yellow-100">{{ $openCount }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-800 dark:text-blue-200">🟡 Voorbereid</p>
                        <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">{{ $voorbereidCount }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 border border-green-200 dark:border-green-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-green-800 dark:text-green-200">📤 Ingediend</p>
                        <p class="text-2xl font-bold text-green-900 dark:text-green-100">{{ $ingediendCount }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4 border border-red-200 dark:border-red-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-red-800 dark:text-red-200">🔒 Afgesloten</p>
                        <p class="text-2xl font-bold text-red-900 dark:text-red-100">{{ $afgeslotenCount }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        {{ $this->table }}
    </div>
</x-filament-panels::page>



