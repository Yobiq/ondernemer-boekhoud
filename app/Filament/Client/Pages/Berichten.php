<?php

namespace App\Filament\Client\Pages;

use App\Models\Task;
use App\Models\Document;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Livewire\Attributes\Computed;

class Berichten extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationLabel = '💬 Berichten';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.client.pages.berichten';
    protected static ?string $navigationGroup = 'Documenten';

    public $viewMode = 'cards'; // 'cards' or 'table'
    public string $activeTab = 'all'; // 'all', 'unread', 'replied', 'admin_replies'

    public function mount(): void
    {
        // Initialize activeTab if not set
        if (!isset($this->activeTab)) {
            $this->activeTab = 'all';
        }
    }

    #[Computed]
    public function getTasksProperty()
    {
        $clientId = Auth::user()->client_id;
        
        // Get the base query and modify it for our card view
        $query = Task::query()
            ->where('client_id', $clientId)
            ->with(['document'])
            ->latest('created_at');

        // Apply tab filter FIRST (highest priority)
        if ($this->activeTab === 'unread') {
            $query->whereNull('read_at');
        } elseif ($this->activeTab === 'replied') {
            $query->whereNotNull('client_reply');
        } elseif ($this->activeTab === 'admin_replies') {
            $query->whereNotNull('admin_reply');
        }
        // 'all' tab shows everything, no filter needed

        // Get filter values safely - check if tableFilters is an array
        // tableFilters might be a Table object, so we need to check
        $filters = [];
        if (isset($this->tableFilters) && is_array($this->tableFilters)) {
            $filters = $this->tableFilters;
        } elseif (property_exists($this, 'tableFilters') && is_array($this->tableFilters)) {
            $filters = $this->tableFilters;
        }
        
        // Apply unread filter
        if (isset($filters['unread']) && ($filters['unread'] === true || (is_array($filters['unread']) && isset($filters['unread']['value']) && $filters['unread']['value'] === true))) {
            $query->whereNull('read_at');
        }

        // Apply status filter
        $statusFilter = $filters['status'] ?? null;
        if ($statusFilter) {
            $statusValues = $this->extractFilterValues($statusFilter);
            if (!empty($statusValues)) {
                $query->whereIn('status', $statusValues);
            }
        }

        // Apply type filter
        $typeFilter = $filters['type'] ?? null;
        if ($typeFilter) {
            $typeValues = $this->extractFilterValues($typeFilter);
            if (!empty($typeValues)) {
                $query->whereIn('type', $typeValues);
            }
        }

        // Apply priority filter
        $priorityFilter = $filters['priority'] ?? null;
        if ($priorityFilter) {
            $priorityValues = $this->extractFilterValues($priorityFilter);
            if (!empty($priorityValues)) {
                $query->whereIn('priority', $priorityValues);
            }
        }

        // Apply has_reply filter
        if (isset($filters['has_reply']) && ($filters['has_reply'] === true || (is_array($filters['has_reply']) && isset($filters['has_reply']['value']) && $filters['has_reply']['value'] === true))) {
            $query->whereNotNull('client_reply');
        }

        // Apply deadline_urgent filter
        if (isset($filters['deadline_urgent']) && ($filters['deadline_urgent'] === true || (is_array($filters['deadline_urgent']) && isset($filters['deadline_urgent']['value']) && $filters['deadline_urgent']['value'] === true))) {
            $query->whereNotNull('deadline')
                ->where('deadline', '<=', now()->addDays(3))
                ->where('status', 'open');
        }

        // Apply deadline_overdue filter
        if (isset($filters['deadline_overdue']) && ($filters['deadline_overdue'] === true || (is_array($filters['deadline_overdue']) && isset($filters['deadline_overdue']['value']) && $filters['deadline_overdue']['value'] === true))) {
            $query->whereNotNull('deadline')
                ->where('deadline', '<', now())
                ->where('status', 'open');
        }

        // Apply pagination manually for card view
        $perPage = $this->tableRecordsPerPage ?? 10;
        $currentPage = request()->get('page', 1);
        
        return $query->paginate($perPage, ['*'], 'page', $currentPage);
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        $clientId = Auth::user()->client_id;

        return $table
            ->query(
                Task::query()
                    ->where('client_id', $clientId)
                    ->with(['document'])
                    ->latest('created_at')
            )
            ->heading('')
            ->description('')
            ->columns([
                // Minimal columns for table view (hidden by default)
                TextColumn::make('id')->hidden(),
            ])
            ->filters([
                Filter::make('unread')
                    ->label('📬 Ongelezen')
                    ->query(fn ($query) => $query->whereNull('read_at'))
                    ->toggle()
                    ->default(),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'open' => 'Open',
                        'resolved' => '✅ Afgehandeld',
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

                SelectFilter::make('priority')
                    ->label('Prioriteit')
                    ->options([
                        'urgent' => '⚠️ Urgent',
                        'high' => 'Hoog',
                        'normal' => 'Normaal',
                        'low' => 'Laag',
                    ])
                    ->multiple(),

                Filter::make('has_reply')
                    ->label('💬 Met Reactie')
                    ->query(fn ($query) => $query->whereNotNull('client_reply'))
                    ->toggle(),

                Filter::make('deadline_urgent')
                    ->label('⏰ Urgente Deadlines')
                    ->query(fn ($query) => 
                        $query->whereNotNull('deadline')
                            ->where('deadline', '<=', now()->addDays(3))
                            ->where('status', 'open')
                    )
                    ->toggle(),

                Filter::make('deadline_overdue')
                    ->label('🚨 Verlopen Deadlines')
                    ->query(fn ($query) => 
                        $query->whereNotNull('deadline')
                            ->where('deadline', '<', now())
                            ->where('status', 'open')
                    )
                    ->toggle(),
            ])
            ->actions([
                Action::make('view')
                    ->label('Bekijk')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->modalHeading(function (?Task $record) {
                        if (!$record) return 'Bericht Details';
                        return 'Bericht Details' . ($record->isUnread() ? ' (Nieuw)' : '');
                    })
                    ->modalContent(function (?Task $record) {
                        if (!$record) return null;
                        if ($record->isUnread()) {
                            $record->markAsRead();
                        }
                        return view('filament.client.components.task-detail-enhanced', [
                            'task' => $record,
                        ]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Sluiten')
                    ->after(function (?Task $record) {
                        if ($record && $record->isUnread()) {
                            $record->markAsRead();
                        }
                    }),

                Action::make('reply')
                    ->label('Beantwoorden')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('info')
                    ->visible(function (?Task $record) {
                        return $record && $record->status === 'open';
                    })
                    ->form([
                        Section::make('Uw Reactie')
                            ->schema([
                                Textarea::make('reply')
                                    ->label('Bericht')
                                    ->required()
                                    ->rows(6)
                                    ->placeholder('Typ uw reactie hier...')
                                    ->helperText('Uw reactie wordt naar uw boekhouder gestuurd'),
                            ]),
                    ])
                    ->action(function (?Task $record, array $data) {
                        if (!$record) return;
                        
                        $record->update([
                            'client_reply' => $data['reply'],
                            'replied_at' => now(),
                        ]);
                        
                        Notification::make()
                            ->title('Reactie Verzonden')
                            ->body('Uw reactie is verzonden naar uw boekhouder.')
                            ->success()
                            ->send();
                    }),

                Action::make('mark_read')
                    ->label('Markeer als Gelezen')
                    ->icon('heroicon-o-check')
                    ->color('gray')
                    ->visible(function (?Task $record) {
                        return $record && $record->isUnread();
                    })
                    ->action(function (?Task $record) {
                        if (!$record) return;
                        $record->markAsRead();
                        
                        Notification::make()
                            ->title('Gemarkeerd als Gelezen')
                            ->success()
                            ->send();
                    }),

                Action::make('mark_unread')
                    ->label('Markeer als Ongelezen')
                    ->icon('heroicon-o-envelope')
                    ->color('gray')
                    ->visible(function (?Task $record) {
                        return $record && $record->isRead();
                    })
                    ->action(function (?Task $record) {
                        if (!$record) return;
                        $record->update(['read_at' => null]);
                        
                        Notification::make()
                            ->title('Gemarkeerd als Ongelezen')
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
                \Filament\Tables\Actions\BulkAction::make('mark_read')
                    ->label('📬 Markeer als Gelezen')
                    ->icon('heroicon-o-check')
                    ->color('primary')
                    ->action(function ($records) {
                        $count = $records->whereNull('read_at')->count();
                        $records->whereNull('read_at')->each->markAsRead();
                        
                        Notification::make()
                            ->title('Gemarkeerd als Gelezen')
                            ->body("{$count} bericht(en) gemarkeerd als gelezen.")
                            ->success()
                            ->send();
                    }),

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
            ->defaultSort('created_at', 'desc')
            ->poll('30s')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->emptyStateHeading('Geen berichten')
            ->emptyStateDescription('U heeft nog geen berichten ontvangen van uw boekhouder.')
            ->emptyStateIcon('heroicon-o-chat-bubble-left-right');
    }

    protected function getRelatedDocumentsCount(?Task $task): int
    {
        if (!$task) return 0;
        
        $description = $task->description ?? '';
        $docIds = [];
        
        if ($task->document_id) {
            $docIds[] = $task->document_id;
        }
        
        preg_match_all('/Gerelateerde documenten:?\s*([^\n]+)/i', $description, $matches);
        if (!empty($matches[1])) {
            $docNames = explode(',', $matches[1][0]);
            $docNames = array_map('trim', $docNames);
            
            $clientId = Auth::user()->client_id;
            $foundDocs = Document::where('client_id', $clientId)
                ->whereIn('original_filename', $docNames)
                ->pluck('id')
                ->toArray();
            
            $docIds = array_merge($docIds, $foundDocs);
        }
        
        return count(array_unique($docIds));
    }

    protected function getRelatedDocuments(?Task $task)
    {
        if (!$task) return collect();
        
        $docIds = [];
        
        if ($task->document_id) {
            $docIds[] = $task->document_id;
        }
        
        $description = $task->description ?? '';
        preg_match_all('/Gerelateerde documenten:?\s*([^\n]+)/i', $description, $matches);
        if (!empty($matches[1])) {
            $docNames = explode(',', $matches[1][0]);
            $docNames = array_map('trim', $docNames);
            
            $clientId = Auth::user()->client_id;
            $foundDocs = Document::where('client_id', $clientId)
                ->whereIn('original_filename', $docNames)
                ->pluck('id')
                ->toArray();
            
            $docIds = array_merge($docIds, $foundDocs);
        }
        
        if (empty($docIds)) {
            return collect();
        }
        
        return Document::whereIn('id', array_unique($docIds))->get();
    }

    /**
     * Extract filter values from various filter formats
     * Handles nested arrays and ensures flat array output
     */
    protected function extractFilterValues($filter): array
    {
        if (!is_array($filter)) {
            return [];
        }

        // If it has a 'value' key, use that
        if (isset($filter['value'])) {
            $values = $filter['value'];
        } else {
            $values = $filter;
        }

        // Ensure it's an array
        if (!is_array($values)) {
            return [];
        }

        // Flatten nested arrays and filter out non-scalar values
        $flatValues = [];
        foreach ($values as $value) {
            if (is_scalar($value)) {
                $flatValues[] = $value;
            } elseif (is_array($value)) {
                // Recursively flatten nested arrays
                $flatValues = array_merge($flatValues, array_filter($value, 'is_scalar'));
            }
        }

        return array_values(array_unique($flatValues));
    }

    public function markAsRead($taskId): void
    {
        $task = Task::find($taskId);
        if ($task && $task->isUnread()) {
            $task->markAsRead();
            Notification::make()
                ->title('Gemarkeerd als Gelezen')
                ->success()
                ->send();
        }
    }

    public function resolveTask($taskId): void
    {
        $task = Task::find($taskId);
        if ($task && $task->status === 'open') {
            $task->update(['status' => 'resolved']);
            Notification::make()
                ->title('Taak Afgehandeld')
                ->body('De taak is gemarkeerd als afgehandeld.')
                ->success()
                ->send();
        }
    }

    public function replyToTask($taskId): void
    {
        $task = Task::find($taskId);
        if (!$task || $task->status !== 'open') {
            return;
        }

        $this->validate([
            'replyText' => 'required|string|min:3',
        ]);

        $task->update([
            'client_reply' => $this->replyText,
            'replied_at' => now(),
        ]);

        $this->replyText = '';

        Notification::make()
            ->title('Reactie Verzonden')
            ->body('Uw reactie is verzonden naar uw boekhouder.')
            ->success()
            ->send();
    }

    public $replyText = '';

    public function getTitle(): string
    {
        return 'Berichten';
    }

    public function getHeading(): string
    {
        return 'Berichten';
    }

    public function getSubheading(): ?string
    {
        $clientId = Auth::user()->client_id;
        $openTasks = Task::where('client_id', $clientId)
            ->where('status', 'open')
            ->count();
        $unreadTasks = Task::where('client_id', $clientId)
            ->whereNull('read_at')
            ->count();
        
        if ($unreadTasks > 0) {
            return "{$unreadTasks} nieuw bericht" . ($unreadTasks > 1 ? 'en' : '');
        } elseif ($openTasks > 0) {
            return "{$openTasks} open" . ($openTasks > 1 ? 'e' : '') . " taak" . ($openTasks > 1 ? 'en' : '');
        }
        
        return 'Communicatie, taken en vragen van uw boekhouder';
    }
}
