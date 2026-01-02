<?php

namespace App\Filament\Widgets;

use App\Models\VatPeriod;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PeriodStatusWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $open = VatPeriod::where('status', 'open')->count();
        $voorbereid = VatPeriod::where('status', 'voorbereid')->count();
        $ingediend = VatPeriod::where('status', 'ingediend')->count();
        $afgesloten = VatPeriod::where('status', 'afgesloten')->count();
        
        return [
            Stat::make('Open', $open)
                ->description('Periodes in behandeling')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            
            Stat::make('Voorbereid', $voorbereid)
                ->description('Klaar voor indiening')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info'),
            
            Stat::make('Ingediend', $ingediend)
                ->description('Bij Belastingdienst')
                ->descriptionIcon('heroicon-m-paper-airplane')
                ->color('success'),
            
            Stat::make('Afgesloten', $afgesloten)
                ->description('Definitief afgerond')
                ->descriptionIcon('heroicon-m-lock-closed')
                ->color('gray'),
        ];
    }
}

