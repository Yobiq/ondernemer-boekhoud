<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TransactionImportService
{
    /**
     * Import transactions from CSV file
     * 
     * Expected CSV format (Dutch bank export):
     * Datum,Naam/Omschrijving,Rekening,Tegenrekening,Code,Bedrag,Mededelingen
     * 
     * @param string $filePath Path to CSV file
     * @param int $clientId Client ID
     * @return array ['imported' => int, 'skipped' => int, 'errors' => array]
     */
    public function importFromCsv(string $filePath, int $clientId): array
    {
        $client = Client::findOrFail($clientId);
        
        $stats = [
            'imported' => 0,
            'skipped' => 0,
            'errors' => [],
        ];
        
        if (!file_exists($filePath)) {
            $stats['errors'][] = 'Bestand niet gevonden';
            return $stats;
        }
        
        $handle = fopen($filePath, 'r');
        
        if ($handle === false) {
            $stats['errors'][] = 'Kan bestand niet openen';
            return $stats;
        }
        
        // Skip header row
        $header = fgetcsv($handle, 0, ',');
        
        $rowNumber = 1;
        
        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            $rowNumber++;
            
            try {
                $result = $this->importRow($row, $client);
                
                if ($result['success']) {
                    $stats['imported']++;
                } else {
                    $stats['skipped']++;
                    if (!empty($result['reason'])) {
                        $stats['errors'][] = "Regel {$rowNumber}: {$result['reason']}";
                    }
                }
            } catch (\Exception $e) {
                $stats['errors'][] = "Regel {$rowNumber}: {$e->getMessage()}";
                Log::error('Transaction import error', [
                    'row' => $rowNumber,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        fclose($handle);
        
        Log::info('Transaction import completed', [
            'client_id' => $clientId,
            'imported' => $stats['imported'],
            'skipped' => $stats['skipped'],
            'errors' => count($stats['errors']),
        ]);
        
        return $stats;
    }
    
    /**
     * Import a single row
     */
    protected function importRow(array $row, Client $client): array
    {
        // Expected format: Datum,Naam/Omschrijving,Rekening,Tegenrekening,Code,Bedrag,Mededelingen
        if (count($row) < 6) {
            return ['success' => false, 'reason' => 'Onvoldoende kolommen'];
        }
        
        $data = [
            'date' => $row[0] ?? null,
            'counterparty_name' => $row[1] ?? null,
            'own_iban' => $row[2] ?? null,
            'counterparty_iban' => $row[3] ?? null,
            'code' => $row[4] ?? null,
            'amount' => $row[5] ?? null,
            'description' => $row[6] ?? null,
        ];
        
        // Validate
        $validator = Validator::make($data, [
            'date' => 'required|date_format:Ymd',
            'amount' => 'required|numeric',
            'counterparty_iban' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return ['success' => false, 'reason' => 'Validatie fout: ' . $validator->errors()->first()];
        }
        
        // Create unique bank reference
        $bankReference = $this->generateBankReference($data);
        
        // Check if already exists
        if (Transaction::where('bank_reference', $bankReference)->exists()) {
            return ['success' => false, 'reason' => 'Transactie bestaat al'];
        }
        
        // Parse date (format: YYYYMMDD)
        $date = \Carbon\Carbon::createFromFormat('Ymd', $data['date']);
        
        // Parse amount (Dutch format: 1234,56 or -1234,56)
        $amount = str_replace(',', '.', str_replace('.', '', $data['amount']));
        $amount = (float) $amount;
        
        // Create transaction
        Transaction::create([
            'client_id' => $client->id,
            'bank_reference' => $bankReference,
            'amount' => $amount,
            'transaction_date' => $date,
            'iban' => $data['counterparty_iban'],
            'counterparty_name' => $data['counterparty_name'],
            'description' => $data['description'],
        ]);
        
        return ['success' => true];
    }
    
    /**
     * Generate unique bank reference
     */
    protected function generateBankReference(array $data): string
    {
        // Combine date + amount + IBAN to create unique reference
        return md5(
            $data['date'] . 
            $data['amount'] . 
            ($data['counterparty_iban'] ?? '') . 
            ($data['description'] ?? '')
        );
    }
    
    /**
     * Parse ING bank CSV format
     */
    public function importFromIngCsv(string $filePath, int $clientId): array
    {
        // ING has a different format
        // Can be implemented similarly
        return ['imported' => 0, 'skipped' => 0, 'errors' => ['ING format nog niet geïmplementeerd']];
    }
    
    /**
     * Parse Rabobank CSV format
     */
    public function importFromRabobankCsv(string $filePath, int $clientId): array
    {
        // Rabobank has a different format
        // Can be implemented similarly
        return ['imported' => 0, 'skipped' => 0, 'errors' => ['Rabobank format nog niet geïmplementeerd']];
    }
}

