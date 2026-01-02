<x-filament-panels::page>
    <div class="global-search-container">
        {{-- Search Form --}}
        <div class="search-form-section mb-6">
            {{ $this->form }}
        </div>

        {{-- Results --}}
        @php
            $searchQuery = $this->data['searchQuery'] ?? '';
            $totalResults = $this->getTotalResultsProperty();
            $results = $this->getResultsProperty();
        @endphp

        @if(!empty($searchQuery))
            @if($totalResults > 0)
                <div class="results-header mb-4">
                    <h2 class="text-lg font-semibold">
                        {{ $totalResults }} resultaat(en) gevonden voor "{{ $searchQuery }}"
                    </h2>
                </div>

                <div class="results-grid">
                    @foreach($results as $result)
                    <a href="{{ $result['url'] }}" class="result-item result-item-{{ $result['color'] }}">
                        <div class="result-icon">
                            @svg($result['icon'], 'w-6 h-6')
                        </div>
                        <div class="result-content">
                            <div class="result-title">{{ $result['title'] }}</div>
                            <div class="result-subtitle">{{ $result['subtitle'] }}</div>
                            <div class="result-type">{{ ucfirst(str_replace('_', ' ', $result['type'])) }}</div>
                        </div>
                        <div class="result-arrow">
                            →
                        </div>
                    </a>
                    @endforeach
                </div>
            @else
                <div class="empty-results">
                    <div class="empty-icon">🔍</div>
                    <div class="empty-title">Geen resultaten gevonden</div>
                    <div class="empty-description">
                        Probeer een andere zoekterm of controleer de spelling.
                    </div>
                </div>
            @endif
        @else
            <div class="empty-state">
                <div class="empty-icon">⌨️</div>
                <div class="empty-title">Start met zoeken</div>
                <div class="empty-description">
                    Typ een zoekterm in het veld hierboven om te zoeken naar:
                </div>
                <div class="search-tips">
                    <div class="tip-item">
                        <span class="tip-icon">👤</span>
                        <span>Klanten (naam, bedrijf, email)</span>
                    </div>
                    <div class="tip-item">
                        <span class="tip-icon">📄</span>
                        <span>Documenten (bestandsnaam, leverancier)</span>
                    </div>
                    <div class="tip-item">
                        <span class="tip-icon">📅</span>
                        <span>BTW Periodes</span>
                    </div>
                    <div class="tip-item">
                        <span class="tip-icon">✅</span>
                        <span>Taken</span>
                    </div>
                </div>
                <div class="keyboard-hint">
                    <kbd class="keyboard-key">⌘</kbd> + <kbd class="keyboard-key">K</kbd> voor snel zoeken
                </div>
            </div>
        @endif
    </div>

    <style>
        .global-search-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .search-form-section {
            position: sticky;
            top: 1rem;
            z-index: 10;
            background: white;
            padding: 1rem;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .dark .search-form-section {
            background: #1f2937;
        }

        .results-header {
            padding: 0.5rem 0;
        }

        .results-grid {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .result-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: white;
            border: 1px solid #e5e7eb;
            border-left: 4px solid;
            border-radius: 0.5rem;
            transition: all 0.2s;
            text-decoration: none;
            color: inherit;
        }

        .dark .result-item {
            background: #1f2937;
            border-color: #374151;
        }

        .result-item:hover {
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .result-item-primary {
            border-left-color: #3b82f6;
        }

        .result-item-success {
            border-left-color: #10b981;
        }

        .result-item-warning {
            border-left-color: #f59e0b;
        }

        .result-item-info {
            border-left-color: #06b6d4;
        }

        .result-icon {
            flex-shrink: 0;
            width: 3rem;
            height: 3rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f3f4f6;
            border-radius: 0.5rem;
        }

        .dark .result-icon {
            background: #374151;
        }

        .result-content {
            flex: 1;
            min-width: 0;
        }

        .result-title {
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 0.25rem;
            color: #111827;
        }

        .dark .result-title {
            color: #f9fafb;
        }

        .result-subtitle {
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 0.25rem;
        }

        .result-type {
            font-size: 0.75rem;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .result-arrow {
            flex-shrink: 0;
            color: #9ca3af;
            font-size: 1.5rem;
        }

        .empty-state, .empty-results {
            text-align: center;
            padding: 4rem 2rem;
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .empty-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #111827;
        }

        .dark .empty-title {
            color: #f9fafb;
        }

        .empty-description {
            color: #6b7280;
            margin-bottom: 2rem;
        }

        .search-tips {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            max-width: 600px;
            margin: 0 auto 2rem;
        }

        .tip-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem;
            background: #f9fafb;
            border-radius: 0.5rem;
        }

        .dark .tip-item {
            background: #374151;
        }

        .tip-icon {
            font-size: 1.5rem;
        }

        .keyboard-hint {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            color: #6b7280;
            font-size: 0.875rem;
        }

        .keyboard-key {
            padding: 0.25rem 0.5rem;
            background: #e5e7eb;
            border: 1px solid #d1d5db;
            border-radius: 0.25rem;
            font-family: monospace;
            font-size: 0.875rem;
        }

        .dark .keyboard-key {
            background: #374151;
            border-color: #4b5563;
        }
    </style>

    @push('scripts')
    <script>
        // Global keyboard shortcut: Cmd+K or Ctrl+K
        document.addEventListener('keydown', function(e) {
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                e.preventDefault();
                // Focus search input
                const searchInput = document.querySelector('input[type="text"]');
                if (searchInput) {
                    searchInput.focus();
                    searchInput.select();
                }
            }
        });
    </script>
    @endpush
</x-filament-panels::page>

