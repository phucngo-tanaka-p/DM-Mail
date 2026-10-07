<?php

namespace App\Enums;

enum SendJobStatus: string
{
    case Sending = 'sending';
    case Paused = 'paused';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Sending => '送信中',
            self::Paused => '一時停止中',
            self::Completed => '完了',
            self::Cancelled => '中止',
        };
    }
}
