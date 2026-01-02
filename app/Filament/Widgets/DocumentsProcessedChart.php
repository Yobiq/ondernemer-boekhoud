<?php

namespace App\Filament\Widgets;

use App\Models\Document;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class DocumentsProcessedChart extends ChartWidget
{
    protected static ?string $heading = 'Documenten verwerkt (laatste 30 dagen)';
    protected static ?int $sort = 2;
    
    protected function getData(): array
    {
        // Get last 30 days
        $data = Document::where('created_at', '>=', now()->subDays(30))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        $labels = [];
        $values = [];
        
        foreach ($data as $item) {
            $labels[] = \Carbon\Carbon::parse($item->date)->format('d M');
            $values[] = $item->count;
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Documenten',
                    'data' => $values,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
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
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}

