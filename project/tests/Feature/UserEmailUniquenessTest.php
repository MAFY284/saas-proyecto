<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UserEmailUniquenessTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_have_a_unique_email_index(): void
    {
        $this->assertTrue(Schema::hasIndex('users', ['email'], 'unique'));
    }

    public function test_database_rejects_duplicate_user_emails(): void
    {
        User::factory()->create(['email' => 'duplicate@example.com']);

        $this->expectException(QueryException::class);

        User::factory()->create(['email' => 'duplicate@example.com']);
    }
}
