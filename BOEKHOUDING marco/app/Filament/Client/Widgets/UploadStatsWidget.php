<?php

namespace App\Filament\Client\Widgets;

use App\Models\Document;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class UploadStatsWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    
    protected function getStats(): array
    {
        $clientId = Auth::user()->client_id ?? null;
        
        // Current stats
        $total = Document::where('client_id', $clientId)->count();
        $approved = Document::where('client_id', $clientId)
            ->where('status', 'approved')
            ->count();
        $pending = Document::where('client_id', $clientId)
            ->whereIn('status', ['pending', 'ocr_processing', 'review_required'])
            ->count();
        
        $thisMonth = Document::where('client_id', $clientId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        
        // Last month for comparison
        $lastMonth = Document::where('client_id', $clientId)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
        
        // Calculate trend
        $monthTrend = 0;
        if ($lastMonth > 0) {
            $monthTrend = round((($thisMonth - $lastMonth) / $lastMonth) * 100);
        } elseif ($thisMonth > 0) {
            $monthTrend = 100;
        }
        
        // Approval rate
        $approvalRate = $total > 0 ? round(($approved / $total) * 100) : 0;
        
        // Last 7 days for chart
        $last7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $last7Days[] = Document::where('client_id', $clientId)
                ->whereDate('created_at', now()->subDays($i))
                ->count();
        }
        
        return [
            Stat::make('📄 Totaal Documenten', $total)
                ->description($total === 1 ? '1 document geüpload' : ($total . ' documenten geüpload'))
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->color('primary'),
            
            Stat::make('✅ Goedgekeurd', $approved)
                ->description($approvalRate . '% goedkeuringspercentage')
                ->descriptionIcon($approvalRate >= 80 ? 'heroicon-o-check-badge' : 'heroicon-o-check-circle')
                ->color($approvalRate >= 80 ? 'success' : 'info'),
            
            Stat::make('⏳ In Behandeling', $pending)
                ->description($pending === 0 ? 'Alles verwerkt!' : ($pending === 1 ? '1 wacht op verwerking' : $pending . ' wachten op verwerking'))
                ->descriptionIcon($pending === 0 ? 'heroicon-o-check' : 'heroicon-o-clock')
                ->color($pending === 0 ? 'success' : 'warning'),
            
            Stat::make('📅 Deze Maand', $thisMonth)
                ->description(now()->translatedFormat('F Y'))
                ->descriptionIcon($monthTrend > 0 ? 'heroicon-o-arrow-trending-up' : ($monthTrend < 0 ? 'heroicon-o-arrow-trending-down' : 'heroicon-o-minus'))
                ->color($monthTrend > 0 ? 'success' : ($monthTrend < 0 ? 'danger' : 'gray'))
                ->extraAttributes([
                    'class' => 'relative',
                ]),
        ];
    }
}

