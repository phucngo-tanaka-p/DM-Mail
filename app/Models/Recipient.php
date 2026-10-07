<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\RecipientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * 送信リストの1行。
 *
 * @property int $id 画面上の「No」
 * @property string $email
 * @property string|null $cc 「;」区切り
 * @property string|null $bcc 「;」区切り
 * @property string|null $company_name
 * @property string|null $person_name
 * @property string $honorific
 * @property array<string, string>|null $custom_fields 追加項目（キー＝列見出し）
 * @property bool $exclude 送信しない
 * @property CarbonImmutable|null $unsubscribed_at 配信停止日
 * @property CarbonImmutable|null $last_sent_at 送信済
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['email', 'cc', 'bcc', 'company_name', 'person_name', 'honorific', 'custom_fields', 'exclude'])]
class Recipient extends Model
{
    /** @use HasFactory<RecipientFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'custom_fields' => 'array',
            'exclude' => 'boolean',
            'unsubscribed_at' => 'datetime',
            'last_sent_at' => 'datetime',
        ];
    }

    /**
     * 送信対象（「送信しない」でも「配信停止」でもない）のみに絞り込む。
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function sendable(Builder $query): void
    {
        $query->where('exclude', false)->whereNull('unsubscribed_at');
    }

    public function isUnsubscribed(): bool
    {
        return $this->unsubscribed_at !== null;
    }

    /**
     * @return HasMany<SendJobItem, $this>
     */
    public function sendJobItems(): HasMany
    {
        return $this->hasMany(SendJobItem::class);
    }
}
