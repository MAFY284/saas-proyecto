<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['league_id', 'name']);
            $table->unique(['league_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
