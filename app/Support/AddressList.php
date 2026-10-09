<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * CC・BCC 欄の「複数アドレスを ; 区切りで入れた文字列」を扱う。
 *
 * 入力ゆれ（「,」や全角の「；」「，」「、」、改行、空白）も区切りとして受け付け、
 * 保存時は「a@example.com;b@example.com」の形にそろえる。
 */
class AddressList
{
    /**
     * @return list<string>
     */
    public static function parse(?string $value): array
    {
        $parts = preg_split('/[;,；，、\s]+/u', $value ?? '') ?: [];

        $addresses = array_filter(
            array_map(fn (string $part): string => Str::trim($part), $parts),
            fn (string $part): bool => $part !== '',
        );

        return array_values(array_unique($addresses));
    }

    public static function normalize(?string $value): ?string
    {
        $addresses = self::parse($value);

        return $addresses === [] ? null : implode(';', $addresses);
    }
}
