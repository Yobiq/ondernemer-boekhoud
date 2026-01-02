<?php

namespace App\Filament\Client\Widgets;

use App\Models\Document;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class MyDocumentsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = ['default' => 'full', 'md' => 'full', 'lg' => 'full'];
    protected static ?int $sort = 8;
    
    public function table(Table $table): Table
    {
        $clientId = Auth::user()->client_id ?? null;
        
        return $table
            ->heading('📄 Mijn Documenten')
            ->description('Zoek, filter en sorteer uw documenten')
            ->query(
                Document::query()
                    ->where('client_id', $clientId)
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('original_filename')
                    ->label('Bestandsnaam')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-document')
                    ->limit(30)
                    ->tooltip(function (Document $record): string {
                        return $record->original_filename;
                    }),
                
                Tables\Columns\TextColumn::make('document_type')
                    ->label('Type')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'receipt' => 'gray',
                        'purchase_invoice' => 'info',
                        'bank_statement' => 'success',
                        'sales_invoice' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'receipt' => '🧾 Bon',
                        'purchase_invoice' => '📄 Inkoop',
                        'bank_statement' => '🏦 Bank',
                        'sales_invoice' => '📊 Verkoop',
                        default => '📁 Overig',
                    })
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'ocr_processing' => 'info',
                        'review_required' => 'warning',
                        'approved' => 'success',
                        'archived' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(function (string $state, $record) {
                        $statusText = match ($state) {
                            'pending' => '⏳ In wachtrij',
                            'ocr_processing' => '🔄 Wordt verwerkt',
                            'review_required' => '👀 In beoordeling',
                            'approved' => '✅ Goedgekeurd',
                            'archived' => '📦 Gearchiveerd',
                            default => $state,
                        };
                        
                        // Add processing indicator for OCR processing
                        if ($state === 'ocr_processing') {
                            $statusText .= ' <span class="processing-spinner"></span>';
                        }
                        
                        return $statusText;
                    })
                    ->html(),
                
                Tables\Columns\TextColumn::make('supplier_name')
                    ->label('Leverancier')
                    ->searchable()
                    ->sortable()
                    ->limit(25)
                    ->placeholder('Onbekend')
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                
                Tables\Columns\TextColumn::make('amount_incl')
                    ->label('Bedrag')
                    ->money('EUR')
                    ->sortable()
                    ->placeholder('€ 0,00')
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('EUR')
                            ->label('Totaal'),
                    ]),
                
                Tables\Columns\TextColumn::make('document_date')
                    ->label('Document Datum')
                    ->date('d-m-Y')
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Upload Datum')
                    ->date('d-m-Y H:i')
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('confidence_score')
                    ->label('Zekerheid')
                    ->formatStateUsing(fn (?float $state): string => $state ? round($state) . '%' : '-')
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'In wachtrij',
                        'ocr_processing' => 'Wordt verwerkt',
                        'review_required' => 'In beoordeling',
                        'approved' => 'Goedgekeurd',
                        'archived' => 'Gearchiveerd',
                    ])
                    ->multiple(),
                
                Tables\Filters\SelectFilter::make('document_type')
                    ->label('Document Type')
                    ->options([
                        'receipt' => 'Bonnetjes',
                        'purchase_invoice' => 'Inkoopfacturen',
                        'bank_statement' => 'Bankafschriften',
                        'sales_invoice' => 'Verkoopfacturen',
                        'other' => 'Overig',
                    ])
                    ->multiple(),
                
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')
                            ->label('Van'),
                        \Filament\Forms\Components\DatePicker::make('until')
                            ->label('Tot'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'], fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (!$data['from'] && !$data['until']) {
                            return null;
                        }
                        
                        if ($data['from'] && $data['until']) {
                            return 'Upload: ' . \Carbon\Carbon::parse($data['from'])->format('d-m-Y') . ' tot ' . \Carbon\Carbon::parse($data['until'])->format('d-m-Y');
                        }
                        
                        if ($data['from']) {
                            return 'Upload vanaf: ' . \Carbon\Carbon::parse($data['from'])->format('d-m-Y');
                        }
                        
                        return 'Upload tot: ' . \Carbon\Carbon::parse($data['until'])->format('d-m-Y');
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('details')
                    ->label('Details')
                    ->icon('heroicon-o-information-circle')
                    ->color('primary')
                    ->modalHeading(fn (Document $record) => 'Details: ' . $record->original_filename)
                    ->modalContent(fn (Document $record) => view('filament.client.document-details', ['document' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Sluiten'),
                    
                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(fn (Document $record): string => route('documents.download', $record))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Bulk actions can be added here in the future
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->poll('60s')
            ->emptyStateHeading('Nog geen documenten')
            ->emptyStateDescription('Upload uw eerste document om te beginnen!')
            ->emptyStateIcon(null)
            ->deferLoading(); // Defer loading to prevent JavaScript errors
    }
}

