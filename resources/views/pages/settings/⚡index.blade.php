<?php

use App\Support\MailSettings;
use Flux\Flux;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('設定')] class extends Component
{
    public string $archive_bcc = '';

    public string $send_interval = '';

    public string $signature = '';

    public bool $hasSignature = false;

    public function mount(MailSettings $settings): void
    {
        $this->archive_bcc = $settings->archiveBcc() ?? '';
        $this->send_interval = (string) $settings->sendInterval();
        $signature = $settings->signature();

        $this->signature = $signature ?? '';
        $this->hasSignature = $signature !== null;
    }

    /**
     * @return list<string>
     */
    #[Computed]
    public function commonCc(): array
    {
        return app(MailSettings::class)->commonCc();
    }

    /**
     * @return list<string>
     */
    #[Computed]
    public function commonBcc(): array
    {
        return app(MailSettings::class)->commonBcc();
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'archive_bcc' => ['nullable', 'email', 'max:255'],
            'send_interval' => ['required', 'integer', 'min:1', 'max:60'],
            'signature' => ['required', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'archive_bcc' => '保存用BCCアドレス',
            'send_interval' => '送信間隔',
            'signature' => '署名',
        ];
    }

    public function save(MailSettings $settings): void
    {
        // 全角スペースも取り除く
        $this->archive_bcc = Str::trim($this->archive_bcc);
        $this->signature = Str::trim($this->signature);

        $validated = $this->validate();

        $settings->update(
            archiveBcc: $validated['archive_bcc'],
            sendInterval: (int) $validated['send_interval'],
            signature: $validated['signature'],
        );

        $this->hasSignature = true;

        Flux::toast(variant: 'success', text: '設定を保存しました。');
    }
};
?>

<section class="w-full max-w-2xl">
    <flux:heading size="xl" level="1">設定</flux:heading>
    <flux:text class="mt-2">すべてのメール送信に共通する設定です。</flux:text>

    @unless ($hasSignature)
        <flux:callout variant="warning" icon="exclamation-triangle" class="mt-6">
            <flux:callout.heading>署名が未設定です</flux:callout.heading>
            <flux:callout.text>署名を保存するまで、メールは送信できません。</flux:callout.text>
        </flux:callout>
    @endunless

    <form wire:submit="save" class="mt-8 space-y-8">
        <flux:textarea
            wire:model="signature"
            label="署名（フッター）"
            description="すべてのメールの最後に入ります。特定電子メール法により、会社名・住所・連絡先の記載が必要です。"
            rows="6"
            placeholder="株式会社〇〇&#10;〒100-0001 東京都千代田区〇〇 1-2-3&#10;TEL：03-0000-0000　Email：info@example.com"
            data-test="signature"
        />

        <flux:field>
            <flux:label>送信間隔</flux:label>
            <flux:description>1通送るごとに待つ秒数です。メールサーバーの送信制限に合わせて調整してください（1〜60秒）。</flux:description>
            <flux:input.group class="max-w-48">
                <flux:input wire:model="send_interval" type="number" min="1" max="60" step="1" data-test="send-interval" />
                <flux:input.group.suffix>秒</flux:input.group.suffix>
            </flux:input.group>
            <flux:error name="send_interval" />
        </flux:field>

        <flux:input
            wire:model="archive_bcc"
            type="email"
            label="保存用BCCアドレス"
            badge="任意"
            description="送信したメールの控えをBCCで受け取るアドレスです。空欄の場合、控えは送りません。"
            placeholder="archive@example.com"
            data-test="archive-bcc"
        />

        <flux:button type="submit" variant="primary" data-test="save-settings">保存する</flux:button>
    </form>

    @if ($this->commonCc !== [] || $this->commonBcc !== [])
        <flux:callout icon="information-circle" class="mt-10" data-test="common-addresses">
            <flux:callout.heading>すべてのメールに付くアドレス（環境変数で設定）</flux:callout.heading>
            <flux:callout.text>
                @if ($this->commonCc !== [])
                    CC：{{ implode('、', $this->commonCc) }}<br>
                @endif
                @if ($this->commonBcc !== [])
                    BCC：{{ implode('、', $this->commonBcc) }}<br>
                @endif
                上の保存用BCCと併せて送信されます。変更はサーバー管理者にご依頼ください。
            </flux:callout.text>
        </flux:callout>
    @endif
</section>
