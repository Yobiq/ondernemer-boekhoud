<x-filament-panels::page>
    <div class="space-y-6">
        @php
            try {
                $insights = $this->insights;
                $clients = $this->clients;
            } catch (\Exception $e) {
                $insights = [
                    'period' => ['label' => 'Huidig Kwartaal', 'start' => now()->startOfQuarter(), 'end' => now()->endOfQuarter()],
                    'summary' => ['total_documents' => 0, 'sales_total' => 0, 'purchase_total' => 0, 'profit' => 0, 'profit_margin' => 0, 'vat_verschuldigd' => 0, 'vat_aftrekbaar' => 0, 'netto_btw' => 0],
                    'comparison' => ['sales_change' => 0, 'purchase_change' => 0, 'profit_change' => 0],
                    'vat_breakdown' => [],
                    'top_suppliers' => collect(),
                    'top_accounts' => collect(),
                    'monthly_trend' => collect(),
                    'automation_rate' => 0,
                ];
                $clients = collect();
            }
        @endphp

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Klant (optioneel)
                    </label>
                    <select wire:model.live="clientId" 
                            class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="">Alle Klanten</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Periode
                    </label>
                    <select wire:model.live="periodFilter" 
                            class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <option value="current_quarter">Huidig Kwartaal</option>
                        <option value="last_quarter">Vorige Kwartaal</option>
                        <option value="current_year">Huidig Jaar</option>
                        <option value="last_year">Vorig Jaar</option>
                    </select>
                </div>
                
                <div class="flex items-end">
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Periode: <strong>{{ $insights['period']['label'] }}</strong><br>
                        {{ \Carbon\Carbon::parse($insights['period']['start'])->format('d-m-Y') }} 
                        t/m 
                        {{ \Carbon\Carbon::parse($insights['period']['end'])->format('d-m-Y') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/30 dark:to-green-800/30 rounded-lg shadow p-6 border-2 border-green-200 dark:border-green-700">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-semibold text-green-700 dark:text-green-300">Omzet (Verkoop)</span>
                    <span class="text-2xl">📈</span>
                </div>
                <div class="text-3xl font-bold text-green-900 dark:text-green-100 mb-1">
                    €{{ number_format($insights['summary']['sales_total'], 2, ',', '.') }}
                </div>
                @if($insights['comparison']['sales_change'] != 0)
                    <div class="text-sm {{ $insights['comparison']['sales_change'] > 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $insights['comparison']['sales_change'] > 0 ? '↑' : '↓' }} 
                        {{ number_format(abs($insights['comparison']['sales_change']), 1) }}% vs vorige periode
                    </div>
                @endif
            </div>

            <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/30 rounded-lg shadow p-6 border-2 border-blue-200 dark:border-blue-700">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-semibold text-blue-700 dark:text-blue-300">Inkoop & Kosten</span>
                    <span class="text-2xl">📉</span>
                </div>
                <div class="text-3xl font-bold text-blue-900 dark:text-blue-100 mb-1">
                    €{{ number_format($insights['summary']['purchase_total'], 2, ',', '.') }}
                </div>
                @if($insights['comparison']['purchase_change'] != 0)
                    <div class="text-sm {{ $insights['comparison']['purchase_change'] < 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $insights['comparison']['purchase_change'] < 0 ? '↓' : '↑' }} 
                        {{ number_format(abs($insights['comparison']['purchase_change']), 1) }}% vs vorige periode
                    </div>
                @endif
            </div>

            <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/30 dark:to-purple-800/30 rounded-lg shadow p-6 border-2 border-purple-200 dark:border-purple-700">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-semibold text-purple-700 dark:text-purple-300">Winst</span>
                    <span class="text-2xl">💰</span>
                </div>
                <div class="text-3xl font-bold {{ $insights['summary']['profit'] >= 0 ? 'text-purple-900 dark:text-purple-100' : 'text-red-600 dark:text-red-400' }} mb-1">
                    €{{ number_format($insights['summary']['profit'], 2, ',', '.') }}
                </div>
                <div class="text-sm text-purple-600 dark:text-purple-400">
                    Marge: {{ number_format($insights['summary']['profit_margin'], 1) }}%
                </div>
                @if($insights['comparison']['profit_change'] != 0)
                    <div class="text-sm {{ $insights['comparison']['profit_change'] > 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $insights['comparison']['profit_change'] > 0 ? '↑' : '↓' }} 
                        {{ number_format(abs($insights['comparison']['profit_change']), 1) }}% vs vorige periode
                    </div>
                @endif
            </div>

            <div class="bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/30 dark:to-orange-800/30 rounded-lg shadow p-6 border-2 border-orange-200 dark:border-orange-700">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-semibold text-orange-700 dark:text-orange-300">Netto BTW</span>
                    <span class="text-2xl">🧾</span>
                </div>
                <div class="text-3xl font-bold {{ $insights['summary']['netto_btw'] >= 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }} mb-1">
                    {{ $insights['summary']['netto_btw'] >= 0 ? 'Te Betalen' : 'Te Ontvangen' }}
                </div>
                <div class="text-2xl font-bold {{ $insights['summary']['netto_btw'] >= 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                    €{{ number_format(abs($insights['summary']['netto_btw']), 2, ',', '.') }}
                </div>
            </div>
        </div>

        <!-- BTW Breakdown -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">BTW Overzicht per Rubriek</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                    <h4 class="font-medium text-green-900 dark:text-green-100 mb-2">BTW Verschuldigd</h4>
                    @php
                        $verschuldigd = collect($insights['vat_breakdown'])->filter(fn($v, $k) => in_array($k, ['1a', '1b', '1c']));
                    @endphp
                    @foreach($verschuldigd as $rubriek => $data)
                        <div class="flex justify-between text-sm mb-1">
                            <span>Rubriek {{ $rubriek }}:</span>
                            <span class="font-medium">€{{ number_format($data['vat'], 2, ',', '.') }}</span>
                        </div>
                    @endforeach
                    <div class="mt-2 pt-2 border-t border-green-300 dark:border-green-700">
                        <div class="flex justify-between font-bold">
                            <span>Totaal:</span>
                            <span>€{{ number_format($insights['summary']['vat_verschuldigd'], 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                    <h4 class="font-medium text-blue-900 dark:text-blue-100 mb-2">BTW Aftrekbaar</h4>
                    @php
                        $aftrekbaar = collect($insights['vat_breakdown'])->filter(fn($v, $k) => in_array($k, ['2a', '5b']));
                    @endphp
                    @foreach($aftrekbaar as $rubriek => $data)
                        <div class="flex justify-between text-sm mb-1">
                            <span>Rubriek {{ $rubriek }}:</span>
                            <span class="font-medium">€{{ number_format($data['vat'], 2, ',', '.') }}</span>
                        </div>
                    @endforeach
                    <div class="mt-2 pt-2 border-t border-blue-300 dark:border-blue-700">
                        <div class="flex justify-between font-bold">
                            <span>Totaal:</span>
                            <span>€{{ number_format($insights['summary']['vat_aftrekbaar'], 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Suppliers & Accounts -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Top 10 Leveranciers</h3>
                <div class="space-y-2">
                    @foreach($insights['top_suppliers'] as $supplier)
                        <div class="flex justify-between items-center p-2 bg-gray-50 dark:bg-gray-700 rounded">
                            <div>
                                <p class="font-medium text-sm">{{ $supplier['name'] }}</p>
                                <p class="text-xs text-gray-500">{{ $supplier['count'] }} document(en)</p>
                            </div>
                            <p class="font-bold">€{{ number_format($supplier['total'], 2, ',', '.') }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Top 10 Grootboekrekeningen</h3>
                <div class="space-y-2">
                    @foreach($insights['top_accounts'] as $account)
                        <div class="flex justify-between items-center p-2 bg-gray-50 dark:bg-gray-700 rounded">
                            <div>
                                <p class="font-medium text-sm">{{ $account['code'] }} - {{ $account['description'] }}</p>
                                <p class="text-xs text-gray-500">{{ $account['count'] }} document(en)</p>
                            </div>
                            <p class="font-bold">€{{ number_format($account['total'], 2, ',', '.') }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Automation Rate -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Automatisering</h3>
            <div class="flex items-center gap-4">
                <div class="flex-1">
                    <div class="flex justify-between mb-2">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Automatisch Goedgekeurd</span>
                        <span class="text-sm font-medium">{{ number_format($insights['automation_rate'], 1) }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4">
                        <div class="bg-green-600 h-4 rounded-full transition-all duration-500" 
                             style="width: {{ $insights['automation_rate'] }}%"></div>
                    </div>
                </div>
                <div class="text-3xl">
                    @if($insights['automation_rate'] >= 90)
                        🎯
                    @elseif($insights['automation_rate'] >= 70)
                        ✅
                    @else
                        ⚠️
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>

