<?php

namespace App\Support;

use App\Models\Recipient;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * 設定画面で管理する値（settings テーブル）への型付きアクセス。
 */
class MailSettings
{
    public const DEFAULT_SEND_INTERVAL = 3;

    private const ARCHIVE_BCC = 'archive_bcc';

    private const SEND_INTERVAL = 'send_interval';

    private const SIGNATURE = 'signature';

    /**
     * 保存用BCCアドレス（未設定なら null）
     */
    public function archiveBcc(): ?string
    {
        return $this->read(self::ARCHIVE_BCC);
    }

    /**
     * 送信間隔（秒）
     */
    public function sendInterval(): int
    {
        return (int) ($this->read(self::SEND_INTERVAL) ?? self::DEFAULT_SEND_INTERVAL);
    }

    /**
     * 署名（フッター）。未設定なら null
     */
    public function signature(): ?string
    {
        return $this->read(self::SIGNATURE);
    }

    /**
     * 全メール共通の CC（環境変数 MAIL_CC）
     *
     * @return list<string>
     */
    public function commonCc(): array
    {
        return AddressList::parse(config()->string('mail.common_cc'));
    }

    /**
     * 全メール共通の BCC（環境変数 MAIL_BCC）
     *
     * @return list<string>
     */
    public function commonBcc(): array
    {
        return AddressList::parse(config()->string('mail.common_bcc'));
    }

    /**
     * 宛先1件に付ける CC / BCC を、宛先ごと・環境変数・保存用BCC から組み立てる。
     *
     * 同じアドレスに2通届かないよう、To と重なるものは除き、CC と BCC の両方にあるものは CC だけに残す。
     * 大文字・小文字は区別しない。
     *
     * @return array{cc: list<string>, bcc: list<string>}
     */
    public function copyAddressesFor(Recipient $recipient): array
    {
        $seen = [Str::lower($recipient->email) => true];

        $cc = $this->withoutSeen([...AddressList::parse($recipient->cc), ...$this->commonCc()], $seen);
        $bcc = $this->withoutSeen([
            ...AddressList::parse($recipient->bcc),
            ...$this->commonBcc(),
            ...AddressList::parse($this->archiveBcc()),
        ], $seen);

        return ['cc' => $cc, 'bcc' => $bcc];
    }

    public function update(?string $archiveBcc, int $sendInterval, string $signature): void
    {
        DB::transaction(function () use ($archiveBcc, $sendInterval, $signature) {
            $this->write(self::ARCHIVE_BCC, $archiveBcc);
            $this->write(self::SEND_INTERVAL, (string) $sendInterval);
            $this->write(self::SIGNATURE, $signature);
        });
    }

    /**
     * @param  list<string>  $addresses
     * @param  array<string, true>  $seen
     * @return list<string>
     */
    private function withoutSeen(array $addresses, array &$seen): array
    {
        $result = [];

        foreach ($addresses as $address) {
            $key = Str::lower($address);

            if (! isset($seen[$key])) {
                $seen[$key] = true;
                $result[] = $address;
            }
        }

        return $result;
    }

    private function read(string $key): ?string
    {
        $value = Setting::query()->find($key)?->value;

        return filled($value) ? $value : null;
    }

    private function write(string $key, ?string $value): void
    {
        Setting::query()->updateOrCreate(['key' => $key], ['value' => filled($value) ? $value : null]);
    }
}
