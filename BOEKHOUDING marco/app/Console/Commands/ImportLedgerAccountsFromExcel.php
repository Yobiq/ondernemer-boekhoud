<?php

namespace App\Console\Commands;

use App\Models\LedgerAccount;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ImportLedgerAccountsFromExcel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ledger:import-excel {file=public/rekenschema grootboek.xlsx}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import ledger accounts from Excel file (rekenschema grootboek.xlsx)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');
        $fullPath = base_path($filePath);

        if (!file_exists($fullPath)) {
            $this->error("File not found: {$fullPath}");
            return Command::FAILURE;
        }

        $this->info("Reading Excel file: {$fullPath}");

        try {
            $spreadsheet = IOFactory::load($fullPath);
            $worksheet = $spreadsheet->getActiveSheet();
            $highestRow = $worksheet->getHighestRow();
            $highestColumn = $worksheet->getHighestColumn();

            $this->info("Found {$highestRow} rows, highest column: {$highestColumn}");

            // Try to detect header row
            $headerRow = 1;
            $dataStartRow = 2;

            // Look for common header patterns
            for ($row = 1; $row <= min(5, $highestRow); $row++) {
                $cellA = $worksheet->getCell("A{$row}")->getValue();
                $cellB = $worksheet->getCell("B{$row}")->getValue();
                
                // Check if this looks like a header
                if (stripos($cellA ?? '', 'code') !== false || 
                    stripos($cellA ?? '', 'rekening') !== false ||
                    stripos($cellB ?? '', 'omschrijving') !== false ||
                    stripos($cellB ?? '', 'beschrijving') !== false) {
                    $headerRow = $row;
                    $dataStartRow = $row + 1;
                    break;
                }
            }

            $this->info("Using header row: {$headerRow}, data starts at row: {$dataStartRow}");

            $imported = 0;
            $updated = 0;
            $skipped = 0;

            // Read data rows
            for ($row = $dataStartRow; $row <= $highestRow; $row++) {
                $code = $this->getCellValue($worksheet, "A{$row}");
                $description = $this->getCellValue($worksheet, "B{$row}");
                $type = $this->getCellValue($worksheet, "C{$row}") ?? $this->getCellValue($worksheet, "D{$row}");
                $vatDefault = $this->getCellValue($worksheet, "D{$row}") ?? $this->getCellValue($worksheet, "E{$row}");

                // Skip empty rows
                if (empty($code) && empty($description)) {
                    continue;
                }

                // Normalize code (remove spaces, ensure string)
                $code = trim((string) $code);
                if (empty($code)) {
                    $skipped++;
                    continue;
                }

                // Determine type from code if not provided
                if (empty($type)) {
                    $codeNum = (int) $code;
                    $type = ($codeNum >= 0 && $codeNum < 4000) ? 'balans' : 'winst_verlies';
                } else {
                    $type = $this->normalizeType($type);
                }

                // Normalize VAT default
                $vatDefault = $this->normalizeVatDefault($vatDefault);

                // Normalize description
                $description = trim((string) ($description ?? ''));

                if (empty($description)) {
                    $description = "Rekening {$code}";
                }

                // Update or create
                $account = LedgerAccount::updateOrCreate(
                    ['code' => $code],
                    [
                        'description' => $description,
                        'type' => $type,
                        'vat_default' => $vatDefault,
                        'active' => true,
                    ]
                );

                if ($account->wasRecentlyCreated) {
                    $imported++;
                } else {
                    $updated++;
                }

                $this->line("  ✓ {$code}: {$description} ({$type})");
            }

            $this->newLine();
            $this->info("Import completed!");
            $this->table(
                ['Status', 'Count'],
                [
                    ['Imported', $imported],
                    ['Updated', $updated],
                    ['Skipped', $skipped],
                    ['Total', $imported + $updated],
                ]
            );

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Error reading Excel file: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return Command::FAILURE;
        }
    }

    /**
     * Get cell value safely
     */
    private function getCellValue($worksheet, string $cell): ?string
    {
        try {
            $value = $worksheet->getCell($cell)->getValue();
            if ($value === null) {
                return null;
            }
            // Handle calculated values
            if ($value instanceof \PhpOffice\PhpSpreadsheet\Cell\Cell) {
                $value = $value->getCalculatedValue();
            }
            return trim((string) $value);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Normalize account type
     */
    private function normalizeType(?string $type): string
    {
        if (empty($type)) {
            return 'winst_verlies';
        }

        $type = strtolower(trim($type));
        
        if (stripos($type, 'balans') !== false) {
            return 'balans';
        }
        
        if (stripos($type, 'winst') !== false || stripos($type, 'verlies') !== false) {
            return 'winst_verlies';
        }

        return 'winst_verlies'; // Default
    }

    /**
     * Normalize VAT default value
     */
    private function normalizeVatDefault(?string $vat): ?string
    {
        if (empty($vat)) {
            return null;
        }

        $vat = trim((string) $vat);
        
        // Extract number
        if (preg_match('/(\d+)/', $vat, $matches)) {
            $num = (int) $matches[1];
            if ($num == 21) return '21';
            if ($num == 9) return '9';
            if ($num == 0) return '0';
        }

        // Check for verlegd/reverse charge
        if (stripos($vat, 'verleg') !== false || 
            stripos($vat, 'reverse') !== false ||
            stripos($vat, 'shifted') !== false) {
            return 'verlegd';
        }

        return null;
    }
}
