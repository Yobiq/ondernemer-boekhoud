<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Widgets\TaxTrendsWidget;
use App\Filament\Widgets\RubriekBreakdownWidget;

class TaxAnalyticsRelationManager extends RelationManager
{
    protected static string $relationship = 'vatPeriods';
    
    protected static ?string $title = 'BTW Analytics';
    
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('period_string')
            ->columns([
                Tables\Columns\TextColumn::make('period_string')
                    ->label('Periode'),
                
                Tables\Columns\TextColumn::make('total_vat')
                    ->label('Totaal BTW')
                    ->getStateUsing(function ($record) {
                        return $record->documents->sum('amount_vat') ?? 0;
                    })
                    ->money('EUR', locale: 'nl'),
                
                Tables\Columns\TextColumn::make('total_documents')
                    ->label('Documenten')
                    ->getStateUsing(fn ($record) => $record->documents->count()),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ]);
    }
}

