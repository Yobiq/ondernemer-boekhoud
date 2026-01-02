<x-filament-panels::page>
    <div class="space-y-6">
        {{ $this->form }}
        
        @if($this->comparison)
            <x-filament::section>
                <x-slot name="heading">
                    Vergelijking Resultaten
                </x-slot>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left p-2">Rubriek</th>
                                <th class="text-right p-2">Periode 1 Grondslag</th>
                                <th class="text-right p-2">Periode 1 BTW</th>
                                <th class="text-right p-2">Periode 2 Grondslag</th>
                                <th class="text-right p-2">Periode 2 BTW</th>
                                <th class="text-right p-2">Verschil Grondslag</th>
                                <th class="text-right p-2">Verschil BTW</th>
                                <th class="text-right p-2">% Verandering</th>
                                <th class="text-center p-2">Variantie</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($this->comparison as $comp)
                                <tr class="border-b hover:bg-gray-50 dark:hover:bg-gray-900">
                                    <td class="p-2 font-semibold">Rubriek {{ $comp['rubriek'] }}</td>
                                    <td class="p-2 text-right">€{{ number_format($comp['period1_amount'], 2, ',', '.') }}</td>
                                    <td class="p-2 text-right">€{{ number_format($comp['period1_vat'], 2, ',', '.') }}</td>
                                    <td class="p-2 text-right">€{{ number_format($comp['period2_amount'], 2, ',', '.') }}</td>
                                    <td class="p-2 text-right">€{{ number_format($comp['period2_vat'], 2, ',', '.') }}</td>
                                    <td class="p-2 text-right {{ $comp['amount_diff'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $comp['amount_diff'] >= 0 ? '+' : '' }}€{{ number_format($comp['amount_diff'], 2, ',', '.') }}
                                    </td>
                                    <td class="p-2 text-right {{ $comp['vat_diff'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $comp['vat_diff'] >= 0 ? '+' : '' }}€{{ number_format($comp['vat_diff'], 2, ',', '.') }}
                                    </td>
                                    <td class="p-2 text-right {{ abs($comp['amount_percent_change']) > 10 ? 'text-red-600' : (abs($comp['amount_percent_change']) > 5 ? 'text-yellow-600' : 'text-green-600') }}">
                                        {{ $comp['amount_percent_change'] >= 0 ? '+' : '' }}{{ number_format($comp['amount_percent_change'], 1) }}%
                                    </td>
                                    <td class="p-2 text-center">
                                        @if($comp['variance'] === 'high')
                                            <span class="px-2 py-1 bg-red-100 dark:bg-red-900/20 text-red-800 dark:text-red-200 rounded text-xs font-semibold">Hoog</span>
                                        @elseif($comp['variance'] === 'medium')
                                            <span class="px-2 py-1 bg-yellow-100 dark:bg-yellow-900/20 text-yellow-800 dark:text-yellow-200 rounded text-xs font-semibold">Medium</span>
                                        @else
                                            <span class="px-2 py-1 bg-green-100 dark:bg-green-900/20 text-green-800 dark:text-green-200 rounded text-xs font-semibold">Laag</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>

