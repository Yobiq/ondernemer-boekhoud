<?php

namespace App\Filament\Widgets;

use App\Models\Document;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class ProcessingTimeMetricsWidget extends BaseWidget
{
    protected static ?int $sort = 6;
    
    protected function getStats(): array
    {
        // Calculate average processing time (from upload to approval)
        // Use database-agnostic approach
        $documents = Document::where('status', 'approved')
            ->whereNotNull('created_at')
            ->whereNotNull('updated_at')
            ->get();
        
        $totalHours = 0;
        $count = 0;
        
        foreach ($documents as $document) {
            $hours = $document->created_at->diffInHours($document->updated_at);
            $totalHours += $hours;
            $count++;
        }
        
        $avgProcessingTime = $count > 0 ? ($totalHours / $count) : 0;
        
        // Documents processed today
        $processedToday = Document::whereDate('updated_at', today())
            ->where('status', 'approved')
            ->count();
        
        // Pending documents
        $pending = Document::whereIn('status', ['pending', 'ocr_processing', 'review_required'])
            ->count();
        
        return [
            Stat::make('Gem. Verwerkingstijd', round($avgProcessingTime, 1) . ' uur')
                ->description('Van upload tot goedkeuring')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),
            
            Stat::make('Vandaag Verwerkt', $processedToday)
                ->description('Documenten goedgekeurd vandaag')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            
            Stat::make('In Wachtrij', $pending)
                ->description('Documenten wachtend op verwerking')
                ->descriptionIcon('heroicon-m-queue-list')
                ->color('warning'),
        ];
    }
}

