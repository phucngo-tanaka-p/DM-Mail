<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | 利用者が「何を直せばよいか」が分かる言い回しにしています。
    |
    */

    'accepted' => ':attributeにチェックを入れてください。',
    'accepted_if' => ':otherが:valueの場合、:attributeにチェックを入れてください。',
    'active_url' => ':attributeには正しいURLを入力してください。',
    'after' => ':attributeには:dateより後の日付を入力してください。',
    'after_or_equal' => ':attributeには:date以降の日付を入力してください。',
    'alpha' => ':attributeは英字のみで入力してください。',
    'alpha_dash' => ':attributeは英数字・ハイフン・アンダースコアのみで入力してください。',
    'alpha_num' => ':attributeは英数字のみで入力してください。',
    'any_of' => ':attributeの値が正しくありません。',
    'array' => ':attributeの形式が正しくありません。',
    'array_keys' => ':attributeには次の項目のみ指定できます：:values',
    'ascii' => ':attributeは半角英数字と記号のみで入力してください。',
    'base64' => ':attributeの形式が正しくありません。',
    'before' => ':attributeには:dateより前の日付を入力してください。',
    'before_or_equal' => ':attributeには:date以前の日付を入力してください。',
    'between' => [
        'array' => ':attributeは:min〜:max件で指定してください。',
        'file' => ':attributeは:min〜:maxKBのファイルを選んでください。',
        'numeric' => ':attributeは:min〜:maxの数値を入力してください。',
        'string' => ':attributeは:min〜:max文字で入力してください。',
    ],
    'boolean' => ':attributeの値が正しくありません。',
    'can' => ':attributeに許可されていない値が含まれています。',
    'confirmed' => ':attributeが確認用と一致しません。もう一度入力してください。',
    'contains' => ':attributeに必要な値が含まれていません。',
    'current_password' => 'パスワードが正しくありません。',
    'date' => ':attributeには正しい日付を入力してください。',
    'date_equals' => ':attributeには:dateを入力してください。',
    'date_format' => ':attributeは「:format」の形式で入力してください。',
    'decimal' => ':attributeは小数点以下:decimal桁で入力してください。',
    'declined' => ':attributeのチェックを外してください。',
    'declined_if' => ':otherが:valueの場合、:attributeのチェックを外してください。',
    'different' => ':attributeと:otherには異なる値を入力してください。',
    'digits' => ':attributeは:digits桁の数字で入力してください。',
    'digits_between' => ':attributeは:min〜:max桁の数字で入力してください。',
    'dimensions' => ':attributeの画像サイズが正しくありません。',
    'distinct' => ':attributeに重複した値があります。',
    'doesnt_contain' => ':attributeに次の値を含めることはできません：:values',
    'doesnt_end_with' => ':attributeの末尾に次の値は使えません：:values',
    'doesnt_start_with' => ':attributeの先頭に次の値は使えません：:values',
    'email' => ':attributeの形式が正しくありません。例：taro@example.com',
    'encoding' => ':attributeは:encodingで保存してください。',
    'ends_with' => ':attributeは次のいずれかで終わるように入力してください：:values',
    'enum' => ':attributeの選択が正しくありません。',
    'exists' => ':attributeの選択が正しくありません。',
    'extensions' => ':attributeは次の形式のファイルを選んでください：:values',
    'file' => ':attributeにはファイルを選んでください。',
    'filled' => ':attributeを入力してください。',
    'gt' => [
        'array' => ':attributeは:value件より多く指定してください。',
        'file' => ':attributeは:valueKBより大きいファイルを選んでください。',
        'numeric' => ':attributeには:valueより大きい数値を入力してください。',
        'string' => ':attributeは:value文字より多く入力してください。',
    ],
    'gte' => [
        'array' => ':attributeは:value件以上指定してください。',
        'file' => ':attributeは:valueKB以上のファイルを選んでください。',
        'numeric' => ':attributeには:value以上の数値を入力してください。',
        'string' => ':attributeは:value文字以上で入力してください。',
    ],
    'hex_color' => ':attributeには正しいカラーコードを入力してください。',
    'image' => ':attributeには画像ファイルを選んでください。',
    'in' => ':attributeの選択が正しくありません。',
    'in_array' => ':attributeは:otherに含まれる値を指定してください。',
    'in_array_keys' => ':attributeには次の項目のいずれかを含めてください：:values',
    'integer' => ':attributeには整数を入力してください。',
    'ip' => ':attributeには正しいIPアドレスを入力してください。',
    'ipv4' => ':attributeには正しいIPv4アドレスを入力してください。',
    'ipv6' => ':attributeには正しいIPv6アドレスを入力してください。',
    'json' => ':attributeの形式が正しくありません。',
    'list' => ':attributeの形式が正しくありません。',
    'lowercase' => ':attributeは小文字で入力してください。',
    'lt' => [
        'array' => ':attributeは:value件より少なく指定してください。',
        'file' => ':attributeは:valueKBより小さいファイルを選んでください。',
        'numeric' => ':attributeには:valueより小さい数値を入力してください。',
        'string' => ':attributeは:value文字より少なく入力してください。',
    ],
    'lte' => [
        'array' => ':attributeは:value件以下で指定してください。',
        'file' => ':attributeは:valueKB以下のファイルを選んでください。',
        'numeric' => ':attributeには:value以下の数値を入力してください。',
        'string' => ':attributeは:value文字以下で入力してください。',
    ],
    'mac_address' => ':attributeには正しいMACアドレスを入力してください。',
    'max' => [
        'array' => ':attributeは:max件以下で指定してください。',
        'file' => ':attributeは:maxKB以下のファイルを選んでください。',
        'numeric' => ':attributeには:max以下の数値を入力してください。',
        'string' => ':attributeは:max文字以内で入力してください。',
    ],
    'max_digits' => ':attributeは:max桁以内で入力してください。',
    'mimes' => ':attributeは次の形式のファイルを選んでください：:values',
    'mimetypes' => ':attributeは次の形式のファイルを選んでください：:values',
    'min' => [
        'array' => ':attributeは:min件以上指定してください。',
        'file' => ':attributeは:minKB以上のファイルを選んでください。',
        'numeric' => ':attributeには:min以上の数値を入力してください。',
        'string' => ':attributeは:min文字以上で入力してください。',
    ],
    'min_digits' => ':attributeは:min桁以上で入力してください。',
    'missing' => ':attributeは指定しないでください。',
    'missing_if' => ':otherが:valueの場合、:attributeは指定しないでください。',
    'missing_unless' => ':otherが:valueでない場合、:attributeは指定しないでください。',
    'missing_with' => ':valuesを指定する場合、:attributeは指定しないでください。',
    'missing_with_all' => ':valuesを指定する場合、:attributeは指定しないでください。',
    'multiple_of' => ':attributeには:valueの倍数を入力してください。',
    'not_in' => ':attributeの選択が正しくありません。',
    'not_regex' => ':attributeの形式が正しくありません。',
    'numeric' => ':attributeには数値を入力してください。',
    'password' => [
        'letters' => ':attributeには英字を1文字以上含めてください。',
        'mixed' => ':attributeには大文字と小文字をそれぞれ1文字以上含めてください。',
        'numbers' => ':attributeには数字を1文字以上含めてください。',
        'symbols' => ':attributeには記号を1文字以上含めてください。',
        'uncompromised' => 'この:attributeは過去に流出したことがあります。別の:attributeを設定してください。',
    ],
    'present' => ':attributeを指定してください。',
    'present_if' => ':otherが:valueの場合、:attributeを指定してください。',
    'present_unless' => ':otherが:valueでない場合、:attributeを指定してください。',
    'present_with' => ':valuesを指定する場合、:attributeも指定してください。',
    'present_with_all' => ':valuesを指定する場合、:attributeも指定してください。',
    'prohibited' => ':attributeは入力できません。',
    'prohibited_if' => ':otherが:valueの場合、:attributeは入力できません。',
    'prohibited_if_accepted' => ':otherにチェックがある場合、:attributeは入力できません。',
    'prohibited_if_declined' => ':otherにチェックがない場合、:attributeは入力できません。',
    'prohibited_unless' => ':otherが:valuesでない場合、:attributeは入力できません。',
    'prohibits' => ':attributeを入力する場合、:otherは入力できません。',
    'regex' => ':attributeの形式が正しくありません。',
    'required' => ':attributeを入力してください。',
    'required_array_keys' => ':attributeには次の項目を含めてください：:values',
    'required_if' => ':otherが:valueの場合、:attributeを入力してください。',
    'required_if_accepted' => ':otherにチェックがある場合、:attributeを入力してください。',
    'required_if_declined' => ':otherにチェックがない場合、:attributeを入力してください。',
    'required_unless' => ':otherが:valuesでない場合、:attributeを入力してください。',
    'required_with' => ':valuesを入力する場合、:attributeも入力してください。',
    'required_with_all' => ':valuesを入力する場合、:attributeも入力してください。',
    'required_without' => ':valuesを入力しない場合、:attributeを入力してください。',
    'required_without_all' => ':valuesのいずれも入力しない場合、:attributeを入力してください。',
    'same' => ':attributeと:otherが一致しません。',
    'size' => [
        'array' => ':attributeは:size件で指定してください。',
        'file' => ':attributeは:sizeKBのファイルを選んでください。',
        'numeric' => ':attributeには:sizeを入力してください。',
        'string' => ':attributeは:size文字で入力してください。',
    ],
    'starts_with' => ':attributeは次のいずれかで始まるように入力してください：:values',
    'string' => ':attributeには文字を入力してください。',
    'timezone' => ':attributeには正しいタイムゾーンを指定してください。',
    'unique' => 'この:attributeはすでに登録されています。',
    'uploaded' => ':attributeのアップロードに失敗しました。もう一度お試しください。',
    'uppercase' => ':attributeは大文字で入力してください。',
    'url' => ':attributeには正しいURLを入力してください。',
    'ulid' => ':attributeの形式が正しくありません。',
    'uuid' => ':attributeの形式が正しくありません。',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    */

    'custom' => [],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | 入力項目名（:attribute）の日本語表記。
    |
    */

    'attributes' => [
        'name' => '名前',
        'email' => 'メールアドレス',
        'password' => 'パスワード',
        'password_confirmation' => 'パスワード（確認）',
        'current_password' => '現在のパスワード',
    ],

];
