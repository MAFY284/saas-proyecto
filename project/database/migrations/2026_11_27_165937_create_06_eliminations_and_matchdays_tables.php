<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eliminations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')
                ->constrained('tournaments')
                ->cascadeOnDelete();
            $table->string('name', 100);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->unsignedSmallInteger('order')->default(1);
            $table->string('status', 30)->default('scheduled');
            $table->timestamps();

            $table->unique(['tournament_id', 'name'], 'eliminations_tournament_name_unique');
        });

        Schema::create('matchdays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('elimination_id')
                ->constrained('eliminations')
                ->cascadeOnDelete();
            $table->string('name', 100);
            $table->date('date');
            $table->unsignedSmallInteger('order')->default(1);
            $table->string('status', 30)->default('scheduled');
            $table->timestamps();

            $table->unique(['elimination_id', 'name'], 'matchdays_elimination_name_unique');
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->foreignId('matchday_id')
                ->after('tournament_id')
                ->constrained('matchdays')
                ->cascadeOnDelete();

            $table->dropColumn('matchday');

            $table->unique(
                ['matchday_id', 'local_team_id', 'visitor_team_id'],
                'matches_matchday_teams_unique'
            );
        });

        Schema::table('tournament_teams', function (Blueprint $table) {
            $table->unique(['tournament_id', 'team_id'], 'tournament_teams_unique');
        });
    }

    public function down(): void
    {
        Schema::table('tournament_teams', function (Blueprint $table) {
            $table->dropUnique('tournament_teams_unique');
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->dropUnique('matches_matchday_teams_unique');
            $table->dropForeign(['matchday_id']);
            $table->dropColumn('matchday_id');
            $table->integer('matchday')->nullable()->after('visitor_goals');
        });

        Schema::dropIfExists('matchdays');
        Schema::dropIfExists('eliminations');
    }
};