<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdminUserSeeder extends Seeder
{
    /**
     * Create admin user and roles
     */
    public function run(): void
    {
        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $accountantRole = Role::firstOrCreate(['name' => 'accountant']);
        $boekhouderRole = Role::firstOrCreate(['name' => 'boekhouder']);
        $clientRole = Role::firstOrCreate(['name' => 'client']);
        
        // Create permissions
        $permissions = [
            'view_documents',
            'create_documents',
            'update_documents',
            'delete_documents',
            'approve_documents',
            'view_all_clients',
            'manage_ledger_accounts',
            'manage_btw_reports',
            'view_audit_logs',
        ];
        
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
        
        // Assign all permissions to admin
        $adminRole->givePermissionTo(Permission::all());
        $accountantRole->givePermissionTo(Permission::all());
        $boekhouderRole->givePermissionTo(Permission::all());
        
        // Client has limited permissions
        $clientRole->givePermissionTo(['view_documents', 'create_documents']);
        
        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@nlaccounting.nl'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
            ]
        );
        
        $admin->assignRole('admin');
        
        // Create demo accountant
        $accountant = User::firstOrCreate(
            ['email' => 'boekhouder@nlaccounting.nl'],
            [
                'name' => 'Boekhouder Demo',
                'password' => Hash::make('boekhouder123'),
                'email_verified_at' => now(),
            ]
        );
        
        $accountant->assignRole('boekhouder');
        
        $this->command->info('✅ Admin users created!');
        $this->command->info('📧 Email: admin@nlaccounting.nl | Password: admin123');
        $this->command->info('📧 Email: boekhouder@nlaccounting.nl | Password: boekhouder123');
    }
}
