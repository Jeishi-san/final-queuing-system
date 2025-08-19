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
            $table->string('ticket_number')->unique();

            // Store agent and team leader as plain text
            $table->string('agent_name'); 
            $table->string('team_leader_name');

            // Component as text
            $table->string('component');

            // Issue description
            $table->text('issue_description');

            // Status (Open, In Progress, Resolved, etc.)
            $table->string('status')->default('Pending');

            // Optional: IT Personnel handling the ticket (can stay nullable foreign key)
            $table->foreignId('it_personnel_id')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
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
