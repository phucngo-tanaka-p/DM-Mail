<?php

namespace Tests\Feature;

use App\Enums\SendJobItemStatus;
use App\Enums\SendJobStatus;
use App\Models\Recipient;
use App\Models\SendJob;
use App\Models\SendJobItem;
use App\Models\Setting;
use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_recipient_defaults(): void
    {
        $recipient = Recipient::create(['email' => 'taro@example.com'])->refresh();

        $this->assertSame('様', $recipient->honorific);
        $this->assertFalse($recipient->exclude);
        $this->assertNull($recipient->unsubscribed_at);
        $this->assertNull($recipient->last_sent_at);
    }

    public function test_custom_fields_keep_japanese_column_names(): void
    {
        $recipient = Recipient::factory()->create([
            'custom_fields' => ['担当部署' => '営業部', '商品名' => 'DM送信システム'],
        ]);

        $this->assertSame(
            ['担当部署' => '営業部', '商品名' => 'DM送信システム'],
            $recipient->fresh()->custom_fields,
        );
    }

    public function test_sendable_scope_skips_excluded_and_unsubscribed(): void
    {
        $sendable = Recipient::factory()->create();
        Recipient::factory()->excluded()->create();
        $unsubscribed = Recipient::factory()->unsubscribed()->create();

        $this->assertSame([$sendable->id], Recipient::sendable()->pluck('id')->all());
        $this->assertTrue($unsubscribed->isUnsubscribed());
    }

    public function test_send_history_survives_template_and_recipient_deletion(): void
    {
        $template = Template::factory()->create(['subject' => '新製品のご案内']);
        $recipient = Recipient::factory()->create(['email' => 'tanaka@example.com']);
        $job = $this->createJobFrom($template, ['created_by' => User::factory()->create()->id]);
        $item = $job->items()->create(['recipient_id' => $recipient->id, 'email' => $recipient->email]);

        $template->delete();
        $recipient->delete();

        $job->refresh();
        $item->refresh();
        $this->assertNull($job->template_id);
        $this->assertNull($item->recipient_id);
        // 誰に・何を送ったかは残っている
        $this->assertSame('tanaka@example.com', $item->email);
        $this->assertSame('新製品のご案内', $job->subject);
        $this->assertSame($template->body_html, $job->body_html);
        $this->assertSame(SendJobStatus::Sending, $job->status);
        $this->assertSame(SendJobItemStatus::Pending, $item->status);
    }

    public function test_editing_template_does_not_change_a_started_job(): void
    {
        $template = Template::factory()->create(['subject' => '旧タイトル']);
        $job = $this->createJobFrom($template);

        $template->update(['subject' => '新タイトル']);

        $this->assertSame('旧タイトル', $job->fresh()->subject);
    }

    public function test_deleting_a_send_job_removes_its_items(): void
    {
        $job = $this->createJobFrom(Template::factory()->create(), ['status' => SendJobStatus::Completed]);
        $job->items()->create(['recipient_id' => Recipient::factory()->create()->id, 'email' => 'a@example.com']);

        $job->delete();

        $this->assertSame(0, SendJobItem::count());
    }

    public function test_setting_uses_string_key(): void
    {
        Setting::create(['key' => 'send_interval', 'value' => '3']);

        $this->assertSame('3', Setting::find('send_interval')->value);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createJobFrom(Template $template, array $attributes = []): SendJob
    {
        return SendJob::create([
            'template_id' => $template->id,
            'subject' => $template->subject,
            'body_html' => $template->body_html,
            'body_text' => $template->body_text,
            'status' => SendJobStatus::Sending,
            ...$attributes,
        ]);
    }
}
