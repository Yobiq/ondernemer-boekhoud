<?php

namespace App\Filament\Resources\VatPeriodResource\RelationManagers;

use App\Services\VatCalculatorService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class VatRubricOverviewRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $title = 'BTW Rubrieken Overzicht';

    protected static ?string $recordTitleAttribute = 'rubriek';
    
    protected static bool $isLazy = false;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Read-only overview, no form needed
            ]);
    }

    public function table(Table $table): Table
    {
        $vatCalculator = app(VatCalculatorService::class);

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pivot.rubriek')
                    ->label('Rubriek')
                    ->getStateUsing(function ($record) use ($vatCalculator) {
                        if (!$record || !$record->pivot) {
                            return '—';
                        }
                        $rubriek = $record->pivot->rubriek ?? $vatCalculator->calculateRubriek($record);
                        $name = $vatCalculator->getRubriekName($rubriek);
                        return "{$rubriek} - {$name}";
                    }),
                
                Tables\Columns\TextColumn::make('amount_excl')
                    ->label('Grondslag Totaal')
                    ->money('EUR')
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('EUR')
                            ->label('Totaal'),
                    ]),
                
                Tables\Columns\TextColumn::make('amount_vat')
                    ->label('BTW Totaal')
                    ->money('EUR')
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('EUR')
                            ->label('Totaal'),
                    ]),
                
                Tables\Columns\TextColumn::make('supplier_name')
                    ->label('Leverancier')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('document_date')
                    ->label('Datum')
                    ->date('d-m-Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('pivot.btw_code')
                    ->label('BTW Code')
                    ->getStateUsing(fn ($record) => $record->pivot->btw_code ?? '—'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('rubriek')
                    ->label('Rubriek')
                    ->options([
                        '1a' => '1a - Hoog tarief',
                        '1b' => '1b - Laag tarief',
                        '1c' => '1c - Overige tarieven',
                        '2a' => '2a - Verleggingsregeling binnenland',
                        '2b' => '2b - Verleggingsregeling buitenland',
                        '3a' => '3a - Leveringen buitenland',
                        '3b' => '3b - Diensten buitenland',
                        '4a' => '4a - Voorbelasting (EU)',
                        '4b' => '4b - Voorbelasting (buiten EU)',
                        '5b' => '5b - Totaal verschuldigd/te ontvangen',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            return $query->wherePivot('rubriek', $data['value']);
                        }
                        return $query;
                    }),
            ])
            ->headerActions([
                // No create action - documents are added via DocumentResource
            ])
            ->actions([
                Tables\Actions\Action::make('bekijk_documenten')
                    ->label('Bekijk Documenten')
                    ->icon('heroicon-o-eye')
                    ->url(function ($record) use ($vatCalculator) {
                        $period = $this->getOwnerRecord();
                        if (!$period || !$record || !$record->pivot) {
                            return null;
                        }
                        return route('filament.admin.pages.vat-period-documents', [
                            'period' => $period->id,
                            'rubriek' => $record->pivot->rubriek ?? $vatCalculator->calculateRubriek($record),
                        ]);
                    })
                    ->visible(fn () => $this->getOwnerRecord() !== null),
            ])
            ->bulkActions([
                // No bulk actions
            ])
            ->groups([
                Tables\Grouping\Group::make('vat_rubriek')
                    ->label('Rubriek')
                    ->getTitleFromRecordUsing(function ($record) use ($vatCalculator) {
                        if (!$record) {
                            return '—';
                        }
                        // Try pivot first, then vat_rubriek field, then calculate
                        $rubriek = null;
                        if ($record->pivot && isset($record->pivot->rubriek)) {
                            $rubriek = $record->pivot->rubriek;
                        } elseif (isset($record->vat_rubriek)) {
                            $rubriek = $record->vat_rubriek;
                        } else {
                            $rubriek = $vatCalculator->calculateRubriek($record);
                        }
                        return "Rubriek {$rubriek}";
                    }),
            ]);
    }
}


