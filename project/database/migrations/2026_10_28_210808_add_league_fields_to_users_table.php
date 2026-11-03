<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Amplía la tabla users de Laravel con los campos de ligas3.sql.
     * El email sigue siendo único. No se crea otra tabla users.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('user_type_id')->nullable()->after('id');
            $table->foreignId('language_id')->nullable()->after('user_type_id');
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('phone', 50)->nullable()->after('password');
            $table->string('time_zone', 100)->default('UTC')->after('phone');
            $table->boolean('is_active')->default(true)->after('time_zone');
        });

        $users = DB::table('users')
            ->where(function ($query) {
                $query->whereNull('first_name')->orWhereNull('last_name');
            })
            ->get(['id', 'name', 'first_name', 'last_name']);

        foreach ($users as $user) {
            $parts = preg_split('/\s+/', trim((string) $user->name), 2) ?: [];
            $firstName = ($parts[0] ?? '') !== '' ? $parts[0] : 'Usuario';
            $lastName = ($parts[1] ?? '') !== '' ? $parts[1] : $firstName;

            DB::table('users')->where('id', $user->id)->update([
                'first_name' => $user->first_name ?: $firstName,
                'last_name' => $user->last_name ?: $lastName,
            ]);
        }

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE users MODIFY first_name VARCHAR(255) NOT NULL');
            DB::statement('ALTER TABLE users MODIFY last_name VARCHAR(255) NOT NULL');
        }

        Schema::table('users', function (Blueprint $table) {
            $table->index('time_zone');
            $table->foreign('user_type_id')->references('id')->on('user_types');
            $table->foreign('language_id')->references('id')->on('languages');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['time_zone']);
            $table->dropForeign(['user_type_id']);
            $table->dropForeign(['language_id']);
            $table->dropColumn([
                'user_type_id',
                'language_id',
                'first_name',
                'last_name',
                'phone',
                'time_zone',
                'is_active',
            ]);
        });
    }
};
