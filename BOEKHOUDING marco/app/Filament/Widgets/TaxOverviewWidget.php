<?php

namespace App\Filament\Widgets;

use App\Models\VatPeriod;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class TaxOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $currentYear = now()->year;
        $currentQuarter = ceil(now()->month / 3);
        
        // Total VAT this quarter
        $quarterVat = VatPeriod::where('year', $currentYear)
            ->where('quarter', $currentQuarter)
            ->with('documents')
            ->get()
            ->sum(function ($period) {
                return $period->documents->sum('amount_vat') ?? 0;
            });
        
        // Total VAT this year
        $yearVat = VatPeriod::where('year', $currentYear)
            ->with('documents')
            ->get()
            ->sum(function ($period) {
                return $period->documents->sum('amount_vat') ?? 0;
            });
        
        // Number of active periods
        $activePeriods = VatPeriod::where('status', 'open')
            ->orWhere('status', 'voorbereid')
            ->count();
        
        return [
            Stat::make('BTW Dit Kwartaal', '€' . number_format($quarterVat, 2, ',', '.'))
                ->description('Totaal BTW bedrag dit kwartaal')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            
            Stat::make('BTW Dit Jaar', '€' . number_format($yearVat, 2, ',', '.'))
                ->description('Totaal BTW bedrag dit jaar')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),
            
            Stat::make('Actieve Periodes', $activePeriods)
                ->description('Open of voorbereide periodes')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}

