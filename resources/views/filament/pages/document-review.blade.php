<x-filament-panels::page>
    @if($document)
        {{-- Document Counter & Navigation --}}
        <div class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="text-lg font-bold text-blue-600 dark:text-blue-400">
                        📄 Document {{ $currentIndex }} van {{ $totalDocuments }}
                    </div>
                    
                    <div class="flex gap-2">
                        <x-filament::button
                            color="gray"
                            size="sm"
                            wire:click="previous"
                            icon="heroicon-o-arrow-left"
                            :disabled="$currentIndex <= 1"
                        >
                            Vorige (←)
                        </x-filament::button>
                        
                        <x-filament::button
                            color="gray"
                            size="sm"
                            wire:click="next"
                            icon="heroicon-o-arrow-right"
                            icon-position="after"
                            :disabled="$currentIndex >= $totalDocuments"
                        >
                            Volgende (→)
                        </x-filament::button>
                    </div>
                </div>
                
                <x-filament::button
                    color="warning"
                    size="sm"
                    wire:click="bulkApprove"
                    icon="heroicon-o-check-circle"
                    :disabled="$totalDocuments === 0"
                >
                    Bulk Goedkeuren (85%+)
                </x-filament::button>
            </div>
        </div>
        
        <form wire:submit="approve">
            {{ $this->form }}
            
            {{-- OCR Raw Text Expander --}}
            @if($this->getOcrRawText())
                <div class="mt-6">
                    <x-filament::section
                        collapsible
                        collapsed
                        icon="heroicon-o-document-text"
                    >
                        <x-slot name="heading">
                            OCR Ruwe Tekst
                        </x-slot>
                        
                        <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded font-mono text-sm whitespace-pre-wrap max-h-96 overflow-y-auto">
                            {{ $this->getOcrRawText() }}
                        </div>
                    </x-filament::section>
                </div>
            @endif
            
            {{-- Action Buttons --}}
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mt-6 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800">
                <div class="flex gap-3">
                    <x-filament::button
                        color="danger"
                        wire:click="reject"
                        type="button"
                        icon="heroicon-o-x-circle"
                    >
                        Afwijzen
                    </x-filament::button>
                </div>
                
                <div class="flex gap-3">
                    <x-filament::button
                        color="gray"
                        wire:click="next"
                        type="button"
                        icon="heroicon-o-arrow-right"
                        icon-position="after"
                    >
                        Overslaan (→)
                    </x-filament::button>
                    
                    <x-filament::button
                        type="submit"
                        color="success"
                        icon="heroicon-o-check-circle"
                        size="lg"
                    >
                        ✓ Goedkeuren (Enter)
                    </x-filament::button>
                </div>
            </div>
        </form>
        
        {{-- Keyboard Shortcuts Help --}}
        <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
            <div class="flex items-center gap-2 mb-2">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                <strong class="text-blue-900 dark:text-blue-100">Toetsenbord sneltoetsen:</strong>
            </div>
            <div class="flex flex-wrap gap-3 text-sm text-blue-800 dark:text-blue-200">
                <div class="flex items-center gap-2">
                    <kbd class="px-2 py-1 bg-white dark:bg-gray-800 border border-blue-300 dark:border-blue-700 rounded font-mono">A</kbd>
                    <span>= Goedkeuren</span>
                </div>
                <div class="flex items-center gap-2">
                    <kbd class="px-2 py-1 bg-white dark:bg-gray-800 border border-blue-300 dark:border-blue-700 rounded font-mono">R</kbd>
                    <span>= Afwijzen</span>
                </div>
                <div class="flex items-center gap-2">
                    <kbd class="px-2 py-1 bg-white dark:bg-gray-800 border border-blue-300 dark:border-blue-700 rounded font-mono">Enter</kbd>
                    <span>= Goedkeuren</span>
                </div>
                <div class="flex items-center gap-2">
                    <kbd class="px-2 py-1 bg-white dark:bg-gray-800 border border-blue-300 dark:border-blue-700 rounded font-mono">→</kbd>
                    <span>= Volgende</span>
                </div>
                <div class="flex items-center gap-2">
                    <kbd class="px-2 py-1 bg-white dark:bg-gray-800 border border-blue-300 dark:border-blue-700 rounded font-mono">←</kbd>
                    <span>= Vorige</span>
                </div>
                <div class="flex items-center gap-2">
                    <kbd class="px-2 py-1 bg-white dark:bg-gray-800 border border-blue-300 dark:border-blue-700 rounded font-mono">Esc</kbd>
                    <span>= Overslaan</span>
                </div>
            </div>
        </div>
        
        <script>
            // Enhanced keyboard shortcuts for Document Review
            document.addEventListener('keydown', function(e) {
                // Don't trigger shortcuts when typing in inputs
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT') {
                    // Allow Enter in textareas for new lines
                    if (e.target.tagName === 'TEXTAREA' && e.key === 'Enter' && !e.ctrlKey && !e.metaKey) {
                        return;
                    }
                    // Allow shortcuts with Ctrl/Cmd modifier
                    if (!e.ctrlKey && !e.metaKey) {
                        return;
                    }
                }
                
                // A = Approve
                if (e.key === 'a' || e.key === 'A') {
                    e.preventDefault();
                    @this.call('approve');
                    return;
                }
                
                // R = Reject
                if (e.key === 'r' || e.key === 'R') {
                    e.preventDefault();
                    @this.call('reject');
                    return;
                }
                
                // Enter = Approve (when not in textarea)
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    @this.call('approve');
                } else if (e.key === 'ArrowRight' && !e.ctrlKey && !e.metaKey) {
                    e.preventDefault();
                    @this.call('next');
                } else if (e.key === 'ArrowLeft' && !e.ctrlKey && !e.metaKey) {
                    e.preventDefault();
                    @this.call('previous');
                } else if (e.key === 'Escape') {
                    e.preventDefault();
                    @this.call('next');
                }
            });
        </script>
    @else
        <x-filament::card>
            <div class="text-center py-12">
                <div class="text-6xl mb-4">🎉</div>
                <h2 class="text-2xl font-bold mb-2 text-gray-900 dark:text-white">Geen documenten meer te beoordelen!</h2>
                <p class="text-gray-600 dark:text-gray-400 mb-6">Alle documenten zijn verwerkt.</p>
                
                <div class="flex gap-3 justify-center">
                    <x-filament::button
                        href="/admin"
                        icon="heroicon-o-home"
                    >
                        Terug naar Dashboard
                    </x-filament::button>
                    <x-filament::button
                        href="{{ \App\Filament\Resources\DocumentResource::getUrl('index') }}"
                        icon="heroicon-o-document-text"
                        color="gray"
                    >
                        Alle Documenten Bekijken
                    </x-filament::button>
                </div>
            </div>
        </x-filament::card>
    @endif
</x-filament-panels::page>
