<?php

namespace App\Filament\Widgets;

use App\Models\Document;
use App\Models\Client;
use Filament\Widgets\ChartWidget;

class AutomationRateWidget extends ChartWidget
{
    protected static ?string $heading = 'Automatiseringsgraad';
    
    protected function getData(): array
    {
        $total = Document::where('status', '!=', 'archived')->count();
        $autoApproved = Document::where('status', 'approved')
            ->where('auto_approved', true)
            ->count();
        
        $manualApproved = Document::where('status', 'approved')
            ->where('auto_approved', false)
            ->count();
        
        $pending = Document::where('status', 'review_required')->count();
        
        $automationRate = $total > 0 ? round(($autoApproved / $total) * 100, 1) : 0;
        
        return [
            'datasets' => [
                [
                    'label' => 'Automatiseringsgraad',
                    'data' => [$autoApproved, $manualApproved, $pending],
                    'backgroundColor' => [
                        'rgb(34, 197, 94)', // green - auto approved
                        'rgb(59, 130, 246)', // blue - manual approved
                        'rgb(239, 68, 68)', // red - pending
                    ],
                ],
            ],
            'labels' => ['Automatisch goedgekeurd', 'Handmatig goedgekeurd', 'In behandeling'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
    
    public function getDescription(): ?string
    {
        $total = Document::where('status', '!=', 'archived')->count();
        $autoApproved = Document::where('status', 'approved')
            ->where('auto_approved', true)
            ->count();
        
        $rate = $total > 0 ? round(($autoApproved / $total) * 100, 1) : 0;
        
        return "Doel: 90-95% automatisering | Huidig: {$rate}%";
    }
    
    /**
     * Get per-client automation rates
     */
    public function getPerClientRates(): array
    {
        $clients = Client::withCount([
            'documents as total_documents' => function ($query) {
                $query->where('status', '!=', 'archived');
            },
            'documents as auto_approved_documents' => function ($query) {
                $query->where('status', 'approved')
                    ->where('auto_approved', true);
            },
        ])
        ->having('total_documents', '>', 0)
        ->orderByDesc('auto_approved_documents')
        ->limit(10)
        ->get();
        
        return $clients->map(function ($client) {
            $rate = $client->total_documents > 0 
                ? round(($client->auto_approved_documents / $client->total_documents) * 100, 1)
                : 0;
            
            return [
                'client' => $client->name,
                'rate' => $rate,
                'total' => $client->total_documents,
                'auto_approved' => $client->auto_approved_documents,
            ];
        })->toArray();
    }
}

