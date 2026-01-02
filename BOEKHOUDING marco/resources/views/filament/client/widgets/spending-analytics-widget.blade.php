@php
    $data = $this->getViewData();
    $thisMonth = $data['thisMonth'];
    $lastMonth = $data['lastMonth'];
    $change = $data['change'];
    $thisYear = $data['thisYear'];
    $avgPerMonth = $data['avgPerMonth'];
@endphp

<x-filament-widgets::widget>
    <div class="spending-analytics-widget p-4 sm:p-6 lg:p-8">
        <div class="mb-4 sm:mb-6">
            <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white mb-1">
                💰 Uitgaven Overzicht
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Deze maand vs vorige maand
            </p>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            {{-- This Month Card --}}
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/30 rounded-xl sm:rounded-2xl p-4 sm:p-5 border-2 border-blue-200 dark:border-blue-700">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs sm:text-sm font-semibold text-blue-700 dark:text-blue-300 uppercase tracking-wide">
                        Deze Maand
                    </span>
                    <span class="text-xl sm:text-2xl">💰</span>
                </div>
                <div class="text-2xl sm:text-3xl lg:text-4xl font-bold text-blue-900 dark:text-blue-100 mb-1">
                    €{{ number_format($thisMonth, 2, ',', '.') }}
                </div>
                <div class="flex items-center gap-2 mt-2">
                    @if($change != 0)
                        <span class="text-xs sm:text-sm font-semibold {{ $change >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ $change >= 0 ? '↑' : '↓' }} {{ abs($change) }}%
                        </span>
                        <span class="text-xs text-gray-600 dark:text-gray-400">
                            vs vorige maand
                        </span>
                    @else
                        <span class="text-xs text-gray-600 dark:text-gray-400">
                            Geen verschil
                        </span>
                    @endif
                </div>
            </div>
            
            {{-- Last Month Card --}}
            <div class="bg-gray-50 dark:bg-gray-800 rounded-xl sm:rounded-2xl p-4 sm:p-5 border-2 border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs sm:text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">
                        Vorige Maand
                    </span>
                    <span class="text-xl sm:text-2xl">📊</span>
                </div>
                <div class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white">
                    €{{ number_format($lastMonth, 2, ',', '.') }}
                </div>
            </div>
            
            {{-- This Year Card --}}
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/30 dark:to-purple-800/30 rounded-xl sm:rounded-2xl p-4 sm:p-5 border-2 border-purple-200 dark:border-purple-700">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs sm:text-sm font-semibold text-purple-700 dark:text-purple-300 uppercase tracking-wide">
                        Dit Jaar
                    </span>
                    <span class="text-xl sm:text-2xl">📅</span>
                </div>
                <div class="text-2xl sm:text-3xl lg:text-4xl font-bold text-purple-900 dark:text-purple-100">
                    €{{ number_format($thisYear, 2, ',', '.') }}
                </div>
            </div>
            
            {{-- Average Per Month Card --}}
            <div class="bg-gray-50 dark:bg-gray-800 rounded-xl sm:rounded-2xl p-4 sm:p-5 border-2 border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs sm:text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">
                        Gemiddeld/Maand
                    </span>
                    <span class="text-xl sm:text-2xl">📈</span>
                </div>
                <div class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white">
                    €{{ number_format($avgPerMonth, 2, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>

