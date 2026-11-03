<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('locality_id')->nullable()->constrained('localities')->nullOnDelete();
            $table->foreignId('address_id')->nullable()->constrained('addresses')->nullOnDelete();
            $table->string('name', 150);
            $table->string('address', 255)->nullable();
            $table->string('surface_type', 50)->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->text('operating_hours')->nullable();
            $table->string('status', 20)->default('available')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venues');
    }
};
