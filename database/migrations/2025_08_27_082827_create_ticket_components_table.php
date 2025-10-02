<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ticket_components', function (Blueprint $table) {
            $table->id();

            // FK to tickets
            $table->foreignId('ticket_id')
                  ->constrained('tickets')
                  ->cascadeOnDelete();

            // FK to components
            $table->foreignId('component_id')
                  ->constrained('components')
                  ->cascadeOnDelete();

            // Quantity of the component used
            $table->integer('quantity')->default(1);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_components');
    }
};

