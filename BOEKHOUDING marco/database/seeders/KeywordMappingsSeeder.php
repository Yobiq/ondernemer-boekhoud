<?php

namespace Database\Seeders;

use App\Models\LedgerAccount;
use App\Models\LedgerKeywordMapping;
use Illuminate\Database\Seeder;

class KeywordMappingsSeeder extends Seeder
{
    /**
     * Seed common keyword mappings for automatic ledger suggestions
     */
    public function run(): void
    {
        $mappings = [
            // Fuel & Transportation (4500-4540)
            ['keyword' => 'benzine', 'account_code' => '4500', 'priority' => 10],
            ['keyword' => 'diesel', 'account_code' => '4500', 'priority' => 10],
            ['keyword' => 'shell', 'account_code' => '4500', 'priority' => 8],
            ['keyword' => 'esso', 'account_code' => '4500', 'priority' => 8],
            ['keyword' => 'bp', 'account_code' => '4500', 'priority' => 8],
            ['keyword' => 'tankstation', 'account_code' => '4500', 'priority' => 9],
            ['keyword' => 'onderhoud auto', 'account_code' => '4510', 'priority' => 10],
            ['keyword' => 'garage', 'account_code' => '4510', 'priority' => 8],
            ['keyword' => 'verzekering auto', 'account_code' => '4520', 'priority' => 10],
            ['keyword' => 'lease', 'account_code' => '4540', 'priority' => 10],
            
            // Office Supplies (4300)
            ['keyword' => 'kantoor', 'account_code' => '4300', 'priority' => 8],
            ['keyword' => 'office', 'account_code' => '4300', 'priority' => 7],
            ['keyword' => 'staples', 'account_code' => '4300', 'priority' => 9],
            ['keyword' => 'paperclip', 'account_code' => '4300', 'priority' => 8],
            ['keyword' => 'fellowes', 'account_code' => '4300', 'priority' => 8],
            ['keyword' => 'toner', 'account_code' => '4300', 'priority' => 9],
            ['keyword' => 'inkt', 'account_code' => '4300', 'priority' => 9],
            ['keyword' => 'printer', 'account_code' => '4300', 'priority' => 7],
            
            // Printing (4310)
            ['keyword' => 'drukwerk', 'account_code' => '4310', 'priority' => 10],
            ['keyword' => 'visitekaart', 'account_code' => '4310', 'priority' => 10],
            ['keyword' => 'briefpapier', 'account_code' => '4310', 'priority' => 10],
            
            // Phone & Internet (4410-4420)
            ['keyword' => 'telefoon', 'account_code' => '4410', 'priority' => 10],
            ['keyword' => 'kpn', 'account_code' => '4410', 'priority' => 9],
            ['keyword' => 'ziggo', 'account_code' => '4410', 'priority' => 9],
            ['keyword' => 'vodafone', 'account_code' => '4410', 'priority' => 9],
            ['keyword' => 'tmobile', 'account_code' => '4410', 'priority' => 9],
            ['keyword' => 'tele2', 'account_code' => '4410', 'priority' => 9],
            ['keyword' => 'internet', 'account_code' => '4410', 'priority' => 9],
            ['keyword' => 'mobiel', 'account_code' => '4410', 'priority' => 8],
            ['keyword' => 'website', 'account_code' => '4420', 'priority' => 9],
            ['keyword' => 'hosting', 'account_code' => '4420', 'priority' => 10],
            ['keyword' => 'domein', 'account_code' => '4420', 'priority' => 10],
            
            // Software & IT (4900)
            ['keyword' => 'software', 'account_code' => '4900', 'priority' => 10],
            ['keyword' => 'licentie', 'account_code' => '4900', 'priority' => 9],
            ['keyword' => 'microsoft', 'account_code' => '4900', 'priority' => 8],
            ['keyword' => 'adobe', 'account_code' => '4900', 'priority' => 8],
            ['keyword' => 'saas', 'account_code' => '4900', 'priority' => 9],
            ['keyword' => 'dropbox', 'account_code' => '4900', 'priority' => 8],
            
            // Advertising & Marketing (4600-4620)
            ['keyword' => 'google ads', 'account_code' => '4600', 'priority' => 10],
            ['keyword' => 'facebook ads', 'account_code' => '4600', 'priority' => 10],
            ['keyword' => 'advertentie', 'account_code' => '4600', 'priority' => 9],
            ['keyword' => 'marketing', 'account_code' => '4600', 'priority' => 8],
            ['keyword' => 'relatiegeschenk', 'account_code' => '4610', 'priority' => 10],
            ['keyword' => 'representatie', 'account_code' => '4620', 'priority' => 10],
            ['keyword' => 'borrel', 'account_code' => '4620', 'priority' => 8],
            ['keyword' => 'diner', 'account_code' => '4620', 'priority' => 7],
            
            // Professional Services (4710-4730)
            ['keyword' => 'accountant', 'account_code' => '4710', 'priority' => 10],
            ['keyword' => 'boekhouder', 'account_code' => '4710', 'priority' => 10],
            ['keyword' => 'advocaat', 'account_code' => '4720', 'priority' => 10],
            ['keyword' => 'juridisch', 'account_code' => '4720', 'priority' => 9],
            ['keyword' => 'notaris', 'account_code' => '4720', 'priority' => 10],
            ['keyword' => 'advies', 'account_code' => '4730', 'priority' => 7],
            ['keyword' => 'consultant', 'account_code' => '4730', 'priority' => 8],
            
            // Banking (4740)
            ['keyword' => 'bank', 'account_code' => '4740', 'priority' => 7],
            ['keyword' => 'transactie', 'account_code' => '4740', 'priority' => 6],
            ['keyword' => 'abnamro', 'account_code' => '4740', 'priority' => 8],
            ['keyword' => 'rabobank', 'account_code' => '4740', 'priority' => 8],
            ['keyword' => 'ing', 'account_code' => '4740', 'priority' => 8],
            
            // Utilities (4210)
            ['keyword' => 'energie', 'account_code' => '4210', 'priority' => 9],
            ['keyword' => 'elektriciteit', 'account_code' => '4210', 'priority' => 10],
            ['keyword' => 'gas', 'account_code' => '4210', 'priority' => 9],
            ['keyword' => 'water', 'account_code' => '4210', 'priority' => 9],
            ['keyword' => 'essent', 'account_code' => '4210', 'priority' => 8],
            ['keyword' => 'eneco', 'account_code' => '4210', 'priority' => 8],
            ['keyword' => 'vattenfall', 'account_code' => '4210', 'priority' => 8],
            
            // Rent (4200)
            ['keyword' => 'huur', 'account_code' => '4200', 'priority' => 10],
            ['keyword' => 'pand', 'account_code' => '4200', 'priority' => 8],
            
            // Training & Education (4910)
            ['keyword' => 'cursus', 'account_code' => '4910', 'priority' => 10],
            ['keyword' => 'opleiding', 'account_code' => '4910', 'priority' => 10],
            ['keyword' => 'training', 'account_code' => '4910', 'priority' => 10],
            ['keyword' => 'boek', 'account_code' => '4910', 'priority' => 7],
            ['keyword' => 'bol.com', 'account_code' => '4910', 'priority' => 6],
        ];
        
        foreach ($mappings as $mapping) {
            $account = LedgerAccount::where('code', $mapping['account_code'])->first();
            
            if ($account) {
                LedgerKeywordMapping::firstOrCreate(
                    [
                        'keyword' => $mapping['keyword'],
                        'ledger_account_id' => $account->id,
                    ],
                    [
                        'priority' => $mapping['priority'],
                    ]
                );
            }
        }
        
        $this->command->info('✅ ' . count($mappings) . ' keyword mappings created!');
    }
}

