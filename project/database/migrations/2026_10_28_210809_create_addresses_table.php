<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * leagues.address_id apunta a addresses y addresses.league_id apunta a leagues.
     * La liga se crea antes; esta llave se agrega cuando addresses ya existe.
     */
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('league_id')->constrained('leagues');
            $table->string('name')->index();
            $table->foreignId('country_id')->constrained('countries');
            $table->foreignId('admin_division_id')->nullable()->constrained('admin_divisions');
            $table->foreignId('municipality_id')->nullable()->constrained('municipalities');
            $table->foreignId('locality_id')->nullable()->constrained('localities');
            $table->string('line_1');
            $table->string('line_2')->nullable();
            $table->string('postal_code', 20)->nullable()->index();
            $table->string('reference')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::table('leagues', function (Blueprint $table) {
            $table->foreign('address_id')->references('id')->on('addresses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leagues', function (Blueprint $table) {
            $table->dropForeign(['address_id']);
        });

        Schema::dropIfExists('addresses');
    }
};
