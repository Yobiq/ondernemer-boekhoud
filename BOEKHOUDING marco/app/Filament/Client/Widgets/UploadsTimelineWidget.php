<?php

namespace App\Filament\Client\Widgets;

use App\Models\Document;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UploadsTimelineWidget extends Widget
{
    protected static string $view = 'filament.client.widgets.uploads-timeline-widget';
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 5;

    /**
     * Disable this widget - user requested removal
     */
    public static function canView(): bool
    {
        return false;
    }

    public function getViewData(): array
    {
        if (!Auth::check()) {
            return [
                'timeline' => [],
                'total' => 0,
                'avg' => 0,
            ];
        }
        
        $clientId = Auth::user()->client_id ?? null;
        
        // Get last 30 days of uploads
        $startDate = now()->subDays(29)->startOfDay();
        
        $uploads = Document::where('client_id', $clientId)
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // Fill in missing dates with 0
        $timeline = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $timeline[] = [
                'date' => $date,
                'label' => now()->subDays($i)->format('d M'),
                'count' => $uploads->firstWhere('date', $date)->count ?? 0,
            ];
        }
        
        $totalCount = array_sum(array_column($timeline, 'count'));
        $avg = count($timeline) > 0 ? round($totalCount / count($timeline), 1) : 0;
        
        return [
            'timeline' => $timeline,
            'total' => $totalCount,
            'avg' => $avg,
        ];
    }
}

