        <?php

        use Illuminate\Database\Migrations\Migration;
        use Illuminate\Database\Schema\Blueprint;
        use Illuminate\Support\Facades\Schema;

        return new class extends Migration {
            public function up(): void {
                Schema::create('agents', function (Blueprint $table) {
                    $table->id();
                    $table->string('name');
                    $table->string('email')->unique();

                    // Each agent belongs to a team leader (can be NULL)
                    $table->foreignId('team_leader_id')
                        ->nullable()
                        ->constrained('team_leaders')
                        ->nullOnDelete();

                    $table->timestamps();
                });
            }

            public function down(): void {
                Schema::dropIfExists('agents');
            }
        };
