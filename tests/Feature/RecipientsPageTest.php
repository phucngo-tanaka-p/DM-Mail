<?php

namespace Tests\Feature;

use App\Models\Recipient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tests\TestCase;

class RecipientsPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_list_shows_recipients_with_custom_field_columns(): void
    {
        Recipient::factory()->create([
            'email' => 'tanaka@example.com',
            'company_name' => '田中商事',
            'custom_fields' => ['クーポンコード' => 'A7K2-9QXM'],
        ]);
        Recipient::factory()->unsubscribed()->create(['email' => 'stop@example.com']);

        $this->get(route('recipients.index'))
            ->assertOk()
            ->assertSee('全 2 件')
            ->assertSee('tanaka@example.com')
            ->assertSee('田中商事')
            ->assertSee('クーポンコード')
            ->assertSee('A7K2-9QXM')
            ->assertSee('配信停止')
            ->assertSee('表示： 1 〜 2 件 / 全 2 件')
            ->assertDontSee('Showing');
    }

    public function test_search_matches_columns_and_custom_field_values_only(): void
    {
        $tanaka = Recipient::factory()->create(['email' => 'tanaka@example.com', 'company_name' => '田中商事']);
        $suzuki = Recipient::factory()->create([
            'email' => 'suzuki@example.com',
            'company_name' => '鈴木工業',
            'custom_fields' => ['担当部署' => '営業部'],
        ]);

        $component = Livewire::test('pages::recipients.index');

        $component->set('search', '田中')->assertSee('tanaka@example.com')->assertDontSee('suzuki@example.com');
        $component->set('search', '営業')->assertSee('suzuki@example.com')->assertDontSee('tanaka@example.com');
        // 列名（担当部署）では一致しない
        $component->set('search', '担当部署')->assertDontSee('suzuki@example.com');
        $component->set('search', (string) $suzuki->id)->assertSee('suzuki@example.com')->assertDontSee('tanaka@example.com');
        $component->set('search', '')->assertSee($tanaka->email)->assertSee($suzuki->email);
        $component->set('search', '存在しない宛先')->assertSee('該当する宛先がありません。');
    }

    public function test_recipient_can_be_added_with_normalized_cc_and_custom_fields(): void
    {
        Recipient::factory()->create(['custom_fields' => ['クーポンコード' => 'OLD-1']]);

        Livewire::test('pages::recipients.index')
            ->call('create')
            ->assertSet('custom_names', ['クーポンコード'])
            ->set('email', ' new@example.com ')
            ->set('company_name', '新規株式会社')
            ->set('cc', 'a@example.com，b@example.com ; a@example.com')
            ->set('custom_values.0', 'NEW-2')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showFormModal', false);

        $recipient = Recipient::where('email', 'new@example.com')->firstOrFail();
        $this->assertSame('新規株式会社', $recipient->company_name);
        $this->assertSame('a@example.com;b@example.com', $recipient->cc);
        $this->assertSame('様', $recipient->honorific);
        $this->assertSame(['クーポンコード' => 'NEW-2'], $recipient->custom_fields);
    }

    public function test_recipient_can_be_edited(): void
    {
        $recipient = Recipient::factory()->create(['email' => 'old@example.com', 'custom_fields' => ['商品' => 'A']]);

        Livewire::test('pages::recipients.index')
            ->call('edit', $recipient->id)
            ->assertSet('email', 'old@example.com')
            ->assertSet('custom_values', ['A'])
            ->set('email', 'edited@example.com')
            ->set('honorific', '御中')
            ->set('custom_values.0', '')
            ->set('exclude', true)
            ->call('save')
            ->assertHasNoErrors();

        $recipient->refresh();
        $this->assertSame('edited@example.com', $recipient->email);
        $this->assertSame('御中', $recipient->honorific);
        $this->assertNull($recipient->custom_fields);
        $this->assertTrue($recipient->exclude);
    }

    public function test_validation_errors_are_in_japanese(): void
    {
        Livewire::test('pages::recipients.index')
            ->call('create')
            ->set('email', 'not-an-email')
            ->set('bcc', 'ok@example.com; wrong@')
            ->call('save')
            ->assertHasErrors([
                'email' => 'メールアドレスの形式が正しくありません。例：taro@example.com',
                'bcc' => 'BCCに正しくないメールアドレスがあります：wrong@',
            ])
            ->assertSet('showFormModal', true);

        $this->assertSame(0, Recipient::count());
    }

    public function test_exclude_can_be_toggled_from_the_list_except_for_unsubscribed(): void
    {
        $recipient = Recipient::factory()->create();
        $unsubscribed = Recipient::factory()->unsubscribed()->create();

        $component = Livewire::test('pages::recipients.index');

        $component->call('toggleExclude', $recipient->id);
        $this->assertTrue($recipient->fresh()->exclude);

        $component->call('toggleExclude', $recipient->id);
        $this->assertFalse($recipient->fresh()->exclude);

        $component->call('toggleExclude', $unsubscribed->id);
        $this->assertFalse($unsubscribed->fresh()->exclude);
    }

    public function test_recipient_is_deleted_only_after_confirmation(): void
    {
        $recipient = Recipient::factory()->create();

        $component = Livewire::test('pages::recipients.index')
            ->call('confirmDelete', $recipient->id)
            ->assertSet('showDeleteModal', true);

        $this->assertModelExists($recipient);

        $component->call('delete')->assertSet('showDeleteModal', false);

        $this->assertModelMissing($recipient);
    }

    public function test_deleting_the_last_row_of_the_last_page_goes_back_a_page(): void
    {
        Recipient::factory()->count(51)->create();
        $last = Recipient::query()->latest('id')->firstOrFail();

        Livewire::withQueryParams(['page' => 2])
            ->test('pages::recipients.index')
            ->assertSee($last->email)
            ->call('confirmDelete', $last->id)
            ->call('delete')
            ->assertSet('paginators.page', 1)
            ->assertDontSee('まだ宛先がありません');
    }

    public function test_custom_field_columns_follow_added_and_deleted_recipients(): void
    {
        $component = Livewire::test('pages::recipients.index')->assertSet('custom_names', []);

        $recipient = Recipient::factory()->create(['custom_fields' => ['クーポンコード' => 'A1']]);
        $component->call('create')->assertSet('custom_names', ['クーポンコード']);

        $component->call('confirmDelete', $recipient->id)->call('delete')->call('create')->assertSet('custom_names', []);
    }

    public function test_ids_used_by_actions_cannot_be_changed_from_the_browser(): void
    {
        $this->expectException(CannotUpdateLockedPropertyException::class);

        Livewire::test('pages::recipients.index')->set('deletingId', 1);
    }

    public function test_unsubscribe_can_be_lifted_from_the_edit_form(): void
    {
        $recipient = Recipient::factory()->unsubscribed()->create();

        Livewire::test('pages::recipients.index')
            ->call('edit', $recipient->id)
            ->assertSee('配信停止中')
            ->call('resubscribe')
            ->assertDontSee('配信停止中');

        $this->assertFalse($recipient->fresh()->isUnsubscribed());
    }
}
