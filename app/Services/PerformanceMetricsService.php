<?php

namespace App\Services;

use App\Models\Document;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class PerformanceMetricsService
{
    /**
     * Calculate average OCR processing time (upload to OCR completion)
     * Time from 'pending' to 'ocr_processing' complete
     */
    public function getAverageOcrTime(?int $clientId = null, ?string $dateFrom = null): array
    {
        $cacheKey = "metrics_ocr_time_{$clientId}_{$dateFrom}";
        
        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($clientId, $dateFrom) {
            $query = Document::query()
                ->where('status', '!=', 'pending')
                ->whereNotNull('updated_at');
            
            if ($clientId) {
                $query->where('client_id', $clientId);
            }
            
            if ($dateFrom) {
                $query->where('created_at', '>=', $dateFrom);
            }
            
            $documents = $query->get();
            
            if ($documents->isEmpty()) {
                return [
                    'average_seconds' => 0,
                    'average_minutes' => 0,
                    'average_hours' => 0,
                    'count' => 0,
                    'fastest_seconds' => 0,
                    'slowest_seconds' => 0,
                ];
            }
            
            $times = [];
            foreach ($documents as $doc) {
                // Simple approximation: updated_at - created_at for non-pending docs
                $seconds = $doc->created_at->diffInSeconds($doc->updated_at);
                $times[] = $seconds;
            }
            
            $avgSeconds = count($times) > 0 ? array_sum($times) / count($times) : 0;
            
            return [
                'average_seconds' => round($avgSeconds, 2),
                'average_minutes' => round($avgSeconds / 60, 2),
                'average_hours' => round($avgSeconds / 3600, 2),
                'count' => count($times),
                'fastest_seconds' => count($times) > 0 ? min($times) : 0,
                'slowest_seconds' => count($times) > 0 ? max($times) : 0,
            ];
        });
    }
    
    /**
     * Calculate average approval time (OCR complete to approved)
     */
    public function getAverageApprovalTime(?int $clientId = null, ?string $dateFrom = null): array
    {
        $cacheKey = "metrics_approval_time_{$clientId}_{$dateFrom}";
        
        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($clientId, $dateFrom) {
            $query = Document::query()
                ->where('status', 'approved');
            
            if ($clientId) {
                $query->where('client_id', $clientId);
            }
            
            if ($dateFrom) {
                $query->where('created_at', '>=', $dateFrom);
            }
            
            $documents = $query->get();
            
            if ($documents->isEmpty()) {
                return [
                    'average_seconds' => 0,
                    'average_minutes' => 0,
                    'average_hours' => 0,
                    'count' => 0,
                ];
            }
            
            $times = [];
            foreach ($documents as $doc) {
                // Time from creation to approval (updated_at when approved)
                $seconds = $doc->created_at->diffInSeconds($doc->updated_at);
                $times[] = $seconds;
            }
            
            $avgSeconds = count($times) > 0 ? array_sum($times) / count($times) : 0;
            
            return [
                'average_seconds' => round($avgSeconds, 2),
                'average_minutes' => round($avgSeconds / 60, 2),
                'average_hours' => round($avgSeconds / 3600, 2),
                'count' => count($times),
            ];
        });
    }
    
    /**
     * Calculate auto-approval rate
     */
    public function getAutoApprovalRate(?int $clientId = null, ?string $dateFrom = null): array
    {
        $cacheKey = "metrics_auto_approval_{$clientId}_{$dateFrom}";
        
        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($clientId, $dateFrom) {
            $query = Document::query();
            
            if ($clientId) {
                $query->where('client_id', $clientId);
            }
            
            if ($dateFrom) {
                $query->where('created_at', '>=', $dateFrom);
            }
            
            $total = $query->count();
            
            if ($total === 0) {
                return [
                    'total' => 0,
                    'auto_approved' => 0,
                    'manual_review' => 0,
                    'rate' => 0,
                ];
            }
            
            // Documents that went directly to approved (confidence >= 90)
            $autoApproved = (clone $query)
                ->where('status', 'approved')
                ->where('confidence_score', '>=', 90)
                ->count();
            
            // Documents that required review
            $manualReview = (clone $query)
                ->whereIn('status', ['review_required', 'task_opened'])
                ->count();
            
            $rate = $total > 0 ? ($autoApproved / $total) * 100 : 0;
            
            return [
                'total' => $total,
                'auto_approved' => $autoApproved,
                'manual_review' => $manualReview,
                'rate' => round($rate, 2),
            ];
        });
    }
    
    /**
     * Get processing time trend (last 30 days)
     */
    public function getProcessingTimeTrend(?int $clientId = null): array
    {
        $cacheKey = "metrics_time_trend_{$clientId}";
        
        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($clientId) {
            $startDate = now()->subDays(29)->startOfDay();
            
            $query = Document::query()
                ->where('created_at', '>=', $startDate)
                ->where('status', '!=', 'pending');
            
            if ($clientId) {
                $query->where('client_id', $clientId);
            }
            
            $documents = $query
                ->select('id', 'created_at', 'updated_at')
                ->orderBy('created_at')
                ->get();
            
            // Group by date and calculate average processing time
            $trend = [];
            for ($i = 29; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $dayDocs = $documents->filter(function ($doc) use ($date) {
                    return $doc->created_at->format('Y-m-d') === $date;
                });
                
                if ($dayDocs->isEmpty()) {
                    $trend[] = [
                        'date' => $date,
                        'label' => now()->subDays($i)->format('d M'),
                        'avg_minutes' => 0,
                        'count' => 0,
                    ];
                } else {
                    $totalMinutes = $dayDocs->sum(function ($doc) {
                        return $doc->created_at->diffInMinutes($doc->updated_at);
                    });
                    $avgMinutes = $totalMinutes / $dayDocs->count();
                    
                    $trend[] = [
                        'date' => $date,
                        'label' => now()->subDays($i)->format('d M'),
                        'avg_minutes' => round($avgMinutes, 2),
                        'count' => $dayDocs->count(),
                    ];
                }
            }
            
            return $trend;
        });
    }
    
    /**
     * Get approval rate trend by document type
     */
    public function getApprovalRateByType(?int $clientId = null): array
    {
        $cacheKey = "metrics_approval_by_type_{$clientId}";
        
        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($clientId) {
            $query = Document::query();
            
            if ($clientId) {
                $query->where('client_id', $clientId);
            }
            
            $types = $query
                ->select('document_type', DB::raw('COUNT(*) as total'), 
                         DB::raw('SUM(CASE WHEN status = \'approved\' AND confidence_score >= 90 THEN 1 ELSE 0 END) as auto_approved'))
                ->groupBy('document_type')
                ->get();
            
            return $types->map(function ($type) {
                $rate = $type->total > 0 ? ($type->auto_approved / $type->total) * 100 : 0;
                return [
                    'type' => $type->document_type ?? 'other',
                    'total' => $type->total,
                    'auto_approved' => $type->auto_approved,
                    'rate' => round($rate, 2),
                ];
            })->toArray();
        });
    }
    
    /**
     * Get performance badges based on metrics
     */
    public function getPerformanceBadges(?int $clientId = null): array
    {
        $ocrTime = $this->getAverageOcrTime($clientId, now()->subDays(30)->toDateString());
        $approvalRate = $this->getAutoApprovalRate($clientId, now()->subDays(30)->toDateString());
        
        $badges = [];
        
        // Speed badge: < 2 hours average processing
        if ($ocrTime['average_hours'] > 0 && $ocrTime['average_hours'] < 2) {
            $badges[] = [
                'name' => 'Snel Verwerkt',
                'icon' => '⚡',
                'color' => 'success',
                'description' => 'Gemiddelde verwerkingstijd < 2 uur',
            ];
        }
        
        // Quality badge: > 80% auto-approval
        if ($approvalRate['rate'] >= 80) {
            $badges[] = [
                'name' => 'Hoge Kwaliteit',
                'icon' => '⭐',
                'color' => 'warning',
                'description' => 'Meer dan 80% automatisch goedgekeurd',
            ];
        }
        
        // Consistency badge: Regular uploads
        $recentDocs = Document::where('client_id', $clientId)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();
        
        if ($recentDocs >= 20) {
            $badges[] = [
                'name' => 'Consistent',
                'icon' => '📊',
                'color' => 'info',
                'description' => 'Regelmatige uploads',
            ];
        }
        
        return $badges;
    }
    
    /**
     * Clear all cached metrics
     */
    public function clearCache(?int $clientId = null): void
    {
        $patterns = [
            "metrics_ocr_time_{$clientId}_*",
            "metrics_approval_time_{$clientId}_*",
            "metrics_auto_approval_{$clientId}_*",
            "metrics_time_trend_{$clientId}",
            "metrics_approval_by_type_{$clientId}",
        ];
        
        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }
    }
}

