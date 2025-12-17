<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Check if the user already exists to prevent duplication
        if (User::where('email', 'super.admin@cnx.test')->doesntExist()) {
            
            User::create([
                'name' => 'Super Admin',
                'email' => 'super.admin@cnx.test',
                
                // Use a strong, known password for the seeder
                'password' => Hash::make('password serve'), 
                
                // 🔑 CRITICAL: Set the role to super_admin
                'role' => 'super_admin',
            ]);
            
            $this->command->info('Super Admin user created successfully.');
        } else {
            $this->command->warn('Super Admin user already exists.');
        }
    }
}