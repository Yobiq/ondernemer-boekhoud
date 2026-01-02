<?php

namespace App\Filament\Widgets;

use App\Models\VatPeriod;
use Filament\Widgets\TableWidget;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Carbon\Carbon;

class TaxCalendarWidget extends TableWidget
{
    protected static ?string $heading = 'Aankomende BTW Deadlines';
    
    protected static ?int $sort = 10;
    
    protected int | string | array $columnSpan = 'full';
    
    public function table(Table $table): Table
    {
        // Calculate next quarter deadline (typically end of month after quarter)
        $now = now();
        $currentQuarter = ceil($now->month / 3);
        $currentYear = $now->year;
        
        // BTW deadlines are typically end of month after quarter end
        // Q1 (Jan-Mar) -> deadline end of April
        // Q2 (Apr-Jun) -> deadline end of July
        // Q3 (Jul-Sep) -> deadline end of October
        // Q4 (Oct-Dec) -> deadline end of January next year
        
        $upcomingPeriods = VatPeriod::where('status', 'open')
            ->orWhere('status', 'voorbereid')
            ->with('client')
            ->orderBy('period_end', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($period) {
                // Calculate deadline (typically 1 month after period end)
                $deadline = $period->period_end->copy()->addMonth()->endOfMonth();
                $daysUntil = now()->diffInDays($deadline, false);
                
                return [
                    'period' => $period,
                    'deadline' => $deadline,
                    'days_until' => $daysUntil,
                    'urgency' => $daysUntil < 7 ? 'urgent' : ($daysUntil < 30 ? 'soon' : 'normal'),
                ];
            })
            ->sortBy('days_until');
        
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
                    ->searchable(),
                
                TextColumn::make('period_string')
                    ->label('Periode')
                    ->getStateUsing(fn ($record) => $record->period_string),
                
                TextColumn::make('deadline')
                    ->label('Deadline')
                    ->getStateUsing(function ($record) {
                        $deadline = $record->period_end->copy()->addMonth()->endOfMonth();
                        return $deadline->format('d-m-Y');
                    })
                    ->badge()
                    ->color(function ($record) {
                        $deadline = $record->period_end->copy()->addMonth()->endOfMonth();
                        $daysUntil = now()->diffInDays($deadline, false);
                        
                        if ($daysUntil < 7) return 'danger';
                        if ($daysUntil < 30) return 'warning';
                        return 'success';
                    }),
                
                TextColumn::make('days_until')
                    ->label('Dagen')
                    ->getStateUsing(function ($record) {
                        $deadline = $record->period_end->copy()->addMonth()->endOfMonth();
                        $days = now()->diffInDays($deadline, false);
                        return $days >= 0 ? "{$days} dagen" : "Verlopen";
                    }),
                
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'open' => 'warning',
                        'voorbereid' => 'info',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('period_end', 'asc')
            ->paginated([5, 10]);
    }
}

