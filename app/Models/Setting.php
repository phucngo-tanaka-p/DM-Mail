<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * 設定画面の値（key-value）。
 *
 * @property string $key
 * @property string|null $value
 */
#[Fillable(['key', 'value'])]
#[Table(key: 'key', keyType: 'string', incrementing: false, timestamps: false)]
class Setting extends Model
{
    //
}
