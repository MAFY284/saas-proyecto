<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leagues', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('slug', 255);
            $table->string('short_name', 100)->nullable();
            $table->text('description')->nullable();
            $table->foreignId('country_id')->constrained('countries')->restrictOnDelete();
            $table->foreignId('currency_id')->constrained('currencies')->restrictOnDelete();
            $table->string('contact_email', 255)->nullable();
            $table->string('contact_phone', 50)->nullable();
            $table->string('logo_url', 500)->nullable();
            $table->string('website_url', 500)->nullable();
            $table->string('time_zone', 100)->default('UTC');
            $table->boolean('is_active')->default(1);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('address_id')->constrained('addresses')->restrictOnDelete();
            $table->string('name', 100);
            $table->string('address', 255)->nullable();
            $table->string('surface_type', 50)->nullable();
            $table->integer('capacity')->nullable();
            $table->text('operating_hours')->nullable();
            $table->string('status', 20)->default('available');
            $table->timestamps();
        });

        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('locality_id')->constrained('localities')->restrictOnDelete();
            $table->string('name', 255);
            $table->string('slug', 255);
            $table->string('short_name', 50)->nullable();
            $table->string('logo_url', 500)->nullable();
            $table->string('contact_email', 255)->nullable();
            $table->string('contact_phone', 50)->nullable();
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });

        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('person_data')->restrictOnDelete();
            $table->enum('type_id', ['player', 'coach', 'referee', 'own'])->nullable();
            $table->string('photo_url', 255)->nullable();
            $table->integer('goals')->nullable();
            $table->timestamps();
            $table->unique(['person_id', 'type_id'], 'players_index_0');
        });

        Schema::create('team_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
            $table->boolean('captain')->nullable();
            $table->integer('jersey_number')->nullable();
            $table->timestamps();
        });

        Schema::create('player_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('players');
            $table->foreignId('position_id')->constrained('positions')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_positions');
        Schema::dropIfExists('team_players');
        Schema::dropIfExists('players');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('fields');
        Schema::dropIfExists('leagues');
    }
};