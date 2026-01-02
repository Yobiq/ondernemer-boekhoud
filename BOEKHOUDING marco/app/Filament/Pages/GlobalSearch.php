<?php

namespace App\Filament\Pages;

use App\Models\Client;
use App\Models\Document;
use App\Models\VatPeriod;
use App\Models\Task;
use Filament\Pages\Page;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

class GlobalSearch extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass';
    protected static ?string $navigationLabel = '🔍 Zoeken';
    protected static ?string $navigationGroup = null;
    protected static ?int $navigationSort = 99;
    protected static string $view = 'filament.pages.global-search';

    public array $data = [];
    protected Collection $results;
    protected int $totalResults = 0;

    public function mount(): void
    {
        $this->data = ['searchQuery' => ''];
        $this->results = collect();
        $this->totalResults = 0;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('searchQuery')
                    ->label('Zoeken')
                    ->placeholder('Zoek op klant, document, BTW periode, taak...')
                    ->live()
                    ->debounce(300)
                    ->suffixIcon('heroicon-o-magnifying-glass')
                    ->extraInputAttributes(['class' => 'text-lg'])
                    ->afterStateUpdated(fn () => $this->performSearch()),
            ])
            ->statePath('data');
    }

    public function getSearchQueryProperty(): string
    {
        return $this->data['searchQuery'] ?? '';
    }

    public function getResultsProperty(): Collection
    {
        return $this->results ?? collect();
    }

    public function getTotalResultsProperty(): int
    {
        return $this->totalResults ?? 0;
    }

    public function performSearch(): void
    {
        $query = $this->data['searchQuery'] ?? '';
        
        if (empty($query)) {
            $this->results = collect();
            $this->totalResults = 0;
            return;
        }

        $results = collect();

        // Search Clients
        $clients = Client::where('name', 'like', "%{$query}%")
            ->orWhere('company_name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->orWhere('vat_number', 'like', "%{$query}%")
            ->limit(10)
            ->get()
            ->map(fn($client) => [
                'type' => 'client',
                'id' => $client->id,
                'title' => $client->name,
                'subtitle' => $client->company_name ?: $client->email,
                'url' => route('filament.admin.resources.clients.edit', $client->id),
                'icon' => 'heroicon-o-user',
                'color' => 'primary',
            ]);

        // Search Documents
        $documents = Document::where('original_filename', 'like', "%{$query}%")
            ->orWhere('supplier_name', 'like', "%{$query}%")
            ->orWhere('supplier_vat', 'like', "%{$query}%")
            ->with('client')
            ->limit(20)
            ->get()
            ->map(fn($doc) => [
                'type' => 'document',
                'id' => $doc->id,
                'title' => $doc->original_filename,
                'subtitle' => ($doc->client?->name ?? 'Onbekend') . ' · €' . number_format($doc->amount_incl ?? 0, 2, ',', '.'),
                'url' => route('filament.admin.pages.document-review', ['document' => $doc->id]),
                'icon' => 'heroicon-o-document-text',
                'color' => match($doc->status) {
                    'approved' => 'success',
                    'review_required' => 'warning',
                    'pending' => 'gray',
                    default => 'info',
                },
            ]);

        // Search VAT Periods
        $vatPeriods = VatPeriod::where('period_string', 'like', "%{$query}%")
            ->with('client')
            ->limit(10)
            ->get()
            ->map(fn($period) => [
                'type' => 'vat_period',
                'id' => $period->id,
                'title' => $period->period_string,
                'subtitle' => ($period->client?->name ?? 'Onbekend') . ' · ' . ucfirst($period->status),
                'url' => route('filament.admin.resources.vat-periods.view', $period->id),
                'icon' => 'heroicon-o-calendar',
                'color' => 'info',
            ]);

        // Search Tasks
        $tasks = Task::where('description', 'like', "%{$query}%")
            ->with('client')
            ->limit(10)
            ->get()
            ->map(fn($task) => [
                'type' => 'task',
                'id' => $task->id,
                'title' => substr($task->description, 0, 50) . '...',
                'subtitle' => ($task->client?->name ?? 'Onbekend') . ' · ' . ucfirst($task->status),
                'url' => route('filament.admin.resources.tasks.edit', $task->id),
                'icon' => 'heroicon-o-clipboard-document-check',
                'color' => match($task->status) {
                    'open' => 'warning',
                    'resolved' => 'success',
                    default => 'gray',
                },
            ]);

        $results = $results
            ->merge($clients)
            ->merge($documents)
            ->merge($vatPeriods)
            ->merge($tasks);

        $this->results = $results;
        $this->totalResults = $results->count();
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('clear')
                ->label('Wissen')
                ->icon('heroicon-o-x-mark')
                ->color('gray')
                ->action(function () {
                    $this->data['searchQuery'] = '';
                    $this->results = collect();
                    $this->totalResults = 0;
                })
                ->visible(fn() => !empty($this->data['searchQuery'] ?? '')),
        ];
    }

    public function getTitle(): string
    {
        return 'Global Search';
    }

    public function getHeading(): string
    {
        return '🔍 Global Search';
    }
}
