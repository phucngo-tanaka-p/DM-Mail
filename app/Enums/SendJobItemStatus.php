<?php

namespace App\Enums;

enum SendJobItemStatus: string
{
    case Pending = 'pending';
    case Sent = 'sent';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => '未送信',
            self::Sent => '送信済',
            self::Failed => '失敗',
        };
    }
}
