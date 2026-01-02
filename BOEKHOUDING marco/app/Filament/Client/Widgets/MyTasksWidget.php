<?php

namespace App\Filament\Client\Widgets;

use App\Models\Task;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class MyTasksWidget extends BaseWidget
{
    protected int | string | array $columnSpan = ['default' => 'full', 'md' => 'full', 'lg' => 'full'];
    protected static ?int $sort = 1;
    
    public static function canView(): bool
    {
        if (!Auth::check()) {
            return false;
        }
        
        $user = Auth::user();
        $clientId = $user->client_id ?? null;
        
        if (!$clientId) {
            return false;
        }
        
        return Task::where('client_id', $clientId)->where('status', 'open')->exists();
    }
    
    public function table(Table $table): Table
    {
        $clientId = Auth::user()->client_id ?? null;
        $hasTasks = Task::where('client_id', $clientId)->where('status', 'open')->exists();
        
        if (!$hasTasks) {
            // Return empty query to prevent widget from showing
            return $table->query(\App\Models\Task::query()->whereRaw('1 = 0'));
        }
        
        return $table
            ->heading('📋 Mijn Openstaande Taken')
            ->query(
                Task::query()
                    ->where('client_id', $clientId)
                    ->where('status', 'open')
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'missing_document' => 'warning',
                        'unreadable' => 'danger',
                        'clarification' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'missing_document' => '📄 Ontbrekend Document',
                        'unreadable' => '❌ Onleesbaar',
                        'clarification' => '❓ Verduidelijking',
                        default => $state,
                    }),
                
                Tables\Columns\TextColumn::make('description')
                    ->label('Omschrijving')
                    ->wrap()
                    ->limit(100),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Aangemaakt')
                    ->since()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('resolve')
                    ->label('Upload Antwoord')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('success')
                    ->url(fn (): string => '/klanten/document-upload'),
            ])
            ->emptyStateHeading('Geen openstaande taken')
            ->emptyStateDescription('U bent helemaal bij. Alles is verwerkt!')
            ->emptyStateIcon(null)
            ->deferLoading(); // Defer loading to prevent JavaScript errors
    }
}

