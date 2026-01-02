<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class TaxDocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';
    
    protected static ?string $title = 'BTW Documenten';
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('vat_code')
                    ->label('BTW Code'),
                
                Forms\Components\Select::make('vat_rubriek')
                    ->label('BTW Rubriek')
                    ->options([
                        '1a' => '1a - Hoog tarief',
                        '1b' => '1b - Laag tarief',
                        '1c' => '1c - Vrijgesteld',
                        '2a' => '2a - Verleggingsregeling',
                        '3a' => '3a - Buitenland levering',
                        '3b' => '3b - Buitenland dienst',
                        '4a' => '4a - Voorbelasting',
                        '5b' => '5b - Totaal',
                    ]),
            ]);
    }
    
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('original_filename')
            ->columns([
                Tables\Columns\TextColumn::make('original_filename')
                    ->label('Bestand')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('document_date')
                    ->label('Datum')
                    ->date('d-m-Y'),
                
                Tables\Columns\TextColumn::make('amount_incl')
                    ->label('Bedrag')
                    ->money('EUR', locale: 'nl'),
                
                Tables\Columns\TextColumn::make('vat_code')
                    ->label('BTW Code')
                    ->badge(),
                
                Tables\Columns\TextColumn::make('vat_rubriek')
                    ->label('Rubriek')
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\IconColumn::make('auto_approved')
                    ->label('Auto')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('vat_rubriek')
                    ->label('Rubriek')
                    ->options([
                        '1a' => '1a',
                        '1b' => '1b',
                        '1c' => '1c',
                        '2a' => '2a',
                        '3a' => '3a',
                        '3b' => '3b',
                        '4a' => '4a',
                        '5b' => '5b',
                    ]),
            ])
            ->headerActions([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}

