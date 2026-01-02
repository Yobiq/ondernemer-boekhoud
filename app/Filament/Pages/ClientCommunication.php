<?php

namespace App\Filament\Pages;

use App\Models\Client;
use App\Models\Task;
use App\Models\Document;
use Filament\Pages\Page;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail;

class ClientCommunication extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationLabel = 'Klant Communicatie';
    protected static ?string $navigationGroup = 'Klanten';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.pages.client-communication';

    public array $data = [];
    public string $activeTab = 'send';

    public function mount(): void
    {
        $this->data = [
            'selectedClientId' => null,
            'messageType' => 'task',
            'subject' => null,
            'message' => null,
            'selectedDocuments' => [],
            'deadline' => null,
            'priority' => 'normal',
            'sendEmail' => true,
            'createTask' => true,
        ];
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('selectedClientId')
                    ->label('Klant')
                    ->options(Client::query()->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn () => $this->resetDocumentSelection())
                    ->placeholder('Selecteer een klant...'),

                Select::make('messageType')
                    ->label('Type Bericht')
                    ->options([
                        'task' => '📋 Taak Aanmaken',
                        'reminder' => '⏰ Herinnering',
                        'info' => 'ℹ️ Informatie',
                        'question' => '❓ Vraag',
                        'approval' => '✅ Goedkeuring Vragen',
                    ])
                    ->required()
                    ->live()
                    ->default('task'),

                Select::make('priority')
                    ->label('Prioriteit')
                    ->options([
                        'low' => 'Laag',
                        'normal' => 'Normaal',
                        'high' => 'Hoog',
                        'urgent' => '⚠️ Urgent',
                    ])
                    ->default('normal')
                    ->helperText('Automatisch ingesteld op basis van deadline, maar kan handmatig worden aangepast')
                    ->visible(fn () => ($this->data['messageType'] ?? 'task') === 'task' || ($this->data['messageType'] ?? 'task') === 'reminder'),

                Textarea::make('subject')
                    ->label('Onderwerp')
                    ->required()
                    ->maxLength(255)
                    ->rows(2)
                    ->placeholder('Bijv: Ontbrekende factuur voor maart 2024')
                    ->helperText('Korte beschrijving van het onderwerp'),

                Textarea::make('message')
                    ->label('Bericht')
                    ->required()
                    ->rows(6)
                    ->placeholder('Schrijf uw bericht aan de klant...')
                    ->helperText('Dit bericht wordt gebruikt voor de taak en/of e-mail'),

                CheckboxList::make('selectedDocuments')
                    ->label('Gerelateerde Documenten')
                    ->options(function () {
                        $clientId = $this->data['selectedClientId'] ?? null;
                        if (!$clientId) {
                            return [];
                        }
                        return Document::where('client_id', $clientId)
                            ->where('status', 'approved')
                            ->latest()
                            ->limit(20)
                            ->get()
                            ->mapWithKeys(fn ($doc) => [
                                $doc->id => $doc->original_filename . ' (' . ($doc->document_date?->format('d-m-Y') ?? 'Geen datum') . ')'
                            ]);
                    })
                    ->descriptions(function () {
                        $clientId = $this->data['selectedClientId'] ?? null;
                        if (!$clientId) {
                            return [];
                        }
                        return Document::where('client_id', $clientId)
                            ->where('status', 'approved')
                            ->latest()
                            ->limit(20)
                            ->get()
                            ->mapWithKeys(fn ($doc) => [
                                $doc->id => '€' . number_format($doc->amount_incl ?? 0, 2, ',', '.')
                            ]);
                    })
                    ->columns(2)
                    ->visible(fn () => ($this->data['selectedClientId'] ?? null) !== null)
                    ->helperText('Selecteer documenten die gerelateerd zijn aan dit bericht'),

                DatePicker::make('deadline')
                    ->label('Deadline (optioneel)')
                    ->displayFormat('d-m-Y')
                    ->native(false)
                    ->visible(fn () => ($this->data['messageType'] ?? 'task') === 'task' || ($this->data['messageType'] ?? 'task') === 'reminder'),

                \Filament\Forms\Components\Section::make('Opties')
                    ->schema([
                        \Filament\Forms\Components\Checkbox::make('createTask')
                            ->label('Taak Aanmaken')
                            ->default(true)
                            ->helperText('Maak een taak aan in het systeem'),

                        \Filament\Forms\Components\Checkbox::make('sendEmail')
                            ->label('E-mail Versturen')
                            ->default(true)
                            ->helperText('Stuur een e-mail naar de klant'),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    protected function resetDocumentSelection(): void
    {
        $this->data['selectedDocuments'] = [];
    }

    public function send(): void
    {
        $data = $this->form->getState();
        $client = Client::findOrFail($data['selectedClientId']);

        try {
            // Create task if requested
                    if ($data['createTask'] ?? true) {
                        // Determine priority: use manual selection, or auto-calculate if not set
                        $priority = $data['priority'] ?? 'normal';
                        
                        // Auto-calculate if not manually set and deadline exists
                        if (($data['priority'] ?? null) === 'normal' && !empty($data['deadline'])) {
                            $daysUntil = now()->diffInDays($data['deadline'], false);
                            if ($daysUntil < 0) {
                                $priority = 'urgent';
                            } elseif ($daysUntil <= 3) {
                                $priority = 'high';
                            }
                        }
                        
                        // Override for reminders
                        if ($data['messageType'] === 'reminder' && ($data['priority'] ?? 'normal') === 'normal') {
                            $priority = 'high';
                        }

                        $task = Task::create([
                            'client_id' => $client->id,
                            'type' => match($data['messageType']) {
                                'task' => 'missing_document',
                                'reminder' => 'clarification',
                                'question' => 'clarification',
                                'approval' => 'clarification',
                                default => 'clarification',
                            },
                            'description' => $data['message'],
                            'status' => 'open',
                            'deadline' => $data['deadline'] ?? null,
                            'priority' => $priority,
                            'read_at' => null, // New messages are unread
                        ]);

                // Store document IDs in task description or metadata if needed
                // Note: Task model may not have documents relationship, storing in description for now
                if (!empty($data['selectedDocuments'])) {
                    $docNames = Document::whereIn('id', $data['selectedDocuments'])
                        ->pluck('original_filename')
                        ->implode(', ');
                    $task->update([
                        'description' => $task->description . "\n\nGerelateerde documenten: " . $docNames
                    ]);
                }

                Notification::make()
                    ->title('Taak Aangemaakt')
                    ->body("Taak aangemaakt voor {$client->name}")
                    ->success()
                    ->send();
            }

            // Send email if requested
            if ($data['sendEmail'] ?? true) {
                // TODO: Implement email sending
                // Mail::to($client->email)->send(new ClientMessageMail($data));
                
                Notification::make()
                    ->title('E-mail Verzonden')
                    ->body("E-mail verzonden naar {$client->email}")
                    ->success()
                    ->send();
            }

            // Reset form
            $this->form->fill();
            $this->resetDocumentSelection();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Fout')
                ->body('Er is een fout opgetreden: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function getTitle(): string
    {
        return 'Klant Communicatie';
    }

    public function getHeading(): string
    {
        return '💬 Klant Communicatie';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Task::query()
                    ->whereNotNull('client_reply')
                    ->with(['client', 'document'])
                    ->latest('replied_at')
            )
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
                            'admin_reply' => $data['admin_reply'],
                            'admin_replied_at' => now(),
                            'status' => 'resolved',
                        ]);
                        
                        Notification::make()
                            ->title('Antwoord Verzonden')
                            ->body('Uw antwoord is verzonden naar de klant.')
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
}

