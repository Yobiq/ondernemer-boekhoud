@if(!$hasIssues)
    <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
        <div class="flex items-center gap-3">
            <div class="flex-shrink-0">
                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div>
                <div class="font-semibold text-green-800 dark:text-green-200">Geen problemen gedetecteerd</div>
                <div class="text-sm text-green-700 dark:text-green-300 mt-1">Alle controles zijn geslaagd</div>
            </div>
        </div>
    </div>
@else
    <div class="space-y-3">
        @foreach($insights as $key => $insight)
            @php
                $icon = match($insight['type'] ?? 'info') {
                    'warning' => '⚠️',
                    'error' => '❌',
                    'info' => 'ℹ️',
                    'success' => '✅',
                    default => 'ℹ️',
                };
                
                $bgColor = match($insight['type'] ?? 'info') {
                    'warning' => 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800',
                    'error' => 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800',
                    'info' => 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800',
                    'success' => 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800',
                    default => 'bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700',
                };
                
                $textColor = match($insight['type'] ?? 'info') {
                    'warning' => 'text-yellow-800 dark:text-yellow-200',
                    'error' => 'text-red-800 dark:text-red-200',
                    'info' => 'text-blue-800 dark:text-blue-200',
                    'success' => 'text-green-800 dark:text-green-200',
                    default => 'text-gray-800 dark:text-gray-200',
                };
            @endphp
            
            <div class="p-4 rounded-lg border {{ $bgColor }}">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 text-xl">{{ $icon }}</div>
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold {{ $textColor }}">{{ $insight['title'] ?? 'Inzicht' }}</div>
                        <div class="text-sm mt-1.5 {{ $textColor }} opacity-90 leading-relaxed">
                            {{ $insight['message'] ?? '' }}
                        </div>
                        
                        @if(isset($insight['documents']) && !empty($insight['documents']))
                            <div class="mt-3 space-y-2">
                                <div class="text-xs font-medium {{ $textColor }} opacity-75 uppercase tracking-wide">Gerelateerde documenten:</div>
                                @foreach($insight['documents'] as $doc)
                                    <div class="text-xs {{ $textColor }} opacity-75 pl-4 border-l-2 border-current opacity-30">
                                        <div class="font-medium">{{ $doc['filename'] ?? 'Onbekend' }}</div>
                                        <div class="text-xs opacity-75 mt-0.5">
                                            {{ $doc['date'] ?? '' }} • {{ $doc['amount'] ?? '' }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif



