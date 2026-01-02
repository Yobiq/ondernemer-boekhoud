<?php

namespace App\Services;

use App\Models\Document;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;
use Carbon\Carbon;

/**
 * Service to monitor document processing and alert on issues
 * 
 * Monitors:
 * - Failed OCR jobs
 * - Stuck documents (pending for too long)
 * - VAT calculation discrepancies
 */
class DocumentMonitoringService
{
    /**
     * Check for failed OCR jobs and notify admins
     * 
     * @return int Number of failed jobs found
     */
    public function checkFailedOcrJobs(): int
    {
        $failedJobs = DB::table('failed_jobs')
            ->where('queue', 'ocr')
            ->where('failed_at', '>=', Carbon::now()->subHours(24))
            ->get();
        
        if ($failedJobs->isEmpty()) {
            return 0;
        }
        
        // Get unique document IDs from failed job payloads
        $documentIds = [];
        foreach ($failedJobs as $job) {
            try {
                $payload = json_decode($job->payload, true);
                // Try to extract document ID from serialized job data
                if (isset($payload['data']['command'])) {
                    // Laravel serializes jobs, try to extract from serialized string
                    if (preg_match('/"document";O:\d+:"[^"]+":\d+:\{.*?"id";i:(\d+)/', $job->payload, $matches)) {
                        $documentIds[] = (int) $matches[1];
                    } elseif (preg_match('/document_id["\']?\s*[:=]\s*(\d+)/', $job->payload, $matches)) {
                        $documentIds[] = (int) $matches[1];
                    }
                }
            } catch (\Exception $e) {
                // Skip if payload parsing fails
                continue;
            }
        }
        
        // Notify admins
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            Notification::make()
                ->title('Waarschuwing: Mislukte OCR verwerkingen')
                ->body("Er zijn {$failedJobs->count()} OCR job(s) mislukt in de afgelopen 24 uur. Controleer de failed jobs queue.")
                ->warning()
                ->persistent()
                ->sendToDatabase($admin);
        }
        
        Log::warning("DocumentMonitoring: Found {$failedJobs->count()} failed OCR jobs", [
            'document_ids' => array_unique($documentIds),
        ]);
        
        return $failedJobs->count();
    }
    
    /**
     * Check for stuck documents (pending for too long)
     * 
     * @param int $hoursThreshold Hours after which a document is considered "stuck"
     * @return int Number of stuck documents found
     */
    public function checkStuckDocuments(int $hoursThreshold = 24): int
    {
        $threshold = Carbon::now()->subHours($hoursThreshold);
        
        $stuckDocuments = Document::where('status', 'pending')
            ->where('created_at', '<', $threshold)
            ->get();
        
        if ($stuckDocuments->isEmpty()) {
            return 0;
        }
        
        // Group by client
        $byClient = $stuckDocuments->groupBy('client_id');
        
        foreach ($byClient as $clientId => $documents) {
            // Notify client's bookkeeper
            $bookkeeper = User::where('client_id', $clientId)
                ->where('role', 'bookkeeper')
                ->first();
            
            if ($bookkeeper) {
                Notification::make()
                    ->title('Waarschuwing: Documenten blijven hangen')
                    ->body("Er zijn {$documents->count()} document(en) die al meer dan {$hoursThreshold} uur in behandeling zijn. Controleer de OCR verwerking.")
                    ->warning()
                    ->persistent()
                    ->sendToDatabase($bookkeeper);
            }
            
            // Also notify admins
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                Notification::make()
                    ->title('Waarschuwing: Documenten blijven hangen')
                    ->body("Klant #{$clientId} heeft {$documents->count()} document(en) die al meer dan {$hoursThreshold} uur in behandeling zijn.")
                    ->warning()
                    ->persistent()
                    ->sendToDatabase($admin);
            }
        }
        
        Log::warning("DocumentMonitoring: Found {$stuckDocuments->count()} stuck documents", [
            'threshold_hours' => $hoursThreshold,
            'by_client' => $byClient->map(fn($docs) => $docs->count())->toArray(),
        ]);
        
        return $stuckDocuments->count();
    }
    
    /**
     * Check for VAT calculation discrepancies
     * 
     * Compares calculated VAT with stored VAT amounts
     * 
     * @return int Number of discrepancies found
     */
    public function checkVatDiscrepancies(): int
    {
        $discrepancies = [];
        
        // Get approved documents with VAT amounts
        $documents = Document::where('status', 'approved')
            ->whereNotNull('amount_excl')
            ->whereNotNull('amount_vat')
            ->whereNotNull('vat_rate')
            ->get();
        
        $vatValidator = app(VatValidator::class);
        $tolerance = config('bookkeeping.vat.tolerance', 0.01);
        
        foreach ($documents as $document) {
            $vatRate = (float) $document->vat_rate;
            if ($vatRate == 0) {
                continue; // Skip zero-rate VAT
            }
            
            // Calculate expected VAT
            $expectedVat = round($document->amount_excl * ($vatRate / 100), 2);
            $actualVat = (float) $document->amount_vat;
            $difference = abs($expectedVat - $actualVat);
            
            // Check if difference exceeds tolerance
            if ($difference > $tolerance) {
                $discrepancies[] = [
                    'document_id' => $document->id,
                    'client_id' => $document->client_id,
                    'expected_vat' => $expectedVat,
                    'actual_vat' => $actualVat,
                    'difference' => $difference,
                    'vat_rate' => $vatRate,
                ];
            }
        }
        
        if (empty($discrepancies)) {
            return 0;
        }
        
        // Group by client
        $byClient = collect($discrepancies)->groupBy('client_id');
        
        foreach ($byClient as $clientId => $clientDiscrepancies) {
            // Notify client's bookkeeper
            $bookkeeper = User::where('client_id', $clientId)
                ->where('role', 'bookkeeper')
                ->first();
            
            if ($bookkeeper) {
                Notification::make()
                    ->title('Waarschuwing: BTW berekening afwijkingen')
                    ->body("Er zijn " . count($clientDiscrepancies) . " document(en) met BTW berekening afwijkingen gevonden. Controleer deze documenten.")
                    ->warning()
                    ->persistent()
                    ->sendToDatabase($bookkeeper);
            }
        }
        
        // Also notify admins
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            Notification::make()
                ->title('Waarschuwing: BTW berekening afwijkingen')
                ->body("Er zijn " . count($discrepancies) . " document(en) met BTW berekening afwijkingen gevonden in het systeem.")
                ->warning()
                ->persistent()
                ->sendToDatabase($admin);
        }
        
        Log::warning("DocumentMonitoring: Found " . count($discrepancies) . " VAT calculation discrepancies", [
            'discrepancies' => $discrepancies,
        ]);
        
        return count($discrepancies);
    }
    
    /**
     * Run all monitoring checks
     * 
     * @return array Summary of findings
     */
    public function runAllChecks(): array
    {
        return [
            'failed_ocr_jobs' => $this->checkFailedOcrJobs(),
            'stuck_documents' => $this->checkStuckDocuments(),
            'vat_discrepancies' => $this->checkVatDiscrepancies(),
        ];
    }
}

