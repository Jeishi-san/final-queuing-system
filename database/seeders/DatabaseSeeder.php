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

        // -----------------------
        // Users
        // -----------------------
        DB::table('users')->insert([
            [
                'name' => 'Alice Reyes',
                'email' => 'alice@example.com',
                'password' => Hash::make('password'),
                'image' => 'alice.jpg',
                'employee_id' => 'EMP001',
                'role' => 'IT Staff',
                'department' => 'IT Operations',
                'contact_number' => '09171234567',
                'account_status' => 'active',
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Bob Santos',
                'email' => 'bob@example.com',
                'password' => Hash::make('password'),
                'image' => 'bob.jpg',
                'employee_id' => 'EMP002',
                'role' => 'IT Staff',
                'department' => 'IT Operations',
                'contact_number' => '09172345678',
                'account_status' => 'active',
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Charlie Dela Cruz',
                'email' => 'charlie@example.com',
                'password' => Hash::make('password'),
                'image' => 'charlie.jpg',
                'employee_id' => 'EMP003',
                'role' => 'IT Staff',
                'department' => 'IT Operations',
                'contact_number' => '09173456789',
                'account_status' => 'active',
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Diana Lopez',
                'email' => 'diana@example.com',
                'password' => Hash::make('password'),
                'image' => 'diana.jpg',
                'employee_id' => 'EMP004',
                'role' => 'IT Staff',
                'department' => 'IT Operations',
                'contact_number' => '09174567890',
                'account_status' => 'active',
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Edward Tan',
                'email' => 'edward@example.com',
                'password' => Hash::make('password'),
                'image' => 'edward.jpg',
                'employee_id' => 'EMP005',
                'role' => 'IT Staff',
                'department' => 'IT Operations',
                'contact_number' => '09175678901',
                'account_status' => 'active',
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now
            ],
        ]);

        // -----------------------
        // Tickets
        // -----------------------
        DB::table('tickets')->insert([
            [
                'holder_name' => 'Alice Reyes',
                'holder_email' => 'alice@example.com',
                'ticket_number' => 'TCKT001',
                'issue' => 'Computer won’t start',
                'status' => 'pending approval',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'holder_name' => 'Bob Santos',
                'holder_email' => 'bob@example.com',
                'ticket_number' => 'TCKT002',
                'issue' => 'Printer keeps jamming',
                'status' => 'pending approval',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'holder_name' => 'Charlie Dela Cruz',
                'holder_email' => 'charlie@example.com',
                'ticket_number' => 'TCKT003',
                'issue' => 'Cannot connect to Wi-Fi',
                'status' => 'pending approval',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'holder_name' => 'Diana Lopez',
                'holder_email' => 'diana@example.com',
                'ticket_number' => 'TCKT004',
                'issue' => 'Need Adobe installed',
                'status' => 'pending approval',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'holder_name' => 'Edward Tan',
                'holder_email' => 'edward@example.com',
                'ticket_number' => 'TCKT005',
                'issue' => 'Emails not coming to Outlook',
                'status' => 'pending approval',
                'created_at' => $now,
                'updated_at' => $now
            ],
        ]);

        // -----------------------
        // Ticket Logs
        // -----------------------
        DB::table('ticket_logs')->insert([
            ['ticket_id' => 1, 'user_id' => 1, 'action' => 'created ticket', 'created_at' => $now, 'updated_at' => $now],
            ['ticket_id' => 2, 'user_id' => 2, 'action' => 'created ticket', 'created_at' => $now, 'updated_at' => $now],
            ['ticket_id' => 3, 'user_id' => 3, 'action' => 'created ticket', 'created_at' => $now, 'updated_at' => $now],
            ['ticket_id' => 4, 'user_id' => 4, 'action' => 'created ticket', 'created_at' => $now, 'updated_at' => $now],
            ['ticket_id' => 5, 'user_id' => 5, 'action' => 'created ticket', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // -----------------------
        // Queue
        // -----------------------
        DB::table('queues')->insert([
            ['queue_number' => 'Q001', 'ticket_id' => 1, 'assigned_to' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['queue_number' => 'Q002', 'ticket_id' => 2, 'assigned_to' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['queue_number' => 'Q003', 'ticket_id' => 3, 'assigned_to' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['queue_number' => 'Q004', 'ticket_id' => 4, 'assigned_to' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['queue_number' => 'Q005', 'ticket_id' => 5, 'assigned_to' => 5, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // -----------------------
        // Activity Logs
        // -----------------------
        DB::table('activity_logs')->insert([
            ['user_id' => 1, 'ticket_id' => 1, 'action' => 'login', 'details' => 'User logged in', 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => 2, 'ticket_id' => 2, 'action' => 'update_ticket', 'details' => 'Updated ticket issue description', 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => 3, 'ticket_id' => 3, 'action' => 'create_ticket', 'details' => 'Created new ticket', 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => 4, 'ticket_id' => 4, 'action' => 'close_ticket', 'details' => 'Closed ticket after resolution', 'created_at' => $now, 'updated_at' => $now],
            ['user_id' => 5, 'ticket_id' => 5, 'action' => 'logout', 'details' => 'User logged out', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
