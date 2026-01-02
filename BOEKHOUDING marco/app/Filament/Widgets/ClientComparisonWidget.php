<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\VatPeriod;
use Filament\Widgets\ChartWidget;

class ClientComparisonWidget extends ChartWidget
{
    protected static ?string $heading = 'Klanten Vergelijking';
    
    protected static ?int $sort = 9;
    
    protected int | string | array $columnSpan = 'full';
    
    protected function getData(): array
    {
        // Get top 10 clients by VAT amount this year
        $currentYear = now()->year;
        
        $clients = Client::with(['vatPeriods' => function ($query) use ($currentYear) {
            $query->where('year', $currentYear)
                ->with('documents');
        }])->get();
        
        $clientData = $clients->map(function ($client) {
            $totalVat = $client->vatPeriods->sum(function ($period) {
                return $period->documents->sum('amount_vat') ?? 0;
            });
            
            return [
                'name' => $client->name,
                'vat' => $totalVat,
            ];
        })->sortByDesc('vat')->take(10);
        
        $labels = $clientData->pluck('name')->toArray();
        $vatAmounts = $clientData->pluck('vat')->map(fn($v) => round($v, 2))->toArray();
        
        return [
            'datasets' => [
                [
                    'label' => 'BTW Bedrag (€)',
                    'data' => $vatAmounts,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                    'borderColor' => 'rgb(59, 130, 246)',
                ],
            ],
            'labels' => $labels,
        ];
    }
    
    protected function getType(): string
    {
        return 'bar';
    }
}

