<?php

namespace App\Filament\Widgets;

use App\Models\VatPeriod;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TaxTrendsWidget extends ChartWidget
{
    protected static ?string $heading = 'BTW Trends';
    
    protected static ?int $sort = 7;
    
    protected int | string | array $columnSpan = 'full';
    
    protected function getData(): array
    {
        // Get VAT trends for last 12 months
        $months = [];
        $vatAmounts = [];
        $documentCounts = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();
            
            $months[] = $date->format('M Y');
            
            // Get VAT amount for this month
            $vatAmount = VatPeriod::whereBetween('period_start', [$monthStart, $monthEnd])
                ->orWhereBetween('period_end', [$monthStart, $monthEnd])
                ->with('documents')
                ->get()
                ->sum(function ($period) {
                    return $period->documents->sum('amount_vat') ?? 0;
                });
            
            $vatAmounts[] = round($vatAmount, 2);
            
            // Get document count
            $docCount = VatPeriod::whereBetween('period_start', [$monthStart, $monthEnd])
                ->orWhereBetween('period_end', [$monthStart, $monthEnd])
                ->withCount('documents')
                ->get()
                ->sum('documents_count');
            
            $documentCounts[] = $docCount;
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'BTW Bedrag (€)',
                    'data' => $vatAmounts,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Aantal Documenten',
                    'data' => $documentCounts,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.5)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'yAxisID' => 'y1',
                    'type' => 'bar',
                ],
            ],
            'labels' => $months,
        ];
    }
    
    protected function getType(): string
    {
        return 'line';
    }
    
    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'title' => [
                        'display' => true,
                        'text' => 'BTW Bedrag (€)',
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => 'Aantal Documenten',
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
            ],
        ];
    }
}

