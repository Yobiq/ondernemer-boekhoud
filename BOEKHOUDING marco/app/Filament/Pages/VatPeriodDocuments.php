<?php

namespace App\Filament\Pages;

use App\Models\VatPeriod;
use App\Models\Document;
use App\Services\VatCalculatorService;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class VatPeriodDocuments extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationLabel = 'BTW Periode Documenten';
    
    protected static ?string $navigationGroup = 'Financieel';
    
    protected static ?int $navigationSort = 11;
    
    protected static bool $shouldRegisterNavigation = false; // Hide from navigation, accessed via VatPeriod
    
    protected static string $view = 'filament.pages.vat-period-documents';
    
    public ?VatPeriod $period = null;
    public ?string $rubriek = null;

    public function mount(?int $period = null, ?string $rubriek = null): void
    {
        if ($period) {
            $this->period = VatPeriod::findOrFail($period);
        }
        $this->rubriek = $rubriek;
    }

    public function table(Table $table): Table
    {
        $vatCalculator = app(VatCalculatorService::class);

        return $table
            ->query(function () {
                if (!$this->period) {
                    return Document::query()->whereRaw('1 = 0'); // Empty query
                }

                $query = $this->period->documents()
                    ->where('status', 'approved')
                    ->with(['ledgerAccount', 'client']);

                if ($this->rubriek) {
                    $query->whereHas('vatPeriods', function ($q) {
                        $q->where('vat_periods.id', $this->period->id)
                            ->where('vat_period_documents.rubriek', $this->rubriek);
                    });
                }

                return $query;
            })
            ->columns([
                Tables\Columns\TextColumn::make('supplier_name')
                    ->label('Leverancier')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('document_date')
                    ->label('Datum')
                    ->date('d-m-Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('pivot.rubriek')
                    ->label('Rubriek')
                    ->getStateUsing(function ($record) use ($vatCalculator) {
                        $rubriek = $record->pivot->rubriek ?? $vatCalculator->calculateRubriek($record);
                        return $rubriek;
                    }),
                
                Tables\Columns\TextColumn::make('pivot.btw_code')
                    ->label('BTW Code')
                    ->default('—'),
                
                Tables\Columns\TextColumn::make('ledgerAccount.code')
                    ->label('Grootboek')
                    ->formatStateUsing(fn ($record) => $record->ledgerAccount 
                        ? "{$record->ledgerAccount->code} - {$record->ledgerAccount->description}"
                        : '—'),
                
                Tables\Columns\TextColumn::make('amount_excl')
                    ->label('Grondslag')
                    ->money('EUR')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('amount_vat')
                    ->label('BTW')
                    ->money('EUR')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('amount_incl')
                    ->label('Totaal')
                    ->money('EUR')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('rubriek')
                    ->label('Rubriek')
                    ->options([
                        '1a' => '1a - Hoog tarief',
                        '1b' => '1b - Laag tarief',
                        '1c' => '1c - Overige tarieven',
                        '2a' => '2a - Verleggingsregeling',
                        '3a' => '3a - Leveringen buitenland',
                        '3b' => '3b - Diensten buitenland',
                        '4a' => '4a - Voorbelasting (EU)',
                        '4b' => '4b - Voorbelasting (buiten EU)',
                        '5b' => '5b - Totaal',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value']) && $this->period) {
                            return $query->whereHas('vatPeriods', function ($q) use ($data) {
                                $q->where('vat_periods.id', $this->period->id)
                                    ->where('vat_period_documents.rubriek', $data['value']);
                            });
                        }
                        return $query;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('bekijk')
                    ->label('Bekijk')
                    ->icon('heroicon-o-eye')
                    ->modalContent(function (Document $record) {
                        return view('filament.components.document-detail-modal', [
                            'document' => $record,
                        ]);
                    })
                    ->modalHeading('Document Details'),
            ])
            ->defaultSort('document_date', 'desc');
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }
}

