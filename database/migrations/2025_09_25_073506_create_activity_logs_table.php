<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
Schema::create('activity_logs', function (Blueprint $table) {
    $table->id();

    // ✅ Make user_id nullable (guest submissions won’t have a user_id)
    $table->foreignId('user_id')
          ->nullable()
          ->constrained()
          ->nullOnDelete();

    $table->foreignId('ticket_id')
          ->nullable()
          ->constrained('tickets')
          ->nullOnDelete();

    $table->text('action');
    $table->timestamp('performed_at')->nullable();
    $table->timestamps();
});
    }

    public function down(): void {
        Schema::dropIfExists('activity_logs');
    }
};
