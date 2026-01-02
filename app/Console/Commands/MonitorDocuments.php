<?php

namespace App\Console\Commands;

use App\Services\DocumentMonitoringService;
use Illuminate\Console\Command;

class MonitorDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'documents:monitor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor documents for failed jobs, stuck documents, and VAT discrepancies';

    /**
     * Execute the console command.
     */
    public function handle(DocumentMonitoringService $monitoringService): int
    {
        $this->info('Running document monitoring checks...');
        
        $results = $monitoringService->runAllChecks();
        
        $this->table(
            ['Check', 'Count'],
            [
                ['Failed OCR Jobs', $results['failed_ocr_jobs']],
                ['Stuck Documents', $results['stuck_documents']],
                ['VAT Discrepancies', $results['vat_discrepancies']],
            ]
        );
        
        $total = array_sum($results);
        if ($total > 0) {
            $this->warn("Found {$total} issue(s) - notifications have been sent to relevant users.");
            return Command::FAILURE;
        }
        
        $this->info('All checks passed - no issues found.');
        return Command::SUCCESS;
    }
}


