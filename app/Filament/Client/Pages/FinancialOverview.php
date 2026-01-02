<?php

namespace App\Filament\Client\Pages;

use App\Models\Document;
use Filament\Pages\Page;
use Filament\Widgets\StatsOverviewWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FinancialOverview extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Financieel Overzicht';
    protected static ?string $navigationGroup = 'Mijn Gegevens';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.client.pages.financial-overview';
    
    public function getTitle(): string
    {
        return 'Financieel Overzicht';
    }
    
    public function getHeading(): string
    {
        return '💰 Financieel Overzicht';
    }
    
    /**
     * Get financial summary data
     */
    public function getFinancialSummary(): array
    {
        $clientId = Auth::user()->client_id;
        
        if (!$clientId) {
            return [];
        }
        
        return Cache::remember("financial_summary_{$clientId}", 300, function () use ($clientId) {
            $now = now();
            $thisMonth = $now->copy()->startOfMonth();
            $lastMonth = $now->copy()->subMonth()->startOfMonth();
            $thisYear = $now->copy()->startOfYear();
            
            // This month spending (only paid sales invoices, all other documents)
            $thisMonthSpending = Document::where('client_id', $clientId)
                ->where('created_at', '>=', $thisMonth)
                ->where(function ($query) {
                    $query->where(function ($q) {
                        $q->where('document_type', 'sales_invoice')
                          ->where('is_paid', true);
                    })->orWhere(function ($q) {
                        $q->where('document_type', '!=', 'sales_invoice')
                          ->orWhereNull('document_type');
                    });
                })
                ->whereNotNull('amount_incl')
                ->sum('amount_incl') ?? 0;
            
            // Last month spending
            $lastMonthSpending = Document::where('client_id', $clientId)
                ->whereBetween('created_at', [$lastMonth, $thisMonth])
                ->where(function ($query) {
                    $query->where(function ($q) {
                        $q->where('document_type', 'sales_invoice')
                          ->where('is_paid', true);
                    })->orWhere(function ($q) {
                        $q->where('document_type', '!=', 'sales_invoice')
                          ->orWhereNull('document_type');
                    });
                })
                ->whereNotNull('amount_incl')
                ->sum('amount_incl') ?? 0;
            
            // This year total
            $thisYearTotal = Document::where('client_id', $clientId)
                ->where('created_at', '>=', $thisYear)
                ->where(function ($query) {
                    $query->where(function ($q) {
                        $q->where('document_type', 'sales_invoice')
                          ->where('is_paid', true);
                    })->orWhere(function ($q) {
                        $q->where('document_type', '!=', 'sales_invoice')
                          ->orWhereNull('document_type');
                    });
                })
                ->whereNotNull('amount_incl')
                ->sum('amount_incl') ?? 0;
            
            // Monthly average
            $monthsPassed = $now->month;
            $monthlyAverage = $monthsPassed > 0 ? round($thisYearTotal / $monthsPassed, 2) : $thisYearTotal;
            
            // Calculate change
            $change = $lastMonthSpending > 0 
                ? round((($thisMonthSpending - $lastMonthSpending) / $lastMonthSpending) * 100, 1)
                : ($thisMonthSpending > 0 ? 100 : 0);
            
            // Spending by category (grootboek)
            $spendingByCategory = Document::where('client_id', $clientId)
                ->where('created_at', '>=', $thisYear)
                ->where('status', 'approved')
                ->where(function ($query) {
                    $query->where(function ($q) {
                        $q->where('document_type', 'sales_invoice')
                          ->where('is_paid', true);
                    })->orWhere(function ($q) {
                        $q->where('document_type', '!=', 'sales_invoice')
                          ->orWhereNull('document_type');
                    });
                })
                ->whereNotNull('ledger_account_id')
                ->whereNotNull('amount_incl')
                ->select('ledger_account_id', DB::raw('SUM(amount_incl) as total'))
                ->groupBy('ledger_account_id')
                ->orderByDesc('total')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    $ledgerAccount = \App\Models\LedgerAccount::find($item->ledger_account_id);
                    return [
                        'category' => $ledgerAccount ? "{$ledgerAccount->code} - {$ledgerAccount->description}" : 'Onbekend',
                        'amount' => round($item->total, 2),
                    ];
                })
                ->toArray();
            
            // Spending by month (last 12 months)
            $monthlySpending = [];
            for ($i = 11; $i >= 0; $i--) {
                $monthStart = $now->copy()->subMonths($i)->startOfMonth();
                $monthEnd = $now->copy()->subMonths($i)->endOfMonth();
                
                $amount = Document::where('client_id', $clientId)
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->where('status', 'approved')
                    ->where(function ($query) {
                        $query->where(function ($q) {
                            $q->where('document_type', 'sales_invoice')
                              ->where('is_paid', true);
                        })->orWhere(function ($q) {
                            $q->where('document_type', '!=', 'sales_invoice')
                              ->orWhereNull('document_type');
                        });
                    })
                    ->whereNotNull('amount_incl')
                    ->sum('amount_incl') ?? 0;
                
                $monthlySpending[] = [
                    'month' => $monthStart->format('M Y'),
                    'short' => $monthStart->format('M'),
                    'amount' => round($amount, 2),
                ];
            }
            
            // Top suppliers (by amount)
            $topSuppliers = Document::where('client_id', $clientId)
                ->where('created_at', '>=', $thisYear)
                ->where('status', 'approved')
                ->whereNotNull('supplier_name')
                ->where('supplier_name', '!=', '')
                ->where(function ($query) {
                    $query->where(function ($q) {
                        $q->where('document_type', 'sales_invoice')
                          ->where('is_paid', true);
                    })->orWhere(function ($q) {
                        $q->where('document_type', '!=', 'sales_invoice')
                          ->orWhereNull('document_type');
                    });
                })
                ->whereNotNull('amount_incl')
                ->select('supplier_name', DB::raw('SUM(amount_incl) as total'), DB::raw('COUNT(*) as count'))
                ->groupBy('supplier_name')
                ->orderByDesc('total')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    return [
                        'name' => $item->supplier_name,
                        'amount' => round($item->total, 2),
                        'count' => $item->count,
                    ];
                })
                ->toArray();
            
            return [
                'this_month' => round($thisMonthSpending, 2),
                'last_month' => round($lastMonthSpending, 2),
                'change' => $change,
                'this_year' => round($thisYearTotal, 2),
                'monthly_average' => round($monthlyAverage, 2),
                'by_category' => $spendingByCategory,
                'monthly_trend' => $monthlySpending,
                'top_suppliers' => $topSuppliers,
            ];
        });
    }
    
    /**
     * Get VAT summary
     */
    public function getVatSummary(): array
    {
        $clientId = Auth::user()->client_id;
        
        if (!$clientId) {
            return [];
        }
        
        return Cache::remember("vat_summary_{$clientId}", 300, function () use ($clientId) {
            $thisYear = now()->startOfYear();
            
            // Total VAT paid (purchase invoices)
            $vatPaid = Document::where('client_id', $clientId)
                ->where('created_at', '>=', $thisYear)
                ->where('status', 'approved')
                ->whereIn('document_type', ['purchase_invoice', 'receipt'])
                ->whereNotNull('amount_vat')
                ->sum('amount_vat') ?? 0;
            
            // Total VAT collected (sales invoices - only paid)
            $vatCollected = Document::where('client_id', $clientId)
                ->where('created_at', '>=', $thisYear)
                ->where('status', 'approved')
                ->where('document_type', 'sales_invoice')
                ->where('is_paid', true)
                ->whereNotNull('amount_vat')
                ->sum('amount_vat') ?? 0;
            
            // Net VAT (to pay or receive)
            $netVat = $vatCollected - $vatPaid;
            
            return [
                'vat_paid' => round($vatPaid, 2),
                'vat_collected' => round($vatCollected, 2),
                'net_vat' => round($netVat, 2),
                'is_refund' => $netVat < 0,
            ];
        });
    }
}

