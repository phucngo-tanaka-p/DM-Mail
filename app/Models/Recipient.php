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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

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

    private const CUSTOM_FIELD_NAMES_CACHE_KEY = 'recipients.custom_field_names';

    protected static function booted(): void
    {
        static::saved(function (Recipient $recipient) {
            if ($recipient->wasRecentlyCreated || $recipient->wasChanged('custom_fields')) {
                static::forgetCustomFieldNames();
            }
        });

        static::deleted(fn () => static::forgetCustomFieldNames());
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // 日本語の列名・値をそのまま保存し、検索できるようにする
            'custom_fields' => 'json:unicode',
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

    /**
     * 1つの検索欄で、No・アドレス・CC・BCC・会社名・名前・追加項目の値を部分一致で探す。
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function search(Builder $query, string $term): void
    {
        $term = Str::trim($term);

        if ($term === '') {
            return;
        }

        $pattern = "%{$term}%";

        $query->where(function (Builder $query) use ($term, $pattern) {
            foreach (['email', 'cc', 'bcc', 'company_name', 'person_name'] as $column) {
                $query->orWhereLike($column, $pattern);
            }

            if (ctype_digit($term)) {
                $query->orWhere('id', (int) $term);
            }

            // 追加項目は列名ではなく値だけを対象にする。
            // MySQL の json_search は大文字・小文字を区別するため、ほかの列とそろえて小文字同士で比べる
            match ($this->getConnection()->getDriverName()) {
                'sqlite' => $query->orWhereRaw(
                    'exists (select 1 from json_each(recipients.custom_fields) where json_each.value like ?)',
                    [$pattern],
                ),
                default => $query->orWhereRaw(
                    "json_search(lower(custom_fields), 'one', ?) is not null",
                    [mb_strtolower($pattern)],
                ),
            };
        });
    }

    /**
     * 追加項目の列名を、取り込み順（最初に現れた順）で返す。
     *
     * 全行を読むため結果はキャッシュする。モデルイベントを通らない一括 insert / delete の後は
     * forgetCustomFieldNames() を呼ぶこと。
     *
     * @return list<string>
     */
    public static function customFieldNames(): array
    {
        return Cache::rememberForever(self::CUSTOM_FIELD_NAMES_CACHE_KEY, function (): array {
            $names = [];

            static::query()
                ->whereNotNull('custom_fields')
                ->orderBy('id')
                ->pluck('custom_fields')
                ->each(function (?array $fields) use (&$names) {
                    foreach (array_keys($fields ?? []) as $name) {
                        $names[(string) $name] = true;
                    }
                });

            return array_map('strval', array_keys($names));
        });
    }

    public static function forgetCustomFieldNames(): void
    {
        Cache::forget(self::CUSTOM_FIELD_NAMES_CACHE_KEY);
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
