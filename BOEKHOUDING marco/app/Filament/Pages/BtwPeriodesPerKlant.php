<?php

namespace App\Filament\Pages;

use App\Models\VatPeriod;
use App\Models\Client;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class BtwPeriodesPerKlant extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    
    protected static ?string $navigationLabel = 'BTW Periodes per Klant';
    
    protected static ?string $navigationGroup = 'Workflow';
    
    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.btw-periodes-per-klant';

    public function table(Table $table): Table
    {
        return $table
            ->query(VatPeriod::query()->with(['client', 'preparedBy', 'submittedBy', 'closedBy']))
            ->columns([
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Klant')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('period_string')
                    ->label('Periode')
                    ->getStateUsing(fn (VatPeriod $record) => $record->period_string)
                    ->searchable()
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
                
                Tables\Columns\TextColumn::make('documents_count')
                    ->label('Documenten')
                    ->counts('documents')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Ingediend Op')
                    ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('d-m-Y H:i') : '—')
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('submittedBy.name')
                    ->label('Ingediend Door')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('client_id')
                    ->label('Klant')
                    ->relationship('client', 'name')
                    ->searchable()
                    ->preload(),
                
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'open' => '⏳ Open',
                        'voorbereid' => '🟡 Voorbereid',
                        'ingediend' => '📤 Ingediend',
                        'afgesloten' => '🔒 Afgesloten',
                    ])
                    ->multiple(),
                
                SelectFilter::make('year')
                    ->label('Jaar')
                    ->options(function () {
                        return VatPeriod::selectRaw('DISTINCT year')
                            ->whereNotNull('year')
                            ->orderBy('year', 'desc')
                            ->pluck('year', 'year')
                            ->toArray();
                    }),
            ])
            ->groups([
                Group::make('client.name')
                    ->label('Klant')
                    ->collapsible(),
                Group::make('status')
                    ->label('Status')
                    ->collapsible(),
            ])
            ->defaultGroup('client.name')
            ->defaultSort('period_start', 'desc')
            ->actions([
                Action::make('open_workflow')
                    ->label('Open Workflow')
                    ->icon('heroicon-o-arrow-right')
                    ->color('primary')
                    ->url(fn (VatPeriod $record) => \App\Filament\Pages\ClientTaxWorkflow::getUrl(['client' => $record->client_id, 'period' => $record->id])),
                
                Action::make('view')
                    ->label('Bekijken')
                    ->icon('heroicon-o-eye')
                    ->url(fn (VatPeriod $record) => \App\Filament\Resources\VatPeriodResource::getUrl('view', ['record' => $record])),
                
                Action::make('export_excel')
                    ->label('📊 Export Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (VatPeriod $record) {
                        try {
                            $excelService = app(\App\Services\ExcelExportService::class);
                            return $excelService->exportVatPeriod($record);
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Fout bij Excel export')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn (VatPeriod $record) => $record->status !== 'open'),
                
                Action::make('export_pdf')
                    ->label('📄 Export PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->action(function (VatPeriod $record) {
                        try {
                            $pdfService = app(\App\Services\VatPeriodPdfService::class);
                            return $pdfService->download($record);
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Fout bij PDF genereren')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn (VatPeriod $record) => $record->status !== 'open'),
            ])
            ->bulkActions([
                // No bulk actions
            ]);
    }
}

