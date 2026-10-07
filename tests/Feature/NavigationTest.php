<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

    private const PAGES = [
        'recipients.index' => '送信リスト',
        'templates.index' => 'テンプレート',
        'send.index' => '送信',
        'settings.index' => '設定',
    ];

    public function test_root_redirects_to_recipients(): void
    {
        $this->get('/')->assertRedirect('/recipients');
    }

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        foreach (array_keys(self::PAGES) as $route) {
            $this->get(route($route))->assertRedirect(route('login'));
        }
    }

    public function test_authenticated_users_can_visit_every_menu_page(): void
    {
        $this->actingAs(User::factory()->create());

        foreach (self::PAGES as $route => $heading) {
            $this->get(route($route))->assertOk()->assertSee($heading);
        }
    }

    public function test_registration_is_disabled(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register', [
            'name' => 'x',
            'email' => 'x@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertNotFound();
    }
}
