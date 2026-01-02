<x-filament-panels::page>
    <div class="max-w-6xl mx-auto space-y-8">
        {{-- Clear Hero Header --}}
        <div class="p-8 sm:p-10 bg-gradient-to-r from-blue-600 to-purple-600 rounded-3xl shadow-2xl">
            <div class="flex flex-col sm:flex-row items-center gap-6 text-white">
                <div class="w-20 h-20 bg-white/20 backdrop-blur-xl rounded-3xl flex items-center justify-center text-5xl">
                    📸
                </div>
                <div class="flex-1 text-center sm:text-left">
                    <h1 class="text-4xl font-bold mb-3 text-white">Documenten Uploaden</h1>
                    <p class="text-xl text-white/90">
                        Upload in <span class="font-bold text-yellow-300">5 seconden</span>. 
                        Camera opent automatisch. 
                        <span class="font-bold text-yellow-300">90%</span> direct goedgekeurd!
                    </p>
                </div>
            </div>
        </div>
        
        {{-- Tips - HIGH CONTRAST --}}
        <div class="grid sm:grid-cols-3 gap-6">
            {{-- Tip 1 - White cards with dark text for light mode, Dark cards with white text for dark --}}
            <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-xl border-4 border-emerald-400 dark:border-emerald-500 hover:scale-105 transition-transform">
                <div class="text-5xl mb-4">✨</div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">
                    Beste Kwaliteit
                </h3>
                <p class="text-base text-gray-800 dark:text-gray-100 leading-relaxed mb-4">
                    Foto's geven <span class="font-bold text-emerald-600 dark:text-emerald-400">15-20% betere</span> OCR resultaten dan scans!
                </p>
                <div class="inline-flex px-3 py-2 bg-emerald-100 dark:bg-emerald-900 text-emerald-900 dark:text-emerald-100 rounded-lg text-sm font-bold">
                    ⚡ Aanbevolen
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-xl border-4 border-blue-400 dark:border-blue-500 hover:scale-105 transition-transform">
                <div class="text-5xl mb-4">💡</div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">
                    Goede Belichting
                </h3>
                <p class="text-base text-gray-800 dark:text-gray-100 leading-relaxed mb-4">
                    Gebruik <span class="font-bold text-blue-600 dark:text-blue-400">daglicht</span> of LED lamp. Vermijd schaduwen!
                </p>
                <div class="inline-flex px-3 py-2 bg-blue-100 dark:bg-blue-900 text-blue-900 dark:text-blue-100 rounded-lg text-sm font-bold">
                    💡 Belangrijk
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-xl border-4 border-amber-400 dark:border-amber-500 hover:scale-105 transition-transform">
                <div class="text-5xl mb-4">📐</div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">
                    Recht van Boven
                </h3>
                <p class="text-base text-gray-800 dark:text-gray-100 leading-relaxed mb-4">
                    Houd camera <span class="font-bold text-amber-600 dark:text-amber-400">parallel</span> aan bonnetje
                </p>
                <div class="inline-flex px-3 py-2 bg-amber-100 dark:bg-amber-900 text-amber-900 dark:text-amber-100 rounded-lg text-sm font-bold">
                    📐 Pro Tip
                </div>
            </div>
        </div>
        
        {{-- Upload Form --}}
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl p-8 border-2 border-gray-200 dark:border-gray-700">
            <form wire:submit="submit">
                {{ $this->form }}
            </form>
        </div>
        
        {{-- AI Info - HIGH CONTRAST --}}
        <div class="bg-blue-50 dark:bg-gray-800 p-8 rounded-3xl border-2 border-blue-200 dark:border-gray-700 shadow-lg">
            <div class="flex items-start gap-6">
                <div class="w-16 h-16 bg-blue-600 rounded-2xl flex items-center justify-center text-4xl shadow-lg flex-shrink-0">
                    🤖
                </div>
                <div class="flex-1">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                        Automatische AI Verwerking
                    </h3>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 bg-emerald-500 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"></path></svg>
                            </div>
                            <p class="text-lg text-gray-900 dark:text-white">
                                AI verwerkt binnen <span class="font-bold text-blue-600 dark:text-blue-400">10 seconden</span>
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 bg-emerald-500 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"></path></svg>
                            </div>
                            <p class="text-lg text-gray-900 dark:text-white">
                                <span class="font-bold text-blue-600 dark:text-blue-400">90%</span> automatisch goedgekeurd
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 bg-emerald-500 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"></path></svg>
                            </div>
                            <p class="text-lg text-gray-900 dark:text-white">
                                Email <span class="font-bold text-blue-600 dark:text-blue-400">notificatie</span> bij goedkeuring
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
