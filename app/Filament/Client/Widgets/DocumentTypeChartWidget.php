<?php

namespace App\Filament\Client\Widgets;

use App\Models\Document;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DocumentTypeChartWidget extends Widget
{
    protected static string $view = 'filament.client.widgets.document-type-chart-widget';
    protected int | string | array $columnSpan = ['default' => 6, 'md' => 6, 'lg' => 4];
    protected static ?int $sort = 6;

    public static function canView(): bool
    {
        // Disabled - causes large circular graphics on dashboard
        return false;
    }

    public function getViewData(): array
    {
        if (!Auth::check()) {
            return [
                'types' => [['type' => 'none', 'label' => 'Geen data', 'count' => 0]],
                'total' => 0,
            ];
        }
        
        $clientId = Auth::user()->client_id ?? null;
        
        $types = Document::where('client_id', $clientId)
            ->select('document_type', DB::raw('COUNT(*) as count'))
            ->groupBy('document_type')
            ->get()
            ->map(function ($item) {
                return [
                    'type' => $item->document_type ?? 'other',
                    'label' => $this->getTypeLabel($item->document_type ?? 'other'),
                    'count' => $item->count,
                ];
            })
            ->toArray();
        
        // Ensure we have at least one item
        if (empty($types)) {
            $types = [['type' => 'none', 'label' => 'Geen data', 'count' => 0]];
        }
        
        return [
            'types' => $types,
            'total' => array_sum(array_column($types, 'count')),
        ];
    }
    
    private function getTypeLabel(string $type): string
    {
        return match($type) {
            'receipt' => 'Bonnetjes',
            'purchase_invoice' => 'Inkoopfacturen',
            'bank_statement' => 'Bankafschriften',
            'sales_invoice' => 'Verkoopfacturen',
            'other' => 'Overig',
            default => 'Onbekend',
        };
    }
}

