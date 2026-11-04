<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('referees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('league_id')->constrained('leagues')->cascadeOnDelete();
            $table->foreignId('locality_id')->constrained('localities')->cascadeOnDelete();
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->string('middle_name', 50)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('phone', 15)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('license_number', 30);
            $table->integer('years_experience')->default(0);
            $table->string('category', 30)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['league_id', 'email']);
            $table->unique(['league_id', 'license_number']);
        });

        Schema::create('referee_tournaments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referee_id')->constrained('referees')->cascadeOnDelete();
            $table->foreignId('tournament_id')->constrained('tournaments')->cascadeOnDelete();
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamps();
            $table->unique(['referee_id', 'tournament_id']);
        });

        Schema::create('coaches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('league_id')->constrained('leagues')->cascadeOnDelete();
            $table->foreignId('locality_id')->constrained('localities')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->string('middle_name', 50)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('phone', 15)->nullable();
            $table->string('email', 100)->nullable();
            $table->integer('years_experience')->default(0);
            $table->string('specialty', 50)->nullable();
            $table->string('license_number', 30);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('team_owners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('league_id')->constrained('leagues')->cascadeOnDelete();
            $table->foreignId('locality_id')->constrained('localities')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->string('middle_name', 50)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('phone', 15)->nullable();
            $table->string('email', 100)->nullable();
            $table->date('registration_date')->nullable();
            $table->timestamps();
        });

        Schema::create('sponsors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('league_id')->constrained('leagues')->cascadeOnDelete();
            $table->foreignId('locality_id')->nullable()->constrained('localities')->nullOnDelete();
            $table->string('name', 100)->index();
            $table->string('sponsor_type', 50)->nullable();
            $table->string('phone', 15)->nullable();
            $table->string('email', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('team_sponsors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sponsor_id')->constrained('sponsors')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->decimal('sponsorship_amount', 10, 2);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('team_sponsors');
        Schema::dropIfExists('sponsors');
        Schema::dropIfExists('team_owners');
        Schema::dropIfExists('coaches');
        Schema::dropIfExists('referee_tournaments');
        Schema::dropIfExists('referees');
    }
};