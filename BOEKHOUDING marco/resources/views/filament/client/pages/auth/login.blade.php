<x-filament-panels::page.simple>
    <div class="w-full max-w-md mx-auto">
        {{-- MARCOFIC Header with Better Spacing --}}
        <div class="text-center mb-8 sm:mb-10 lg:mb-12">
            <div class="inline-flex items-center justify-center w-16 h-16 sm:w-20 sm:h-20 lg:w-24 lg:h-24 bg-gradient-to-br from-yellow-600 to-yellow-700 rounded-3xl mb-4 sm:mb-6 shadow-2xl transform hover:rotate-6 transition-all duration-300">
                <span class="text-3xl sm:text-4xl lg:text-5xl">💎</span>
            </div>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold mb-2 sm:mb-3 leading-tight">
                <span class="bg-gradient-to-r from-yellow-400 to-yellow-600 bg-clip-text text-transparent">
                    MARCOFIC
                </span>
            </h1>
            <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400 leading-relaxed">
                Welkom terug bij uw persoonlijke portaal
            </p>
        </div>

        {{-- Welcome Message with Better Spacing --}}
        <div class="mb-6 sm:mb-8 p-5 sm:p-6 lg:p-8 bg-gradient-to-br from-blue-50 to-purple-50 dark:from-gray-800 dark:to-gray-900 rounded-2xl sm:rounded-3xl border-2 border-blue-200 dark:border-gray-700 shadow-lg">
            <div class="flex items-start gap-4 sm:gap-5">
                <div class="text-3xl sm:text-4xl flex-shrink-0">👋</div>
                <div class="flex-1">
                    <h3 class="font-bold text-lg sm:text-xl text-gray-900 dark:text-white mb-2 sm:mb-3">
                        Welkom bij MARCOFIC!
                    </h3>
                    <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400 leading-relaxed">
                        Upload uw bonnetjes en facturen eenvoudig met uw telefoon camera. 
                        Automatische verwerking in 10 seconden. 90% direct goedgekeurd!
                    </p>
                </div>
            </div>
        </div>

        {{-- Login Form --}}
        <div class="mb-6 sm:mb-8">
            <x-filament-panels::form wire:submit="authenticate">
                {{ $this->form }}

                <x-filament-panels::form.actions
                    :actions="$this->getCachedFormActions()"
                    :full-width="$this->hasFullWidthFormActions()"
                />
            </x-filament-panels::form>
        </div>

        {{-- Demo Credentials with Better Spacing --}}
        @if(app()->environment('local'))
        <div class="mb-6 sm:mb-8 p-4 sm:p-5 lg:p-6 bg-yellow-50 dark:bg-yellow-900/20 rounded-xl sm:rounded-2xl border-2 border-yellow-200 dark:border-yellow-800 shadow-sm">
            <div class="text-xs sm:text-sm font-semibold text-yellow-800 dark:text-yellow-300 mb-3 sm:mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
                TEST ACCOUNTS (Demo)
            </div>
            <div class="space-y-2 sm:space-y-3 text-xs sm:text-sm">
                <div class="grid grid-cols-2 gap-2 sm:gap-3">
                    <div class="font-mono text-gray-700 dark:text-gray-300 break-all">📧 jan@goudenlepel.nl</div>
                    <div class="font-mono text-gray-700 dark:text-gray-300">🔑 demo123</div>
                </div>
                <div class="grid grid-cols-2 gap-2 sm:gap-3">
                    <div class="font-mono text-gray-700 dark:text-gray-300 break-all">📧 lisa@techstart.nl</div>
                    <div class="font-mono text-gray-700 dark:text-gray-300">🔑 demo123</div>
                </div>
                <div class="grid grid-cols-2 gap-2 sm:gap-3">
                    <div class="font-mono text-gray-700 dark:text-gray-300 break-all">📧 mo@kledingwinkel-ams.nl</div>
                    <div class="font-mono text-gray-700 dark:text-gray-300">🔑 demo123</div>
                </div>
            </div>
        </div>
        @endif

        {{-- Features with Better Spacing --}}
        <div class="mb-6 sm:mb-8 grid grid-cols-3 gap-3 sm:gap-4 text-center">
            <div class="p-4 sm:p-5 bg-gray-50 dark:bg-gray-800 rounded-xl sm:rounded-2xl border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow">
                <div class="text-2xl sm:text-3xl mb-2 sm:mb-3">📸</div>
                <div class="text-xs sm:text-sm font-semibold text-gray-700 dark:text-gray-300">Camera Upload</div>
            </div>
            <div class="p-4 sm:p-5 bg-gray-50 dark:bg-gray-800 rounded-xl sm:rounded-2xl border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow">
                <div class="text-2xl sm:text-3xl mb-2 sm:mb-3">🤖</div>
                <div class="text-xs sm:text-sm font-semibold text-gray-700 dark:text-gray-300">90% Auto</div>
            </div>
            <div class="p-4 sm:p-5 bg-gray-50 dark:bg-gray-800 rounded-xl sm:rounded-2xl border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow">
                <div class="text-2xl sm:text-3xl mb-2 sm:mb-3">✅</div>
                <div class="text-xs sm:text-sm font-semibold text-gray-700 dark:text-gray-300">100% BTW</div>
            </div>
        </div>

        {{-- Contact Info with Better Spacing --}}
        <div class="mb-6 sm:mb-8 text-center text-xs sm:text-sm text-gray-500 dark:text-gray-400 space-y-3 sm:space-y-4">
            <p class="leading-relaxed">Hulp nodig? Neem contact op:</p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-4 text-xs sm:text-sm">
                <a href="mailto:marcofic2010@gmail.com" class="hover:text-blue-600 dark:hover:text-yellow-400 transition-colors flex items-center gap-2">
                    <span>📧</span>
                    <span class="break-all">marcofic2010@gmail.com</span>
                </a>
                <span class="hidden sm:inline text-gray-300">•</span>
                <a href="tel:0624995871" class="hover:text-blue-600 dark:hover:text-yellow-400 transition-colors flex items-center gap-2">
                    <span>📞</span>
                    <span>06-24995871</span>
                </a>
            </div>
            <p class="text-xs">Ma-Vr 09:00-17:00 | Za 11:00-15:00</p>
        </div>

        {{-- Back to Home --}}
        <div class="text-center">
            <a href="/" class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-yellow-400 transition-colors inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Terug naar home
            </a>
        </div>
    </div>

    <style>
        /* Custom styling for MARCOFIC login */
        .fi-simple-main {
            background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
            padding: 2rem 1rem;
        }
        
        @media (min-width: 640px) {
            .fi-simple-main {
                padding: 3rem 1.5rem;
            }
        }
        
        @media (prefers-color-scheme: dark) {
            .fi-simple-main {
                background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            }
        }
    </style>
</x-filament-panels::page.simple>
