<?php

namespace App\Filament\Widgets;

use App\Models\Document;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DocumentsAwaitingReviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $reviewRequired = Document::where('status', 'review_required')->count();
        $pending = Document::where('status', 'pending')->count();
        $ocrProcessing = Document::where('status', 'ocr_processing')->count();
        
        return [
            Stat::make('Documenten te beoordelen', $reviewRequired)
                ->description('Vereist handmatige review')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('warning'),
                
            Stat::make('In wachtrij', $pending)
                ->description('Wacht op OCR verwerking')
                ->descriptionIcon('heroicon-o-clock')
                ->color('info'),
                
            Stat::make('Wordt verwerkt', $ocrProcessing)
                ->description('OCR is bezig')
                ->descriptionIcon('heroicon-o-arrow-path')
                ->color('primary'),
        ];
    }
}

