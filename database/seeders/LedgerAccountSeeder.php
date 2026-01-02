<?php

namespace Database\Seeders;

use App\Models\LedgerAccount;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LedgerAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Dutch Chart of Accounts (Grootboek)
     * 0000-2999: Balans accounts
     * 4000-9999: Winst & Verlies accounts
     */
    public function run(): void
    {
        $accounts = [
            // ========================================
            // BALANS ACCOUNTS (0000-2999)
            // ========================================
            
            // Vaste Activa (0000-0999)
            ['code' => '0100', 'description' => 'Bedrijfspanden', 'type' => 'balans', 'vat_default' => null],
            ['code' => '0200', 'description' => 'Verbouwingen', 'type' => 'balans', 'vat_default' => '21'],
            ['code' => '0300', 'description' => 'Machines en installaties', 'type' => 'balans', 'vat_default' => '21'],
            ['code' => '0400', 'description' => 'Vervoermiddelen', 'type' => 'balans', 'vat_default' => '21'],
            ['code' => '0500', 'description' => 'Computers en randapparatuur', 'type' => 'balans', 'vat_default' => '21'],
            ['code' => '0700', 'description' => 'Inventaris en inrichting', 'type' => 'balans', 'vat_default' => '21'],
            ['code' => '0900', 'description' => 'Goodwill', 'type' => 'balans', 'vat_default' => null],
            
            // Vlottende Activa (1000-1999)
            ['code' => '1000', 'description' => 'Voorraden gereed product', 'type' => 'balans', 'vat_default' => null],
            ['code' => '1100', 'description' => 'Debiteuren', 'type' => 'balans', 'vat_default' => null],
            ['code' => '1200', 'description' => 'Vooruitbetaalde kosten', 'type' => 'balans', 'vat_default' => null],
            ['code' => '1300', 'description' => 'Overige vorderingen', 'type' => 'balans', 'vat_default' => null],
            ['code' => '1400', 'description' => 'Rekening-courant DGA', 'type' => 'balans', 'vat_default' => null],
            ['code' => '1500', 'description' => 'BTW te vorderen', 'type' => 'balans', 'vat_default' => null],
            ['code' => '1510', 'description' => 'BTW af te dragen', 'type' => 'balans', 'vat_default' => null],
            ['code' => '1800', 'description' => 'Bank', 'type' => 'balans', 'vat_default' => null],
            ['code' => '1850', 'description' => 'Spaarrekening', 'type' => 'balans', 'vat_default' => null],
            ['code' => '1900', 'description' => 'Kas', 'type' => 'balans', 'vat_default' => null],
            
            // Eigen Vermogen (2000-2099)
            ['code' => '2000', 'description' => 'Aandelenkapitaal', 'type' => 'balans', 'vat_default' => null],
            ['code' => '2010', 'description' => 'Agioreserve', 'type' => 'balans', 'vat_default' => null],
            ['code' => '2050', 'description' => 'Winstreserve', 'type' => 'balans', 'vat_default' => null],
            ['code' => '2099', 'description' => 'Resultaat boekjaar', 'type' => 'balans', 'vat_default' => null],
            
            // Voorzieningen (2100-2199)
            ['code' => '2100', 'description' => 'Voorziening pensioen', 'type' => 'balans', 'vat_default' => null],
            ['code' => '2150', 'description' => 'Voorziening groot onderhoud', 'type' => 'balans', 'vat_default' => null],
            
            // Langlopende Schulden (2200-2399)
            ['code' => '2200', 'description' => 'Hypotheek', 'type' => 'balans', 'vat_default' => null],
            ['code' => '2210', 'description' => 'Bankleningen', 'type' => 'balans', 'vat_default' => null],
            ['code' => '2220', 'description' => 'Rekening-courant aandeelhouders', 'type' => 'balans', 'vat_default' => null],
            
            // Kortlopende Schulden (2400-2999)
            ['code' => '2400', 'description' => 'Crediteuren', 'type' => 'balans', 'vat_default' => null],
            ['code' => '2500', 'description' => 'Loonheffing', 'type' => 'balans', 'vat_default' => null],
            ['code' => '2510', 'description' => 'Pensioenpremie', 'type' => 'balans', 'vat_default' => null],
            ['code' => '2520', 'description' => 'Reservering vakantiegeld', 'type' => 'balans', 'vat_default' => null],
            ['code' => '2600', 'description' => 'BTW te betalen', 'type' => 'balans', 'vat_default' => null],
            ['code' => '2650', 'description' => 'Vennootschapsbelasting', 'type' => 'balans', 'vat_default' => null],
            ['code' => '2700', 'description' => 'Overige kortlopende schulden', 'type' => 'balans', 'vat_default' => null],
            
            // ========================================
            // WINST & VERLIES ACCOUNTS (4000-9999)
            // ========================================
            
            // Omzet (8000-8999)
            ['code' => '8000', 'description' => 'Omzet 21% BTW', 'type' => 'winst_verlies', 'vat_default' => '21'],
            ['code' => '8010', 'description' => 'Omzet 9% BTW', 'type' => 'winst_verlies', 'vat_default' => '9'],
            ['code' => '8020', 'description' => 'Omzet 0% BTW (export)', 'type' => 'winst_verlies', 'vat_default' => '0'],
            ['code' => '8030', 'description' => 'Omzet verlegd', 'type' => 'winst_verlies', 'vat_default' => 'verlegd'],
            ['code' => '8100', 'description' => 'Diensten 21% BTW', 'type' => 'winst_verlies', 'vat_default' => '21'],
            ['code' => '8500', 'description' => 'Overige opbrengsten', 'type' => 'winst_verlies', 'vat_default' => null],
            
            // Inkoopwaarde Omzet (7000-7999)
            ['code' => '7000', 'description' => 'Inkoop handelsgoederen 21%', 'type' => 'winst_verlies', 'vat_default' => '21'],
            ['code' => '7010', 'description' => 'Inkoop handelsgoederen 9%', 'type' => 'winst_verlies', 'vat_default' => '9'],
            ['code' => '7020', 'description' => 'Inkoop handelsgoederen verlegd', 'type' => 'winst_verlies', 'vat_default' => 'verlegd'],
            ['code' => '7050', 'description' => 'Vrachtkosten inkoop', 'type' => 'winst_verlies', 'vat_default' => '21'],
            
            // Kosten (4000-6999)
            // Personeelskosten
            ['code' => '4000', 'description' => 'Lonen en salarissen', 'type' => 'winst_verlies', 'vat_default' => null],
            ['code' => '4010', 'description' => 'Sociale lasten', 'type' => 'winst_verlies', 'vat_default' => null],
            ['code' => '4020', 'description' => 'Pensioenpremie', 'type' => 'winst_verlies', 'vat_default' => null],
            ['code' => '4030', 'description' => 'Vakantiegeld', 'type' => 'winst_verlies', 'vat_default' => null],
            ['code' => '4100', 'description' => 'Reiskosten personeel', 'type' => 'winst_verlies', 'vat_default' => '21'],
            ['code' => '4110', 'description' => 'Opleidingskosten', 'type' => 'winst_verlies', 'vat_default' => '21'],
            ['code' => '4120', 'description' => 'Personeelsfeesten', 'type' => 'winst_verlies', 'vat_default' => '9'],
            
            // Huisvestingskosten
            ['code' => '4200', 'description' => 'Huur bedrijfsruimte', 'type' => 'winst_verlies', 'vat_default' => '21'],
            ['code' => '4210', 'description' => 'Gas, water, elektriciteit', 'type' => 'winst_verlies', 'vat_default' => '21'],
            ['code' => '4220', 'description' => 'Schoonmaakkosten', 'type' => 'winst_verlies', 'vat_default' => '21'],
            ['code' => '4230', 'description' => 'Onderhoud bedrijfspand', 'type' => 'winst_verlies', 'vat_default' => '21'],
            ['code' => '4240', 'description' => 'Onroerende zaakbelasting', 'type' => 'winst_verlies', 'vat_default' => null],
            
            // Kantoorkosten
            ['code' => '4300', 'description' => 'Kantoorbenodigdheden', 'type' => 'winst_verlies', 'vat_default' => '21'],
            ['code' => '4310', 'description' => 'Drukwerk en papierwaren', 'type' => 'winst_verlies', 'vat_default' => '9'],
            ['code' => '4320', 'description' => 'Porto en verzendkosten', 'type' => 'winst_verlies', 'vat_default' => '21'],
            ['code' => '4330', 'description' => 'Contributies en abonnementen', 'type' => 'winst_verlies', 'vat_default' => '21'],
            
            // Communicatiekosten
            ['code' => '4410', 'description' => 'Telefoon en internet', 'type' => 'winst_verlies', 'vat_default' => '21'],
            ['code' => '4420', 'description' => 'Website en hosting', 'type' => 'winst_verlies', 'vat_default' => '21'],
            
            // Vervoerskosten
            ['code' => '4500', 'description' => 'Autokosten (brandstof)', 'type' => 'winst_verlies', 'vat_default' => '21'],
            ['code' => '4510', 'description' => 'Onderhoud auto', 'type' => 'winst_verlies', 'vat_default' => '21'],
            ['code' => '4520', 'description' => 'Verzekering auto', 'type' => 'winst_verlies', 'vat_default' => '21'],
            ['code' => '4530', 'description' => 'Motorrijtuigenbelasting', 'type' => 'winst_verlies', 'vat_default' => null],
            ['code' => '4540', 'description' => 'Leasekosten auto', 'type' => 'winst_verlies', 'vat_default' => '21'],
            
            // Verkoopkosten
            ['code' => '4600', 'description' => 'Advertentiekosten', 'type' => 'winst_verlies', 'vat_default' => '21'],
            ['code' => '4610', 'description' => 'Relatiegeschenken', 'type' => 'winst_verlies', 'vat_default' => '21'],
            ['code' => '4620', 'description' => 'Representatiekosten', 'type' => 'winst_verlies', 'vat_default' => '21'],
            ['code' => '4630', 'description' => 'Beurzen en evenementen', 'type' => 'winst_verlies', 'vat_default' => '21'],
            
            // Algemene kosten
            ['code' => '4700', 'description' => 'Verzekeringen', 'type' => 'winst_verlies', 'vat_default' => '21'],
            ['code' => '4710', 'description' => 'Accountantskosten', 'type' => 'winst_verlies', 'vat_default' => '21'],
            ['code' => '4720', 'description' => 'Juridische kosten', 'type' => 'winst_verlies', 'vat_default' => '21'],
            ['code' => '4730', 'description' => 'Advieskosten', 'type' => 'winst_verlies', 'vat_default' => '21'],
            ['code' => '4740', 'description' => 'Bankkosten', 'type' => 'winst_verlies', 'vat_default' => '21'],
            ['code' => '4750', 'description' => 'Incassokosten', 'type' => 'winst_verlies', 'vat_default' => '21'],
            
            // Afschrijvingen
            ['code' => '4800', 'description' => 'Afschrijving bedrijfspand', 'type' => 'winst_verlies', 'vat_default' => null],
            ['code' => '4810', 'description' => 'Afschrijving inventaris', 'type' => 'winst_verlies', 'vat_default' => null],
            ['code' => '4820', 'description' => 'Afschrijving machines', 'type' => 'winst_verlies', 'vat_default' => null],
            ['code' => '4830', 'description' => 'Afschrijving vervoermiddelen', 'type' => 'winst_verlies', 'vat_default' => null],
            ['code' => '4840', 'description' => 'Afschrijving computers', 'type' => 'winst_verlies', 'vat_default' => null],
            
            // Overige kosten
            ['code' => '4900', 'description' => 'Software en licenties', 'type' => 'winst_verlies', 'vat_default' => '21'],
            ['code' => '4910', 'description' => 'Cursussen en vakliteratuur', 'type' => 'winst_verlies', 'vat_default' => '9'],
            ['code' => '4920', 'description' => 'Klein materiaal', 'type' => 'winst_verlies', 'vat_default' => '21'],
            ['code' => '4930', 'description' => 'Onderzoek en ontwikkeling', 'type' => 'winst_verlies', 'vat_default' => '21'],
            ['code' => '4999', 'description' => 'Overige kosten', 'type' => 'winst_verlies', 'vat_default' => '21'], // FALLBACK
            
            // Financiële baten en lasten
            ['code' => '5000', 'description' => 'Rente bankrekening', 'type' => 'winst_verlies', 'vat_default' => null],
            ['code' => '5100', 'description' => 'Rentelasten', 'type' => 'winst_verlies', 'vat_default' => null],
            ['code' => '5110', 'description' => 'Koersverschillen', 'type' => 'winst_verlies', 'vat_default' => null],
        ];

        foreach ($accounts as $account) {
            LedgerAccount::create($account);
        }
    }
}
