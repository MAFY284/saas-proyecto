<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->char('iso2_code', 2);
            $table->char('iso3_code', 3)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->char('code', 3);
            $table->string('symbol', 10)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('iso_code', 10);
            $table->string('native_name', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('admin_divisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('countries')->restrictOnDelete();
            $table->string('name', 255);
            $table->string('code', 50)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('municipalities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_division_id')->constrained('admin_divisions')->restrictOnDelete();
            $table->string('name', 255);
            $table->string('code', 50)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('localities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('municipality_id')->constrained('municipalities')->restrictOnDelete();
            $table->string('name', 255);
            $table->string('postal_code', 20)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->foreignId('country_id')->constrained('countries')->restrictOnDelete();
            $table->foreignId('admin_division_id')->constrained('admin_divisions')->restrictOnDelete();
            $table->foreignId('municipality_id')->constrained('municipalities')->restrictOnDelete();
            $table->foreignId('locality_id')->constrained('localities')->restrictOnDelete();
            $table->string('line_1', 255);
            $table->string('line_2', 255)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('reference', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('person_data', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->date('birth_date')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });

        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('positions');
        Schema::dropIfExists('person_data');
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('localities');
        Schema::dropIfExists('municipalities');
        Schema::dropIfExists('admin_divisions');
        Schema::dropIfExists('languages');
        Schema::dropIfExists('currencies');
        Schema::dropIfExists('countries');
    }
};