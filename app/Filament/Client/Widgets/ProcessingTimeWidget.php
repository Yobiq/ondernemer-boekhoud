<?php

namespace App\Filament\Client\Widgets;

use App\Services\PerformanceMetricsService;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class ProcessingTimeWidget extends Widget
{
    protected static string $view = 'filament.client.widgets.processing-time-widget';
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 3;
    
    // Poll every 60 seconds
    protected $poll = '60s';
    
    public static function canView(): bool
    {
        if (!Auth::check()) {
            return false;
        }
        
        $user = Auth::user();
        $clientId = $user->client_id ?? null;
        
        if (!$clientId) {
            return false;
        }
        
        // Only show if there are processed documents
        return \App\Models\Document::where('client_id', $clientId)
            ->where('status', '!=', 'pending')
            ->exists();
    }
    
    protected function getViewData(): array
    {
        if (!Auth::check()) {
            return $this->getEmptyData();
        }
        
        $clientId = Auth::user()->client_id ?? null;
        
        if (!$clientId) {
            return $this->getEmptyData();
        }
        
        $metricsService = app(PerformanceMetricsService::class);
        
        // Get metrics for last 30 days
        $dateFrom = now()->subDays(30)->toDateString();
        
        $ocrTime = $metricsService->getAverageOcrTime($clientId, $dateFrom);
        $approvalTime = $metricsService->getAverageApprovalTime($clientId, $dateFrom);
        
        // Calculate total processing time
        $totalMinutes = $ocrTime['average_minutes'] + $approvalTime['average_minutes'];
        $totalHours = $totalMinutes / 60;
        
        // Get fastest time
        $fastestMinutes = $ocrTime['fastest_seconds'] / 60;
        
        // Determine performance status
        $performanceStatus = $this->getPerformanceStatus($totalHours);
        
        return [
            'ocr_time' => $ocrTime,
            'approval_time' => $approvalTime,
            'total_minutes' => round($totalMinutes, 1),
            'total_hours' => round($totalHours, 2),
            'fastest_minutes' => round($fastestMinutes, 1),
            'performance_status' => $performanceStatus,
            'count' => $ocrTime['count'],
        ];
    }
    
    protected function getEmptyData(): array
    {
        return [
            'ocr_time' => ['average_minutes' => 0, 'count' => 0],
            'approval_time' => ['average_minutes' => 0, 'count' => 0],
            'total_minutes' => 0,
            'total_hours' => 0,
            'fastest_minutes' => 0,
            'performance_status' => ['label' => 'Geen data', 'color' => 'gray', 'icon' => '📊'],
            'count' => 0,
        ];
    }
    
    protected function getPerformanceStatus(float $hours): array
    {
        if ($hours === 0) {
            return [
                'label' => 'Geen data',
                'color' => 'gray',
                'icon' => '📊',
            ];
        } elseif ($hours < 1) {
            return [
                'label' => 'Uitstekend',
                'color' => 'success',
                'icon' => '🚀',
            ];
        } elseif ($hours < 2) {
            return [
                'label' => 'Snel',
                'color' => 'info',
                'icon' => '⚡',
            ];
        } elseif ($hours < 6) {
            return [
                'label' => 'Goed',
                'color' => 'warning',
                'icon' => '✅',
            ];
        } else {
            return [
                'label' => 'Langzaam',
                'color' => 'danger',
                'icon' => '⏱️',
            ];
        }
    }
}

