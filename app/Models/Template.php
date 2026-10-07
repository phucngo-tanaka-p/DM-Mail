<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\TemplateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 件名・本文の定型文。差し込み項目は {{company}} {{name}} {{honorific}} {{custom.<名前>}} の形で保存する。
 *
 * @property int $id
 * @property string $name
 * @property string $subject
 * @property string $body_html
 * @property string $body_text
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['name', 'subject', 'body_html', 'body_text'])]
class Template extends Model
{
    /** @use HasFactory<TemplateFactory> */
    use HasFactory;

    /**
     * @return HasMany<SendJob, $this>
     */
    public function sendJobs(): HasMany
    {
        return $this->hasMany(SendJob::class);
    }
}
