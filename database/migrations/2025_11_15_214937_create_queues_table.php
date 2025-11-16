<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('queues', function (Blueprint $table) {
            $table->id();
            $table->string('queue_number')->unique();
            $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
            $table->foreignId('assigned_to')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('queue_logs');
    }
};
