<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('queue_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ticket_id')
                  ->constrained('tickets')
                  ->cascadeOnDelete();

            $table->integer('queue_position')->nullable();
            $table->enum('status', ['waiting', 'in_queue', 'completed'])->default('waiting');
            $table->timestamp('logged_at')->useCurrent();

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('queue_logs');
    }
};
