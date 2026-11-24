<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * テスト用の投稿ダミーデータ（Week 14）
 *
 * @extends Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title'    => fake()->sentence(),
            'content'  => fake()->paragraph(),
            'category' => fake()->randomElement(['日記', '技術', 'お知らせ']),
            'user_id'  => User::factory(),
        ];
    }
}
