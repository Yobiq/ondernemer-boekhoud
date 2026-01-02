<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use App\Services\VatPeriodPdfService;
use App\Services\Belastingdienst\XmlExportService;
use App\Services\Belastingdienst\BelastingdienstValidator;

class TaxPeriodsRelationManager extends RelationManager
{
    protected static string $relationship = 'vatPeriods';
    
    protected static ?string $title = 'BTW Periodes';
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('period_start')
                    ->label('Start Datum')
                    ->required(),
                
                Forms\Components\DatePicker::make('period_end')
                    ->label('Eind Datum')
                    ->required()
                    ->after('period_start'),
                
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'open' => 'Open',
                        'voorbereid' => 'Voorbereid',
                        'ingediend' => 'Ingediend',
                        'afgesloten' => 'Afgesloten',
                    ])
                    ->default('open')
                    ->required(),
            ]);
    }
    
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('period_string')
            ->columns([
                Tables\Columns\TextColumn::make('period_string')
                    ->label('Periode')
                    ->getStateUsing(fn ($record) => $record->period_string),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'open',
                        'info' => 'voorbereid',
                        'success' => 'ingediend',
                        'danger' => 'afgesloten',
                    ]),
                
                Tables\Columns\TextColumn::make('documents_count')
                    ->label('Documenten')
                    ->counts('documents'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Action::make('quick_report')
                    ->label('Rapport')
                    ->icon('heroicon-o-document-text')
                    ->action(function ($record) {
                        try {
                            $pdfService = app(VatPeriodPdfService::class);
                            return $pdfService->download($record);
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Fout')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                
                Action::make('validate')
                    ->label('Valideer')
                    ->icon('heroicon-o-check-circle')
                    ->action(function ($record) {
                        try {
                            $validator = app(BelastingdienstValidator::class);
                            $result = $validator->validatePeriod($record);
                            
                            if ($result->isValid) {
                                Notification::make()
                                    ->title('Validatie Geslaagd')
                                    ->body('Periode is geldig')
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Validatiefouten')
                                    ->body(implode(', ', array_values($result->errors)))
                                    ->warning()
                                    ->send();
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Fout')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}

