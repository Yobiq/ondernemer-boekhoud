<?php

namespace App\Filament\Pages;

use App\Models\Client;
use App\Models\VatPeriod;
use App\Services\ClientTaxWorkflowService;
use App\Services\VatPeriodLockService;
use App\Services\VatPeriodPdfService;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\View\View;

class ClientTaxWorkflow extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    protected static ?string $navigationLabel = 'BTW Workflow per Klant';
    protected static ?string $navigationGroup = 'Workflow';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.pages.client-tax-workflow';

    public ?int $clientId = null;
    public ?int $periodId = null;
    public array $workflowData = [];
    public bool $isCalculating = false;
    public string $activeTab = 'all';

    protected static ?string $pollingInterval = '30s';

    public function mount(): void
    {
        // If user is a client, use their client_id
        if (Auth::user()->client_id) {
            $this->clientId = Auth::user()->client_id;
            $this->loadWorkflow();
        }
    }

    public function loadWorkflow(): void
    {
        if (!$this->clientId) {
            return;
        }

        $workflowService = app(ClientTaxWorkflowService::class);
        $client = Client::findOrFail($this->clientId);
        $period = $this->periodId 
            ? VatPeriod::find($this->periodId)
            : null;

        $this->workflowData = $workflowService->getWorkflowStatus($client, $period);
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make('Selectie')
                ->schema([
                    Forms\Components\Select::make('clientId')
                        ->label('Klant')
                        ->options(Client::query()->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function () {
                            $this->periodId = null;
                            $this->loadWorkflow();
                        })
                        ->disabled(fn () => Auth::user()->client_id !== null),
                    
                    Forms\Components\Select::make('periodId')
                        ->label('Periode')
                        ->options(function () {
                            if (!$this->clientId) {
                                return [];
                            }
                            
                            return VatPeriod::where('client_id', $this->clientId)
                                ->orderBy('year', 'desc')
                                ->orderBy('quarter', 'desc')
                                ->get()
                                ->mapWithKeys(function ($period) {
                                    return [$period->id => $period->period_string];
                                });
                        })
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(fn () => $this->loadWorkflow())
                        ->placeholder('Huidige periode'),
                ])
                ->columns(2),
        ];
    }

    public function calculateTax(): void
    {
        if (empty($this->workflowData) || !isset($this->workflowData['period'])) {
            Notification::make()
                ->title('Geen periode geselecteerd')
                ->warning()
                ->send();
            return;
        }

        $this->isCalculating = true;
        
        try {
            $workflowService = app(ClientTaxWorkflowService::class);
            $workflowService->autoCalculateTax($this->workflowData['period']);
            $this->loadWorkflow();
            
            Notification::make()
                ->title('BTW berekening voltooid')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Fout bij berekening')
                ->body($e->getMessage())
                ->danger()
                ->send();
        } finally {
            $this->isCalculating = false;
        }
    }

    public function preparePeriod(): void
    {
        if (empty($this->workflowData) || !isset($this->workflowData['period'])) {
            return;
        }

        try {
            $period = $this->workflowData['period'];
            $period->update([
                'status' => 'voorbereid',
                'prepared_by' => Auth::id(),
                'prepared_at' => now(),
            ]);
            
            $this->loadWorkflow();
            
            Notification::make()
                ->title('Periode voorbereid')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Fout')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function unlockPeriod(): void
    {
        if (empty($this->workflowData) || !isset($this->workflowData['period'])) {
            return;
        }

        try {
            $lockService = app(VatPeriodLockService::class);
            $period = $this->workflowData['period'];
            
            if (!$period->isLocked()) {
                Notification::make()
                    ->title('Periode niet afgesloten')
                    ->body('Deze periode is niet afgesloten.')
                    ->warning()
                    ->send();
                return;
            }
            
            $lockService->unlock($period, Auth::user());
            
            $this->loadWorkflow();
            
            Notification::make()
                ->title('Periode heropend')
                ->body('De periode is heropend voor correcties. Je kunt nu documenten toevoegen of bewerken.')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Fout')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function submitPeriod(): void
    {
        if (empty($this->workflowData) || !isset($this->workflowData['period'])) {
            return;
        }

        try {
            $lockService = app(VatPeriodLockService::class);
            $period = $this->workflowData['period'];
            
            $lockService->lock($period, Auth::user());
            
            $this->loadWorkflow();
            
            Notification::make()
                ->title('BTW aangifte ingediend')
                ->body('De periode is succesvol afgesloten.')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Fout bij indienen')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function exportPdf()
    {
        if (empty($this->workflowData) || !isset($this->workflowData['period'])) {
            Notification::make()
                ->title('Geen periode geselecteerd')
                ->warning()
                ->send();
            return null;
        }

        try {
            $pdfService = app(VatPeriodPdfService::class);
            return $pdfService->download($this->workflowData['period']);
        } catch (\Exception $e) {
            Notification::make()
                ->title('Fout bij PDF genereren')
                ->body($e->getMessage())
                ->danger()
                ->send();
            return null;
        }
    }

    public function processPendingDocuments(): void
    {
        if (!$this->clientId) {
            Notification::make()
                ->title('Geen klant geselecteerd')
                ->warning()
                ->send();
            return;
        }

        try {
            $client = Client::findOrFail($this->clientId);
            $period = $this->periodId 
                ? VatPeriod::find($this->periodId)
                : null;

            if (!$period) {
                $workflowService = app(ClientTaxWorkflowService::class);
                $period = $workflowService->getOrCreateCurrentPeriod($client);
            }

            // Get pending documents
            $pendingDocuments = \App\Models\Document::where('client_id', $client->id)
                ->whereIn('status', ['pending'])
                ->get();

            $processed = 0;
            foreach ($pendingDocuments as $document) {
                // Check if document is in period
                $inPeriod = false;
                if ($document->document_date) {
                    $inPeriod = $document->document_date >= $period->period_start 
                        && $document->document_date <= $period->period_end;
                } else {
                    $inPeriod = $document->created_at >= $period->period_start 
                        && $document->created_at <= $period->period_end;
                }

                if ($inPeriod) {
                    \App\Jobs\ProcessDocumentOcrJob::dispatch($document);
                    $processed++;
                }
            }

            if ($processed > 0) {
                Notification::make()
                    ->title('Documenten verwerken gestart')
                    ->body("{$processed} document(en) worden nu verwerkt door OCR")
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Geen documenten om te verwerken')
                    ->body('Alle documenten zijn al verwerkt of vallen buiten de periode')
                    ->info()
                    ->send();
            }

            $this->loadWorkflow();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Fout bij verwerken')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function processDocument(int $documentId): void
    {
        try {
            $document = \App\Models\Document::findOrFail($documentId);
            
            // Verify document belongs to client
            if ($document->client_id !== $this->clientId) {
                Notification::make()
                    ->title('Toegang geweigerd')
                    ->body('Dit document behoort niet tot de geselecteerde klant')
                    ->danger()
                    ->send();
                return;
            }

            // Check if document is in pending status
            if ($document->status !== 'pending') {
                Notification::make()
                    ->title('Document al verwerkt')
                    ->body("Dit document heeft al status: {$document->status}")
                    ->warning()
                    ->send();
                return;
            }

            // Special handling for sales invoices - they don't need OCR
            if ($document->document_type === 'sales_invoice') {
                // Sales invoices from client portal already have data
                // Just validate and approve if data is complete
                if ($document->amount_incl && $document->document_date) {
                    $document->status = 'approved';
                    $document->confidence_score = 100;
                    $document->save();
                    
                    Notification::make()
                        ->title('Verkoopfactuur goedgekeurd')
                        ->body("{$document->original_filename} is direct goedgekeurd (geen OCR nodig)")
                        ->success()
                        ->send();
                } else {
                    $document->status = 'review_required';
                    $document->save();
                    
                    Notification::make()
                        ->title('Verkoopfactuur heeft review nodig')
                        ->body("{$document->original_filename} mist verplichte gegevens")
                        ->warning()
                        ->send();
                }
            } else {
                // Dispatch OCR job for other document types
                \App\Jobs\ProcessDocumentOcrJob::dispatch($document);

                Notification::make()
                    ->title('Document verwerken gestart')
                    ->body("{$document->original_filename} wordt nu verwerkt door OCR")
                    ->success()
                    ->send();
            }

            $this->loadWorkflow();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Fout bij verwerken')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function getDocumentsProperty(): \Illuminate\Database\Eloquent\Collection
    {
        if (!$this->clientId) {
            return collect();
        }

        $client = Client::findOrFail($this->clientId);
        $workflowService = app(ClientTaxWorkflowService::class);
        $period = $this->periodId 
            ? VatPeriod::find($this->periodId)
            : null;

        if (!$period) {
            $period = $workflowService->getOrCreateCurrentPeriod($client);
        }

        return \App\Models\Document::where('client_id', $client->id)
            ->where(function ($query) use ($period) {
                $query->whereBetween('document_date', [$period->period_start, $period->period_end])
                    ->orWhere(function ($q) use ($period) {
                        $q->whereNull('document_date')
                          ->whereBetween('created_at', [$period->period_start, $period->period_end]);
                    });
            })
            ->with('ledgerAccount') // Eager load grootboekrekening
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getWorkflowProperty(): array
    {
        return $this->workflowData;
    }

    public function getNextActionsProperty(): array
    {
        if (empty($this->workflowData)) {
            return [];
        }

        $workflowService = app(ClientTaxWorkflowService::class);
        return $workflowService->getNextActions(
            $this->workflowData['current_step'] ?? 'documents_processing',
            $this->workflowData['document_status'] ?? [],
            $this->workflowData['issues'] ?? []
        );
    }
}

