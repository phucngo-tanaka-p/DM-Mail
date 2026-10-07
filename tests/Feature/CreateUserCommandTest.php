<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateUserCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_user(): void
    {
        $this->artisan('user:create', [
            '--name' => '田中 太郎',
            '--email' => 'tanaka@example.com',
            '--password' => 'secret-password',
        ])->assertSuccessful();

        $user = User::where('email', 'tanaka@example.com')->firstOrFail();
        $this->assertSame('田中 太郎', $user->name);
        $this->assertTrue(Hash::check('secret-password', $user->password));
    }

    public function test_it_rejects_a_duplicate_email(): void
    {
        User::factory()->create(['email' => 'tanaka@example.com']);

        $this->artisan('user:create', [
            '--name' => '田中',
            '--email' => 'tanaka@example.com',
            '--password' => 'secret-password',
        ])->assertFailed();

        $this->assertSame(1, User::where('email', 'tanaka@example.com')->count());
    }
}
