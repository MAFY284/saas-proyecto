<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('referee_cards', function (Blueprint $table) {
            $table->unsignedSmallInteger('local_score')->nullable()->after('match_date');
            $table->unsignedSmallInteger('visitor_score')->nullable()->after('local_score');
            $table->unsignedBigInteger('referee1')->nullable()->after('visitor_score');
            $table->unsignedBigInteger('referee2')->nullable()->after('referee1');
            $table->unsignedBigInteger('referee3')->nullable()->after('referee2');
            $table->unsignedBigInteger('referee4')->nullable()->after('referee3');
        });

        Schema::table('referee_card_players', function (Blueprint $table) {
            $table->boolean('is_starter')->default(true)->after('player_id');
        });
    }

    public function down(): void
    {
        Schema::table('referee_card_players', function (Blueprint $table) {
            $table->dropColumn('is_starter');
        });

        Schema::table('referee_cards', function (Blueprint $table) {
            $table->dropColumn(['local_score', 'visitor_score', 'referee1', 'referee2', 'referee3', 'referee4']);
        });
    }
};
