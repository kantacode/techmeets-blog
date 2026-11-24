<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * 練習課題3：TDD で追加した「いいね」機能のテスト
 *
 * 仕様（テストを書く前に決めたこと）
 *   - ログインした人は、投稿に「いいね」を付けられる（POST /posts/{post}/like）
 *   - もう一度押すと「いいね」を取り消せる（1人1回まで）
 *   - 未ログインの人は「いいね」できない（ログイン画面へ）
 *   - 投稿の詳細画面に「いいね」の数が表示される
 */
class PostLikeTest extends TestCase
{
    use RefreshDatabase;

    // 正常系：いいねすると数が1つ増え、投稿の詳細画面に戻る
    public function test_user_can_like_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $response = $this->actingAs($user)->post("/posts/{$post->id}/like");

        $response->assertRedirect(route('posts.show', $post));
        $this->assertSame(1, $post->likedUsers()->count());
    }

    // 正常系：もう一度押すと、いいねが取り消される
    public function test_liking_again_removes_like(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($user)->post("/posts/{$post->id}/like");
        $this->actingAs($user)->post("/posts/{$post->id}/like");

        $this->assertSame(0, $post->likedUsers()->count());
    }

    // 正常系：別々の人のいいねは、それぞれ数える
    public function test_likes_from_different_users_are_counted(): void
    {
        $post = Post::factory()->create();
        [$alice, $bob] = User::factory()->count(2)->create();

        $this->actingAs($alice)->post("/posts/{$post->id}/like");
        $this->actingAs($bob)->post("/posts/{$post->id}/like");

        $this->assertSame(2, $post->likedUsers()->count());
    }

    // 正常系：自分がいいねしたかどうかが分かる
    public function test_post_knows_whether_user_liked_it(): void
    {
        $post = Post::factory()->create();
        [$liker, $other] = User::factory()->count(2)->create();

        $this->actingAs($liker)->post("/posts/{$post->id}/like");

        $this->assertTrue($post->isLikedBy($liker));
        $this->assertFalse($post->isLikedBy($other));
    }

    // 異常系：未ログインの人はいいねできない
    public function test_guest_cannot_like_post(): void
    {
        $post = Post::factory()->create();

        $response = $this->post("/posts/{$post->id}/like");

        $response->assertRedirect('/login');
        $this->assertSame(0, $post->likedUsers()->count());
    }

    // 異常系：存在しない投稿にはいいねできない
    public function test_liking_missing_post_returns_404(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/posts/9999/like')->assertNotFound();
    }

    // 表示：詳細画面にいいねの数が出る
    public function test_post_detail_shows_like_count(): void
    {
        $post = Post::factory()->create();
        $post->likedUsers()->attach(User::factory()->count(3)->create());

        $response = $this->get("/posts/{$post->id}");

        $response->assertSee('いいね 3');
    }
}
