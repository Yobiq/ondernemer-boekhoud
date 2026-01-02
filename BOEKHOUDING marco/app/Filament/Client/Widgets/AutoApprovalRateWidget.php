<?php

namespace App\Filament\Client\Widgets;

use App\Services\PerformanceMetricsService;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class AutoApprovalRateWidget extends Widget
{
    protected static string $view = 'filament.client.widgets.auto-approval-rate-widget';
    protected int | string | array $columnSpan = ['default' => 12, 'md' => 6, 'lg' => 6];
    protected static ?int $sort = 4;
    
    // Poll every 60 seconds
    protected $poll = '60s';
    
    public static function canView(): bool
    {
        // Disabled - causes large circular graphics on dashboard
            return false;
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
        
        $approvalData = $metricsService->getAutoApprovalRate($clientId, $dateFrom);
        $byType = $metricsService->getApprovalRateByType($clientId);
        
        // Calculate goal progress
        $goalRate = 90; // Target: 90% auto-approval
        $progress = min(100, ($approvalData['rate'] / $goalRate) * 100);
        
        // Determine status
        $status = $this->getStatus($approvalData['rate']);
        
        return [
            'total' => $approvalData['total'],
            'auto_approved' => $approvalData['auto_approved'],
            'manual_review' => $approvalData['manual_review'],
            'rate' => $approvalData['rate'],
            'goal_rate' => $goalRate,
            'progress' => round($progress, 1),
            'status' => $status,
            'by_type' => $byType,
        ];
    }
    
    protected function getEmptyData(): array
    {
        return [
            'total' => 0,
            'auto_approved' => 0,
            'manual_review' => 0,
            'rate' => 0,
            'goal_rate' => 90,
            'progress' => 0,
            'status' => ['label' => 'Geen data', 'color' => 'gray', 'icon' => '📊'],
            'by_type' => [],
        ];
    }
    
    protected function getStatus(float $rate): array
    {
        if ($rate === 0) {
            return [
                'label' => 'Geen data',
                'color' => 'gray',
                'icon' => '📊',
            ];
        } elseif ($rate >= 90) {
            return [
                'label' => 'Uitstekend',
                'color' => 'success',
                'icon' => '🌟',
            ];
        } elseif ($rate >= 70) {
            return [
                'label' => 'Goed',
                'color' => 'info',
                'icon' => '✅',
            ];
        } elseif ($rate >= 50) {
            return [
                'label' => 'Gemiddeld',
                'color' => 'warning',
                'icon' => '⚠️',
            ];
        } else {
            return [
                'label' => 'Verbetering nodig',
                'color' => 'danger',
                'icon' => '🔴',
            ];
        }
    }
}

