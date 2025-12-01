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
        Schema::table('tickets', function (Blueprint $table) {
            // Drop existing unique index
            $table->dropUnique('tickets_ticket_number_unique');

            // Add composite unique index (allows reuse after soft delete)
            $table->unique(['ticket_number', 'deleted_at'], 'tickets_ticket_deleted_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Remove composite index
            $table->dropUnique('tickets_ticket_deleted_unique');

            // Restore original unique index
            $table->unique('ticket_number', 'tickets_ticket_number_unique');
        });
    }
};
