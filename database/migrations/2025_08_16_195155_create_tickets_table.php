<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
       Schema::create('tickets', function (Blueprint $table) {
    $table->id();
    $table->string('ticket_number')->unique();
    $table->text('issue_description');
    $table->enum('status', ['pending', 'in_progress', 'resolved'])->default('pending');

    // Plain text names + emails
    $table->string('agent_name')->nullable();
    $table->string('agent_email')->nullable();
    $table->string('team_leader_name')->nullable();
    $table->string('team_leader_email')->nullable();

    $table->string('component_name')->nullable();
    $table->string('it_personnel_name')->nullable();

    $table->timestamps();
});
    }

    public function down(): void {
        Schema::dropIfExists('tickets');
    }
};
