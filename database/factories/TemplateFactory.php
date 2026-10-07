<?php

namespace Database\Factories;

use App\Models\Template;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Template>
 */
class TemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => '新製品のご案内',
            'subject' => '【ご案内】{{company}} {{name}}{{honorific}}へ新製品のお知らせ',
            'body_html' => '<p>{{company}}<br>{{name}}{{honorific}}</p><p>いつもお世話になっております。</p>',
            'body_text' => "{{company}}\n{{name}}{{honorific}}\n\nいつもお世話になっております。",
        ];
    }
}
