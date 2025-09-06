<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();

            // Plain text fields instead of foreign keys
            $table->string('agent_name');
            $table->string('agent_email');
            $table->string('team_leader_name');
            $table->string('component');

            // Issue details
            $table->text('issue_description');

            // Status
            $table->string('status')->default('pending');

            // Assigned IT personnel stored as text
            $table->string('it_personnel_name')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
