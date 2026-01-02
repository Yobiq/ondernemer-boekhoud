<?php

namespace App\Filament\Pages;

use App\Models\Client;
use App\Models\Document;
use App\Jobs\ProcessDocumentOcrJob;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Grouping\Group;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class DocumentsByClient extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-folder';
    protected static ?string $navigationLabel = '👥 Klanten & Documenten';
    protected static ?string $navigationGroup = 'Overzichten';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.pages.documents-by-client';

    public ?int $selectedClientId = null;

    public function mount(): void
    {
        // Check if client is passed as query parameter
        $this->selectedClientId = request()->get('client');
    }

    public function table(Table $table): Table
    {
        // If a client is selected, show their documents
        if ($this->selectedClientId) {
            return $this->getDocumentsTable($table);
        }

        // Otherwise, show clients overview
        return $this->getClientsTable($table);
    }

    protected function getClientsTable(Table $table): Table
    {
        return $table
            ->query(
                Client::query()
                    ->withCount([
                        'documents',
                        'documents as pending_documents_count' => fn ($query) => 
                            $query->whereIn('status', ['pending', 'ocr_processing', 'review_required']),
                        'documents as approved_documents_count' => fn ($query) => 
                            $query->where('status', 'approved'),
                    ])
                    ->withSum('documents', 'amount_incl')
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Klant')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->size('lg')
                    ->icon('heroicon-o-user-circle')
                    ->iconColor('primary')
                    ->description(fn (Client $record) => $record->company_name ?: $record->email)
                    ->wrap()
                    ->url(fn (Client $record) => static::getUrl(['client' => $record->id]))
                    ->tooltip(fn (Client $record) => "Klik om alle documenten van {$record->name} te bekijken")
                    ->extraAttributes(['class' => 'cursor-pointer hover:text-primary-600 dark:hover:text-primary-400']),
                
                TextColumn::make('documents_count')
                    ->label('Totaal Documenten')
                    ->counts('documents')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->description(fn (Client $record) => $record->documents_count > 0 
                        ? number_format(($record->approved_documents_count ?? 0) / $record->documents_count * 100, 0) . '% goedgekeurd'
                        : 'Geen documenten'
                    ),
                
                TextColumn::make('pending_documents_count')
                    ->label('Actie Nodig')
                    ->counts('documents', fn ($query) => 
                        $query->whereIn('status', ['pending', 'ocr_processing', 'review_required'])
                    )
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'warning' : 'success')
                    ->formatStateUsing(fn ($state) => $state > 0 ? "⚠️ {$state}" : '✅ Geen')
                    ->sortable()
                    ->tooltip(fn (Client $record, $state) => $state > 0 
                        ? "{$state} document(en) vereisen aandacht"
                        : 'Alle documenten zijn verwerkt'
                    ),
                
                TextColumn::make('approved_documents_count')
                    ->label('Goedgekeurd')
                    ->counts('documents', fn ($query) => 
                        $query->where('status', 'approved')
                    )
                    ->badge()
                    ->color('success')
                    ->sortable(),
                
                TextColumn::make('documents_sum_amount_incl')
                    ->label('Totaal Bedrag')
                    ->money('EUR')
                    ->sortable()
                    ->placeholder('€0,00')
                    ->description(fn (Client $record) => $record->approved_documents_count > 0 
                        ? "Van {$record->approved_documents_count} goedgekeurde documenten"
                        : 'Geen goedgekeurde documenten'
                    )
                    ->weight('bold')
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray'),
                
                BadgeColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(function (Client $record) {
                        $pending = $record->pending_documents_count ?? 0;
                        if ($pending > 0) {
                            return 'action_required';
                        }
                        return 'active';
                    })
                    ->colors([
                        'success' => 'active',
                        'warning' => 'action_required',
                    ])
                    ->formatStateUsing(fn ($state) => $state === 'action_required' ? '⚠️ Actie Nodig' : '✅ Actief'),
            ])
            ->filters([
                SelectFilter::make('has_pending')
                    ->label('Status')
                    ->options([
                        'yes' => '⚠️ Heeft Actie Nodig',
                        'no' => '✅ Geen Actie Nodig',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if ($data['value'] === 'yes') {
                            return $query->has('documents', '>', 0)
                                ->whereHas('documents', fn ($q) => 
                                    $q->whereIn('status', ['pending', 'ocr_processing', 'review_required'])
                                );
                        }
                        if ($data['value'] === 'no') {
                            return $query->whereDoesntHave('documents', fn ($q) => 
                                $q->whereIn('status', ['pending', 'ocr_processing', 'review_required'])
                            );
                        }
                        return $query;
                    }),
            ])
            ->actions([
                Action::make('view_documents')
                    ->label('📋 Bekijk Documenten')
                    ->icon('heroicon-o-folder-open')
                    ->color('primary')
                    ->size('sm')
                    ->url(fn (Client $record) => static::getUrl(['client' => $record->id]))
                    ->openUrlInNewTab(false)
                    ->tooltip('Bekijk alle documenten van deze klant'),
                
                Action::make('workflow')
                    ->label('🔄 BTW Workflow')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->size('sm')
                    ->url(fn (Client $record) => \App\Filament\Pages\ClientTaxWorkflow::getUrl(['client' => $record->id]))
                    ->tooltip('Open BTW workflow voor deze klant'),
                
                Action::make('periods')
                    ->label('📅 Periodes')
                    ->icon('heroicon-o-calendar-days')
                    ->color('success')
                    ->size('sm')
                    ->url(fn (Client $record) => \App\Filament\Pages\BtwPeriodesPerKlant::getUrl(['client' => $record->id]))
                    ->tooltip('Bekijk BTW periodes voor deze klant'),
            ])
            ->bulkActions([
                BulkAction::make('export_selected')
                    ->label('📊 Export Geselecteerde')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function ($records) {
                        $clientIds = $records->pluck('id')->toArray();
                        $documents = \App\Models\Document::whereIn('client_id', $clientIds)->get();
                        $excelService = app(\App\Services\ExcelExportService::class);
                        return $excelService->exportDocuments($documents, 'klanten-export-' . now()->format('Y-m-d') . '.xlsx');
                    })
                    ->requiresConfirmation(),
            ])
            ->defaultSort('name')
            ->poll('30s')
            ->striped()
            ->paginated([10, 25, 50])
            ->recordUrl(fn (Client $record) => static::getUrl(['client' => $record->id]))
            ->recordAction('view_documents')
            ->emptyStateHeading('Geen klanten gevonden')
            ->emptyStateDescription('Voeg klanten toe via het Klanten menu.')
            ->emptyStateIcon('heroicon-o-users')
            ->emptyStateActions([
                Action::make('create_client')
                    ->label('Klant Toevoegen')
                    ->icon('heroicon-o-plus')
                    ->url(\App\Filament\Resources\ClientResource::getUrl('create')),
            ])
            ->deferLoading()
            ->persistSearchInSession()
            ->persistFiltersInSession();
    }

    protected function getDocumentsTable(Table $table): Table
    {
        $client = Client::findOrFail($this->selectedClientId);

        return $table
            ->query(
                Document::query()
                    ->where('client_id', $this->selectedClientId)
                    ->with(['ledgerAccount'])
            )
            ->searchable()
            ->searchPlaceholder('Zoek op bestandsnaam, leverancier, bedrag...')
            ->searchDebounce('500ms')
            ->heading("📋 Documenten van: {$client->name}")
            ->description(function () use ($client) {
                $info = [];
                if ($client->company_name) {
                    $info[] = "Bedrijf: {$client->company_name}";
                }
                if ($client->email) {
                    $info[] = "Email: {$client->email}";
                }
                if ($client->vat_number) {
                    $info[] = "BTW: {$client->vat_number}";
                }
                return !empty($info) ? implode(' • ', $info) : null;
            })
            ->groups([
                Group::make('document_type')
                    ->label('📄 Type')
                    ->collapsible()
                    ->getTitleFromRecordUsing(function (Document $record) {
                        $type = $record->document_type;
                        $count = Document::where('client_id', $this->selectedClientId)
                            ->where('document_type', $type)
                            ->count();
                        
                        $typeLabel = match($type) {
                            'receipt' => '🧾 Bonnetjes',
                            'purchase_invoice' => '📄 Inkoopfacturen',
                            'bank_statement' => '🏦 Bankafschriften',
                            'sales_invoice' => '🧑‍💼 Verkoopfacturen',
                            default => '📁 Overig',
                        };
                        
                        return "{$typeLabel} ({$count})";
                    }),
                
                Group::make('status')
                    ->label('⚡ Status')
                    ->collapsible()
                    ->getTitleFromRecordUsing(function (Document $record) {
                        $status = $record->status;
                        $type = $record->document_type;
                        $count = Document::where('client_id', $this->selectedClientId)
                            ->where('document_type', $type)
                            ->where('status', $status)
                            ->count();
                        
                        $statusLabel = match($status) {
                            'pending' => '⏳ In Wachtrij',
                            'ocr_processing' => '🔄 OCR Bezig',
                            'review_required' => '👀 Review Nodig',
                            'approved' => '✅ Goedgekeurd',
                            'archived' => '📦 Gearchiveerd',
                            default => $status,
                        };
                        
                        return "{$statusLabel} ({$count})";
                    }),
            ])
            ->defaultGroup('document_type')
            ->defaultGroup('status')
            ->columns([
                TextColumn::make('original_filename')
                    ->label('Bestandsnaam')
                    ->searchable()
                    ->sortable()
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
                    ->url(fn (Document $record) => \App\Filament\Pages\DocumentReview::getUrl(['document' => $record->id]))
                    ->tooltip('Klik om document te beoordelen')
                    ->extraAttributes(['class' => 'cursor-pointer hover:text-primary-600 dark:hover:text-primary-400']),
                
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
                        'receipt' => '🧾 Bonnetje',
                        'purchase_invoice' => '📄 Inkoopfactuur',
                        'bank_statement' => '🏦 Bankafschrift',
                        'sales_invoice' => '🧑‍💼 Verkoopfactuur',
                        default => '📁 Overig',
                    }),
                
                BadgeColumn::make('status')
                    ->label('OCR/Status')
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
                        'pending' => '⏳ In Wachtrij',
                        'ocr_processing' => '🔄 OCR Bezig',
                        'review_required' => '👀 Review Nodig',
                        'approved' => '✅ Verwerkt',
                        'archived' => '📦 Gearchiveerd',
                        default => $state,
                    })
                    ->toggleable(isToggledHiddenByDefault: false),
                
                TextColumn::make('supplier_name')
                    ->label('Leverancier')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(),
                
                TextColumn::make('amount_incl')
                    ->label('Bedrag')
                    ->money('EUR')
                    ->placeholder('—')
                    ->weight('bold')
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('document_date')
                    ->label('Datum')
                    ->date('d-m-Y')
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(),
                
                TextColumn::make('confidence_score')
                    ->label('OCR Confidence')
                    ->suffix('%')
                    ->sortable()
                    ->color(fn ($state) => $state >= 90 ? 'success' : ($state >= 70 ? 'warning' : 'danger'))
                    ->placeholder('—')
                    ->formatStateUsing(function ($state, Document $record) {
                        if ($state === null) {
                            // Check if OCR data exists
                            if ($record->ocr_data && !empty($record->ocr_data['raw_text'])) {
                                return '✅ Data';
                            }
                            return '⏳ Wacht';
                        }
                        return number_format($state, 0) . '%';
                    })
                    ->tooltip(function (Document $record) {
                        if ($record->ocr_data) {
                            $hasData = !empty($record->ocr_data['raw_text']);
                            $hasAmounts = !empty($record->ocr_data['amounts']['incl'] ?? null);
                            $hasSupplier = !empty($record->ocr_data['supplier']['name'] ?? null);
                            $hasDate = !empty($record->ocr_data['invoice']['date'] ?? null);
                            
                            $info = ['OCR Data: ✅'];
                            if ($hasAmounts) $info[] = 'Bedragen: ✅';
                            if ($hasSupplier) $info[] = 'Leverancier: ✅';
                            if ($hasDate) $info[] = 'Datum: ✅';
                            
                            return implode(' | ', $info);
                        }
                        return 'Geen OCR data beschikbaar';
                    })
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                SelectFilter::make('document_type')
                    ->label('📄 Document Type')
                    ->options([
                        'receipt' => '🧾 Bonnetje',
                        'purchase_invoice' => '📄 Inkoopfactuur',
                        'bank_statement' => '🏦 Bankafschrift',
                        'sales_invoice' => '🧑‍💼 Verkoopfactuur',
                        'other' => '📁 Overig',
                    ])
                    ->multiple()
                    ->indicator('Type'),
                
                SelectFilter::make('status')
                    ->label('⚡ Status')
                    ->options([
                        'pending' => '⏳ In Wachtrij',
                        'ocr_processing' => '🔄 Wordt Verwerkt',
                        'review_required' => '👀 In Beoordeling',
                        'approved' => '✅ Goedgekeurd',
                        'archived' => '📦 Gearchiveerd',
                    ])
                    ->multiple()
                    ->default(['pending', 'ocr_processing', 'review_required'])
                    ->indicator('Status'),
                
                Filter::make('date_range')
                    ->label('📅 Datum Bereik')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('date_from')
                            ->label('Van')
                            ->displayFormat('d-m-Y'),
                        \Filament\Forms\Components\DatePicker::make('date_to')
                            ->label('Tot')
                            ->displayFormat('d-m-Y'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('document_date', '>=', $date),
                            )
                            ->when(
                                $data['date_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('document_date', '<=', $date),
                            );
                    })
                    ->indicator('Datum'),
                
                Filter::make('needs_attention')
                    ->label('⚠️ Actie Nodig')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereIn('status', ['pending', 'ocr_processing', 'review_required'])
                    )
                    ->toggle()
                    ->indicator('Actie Nodig'),
                
                Filter::make('confidence_threshold')
                    ->label('🎯 OCR Confidence')
                    ->form([
                        \Filament\Forms\Components\Select::make('threshold')
                            ->label('Minimum Confidence')
                            ->options([
                                '90' => '90%+ (Zeer Hoog)',
                                '70' => '70%+ (Hoog)',
                                '50' => '50%+ (Gemiddeld)',
                                '0' => 'Alle',
                            ])
                            ->default('0'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!empty($data['threshold']) && $data['threshold'] !== '0') {
                            return $query->where('confidence_score', '>=', (int)$data['threshold']);
                        }
                        return $query;
                    })
                    ->indicator('Confidence'),
                
                Filter::make('amount_range')
                    ->label('💰 Bedrag Bereik')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('amount_min')
                            ->label('Minimum')
                            ->numeric()
                            ->prefix('€'),
                        \Filament\Forms\Components\TextInput::make('amount_max')
                            ->label('Maximum')
                            ->numeric()
                            ->prefix('€'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['amount_min'],
                                fn (Builder $query, $amount): Builder => $query->where('amount_incl', '>=', $amount),
                            )
                            ->when(
                                $data['amount_max'],
                                fn (Builder $query, $amount): Builder => $query->where('amount_incl', '<=', $amount),
                            );
                    })
                    ->indicator('Bedrag'),
            ])
            ->headerActions([
                Action::make('back_to_clients')
                    ->label('← Terug naar Klanten')
                    ->icon('heroicon-o-arrow-left')
                    ->color('gray')
                    ->url(static::getUrl())
                    ->outlined()
                    ->keyBindings(['b'])
                    ->tooltip('Terug naar klantenoverzicht (B)'),
                
                Action::make('workflow')
                    ->label('🔄 BTW Workflow')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->url(fn () => \App\Filament\Pages\ClientTaxWorkflow::getUrl(['client' => $this->selectedClientId]))
                    ->keyBindings(['w'])
                    ->tooltip('Open BTW workflow (W)'),
                
                Action::make('process_all_pending')
                    ->label('⚡ Verwerk Alle Pending')
                    ->icon('heroicon-o-bolt')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Alle Pending Documenten Verwerken')
                    ->modalDescription(function () {
                        $pendingCount = Document::where('client_id', $this->selectedClientId)
                            ->whereIn('status', ['pending', 'ocr_processing'])
                            ->count();
                        return "Weet u zeker dat u {$pendingCount} pending document(en) wilt verwerken?";
                    })
                    ->action(function () {
                        $documents = Document::where('client_id', $this->selectedClientId)
                            ->whereIn('status', ['pending', 'ocr_processing'])
                            ->get();
                        
                        foreach ($documents as $document) {
                            if ($document->status === 'pending') {
                                ProcessDocumentOcrJob::dispatch($document);
                            }
                        }
                        
                        Notification::make()
                            ->title('Verwerking Gestart')
                            ->body("{$documents->count()} document(en) worden verwerkt.")
                            ->success()
                            ->send();
                    })
                    ->visible(fn () => Document::where('client_id', $this->selectedClientId)
                        ->whereIn('status', ['pending', 'ocr_processing'])
                        ->exists())
                    ->tooltip('Verwerk alle pending documenten'),
                
                Action::make('export_excel')
                    ->label('📊 Export naar Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->keyBindings(['e'])
                    ->action(function () use ($client) {
                        // Build query manually since getTableQuery() may not be available in action context
                        $query = Document::query()
                            ->where('client_id', $this->selectedClientId)
                            ->with(['ledgerAccount']);
                        
                        // Apply any active filters from the table
                        $documents = $query->get();
                        
                        $excelService = app(\App\Services\ExcelExportService::class);
                        return $excelService->exportDocuments($documents, "documenten-{$client->name}-" . now()->format('Y-m-d') . '.xlsx');
                    })
                    ->tooltip('Exporteer alle documenten naar Excel (E)'),
            ])
            ->actions([
                Action::make('quick_view')
                    ->label('👁️ Quick View')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading(fn (Document $record) => "Document: {$record->original_filename}")
                    ->modalContent(fn (Document $record) => view('filament.components.document-quick-view', ['document' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Sluiten')
                    ->tooltip('Snel bekijken zonder pagina te verlaten'),
                
                Action::make('view_ocr_data')
                    ->label('📊 OCR Data')
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->modalHeading(fn (Document $record) => "OCR Data: {$record->original_filename}")
                    ->modalContent(fn (Document $record) => view('filament.components.ocr-data-viewer', ['document' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Sluiten')
                    ->visible(fn (Document $record) => !empty($record->ocr_data) || in_array($record->status, ['ocr_processing', 'review_required', 'approved']))
                    ->tooltip('Bekijk geëxtraheerde OCR data'),
                
                Action::make('view')
                    ->label('Bekijken')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Document $record) => route('documents.file', $record))
                    ->openUrlInNewTab()
                    ->tooltip('Open document in nieuw tabblad'),
                
                Action::make('review')
                    ->label('Beoordelen')
                    ->icon('heroicon-o-document-check')
                    ->color('warning')
                    ->url(fn (Document $record) => \App\Filament\Pages\DocumentReview::getUrl(['document' => $record->id]))
                    ->visible(fn (Document $record) => $record->status === 'review_required')
                    ->tooltip('Open document review pagina'),
                
                Action::make('reprocess_ocr')
                    ->label('🔄 Herverwerk OCR')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('OCR Herverwerken')
                    ->modalDescription('Weet u zeker dat u de OCR voor dit document opnieuw wilt uitvoeren?')
                    ->action(function (Document $record) {
                        $record->update([
                            'status' => 'pending',
                            'ocr_data' => null,
                            'amount_excl' => null,
                            'amount_vat' => null,
                            'amount_incl' => null,
                            'vat_rate' => null,
                            'document_date' => null,
                            'supplier_name' => null,
                            'supplier_vat' => null,
                            'confidence_score' => null,
                            'review_required_reason' => null,
                        ]);
                        
                        ProcessDocumentOcrJob::dispatch($record);
                        
                        Notification::make()
                            ->title('OCR Herverwerking Gestart')
                            ->body("Document #{$record->id} wordt opnieuw verwerkt.")
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Document $record) => in_array($record->status, ['pending', 'ocr_processing', 'review_required']))
                    ->tooltip('Herverwerk OCR voor dit document'),
            ])
            ->bulkActions([
                BulkAction::make('bulk_approve')
                    ->label('✅ Bulk Goedkeuren (Smart)')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Bulk Goedkeuren met Validatie')
                    ->modalDescription(function ($records) {
                        $total = $records->count();
                        $canApprove = $records->filter(function ($doc) {
                            return $doc->status === 'review_required' 
                                && $doc->confidence_score >= 85
                                && $doc->ledger_account_id !== null;
                        })->count();
                        
                        $needsAttention = $total - $canApprove;
                        
                        $message = "{$total} document(en) geselecteerd.\n";
                        $message .= "✅ {$canApprove} kunnen automatisch goedgekeurd worden (confidence ≥85%, grootboek toegewezen).\n";
                        if ($needsAttention > 0) {
                            $message .= "⚠️ {$needsAttention} vereisen aandacht (lage confidence of ontbrekend grootboek).";
                        }
                        
                        return $message;
                    })
                    ->action(function ($records) {
                        $vatCalculator = app(\App\Services\VatCalculatorService::class);
                        $approved = 0;
                        $skipped = 0;
                        $errors = [];
                        
                        DB::transaction(function () use ($records, $vatCalculator, &$approved, &$skipped, &$errors) {
                            foreach ($records as $document) {
                                try {
                                    // Validate before approving
                                    if ($document->status !== 'review_required' && $document->status !== 'pending') {
                                        $skipped++;
                                        continue;
                                    }
                                    
                                    // Check confidence
                                    if ($document->confidence_score < 85) {
                                        $errors[] = "{$document->original_filename}: lage confidence ({$document->confidence_score}%)";
                                        $skipped++;
                                        continue;
                                    }
                                    
                                    // Check ledger account
                                    if (!$document->ledger_account_id) {
                                        $errors[] = "{$document->original_filename}: geen grootboekrekening toegewezen";
                                        $skipped++;
                                        continue;
                                    }
                                    
                                    // Validate BTW
                                    $vatValidator = app(\App\Services\VatValidator::class);
                                    $validation = $vatValidator->validate(
                                        (float) $document->amount_excl,
                                        (float) $document->amount_vat,
                                        $document->vat_rate
                                    );
                                    
                                    if (!$validation['valid']) {
                                        $errors[] = "{$document->original_filename}: {$validation['message']}";
                                        $skipped++;
                                        continue;
                                    }
                                    
                                    // Approve
                                    $document->update([
                                        'status' => 'approved',
                                        'vat_rubriek' => $document->vat_rubriek ?? $vatCalculator->calculateRubriek($document),
                                    ]);
                                    $approved++;
                                    
                                } catch (\Exception $e) {
                                    $errors[] = "{$document->original_filename}: {$e->getMessage()}";
                                    $skipped++;
                                }
                            }
                        });
                        
                        $notification = Notification::make()
                            ->title('Bulk Goedkeuring Voltooid')
                            ->body("✅ {$approved} document(en) goedgekeurd");
                        
                        if ($skipped > 0) {
                            $notification->body("✅ {$approved} goedgekeurd, ⚠️ {$skipped} overgeslagen");
                        }
                        
                        if (!empty($errors)) {
                            $notification->body($notification->getBody() . "\n\nFouten:\n" . implode("\n", array_slice($errors, 0, 5)));
                            if (count($errors) > 5) {
                                $notification->body($notification->getBody() . "\n... en " . (count($errors) - 5) . " meer");
                            }
                        }
                        
                        $notification->success()->send();
                    })
                    ->deselectRecordsAfterCompletion()
                    ->visible(fn () => $this->selectedClientId !== null),
                
                BulkAction::make('bulk_reprocess_ocr')
                    ->label('🔄 Bulk OCR Herverwerken')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Bulk OCR Herverwerken')
                    ->modalDescription(fn ($records) => "Weet u zeker dat u OCR wilt herverwerken voor {$records->count()} document(en)?")
                    ->action(function ($records) {
                        $count = 0;
                        foreach ($records as $document) {
                            $document->update([
                                'status' => 'pending',
                                'ocr_data' => null,
                                'amount_excl' => null,
                                'amount_vat' => null,
                                'amount_incl' => null,
                                'vat_rate' => null,
                                'document_date' => null,
                                'supplier_name' => null,
                                'supplier_vat' => null,
                                'confidence_score' => null,
                                'review_required_reason' => null,
                            ]);
                            
                            ProcessDocumentOcrJob::dispatch($document);
                            $count++;
                        }
                        
                        Notification::make()
                            ->title('Bulk OCR Herverwerking Gestart')
                            ->body("{$count} document(en) worden opnieuw verwerkt.")
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion()
                    ->visible(fn () => $this->selectedClientId !== null),
                
                BulkAction::make('bulk_export')
                    ->label('📊 Export Geselecteerde')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function ($records) {
                        $excelService = app(\App\Services\ExcelExportService::class);
                        return $excelService->exportDocuments($records, 'geselecteerde-documenten-' . now()->format('Y-m-d') . '.xlsx');
                    })
                    ->visible(fn () => $this->selectedClientId !== null),
                
                BulkAction::make('bulk_assign_ledger')
                    ->label('📊 Bulk Grootboek Toewijzen')
                    ->icon('heroicon-o-book-open')
                    ->color('info')
                    ->form([
                        \Filament\Forms\Components\Select::make('ledger_account_id')
                            ->label('Grootboekrekening')
                            ->relationship('ledgerAccount', 'description', fn ($query) => $query->orderBy('code'))
                            ->searchable(['code', 'description'])
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->code} - {$record->description}")
                            ->required(),
                    ])
                    ->action(function ($records, array $data) {
                        $count = $records->count();
                        $records->each->update(['ledger_account_id' => $data['ledger_account_id']]);
                        
                        Notification::make()
                            ->title('Grootboek Toegewezen')
                            ->body("{$count} document(en) gekoppeld aan grootboekrekening")
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->deselectRecordsAfterCompletion(),
                
                BulkAction::make('bulk_archive')
                    ->label('📦 Bulk Archiveren')
                    ->icon('heroicon-o-archive-box')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Bulk Archiveren')
                    ->modalDescription(fn ($records) => "Weet u zeker dat u {$records->count()} document(en) wilt archiveren?")
                    ->action(function ($records) {
                        $count = $records->count();
                        $records->each->update(['status' => 'archived']);
                        
                        Notification::make()
                            ->title('Gearchiveerd')
                            ->body("{$count} document(en) gearchiveerd")
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),
                
                BulkAction::make('bulk_mark_paid')
                    ->label('💰 Bulk Markeer als Betaald')
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
                    ->action(function ($records, array $data) {
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
                    ->visible(fn ($records) => $records && $records->where('document_type', 'sales_invoice')->where('is_paid', false)->isNotEmpty()),
            ])
            ->defaultSort('document_date', 'desc')
            ->poll('30s')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->recordUrl(fn (Document $record) => \App\Filament\Pages\DocumentReview::getUrl(['document' => $record->id]))
            ->recordAction('review')
            ->emptyStateHeading('Geen documenten gevonden')
            ->emptyStateDescription("Deze klant heeft nog geen documenten geüpload. Documenten kunnen worden geüpload via het klantenportaal.")
            ->emptyStateIcon('heroicon-o-document-text')
            ->emptyStateActions([
                Action::make('upload_guide')
                    ->label('📖 Upload Instructies')
                    ->icon('heroicon-o-information-circle')
                    ->url('#')
                    ->tooltip('Bekijk hoe klanten documenten kunnen uploaden'),
            ])
            ->deferLoading()
            ->persistSearchInSession()
            ->persistColumnSearchesInSession()
            ->persistFiltersInSession();
    }
}
