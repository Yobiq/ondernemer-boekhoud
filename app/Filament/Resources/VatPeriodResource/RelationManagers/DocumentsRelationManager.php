<?php

namespace App\Filament\Resources\VatPeriodResource\RelationManagers;

use App\Models\Document;
use App\Services\VatCalculatorService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $title = 'Documenten in Periode';

    protected static ?string $recordTitleAttribute = 'original_filename';
    
    protected static bool $isLazy = true;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Documents are managed via attach/detach actions
            ]);
    }

    public function table(Table $table): Table
    {
        $vatCalculator = app(VatCalculatorService::class);
        $period = $this->getOwnerRecord();

        return $table
            ->heading("Documenten in {$period->period_string}")
            ->description('Documenten die aan deze periode zijn gekoppeld')
            ->columns([
                Tables\Columns\TextColumn::make('original_filename')
                    ->label('Document')
                    ->searchable()
                    ->wrap()
                    ->icon(fn (Document $record): string => match($record->document_type) {
                        'receipt' => 'heroicon-o-receipt-percent',
                        'purchase_invoice' => 'heroicon-o-document-text',
                        'sales_invoice' => 'heroicon-o-currency-euro',
                        default => 'heroicon-o-document',
                    })
                    ->iconColor(fn (Document $record): string => match($record->document_type) {
                        'receipt' => 'gray',
                        'purchase_invoice' => 'info',
                        'sales_invoice' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('document_date')
                    ->label('Datum')
                    ->date('d-m-Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('pivot.rubriek')
                    ->label('Rubriek')
                    ->getStateUsing(function ($record) use ($vatCalculator) {
                        if (!$record || !$record->pivot) {
                            return '—';
                        }
                        $rubriek = $record->pivot->rubriek ?? $vatCalculator->calculateRubriek($record);
                        $name = $vatCalculator->getRubriekName($rubriek);
                        return "{$rubriek} - {$name}";
                    })
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('amount_vat')
                    ->label('BTW')
                    ->money('EUR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'secondary' => 'pending',
                        'info' => 'ocr_processing',
                        'warning' => 'review_required',
                        'success' => 'approved',
                        'danger' => 'archived',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('document_type')
                    ->label('Type')
                    ->options([
                        'receipt' => '🧾 Bon',
                        'purchase_invoice' => '📄 Inkoop',
                        'sales_invoice' => '🧑‍💼 Verkoop',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('pivot.rubriek')
                    ->label('Rubriek')
                    ->options([
                        '1a' => '1a - Hoog tarief',
                        '1b' => '1b - Laag tarief',
                        '1c' => '1c - Overige tarieven',
                        '2a' => '2a - Verleggingsregeling binnenland',
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
                Action::make('add_documents')
                    ->label('Documenten Toevoegen')
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->visible(fn () => !$this->getOwnerRecord()->isLocked())
                    ->form([
                        Forms\Components\Select::make('documents')
                            ->label('Selecteer Documenten')
                            ->multiple()
                            ->searchable()
                            ->options(function () {
                                $period = $this->getOwnerRecord();
                                return Document::query()
                                    ->where('client_id', $period->client_id)
                                    ->where('status', 'approved')
                                    ->whereDoesntHave('vatPeriods', function ($q) use ($period) {
                                        $q->where('vat_periods.id', $period->id);
                                    })
                                    ->get()
                                    ->mapWithKeys(fn ($document) => [
                                        $document->id => $document->original_filename . ' (' . ($document->document_date ? $document->document_date->format('d-m-Y') : 'geen datum') . ')'
                                    ]);
                            })
                            ->required()
                            ->helperText('Zoek en selecteer goedgekeurde documenten om toe te voegen aan deze periode')
                            ->preload(),
                    ])
                    ->action(function (array $data) use ($vatCalculator) {
                        $period = $this->getOwnerRecord();
                        
                        if ($period->isLocked()) {
                            Notification::make()
                                ->title('Periode is vergrendeld')
                                ->body('U kunt geen documenten toevoegen aan een vergrendelde periode.')
                                ->danger()
                                ->send();
                            return;
                        }

                        $documentIds = $data['documents'] ?? [];
                        if (empty($documentIds)) {
                            return;
                        }

                        DB::transaction(function () use ($period, $documentIds, $vatCalculator) {
                            $attachData = [];
                            
                            foreach ($documentIds as $documentId) {
                                $document = Document::find($documentId);
                                if (!$document) continue;

                                // Skip unpaid sales invoices (cash basis rule)
                                if ($document->document_type === 'sales_invoice' && !$document->is_paid) {
                                    continue;
                                }

                                // Calculate rubriek and VAT code
                                $rubriek = $document->vat_rubriek ?? $vatCalculator->calculateRubriek($document);
                                $vatRate = (float) ($document->vat_rate ?? 0);
                                $vatCode = $document->vat_code ?? match(true) {
                                    $vatRate == 21 => 'NL21',
                                    $vatRate == 9 => 'NL9',
                                    $vatRate == 0 => 'NL0',
                                    default => null,
                                };

                                $attachData[$documentId] = [
                                    'rubriek' => $rubriek,
                                    'btw_code' => $vatCode,
                                ];
                            }

                            if (!empty($attachData)) {
                                $period->documents()->syncWithoutDetaching($attachData);
                                
                                Notification::make()
                                    ->title('Documenten toegevoegd')
                                    ->body(count($attachData) . ' document(en) succesvol toegevoegd aan periode.')
                                    ->success()
                                    ->send();
                            }
                        });
                    }),
            ])
            ->actions([
                Action::make('remove')
                    ->label('Verwijderen')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn () => !$this->getOwnerRecord()->isLocked())
                    ->requiresConfirmation()
                    ->action(function (Document $record) {
                        $period = $this->getOwnerRecord();
                        
                        if ($period->isLocked()) {
                            Notification::make()
                                ->title('Periode is vergrendeld')
                                ->danger()
                                ->send();
                            return;
                        }

                        $period->documents()->detach($record->id);
                        
                        Notification::make()
                            ->title('Document verwijderd')
                            ->body('Document is verwijderd uit deze periode.')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkAction::make('remove')
                    ->label('Verwijderen uit Periode')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn () => !$this->getOwnerRecord()->isLocked())
                    ->requiresConfirmation()
                    ->action(function ($records) {
                        $period = $this->getOwnerRecord();
                        
                        if ($period->isLocked()) {
                            Notification::make()
                                ->title('Periode is vergrendeld')
                                ->danger()
                                ->send();
                            return;
                        }

                        $documentIds = $records->pluck('id')->toArray();
                        $period->documents()->detach($documentIds);
                        
                        Notification::make()
                            ->title('Documenten verwijderd')
                            ->body(count($documentIds) . ' document(en) verwijderd uit periode.')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('document_date', 'desc')
            ->poll('30s');
    }
}

