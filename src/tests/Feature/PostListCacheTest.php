<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Week 17：投稿一覧のキャッシュ
 * キャッシュで一番多いバグは「変わったのに古いまま表示される」なので、そこを確かめる
 */
class PostListCacheTest extends TestCase
{
    use RefreshDatabase;

    // 2回目は DB に投稿を聞きに行かない（キャッシュから返す）
    public function test_second_visit_does_not_query_posts(): void
    {
        Post::factory()->count(3)->create();
        $this->get('/posts')->assertOk();

        DB::enableQueryLog();
        $this->get('/posts')->assertOk();
        $postQueries = collect(DB::getQueryLog())->filter(fn ($q) => str_contains($q['query'], 'from "posts"'));

        $this->assertCount(0, $postQueries);
    }

    // 新しい投稿は、キャッシュがあってもすぐ一覧に出る
    public function test_new_post_appears_even_after_list_was_cached(): void
    {
        Post::factory()->create(['title' => '前からある投稿']);
        $this->get('/posts')->assertSee('前からある投稿');

        Post::factory()->create(['title' => 'あとから足した投稿']);

        $this->get('/posts')->assertSee('あとから足した投稿');
    }

    // 投稿を消したら、一覧から消える
    public function test_deleted_post_disappears_from_cached_list(): void
    {
        $post = Post::factory()->create(['title' => '消す投稿']);
        $this->get('/posts')->assertSee('消す投稿');

        $post->delete();

        $this->get('/posts')->assertDontSee('消す投稿');
    }

    // コメントが増えたら、一覧のコメント数も増える
    public function test_comment_count_is_updated_after_list_was_cached(): void
    {
        $post = Post::factory()->create();
        $this->get('/posts')->assertSee('コメント 0件');

        $post->comments()->create(['user_id' => $post->user_id, 'body' => 'コメント']);

        $this->get('/posts')->assertSee('コメント 1件');
    }

    // いいねしたら、一覧のいいね数も増える
    public function test_like_count_is_updated_after_list_was_cached(): void
    {
        $post = Post::factory()->create();
        $user = User::factory()->create();
        $this->get('/posts')->assertSee('いいね 0');

        $this->actingAs($user)->post("/posts/{$post->id}/like");

        $this->get('/posts')->assertSee('いいね 1');
    }

    // 2ページ目は、1ページ目とは別にキャッシュされる
    public function test_each_page_is_cached_separately(): void
    {
        Post::factory()->count(11)->create();

        $first = $this->get('/posts')->viewData('posts');
        $second = $this->get('/posts?page=2')->viewData('posts');

        $this->assertSame(1, $first->currentPage());
        $this->assertSame(2, $second->currentPage());
        $this->assertCount(1, $second);
    }
}
