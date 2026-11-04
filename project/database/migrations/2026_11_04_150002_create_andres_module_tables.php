<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('address_id')->nullable()->constrained('addresses')->nullOnDelete();
            $table->string('name', 100);
            $table->string('address', 255)->nullable();
            $table->string('surface_type', 50)->nullable();
            $table->integer('capacity')->nullable();
            $table->text('operating_hours')->nullable();
            $table->string('status', 20)->default('available');
            $table->timestamps();
        });

        Schema::create('tournament_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained('tournaments')->cascadeOnDelete();
            $table->foreignId('field_id')->constrained('fields')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['tournament_id', 'field_id']);
        });

        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->integer('jersey_number')->nullable();
            $table->string('position', 50)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('photo_url', 255)->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });

        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained('tournaments')->cascadeOnDelete();
            $table->foreignId('home_team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('away_team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('field_id')->constrained('fields')->cascadeOnDelete();
            $table->foreignId('referee_id')->nullable()->constrained('referees')->nullOnDelete();
            $table->dateTime('date_time');
            $table->integer('home_goals')->default(0);
            $table->integer('away_goals')->default(0);
            $table->integer('matchday')->nullable();
            $table->string('status', 20)->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('games');
        Schema::dropIfExists('players');
        Schema::dropIfExists('tournament_fields');
        Schema::dropIfExists('fields');
    }
};