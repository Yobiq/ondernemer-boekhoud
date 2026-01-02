<?php

namespace App\Filament\Client\Widgets;

use App\Models\Document;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class SpendingAnalyticsWidget extends Widget
{
    protected static string $view = 'filament.client.widgets.spending-analytics-widget';
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 4;
    
    /**
     * Disable this widget - it should not be displayed
     */
    public static function canView(): bool
    {
        return false;
    }

    public function getViewData(): array
    {
        $clientId = Auth::user()->client_id ?? null;
        
        // This month spending - for sales invoices, only count paid ones
        $thisMonth = Document::where('client_id', $clientId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where(function ($query) {
                $query->where(function ($q) {
                    // Sales invoices: only paid
                    $q->where('document_type', 'sales_invoice')
                      ->where('is_paid', true);
                })->orWhere(function ($q) {
                    // Other documents: all
                    $q->where('document_type', '!=', 'sales_invoice')
                      ->orWhereNull('document_type');
                });
            })
            ->whereNotNull('amount_incl')
            ->sum('amount_incl');
        
        // Last month spending
        $lastMonth = Document::where('client_id', $clientId)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('document_type', 'sales_invoice')
                      ->where('is_paid', true);
                })->orWhere(function ($q) {
                    $q->where('document_type', '!=', 'sales_invoice')
                      ->orWhereNull('document_type');
                });
            })
            ->whereNotNull('amount_incl')
            ->sum('amount_incl');
        
        // Calculate change
        $change = $lastMonth > 0 
            ? round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1)
            : ($thisMonth > 0 ? 100 : 0);
        
        // This year total
        $thisYear = Document::where('client_id', $clientId)
            ->whereYear('created_at', now()->year)
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('document_type', 'sales_invoice')
                      ->where('is_paid', true);
                })->orWhere(function ($q) {
                    $q->where('document_type', '!=', 'sales_invoice')
                      ->orWhereNull('document_type');
                });
            })
            ->whereNotNull('amount_incl')
            ->sum('amount_incl');
        
        // Average per month
        $avgPerMonth = now()->month > 0 
            ? round($thisYear / now()->month, 2)
            : $thisYear;
        
        return [
            'thisMonth' => round($thisMonth, 2),
            'lastMonth' => round($lastMonth, 2),
            'change' => $change,
            'thisYear' => round($thisYear, 2),
            'avgPerMonth' => round($avgPerMonth, 2),
        ];
    }
}

