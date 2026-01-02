<?php

namespace App\Filament\Client\Pages;

use App\Filament\Client\Widgets\MyDocumentsWidget;
use App\Filament\Client\Widgets\MyTasksWidget;
use App\Services\DashboardExportService;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Cache;

class Dashboard extends Page
{
    protected static string $view = 'filament.client.pages.dashboard';
    
    protected static ?string $navigationLabel = 'Dashboard';
    
    protected static ?string $navigationIcon = 'heroicon-o-home';
    
    protected static ?int $navigationSort = 1;
    
    /**
     * Disable widget auto-discovery for this page
     */
    public static function getWidgets(): array
    {
        return [];
    }
    
    /**
     * Prevent Filament from auto-rendering widgets
     */
    protected function shouldRenderWidgets(): bool
    {
        return false;
    }
    
    /**
     * Get the page title
     */
    public function getTitle(): string
    {
        return 'Dashboard';
    }
    
    /**
     * Get the page heading
     */
    public function getHeading(): string
    {
        return 'Dashboard';
    }
    
    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_csv')
                ->label('Exporteer CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    $exportService = app(DashboardExportService::class);
                    $clientId = auth()->user()->client_id ?? null;
                    $dateFilter = session('dashboard_date_filter', '30');
                    $dateFrom = match($dateFilter) {
                        '7' => now()->subDays(7)->toDateString(),
                        '30' => now()->subDays(30)->toDateString(),
                        '90' => now()->subDays(90)->toDateString(),
                        'all' => null,
                        default => now()->subDays(30)->toDateString(),
                    };
                    
                    return $exportService->exportToCsv($clientId, $dateFrom);
                }),
            
            Action::make('export_pdf')
                ->label('Exporteer Rapport')
                ->icon('heroicon-o-document-text')
                ->color('danger')
                ->action(function () {
                    $exportService = app(DashboardExportService::class);
                    $clientId = auth()->user()->client_id ?? null;
                    $dateFilter = session('dashboard_date_filter', '30');
                    $dateFrom = match($dateFilter) {
                        '7' => now()->subDays(7)->toDateString(),
                        '30' => now()->subDays(30)->toDateString(),
                        '90' => now()->subDays(90)->toDateString(),
                        'all' => null,
                        default => now()->subDays(30)->toDateString(),
                    };
                    
                    $html = $exportService->exportToPdf($clientId, $dateFrom);
                    $filename = 'dashboard_rapport_' . now()->format('Y-m-d_His') . '.html';
                    
                    return response($html, 200, [
                        'Content-Type' => 'text/html',
                        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                    ]);
                }),
        ];
    }
    
    
    /**
     * Get key metrics for dashboard
     */
    public function getMetrics(): array
    {
        $clientId = auth()->user()->client_id ?? null;
        
        if (!$clientId) {
            return [];
        }
        
        return Cache::remember("dashboard_metrics_{$clientId}", 300, function () use ($clientId) {
            // Optimize: Use efficient queries
            $currentMonth = now()->month;
            $currentYear = now()->year;
            $lastMonth = now()->subMonth()->month;
            $lastYear = now()->subMonth()->year;
            $sevenDaysAgo = now()->subDays(7);
            
            // Total documents
            $totalDocs = \App\Models\Document::where('client_id', $clientId)->count();
            
            // Approved documents
            $approvedDocs = \App\Models\Document::where('client_id', $clientId)
                ->where('status', 'approved')
                ->count();
            
            // Pending documents
            $pendingDocs = \App\Models\Document::where('client_id', $clientId)
                ->whereIn('status', ['pending', 'ocr_processing', 'review_required'])
                ->count();
            
            // This month
            $thisMonthDocs = \App\Models\Document::where('client_id', $clientId)
                ->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->count();
            
            // Last month
            $lastMonthDocs = \App\Models\Document::where('client_id', $clientId)
                ->whereMonth('created_at', $lastMonth)
                ->whereYear('created_at', $lastYear)
                ->count();
            
            // Recent uploads (last 7 days)
            $recentUploads = \App\Models\Document::where('client_id', $clientId)
                ->where('created_at', '>=', $sevenDaysAgo)
                ->count();
            
            // Total amount - for sales invoices, only count paid ones; for others, count all
            $totalAmount = \App\Models\Document::where('client_id', $clientId)
                ->where(function ($query) {
                    $query->where(function ($q) {
                        // Sales invoices: only paid
                        $q->where('document_type', 'sales_invoice')
                          ->where('is_paid', true);
                    })->orWhere(function ($q) {
                        // Other documents: all
                        $q->where('document_type', '!=', 'sales_invoice')
                          ->orWhereNull('document_type');
                    });
                })
                ->whereNotNull('amount_incl')
                ->sum('amount_incl') ?? 0;
            
            // Calculate trends
            $monthTrend = 0;
            if ($lastMonthDocs > 0) {
                $monthTrend = round((($thisMonthDocs - $lastMonthDocs) / $lastMonthDocs) * 100);
            } elseif ($thisMonthDocs > 0) {
                $monthTrend = 100;
            }
            
            // Approval rate
            $approvalRate = $totalDocs > 0 ? round(($approvedDocs / $totalDocs) * 100) : 0;
            
            return [
                [
                    'type' => 'primary',
                    'icon' => '📄',
                    'label' => 'Totaal Documenten',
                    'value' => number_format($totalDocs),
                    'subtext' => 'Alle uploads',
                ],
                [
                    'type' => 'success',
                    'icon' => '✅',
                    'label' => 'Goedgekeurd',
                    'value' => $approvalRate . '%',
                    'subtext' => number_format($approvedDocs) . ' documenten',
                    'trend' => [
                        'direction' => $approvalRate >= 80 ? 'positive' : 'neutral',
                        'icon' => $approvalRate >= 80 ? '↑' : '→',
                        'text' => $approvalRate >= 80 ? 'Uitstekend' : 'Goed',
                    ],
                ],
                [
                    'type' => 'warning',
                    'icon' => '⏳',
                    'label' => 'In Behandeling',
                    'value' => number_format($pendingDocs),
                    'subtext' => $pendingDocs === 0 ? 'Alles verwerkt!' : 'Wordt verwerkt',
                ],
                [
                    'type' => 'info',
                    'icon' => '📅',
                    'label' => 'Deze Maand',
                    'value' => number_format($thisMonthDocs),
                    'trend' => [
                        'direction' => $monthTrend > 0 ? 'positive' : ($monthTrend < 0 ? 'negative' : 'neutral'),
                        'icon' => $monthTrend > 0 ? '↑' : ($monthTrend < 0 ? '↓' : '→'),
                        'text' => abs($monthTrend) . '% vs vorige maand',
                    ],
                ],
                [
                    'type' => 'purple',
                    'icon' => '💰',
                    'label' => 'Totaal Bedrag',
                    'value' => '€' . number_format($totalAmount, 2, ',', '.'),
                    'subtext' => 'Alle documenten',
                ],
                [
                    'type' => 'blue',
                    'icon' => '⚡',
                    'label' => 'Laatste 7 Dagen',
                    'value' => number_format($recentUploads),
                    'subtext' => 'Recente uploads',
                ],
            ];
        });
    }
    
    /**
     * Get recent activity feed
     */
    public function getRecentActivity(): array
    {
        $clientId = auth()->user()->client_id ?? null;
        
        if (!$clientId) {
            return [];
        }
        
        return Cache::remember("dashboard_activity_{$clientId}", 60, function () use ($clientId) {
            $activities = [];
            
            // Optimized: Get recent documents with only needed fields
            $recentDocs = \App\Models\Document::where('client_id', $clientId)
                ->select('id', 'original_filename', 'created_at', 'status', 'updated_at')
                ->orderBy('created_at', 'desc')
                ->limit(8)
                ->get();
            
            foreach ($recentDocs as $doc) {
                // Add upload activity
                $activities[] = [
                    'type' => 'upload',
                    'icon' => '📤',
                    'title' => 'Document geüpload: ' . \Illuminate\Support\Str::limit($doc->original_filename, 40),
                    'time' => $doc->created_at->diffForHumans(),
                    'timestamp' => $doc->created_at->timestamp,
                ];
                
                // Add status change if approved and updated after creation
                if ($doc->status === 'approved' && $doc->updated_at->gt($doc->created_at)) {
                    $activities[] = [
                        'type' => 'status',
                        'icon' => '✅',
                        'title' => 'Document goedgekeurd: ' . \Illuminate\Support\Str::limit($doc->original_filename, 35),
                        'time' => $doc->updated_at->diffForHumans(),
                        'timestamp' => $doc->updated_at->timestamp,
                    ];
                }
            }
            
            // Sort by timestamp (newest first) and limit to 10
            usort($activities, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);
            return array_slice($activities, 0, 10);
        });
    }
    
    /**
     * Get smart insights and recommendations
     */
    public function getInsights(): array
    {
        $clientId = auth()->user()->client_id ?? null;
        
        if (!$clientId) {
            return [];
        }
        
        return Cache::remember("dashboard_insights_{$clientId}", 300, function () use ($clientId) {
            $insights = [];
            
            // Check approval rate
            $totalDocs = \App\Models\Document::where('client_id', $clientId)->count();
            $approvedDocs = \App\Models\Document::where('client_id', $clientId)
                ->where('status', 'approved')
                ->count();
            $approvalRate = $totalDocs > 0 ? round(($approvedDocs / $totalDocs) * 100) : 0;
            
            // Pending documents
            $pendingDocs = \App\Models\Document::where('client_id', $clientId)
                ->whereIn('status', ['pending', 'ocr_processing', 'review_required'])
                ->count();
            
            if ($approvalRate >= 90 && $totalDocs > 10) {
                $insights[] = [
                    'type' => 'success',
                    'icon' => '🎉',
                    'title' => 'Uitstekende Goedkeuringsgraad',
                    'text' => "Uw goedkeuringspercentage is {$approvalRate}%! Blijf documenten duidelijk uploaden.",
                ];
            } elseif ($approvalRate < 70 && $totalDocs > 5) {
                $insights[] = [
                    'type' => 'warning',
                    'icon' => '💡',
                    'title' => 'Verbeter Goedkeuringsgraad',
                    'text' => "Uw goedkeuringspercentage is {$approvalRate}%. Probeer foto's recht boven documenten te maken voor betere resultaten.",
                ];
            }
            
            // Use already calculated pendingDocs
            if ($pendingDocs > 5) {
                $insights[] = [
                    'type' => 'info',
                    'icon' => '⏳',
                    'title' => 'Documenten in Verwerking',
                    'text' => "U heeft {$pendingDocs} documenten in behandeling. Deze worden automatisch verwerkt.",
                ];
            }
            
            // Check recent activity (optimized query)
            $recentUploads = \App\Models\Document::where('client_id', $clientId)
                ->where('created_at', '>=', now()->subDays(7))
                ->exists();
            
            if (!$recentUploads && $totalDocs > 0) {
                $insights[] = [
                    'type' => 'info',
                    'icon' => '📤',
                    'title' => 'Geen Recente Uploads',
                    'text' => 'U heeft deze week nog geen documenten geüpload. Upload regelmatig voor up-to-date administratie.',
                ];
            }
            
            // Check monthly trend
            $thisMonth = \App\Models\Document::where('client_id', $clientId)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();
            
            $lastMonth = \App\Models\Document::where('client_id', $clientId)
                ->whereMonth('created_at', now()->subMonth()->month)
                ->whereYear('created_at', now()->subMonth()->year)
                ->count();
            
            if ($thisMonth > $lastMonth * 1.5 && $lastMonth > 0) {
                $insights[] = [
                    'type' => 'success',
                    'icon' => '📈',
                    'title' => 'Toename in Activiteit',
                    'text' => "U heeft deze maand {$thisMonth} documenten geüpload, een stijging ten opzichte van vorige maand!",
                ];
            }
            
            return $insights;
        });
    }
    
    /**
     * Get trends data
     */
    public function getTrends(): array
    {
        $clientId = auth()->user()->client_id ?? null;
        
        if (!$clientId) {
            return [];
        }
        
        return Cache::remember("dashboard_trends_{$clientId}", 600, function () use ($clientId) {
            $thisMonth = \App\Models\Document::where('client_id', $clientId)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();
            
            $lastMonth = \App\Models\Document::where('client_id', $clientId)
                ->whereMonth('created_at', now()->subMonth()->month)
                ->whereYear('created_at', now()->subMonth()->year)
                ->count();
            
            $monthTrend = 0;
            if ($lastMonth > 0) {
                $monthTrend = round((($thisMonth - $lastMonth) / $lastMonth) * 100);
            } elseif ($thisMonth > 0) {
                $monthTrend = 100;
            }
            
            return [
                'month' => [
                    'current' => $thisMonth,
                    'previous' => $lastMonth,
                    'trend' => $monthTrend,
                ],
            ];
        });
    }
    
    /**
     * Get processing time estimates
     */
    public function getProcessingEstimates(): array
    {
        $clientId = auth()->user()->client_id ?? null;
        
        if (!$clientId) {
            return [];
        }
        
        // Calculate average processing time from recent documents
        $recentProcessed = \App\Models\Document::where('client_id', $clientId)
            ->where('status', 'approved')
            ->whereNotNull('created_at')
            ->whereNotNull('updated_at')
            ->orderBy('updated_at', 'desc')
            ->limit(20)
            ->get();
        
        if ($recentProcessed->isEmpty()) {
            return [
                'average_hours' => 24,
                'estimate' => '24 uur',
            ];
        }
        
        $totalHours = 0;
        $count = 0;
        
        foreach ($recentProcessed as $doc) {
            $hours = $doc->created_at->diffInHours($doc->updated_at);
            if ($hours > 0 && $hours < 168) { // Less than a week
                $totalHours += $hours;
                $count++;
            }
        }
        
        $averageHours = $count > 0 ? round($totalHours / $count) : 24;
        
        return [
            'average_hours' => $averageHours,
            'estimate' => $averageHours < 24 ? "{$averageHours} uur" : round($averageHours / 24) . ' dagen',
        ];
    }
    
    /**
     * Get spending analytics data
     */
    public function getSpendingAnalytics(): array
    {
        $clientId = auth()->user()->client_id ?? null;
        
        if (!$clientId) {
            return [];
        }
        
        return Cache::remember("dashboard_spending_{$clientId}", 300, function () use ($clientId) {
            // This month spending - for sales invoices, only count paid ones
            $thisMonth = \App\Models\Document::where('client_id', $clientId)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->where(function ($query) {
                    $query->where(function ($q) {
                        // Sales invoices: only paid
                        $q->where('document_type', 'sales_invoice')
                          ->where('is_paid', true);
                    })->orWhere(function ($q) {
                        // Other documents: all
                        $q->where('document_type', '!=', 'sales_invoice')
                          ->orWhereNull('document_type');
                    });
                })
                ->whereNotNull('amount_incl')
                ->sum('amount_incl') ?? 0;
            
            // Last month spending
            $lastMonth = \App\Models\Document::where('client_id', $clientId)
                ->whereMonth('created_at', now()->subMonth()->month)
                ->whereYear('created_at', now()->subMonth()->year)
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
            
            // Calculate change
            $change = $lastMonth > 0 
                ? round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1)
                : ($thisMonth > 0 ? 100 : 0);
            
            // This year total
            $thisYear = \App\Models\Document::where('client_id', $clientId)
                ->whereYear('created_at', now()->year)
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
            
            // Average per month
            $avgPerMonth = now()->month > 0 
                ? round($thisYear / now()->month, 2)
                : $thisYear;
            
            return [
                'thisMonth' => round($thisMonth, 2),
                'lastMonth' => round($lastMonth, 2),
                'change' => $change,
                'thisYear' => round($thisYear, 2),
                'avgPerMonth' => round($avgPerMonth, 2),
            ];
        });
    }
    
    /**
     * Get top suppliers data
     */
    public function getTopSuppliers(): array
    {
        $clientId = auth()->user()->client_id ?? null;
        
        if (!$clientId) {
            return [];
        }
        
        return Cache::remember("dashboard_suppliers_{$clientId}", 300, function () use ($clientId) {
            // For sales invoices, only count paid ones; for others, count all
            $suppliers = \App\Models\Document::where('client_id', $clientId)
                ->whereNotNull('supplier_name')
                ->where('supplier_name', '!=', '')
                ->where('status', 'approved')
                ->where(function ($query) {
                    $query->where(function ($q) {
                        // Sales invoices: only paid
                        $q->where('document_type', 'sales_invoice')
                          ->where('is_paid', true);
                    })->orWhere(function ($q) {
                        // Other documents: all
                        $q->where('document_type', '!=', 'sales_invoice')
                          ->orWhereNull('document_type');
                    });
                })
                ->whereNotNull('amount_incl')
                ->select(
                    'supplier_name',
                    \Illuminate\Support\Facades\DB::raw('COUNT(*) as doc_count'),
                    \Illuminate\Support\Facades\DB::raw('COALESCE(SUM(amount_incl), 0) as total_amount')
                )
                ->groupBy('supplier_name')
                ->havingRaw('COALESCE(SUM(amount_incl), 0) > 0')
                ->orderByDesc('total_amount')
                ->limit(5)
                ->get()
                ->map(function ($item) {
                    return [
                        'name' => $item->supplier_name ?? 'Onbekend',
                        'count' => (int) ($item->doc_count ?? 0),
                        'amount' => round((float) ($item->total_amount ?? 0), 2),
                    ];
                })
                ->toArray();
            
            return $suppliers;
        });
    }
    
    /**
     * Get uploads timeline data (last 30 days)
     */
    public function getUploadsTimeline(): array
    {
        $clientId = auth()->user()->client_id ?? null;
        
        if (!$clientId) {
            return [];
        }
        
        return Cache::remember("dashboard_timeline_{$clientId}", 60, function () use ($clientId) {
            $startDate = now()->subDays(29)->startOfDay();
            
            $uploads = \App\Models\Document::where('client_id', $clientId)
                ->where('created_at', '>=', $startDate)
                ->select(
                    \Illuminate\Support\Facades\DB::raw('DATE(created_at) as date'),
                    \Illuminate\Support\Facades\DB::raw('COUNT(*) as count')
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get();
            
            // Fill in missing dates with 0
            $timeline = [];
            for ($i = 29; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $timeline[] = [
                    'date' => $date,
                    'label' => now()->subDays($i)->format('d M'),
                    'count' => $uploads->firstWhere('date', $date)->count ?? 0,
                ];
            }
            
            $totalCount = array_sum(array_column($timeline, 'count'));
            $avg = count($timeline) > 0 ? round($totalCount / count($timeline), 1) : 0;
            
            return [
                'timeline' => $timeline,
                'total' => $totalCount,
                'avg' => $avg,
            ];
        });
    }
    
    /**
     * Get monthly comparison data (last 3 months)
     */
    public function getMonthlyComparison(): array
    {
        $clientId = auth()->user()->client_id ?? null;
        
        if (!$clientId) {
            return [];
        }
        
        return Cache::remember("dashboard_monthly_{$clientId}", 300, function () use ($clientId) {
            $months = [];
            
            for ($i = 2; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $monthStart = $date->copy()->startOfMonth();
                $monthEnd = $date->copy()->endOfMonth();
                
                $count = \App\Models\Document::where('client_id', $clientId)
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->count();
                
                // For sales invoices, only count paid ones; for others, count all
                $amount = \App\Models\Document::where('client_id', $clientId)
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->where(function ($query) {
                        $query->where(function ($q) {
                            // Sales invoices: only paid
                            $q->where('document_type', 'sales_invoice')
                              ->where('is_paid', true);
                        })->orWhere(function ($q) {
                            // Other documents: all
                            $q->where('document_type', '!=', 'sales_invoice')
                              ->orWhereNull('document_type');
                        });
                    })
                    ->whereNotNull('amount_incl')
                    ->sum('amount_incl') ?? 0;
                
                $months[] = [
                    'month' => $date->format('M Y'),
                    'short' => $date->format('M'),
                    'count' => $count,
                    'amount' => round($amount, 2),
                ];
            }
            
            return $months;
        });
    }
    
    /**
     * Get document type breakdown
     */
    public function getDocumentTypeBreakdown(): array
    {
        $clientId = auth()->user()->client_id ?? null;
        
        if (!$clientId) {
            return [];
        }
        
        return Cache::remember("dashboard_types_{$clientId}", 300, function () use ($clientId) {
            $types = \App\Models\Document::where('client_id', $clientId)
                ->whereNotNull('document_type')
                ->select(
                    'document_type',
                    \Illuminate\Support\Facades\DB::raw('COUNT(*) as count')
                )
                ->groupBy('document_type')
                ->orderByDesc('count')
                ->get()
                ->map(function ($item) {
                    return [
                        'type' => $item->document_type ?? 'Onbekend',
                        'count' => (int) ($item->count ?? 0),
                    ];
                })
                ->toArray();
            
            return $types;
        });
    }
}

