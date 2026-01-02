<?php

namespace App\Filament\Pages;

use App\Models\Client;
use App\Models\Document;
use App\Models\VatPeriod;
use App\Models\LedgerAccount;
use App\Services\VatCalculatorService;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinancialInsightsDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = '📈 Financial Insights';
    protected static ?string $navigationGroup = 'Overzichten';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.pages.financial-insights-dashboard';

    public ?int $clientId = null;
    public ?string $periodFilter = 'current_quarter';

    protected static ?string $pollingInterval = '30s';

    public function mount(): void
    {
        $this->periodFilter = 'current_quarter';
        $this->clientId = null;
    }
    
    public function updatedClientId(): void
    {
        // Trigger recomputation when client changes
    }
    
    public function updatedPeriodFilter(): void
    {
        // Trigger recomputation when period changes
    }

    public function getInsightsProperty(): array
    {
        $period = $this->getPeriod();
        $clientId = $this->clientId;

        // Base query
        $documentsQuery = Document::where('status', 'approved')
            ->whereBetween('document_date', [$period['start'], $period['end']]);

        if ($clientId) {
            $documentsQuery->where('client_id', $clientId);
        }

        $documents = $documentsQuery->get();

        // Previous period for comparison
        $prevPeriod = $this->getPreviousPeriod();
        $prevDocuments = Document::where('status', 'approved')
            ->whereBetween('document_date', [$prevPeriod['start'], $prevPeriod['end']]);
        
        if ($clientId) {
            $prevDocuments->where('client_id', $clientId);
        }
        $prevDocuments = $prevDocuments->get();

        // Sales vs Purchase
        $salesInvoices = $documents->where('document_type', 'sales_invoice')
            ->where('is_paid', true); // Only paid sales invoices
        $purchaseInvoices = $documents->whereIn('document_type', ['purchase_invoice', 'receipt']);

        $salesTotal = $salesInvoices->sum('amount_incl');
        $purchaseTotal = $purchaseInvoices->sum('amount_incl');
        $profit = $salesTotal - $purchaseTotal;
        $profitMargin = $salesTotal > 0 ? ($profit / $salesTotal) * 100 : 0;

        // Previous period comparison
        $prevSales = $prevDocuments->where('document_type', 'sales_invoice')
            ->where('is_paid', true)
            ->sum('amount_incl');
        $prevPurchase = $prevDocuments->whereIn('document_type', ['purchase_invoice', 'receipt'])
            ->sum('amount_incl');
        $prevProfit = $prevSales - $prevPurchase;

        // VAT Analysis
        $vatCalculator = app(VatCalculatorService::class);
        $vatTotals = [];
        foreach ($documents as $doc) {
            $rubriek = $doc->vat_rubriek ?? $vatCalculator->calculateRubriek($doc);
            if (!isset($vatTotals[$rubriek])) {
                $vatTotals[$rubriek] = ['amount' => 0, 'vat' => 0, 'count' => 0];
            }
            $vatTotals[$rubriek]['amount'] += $doc->amount_excl ?? 0;
            $vatTotals[$rubriek]['vat'] += $doc->amount_vat ?? 0;
            $vatTotals[$rubriek]['count']++;
        }

        // Top Suppliers
        $topSuppliers = $documents
            ->whereNotNull('supplier_name')
            ->groupBy('supplier_name')
            ->map(function ($group) {
                return [
                    'name' => $group->first()->supplier_name,
                    'count' => $group->count(),
                    'total' => $group->sum('amount_incl'),
                ];
            })
            ->sortByDesc('total')
            ->take(10)
            ->values();

        // Top Ledger Accounts
        $topAccounts = $documents
            ->whereNotNull('ledger_account_id')
            ->groupBy('ledger_account_id')
            ->map(function ($group) {
                $account = $group->first()->ledgerAccount;
                return [
                    'code' => $account ? $account->code : 'N/A',
                    'description' => $account ? $account->description : 'Onbekend',
                    'count' => $group->count(),
                    'total' => $group->sum('amount_incl'),
                ];
            })
            ->sortByDesc('total')
            ->take(10)
            ->values();

        // Monthly Trend
        $monthlyTrend = $documents
            ->groupBy(function ($doc) {
                return Carbon::parse($doc->document_date)->format('Y-m');
            })
            ->map(function ($group) {
                return [
                    'month' => Carbon::parse($group->first()->document_date)->format('M Y'),
                    'sales' => $group->where('document_type', 'sales_invoice')
                        ->where('is_paid', true)
                        ->sum('amount_incl'),
                    'purchase' => $group->whereIn('document_type', ['purchase_invoice', 'receipt'])
                        ->sum('amount_incl'),
                    'count' => $group->count(),
                ];
            })
            ->sortKeys()
            ->values();

        // Automation Rate
        $autoApproved = $documents->where('auto_approved', true)->count();
        $automationRate = $documents->count() > 0 
            ? ($autoApproved / $documents->count()) * 100 
            : 0;

        return [
            'period' => $period,
            'summary' => [
                'total_documents' => $documents->count(),
                'sales_total' => $salesTotal,
                'purchase_total' => $purchaseTotal,
                'profit' => $profit,
                'profit_margin' => $profitMargin,
                'vat_verschuldigd' => collect($vatTotals)->filter(fn($v, $k) => in_array($k, ['1a', '1b', '1c']))->sum('vat'),
                'vat_aftrekbaar' => collect($vatTotals)->filter(fn($v, $k) => in_array($k, ['2a', '5b']))->sum('vat'),
                'netto_btw' => collect($vatTotals)->filter(fn($v, $k) => in_array($k, ['1a', '1b', '1c']))->sum('vat') 
                    - collect($vatTotals)->filter(fn($v, $k) => in_array($k, ['2a', '5b']))->sum('vat'),
            ],
            'comparison' => [
                'sales_change' => $prevSales > 0 ? (($salesTotal - $prevSales) / $prevSales) * 100 : 0,
                'purchase_change' => $prevPurchase > 0 ? (($purchaseTotal - $prevPurchase) / $prevPurchase) * 100 : 0,
                'profit_change' => $prevProfit != 0 ? (($profit - $prevProfit) / abs($prevProfit)) * 100 : 0,
            ],
            'vat_breakdown' => $vatTotals,
            'top_suppliers' => $topSuppliers,
            'top_accounts' => $topAccounts,
            'monthly_trend' => $monthlyTrend,
            'automation_rate' => $automationRate,
        ];
    }

    protected function getPeriod(): array
    {
        return match($this->periodFilter) {
            'current_quarter' => [
                'start' => now()->startOfQuarter(),
                'end' => now()->endOfQuarter(),
                'label' => 'Huidig Kwartaal',
            ],
            'last_quarter' => [
                'start' => now()->subQuarter()->startOfQuarter(),
                'end' => now()->subQuarter()->endOfQuarter(),
                'label' => 'Vorige Kwartaal',
            ],
            'current_year' => [
                'start' => now()->startOfYear(),
                'end' => now()->endOfYear(),
                'label' => 'Huidig Jaar',
            ],
            'last_year' => [
                'start' => now()->subYear()->startOfYear(),
                'end' => now()->subYear()->endOfYear(),
                'label' => 'Vorig Jaar',
            ],
            default => [
                'start' => now()->startOfQuarter(),
                'end' => now()->endOfQuarter(),
                'label' => 'Huidig Kwartaal',
            ],
        };
    }

    protected function getPreviousPeriod(): array
    {
        return match($this->periodFilter) {
            'current_quarter' => [
                'start' => now()->subQuarter()->startOfQuarter(),
                'end' => now()->subQuarter()->endOfQuarter(),
            ],
            'last_quarter' => [
                'start' => now()->subQuarter(2)->startOfQuarter(),
                'end' => now()->subQuarter(2)->endOfQuarter(),
            ],
            'current_year' => [
                'start' => now()->subYear()->startOfYear(),
                'end' => now()->subYear()->endOfYear(),
            ],
            'last_year' => [
                'start' => now()->subYear(2)->startOfYear(),
                'end' => now()->subYear(2)->endOfYear(),
            ],
            default => [
                'start' => now()->subQuarter()->startOfQuarter(),
                'end' => now()->subQuarter()->endOfQuarter(),
            ],
        };
    }

    public function getClientsProperty()
    {
        return Client::orderBy('name')->get();
    }
}

