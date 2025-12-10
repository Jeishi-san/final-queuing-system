<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
    {
        // 1. Only add to 'tickets' if it doesn't exist yet
        if (Schema::hasTable('tickets') && !Schema::hasColumn('tickets', 'deleted_at')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // 2. Only add to 'queues' if it doesn't exist yet
        if (Schema::hasTable('queues') && !Schema::hasColumn('queues', 'deleted_at')) {
            Schema::table('queues', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('queues', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
