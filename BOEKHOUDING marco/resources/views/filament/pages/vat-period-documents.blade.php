<x-filament-panels::page>
    <div class="space-y-6">
        @if($period)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h2 class="text-xl font-bold mb-4">BTW Periode: {{ $period->period_string }}</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Klant</div>
                        <div class="font-semibold">{{ $period->client->name }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Status</div>
                        <div class="font-semibold">
                            @if($period->status === 'open')
                                ⏳ Open
                            @elseif($period->status === 'voorbereid')
                                🟡 Voorbereid
                            @elseif($period->status === 'ingediend')
                                📤 Ingediend
                            @else
                                🔒 Afgesloten
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Start</div>
                        <div class="font-semibold">{{ $period->period_start->format('d-m-Y') }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Eind</div>
                        <div class="font-semibold">{{ $period->period_end->format('d-m-Y') }}</div>
                    </div>
                </div>
            </div>

            @if($rubriek)
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                    <h3 class="font-semibold text-blue-900 dark:text-blue-100">
                        Rubriek: {{ $rubriek }}
                    </h3>
                </div>
            @endif
        @endif

        {{ $this->table }}
    </div>
</x-filament-panels::page>


