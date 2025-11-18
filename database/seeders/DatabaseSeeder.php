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
            [
                'name' => 'Fiona Cruz',
                'email' => 'fiona@example.com',
                'password' => Hash::make('password'),
                'image' => 'fiona.jpg',
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
            [
                'name' => 'Gideon Lim',
                'email' => 'gideon.lim@example.com',
                'password' => Hash::make('password'),
                'image' => 'gideon_lim.jpg',
                'employee_id' => 'EMP007',
                'role' => 'IT Staff',
                'department' => 'IT Operations',
                'contact_number' => '09182345678',
                'account_status' => 'active',
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Hannah Reyes',
                'email' => 'hannah.reyes@example.com',
                'password' => Hash::make('password'),
                'image' => 'hannah_reyes.jpg',
                'employee_id' => 'EMP008',
                'role' => 'IT Staff',
                'department' => 'IT Operations',
                'contact_number' => '09183456789',
                'account_status' => 'active',
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Ian Santos',
                'email' => 'ian.santos@example.com',
                'password' => Hash::make('password'),
                'image' => 'ian_santos.jpg',
                'employee_id' => 'EMP009',
                'role' => 'IT Staff',
                'department' => 'IT Operations',
                'contact_number' => '09184567890',
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
                'ticket_number' => 'INC000012496537',
                'issue' => 'Computer won’t start',
                'status' => 'in progress',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'holder_name' => 'Bob Santos',
                'holder_email' => 'bob@example.com',
                'ticket_number' => 'INC000012496532',
                'issue' => 'Printer keeps jamming',
                'status' => 'in progress',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'holder_name' => 'Charlie Dela Cruz',
                'holder_email' => 'charlie@example.com',
                'ticket_number' => 'INC000012496516',
                'issue' => 'Cannot connect to Wi-Fi',
                'status' => 'pending approval',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'holder_name' => 'Diana Lopez',
                'holder_email' => 'diana@example.com',
                'ticket_number' => 'INC000012496531',
                'issue' => 'Need Adobe installed',
                'status' => 'in progress',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'holder_name' => 'Edward Tan',
                'holder_email' => 'edward@example.com',
                'ticket_number' => 'INC000012496154',
                'issue' => 'Emails not coming to Outlook',
                'status' => 'pending approval',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'holder_name' => 'George Lim',
                'holder_email' => 'george@example.com',
                'ticket_number' => 'INC000012498002',
                'issue' => 'Cannot access shared network drive',
                'status' => 'queued',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'holder_name' => 'Fiona Cruz',
                'holder_email' => 'fiona@example.com',
                'ticket_number' => 'INC000012498001',
                'issue' => 'Laptop battery not charging',
                'status' => 'queued',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'holder_name' => 'Ian Santos',
                'holder_email' => 'ian@example.com',
                'ticket_number' => 'INC000012498004',
                'issue' => 'Software installation request',
                'status' => 'queued',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'holder_name' => 'Jessica Tan',
                'holder_email' => 'jessica@example.com',
                'ticket_number' => 'INC000012498005',
                'issue' => 'VPN connection failing intermittently',
                'status' => 'queued',
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
            ['queue_number' => 'DAM073010', 'ticket_id' => 1, 'assigned_to' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['queue_number' => 'DAM073011', 'ticket_id' => 2, 'assigned_to' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['queue_number' => 'DAM073012', 'ticket_id' => 3, 'assigned_to' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['queue_number' => 'DAM073013', 'ticket_id' => 4, 'assigned_to' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['queue_number' => 'DAM073014', 'ticket_id' => 5, 'assigned_to' => 5, 'created_at' => $now, 'updated_at' => $now],
            ['queue_number' => 'DAM073015', 'ticket_id' => 10, 'assigned_to' => 6, 'created_at' => $now, 'updated_at' => $now],
            ['queue_number' => 'DAM073016', 'ticket_id' => 11, 'assigned_to' => 7, 'created_at' => $now, 'updated_at' => $now],
            ['queue_number' => 'DAM073017', 'ticket_id' => 12, 'assigned_to' => 8, 'created_at' => $now, 'updated_at' => $now],
            ['queue_number' => 'DAM073018', 'ticket_id' => 13, 'assigned_to' => 9, 'created_at' => $now, 'updated_at' => $now],

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
