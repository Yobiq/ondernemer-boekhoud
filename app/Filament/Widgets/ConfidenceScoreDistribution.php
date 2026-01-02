<?php

namespace App\Filament\Widgets;

use App\Models\Document;
use Filament\Widgets\ChartWidget;

class ConfidenceScoreDistribution extends ChartWidget
{
    protected static ?string $heading = 'Confidence Score Verdeling';
    protected static ?int $sort = 3;
    
    protected function getData(): array
    {
        // Buckets: 0-50, 50-70, 70-85, 85-95, 95-100
        $veryLow = Document::whereNotNull('confidence_score')
            ->where('confidence_score', '<', 50)
            ->count();
        
        $low = Document::whereNotNull('confidence_score')
            ->whereBetween('confidence_score', [50, 70])
            ->count();
        
        $medium = Document::whereNotNull('confidence_score')
            ->whereBetween('confidence_score', [70, 85])
            ->count();
        
        $high = Document::whereNotNull('confidence_score')
            ->whereBetween('confidence_score', [85, 95])
            ->count();
        
        $veryHigh = Document::whereNotNull('confidence_score')
            ->where('confidence_score', '>=', 95)
            ->count();
        
        return [
            'datasets' => [
                [
                    'label' => 'Documenten',
                    'data' => [$veryLow, $low, $medium, $high, $veryHigh],
                    'backgroundColor' => [
                        'rgb(239, 68, 68)',   // red
                        'rgb(251, 146, 60)',  // orange
                        'rgb(251, 191, 36)',  // yellow
                        'rgb(132, 204, 22)',  // lime
                        'rgb(34, 197, 94)',   // green
                    ],
                ],
            ],
            'labels' => ['0-50%', '50-70%', '70-85%', '85-95%', '95-100%'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
    
    public function getDescription(): ?string
    {
        $total = Document::whereNotNull('confidence_score')->count();
        $highConfidence = Document::where('confidence_score', '>=', 85)->count();
        
        $percentage = $total > 0 ? round(($highConfidence / $total) * 100) : 0;
        
        return "{$percentage}% van documenten heeft hoge confidence (≥85%)";
    }
}

