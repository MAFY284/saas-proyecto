<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referee_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->restrictOnDelete();
            $table->foreignId('field_id')->constrained('fields')->restrictOnDelete();
            $table->string('city', 100);
            $table->date('match_date');
            $table->text('observations')->nullable();
            $table->timestamps();
            $table->unique('match_id', 'referee_cards_match_unique');
        });

        Schema::create('referee_card_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referee_card_id')->constrained('referee_cards')->cascadeOnDelete();
            $table->foreignId('player_id')->constrained('players')->restrictOnDelete();
            $table->integer('goals')->default(0);
            $table->integer('yellow_cards')->default(0);
            $table->integer('direct_red_cards')->default(0);
            $table->integer('indirect_red_cards')->default(0);
            $table->timestamps();
            $table->unique(['referee_card_id', 'player_id'], 'referee_card_players_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referee_card_players');
        Schema::dropIfExists('referee_cards');
    }
};
