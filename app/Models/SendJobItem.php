<?php

namespace App\Models;

use App\Enums\SendJobItemStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 一括送信の宛先1件分の結果。
 *
 * @property int $id
 * @property int $send_job_id
 * @property int|null $recipient_id
 * @property string $email ジョブ作成時点の宛先アドレス
 * @property SendJobItemStatus $status
 * @property string|null $error_message
 * @property CarbonImmutable|null $sent_at
 */
#[Fillable(['send_job_id', 'recipient_id', 'email', 'status', 'error_message', 'sent_at'])]
#[WithoutTimestamps]
class SendJobItem extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => SendJobItemStatus::class,
            'sent_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<SendJob, $this>
     */
    public function sendJob(): BelongsTo
    {
        return $this->belongsTo(SendJob::class);
    }

    /**
     * @return BelongsTo<Recipient, $this>
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(Recipient::class);
    }
}
