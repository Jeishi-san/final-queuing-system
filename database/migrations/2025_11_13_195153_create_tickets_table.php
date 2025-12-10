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

            // ✅ RECOMMENDED: Allow these to be null so the DB doesn't crash on empty input
            $table->string('holder_name')->nullable(); 
            $table->string('holder_email')->nullable(); 
            
            $table->string('ticket_number')->unique();
            
            // ✅ RECOMMENDED: Allow issue to be empty (optional)
            $table->text('issue')->nullable(); 
            
            $table->enum('status', [
                'queued',
                'in progress',
                'on hold',
                'resolved',
                'cancelled',
                'pending approval',
                'dequeued'
            ])->default('pending approval');

            // ✅ HIGHLY RECOMMENDED: Link to the User table (Foreign Key)
            // This lets you find all tickets belonging to a specific User ID easily
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete(); 
            
            // Optional: If you want to track who REQUESTED the ticket by ID (not just name)
            // $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamps();
            $table->softDeletes(); // Optional: Allows you to "restore" deleted tickets
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