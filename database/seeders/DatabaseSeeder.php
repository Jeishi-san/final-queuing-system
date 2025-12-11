<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        // Truncate (clear) tables before seeding to prevent key conflicts if running multiple times
        // NOTE: This assumes you are running php artisan migrate:fresh or know the tables are empty.
        // DB::table('users')->truncate();
        // DB::table('tickets')->truncate();
        // DB::table('ticket_logs')->truncate();
        // DB::table('activity_logs')->truncate();

        // -----------------------
        // Users
        // -----------------------
        DB::table('users')->insert([
            // 🔑 1. SUPER ADMIN ACCOUNT (Added)
            [
                'name' => 'Super Admin',
                'email' => 'super.admin@cnx.test',
                'password' => Hash::make('password'),
                'image' => 'super_admin.jpg',
                'employee_id' => 'SUPA001',
                'role' => 'super_admin', // Use the new role key
                'department' => 'Management',
                'contact_number' => '09991234567',
                'account_status' => 'active',
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now
            ]
        ]);
    }
}