<x-filament-panels::page>
    <style>
        /* Enhanced onboarding wizard styling with better spacing */
        .fi-fo-wizard-step {
            padding: 1.5rem 0;
        }
        
        .fi-fo-wizard-step-content {
            padding: 1.5rem 0;
        }
        
        @media (min-width: 640px) {
            .fi-fo-wizard-step {
                padding: 2rem 0;
            }
            
            .fi-fo-wizard-step-content {
                padding: 2.5rem 0;
            }
        }
        
        @media (min-width: 1024px) {
            .fi-fo-wizard-step-content {
                padding: 3rem 0;
            }
        }
    </style>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Page Header --}}
        <div class="mb-6 sm:mb-8 lg:mb-10 text-center">
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 dark:text-white mb-3 sm:mb-4 leading-tight">
                Handleiding
            </h1>
            <p class="text-base sm:text-lg text-gray-600 dark:text-gray-400 leading-relaxed max-w-2xl mx-auto">
                Leer hoe u MARCOFIC het beste gebruikt
            </p>
        </div>

        {{-- Enhanced Wizard Container --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl sm:rounded-3xl shadow-2xl p-4 sm:p-6 lg:p-8 xl:p-10 border-2 border-gray-200 dark:border-gray-700">
            {{ $this->form }}
        </div>
    </div>
</x-filament-panels::page>
