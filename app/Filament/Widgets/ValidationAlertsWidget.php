<?php

namespace App\Filament\Widgets;

use App\Models\Document;
use Filament\Widgets\TableWidget;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ValidationAlertsWidget extends TableWidget
{
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 4;
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Document::query()
                    ->where('status', 'review_required')
                    ->orWhereNull('vat_code')
                    ->orWhereNull('vat_rubriek')
                    ->with('client')
                    ->latest()
            )
            ->columns([
                TextColumn::make('client.name')
                    ->label('Klant')
                    ->searchable(),
                
                TextColumn::make('original_filename')
                    ->label('Bestand')
                    ->searchable()
                    ->limit(30),
                
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'review_required' => 'warning',
                        default => 'gray',
                    }),
                
                TextColumn::make('review_required_reason')
                    ->label('Reden')
                    ->limit(50),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([5, 10]);
    }
}

