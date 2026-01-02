<?php

namespace App\Filament\Client\Widgets;

use App\Models\Document;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TopSuppliersWidget extends Widget
{
    protected static string $view = 'filament.client.widgets.top-suppliers-widget';
    protected int | string | array $columnSpan = ['default' => 6, 'md' => 6, 'lg' => 4];
    protected static ?int $sort = 7;

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
                'suppliers' => [],
                'hasData' => false,
            ];
        }
        
        $clientId = Auth::user()->client_id ?? null;
        
        // For sales invoices, only count paid ones; for others, count all
        $suppliers = Document::where('client_id', $clientId)
            ->whereNotNull('supplier_name')
            ->where('supplier_name', '!=', '')
            ->where('status', 'approved')
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
            ->select(
                'supplier_name',
                DB::raw('COUNT(*) as doc_count'),
                DB::raw('COALESCE(SUM(amount_incl), 0) as total_amount')
            )
            ->groupBy('supplier_name')
            ->havingRaw('COALESCE(SUM(amount_incl), 0) > 0')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->supplier_name ?? 'Onbekend',
                    'count' => (int) ($item->doc_count ?? 0),
                    'amount' => round((float) ($item->total_amount ?? 0), 2),
                ];
            })
            ->toArray();
        
        return [
            'suppliers' => $suppliers,
            'hasData' => !empty($suppliers),
        ];
    }
}

