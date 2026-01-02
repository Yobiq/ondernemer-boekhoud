@if($document)
    @if($document->auto_approved ?? false)
        <div class="p-2 bg-green-50 dark:bg-green-900/20 rounded border border-green-200 dark:border-green-800">
            <div class="font-semibold text-green-800 dark:text-green-200">✅ Automatisch Goedgekeurd</div>
            <div class="text-sm text-green-700 dark:text-green-300 mt-1">
                {{ $document->auto_approval_reason ?? 'Geen reden opgegeven' }}
            </div>
        </div>
    @else
        @php
            $autoApprovalService = app(\App\Services\AutoApprovalService::class);
            $canAutoApprove = $autoApprovalService->shouldAutoApprove($document);
        @endphp
        
        @if($canAutoApprove)
            <div class="p-2 bg-blue-50 dark:bg-blue-900/20 rounded border border-blue-200 dark:border-blue-800">
                <div class="font-semibold text-blue-800 dark:text-blue-200">💡 Kan Automatisch Worden Goedgekeurd</div>
            </div>
        @else
            <div class="p-2 bg-yellow-50 dark:bg-yellow-900/20 rounded border border-yellow-200 dark:border-yellow-800">
                <div class="font-semibold text-yellow-800 dark:text-yellow-200">⚠️ Handmatige Controle Vereist</div>
                <div class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">
                    {{ $document->review_required_reason ?? 'Handmatige controle vereist' }}
                </div>
            </div>
        @endif
    @endif
@else
    <span class="text-gray-500">-</span>
@endif


