<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class JapaneseLocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_in_japanese(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('メールアドレスとパスワードを入力してください')
            ->assertSee('ログインしたままにする')
            ->assertDontSee('Remember me');
    }

    public function test_failed_login_message_is_in_japanese(): void
    {
        $user = User::factory()->create();

        $this->from(route('login'))
            ->post(route('login.store'), ['email' => $user->email, 'password' => 'wrong'])
            ->assertSessionHasErrors(['email' => 'メールアドレスまたはパスワードが正しくありません。']);
    }

    public function test_validation_messages_use_japanese_attribute_names(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::settings.profile')
            ->set('name', '')
            ->set('email', 'not-an-email')
            ->call('updateProfileInformation')
            ->assertHasErrors([
                'name' => '名前を入力してください。',
                'email' => 'メールアドレスの形式が正しくありません。例：taro@example.com',
            ]);
    }
}
