<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentResource\Pages;
use App\Models\Document;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Grouping\Group;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationLabel = 'Documenten';
    
    protected static ?string $navigationGroup = null; // Remove from navigation group
    
    protected static ?int $navigationSort = 1;
    
    // Hide from navigation
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Document Informatie')
                    ->schema([
                        Forms\Components\Select::make('client_id')
                            ->label('Klant')
                            ->relationship('client', 'name')
                            ->searchable()
                            ->required(),
                        
                        Forms\Components\TextInput::make('original_filename')
                            ->label('Bestandsnaam')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => '⏳ In Wachtrij',
                                'ocr_processing' => '🔄 Wordt Verwerkt',
                                'review_required' => '👀 In Beoordeling',
                                'approved' => '✅ Goedgekeurd',
                                'archived' => '📦 Gearchiveerd',
                                'task_opened' => '📋 Taak Geopend',
                            ])
                            ->required(),
                        
                        Forms\Components\Select::make('document_type')
                            ->label('Document Type')
                            ->options([
                                'receipt' => '🧾 Bonnetje',
                                'purchase_invoice' => '📄 Inkoopfactuur',
                                'bank_statement' => '🏦 Bankafschrift',
                                'sales_invoice' => '🧑‍💼 Verkoopfactuur',
                                'other' => '📁 Overig',
                            ]),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Financiële Details')
                    ->schema([
                        Forms\Components\TextInput::make('supplier_name')
                            ->label('Leverancier')
                            ->maxLength(255),
                        
                        Forms\Components\DatePicker::make('document_date')
                            ->label('Factuurdatum')
                            ->displayFormat('d-m-Y')
                            ->native(false),
                        
                        Forms\Components\Select::make('ledger_account_id')
                            ->label('Grootboekrekening')
                            ->relationship('ledgerAccount', 'description', fn ($query) => $query->orderBy('code'))
                            ->searchable(['code', 'description'])
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->code} - {$record->description}"),
                        
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('amount_excl')
                                    ->label('Excl. BTW')
                                    ->numeric()
                                    ->prefix('€')
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        $vatRate = $get('vat_rate');
                                        $amountExcl = (float) $state;
                                        if ($vatRate && $vatRate !== 'verlegd') {
                                            $amountVat = $amountExcl * ((float) $vatRate / 100);
                                            $amountIncl = $amountExcl + $amountVat;
                                            $set('amount_vat', number_format($amountVat, 2, '.', ''));
                                            $set('amount_incl', number_format($amountIncl, 2, '.', ''));
                                        }
                                    }),
                                
                                Forms\Components\TextInput::make('amount_vat')
                                    ->label('BTW Bedrag')
                                    ->numeric()
                                    ->prefix('€')
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        $amountExcl = (float) $get('amount_excl');
                                        $amountVat = (float) $state;
                                        $amountIncl = $amountExcl + $amountVat;
                                        $set('amount_incl', number_format($amountIncl, 2, '.', ''));
                                    }),
                                
                                Forms\Components\TextInput::make('amount_incl')
                                    ->label('Incl. BTW')
                                    ->numeric()
                                    ->prefix('€')
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        $amountExcl = (float) $get('amount_excl');
                                        $amountIncl = (float) $state;
                                        $amountVat = $amountIncl - $amountExcl;
                                        $set('amount_vat', number_format($amountVat, 2, '.', ''));
                                    }),
                            ]),
                        
                        Forms\Components\Select::make('vat_rate')
                            ->label('BTW Tarief')
                            ->options([
                                '21' => '21%',
                                '9' => '9%',
                                '0' => '0%',
                                'verlegd' => 'Verlegd',
                            ])
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                $amountExcl = (float) $get('amount_excl');
                                if ($amountExcl && $state && $state !== 'verlegd') {
                                    $amountVat = $amountExcl * ((float) $state / 100);
                                    $amountIncl = $amountExcl + $amountVat;
                                    $set('amount_vat', number_format($amountVat, 2, '.', ''));
                                    $set('amount_incl', number_format($amountIncl, 2, '.', ''));
                                }
                            }),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('OCR Data')
                    ->schema([
                        Forms\Components\TextInput::make('confidence_score')
                            ->label('Confidence Score')
                            ->numeric()
                            ->suffix('%')
                            ->disabled(),
                        
                        Forms\Components\Textarea::make('ocr_data')
                            ->label('OCR Data (JSON)')
                            ->disabled()
                            ->rows(5)
                            ->formatStateUsing(fn ($state) => $state ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : ''),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->groups([
                Group::make('client.name')
                    ->label('Klant')
                    ->collapsible(),
                Group::make('document_type')
                    ->label('Document Type')
                    ->collapsible(),
                Group::make('status')
                    ->label('Status')
                    ->collapsible(),
            ])
            ->defaultGroup('client.name')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Klant')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('original_filename')
                    ->label('Bestandsnaam')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->original_filename)
                    ->copyable(),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'ocr_processing',
                        'danger' => 'review_required',
                        'success' => 'approved',
                        'gray' => 'archived',
                        'primary' => 'task_opened',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'pending' => '⏳ In Wachtrij',
                        'ocr_processing' => '🔄 Wordt Verwerkt',
                        'review_required' => '👀 In Beoordeling',
                        'approved' => '✅ Goedgekeurd',
                        'archived' => '📦 Gearchiveerd',
                        'task_opened' => '📋 Taak Geopend',
                        default => $state,
                    })
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\BadgeColumn::make('document_type')
                    ->label('Type')
                    ->colors([
                        'success' => 'receipt',
                        'info' => 'purchase_invoice',
                        'warning' => 'bank_statement',
                        'primary' => 'sales_invoice',
                        'gray' => 'other',
                    ])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'receipt' => '🧾 Bonnetje',
                        'purchase_invoice' => '📄 Inkoopfactuur',
                        'bank_statement' => '🏦 Bankafschrift',
                        'sales_invoice' => '🧑‍💼 Verkoopfactuur',
                        default => '📁 Overig',
                    })
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('supplier_name')
                    ->label('Leverancier')
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('document_date')
                    ->label('Factuurdatum')
                    ->date('d-m-Y')
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('amount_incl')
                    ->label('Bedrag')
                    ->money('EUR', locale: 'nl')
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('ledgerAccount.code')
                    ->label('Grootboek')
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('confidence_score')
                    ->label('Confidence')
                    ->suffix('%')
                    ->sortable()
                    ->color(fn ($state) => $state >= 90 ? 'success' : ($state >= 70 ? 'warning' : 'danger'))
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Geüpload')
                    ->dateTime('d-m-Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => '⏳ In Wachtrij',
                        'ocr_processing' => '🔄 Wordt Verwerkt',
                        'review_required' => '👀 In Beoordeling',
                        'approved' => '✅ Goedgekeurd',
                        'archived' => '📦 Gearchiveerd',
                        'task_opened' => '📋 Taak Geopend',
                    ])
                    ->multiple(),
                
                SelectFilter::make('document_type')
                    ->label('Document Type')
                    ->options([
                        'receipt' => '🧾 Bonnetje',
                        'purchase_invoice' => '📄 Inkoopfactuur',
                        'bank_statement' => '🏦 Bankafschrift',
                        'sales_invoice' => '🧑‍💼 Verkoopfactuur',
                        'other' => '📁 Overig',
                    ])
                    ->multiple(),
                
                Filter::make('document_date')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('Van')
                            ->displayFormat('d-m-Y')
                            ->native(false),
                        DatePicker::make('created_until')
                            ->label('Tot')
                            ->displayFormat('d-m-Y')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('document_date', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('document_date', '<=', $date),
                            );
                    }),
                
                Filter::make('amount')
                    ->form([
                        Forms\Components\TextInput::make('amount_from')
                            ->label('Van')
                            ->numeric()
                            ->prefix('€'),
                        Forms\Components\TextInput::make('amount_until')
                            ->label('Tot')
                            ->numeric()
                            ->prefix('€'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['amount_from'],
                                fn (Builder $query, $amount): Builder => $query->where('amount_incl', '>=', $amount),
                            )
                            ->when(
                                $data['amount_until'],
                                fn (Builder $query, $amount): Builder => $query->where('amount_incl', '<=', $amount),
                            );
                    }),
                
                Filter::make('confidence_score')
                    ->form([
                        Forms\Components\TextInput::make('confidence_min')
                            ->label('Minimum Confidence')
                            ->numeric()
                            ->suffix('%'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['confidence_min'],
                                fn (Builder $query, $score): Builder => $query->where('confidence_score', '>=', $score),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Bekijken')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Document $record) => route('documents.file', $record))
                    ->openUrlInNewTab(),
                
                Tables\Actions\Action::make('download')
                    ->label('Downloaden')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (Document $record) => route('documents.download', $record)),
                
                Tables\Actions\Action::make('review')
                    ->label('Beoordelen')
                    ->icon('heroicon-o-document-check')
                    ->color('warning')
                    ->url(fn (Document $record) => \App\Filament\Pages\DocumentReview::getUrl(['document' => $record->id]))
                    ->visible(fn (Document $record) => $record->status === 'review_required'),
                
                Tables\Actions\Action::make('approve')
                    ->label('Goedkeuren')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Document $record) {
                        $record->update(['status' => 'approved']);
                        \Filament\Notifications\Notification::make()
                            ->title('Document goedgekeurd')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Document $record) => $record->status === 'review_required'),
                
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('approve_selected')
                        ->label('Goedkeuren')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $count = $records->where('status', 'review_required')->count();
                            $records->where('status', 'review_required')->each->update(['status' => 'approved']);
                            \Filament\Notifications\Notification::make()
                                ->title("{$count} documenten goedgekeurd")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    
                    Tables\Actions\BulkAction::make('bulk_assign_vat_code')
                        ->label('BTW Code Toewijzen')
                        ->icon('heroicon-o-tag')
                        ->color('info')
                        ->form([
                            Forms\Components\Select::make('vat_code')
                                ->label('BTW Code')
                                ->options([
                                    'NL21' => 'NL21 - Hoog tarief (21%)',
                                    'NL9' => 'NL9 - Laag tarief (9%)',
                                    'NL0' => 'NL0 - Vrijgesteld (0%)',
                                    'VERL' => 'VERL - Verleggingsregeling',
                                    'EU' => 'EU - Intracommunautair',
                                    'IMPORT' => 'IMPORT - Import',
                                    'VOORBELASTING' => 'VOORBELASTING - Voorbelasting',
                                ])
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $vatCalculator = app(\App\Services\VatCalculatorService::class);
                            $successCount = 0;
                            $errorCount = 0;
                            
                            foreach ($records as $record) {
                                try {
                                    $tempDoc = $record->replicate();
                                    $tempDoc->vat_code = $data['vat_code'];
                                    $rubriek = $vatCalculator->calculateRubriek($tempDoc);
                                    
                                    $record->update([
                                        'vat_code' => $data['vat_code'],
                                        'vat_rubriek' => $rubriek,
                                    ]);
                                    $successCount++;
                                } catch (\Exception $e) {
                                    $errorCount++;
                                }
                            }
                            
                            \Filament\Notifications\Notification::make()
                                ->title("BTW Code Toegewezen")
                                ->body("{$successCount} documenten bijgewerkt" . ($errorCount > 0 ? ", {$errorCount} fouten" : ""))
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    
                    Tables\Actions\BulkAction::make('bulk_assign_rubriek')
                        ->label('BTW Rubriek Toewijzen')
                        ->icon('heroicon-o-table-cells')
                        ->color('warning')
                        ->form([
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
                                ])
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $count = $records->count();
                            $records->each->update(['vat_rubriek' => $data['vat_rubriek']]);
                            
                            \Filament\Notifications\Notification::make()
                                ->title("BTW Rubriek Toegewezen")
                                ->body("{$count} documenten bijgewerkt")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    
                    Tables\Actions\BulkAction::make('bulk_approve_high_confidence')
                        ->label('Goedkeuren (Confidence ≥85%)')
                        ->icon('heroicon-o-sparkles')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Bulk Goedkeuring - Hoge Confidence')
                        ->modalDescription('Alleen documenten met confidence score ≥85% worden goedgekeurd.')
                        ->action(function ($records) {
                            $highConfidence = $records->filter(fn($r) => ($r->confidence_score ?? 0) >= 85);
                            $count = $highConfidence->count();
                            
                            $highConfidence->each->update(['status' => 'approved', 'auto_approved' => true]);
                            
                            \Filament\Notifications\Notification::make()
                                ->title("Bulk Goedkeuring Voltooid")
                                ->body("{$count} documenten automatisch goedgekeurd (confidence ≥85%)")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    
                    Tables\Actions\BulkAction::make('archive_selected')
                        ->label('Archiveren')
                        ->icon('heroicon-o-archive-box')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $count = $records->count();
                            $records->each->update(['status' => 'archived']);
                            \Filament\Notifications\Notification::make()
                                ->title("{$count} documenten gearchiveerd")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'edit' => Pages\EditDocument::route('/{record}/edit'),
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['client', 'ledgerAccount']);
    }
}
