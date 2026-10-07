<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

#[Signature('user:create {--name= : 表示名} {--email= : ログイン用メールアドレス} {--password= : パスワード（省略時は入力を求めます）}')]
#[Description('ログイン用のユーザーを作成します（画面からの新規登録はできないため）')]
class CreateUser extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $data = [
            'name' => $this->option('name') ?? text('名前', required: true),
            'email' => $this->option('email') ?? text('メールアドレス', required: true),
            'password' => $this->option('password') ?? password('パスワード', required: true),
        ];

        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::default()],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::create($data);

        $this->info("ユーザーを作成しました：{$user->email}");

        return self::SUCCESS;
    }
}
