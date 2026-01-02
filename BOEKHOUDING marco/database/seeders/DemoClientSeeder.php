<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoClientSeeder extends Seeder
{
    /**
     * Create demo MARCOFIC clients for testing
     */
    public function run(): void
    {
        // Create demo client 1: Restaurant
        $client1 = Client::create([
            'name' => 'Restaurant De Gouden Lepel BV',
            'email' => 'info@goudenlépel.nl',
            'kvk_number' => '12345678',
            'active' => true,
        ]);
        
        $user1 = User::create([
            'name' => 'Jan Jansen',
            'email' => 'jan@goudenlepel.nl',
            'password' => Hash::make('demo123'),
            'client_id' => $client1->id,
            'email_verified_at' => now(),
        ]);
        
        $user1->assignRole('client');
        
        // Create demo client 2: Tech Startup
        $client2 = Client::create([
            'name' => 'TechStart Nederland BV',
            'email' => 'info@techstart.nl',
            'kvk_number' => '87654321',
            'active' => true,
        ]);
        
        $user2 = User::create([
            'name' => 'Lisa de Vries',
            'email' => 'lisa@techstart.nl',
            'password' => Hash::make('demo123'),
            'client_id' => $client2->id,
            'email_verified_at' => now(),
        ]);
        
        $user2->assignRole('client');
        
        // Create demo client 3: Retail Shop
        $client3 = Client::create([
            'name' => 'Kledingwinkel Amsterdam',
            'email' => 'info@kledingwinkel-ams.nl',
            'kvk_number' => '11223344',
            'active' => true,
        ]);
        
        $user3 = User::create([
            'name' => 'Mohammed Ali',
            'email' => 'mo@kledingwinkel-ams.nl',
            'password' => Hash::make('demo123'),
            'client_id' => $client3->id,
            'email_verified_at' => now(),
        ]);
        
        $user3->assignRole('client');
        
        $this->command->info('✅ Demo MARCOFIC clients created!');
        $this->command->info('');
        $this->command->info('🏢 CLIENT 1: Restaurant De Gouden Lepel BV');
        $this->command->info('   Login: jan@goudenlepel.nl / demo123');
        $this->command->info('');
        $this->command->info('🏢 CLIENT 2: TechStart Nederland BV');
        $this->command->info('   Login: lisa@techstart.nl / demo123');
        $this->command->info('');
        $this->command->info('🏢 CLIENT 3: Kledingwinkel Amsterdam');
        $this->command->info('   Login: mo@kledingwinkel-ams.nl / demo123');
        $this->command->info('');
        $this->command->info('📱 Test de camera upload op: http://localhost:8000/klanten');
    }
}

