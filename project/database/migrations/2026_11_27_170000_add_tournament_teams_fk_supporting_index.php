<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TOURNAMENT_TEAMS_INDEX = 'tournament_teams_tournament_id_fk_support_index';
    private const MATCHES_INDEX = 'matches_matchday_id_fk_support_index';

    public function up(): void
    {
        if (! Schema::hasIndex('tournament_teams', 'tournament_teams_unique')) {
            Schema::table('tournament_teams', function (Blueprint $table): void {
                $table->unique(['tournament_id', 'team_id'], 'tournament_teams_unique');
            });
        }

        if (! Schema::hasIndex('tournament_teams', self::TOURNAMENT_TEAMS_INDEX)) {
            Schema::table('tournament_teams', function (Blueprint $table): void {
                $table->index('tournament_id', self::TOURNAMENT_TEAMS_INDEX);
            });
        }

        if (! Schema::hasIndex('matches', self::MATCHES_INDEX)) {
            Schema::table('matches', function (Blueprint $table): void {
                $table->index('matchday_id', self::MATCHES_INDEX);
            });
        }
    }

    public function down(): void
    {
        // Keep these indexes so the previous migration can drop its composite indexes without dropping the FKs.
    }
};
