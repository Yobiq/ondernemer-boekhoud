<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VatPeriodResource\Pages;
use App\Models\VatPeriod;
use App\Services\VatPeriodLockService;
use App\Services\VatPeriodPdfService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class VatPeriodResource extends Resource
{
    protected static ?string $model = VatPeriod::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    
    protected static ?string $navigationLabel = 'BTW Periodes';
    
    protected static ?string $navigationGroup = 'Financieel';
    
    protected static bool $shouldRegisterNavigation = false;
    
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Periode Informatie')
                    ->schema([
                        Forms\Components\Select::make('client_id')
                            ->label('Klant')
                            ->relationship('client', 'name')
                            ->searchable()
                            ->required()
                            ->disabled(fn ($record) => $record && $record->isLocked()),
                        
                        Forms\Components\DatePicker::make('period_start')
                            ->label('Start Datum')
                            ->required()
                            ->default(now()->startOfQuarter())
                            ->disabled(fn ($record) => $record && $record->isLocked()),
                        
                        Forms\Components\DatePicker::make('period_end')
                            ->label('Eind Datum')
                            ->required()
                            ->default(now()->endOfQuarter())
                            ->after('period_start')
                            ->disabled(fn ($record) => $record && $record->isLocked()),
                        
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'open' => '⏳ Open',
                                'voorbereid' => '🟡 Voorbereid',
                                'ingediend' => '📤 Ingediend',
                                'afgesloten' => '🔒 Afgesloten',
                            ])
                            ->default('open')
                            ->required()
                            ->disabled(fn ($record) => $record && $record->isLocked()),
                        
                        Forms\Components\Textarea::make('notes')
                            ->label('Notities')
                            ->rows(3)
                            ->disabled(fn ($record) => $record && $record->isLocked()),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Klant')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('period_string')
                    ->label('Periode')
                    ->getStateUsing(fn (VatPeriod $record) => $record->period_string)
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('period_start')
                    ->label('Start')
                    ->date('d-m-Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('period_end')
                    ->label('Eind')
                    ->date('d-m-Y')
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'open',
                        'info' => 'voorbereid',
                        'success' => 'ingediend',
                        'danger' => 'afgesloten',
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'open' => '⏳ Open',
                        'voorbereid' => '🟡 Voorbereid',
                        'ingediend' => '📤 Ingediend',
                        'afgesloten' => '🔒 Afgesloten',
                        default => $state,
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('preparedBy.name')
                    ->label('Voorbereid door')
                    ->default('—')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('submittedBy.name')
                    ->label('Ingediend door')
                    ->default('—')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('closedBy.name')
                    ->label('Afgesloten door')
                    ->default('—')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('documents_count')
                    ->label('Documenten')
                    ->counts('documents')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('client_id')
                    ->label('Klant')
                    ->relationship('client', 'name')
                    ->searchable(),
                
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'open' => '⏳ Open',
                        'voorbereid' => '🟡 Voorbereid',
                        'ingediend' => '📤 Ingediend',
                        'afgesloten' => '🔒 Afgesloten',
                    ]),
                
                SelectFilter::make('year')
                    ->label('Jaar')
                    ->options(function () {
                        $years = VatPeriod::selectRaw('DISTINCT year')
                            ->orderBy('year', 'desc')
                            ->pluck('year', 'year')
                            ->toArray();
                        return $years;
                    }),
            ])
            ->actions([
                Action::make('voorbereiden')
                    ->label('Voorbereiden')
                    ->icon('heroicon-o-check-circle')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (VatPeriod $record) => $record->status === 'open')
                    ->action(function (VatPeriod $record) {
                        $record->update([
                            'status' => 'voorbereid',
                            'prepared_by' => Auth::id(),
                            'prepared_at' => now(),
                        ]);
                        
                        Notification::make()
                            ->title('Periode voorbereid')
                            ->success()
                            ->send();
                    }),
                
                Action::make('markeer_ingediend')
                    ->label('Markeer als Ingediend')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (VatPeriod $record) => $record->status === 'voorbereid')
                    ->action(function (VatPeriod $record) {
                        $record->update([
                            'status' => 'ingediend',
                            'submitted_by' => Auth::id(),
                            'submitted_at' => now(),
                        ]);
                        
                        Notification::make()
                            ->title('Periode gemarkeerd als ingediend')
                            ->success()
                            ->send();
                    }),
                
                Action::make('afsluiten')
                    ->label('Afsluiten')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Periode Afsluiten')
                    ->modalDescription('Weet u zeker dat u deze periode wilt afsluiten? Na afsluiten kunnen geen mutaties meer worden gemaakt.')
                    ->visible(fn (VatPeriod $record) => !$record->isLocked())
                    ->action(function (VatPeriod $record) {
                        try {
                            $lockService = app(VatPeriodLockService::class);
                            $lockService->lock($record, Auth::user());
                            
                            Notification::make()
                                ->title('Periode afgesloten')
                                ->body('De periode is succesvol afgesloten en kan niet meer worden gewijzigd.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Fout bij afsluiten')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                
                Action::make('bekijk_rubrieken')
                    ->label('Bekijk Rubrieken')
                    ->icon('heroicon-o-table-cells')
                    ->color('primary')
                    ->url(fn (VatPeriod $record) => VatPeriodResource::getUrl('view', ['record' => $record])),
                
                Action::make('export_pdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->action(function (VatPeriod $record) {
                        try {
                            $pdfService = app(VatPeriodPdfService::class);
                            return $pdfService->download($record);
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Fout bij PDF genereren')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                
                Action::make('export_xml')
                    ->label('Export XML')
                    ->icon('heroicon-o-code-bracket')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('XML Export voor Belastingdienst')
                    ->modalDescription('Weet u zeker dat u de XML export wilt genereren? Deze kan worden gebruikt voor BTW aangifte.')
                    ->action(function (VatPeriod $record) {
                        try {
                            $xmlService = app(\App\Services\Belastingdienst\XmlExportService::class);
                            
                            // Validate XML before download
                            $xml = $xmlService->generateXml($record);
                            if (!$xmlService->validateXml($xml)) {
                                Notification::make()
                                    ->title('XML Validatie Mislukt')
                                    ->body('De gegenereerde XML voldoet niet aan het schema.')
                                    ->warning()
                                    ->send();
                                return;
                            }
                            
                            return $xmlService->downloadXml($record);
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Fout bij XML genereren')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                
                Tables\Actions\EditAction::make()
                    ->visible(fn (VatPeriod $record) => !$record->isLocked()),
                
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // No bulk actions for locked periods
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\VatPeriodResource\RelationManagers\DocumentsRelationManager::class,
            \App\Filament\Resources\VatPeriodResource\RelationManagers\VatRubricOverviewRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVatPeriods::route('/'),
            'create' => Pages\CreateVatPeriod::route('/create'),
            'view' => Pages\ViewVatPeriod::route('/{record}'),
            'edit' => Pages\EditVatPeriod::route('/{record}/edit'),
        ];
    }
}

