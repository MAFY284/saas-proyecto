<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasIndex('users', ['email'], 'unique')) {
            return;
        }

        $hasDuplicateEmails = DB::table('users')
            ->select('email')
            ->groupBy('email')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($hasDuplicateEmails) {
            throw new \RuntimeException(
                'Cannot add the unique users.email constraint while duplicate email addresses exist. Resolve the duplicates and rerun the migration.'
            );
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->unique('email', 'users_email_unique_v2');
        });
    }

    public function down(): void
    {
        if (! Schema::hasIndex('users', 'users_email_unique_v2')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique('users_email_unique_v2');
        });
    }
};
