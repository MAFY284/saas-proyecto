<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
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

        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('league_id')->constrained('leagues')->cascadeOnDelete();
            $table->foreignId('locality_id')->nullable()->constrained('localities')->nullOnDelete();
            $table->string('name', 255);
            $table->string('slug', 255);
            $table->string('short_name', 50)->nullable();
            $table->string('logo_url', 500)->nullable();
            $table->string('coach_name', 255)->nullable();
            $table->string('contact_email', 255)->nullable();
            $table->string('contact_phone', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['tenant_id', 'name']);
            $table->unique(['tenant_id', 'slug']);
        });

        Schema::create('tournament_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained('tournaments')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->string('group_name', 50)->nullable();
            $table->integer('points')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('tournament_teams');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('tournaments');
    }
};