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
                'name' => 'Jason Mercado',
                'email' => 'jason@example.com',
                'password' => Hash::make('password'),
                'image' => 'jason_mercado.jpg',
                'employee_id' => 'EMP010',
                'role' => 'IT Staff',
                'department' => 'IT Operations',
                'contact_number' => '09186671234',
                'account_status' => 'active',
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Katrina Villanueva',
                'email' => 'katrina@example.com',
                'password' => Hash::make('password'),
                'image' => 'katrina_villanueva.jpg',
                'employee_id' => 'EMP011',
                'role' => 'IT Staff',
                'department' => 'IT Operations',
                'contact_number' => '09187782345',
                'account_status' => 'active',
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Leonard Chua',
                'email' => 'leonard@example.com',
                'password' => Hash::make('password'),
                'image' => 'leonard_chua.jpg',
                'employee_id' => 'EMP012',
                'role' => 'IT Staff',
                'department' => 'IT Operations',
                'contact_number' => '09188893456',
                'account_status' => 'active',
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Marianne Go',
                'email' => 'marianne@example.com',
                'password' => Hash::make('password'),
                'image' => 'marianne_go.jpg',
                'employee_id' => 'EMP013',
                'role' => 'IT Staff',
                'department' => 'IT Operations',
                'contact_number' => '09189904567',
                'account_status' => 'active',
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Nathan Torres',
                'email' => 'nathan@example.com',
                'password' => Hash::make('password'),
                'image' => 'nathan_torres.jpg',
                'employee_id' => 'EMP014',
                'role' => 'IT Staff',
                'department' => 'IT Operations',
                'contact_number' => '09180015678',
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
                'holder_name' => 'Ramon Delgado',
                'holder_email' => 'ramon.delgado@example.com',
                'ticket_number' => 'INC000045821001',
                'issue' => 'Monitor flickering on startup',
                'status' => 'pending approval',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'holder_name' => 'Julia Manalili',
                'holder_email' => 'julia.manalili@example.com',
                'ticket_number' => 'INC000045821002',
                'issue' => 'Keyboard keys not responding',
                'status' => 'pending approval',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'holder_name' => 'Christian Uy',
                'holder_email' => 'christian.uy@example.com',
                'ticket_number' => 'INC000045821003',
                'issue' => 'PC randomly shuts down',
                'status' => 'pending approval',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'holder_name' => 'Melissa Fajardo',
                'holder_email' => 'melissa.fajardo@example.com',
                'ticket_number' => 'INC000045821004',
                'issue' => 'Mouse cursor freezing intermittently',
                'status' => 'pending approval',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'holder_name' => 'Patrick Salcedo',
                'holder_email' => 'patrick.salcedo@example.com',
                'ticket_number' => 'INC000045821005',
                'issue' => 'VPN disconnects every few minutes',
                'status' => 'pending approval',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'holder_name' => 'Angela Roque',
                'holder_email' => 'angela.roque@example.com',
                'ticket_number' => 'INC000045821006',
                'issue' => 'Unable to install required software',
                'status' => 'pending approval',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'holder_name' => 'Bryan Costales',
                'holder_email' => 'bryan.costales@example.com',
                'ticket_number' => 'INC000045821007',
                'issue' => 'System running unusually slow',
                'status' => 'pending approval',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'holder_name' => 'Celine Navarro',
                'holder_email' => 'celine.navarro@example.com',
                'ticket_number' => 'INC000045821008',
                'issue' => 'Email client not syncing',
                'status' => 'pending approval',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'holder_name' => 'Daryl Montano',
                'holder_email' => 'daryl.montano@example.com',
                'ticket_number' => 'INC000045821009',
                'issue' => 'Printer not detected on network',
                'status' => 'pending approval',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'holder_name' => 'Erika Santos',
                'holder_email' => 'erika.santos@example.com',
                'ticket_number' => 'INC000045821010',
                'issue' => 'Blue screen error during boot',
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
        // DB::table('queues')->insert([
        //     ['queue_number' => 'DAM073010', 'ticket_id' => 1, 'assigned_to' => 1, 'created_at' => $now, 'updated_at' => $now],
        //     ['queue_number' => 'DAM073011', 'ticket_id' => 2, 'assigned_to' => 2, 'created_at' => $now, 'updated_at' => $now],
        //     ['queue_number' => 'DAM073012', 'ticket_id' => 3, 'assigned_to' => 3, 'created_at' => $now, 'updated_at' => $now],
        //     ['queue_number' => 'DAM073013', 'ticket_id' => 4, 'assigned_to' => 4, 'created_at' => $now, 'updated_at' => $now],
        //     ['queue_number' => 'DAM073014', 'ticket_id' => 5, 'assigned_to' => 5, 'created_at' => $now, 'updated_at' => $now],
        //     ['queue_number' => 'DAM073015', 'ticket_id' => 10, 'assigned_to' => 6, 'created_at' => $now, 'updated_at' => $now],
        //     ['queue_number' => 'DAM073016', 'ticket_id' => 11, 'assigned_to' => 7, 'created_at' => $now, 'updated_at' => $now],
        //     ['queue_number' => 'DAM073017', 'ticket_id' => 12, 'assigned_to' => 8, 'created_at' => $now, 'updated_at' => $now],
        //     ['queue_number' => 'DAM073018', 'ticket_id' => 13, 'assigned_to' => 9, 'created_at' => $now, 'updated_at' => $now],
        // ]);

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
