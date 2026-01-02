{{-- Global Keyboard Shortcuts for Admin Panel --}}
<script>
    (function() {
        'use strict';

        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initShortcuts);
        } else {
            initShortcuts();
        }

        function initShortcuts() {
            // Global keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // Don't trigger if typing in inputs (unless Cmd/Ctrl modifier)
                const isInput = ['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName);
                const isContentEditable = e.target.contentEditable === 'true';
                
                if ((isInput || isContentEditable) && !e.ctrlKey && !e.metaKey) {
                    return;
                }

                // Cmd+K or Ctrl+K: Global Search
                if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                    e.preventDefault();
                    const searchUrl = '{{ route("filament.admin.pages.global-search") }}';
                    window.location.href = searchUrl;
                    return;
                }

                // Cmd+/ or Ctrl+/: Show shortcuts help
                if ((e.metaKey || e.ctrlKey) && e.key === '/') {
                    e.preventDefault();
                    showShortcutsHelp();
                    return;
                }
            });

            // Show shortcuts help modal
            function showShortcutsHelp() {
                const shortcuts = [
                    { key: '⌘K', description: 'Global Search' },
                    { key: '⌘/', description: 'Toon deze help' },
                    { key: 'A', description: 'Goedkeuren (Document Review)' },
                    { key: 'R', description: 'Afwijzen (Document Review)' },
                    { key: 'Enter', description: 'Goedkeuren (Document Review)' },
                    { key: '→', description: 'Volgende document' },
                    { key: '←', description: 'Vorige document' },
                    { key: 'Esc', description: 'Overslaan/Sluiten' },
                ];

                const modal = document.createElement('div');
                modal.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50';
                modal.innerHTML = `
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full mx-4 p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-2xl font-bold">⌨️ Keyboard Shortcuts</h2>
                            <button onclick="this.closest('.fixed').remove()" class="text-gray-500 hover:text-gray-700">
                                ✕
                            </button>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            ${shortcuts.map(s => `
                                <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded">
                                    <kbd class="px-2 py-1 bg-white dark:bg-gray-800 border rounded font-mono text-sm">${s.key}</kbd>
                                    <span class="text-sm">${s.description}</span>
                                </div>
                            `).join('')}
                        </div>
                        <div class="mt-4 text-sm text-gray-500">
                            Tip: Druk op <kbd class="px-1 py-0.5 bg-gray-200 dark:bg-gray-700 rounded text-xs">Esc</kbd> om te sluiten
                        </div>
                    </div>
                `;

                document.body.appendChild(modal);

                const closeHandler = (e) => {
                    if (e.key === 'Escape') {
                        modal.remove();
                        document.removeEventListener('keydown', closeHandler);
                    }
                };
                document.addEventListener('keydown', closeHandler);

                modal.addEventListener('click', (e) => {
                    if (e.target === modal) {
                        modal.remove();
                    }
                });
            }
        }
    })();
</script>

