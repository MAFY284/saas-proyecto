<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('municipalities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_division_id')->constrained('admin_divisions');
            $table->string('name');
            $table->string('code', 50)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['admin_division_id', 'name']);
            $table->unique(['admin_division_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('municipalities');
    }
};
