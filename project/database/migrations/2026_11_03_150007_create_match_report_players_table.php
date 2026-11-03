<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_report_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_report_id')->constrained('match_reports')->cascadeOnDelete();
            $table->foreignId('player_id')->constrained('players');
            $table->foreignId('team_id')->constrained('teams');
            $table->boolean('is_starter')->default(true);
            $table->unsignedSmallInteger('goals')->default(0);
            $table->unsignedSmallInteger('yellow_cards')->default(0);
            $table->unsignedSmallInteger('red_cards')->default(0);
            $table->timestamps();

            $table->unique(['match_report_id', 'player_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_report_players');
    }
};
