<?php

namespace App\Filament\Client\Pages;

use App\Models\Document;
use App\Models\Transaction;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Rapporten extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Rapporten & Analytics';
    protected static ?int $navigationSort = 4;
    protected static string $view = 'filament.client.pages.rapporten';
    protected static ?string $navigationGroup = 'Documenten';
    
    public ?array $data = [];
    public $reportData = [];
    
    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_csv')
                ->label('Exporteer CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action('exportCsv'),
        ];
    }
    
    public function mount(): void
    {
        $this->form->fill([
            'period' => '30',
            'report_type' => 'summary',
        ]);
        $this->generateReport();
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Rapport Instellingen')
                    ->schema([
                        Select::make('period')
                            ->label('Periode')
                            ->options([
                                '7' => 'Laatste 7 dagen',
                                '30' => 'Laatste 30 dagen',
                                '90' => 'Laatste 90 dagen',
                                '365' => 'Laatste jaar',
                                'all' => 'Alle periodes',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn () => $this->generateReport()),
                        
                        Select::make('report_type')
                            ->label('Rapport Type')
                            ->options([
                                'summary' => 'Samenvatting',
                                'expenses' => 'Uitgaven',
                                'income' => 'Inkomsten',
                                'vat' => 'BTW Overzicht',
                                'documents' => 'Documenten Overzicht',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn () => $this->generateReport()),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }
    
    public function generateReport(): void
    {
        $data = $this->form->getState();
        $clientId = Auth::user()->client_id;
        
        $dateFrom = match($data['period'] ?? '30') {
            '7' => now()->subDays(7),
            '30' => now()->subDays(30),
            '90' => now()->subDays(90),
            '365' => now()->subDays(365),
            'all' => null,
            default => now()->subDays(30),
        };
        
        $query = Document::where('client_id', $clientId);
        if ($dateFrom) {
            $query->where('created_at', '>=', $dateFrom);
        }
        
        $documents = $query->get();
        
        // For income reports, only count paid sales invoices
        $reportType = $data['report_type'] ?? 'summary';
        $isIncomeReport = in_array($reportType, ['income', 'summary']);
        
        // Filter documents based on report type
        $filteredDocuments = $documents;
        if ($isIncomeReport) {
            // For income/summary: only paid sales invoices count for revenue
            $filteredDocuments = $documents->filter(function ($doc) {
                if ($doc->document_type === 'sales_invoice') {
                    return $doc->is_paid === true;
                }
                return true; // Other document types count normally
            });
        }
        
        $this->reportData = [
            'period' => $data['period'] ?? '30',
            'report_type' => $reportType,
            'total_documents' => $documents->count(),
            'total_amount' => $filteredDocuments->sum('amount_incl') ?? 0,
            'total_vat' => $filteredDocuments->sum('amount_vat') ?? 0,
            'approved_count' => $documents->where('status', 'approved')->count(),
            'pending_count' => $documents->whereIn('status', ['pending', 'ocr_processing', 'review_required'])->count(),
            'by_type' => $documents->groupBy('document_type')->map->count(),
            'by_status' => $documents->groupBy('status')->map->count(),
            'monthly_breakdown' => $filteredDocuments->groupBy(function ($doc) {
                return $doc->created_at->format('Y-m');
            })->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'amount' => $group->sum('amount_incl') ?? 0,
                ];
            }),
            'top_suppliers' => $filteredDocuments->whereNotNull('supplier_name')
                ->groupBy('supplier_name')
                ->map(function ($group) {
                    return [
                        'count' => $group->count(),
                        'amount' => $group->sum('amount_incl') ?? 0,
                    ];
                })
                ->sortByDesc('amount')
                ->take(10),
            'paid_invoices_count' => $documents->where('document_type', 'sales_invoice')->where('is_paid', true)->count(),
            'unpaid_invoices_count' => $documents->where('document_type', 'sales_invoice')->where('is_paid', false)->count(),
        ];
    }
    
    public function exportCsv()
    {
        $clientId = Auth::user()->client_id;
        $data = $this->form->getState();
        
        $dateFrom = match($data['period'] ?? '30') {
            '7' => now()->subDays(7),
            '30' => now()->subDays(30),
            '90' => now()->subDays(90),
            '365' => now()->subDays(365),
            'all' => null,
            default => now()->subDays(30),
        };
        
        $query = Document::where('client_id', $clientId);
        if ($dateFrom) {
            $query->where('created_at', '>=', $dateFrom);
        }
        
        $documents = $query->get();
        
        $filename = 'rapport_' . now()->format('Y-m-d_His') . '.csv';
        
        return response()->streamDownload(function () use ($documents) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header
            fputcsv($file, ['Datum', 'Bestand', 'Type', 'Status', 'Leverancier', 'Bedrag Excl.', 'BTW', 'Bedrag Incl.', 'Document Datum'], ';');
            
            // Data
            foreach ($documents as $doc) {
                fputcsv($file, [
                    $doc->created_at->format('d-m-Y H:i'),
                    $doc->original_filename,
                    $doc->document_type ?? '—',
                    $doc->status,
                    $doc->supplier_name ?? '—',
                    $doc->amount_excl ? number_format($doc->amount_excl, 2, ',', '.') : '—',
                    $doc->amount_vat ? number_format($doc->amount_vat, 2, ',', '.') : '—',
                    $doc->amount_incl ? number_format($doc->amount_incl, 2, ',', '.') : '—',
                    $doc->document_date ? $doc->document_date->format('d-m-Y') : '—',
                ], ';');
            }
            
            fclose($file);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}

