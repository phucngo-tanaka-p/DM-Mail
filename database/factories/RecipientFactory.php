<?php

namespace Database\Factories;

use App\Models\Recipient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Recipient>
 */
class RecipientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'company_name' => fake()->company(),
            'person_name' => fake()->name(),
            'honorific' => '様',
        ];
    }

    /**
     * 「送信しない」にチェックされた行。
     */
    public function excluded(): static
    {
        return $this->state(fn (array $attributes) => [
            'exclude' => true,
        ]);
    }

    /**
     * 配信停止済みの行。
     */
    public function unsubscribed(): static
    {
        return $this->afterMaking(function (Recipient $recipient) {
            $recipient->unsubscribed_at = now();
        });
    }
}
