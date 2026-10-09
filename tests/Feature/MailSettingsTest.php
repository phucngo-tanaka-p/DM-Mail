<?php

namespace Tests\Feature;

use App\Models\Recipient;
use App\Support\MailSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MailSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_common_addresses_are_read_from_env_with_either_separator(): void
    {
        config([
            'mail.common_cc' => 'boss@example.com, sales@example.com',
            'mail.common_bcc' => 'log@example.com;audit@example.com',
        ]);

        $settings = app(MailSettings::class);

        $this->assertSame(['boss@example.com', 'sales@example.com'], $settings->commonCc());
        $this->assertSame(['log@example.com', 'audit@example.com'], $settings->commonBcc());
    }

    public function test_no_common_addresses_when_env_is_empty(): void
    {
        config(['mail.common_cc' => '', 'mail.common_bcc' => '']);

        $recipient = Recipient::factory()->make(['cc' => null, 'bcc' => null]);

        $this->assertSame(['cc' => [], 'bcc' => []], app(MailSettings::class)->copyAddressesFor($recipient));
    }

    public function test_copy_addresses_merge_recipient_env_and_archive_without_duplicates(): void
    {
        config([
            'mail.common_cc' => 'boss@example.com;Shared@example.com',
            'mail.common_bcc' => 'log@example.com;BOSS@example.com',
        ]);
        app(MailSettings::class)->update('archive@example.com', 3, '株式会社テスト');

        $recipient = Recipient::factory()->make([
            'email' => 'taro@example.com',
            'cc' => 'shared@example.com;TARO@example.com',
            'bcc' => 'archive@example.com;private@example.com',
        ]);

        $this->assertSame([
            // To と同じアドレスは除き、大文字・小文字違いは同じものとして扱う
            'cc' => ['shared@example.com', 'boss@example.com'],
            // CC に入ったアドレスは BCC に重ねない
            'bcc' => ['archive@example.com', 'private@example.com', 'log@example.com'],
        ], app(MailSettings::class)->copyAddressesFor($recipient));
    }
}
