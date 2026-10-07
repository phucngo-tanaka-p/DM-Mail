<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 一括送信の添付ファイル。
 *
 * @property int $id
 * @property int $send_job_id
 * @property string $path private ディスク上のパス
 * @property string $original_name
 * @property int $size バイト
 */
#[Fillable(['send_job_id', 'path', 'original_name', 'size'])]
#[WithoutTimestamps]
class SendJobAttachment extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<SendJob, $this>
     */
    public function sendJob(): BelongsTo
    {
        return $this->belongsTo(SendJob::class);
    }
}
