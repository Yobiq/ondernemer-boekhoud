<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TransactionsStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $unmatched = Transaction::whereNull('matched_document_id')->count();
        $matched = Transaction::whereNotNull('matched_document_id')->count();
        $total = $matched + $unmatched;
        
        $matchRate = $total > 0 ? round(($matched / $total) * 100, 1) : 0;
        
        return [
            Stat::make('Niet-gekoppelde transacties', $unmatched)
                ->description('Vereist koppeling aan document')
                ->descriptionIcon('heroicon-o-link-slash')
                ->color('danger'),
                
            Stat::make('Gekoppelde transacties', $matched)
                ->description("{$matchRate}% gekoppeld")
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),
        ];
    }
}

