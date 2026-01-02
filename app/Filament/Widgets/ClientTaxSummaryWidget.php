<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\VatPeriod;
use Filament\Widgets\TableWidget;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ClientTaxSummaryWidget extends TableWidget
{
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 2;
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Client::query()
                    ->with(['vatPeriods.documents'])
                    ->withCount('vatPeriods')
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Klant')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('total_vat')
                    ->label('Totaal BTW')
                    ->getStateUsing(function (Client $record) {
                        return $record->vatPeriods()
                            ->with('documents')
                            ->get()
                            ->sum(function ($period) {
                                return $period->documents->sum('amount_vat') ?? 0;
                            });
                    })
                    ->money('EUR', locale: 'nl')
                    ->sortable(),
                
                TextColumn::make('vat_periods_count')
                    ->label('Aantal Periodes')
                    ->counts('vatPeriods')
                    ->sortable(),
            ])
            ->defaultSort('total_vat', 'desc')
            ->paginated(false);
    }
}

