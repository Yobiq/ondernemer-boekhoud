<?php

namespace App\Filament\Widgets;

use App\Models\Document;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopSuppliersWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';
    
    protected static ?string $heading = 'Top 10 Leveranciers';
    
    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('supplier_name')
                    ->label('Leverancier')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('doc_count')
                    ->label('Aantal Documenten')
                    ->numeric()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Totaalbedrag')
                    ->money('EUR')
                    ->sortable(),
            ])
            ->paginated(false)
            ->defaultSort('doc_count', 'desc');
    }
    
    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return Document::query()
            ->whereNotNull('supplier_name')
            ->where('supplier_name', '!=', '')
            ->selectRaw('supplier_name, count(*) as doc_count, sum(amount_incl) as total_amount')
            ->groupBy('supplier_name')
            ->orderByDesc('doc_count')
            ->limit(10);
    }
    
    /**
     * Get the record key for the table widget
     * Since we're using groupBy, we need to override this to use supplier_name
     */
    public function getTableRecordKey($record): string
    {
        // For grouped queries, use supplier_name as the key since we don't have an ID
        if (is_object($record)) {
            $supplierName = $record->supplier_name ?? (method_exists($record, 'getAttribute') ? $record->getAttribute('supplier_name') : null) ?? null;
            if ($supplierName) {
                return (string) $supplierName;
            }
        } elseif (is_array($record) && isset($record['supplier_name'])) {
            return (string) $record['supplier_name'];
        }
        
        // Fallback: use a hash if supplier_name is not available
        return md5(serialize($record));
    }
}

