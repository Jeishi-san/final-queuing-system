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
        Schema::create('queue_logs', function (Blueprint $table) {
            $table->id();

            // Foreign key to tickets
            $table->foreignId('ticket_id')->constrained('tickets')->onDelete('cascade');

            // Foreign key to IT personnel handling the ticket
            $table->foreignId('it_personnel_id')->nullable()->constrained('users')->onDelete('set null');

            // Queue action status
            $table->enum('action', ['queued', 'assigned', 'in_progress', 'resolved', 'closed'])->default('queued');

            // Optional notes about the action taken
            $table->text('remarks')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queue_logs');
    }
};
