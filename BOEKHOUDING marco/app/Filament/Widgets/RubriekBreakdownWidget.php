<?php

namespace App\Filament\Widgets;

use App\Models\Document;
use App\Models\VatPeriod;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class RubriekBreakdownWidget extends ChartWidget
{
    protected static ?string $heading = 'BTW Rubrieken Verdeling';
    
    protected static ?int $sort = 8;
    
    protected function getData(): array
    {
        // Get current year data
        $currentYear = now()->year;
        
        // Get all documents from approved periods this year
        $documents = Document::whereHas('vatPeriods', function ($query) use ($currentYear) {
            $query->where('year', $currentYear)
                ->where('status', '!=', 'open');
        })
        ->whereNotNull('vat_rubriek')
        ->where('status', 'approved')
        ->get();
        
        // Group by rubriek
        $rubriekData = $documents->groupBy('vat_rubriek')->map(function ($docs, $rubriek) {
            return [
                'amount' => $docs->sum('amount_excl'),
                'vat' => $docs->sum('amount_vat'),
                'count' => $docs->count(),
            ];
        });
        
        $labels = [];
        $amounts = [];
        $colors = [
            '1a' => 'rgb(59, 130, 246)',
            '1b' => 'rgb(34, 197, 94)',
            '1c' => 'rgb(251, 191, 36)',
            '2a' => 'rgb(168, 85, 247)',
            '3a' => 'rgb(236, 72, 153)',
            '3b' => 'rgb(249, 115, 22)',
            '4a' => 'rgb(14, 165, 233)',
            '5b' => 'rgb(239, 68, 68)',
        ];
        
        $backgroundColors = [];
        
        foreach ($rubriekData as $rubriek => $data) {
            $labels[] = "Rubriek {$rubriek}";
            $amounts[] = round($data['amount'], 2);
            $backgroundColors[] = $colors[$rubriek] ?? 'rgb(156, 163, 175)';
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Grondslag (€)',
                    'data' => $amounts,
                    'backgroundColor' => $backgroundColors,
                ],
            ],
            'labels' => $labels,
        ];
    }
    
    protected function getType(): string
    {
        return 'doughnut';
    }
}

