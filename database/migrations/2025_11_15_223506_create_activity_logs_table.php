<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            // IT user who performed the action
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            // ✅ CRITICAL FIX: Add the link to the tickets table
            $table->foreignId('ticket_id')
                  ->nullable()
                  ->constrained('tickets')
                  ->cascadeOnDelete(); // Cascade delete if the ticket is removed

            $table->string('action');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('activity_logs');
    }
};