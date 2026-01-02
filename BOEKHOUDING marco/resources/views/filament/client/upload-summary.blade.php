<div class="text-center py-6 sm:py-8 lg:py-10">
    @if($total === 0)
        <div class="text-5xl sm:text-6xl mb-4 sm:mb-6">⚠️</div>
        <h3 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white mb-2 sm:mb-3">
            Nog geen document gekozen
        </h3>
        <p class="text-base sm:text-lg text-gray-600 dark:text-gray-400">
            Ga terug en upload een document
        </p>
    @else
        <div class="text-6xl sm:text-7xl lg:text-8xl mb-6 sm:mb-8">✅</div>
        <h3 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-6 sm:mb-8 leading-tight">
            Perfect! Klaar om te versturen
        </h3>
        <div class="max-w-2xl mx-auto space-y-6 sm:space-y-8">
            {{-- Summary Card --}}
            <div class="p-6 sm:p-8 bg-gradient-to-br from-blue-50 to-purple-50 dark:from-gray-700 dark:to-gray-800 rounded-2xl sm:rounded-3xl border-2 border-blue-200 dark:border-gray-600 shadow-lg">
                <div class="grid grid-cols-2 gap-6 sm:gap-8">
                    <div class="text-center sm:text-left">
                        <div class="text-sm sm:text-base text-gray-600 dark:text-gray-400 font-medium mb-2">
                            Aantal documenten:
                        </div>
                        <div class="text-3xl sm:text-4xl lg:text-5xl font-bold text-blue-600 dark:text-blue-400">
                            {{ $total }}
                        </div>
                    </div>
                    <div class="text-center sm:text-right">
                        <div class="text-sm sm:text-base text-gray-600 dark:text-gray-400 font-medium mb-2">
                            Type:
                        </div>
                        <div class="text-lg sm:text-xl lg:text-2xl font-bold text-purple-600 dark:text-purple-400">
                            {{ ucfirst($typeLabel) }}
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Process Steps --}}
            <div class="bg-green-50 dark:bg-green-900/30 p-6 sm:p-8 rounded-2xl sm:rounded-3xl border-2 border-green-200 dark:border-green-700 shadow-lg">
                <h4 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white mb-4 sm:mb-6 flex items-center justify-center gap-2">
                    <span>📋</span>
                    <span>Wat gebeurt er nu?</span>
                </h4>
                <ul class="text-left space-y-3 sm:space-y-4 text-sm sm:text-base text-gray-800 dark:text-gray-200 max-w-md mx-auto">
                    <li class="flex items-start gap-3">
                        <span class="text-green-500 dark:text-green-400 text-xl font-bold mt-0.5 flex-shrink-0">✓</span>
                        <span class="leading-relaxed">Upload naar veilige cloud storage</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="text-green-500 dark:text-green-400 text-xl font-bold mt-0.5 flex-shrink-0">✓</span>
                        <span class="leading-relaxed">AI leest het document (10 seconden)</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="text-green-500 dark:text-green-400 text-xl font-bold mt-0.5 flex-shrink-0">✓</span>
                        <span class="leading-relaxed">BTW wordt gecontroleerd</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="text-green-500 dark:text-green-400 text-xl font-bold mt-0.5 flex-shrink-0">✓</span>
                        <span class="leading-relaxed">90% direct goedgekeurd!</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="text-green-500 dark:text-green-400 text-xl font-bold mt-0.5 flex-shrink-0">✓</span>
                        <span class="leading-relaxed">U krijgt een email notificatie</span>
                    </li>
                </ul>
            </div>
        </div>
    @endif
</div>

