<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();

            // Basic ticket info
            $table->string('ticket_number')->unique();
            $table->text('issue_description');

            // Ticket status
            $table->enum('status', ['pending', 'in_progress', 'resolved'])
                  ->default('pending');

            // Agent who reported the issue
            $table->foreignId('agent_id')
                  ->nullable()                          // agent may not always be known
                  ->constrained('agents')               // references agents table
                  ->nullOnDelete();                     // if agent deleted, set to NULL

            // Team leader associated with the agent
            $table->foreignId('team_leader_id')
                  ->nullable()                          // not always required
                  ->constrained('team_leaders')         // references team_leaders table
                  ->nullOnDelete();                     // if team leader deleted, set to NULL

            // IT personnel assigned to handle the ticket
            // We keep the column name it_personnel_id but link it to the users table
            $table->foreignId('it_personnel_id')
                  ->nullable()                          // allow NULL when ticket is unassigned
                  ->constrained('users')                // references users table (IT staff)
                  ->nullOnDelete();                     // if user is deleted, set FK to NULL

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
