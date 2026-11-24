<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Week 8 のコメント欄の Feature Test
 */
class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_comment_on_post(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $response = $this->actingAs($user)->post("/posts/{$post->id}/comments", ['body' => 'いい記事！']);

        $response->assertRedirect(route('posts.show', $post));
        $this->assertDatabaseHas('comments', ['post_id' => $post->id, 'user_id' => $user->id, 'body' => 'いい記事！']);
    }

    public function test_comment_body_is_required(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $response = $this->actingAs($user)->post("/posts/{$post->id}/comments", ['body' => '']);

        $response->assertSessionHasErrors('body');
    }

    // 境界値：1000文字 → OK / 1001文字 → NG
    public function test_comment_of_1000_characters_is_accepted(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $response = $this->actingAs($user)->post("/posts/{$post->id}/comments", ['body' => str_repeat('あ', 1000)]);

        $response->assertSessionHasNoErrors();
    }

    public function test_comment_of_1001_characters_is_rejected(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $response = $this->actingAs($user)->post("/posts/{$post->id}/comments", ['body' => str_repeat('あ', 1001)]);

        $response->assertSessionHasErrors('body');
    }

    public function test_author_can_delete_own_comment(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $comment = $post->comments()->create(['user_id' => $user->id, 'body' => '消すコメント']);

        $response = $this->actingAs($user)->delete("/comments/{$comment->id}");

        $response->assertRedirect(route('posts.show', $post));
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_comment_belongs_to_post_and_user(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $comment = Comment::create(['post_id' => $post->id, 'user_id' => $user->id, 'body' => '本文']);

        $this->assertTrue($comment->post->is($post));
        $this->assertTrue($comment->user->is($user));
    }
}
