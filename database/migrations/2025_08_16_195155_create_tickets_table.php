<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();

            // 📌 Basic ticket info
            $table->string('ticket_number')->unique();
            $table->text('issue_description');

            // 📌 Ticket status
            $table->enum('status', ['pending', 'in_progress', 'resolved'])
                  ->default('pending');

            // 📌 Agent who reported the issue
            $table->foreignId('agent_id')
                  ->nullable()
                  ->constrained('agents')
                  ->nullOnDelete(); // If agent is deleted, keep ticket but set NULL

            // 📌 Team leader associated with the agent
            $table->foreignId('team_leader_id')
                  ->nullable()
                  ->constrained('team_leaders')
                  ->nullOnDelete(); // If leader deleted, set NULL

            // 📌 IT personnel assigned to handle the ticket
            $table->foreignId('it_personnel_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete(); // If IT personnel deleted, set NULL

            // ✅ NEW: Component (e.g., hardware or software involved)
            $table->foreignId('component_id')
                  ->nullable()
                  ->constrained('components')
                  ->nullOnDelete(); // If component deleted, set NULL

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
