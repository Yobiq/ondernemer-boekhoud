<?php

namespace App\Filament\Pages;

use App\Models\VatPeriod;
use App\Models\Client;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Carbon\Carbon;

class TaxCalendar extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;
    
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    
    protected static string $view = 'filament.pages.tax-calendar';
    
    protected static ?string $navigationLabel = 'BTW Kalender';
    
    protected static ?string $navigationGroup = 'Financieel';
    
    protected static ?int $navigationSort = 15;
    
    protected static bool $shouldRegisterNavigation = false;
    
    protected static ?string $title = 'BTW Deadlines & Kalender';
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                VatPeriod::query()
                    ->where(function ($query) {
                        $query->where('status', 'open')
                            ->orWhere('status', 'voorbereid');
                    })
                    ->with('client')
                    ->orderBy('period_end', 'asc')
            )
            ->columns([
                TextColumn::make('client.name')
                    ->label('Klant')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('period_string')
                    ->label('Periode')
                    ->getStateUsing(fn ($record) => $record->period_string)
                    ->sortable(),
                
                TextColumn::make('period_end')
                    ->label('Periode Eind')
                    ->date('d-m-Y')
                    ->sortable(),
                
                TextColumn::make('deadline')
                    ->label('Deadline')
                    ->getStateUsing(function ($record) {
                        // BTW deadline is typically end of month after period end
                        $deadline = $record->period_end->copy()->addMonth()->endOfMonth();
                        return $deadline->format('d-m-Y');
                    })
                    ->badge()
                    ->color(function ($record) {
                        $deadline = $record->period_end->copy()->addMonth()->endOfMonth();
                        $daysUntil = now()->diffInDays($deadline, false);
                        
                        if ($daysUntil < 0) return 'danger'; // Overdue
                        if ($daysUntil < 7) return 'danger'; // Urgent
                        if ($daysUntil < 30) return 'warning'; // Soon
                        return 'success'; // OK
                    })
                    ->sortable(),
                
                TextColumn::make('days_until_deadline')
                    ->label('Dagen')
                    ->getStateUsing(function ($record) {
                        $deadline = $record->period_end->copy()->addMonth()->endOfMonth();
                        $days = now()->diffInDays($deadline, false);
                        
                        if ($days < 0) {
                            return "Verlopen (" . abs($days) . " dagen)";
                        }
                        return "{$days} dagen";
                    })
                    ->badge()
                    ->color(function ($record) {
                        $deadline = $record->period_end->copy()->addMonth()->endOfMonth();
                        $days = now()->diffInDays($deadline, false);
                        
                        if ($days < 0) return 'danger';
                        if ($days < 7) return 'danger';
                        if ($days < 30) return 'warning';
                        return 'success';
                    }),
                
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'open' => 'warning',
                        'voorbereid' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),
                
                TextColumn::make('documents_count')
                    ->label('Documenten')
                    ->counts('documents')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'open' => 'Open',
                        'voorbereid' => 'Voorbereid',
                    ]),
                
                Tables\Filters\Filter::make('urgency')
                    ->label('Urgentie')
                    ->form([
                        \Filament\Forms\Components\Select::make('level')
                            ->label('Urgentie Niveau')
                            ->options([
                                'overdue' => 'Verlopen',
                                'urgent' => 'Urgent (< 7 dagen)',
                                'soon' => 'Binnenkort (< 30 dagen)',
                                'ok' => 'OK (> 30 dagen)',
                            ]),
                    ])
                    ->query(function ($query, array $data) {
                        if (!isset($data['level'])) {
                            return $query;
                        }
                        
                        return $query->get()->filter(function ($period) use ($data) {
                            $deadline = $period->period_end->copy()->addMonth()->endOfMonth();
                            $days = now()->diffInDays($deadline, false);
                            
                            return match($data['level']) {
                                'overdue' => $days < 0,
                                'urgent' => $days >= 0 && $days < 7,
                                'soon' => $days >= 7 && $days < 30,
                                'ok' => $days >= 30,
                                default => true,
                            };
                        });
                    }),
            ])
            ->actions([
                Action::make('create_period')
                    ->label('Nieuwe Periode')
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->form([
                        \Filament\Forms\Components\Select::make('client_id')
                            ->label('Klant')
                            ->options(Client::pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        
                        \Filament\Forms\Components\DatePicker::make('period_start')
                            ->label('Start Datum')
                            ->default(now()->startOfQuarter())
                            ->required(),
                        
                        \Filament\Forms\Components\DatePicker::make('period_end')
                            ->label('Eind Datum')
                            ->default(now()->endOfQuarter())
                            ->required()
                            ->after('period_start'),
                    ])
                    ->action(function (array $data) {
                        $period = VatPeriod::create([
                            'client_id' => $data['client_id'],
                            'period_start' => $data['period_start'],
                            'period_end' => $data['period_end'],
                            'status' => 'open',
                            'year' => Carbon::parse($data['period_start'])->year,
                            'quarter' => ceil(Carbon::parse($data['period_start'])->month / 3),
                            'month' => Carbon::parse($data['period_start'])->month,
                        ]);
                        
                        Notification::make()
                            ->title('Periode Aangemaakt')
                            ->body("Periode voor {$period->client->name} is aangemaakt")
                            ->success()
                            ->send();
                    }),
                
                Action::make('quick_submit')
                    ->label('Snel Indienen')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'voorbereid')
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'ingediend',
                            'submitted_by' => auth()->id(),
                            'submitted_at' => now(),
                        ]);
                        
                        Notification::make()
                            ->title('Periode Ingediend')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('period_end', 'asc');
    }
}

