<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tournaments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('league_id')->constrained('leagues')->cascadeOnDelete();
            $table->string('name', 255);
            $table->string('season', 50)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('format', 50)->nullable();
            $table->string('status', 50)->default('active');
            $table->timestamps();
        });

        Schema::create('tournament_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained('tournaments')->cascadeOnDelete();
            $table->foreignId('field_id')->constrained('fields')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('tournament_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained('tournaments')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->restrictOnDelete();
            $table->string('group_name', 50)->nullable();
            $table->integer('points')->default(0);
            $table->timestamps();
        });

        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained('tournaments')->restrictOnDelete();
            $table->foreignId('local_team_id')->constrained('teams')->restrictOnDelete();
            $table->foreignId('visitor_team_id')->constrained('teams')->restrictOnDelete();
            $table->foreignId('field_id')->constrained('fields')->restrictOnDelete();
            $table->dateTime('date_time');
            $table->integer('local_goals')->default(0);
            $table->integer('visitor_goals')->default(0);
            $table->integer('matchday')->nullable();
            $table->string('status', 20)->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('match_sheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            $table->dateTime('match_date');
            $table->integer('local_score')->default(0);
            $table->integer('visitor_score')->default(0);
            $table->text('incidents')->nullable();
            $table->string('status', 50)->default('completed');
            $table->bigInteger('referee1');
            $table->bigInteger('referee2')->nullable();
            $table->bigInteger('referee3')->nullable();
            $table->bigInteger('referee4')->nullable();
            $table->timestamps();
        });

        Schema::create('match_sheet_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_sheet_id')->constrained('match_sheets')->cascadeOnDelete();
            $table->foreignId('player_id')->constrained('players')->restrictOnDelete();
            $table->boolean('is_starter')->default(1);
            $table->integer('goals')->default(0);
            $table->integer('yellow_cards')->default(0);
            $table->integer('red_cards')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_sheet_players');
        Schema::dropIfExists('match_sheets');
        Schema::dropIfExists('matches');
        Schema::dropIfExists('tournament_teams');
        Schema::dropIfExists('tournament_fields');
        Schema::dropIfExists('tournaments');
    }
};