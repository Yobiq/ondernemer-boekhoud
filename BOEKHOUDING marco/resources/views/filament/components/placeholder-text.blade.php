@php
    $type = $type ?? 'info';
    $bgColor = match($type) {
        'info' => 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800',
        'warning' => 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800',
        'error' => 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800',
        default => 'bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700',
    };
    $textColor = match($type) {
        'info' => 'text-blue-800 dark:text-blue-200',
        'warning' => 'text-yellow-800 dark:text-yellow-200',
        'error' => 'text-red-800 dark:text-red-200',
        default => 'text-gray-800 dark:text-gray-200',
    };
@endphp

<div class="p-3 rounded-lg border {{ $bgColor }}">
    <div class="flex items-center gap-2">
        <span class="text-sm {{ $textColor }}">{{ $text }}</span>
    </div>
</div>
