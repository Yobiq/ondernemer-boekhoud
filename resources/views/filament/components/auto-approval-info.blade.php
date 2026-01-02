<div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
    <div class="flex items-start gap-3">
        <span class="text-2xl">{{ $icon ?? '📊' }}</span>
        <div class="flex-1">
            <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Auto-Approval Status</h4>
            @if($canAutoApprove ?? false)
                <div class="p-2 bg-green-50 dark:bg-green-900/20 rounded border border-green-200 dark:border-green-800">
                    <div class="font-semibold text-green-800 dark:text-green-200">✅ Kan Automatisch Worden Goedgekeurd</div>
                    @if(!empty($autoApprovalReasons))
                        <ul class="text-sm text-green-700 dark:text-green-300 mt-1 list-disc list-inside">
                            @foreach($autoApprovalReasons as $reason)
                                <li>{{ $reason }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @else
                <div class="p-2 bg-yellow-50 dark:bg-yellow-900/20 rounded border border-yellow-200 dark:border-yellow-800">
                    <div class="font-semibold text-yellow-800 dark:text-yellow-200">⚠️ Handmatige Controle Vereist</div>
                </div>
            @endif
        </div>
    </div>
</div>



