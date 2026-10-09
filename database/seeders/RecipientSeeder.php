<?php

namespace Database\Seeders;

use App\Models\Recipient;
use Illuminate\Database\Seeder;

/**
 * 画面確認用のダミー送信リスト（開発環境専用）。
 *
 * php artisan db:seed --class=RecipientSeeder
 */
class RecipientSeeder extends Seeder
{
    public const COUNT = 120;

    private const DEPARTMENTS = ['営業部', '総務部', '経理部', '購買部', '情報システム部'];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (range(1, self::COUNT) as $number) {
            $factory = Recipient::factory()->state([
                'custom_fields' => [
                    'クーポンコード' => fake()->unique()->bothify('??##-####'),
                    '担当部署' => fake()->randomElement(self::DEPARTMENTS),
                ],
            ]);

            $factory = match (true) {
                // 会社宛て（担当者名なし・御中）
                $number % 15 === 0 => $factory->state(['person_name' => null, 'honorific' => '御中']),
                // CC 付き
                $number % 12 === 0 => $factory->state(['cc' => fake()->safeEmail().';'.fake()->safeEmail()]),
                // 送信しない
                $number % 10 === 0 => $factory->excluded(),
                // 配信停止
                $number % 17 === 0 => $factory->unsubscribed(),
                // 過去に送信済み
                $number % 3 === 0 => $factory->state(['last_sent_at' => now()->subDays(fake()->numberBetween(1, 60))]),
                default => $factory,
            };

            $factory->create();
        }
    }
}
