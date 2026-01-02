<div class="py-8 text-center">
    <div class="text-7xl mb-6">🎉</div>
    <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
        U Bent Helemaal Klaar!
    </h2>
    <p class="text-xl text-gray-600 dark:text-gray-400 mb-8 max-w-2xl mx-auto">
        U weet nu hoe u documenten uploadt, uw dashboard gebruikt, en optimale resultaten behaalt.
    </p>

    <!-- Quick Summary -->
    <div class="max-w-2xl mx-auto mb-10">
        <div class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-2xl p-8 border-2 border-gray-200 dark:border-gray-700">
            <h3 class="font-bold text-xl mb-6 text-gray-900 dark:text-white">📋 Samenvatting</h3>
            <div class="grid sm:grid-cols-2 gap-6 text-left">
                <div>
                    <div class="font-bold text-blue-600 dark:text-blue-400 mb-2">✅ Wat U Hebt Geleerd:</div>
                    <ul class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                        <li class="flex items-start gap-2">
                            <span class="text-green-500">→</span>
                            <span>Camera upload (5 seconden)</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-green-500">→</span>
                            <span>Dashboard navigatie</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-green-500">→</span>
                            <span>Pro foto tips</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-green-500">→</span>
                            <span>Status tracking</span>
                        </li>
                    </ul>
                </div>
                <div>
                    <div class="font-bold text-purple-600 dark:text-purple-400 mb-2">🚀 Volgende Stappen:</div>
                    <ul class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                        <li class="flex items-start gap-2">
                            <span class="text-purple-500">1.</span>
                            <span>Upload uw eerste document</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-purple-500">2.</span>
                            <span>Wacht 10 seconden</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-purple-500">3.</span>
                            <span>Check status op dashboard</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-purple-500">4.</span>
                            <span>Herhaal met meer documenten!</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Next Actions -->
    <div class="flex flex-col sm:flex-row gap-4 justify-center max-w-xl mx-auto mb-8">
        <a href="{{ \App\Filament\Client\Pages\DocumentUpload::getUrl() }}" class="flex items-center justify-center gap-3 px-8 py-5 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-2xl font-bold text-lg shadow-2xl hover:shadow-xl hover:scale-105 transition-all">
            <span class="text-3xl">📸</span>
            <span>Upload Eerste Document</span>
        </a>
        <a href="/klanten" class="flex items-center justify-center gap-3 px-8 py-5 bg-white dark:bg-gray-800 text-gray-900 dark:text-white border-2 border-gray-200 dark:border-gray-700 rounded-2xl font-bold text-lg hover:scale-105 transition-all">
            <span class="text-3xl">📊</span>
            <span>Bekijk Dashboard</span>
        </a>
    </div>

    <!-- Help -->
    <div class="max-w-2xl mx-auto p-6 bg-blue-50 dark:bg-blue-900/20 rounded-2xl border border-blue-200 dark:border-blue-800">
        <div class="text-center">
            <h4 class="font-bold text-lg text-gray-900 dark:text-white mb-3">💬 Hulp Nodig?</h4>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Ons MARCOFIC team staat altijd voor u klaar!
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center text-sm">
                <a href="mailto:marcofic2010@gmail.com" class="flex items-center justify-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 text-blue-600 dark:text-blue-400 rounded-lg font-semibold hover:scale-105 transition">
                    <span>📧</span>
                    <span>marcofic2010@gmail.com</span>
                </a>
                <a href="tel:0624995871" class="flex items-center justify-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 text-blue-600 dark:text-blue-400 rounded-lg font-semibold hover:scale-105 transition">
                    <span>📞</span>
                    <span>06-24995871</span>
                </a>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-500 mt-3">Ma-Vr 09:00-17:00 | Za 11:00-15:00</p>
        </div>
    </div>

    <!-- Quick Reference -->
    <div class="mt-8 max-w-2xl mx-auto">
        <div class="bg-gray-50 dark:bg-gray-900 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
            <h4 class="font-bold mb-4 text-gray-900 dark:text-white flex items-center gap-2">
                <span>📚</span> Snelle Referentie
            </h4>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <div class="font-semibold text-gray-700 dark:text-gray-300 mb-1">Upload Knop:</div>
                    <div class="text-gray-600 dark:text-gray-400">Menu → Document Uploaden</div>
                </div>
                <div>
                    <div class="font-semibold text-gray-700 dark:text-gray-300 mb-1">Status Zien:</div>
                    <div class="text-gray-600 dark:text-gray-400">Dashboard → Recente Documenten</div>
                </div>
                <div>
                    <div class="font-semibold text-gray-700 dark:text-gray-300 mb-1">Taken Bekijken:</div>
                    <div class="text-gray-600 dark:text-gray-400">Dashboard → Openstaande Taken</div>
                </div>
                <div>
                    <div class="font-semibold text-gray-700 dark:text-gray-300 mb-1">Handleiding:</div>
                    <div class="text-gray-600 dark:text-gray-400">Menu → Handleiding (altijd)</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Message -->
    <div class="mt-8 text-center">
        <p class="text-lg text-gray-600 dark:text-gray-400 mb-2">
            ✨ <strong>Succes met MARCOFIC!</strong> ✨
        </p>
        <p class="text-sm text-gray-500 dark:text-gray-500">
            U kunt deze handleiding altijd opnieuw bekijken via het menu
        </p>
    </div>
</div>

