<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\MailSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SettingsPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_defaults_when_nothing_is_saved(): void
    {
        $settings = app(MailSettings::class);

        $this->assertNull($settings->archiveBcc());
        $this->assertSame(3, $settings->sendInterval());
        $this->assertNull($settings->signature());

        $this->get(route('settings.index'))
            ->assertOk()
            ->assertSee('署名が未設定です');

        Livewire::test('pages::settings.index')
            ->assertSet('send_interval', '3')
            ->assertSet('archive_bcc', '')
            ->assertSet('signature', '');
    }

    public function test_settings_can_be_saved(): void
    {
        Livewire::test('pages::settings.index')
            ->set('signature', "  株式会社テスト\n東京都千代田区1-2-3\nTEL 03-0000-0000  ")
            ->set('send_interval', '5')
            ->set('archive_bcc', ' archive@example.com ')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('hasSignature', true);

        $settings = app(MailSettings::class);
        $this->assertSame("株式会社テスト\n東京都千代田区1-2-3\nTEL 03-0000-0000", $settings->signature());
        $this->assertSame(5, $settings->sendInterval());
        $this->assertSame('archive@example.com', $settings->archiveBcc());

        $this->get(route('settings.index'))->assertDontSee('署名が未設定です');
    }

    public function test_archive_bcc_can_be_cleared(): void
    {
        app(MailSettings::class)->update('archive@example.com', 3, '株式会社テスト');

        Livewire::test('pages::settings.index')
            ->set('archive_bcc', '')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertNull(app(MailSettings::class)->archiveBcc());
    }

    public function test_validation_messages_are_in_japanese(): void
    {
        Livewire::test('pages::settings.index')
            ->set('signature', '   ')
            ->set('send_interval', '0')
            ->set('archive_bcc', 'not-an-email')
            ->call('save')
            ->assertHasErrors([
                'signature' => '署名を入力してください。',
                'send_interval' => '送信間隔には1以上の数値を入力してください。',
                'archive_bcc' => '保存用BCCアドレスの形式が正しくありません。例：taro@example.com',
            ]);

        $this->assertNull(app(MailSettings::class)->signature());
    }

    public function test_full_width_spaces_are_trimmed(): void
    {
        Livewire::test('pages::settings.index')
            ->set('signature', '　　')
            ->call('save')
            ->assertHasErrors(['signature' => 'required']);

        Livewire::test('pages::settings.index')
            ->set('signature', '　株式会社テスト　')
            ->set('archive_bcc', '　archive@example.com　')
            ->call('save')
            ->assertHasNoErrors();

        $settings = app(MailSettings::class);
        $this->assertSame('株式会社テスト', $settings->signature());
        $this->assertSame('archive@example.com', $settings->archiveBcc());
    }

    public function test_send_interval_must_be_a_whole_number_up_to_60(): void
    {
        Livewire::test('pages::settings.index')
            ->set('signature', '株式会社テスト')
            ->set('send_interval', '61')
            ->call('save')
            ->assertHasErrors(['send_interval' => 'max']);

        Livewire::test('pages::settings.index')
            ->set('signature', '株式会社テスト')
            ->set('send_interval', '1.5')
            ->call('save')
            ->assertHasErrors(['send_interval' => 'integer']);
    }
}
