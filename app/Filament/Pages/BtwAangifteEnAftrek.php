<?php

namespace App\Filament\Pages;

use App\Models\Client;
use App\Models\Document;
use App\Models\VatPeriod;
use App\Services\VatCalculatorService;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class BtwAangifteEnAftrek extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationLabel = '📊 BTW Aangifte & Aftrek';
    protected static ?string $navigationGroup = 'Overzichten';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.pages.btw-aangifte-en-aftrek';

    public ?int $selectedClientId = null;
    public string $activeTab = 'clients';

    public function mount(): void
    {
        $this->activeTab = 'clients';
    }

    public function updatedActiveTab(): void
    {
        $this->resetTable();
    }

    public function selectClient(int $clientId): void
    {
        $this->selectedClientId = $clientId;
        $this->activeTab = 'documents';
        $this->resetTable();
    }

    public function backToClients(): void
    {
        $this->selectedClientId = null;
        $this->activeTab = 'clients';
        $this->resetTable();
    }

    public function getTableQueryStringIdentifier(): ?string
    {
        return $this->activeTab === 'documents' ? 'documents' : 'clients';
    }

    public function table(Table $table): Table
    {
        return $this->activeTab === 'documents' 
            ? $this->getDocumentsTable($table)
            : $this->getClientsTable($table);
    }

    protected function getClientsTable(Table $table): Table
    {
        return $table
            ->query(
                Client::query()
                    ->withCount([
                        'documents as aangifte_count' => function ($query) {
                            $query->where('status', 'approved')
                                ->where('document_type', 'sales_invoice')
                                ->where('is_paid', true);
                        },
                        'documents as aftrek_count' => function ($query) {
                            $query->where('status', 'approved')
                                ->whereIn('document_type', ['purchase_invoice', 'receipt'])
                                ->whereNotNull('amount_vat')
                                ->where('amount_vat', '>', 0);
                        },
                    ])
                    ->withSum([
                        'documents as aangifte_total' => function ($query) {
                            $query->where('status', 'approved')
                                ->where('document_type', 'sales_invoice')
                                ->where('is_paid', true)
                                ->whereNotNull('amount_vat');
                        }
                    ], 'amount_vat')
                    ->withSum([
                        'documents as aftrek_total' => function ($query) {
                            $query->where('status', 'approved')
                                ->whereIn('document_type', ['purchase_invoice', 'receipt'])
                                ->whereNotNull('amount_vat')
                                ->where('amount_vat', '>', 0);
                        }
                    ], 'amount_vat')
            )
            ->heading('BTW Overzicht per Klant')
            ->description('Selecteer een klant om de BTW details te bekijken')
            ->columns([
                TextColumn::make('name')
                    ->label('Klant')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->size('lg'),

                TextColumn::make('aangifte_count')
                    ->label('Aangifte')
                    ->formatStateUsing(fn ($state) => $state ?? 0)
                    ->badge()
                    ->color('info')
                    ->suffix(' documenten'),

                TextColumn::make('aangifte_total')
                    ->label('BTW Verschuldigd')
                    ->money('EUR')
                    ->color('info')
                    ->weight('bold')
                    ->formatStateUsing(fn ($state) => $state ?? 0)
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('EUR')
                            ->label('Totaal'),
                    ]),

                TextColumn::make('aftrek_count')
                    ->label('Aftrek')
                    ->formatStateUsing(fn ($state) => $state ?? 0)
                    ->badge()
                    ->color('success')
                    ->suffix(' documenten'),

                TextColumn::make('aftrek_total')
                    ->label('BTW Aftrekbaar')
                    ->money('EUR')
                    ->color('success')
                    ->weight('bold')
                    ->formatStateUsing(fn ($state) => $state ?? 0)
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('EUR')
                            ->label('Totaal'),
                    ]),

                TextColumn::make('netto_btw')
                    ->label('Netto BTW')
                    ->state(function ($record) {
                        $aangifte = (float) ($record->aangifte_total ?? 0);
                        $aftrek = (float) ($record->aftrek_total ?? 0);
                        return $aangifte - $aftrek;
                    })
                    ->money('EUR')
                    ->color(function ($record) {
                        $aangifte = (float) ($record->aangifte_total ?? 0);
                        $aftrek = (float) ($record->aftrek_total ?? 0);
                        $netto = $aangifte - $aftrek;
                        return $netto < 0 ? 'success' : 'warning';
                    })
                    ->weight('bold')
                    ->summarize([
                        Tables\Columns\Summarizers\Summarizer::make()
                            ->label('Totaal Netto')
                            ->using(function ($query) {
                                $aangifteSum = $query->sum('aangifte_total') ?? 0;
                                $aftrekSum = $query->sum('aftrek_total') ?? 0;
                                return \Filament\Support\format_money($aangifteSum - $aftrekSum, 'EUR');
                            }),
                    ]),
            ])
            ->filters([
                SelectFilter::make('has_btw')
                    ->label('BTW Status')
                    ->options([
                        'with_aangifte' => 'Met Aangifte',
                        'with_aftrek' => 'Met Aftrek',
                        'with_both' => 'Met Beide',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            isset($data['value']) && $data['value'] === 'with_aangifte',
                            fn ($q) => $q->has('documents', '>', 0, function ($q) {
                                $q->where('status', 'approved')
                                    ->where('document_type', 'sales_invoice')
                                    ->where('is_paid', true);
                            })
                        )->when(
                            isset($data['value']) && $data['value'] === 'with_aftrek',
                            fn ($q) => $q->has('documents', '>', 0, function ($q) {
                                $q->where('status', 'approved')
                                    ->whereIn('document_type', ['purchase_invoice', 'receipt'])
                                    ->whereNotNull('amount_vat')
                                    ->where('amount_vat', '>', 0);
                            })
                        )->when(
                            isset($data['value']) && $data['value'] === 'with_both',
                            fn ($q) => $q->has('documents', '>', 0, function ($q) {
                                $q->where('status', 'approved')
                                    ->where('document_type', 'sales_invoice')
                                    ->where('is_paid', true);
                            })->has('documents', '>', 0, function ($q) {
                                $q->where('status', 'approved')
                                    ->whereIn('document_type', ['purchase_invoice', 'receipt'])
                                    ->whereNotNull('amount_vat')
                                    ->where('amount_vat', '>', 0);
                            })
                        );
                    }),
            ])
            ->actions([
                Action::make('view')
                    ->label('Bekijk Details')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->action(function ($record) {
                        $this->selectClient($record->id);
                    }),
            ])
            ->defaultSort('name')
            ->poll('30s')
            ->striped()
            ->paginated([25, 50, 100]);
    }

    protected function getDocumentsTable(Table $table): Table
    {
        if (!$this->selectedClientId) {
            return $table->query(Document::query()->whereRaw('1 = 0'));
        }

        $client = Client::find($this->selectedClientId);

        return $table
            ->query(
                Document::query()
                    ->where('client_id', $this->selectedClientId)
                    ->where('status', 'approved')
                    ->with(['client'])
            )
            ->heading("BTW Details: {$client->name}")
            ->description('Aangifte en aftrekbare BTW voor deze klant')
            ->groups([
                Group::make('document_type')
                    ->label('Document Type')
                    ->getTitleFromRecordUsing(function (Document $record): string {
                        if ($record->document_type === 'sales_invoice' && $record->is_paid) {
                            return '📤 BTW Verschuldigd (Verkoop)';
                        }
                        if (in_array($record->document_type, ['purchase_invoice', 'receipt'])) {
                            return '📥 BTW Aftrekbaar (Inkoop)';
                        }
                        return '📁 Overig';
                    })
                    ->collapsible(),
            ])
            ->defaultGroup('document_type')
            ->columns([
                TextColumn::make('original_filename')
                    ->label('Document')
                    ->searchable()
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
                    })
                    ->wrap(),

                BadgeColumn::make('document_type')
                    ->label('Type')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'receipt' => '🧾 Bon',
                        'purchase_invoice' => '📄 Inkoop',
                        'sales_invoice' => '🧑‍💼 Verkoop',
                        default => '📁 Overig',
                    })
                    ->colors([
                        'gray' => 'receipt',
                        'info' => 'purchase_invoice',
                        'warning' => 'sales_invoice',
                    ]),

                TextColumn::make('btw_type')
                    ->label('BTW Type')
                    ->state(function (Document $record): string {
                        if ($record->document_type === 'sales_invoice' && $record->is_paid) {
                            return 'Verschuldigd';
                        }
                        if (in_array($record->document_type, ['purchase_invoice', 'receipt'])) {
                            return 'Aftrekbaar';
                        }
                        return '—';
                    })
                    ->badge()
                    ->color(fn ($state): string => match($state) {
                        'Verschuldigd' => 'info',
                        'Aftrekbaar' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('vat_rubriek')
                    ->label('Rubriek')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('amount_excl')
                    ->label('Excl. BTW')
                    ->money('EUR')
                    ->sortable(),

                TextColumn::make('amount_vat')
                    ->label('BTW Bedrag')
                    ->money('EUR')
                    ->sortable()
                    ->color(fn (Document $record): string => 
                        ($record->document_type === 'sales_invoice' && $record->is_paid) 
                            ? 'info' 
                            : 'success'
                    )
                    ->weight('bold')
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('EUR')
                            ->label('Totaal BTW'),
                    ]),

                TextColumn::make('vat_rate')
                    ->label('BTW %')
                    ->suffix('%')
                    ->badge()
                    ->color(fn (?string $state): string => match($state) {
                        '21' => 'warning',
                        '9' => 'info',
                        '0' => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('document_date')
                    ->label('Datum')
                    ->date('d-m-Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('btw_type')
                    ->label('BTW Type')
                    ->options([
                        'verschuldigd' => 'Verschuldigd',
                        'aftrekbaar' => 'Aftrekbaar',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            isset($data['value']) && $data['value'] === 'verschuldigd',
                            fn ($q) => $q->where('document_type', 'sales_invoice')->where('is_paid', true)
                        )->when(
                            isset($data['value']) && $data['value'] === 'aftrekbaar',
                            fn ($q) => $q->whereIn('document_type', ['purchase_invoice', 'receipt'])
                        );
                    }),

                SelectFilter::make('document_type')
                    ->label('Document Type')
                    ->options([
                        'receipt' => '🧾 Bonnetje',
                        'purchase_invoice' => '📄 Inkoopfactuur',
                        'sales_invoice' => '🧑‍💼 Verkoopfactuur',
                    ])
                    ->multiple(),
            ])
            ->headerActions([
                Action::make('back')
                    ->label('Terug naar Klanten')
                    ->icon('heroicon-o-arrow-left')
                    ->color('gray')
                    ->action(fn () => $this->backToClients()),
            ])
            ->defaultSort('document_date', 'desc')
            ->poll('30s')
            ->striped()
            ->paginated([25, 50, 100]);
    }

    /**
     * Get summary statistics for all clients
     */
    public function getSummary(): array
    {
        // BTW Verschuldigd (only paid sales invoices)
        $verschuldigd = Document::where('status', 'approved')
            ->where('document_type', 'sales_invoice')
            ->where('is_paid', true)
            ->whereNotNull('amount_vat')
            ->sum('amount_vat') ?? 0;

        // BTW Aftrekbaar
        $aftrekbaar = Document::where('status', 'approved')
            ->whereIn('document_type', ['purchase_invoice', 'receipt'])
            ->whereNotNull('amount_vat')
            ->where('amount_vat', '>', 0)
            ->sum('amount_vat') ?? 0;

        // Netto BTW
        $netto = $verschuldigd - $aftrekbaar;

        return [
            'verschuldigd' => round($verschuldigd, 2),
            'aftrekbaar' => round($aftrekbaar, 2),
            'netto' => round($netto, 2),
            'is_refund' => $netto < 0,
        ];
    }

    public function getTitle(): string
    {
        return 'BTW Aangifte & Aftrek';
    }

    public function getHeading(): string
    {
        return '📊 BTW Aangifte & Aftrek';
    }
}
