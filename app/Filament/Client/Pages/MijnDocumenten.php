<?php

namespace App\Filament\Client\Pages;

use App\Models\Document;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;

class MijnDocumenten extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Mijn Documenten';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.client.pages.mijn-documenten';
    protected static ?string $navigationGroup = 'Documenten';
    
    public function table(Table $table): Table
    {
        $clientId = Auth::user()->client_id;
        
        return $table
            ->query(
                Document::query()
                    ->where('client_id', $clientId)
                    ->latest()
            )
            ->searchable() // Enable global search
            ->searchPlaceholder('Zoek op bestandsnaam, leverancier, bedrag...')
            ->searchDebounce('500ms') // Wait 500ms after typing before searching
            ->groups([
                \Filament\Tables\Grouping\Group::make('document_type')
                    ->label('Document Type')
                    ->collapsible(),
                \Filament\Tables\Grouping\Group::make('status')
                    ->label('Status')
                    ->collapsible(),
            ])
            ->defaultGroup('document_type')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Geüpload')
                    ->dateTime('d-m-Y H:i')
                    ->sortable()
                    ->size('sm')
                    ->toggleable(),
                
                TextColumn::make('original_filename')
                    ->label('Bestand')
                    ->searchable()
                    ->icon(fn (Document $record): string => match($record->document_type) {
                        'receipt' => 'heroicon-o-receipt-percent',
                        'purchase_invoice' => 'heroicon-o-document-text',
                        'bank_statement' => 'heroicon-o-building-library',
                        'sales_invoice' => 'heroicon-o-currency-euro',
                        default => 'heroicon-o-document',
                    })
                    ->iconColor(fn (Document $record): string => match($record->document_type) {
                        'receipt' => 'gray',
                        'purchase_invoice' => 'info',
                        'bank_statement' => 'success',
                        'sales_invoice' => 'warning',
                        default => 'gray',
                    })
                    ->weight('bold')
                    ->wrap()
                    ->description(fn (Document $record): string => $record->document_type ? match($record->document_type) {
                        'receipt' => '🧾 Bonnetje',
                        'purchase_invoice' => '📄 Inkoopfactuur',
                        'bank_statement' => '🏦 Bankafschrift',
                        'sales_invoice' => '🧑‍💼 Verkoopfactuur',
                        default => '📁 Overig',
                    } : '')
                    ->tooltip(fn (Document $record): string => $record->original_filename),
                
                BadgeColumn::make('document_type')
                    ->label('Type')
                    ->colors([
                        'gray' => 'receipt',
                        'info' => 'purchase_invoice',
                        'success' => 'bank_statement',
                        'warning' => 'sales_invoice',
                        'secondary' => 'other',
                    ])
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'receipt' => '🧾 Bon',
                        'purchase_invoice' => '📄 Inkoop',
                        'bank_statement' => '🏦 Bank',
                        'sales_invoice' => '🧑‍💼 Verkoop',
                        default => '📁 Overig',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('supplier_name')
                    ->label('Leverancier')
                    ->searchable()
                    ->placeholder('—')
                    ->size('sm')
                    ->toggleable(),
                
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'secondary' => 'pending',
                        'info' => 'ocr_processing',
                        'warning' => 'review_required',
                        'success' => 'approved',
                        'danger' => 'archived',
                    ])
                    ->icons([
                        'heroicon-o-clock' => 'pending',
                        'heroicon-o-arrow-path' => 'ocr_processing',
                        'heroicon-o-eye' => 'review_required',
                        'heroicon-o-check-circle' => 'approved',
                        'heroicon-o-archive-box' => 'archived',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'In Wachtrij',
                        'ocr_processing' => 'Wordt Verwerkt',
                        'review_required' => 'In Beoordeling',
                        'approved' => 'Goedgekeurd',
                        'archived' => 'Gearchiveerd',
                        default => $state,
                    }),
                
                TextColumn::make('amount_incl')
                    ->label('Bedrag')
                    ->money('EUR')
                    ->placeholder('—')
                    ->weight('bold')
                    ->size('sm')
                    ->sortable()
                    ->toggleable()
                    ->color(fn (?float $state): string => $state && $state > 0 ? 'success' : 'gray')
                    ->description(fn (Document $record): ?string => 
                        $record->document_type === 'sales_invoice' 
                            ? ($record->is_paid ? '✅ Betaald' : '⏳ Onbetaald')
                            : null
                    )
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Sum::make()
                            ->money('EUR')
                            ->label('Totaal'),
                    ]),
                
                TextColumn::make('is_paid')
                    ->label('Betaalstatus')
                    ->icon(fn (Document $record): string => 
                        $record->is_paid ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle'
                    )
                    ->color(fn (Document $record): string => 
                        $record->is_paid ? 'success' : 'gray'
                    )
                    ->formatStateUsing(fn (Document $record): string => 
                        $record->is_paid 
                            ? 'Betaald' . ($record->paid_at ? ' (' . $record->paid_at->format('d-m-Y') . ')' : '')
                            : 'Onbetaald'
                    )
                    ->visible(fn () => false) // Hidden by default, can be toggled
                    ->toggleable(),
                
                TextColumn::make('document_date')
                    ->label('Datum')
                    ->date('d-m-Y')
                    ->sortable()
                    ->placeholder('—')
                    ->size('sm')
                    ->toggleable(),
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
                    ])
                    ->multiple()
                    ->placeholder('Alle statussen'),
                
                SelectFilter::make('document_type')
                    ->label('Type')
                    ->options([
                        'receipt' => '🧾 Bonnetje',
                        'purchase_invoice' => '📄 Inkoopfactuur',
                        'bank_statement' => '🏦 Bankafschrift',
                        'sales_invoice' => '🧑‍💼 Verkoopfactuur',
                        'other' => '📁 Overig',
                    ])
                    ->multiple()
                    ->placeholder('Alle types'),
                
                Filter::make('supplier')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('supplier_name')
                            ->label('Leverancier')
                            ->placeholder('Zoek op leverancier...')
                            ->autocomplete(),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(isset($data['supplier_name']) && !empty($data['supplier_name']), fn ($q, $name) => $q->where('supplier_name', 'like', "%{$name}%"));
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (!isset($data['supplier_name']) || empty($data['supplier_name'])) {
                            return null;
                        }
                        return 'Leverancier: ' . $data['supplier_name'];
                    }),
                
                Filter::make('created_at')
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
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(isset($data['created_from']) && $data['created_from'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when(isset($data['created_until']) && $data['created_until'], fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): ?string {
                        $from = $data['created_from'] ?? null;
                        $until = $data['created_until'] ?? null;
                        
                        if (!$from && !$until) {
                            return null;
                        }
                        if ($from && $until) {
                            return 'Upload: ' . \Carbon\Carbon::parse($from)->format('d-m-Y') . ' tot ' . \Carbon\Carbon::parse($until)->format('d-m-Y');
                        }
                        if ($from) {
                            return 'Upload vanaf: ' . \Carbon\Carbon::parse($from)->format('d-m-Y');
                        }
                        return 'Upload tot: ' . \Carbon\Carbon::parse($until)->format('d-m-Y');
                    }),
                
                Filter::make('document_date')
                    ->form([
                        DatePicker::make('doc_date_from')
                            ->label('Document Datum Van')
                            ->displayFormat('d-m-Y')
                            ->native(false),
                        DatePicker::make('doc_date_until')
                            ->label('Document Datum Tot')
                            ->displayFormat('d-m-Y')
                            ->native(false),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(isset($data['doc_date_from']) && $data['doc_date_from'], fn ($q, $date) => $q->whereDate('document_date', '>=', $date))
                            ->when(isset($data['doc_date_until']) && $data['doc_date_until'], fn ($q, $date) => $q->whereDate('document_date', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): ?string {
                        $from = $data['doc_date_from'] ?? null;
                        $until = $data['doc_date_until'] ?? null;
                        
                        if (!$from && !$until) {
                            return null;
                        }
                        if ($from && $until) {
                            return 'Document Datum: ' . \Carbon\Carbon::parse($from)->format('d-m-Y') . ' tot ' . \Carbon\Carbon::parse($until)->format('d-m-Y');
                        }
                        if ($from) {
                            return 'Document Datum vanaf: ' . \Carbon\Carbon::parse($from)->format('d-m-Y');
                        }
                        return 'Document Datum tot: ' . \Carbon\Carbon::parse($until)->format('d-m-Y');
                    }),
                
                Filter::make('amount')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('amount_from')
                            ->label('Bedrag vanaf')
                            ->numeric()
                            ->prefix('€'),
                        \Filament\Forms\Components\TextInput::make('amount_until')
                            ->label('Bedrag tot')
                            ->numeric()
                            ->prefix('€'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(isset($data['amount_from']) && $data['amount_from'], fn ($q, $amount) => $q->where('amount_incl', '>=', $amount))
                            ->when(isset($data['amount_until']) && $data['amount_until'], fn ($q, $amount) => $q->where('amount_incl', '<=', $amount));
                    })
                    ->indicateUsing(function (array $data): ?string {
                        $from = $data['amount_from'] ?? null;
                        $until = $data['amount_until'] ?? null;
                        
                        if (!$from && !$until) {
                            return null;
                        }
                        if ($from && $until) {
                            return 'Bedrag: €' . number_format($from, 2, ',', '.') . ' - €' . number_format($until, 2, ',', '.');
                        }
                        if ($from) {
                            return 'Bedrag vanaf: €' . number_format($from, 2, ',', '.');
                        }
                        return 'Bedrag tot: €' . number_format($until, 2, ',', '.');
                    }),
                
                Filter::make('quick_filters')
                    ->form([
                        \Filament\Forms\Components\Select::make('quick_filter')
                            ->label('Snelle Filters')
                            ->options([
                                'today' => '📅 Vandaag',
                                'this_week' => '📆 Deze Week',
                                'this_month' => '📅 Deze Maand',
                                'last_month' => '📅 Vorige Maand',
                                'last_30_days' => '📊 Laatste 30 Dagen',
                                'last_90_days' => '📊 Laatste 90 Dagen',
                                'this_year' => '📅 Dit Jaar',
                            ])
                            ->placeholder('Kies een periode...'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(isset($data['quick_filter']) && $data['quick_filter'], function ($q, $filter) {
                            return match($filter) {
                                'today' => $q->whereDate('created_at', today()),
                                'this_week' => $q->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
                                'this_month' => $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
                                'last_month' => $q->whereMonth('created_at', now()->subMonth()->month)->whereYear('created_at', now()->subMonth()->year),
                                'last_30_days' => $q->where('created_at', '>=', now()->subDays(30)),
                                'last_90_days' => $q->where('created_at', '>=', now()->subDays(90)),
                                'this_year' => $q->whereYear('created_at', now()->year),
                                default => $q,
                            };
                        });
                    })
                    ->indicateUsing(function (array $data): ?string {
                        $filter = $data['quick_filter'] ?? null;
                        if (!$filter) {
                            return null;
                        }
                        return match($filter) {
                            'today' => 'Vandaag',
                            'this_week' => 'Deze Week',
                            'this_month' => 'Deze Maand',
                            'last_month' => 'Vorige Maand',
                            'last_30_days' => 'Laatste 30 Dagen',
                            'last_90_days' => 'Laatste 90 Dagen',
                            'this_year' => 'Dit Jaar',
                            default => null,
                        };
                    }),
                
                Filter::make('payment_status')
                    ->label('Betaalstatus')
                    ->form([
                        \Filament\Forms\Components\Select::make('is_paid')
                            ->label('Status')
                            ->options([
                                '1' => '✅ Betaald',
                                '0' => '⏳ Onbetaald',
                            ])
                            ->placeholder('Alle'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            isset($data['is_paid']) && $data['is_paid'] !== '',
                            fn ($q, $value) => $q->where('is_paid', (bool) $value)
                        );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (!isset($data['is_paid']) || $data['is_paid'] === '') {
                            return null;
                        }
                        return $data['is_paid'] === '1' ? 'Betaald' : 'Onbetaald';
                    })
                    ->visible(fn () => true),
            ])
            ->actions([
                Action::make('preview')
                    ->label('Bekijken')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading(fn (Document $record) => 'Preview: ' . $record->original_filename)
                    ->modalContent(fn (Document $record) => view('filament.client.document-preview', ['document' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Sluiten')
                    ->modalWidth('7xl'),
                
                Action::make('mark_as_paid')
                    ->label(fn (Document $record) => $record->is_paid ? 'Betaald' : 'Markeer als Betaald')
                    ->icon(fn (Document $record) => $record->is_paid ? 'heroicon-o-check-circle' : 'heroicon-o-currency-euro')
                    ->color(fn (Document $record) => $record->is_paid ? 'success' : 'warning')
                    ->requiresConfirmation(fn (Document $record) => !$record->is_paid)
                    ->modalHeading('Markeer als Betaald')
                    ->modalDescription(fn (Document $record) => "Weet u zeker dat u '{$record->original_filename}' als betaald wilt markeren?")
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('paid_at')
                            ->label('Betaaldatum')
                            ->default(now())
                            ->required()
                            ->displayFormat('d-m-Y')
                            ->native(false),
                        \Filament\Forms\Components\Select::make('payment_method')
                            ->label('Betaalmethode')
                            ->options([
                                'bank_transfer' => 'Bankoverschrijving',
                                'cash' => 'Contant',
                                'card' => 'Pinpas/Creditcard',
                                'paypal' => 'PayPal',
                                'other' => 'Anders',
                            ])
                            ->default('bank_transfer'),
                    ])
                    ->action(function (Document $record, array $data) {
                        $record->update([
                            'is_paid' => true,
                            'paid_at' => $data['paid_at'] ?? now(),
                        ]);
                        
                        Notification::make()
                            ->title('Factuur gemarkeerd als betaald')
                            ->body("Factuur '{$record->original_filename}' is gemarkeerd als betaald op " . ($data['paid_at'] ?? now())->format('d-m-Y'))
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Document $record) => $record->document_type === 'sales_invoice' && $record->status === 'approved')
                    ->disabled(fn (Document $record) => $record->is_paid),
                
                Action::make('mark_as_unpaid')
                    ->label('Markeer als Onbetaald')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Markeer als Onbetaald')
                    ->modalDescription(fn (Document $record) => "Weet u zeker dat u '{$record->original_filename}' als onbetaald wilt markeren?")
                    ->action(function (Document $record) {
                        $record->update([
                            'is_paid' => false,
                            'paid_at' => null,
                        ]);
                        
                        Notification::make()
                            ->title('Factuur gemarkeerd als onbetaald')
                            ->body("Factuur '{$record->original_filename}' is gemarkeerd als onbetaald")
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Document $record) => $record->document_type === 'sales_invoice' && $record->is_paid),
                
                Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->url(fn (Document $record): string => route('documents.download', $record))
                    ->openUrlInNewTab(),
                
                Action::make('details')
                    ->label('Details')
                    ->icon('heroicon-o-information-circle')
                    ->color('gray')
                    ->modalHeading(fn (Document $record) => 'Details: ' . $record->original_filename)
                    ->modalContent(fn (Document $record) => view('filament.client.document-details', ['document' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Sluiten')
                    ->modalWidth('2xl'),
            ])
            ->bulkActions([
                BulkAction::make('export_selected')
                    ->label('📥 Exporteer Geselecteerd (CSV)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (Collection $records) {
                        $csv = "Datum,Bestand,Type,Status,Leverancier,Bedrag,Document Datum,BTW,Excl. BTW\n";
                        
                        foreach ($records as $record) {
                            $csv .= sprintf(
                                "%s,%s,%s,%s,%s,%s,%s,%s,%s\n",
                                $record->created_at->format('d-m-Y H:i'),
                                '"' . str_replace('"', '""', $record->original_filename) . '"',
                                $record->document_type ?? '—',
                                $record->status,
                                $record->supplier_name ?? '—',
                                $record->amount_incl ? number_format($record->amount_incl, 2, ',', '.') : '—',
                                $record->document_date ? $record->document_date->format('d-m-Y') : '—',
                                $record->amount_vat ? number_format($record->amount_vat, 2, ',', '.') : '—',
                                $record->amount_excl ? number_format($record->amount_excl, 2, ',', '.') : '—'
                            );
                        }
                        
                        $filename = 'documenten_export_' . now()->format('Y-m-d_His') . '.csv';
                        
                        return response()->streamDownload(function () use ($csv) {
                            echo $csv;
                        }, $filename, [
                            'Content-Type' => 'text/csv; charset=UTF-8',
                            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                        ]);
                    })
                    ->requiresConfirmation()
                    ->deselectRecordsAfterCompletion(),
                
                BulkAction::make('archive_selected')
                    ->label('📦 Archiveer Geselecteerd')
                    ->icon('heroicon-o-archive-box')
                    ->color('warning')
                    ->action(function (Collection $records) {
                        $count = $records->count();
                        $records->each->update(['status' => 'archived']);
                        
                        Notification::make()
                            ->title('Gearchiveerd')
                            ->body("{$count} document(en) gearchiveerd")
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->deselectRecordsAfterCompletion(),
                
                BulkAction::make('unarchive_selected')
                    ->label('📤 Herstel Geselecteerd')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->action(function (Collection $records) {
                        $count = $records->count();
                        $records->each->update(['status' => 'approved']);
                        
                        Notification::make()
                            ->title('Hersteld')
                            ->body("{$count} document(en) hersteld")
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->deselectRecordsAfterCompletion()
                    ->visible(fn (?Collection $records) => $records && $records->where('status', 'archived')->isNotEmpty()),
                
                BulkAction::make('mark_paid')
                    ->label('💰 Markeer als Betaald')
                    ->icon('heroicon-o-currency-euro')
                    ->color('success')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('paid_at')
                            ->label('Betaaldatum')
                            ->default(now())
                            ->required()
                            ->displayFormat('d-m-Y')
                            ->native(false),
                    ])
                    ->action(function (Collection $records, array $data) {
                        $salesInvoices = $records->filter(fn ($doc) => $doc->document_type === 'sales_invoice');
                        $count = $salesInvoices->count();
                        
                        $salesInvoices->each->update([
                            'is_paid' => true,
                            'paid_at' => $data['paid_at'] ?? now(),
                        ]);
                        
                        Notification::make()
                            ->title('Gemarkeerd als betaald')
                            ->body("{$count} verkoopfactuur(en) gemarkeerd als betaald")
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->deselectRecordsAfterCompletion()
                    ->visible(fn (?Collection $records) => $records && $records->where('document_type', 'sales_invoice')->where('is_paid', false)->isNotEmpty()),
                
                BulkAction::make('mark_unpaid')
                    ->label('⏳ Markeer als Onbetaald')
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->action(function (Collection $records) {
                        $salesInvoices = $records->filter(fn ($doc) => $doc->document_type === 'sales_invoice');
                        $count = $salesInvoices->count();
                        
                        $salesInvoices->each->update([
                            'is_paid' => false,
                            'paid_at' => null,
                        ]);
                        
                        Notification::make()
                            ->title('Gemarkeerd als onbetaald')
                            ->body("{$count} verkoopfactuur(en) gemarkeerd als onbetaald")
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->deselectRecordsAfterCompletion()
                    ->visible(fn (?Collection $records) => $records && $records->where('document_type', 'sales_invoice')->where('is_paid', true)->isNotEmpty()),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('15s') // Auto-refresh every 15 seconds for better real-time feel
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->emptyStateHeading('Nog geen documenten')
            ->emptyStateDescription('Upload uw eerste document om te beginnen!')
            ->emptyStateIcon('heroicon-o-document-magnifying-glass')
            ->emptyStateActions([
                Action::make('upload')
                    ->label('📸 Upload Document')
                    ->url(\App\Filament\Client\Pages\SmartUpload::getUrl())
                    ->button()
                    ->color('primary'),
            ])
            ->headerActions([
                Action::make('export_all')
                    ->label('📥 Exporteer Alles (CSV)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function () {
                        $clientId = Auth::user()->client_id;
                        $documents = Document::where('client_id', $clientId)->get();
                        
                        $csv = "Datum,Bestand,Type,Status,Leverancier,Bedrag,Document Datum,BTW,Excl. BTW\n";
                        
                        foreach ($documents as $doc) {
                            $csv .= sprintf(
                                "%s,%s,%s,%s,%s,%s,%s,%s,%s\n",
                                $doc->created_at->format('d-m-Y H:i'),
                                '"' . str_replace('"', '""', $doc->original_filename) . '"',
                                $doc->document_type ?? '—',
                                $doc->status,
                                $doc->supplier_name ?? '—',
                                $doc->amount_incl ? number_format($doc->amount_incl, 2, ',', '.') : '—',
                                $doc->document_date ? $doc->document_date->format('d-m-Y') : '—',
                                $doc->amount_vat ? number_format($doc->amount_vat, 2, ',', '.') : '—',
                                $doc->amount_excl ? number_format($doc->amount_excl, 2, ',', '.') : '—'
                            );
                        }
                        
                        $filename = 'alle_documenten_' . now()->format('Y-m-d_His') . '.csv';
                        
                        return response()->streamDownload(function () use ($csv) {
                            echo $csv;
                        }, $filename, [
                            'Content-Type' => 'text/csv; charset=UTF-8',
                            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                        ]);
                    }),
                
                Action::make('upload_new')
                    ->label('📸 Nieuw Uploaden')
                    ->icon('heroicon-o-plus-circle')
                    ->color('primary')
                    ->url(\App\Filament\Client\Pages\SmartUpload::getUrl()),
            ]);
    }
}

