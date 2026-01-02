<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * 
     * Complete setup for MARCOFIC NL Accounting System
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting MARCOFIC System Setup...');
        $this->command->info('');
        
        // 1. Grootboek (Chart of Accounts)
        $this->command->info('📊 Seeding Dutch Grootboek (90+ accounts)...');
        $this->call(LedgerAccountSeeder::class);
        
        // 2. Keyword Mappings (for intelligent suggestions)
        $this->command->info('🧠 Seeding Keyword Mappings (72+ rules)...');
        $this->call(KeywordMappingsSeeder::class);
        
        // 3. Admin Users & Roles
        $this->command->info('👥 Creating Admin Users & Roles...');
        $this->call(AdminUserSeeder::class);
        
        // 4. Demo Clients (for testing)
        $this->command->info('🏢 Creating Demo MARCOFIC Clients...');
        $this->call(DemoClientSeeder::class);
        
        $this->command->info('');
        $this->command->info('✅ MARCOFIC System Setup Complete!');
        $this->command->info('');
        $this->command->info('🔐 Admin Login: http://localhost:8000/admin');
        $this->command->info('   Email: boekhouder@nlaccounting.nl');
        $this->command->info('   Password: boekhouder123');
        $this->command->info('');
        $this->command->info('📱 Klanten Portaal: http://localhost:8000/klanten');
        $this->command->info('   Test: jan@goudenlepel.nl / demo123');
        $this->command->info('');
        $this->command->info('📸 Camera upload werkt op mobiele telefoons!');
        $this->command->info('🚀 Ready to revolutionize bookkeeping!');
    }
}

