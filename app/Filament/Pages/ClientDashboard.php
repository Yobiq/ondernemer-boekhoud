<?php

namespace App\Filament\Pages;

use App\Models\Client;
use App\Models\Document;
use App\Models\Task;
use App\Models\VatPeriod;
use App\Services\VatCalculatorService;
use Filament\Pages\Page;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ClientDashboard extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationLabel = '📊 Klant Dashboard';
    protected static ?string $navigationGroup = 'Klanten';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.pages.client-dashboard';

    public ?int $selectedClientId = null;
    public ?Client $client = null;
    public array $data = [];

    public function mount(?int $client = null): void
    {
        $this->selectedClientId = $client ?? request()->get('client');
        if ($this->selectedClientId) {
            $this->client = Client::find($this->selectedClientId);
            $this->data['selectedClientId'] = $this->selectedClientId;
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('selectedClientId')
                    ->label('Selecteer Klant')
                    ->options(Client::query()->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->live(onBlur: false)
                    ->afterStateUpdated(function ($state) {
                        $this->selectedClientId = $state;
                        $this->client = $state ? Client::find($state) : null;
                        $this->data['selectedClientId'] = $state;
                    })
                    ->placeholder('Kies een klant...'),
            ])
            ->statePath('data');
    }

    public function updatedDataSelectedClientId($value): void
    {
        if ($value) {
            $this->selectedClientId = (int) $value;
            $this->client = Client::find($this->selectedClientId);
        } else {
            $this->selectedClientId = null;
            $this->client = null;
        }
    }

    public function getClientProperty(): ?Client
    {
        if ($this->client) {
            return $this->client;
        }
        
        if ($this->selectedClientId) {
            return Client::find($this->selectedClientId);
        }
        
        if (!empty($this->data['selectedClientId'] ?? null)) {
            return Client::find($this->data['selectedClientId']);
        }
        
        return null;
    }

    public function getQuickStats(): array
    {
        $client = $this->getClientProperty();
        if (!$client) {
            return [];
        }

        $clientId = $client->id;

        // Document counts
        $totalDocuments = Document::where('client_id', $clientId)->count();
        $pendingDocuments = Document::where('client_id', $clientId)
            ->whereIn('status', ['pending', 'ocr_processing', 'review_required'])
            ->count();
        $approvedDocuments = Document::where('client_id', $clientId)
            ->where('status', 'approved')
            ->count();

        // BTW Status
        $currentPeriod = VatPeriod::where('client_id', $clientId)
            ->where('status', 'open')
            ->latest('period_start')
            ->first();
        
        $btwVerschuldigd = 0;
        if ($currentPeriod) {
            $vatCalculator = app(VatCalculatorService::class);
            $totals = $vatCalculator->calculatePeriodTotals($currentPeriod);
            $btwVerschuldigd = $totals['verschuldigd'] ?? 0;
        }

        // Open tasks
        $openTasks = Task::where('client_id', $clientId)
            ->where('status', 'open')
            ->count();

        // Last activity
        $lastDocument = Document::where('client_id', $clientId)
            ->latest('updated_at')
            ->first();
        $lastActivity = $lastDocument ? $lastDocument->updated_at->diffForHumans() : 'Geen activiteit';

        return [
            'total_documents' => $totalDocuments,
            'pending_documents' => $pendingDocuments,
            'approved_documents' => $approvedDocuments,
            'btw_verschuldigd' => $btwVerschuldigd,
            'open_tasks' => $openTasks,
            'last_activity' => $lastActivity,
            'current_period' => $currentPeriod?->period_string ?? 'Geen actieve periode',
        ];
    }

    public function getActionItems(): array
    {
        $client = $this->getClientProperty();
        if (!$client) {
            return [];
        }

        $clientId = $client->id;
        $actions = [];

        // Documents needing review
        $reviewNeeded = Document::where('client_id', $clientId)
            ->where('status', 'review_required')
            ->count();
        if ($reviewNeeded > 0) {
            $actions[] = [
                'type' => 'warning',
                'icon' => '⚠️',
                'title' => "{$reviewNeeded} document(en) vereisen review",
                'description' => 'Documenten met lage confidence of validatiefouten',
                'action' => 'review_documents',
                'priority' => 'high',
            ];
        }

        // BTW deadline
        $currentPeriod = VatPeriod::where('client_id', $clientId)
            ->where('status', 'open')
            ->latest('period_start')
            ->first();
        
        if ($currentPeriod) {
            $deadline = $currentPeriod->period_end->copy()->addMonths(1)->endOfMonth();
            $daysUntilDeadline = now()->diffInDays($deadline, false);
            
            if ($daysUntilDeadline >= 0 && $daysUntilDeadline <= 30) {
                $actions[] = [
                    'type' => $daysUntilDeadline <= 7 ? 'danger' : 'warning',
                    'icon' => '📅',
                    'title' => "BTW deadline in {$daysUntilDeadline} dagen",
                    'description' => "Periode: {$currentPeriod->period_string} - Deadline: {$deadline->format('d-m-Y')}",
                    'action' => 'view_btw_period',
                    'priority' => $daysUntilDeadline <= 7 ? 'critical' : 'high',
                ];
            }
        }

        // Open tasks
        $openTasks = Task::where('client_id', $clientId)
            ->where('status', 'open')
            ->count();
        if ($openTasks > 0) {
            $actions[] = [
                'type' => 'info',
                'icon' => '❓',
                'title' => "{$openTasks} openstaande ta(a)k(en)",
                'description' => 'Taken die aandacht vereisen van de klant',
                'action' => 'view_tasks',
                'priority' => 'medium',
            ];
        }

        // Documents ready for approval
        $readyToApprove = Document::where('client_id', $clientId)
            ->where('status', 'review_required')
            ->where('confidence_score', '>=', 85)
            ->count();
        if ($readyToApprove > 0) {
            $actions[] = [
                'type' => 'success',
                'icon' => '✅',
                'title' => "{$readyToApprove} document(en) klaar voor goedkeuring",
                'description' => 'Hoge confidence score, kan bulk goedgekeurd worden',
                'action' => 'bulk_approve',
                'priority' => 'low',
            ];
        }

        // Sort by priority
        $priorityOrder = ['critical' => 0, 'high' => 1, 'medium' => 2, 'low' => 3];
        usort($actions, function ($a, $b) use ($priorityOrder) {
            return ($priorityOrder[$a['priority']] ?? 99) <=> ($priorityOrder[$b['priority']] ?? 99);
        });

        return $actions;
    }

    public function getFinancialSnapshot(): array
    {
        $client = $this->getClientProperty();
        if (!$client) {
            return [];
        }

        $clientId = $client->id;
        $now = now();
        $thisMonth = $now->copy()->startOfMonth();
        $lastMonth = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();

        // This month
        $thisMonthDocs = Document::where('client_id', $clientId)
            ->where('status', 'approved')
            ->whereBetween('document_date', [$thisMonth, $now])
            ->get();
        
        $thisMonthTotal = $thisMonthDocs->sum('amount_incl');
        $thisMonthCount = $thisMonthDocs->count();

        // Last month
        $lastMonthDocs = Document::where('client_id', $clientId)
            ->where('status', 'approved')
            ->whereBetween('document_date', [$lastMonth, $lastMonthEnd])
            ->get();
        
        $lastMonthTotal = $lastMonthDocs->sum('amount_incl');
        $lastMonthCount = $lastMonthDocs->count();

        // Top suppliers
        $topSuppliers = Document::where('client_id', $clientId)
            ->where('status', 'approved')
            ->where('document_type', 'purchase_invoice')
            ->whereNotNull('supplier_name')
            ->select('supplier_name', DB::raw('SUM(amount_incl) as total'))
            ->groupBy('supplier_name')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn($doc) => [
                'name' => $doc->supplier_name,
                'total' => (float) $doc->total,
            ]);

        // BTW liability
        $currentPeriod = VatPeriod::where('client_id', $clientId)
            ->where('status', 'open')
            ->latest('period_start')
            ->first();
        
        $btwLiability = 0;
        if ($currentPeriod) {
            $vatCalculator = app(VatCalculatorService::class);
            $totals = $vatCalculator->calculatePeriodTotals($currentPeriod);
            $btwLiability = ($totals['verschuldigd'] ?? 0) - ($totals['aftrekbaar'] ?? 0);
        }

        return [
            'this_month' => [
                'total' => $thisMonthTotal,
                'count' => $thisMonthCount,
            ],
            'last_month' => [
                'total' => $lastMonthTotal,
                'count' => $lastMonthCount,
            ],
            'change_percentage' => $lastMonthTotal > 0 
                ? (($thisMonthTotal - $lastMonthTotal) / $lastMonthTotal) * 100 
                : 0,
            'top_suppliers' => $topSuppliers,
            'btw_liability' => $btwLiability,
        ];
    }

    public function getRecentDocuments(int $limit = 10): array
    {
        $client = $this->getClientProperty();
        if (!$client) {
            return [];
        }

        return Document::where('client_id', $client->id)
            ->latest('updated_at')
            ->limit($limit)
            ->get()
            ->map(fn($doc) => [
                'id' => $doc->id,
                'filename' => $doc->original_filename,
                'type' => $doc->document_type,
                'status' => $doc->status,
                'amount' => $doc->amount_incl,
                'date' => $doc->document_date?->format('d-m-Y') ?? 'Geen datum',
                'updated_at' => $doc->updated_at->diffForHumans(),
            ])
            ->toArray();
    }

    public function getUpcomingDeadlines(): array
    {
        $client = $this->getClientProperty();
        if (!$client) {
            return [];
        }

        $clientId = $client->id;
        $deadlines = [];

        // BTW deadlines
        $periods = VatPeriod::where('client_id', $clientId)
            ->where('status', 'open')
            ->get();

        foreach ($periods as $period) {
            $deadline = $period->period_end->copy()->addMonths(1)->endOfMonth();
            if ($deadline->isFuture()) {
                $deadlines[] = [
                    'type' => 'btw',
                    'title' => "BTW Aangifte {$period->period_string}",
                    'deadline' => $deadline->format('d-m-Y'),
                    'days_remaining' => now()->diffInDays($deadline, false),
                    'url' => route('filament.admin.resources.vat-periods.view', $period->id),
                ];
            }
        }

        // Sort by deadline
        usort($deadlines, fn($a, $b) => $a['days_remaining'] <=> $b['days_remaining']);

        return array_slice($deadlines, 0, 5);
    }

    public function getRecentCommunication(int $limit = 5): array
    {
        $client = $this->getClientProperty();
        if (!$client) {
            return [];
        }

        return Task::where('client_id', $client->id)
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->map(fn($task) => [
                'id' => $task->id,
                'type' => $task->type,
                'description' => substr($task->description, 0, 100) . '...',
                'status' => $task->status,
                'created_at' => $task->created_at->diffForHumans(),
            ])
            ->toArray();
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('view_client')
                ->label('Bekijk Klant Details')
                ->icon('heroicon-o-user')
                ->url(fn() => ($client = $this->getClientProperty()) 
                    ? route('filament.admin.resources.clients.edit', $client->id)
                    : null
                )
                ->visible(fn() => $this->getClientProperty() !== null),
            
            \Filament\Actions\Action::make('view_documents')
                ->label('Alle Documenten')
                ->icon('heroicon-o-document-text')
                ->url(fn() => ($client = $this->getClientProperty()) 
                    ? route('filament.admin.pages.documents-by-client', ['client' => $client->id])
                    : null
                )
                ->visible(fn() => $this->getClientProperty() !== null),
        ];
    }

    public function getTitle(): string
    {
        $client = $this->getClientProperty();
        return $client 
            ? "Dashboard: {$client->name}"
            : 'Klant Dashboard';
    }

    public function getHeading(): string
    {
        $client = $this->getClientProperty();
        return $client 
            ? "📊 {$client->name}"
            : '📊 Klant Dashboard';
    }
}

