<?php

namespace App\Filament\Pages;

use App\Models\Task;
use App\Models\Client;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ClientReplies extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationLabel = '💬 Client Reacties';
    protected static ?string $navigationGroup = 'Klanten';
    protected static ?int $navigationSort = 4;
    protected static string $view = 'filament.pages.client-replies';
    
    // Hide from navigation - now a tab on ClientCommunication
    protected static bool $shouldRegisterNavigation = false;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Task::query()
                    ->whereNotNull('client_reply')
                    ->with(['client', 'document'])
                    ->latest('replied_at')
            )
            ->heading('💬 Client Reacties')
            ->description('Bekijk en beantwoord reacties van klanten op uw berichten')
            ->columns([
                TextColumn::make('client.name')
                    ->label('Klant')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(function (?string $state): string {
                        if (!$state) return '—';
                        return match($state) {
                            'missing_document' => '📋 Document',
                            'clarification' => '❓ Vraag',
                            'approval' => '✅ Goedkeuring',
                            'info' => 'ℹ️ Info',
                            'reminder' => '⏰ Herinnering',
                            default => ucfirst($state),
                        };
                    })
                    ->colors([
                        'warning' => 'missing_document',
                        'info' => 'clarification',
                        'success' => 'approval',
                        'primary' => 'info',
                        'gray' => 'reminder',
                    ]),

                TextColumn::make('description')
                    ->label('Origineel Bericht')
                    ->wrap()
                    ->limit(80)
                    ->tooltip(function (?Task $record) {
                        return $record?->description ?? '';
                    })
                    ->searchable(),

                TextColumn::make('client_reply')
                    ->label('Client Reactie')
                    ->wrap()
                    ->limit(100)
                    ->tooltip(function (?Task $record) {
                        return $record?->client_reply ?? '';
                    })
                    ->searchable()
                    ->weight('bold')
                    ->color('success'),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(function (?string $state): string {
                        if (!$state) return '—';
                        return match($state) {
                            'open' => 'Open',
                            'resolved' => '✅ Afgehandeld',
                            'closed' => 'Gesloten',
                            default => ucfirst($state),
                        };
                    })
                    ->colors([
                        'warning' => 'open',
                        'success' => 'resolved',
                        'gray' => 'closed',
                    ]),

                TextColumn::make('replied_at')
                    ->label('Beantwoord Op')
                    ->dateTime('d-m-Y H:i')
                    ->sortable()
                    ->since()
                    ->description(function (?Task $record) {
                        return $record?->replied_at?->format('d-m-Y H:i') ?? '';
                    }),

                TextColumn::make('created_at')
                    ->label('Origineel Bericht')
                    ->dateTime('d-m-Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'open' => 'Open',
                        'resolved' => 'Afgehandeld',
                        'closed' => 'Gesloten',
                    ])
                    ->multiple(),

                SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'missing_document' => '📋 Ontbrekend Document',
                        'clarification' => '❓ Vraag',
                        'approval' => '✅ Goedkeuring',
                        'info' => 'ℹ️ Informatie',
                        'reminder' => '⏰ Herinnering',
                    ])
                    ->multiple(),

                Filter::make('recent_replies')
                    ->label('Recente Reacties')
                    ->query(fn ($query) => $query->where('replied_at', '>=', now()->subDays(7)))
                    ->toggle(),

                Filter::make('unresolved')
                    ->label('Nog Niet Afgehandeld')
                    ->query(fn ($query) => $query->where('status', 'open'))
                    ->toggle()
                    ->default(),
            ])
            ->actions([
                Action::make('view')
                    ->label('Bekijk Volledig')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->modalHeading('Bericht & Reactie')
                    ->modalContent(function (?Task $record) {
                        if (!$record) return null;
                        return view('filament.components.client-reply-detail', [
                            'task' => $record,
                        ]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Sluiten')
                    ->modalWidth('3xl'),

                Action::make('reply')
                    ->label('Beantwoorden')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('info')
                    ->visible(function (?Task $record) {
                        return $record && $record->status === 'open';
                    })
                    ->form([
                        Section::make('Uw Antwoord')
                            ->schema([
                                Textarea::make('admin_reply')
                                    ->label('Bericht')
                                    ->required()
                                    ->rows(6)
                                    ->placeholder('Typ uw antwoord hier...')
                                    ->helperText('Dit bericht wordt naar de klant gestuurd'),
                            ]),
                    ])
                    ->action(function (?Task $record, array $data) {
                        if (!$record) return;
                        
                        // Update task with admin reply
                        $record->update([
                            'description' => $record->description . "\n\n--- ADMIN ANTWOORD ---\n" . $data['admin_reply'],
                            'status' => 'resolved',
                        ]);
                        
                        Notification::make()
                            ->title('Antwoord Verzonden')
                            ->body('Uw antwoord is toegevoegd aan het bericht.')
                            ->success()
                            ->send();
                    }),

                Action::make('resolve')
                    ->label('Afhandelen')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(function (?Task $record) {
                        return $record && $record->status === 'open';
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Taak Afhandelen')
                    ->modalDescription('Weet u zeker dat u deze taak als afgehandeld wilt markeren?')
                    ->action(function (?Task $record) {
                        if (!$record) return;
                        $record->update(['status' => 'resolved']);
                        
                        Notification::make()
                            ->title('Taak Afgehandeld')
                            ->body('De taak is gemarkeerd als afgehandeld.')
                            ->success()
                            ->send();
                    }),

                Action::make('reopen')
                    ->label('Heropen')
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->visible(function (?Task $record) {
                        return $record && $record->status === 'resolved';
                    })
                    ->action(function (?Task $record) {
                        if (!$record) return;
                        $record->update(['status' => 'open']);
                        
                        Notification::make()
                            ->title('Taak Heropend')
                            ->body('De taak is heropend.')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkAction::make('mark_resolved')
                    ->label('✅ Markeer als Afgehandeld')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($records) {
                        $count = $records->where('status', 'open')->count();
                        $records->where('status', 'open')->each->update(['status' => 'resolved']);
                        
                        Notification::make()
                            ->title('Taken Afgehandeld')
                            ->body("{$count} ta(a)k(en) gemarkeerd als afgehandeld.")
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('replied_at', 'desc')
            ->poll('30s')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->emptyStateHeading('Geen reacties')
            ->emptyStateDescription('Klanten hebben nog geen reacties gegeven op uw berichten.')
            ->emptyStateIcon('heroicon-o-chat-bubble-left-right');
    }

    public function getTitle(): string
    {
        return 'Client Reacties';
    }

    public function getHeading(): string
    {
        return '💬 Client Reacties';
    }
}
