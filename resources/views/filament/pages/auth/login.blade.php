<x-filament-panels::page.simple>
    <div class="w-full">
        <!-- MARCOFIC Admin Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-blue-600 to-purple-600 rounded-3xl mb-4 shadow-2xl">
                <span class="text-4xl">🔐</span>
            </div>
            <h1 class="text-4xl font-bold mb-2">
                <span class="bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                    MARCOFIC
                </span>
            </h1>
            <p class="text-gray-600 dark:text-gray-400 text-sm font-semibold">
                Boekhouder Portaal
            </p>
        </div>

        <!-- Admin Info -->
        <div class="mb-6 p-6 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-900 rounded-2xl border border-blue-200 dark:border-gray-700">
            <div class="flex items-start gap-4">
                <div class="text-3xl">👨‍💼</div>
                <div>
                    <h3 class="font-bold text-gray-900 dark:text-white mb-2">Admin Toegang</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Welkom terug! Log in om toegang te krijgen tot document review, BTW rapporten, en klantenbeheer.
                    </p>
                </div>
            </div>
        </div>

        <!-- Login Form -->
        <x-filament-panels::form wire:submit="authenticate">
            {{ $this->form }}

            <x-filament-panels::form.actions
                :actions="$this->getCachedFormActions()"
                :full-width="$this->hasFullWidthFormActions()"
            />
        </x-filament-panels::form>

        <!-- Demo Credentials (Development Only) -->
        @if(app()->environment('local'))
        <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border-2 border-blue-200 dark:border-blue-800">
            <div class="text-xs font-semibold text-blue-800 dark:text-blue-300 mb-3 flex items-center gap-2">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
                ADMIN TEST ACCOUNTS
            </div>
            <div class="space-y-2 text-xs">
                <div class="grid grid-cols-2 gap-2">
                    <div class="font-mono text-gray-700 dark:text-gray-300">📧 boekhouder@nlaccounting.nl</div>
                    <div class="font-mono text-gray-700 dark:text-gray-300">🔑 boekhouder123</div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div class="font-mono text-gray-700 dark:text-gray-300">📧 admin@nlaccounting.nl</div>
                    <div class="font-mono text-gray-700 dark:text-gray-300">🔑 admin123</div>
                </div>
            </div>
        </div>
        @endif

        <!-- Features -->
        <div class="mt-8 grid grid-cols-3 gap-4">
            <div class="text-center p-4 bg-gray-50 dark:bg-gray-800 rounded-xl">
                <div class="text-2xl mb-2">📊</div>
                <div class="text-xs font-semibold text-gray-700 dark:text-gray-300">Dashboard</div>
            </div>
            <div class="text-center p-4 bg-gray-50 dark:bg-gray-800 rounded-xl">
                <div class="text-2xl mb-2">📄</div>
                <div class="text-xs font-semibold text-gray-700 dark:text-gray-300">Documenten</div>
            </div>
            <div class="text-center p-4 bg-gray-50 dark:bg-gray-800 rounded-xl">
                <div class="text-2xl mb-2">📈</div>
                <div class="text-xs font-semibold text-gray-700 dark:text-gray-300">Rapporten</div>
            </div>
        </div>

        <!-- Back to Home -->
        <div class="mt-6 text-center">
            <a href="/" class="text-sm text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Terug naar home
            </a>
        </div>
    </div>
</x-filament-panels::page.simple>

