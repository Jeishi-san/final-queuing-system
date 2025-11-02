<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();

            // Unique ticket identifier
            $table->string('ticket_number')->unique();

            // Description of the issue
            $table->text('issue_description')->nullable();

            // Ticket status (pending, in_progress, resolved)
            $table->enum('status', ['pending', 'in_progress', 'resolved'])
                ->default('pending')
                ->index();

            // Foreign keys
            $table->foreignId('agent_id')
                ->nullable()
                ->constrained('agents')
                ->nullOnDelete();

            $table->foreignId('team_leader_id')
                ->nullable()
                ->constrained('team_leaders')
                ->nullOnDelete();

            $table->foreignId('it_personnel_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('component_id')
                ->nullable()
                ->constrained('components')
                ->nullOnDelete();

            // Timestamps
            $table->timestamps();

            // Optional soft deletes for audit trail
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
