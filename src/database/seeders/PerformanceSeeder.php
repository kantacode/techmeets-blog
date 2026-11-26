<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Week 17：パフォーマンス計測用のダミーデータ（開発環境だけで使う）
 *
 * 実行：docker compose exec app php artisan db:seed --class=PerformanceSeeder
 *
 * ユーザー10人、投稿50件。投稿ごとにコメント3件と、いいねを0〜10件付ける
 * N+1 は、データが少ないと気づきにくいので、件数を多めにしている
 */
class PerformanceSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::factory()->count(10)->create();

        Post::factory()
            ->count(50)
            ->recycle($users)          // 投稿者は、上の10人の中から選ぶ
            ->create()
            ->each(function (Post $post) use ($users) {
                foreach (range(1, 3) as $i) {
                    $post->comments()->create([
                        'user_id' => $users->random()->id,
                        'body'    => "ダミーのコメント {$i}",
                    ]);
                }

                $post->likedUsers()->attach($users->random(rand(0, 10))->pluck('id'));
            });
    }
}
