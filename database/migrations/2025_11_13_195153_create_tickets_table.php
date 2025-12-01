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

            $table->string('holder_name'); // Name of the person who submitted the ticket (no system account)
            $table->string('holder_email'); // Email of the ticket holder, not unique
            $table->string('ticket_number')->unique(); // Ticket reference number (unique for every ticket)
            $table->text('issue'); // Issue being ticketed (asset replacement, troubleshoot, etc.)
            $table->enum('status', [
                'queued',
                'in progress',
                'on hold',
                'resolved',
                'cancelled',
                'pending approval',
                'dequeued'
            ])->default('pending approval'); // Ticket status

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
