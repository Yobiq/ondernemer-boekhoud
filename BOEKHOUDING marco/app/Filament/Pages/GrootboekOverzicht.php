<?php

namespace App\Filament\Pages;

use App\Models\LedgerAccount;
use App\Models\Document;
use App\Services\ExcelExportService;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\Action;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Summarizers\Sum;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class GrootboekOverzicht extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationLabel = '📊 Grootboek Overzicht';
    protected static ?string $navigationGroup = 'Overzichten';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.pages.grootboek-overzicht';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                LedgerAccount::query()
                    ->withCount(['documents' => function ($query) {
                        $query->where('status', 'approved');
                    }])
                    ->withSum(['documents' => function ($query) {
                        $query->where('status', 'approved');
                    }], 'amount_incl')
                    ->withSum(['documents' => function ($query) {
                        $query->where('status', 'approved');
                    }], 'amount_excl')
                    ->withSum(['documents' => function ($query) {
                        $query->where('status', 'approved');
                    }], 'amount_vat')
            )
            ->groups([
                Group::make('type')
                    ->label('Type')
                    ->collapsible(),
            ])
            ->defaultGroup('type')
            ->columns([
                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),
                
                TextColumn::make('description')
                    ->label('Omschrijving')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                
                BadgeColumn::make('type')
                    ->label('Type')
                    ->colors([
                        'info' => 'balans',
                        'success' => 'winst_verlies',
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'balans' => '⚖️ Balans',
                        'winst_verlies' => '📈 Winst & Verlies',
                        default => $state,
                    }),
                
                TextColumn::make('vat_default')
                    ->label('BTW Standaard')
                    ->formatStateUsing(fn ($state) => $state ? $state . '%' : '—')
                    ->badge()
                    ->colors([
                        'warning' => fn ($state) => $state == '21',
                        'info' => fn ($state) => $state == '9',
                        'gray' => fn ($state) => $state == '0' || $state == null,
                    ]),
                
                TextColumn::make('documents_count')
                    ->label('Aantal Documenten')
                    ->counts('documents', function ($query) {
                        $query->where('status', 'approved');
                    })
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Totaal'),
                    ]),
                
                TextColumn::make('documents_sum_amount_excl')
                    ->label('Totaal Excl. BTW')
                    ->money('EUR')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('EUR')
                            ->label('Totaal'),
                    ]),
                
                TextColumn::make('documents_sum_amount_vat')
                    ->label('Totaal BTW')
                    ->money('EUR')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('EUR')
                            ->label('Totaal'),
                    ]),
                
                TextColumn::make('documents_sum_amount_incl')
                    ->label('Totaal Incl. BTW')
                    ->money('EUR')
                    ->sortable()
                    ->weight('bold')
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('EUR')
                            ->label('Totaal'),
                    ]),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'balans' => '⚖️ Balans',
                        'winst_verlies' => '📈 Winst & Verlies',
                    ]),
                
                SelectFilter::make('vat_default')
                    ->label('BTW Standaard')
                    ->options([
                        '21' => '21%',
                        '9' => '9%',
                        '0' => '0%',
                        'verlegd' => 'Verlegd',
                    ]),
                
                SelectFilter::make('active')
                    ->label('Status')
                    ->options([
                        '1' => 'Actief',
                        '0' => 'Inactief',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (isset($data['value'])) {
                            $query->where('active', $data['value']);
                        }
                        return $query;
                    }),
            ])
            ->headerActions([
                Action::make('export_excel')
                    ->label('📊 Export Grootboek naar Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function () {
                        try {
                            $excelService = app(ExcelExportService::class);
                            return $excelService->exportGrootboek('grootboek-rekenschema-' . now()->format('Y-m-d') . '.xlsx');
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Fout bij Excel export')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->actions([
                Action::make('view_documents')
                    ->label('Bekijk Documenten')
                    ->icon('heroicon-o-eye')
                    ->url(fn (LedgerAccount $record) => \App\Filament\Resources\DocumentResource::getUrl('index', [
                        'tableFilters' => [
                            'ledger_account_id' => [
                                'value' => $record->id,
                            ],
                        ],
                    ]))
                    ->visible(fn (LedgerAccount $record) => $record->documents_count > 0),
            ])
            ->defaultSort('code', 'asc')
            ->poll('30s')
            ->striped();
    }
}

