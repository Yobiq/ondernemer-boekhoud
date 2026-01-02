<?php

namespace App\Filament\Client\Widgets;

use App\Services\PerformanceMetricsService;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class ProcessingTimelineChartWidget extends Widget
{
    protected static string $view = 'filament.client.widgets.processing-timeline-chart-widget';
    protected int | string | array $columnSpan = ['default' => 12, 'md' => 6, 'lg' => 6];
    protected static ?int $sort = 5;
    
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
        
        // Get processing time trend
        $trend = $metricsService->getProcessingTimeTrend($clientId);
        
        if (empty($trend)) {
            return $this->getEmptyData();
        }
        
        // Calculate average and trend
        $avgMinutes = collect($trend)->avg('avg_minutes');
        $totalDocs = collect($trend)->sum('count');
        
        // Calculate trend (improving or declining)
        $firstHalf = collect($trend)->take(15)->avg('avg_minutes');
        $secondHalf = collect($trend)->skip(15)->avg('avg_minutes');
        
        $trendDirection = 'stable';
        $trendPercentage = 0;
        
        if ($firstHalf > 0) {
            $trendPercentage = (($secondHalf - $firstHalf) / $firstHalf) * 100;
            if ($trendPercentage < -5) {
                $trendDirection = 'improving'; // Getting faster
            } elseif ($trendPercentage > 5) {
                $trendDirection = 'declining'; // Getting slower
            }
        }
        
        return [
            'trend' => $trend,
            'avg_minutes' => round($avgMinutes, 1),
            'total_docs' => $totalDocs,
            'trend_direction' => $trendDirection,
            'trend_percentage' => round(abs($trendPercentage), 1),
            'has_data' => true,
        ];
    }
    
    protected function getEmptyData(): array
    {
        return [
            'trend' => [],
            'avg_minutes' => 0,
            'total_docs' => 0,
            'trend_direction' => 'stable',
            'trend_percentage' => 0,
            'has_data' => false,
        ];
    }
}

