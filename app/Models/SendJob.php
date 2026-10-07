<?php

namespace App\Models;

use App\Enums\SendJobStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 「一括送信」1回分。
 *
 * @property int $id
 * @property int|null $template_id
 * @property string $subject 送信開始時点のテンプレート内容
 * @property string $body_html 〃
 * @property string $body_text 〃
 * @property SendJobStatus $status
 * @property string|null $batch_id
 * @property int $total
 * @property int $sent
 * @property int $failed
 * @property int|null $created_by
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['template_id', 'subject', 'body_html', 'body_text', 'status', 'batch_id', 'total', 'sent', 'failed', 'created_by'])]
class SendJob extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => SendJobStatus::class,
            'total' => 'integer',
            'sent' => 'integer',
            'failed' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Template, $this>
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<SendJobItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(SendJobItem::class);
    }

    /**
     * @return HasMany<SendJobAttachment, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(SendJobAttachment::class);
    }
}
