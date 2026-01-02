<?php

namespace App\Services;

use App\Models\VatPeriod;
use App\Models\Client;
use App\Services\VatPeriodPdfService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class TaxReportService
{
    protected VatPeriodPdfService $pdfService;
    
    public function __construct(VatPeriodPdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }
    
    /**
     * Generate tax report for a period
     */
    public function generateReport(VatPeriod $period, string $format = 'pdf'): string
    {
        if ($format === 'pdf') {
            return $this->pdfService->generate($period);
        } elseif ($format === 'excel') {
            return $this->generateExcel($period);
        }
        
        throw new \InvalidArgumentException("Unsupported format: {$format}");
    }
    
    /**
     * Generate Excel report
     */
    protected function generateExcel(VatPeriod $period): string
    {
        // TODO: Implement Excel generation using PhpSpreadsheet
        // For now, return PDF path
        return $this->pdfService->generate($period);
    }
    
    /**
     * Send report via email to client
     */
    public function sendReportToClient(VatPeriod $period, ?string $email = null): bool
    {
        try {
            $client = $period->client;
            $email = $email ?? $client->email;
            
            if (!$email) {
                throw new \Exception('No email address available for client');
            }
            
            $pdfPath = $this->pdfService->generate($period);
            
            // TODO: Implement email sending
            // Mail::to($email)->send(new TaxReportMail($period, $pdfPath));
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to send tax report: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate multi-period report
     */
    public function generateMultiPeriodReport(array $periodIds, string $format = 'pdf'): string
    {
        $periods = VatPeriod::whereIn('id', $periodIds)->with('client')->get();
        
        // Group by client
        $clients = $periods->groupBy('client_id');
        
        // Generate combined report
        // TODO: Implement multi-period report generation
        
        return '';
    }
    
    /**
     * Schedule automatic report generation
     */
    public function scheduleReports(): void
    {
        // Get periods that need reports
        $periods = VatPeriod::where('status', 'ingediend')
            ->where('submitted_at', '>=', now()->subDays(7))
            ->with('client')
            ->get();
        
        foreach ($periods as $period) {
            // Generate and send report
            $this->sendReportToClient($period);
        }
    }
}

