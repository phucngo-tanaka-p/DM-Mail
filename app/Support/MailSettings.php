<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\DB;

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

    public function update(?string $archiveBcc, int $sendInterval, string $signature): void
    {
        DB::transaction(function () use ($archiveBcc, $sendInterval, $signature) {
            $this->write(self::ARCHIVE_BCC, $archiveBcc);
            $this->write(self::SEND_INTERVAL, (string) $sendInterval);
            $this->write(self::SIGNATURE, $signature);
        });
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
