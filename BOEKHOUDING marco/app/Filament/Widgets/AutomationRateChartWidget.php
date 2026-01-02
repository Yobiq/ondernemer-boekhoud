<?php

namespace App\Filament\Widgets;

use App\Models\Document;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class AutomationRateChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Automatiseringsgraad';
    
    protected static ?int $sort = 5;
    
    protected function getData(): array
    {
        // Get automation rate for last 6 months
        $months = [];
        $autoApproved = [];
        $manualApproved = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();
            
            $months[] = $date->format('M Y');
            
            $autoCount = Document::whereBetween('created_at', [$monthStart, $monthEnd])
                ->where('auto_approved', true)
                ->where('status', 'approved')
                ->count();
            
            $manualCount = Document::whereBetween('created_at', [$monthStart, $monthEnd])
                ->where('auto_approved', false)
                ->where('status', 'approved')
                ->count();
            
            $autoApproved[] = $autoCount;
            $manualApproved[] = $manualCount;
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Auto-goedgekeurd',
                    'data' => $autoApproved,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.5)',
                    'borderColor' => 'rgb(34, 197, 94)',
                ],
                [
                    'label' => 'Handmatig goedgekeurd',
                    'data' => $manualApproved,
                    'backgroundColor' => 'rgba(251, 191, 36, 0.5)',
                    'borderColor' => 'rgb(251, 191, 36)',
                ],
            ],
            'labels' => $months,
        ];
    }
    
    protected function getType(): string
    {
        return 'line';
    }
}

