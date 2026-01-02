<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @php
                $totalAccounts = \App\Models\LedgerAccount::count();
                $activeAccounts = \App\Models\LedgerAccount::where('active', true)->count();
                $totalDocuments = \App\Models\Document::where('status', 'approved')->count();
                $totalAmount = \App\Models\Document::where('status', 'approved')->sum('amount_incl');
            @endphp
            
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Totaal Rekeningen</p>
                        <p class="text-2xl font-bold">{{ $totalAccounts }}</p>
                    </div>
                    <div class="text-3xl">📊</div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Actieve Rekeningen</p>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $activeAccounts }}</p>
                    </div>
                    <div class="text-3xl">✅</div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Goedgekeurde Documenten</p>
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $totalDocuments }}</p>
                    </div>
                    <div class="text-3xl">📄</div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Totaal Bedrag</p>
                        <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                            €{{ number_format($totalAmount, 2, ',', '.') }}
                        </p>
                    </div>
                    <div class="text-3xl">💰</div>
                </div>
            </div>
        </div>

        <!-- Info Banner -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-400 dark:border-blue-600 p-4 rounded">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                        Grootboek Rekenschema Overzicht
                    </h3>
                    <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                        <p>
                            Dit overzicht toont alle grootboekrekeningen met het aantal gekoppelde documenten en totale bedragen.
                            Documenten worden automatisch gekoppeld aan de juiste grootboekrekening op basis van:
                        </p>
                        <ul class="list-disc list-inside mt-2 space-y-1">
                            <li><strong>Leverancier historie</strong> (hoogste prioriteit)</li>
                            <li><strong>Document type</strong> (verkoop → 8000-8999, inkoop → 5000-5999, kosten → 4000-4999)</li>
                            <li><strong>Trefwoord matching</strong> (slimme suggesties)</li>
                            <li><strong>BTW type matching</strong> (21%, 9%, 0%)</li>
                        </ul>
                        <p class="mt-2">
                            Klik op "Bekijk Documenten" om alle documenten voor een specifieke rekening te zien.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        {{ $this->table }}
    </div>
</x-filament-panels::page>


