<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('match_sheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $table->foreignId('referee_id')->constrained('referees')->cascadeOnDelete();
            $table->foreignId('field_id')->constrained('fields')->cascadeOnDelete();
            $table->dateTime('match_date');
            $table->integer('local_score')->default(0);
            $table->integer('visitor_score')->default(0);
            $table->text('incidents')->nullable();
            $table->string('status', 50)->default('completed');
            $table->timestamps();
        });

        Schema::create('match_sheet_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_sheet_id')->constrained('match_sheets')->cascadeOnDelete();
            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->boolean('is_starter')->default(true);
            $table->integer('goals')->default(0);
            $table->integer('yellow_cards')->default(0);
            $table->integer('red_cards')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('match_sheet_players');
        Schema::dropIfExists('match_sheets');
    }
};