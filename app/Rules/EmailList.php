<?php

namespace App\Rules;

use App\Support\AddressList;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * 「;」区切りの複数メールアドレスがすべて正しい形式かを確認する。
 */
class EmailList implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail(':attributeの形式が正しくありません。');

            return;
        }

        foreach (AddressList::parse($value) as $address) {
            if (Validator::make(['address' => $address], ['address' => 'email'])->fails()) {
                $fail(":attributeに正しくないメールアドレスがあります：{$address}");

                return;
            }
        }
    }
}
