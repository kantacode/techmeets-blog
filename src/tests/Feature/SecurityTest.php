<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Week 8 練習課題2：セキュリティテスト
 *
 * 実行方法：docker compose exec app php artisan test --filter=SecurityTest
 * （テスト用の一時的なデータベースを使うので、本物のデータは消えません）
 */
class SecurityTest extends TestCase
{
    use RefreshDatabase;

    private function makePost(User $owner, array $attributes = []): Post
    {
        return Post::create(array_merge([
            'title'    => 'タイトル',
            'content'  => '本文',
            'category' => 'カテゴリー',
            'user_id'  => $owner->id,
        ], $attributes));
    }

    // ---------- XSS ----------

    public function test_xss_in_post_and_comment_is_escaped(): void
    {
        $user = User::factory()->create(['name' => '<b>attacker</b>']);
        $post = $this->makePost($user, [
            'title'   => '<script>alert("title")</script>',
            'content' => '<img src=x onerror=alert(1)>',
        ]);
        $post->comments()->create(['user_id' => $user->id, 'body' => '<script>alert("comment")</script>']);

        $this->get("/posts/{$post->id}")
            ->assertOk()
            ->assertDontSee('<script>alert', false)
            ->assertDontSee('<img src=x', false)
            ->assertDontSee('<b>attacker</b>', false)
            ->assertSee('&lt;script&gt;alert', false);

        $this->get('/posts')->assertDontSee('<script>alert', false);
    }

    // ---------- CSRF ----------

    public function test_requests_without_csrf_token_are_rejected(): void
    {
        // Laravel はテスト実行中だけ CSRF チェックを自動で外すので、
        // 「テスト中ではない」ことにした CSRF チェックに差し替えて、本番と同じ状態で試す
        $this->app->instance(ValidateCsrfToken::class, new class($this->app, $this->app['encrypter']) extends ValidateCsrfToken {
            protected function runningUnitTests()
            {
                return false;
            }
        });
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/posts', ['title' => 'a', 'content' => 'b', 'category' => 'c'])
            ->assertStatus(419);

        $this->assertSame(0, Post::count());
    }

    // ---------- SQL インジェクション ----------

    public function test_sql_injection_on_login_fails(): void
    {
        User::factory()->create(['email' => 'victim@example.com']);

        $this->post('/login', [
            'email'    => "' OR '1'='1",
            'password' => "' OR '1'='1",
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_sql_injection_in_url_returns_404(): void
    {
        $this->makePost(User::factory()->create());

        $this->get("/posts/1' OR 1=1--")->assertNotFound();
    }

    // ---------- 認証（ログイン必須） ----------

    public function test_guest_cannot_create_post_or_comment(): void
    {
        $post = $this->makePost(User::factory()->create());

        $this->get('/posts/create')->assertRedirect('/login');
        $this->post('/posts', ['title' => 'a', 'content' => 'b', 'category' => 'c'])->assertRedirect('/login');
        $this->post("/posts/{$post->id}/comments", ['body' => 'x'])->assertRedirect('/login');

        $this->assertSame(1, Post::count());
        $this->assertSame(0, Comment::count());
    }

    // ---------- 認可（本人だけ） ----------

    public function test_other_user_cannot_edit_or_delete_post(): void
    {
        $post = $this->makePost(User::factory()->create());
        $other = User::factory()->create();

        $this->actingAs($other)->get("/posts/{$post->id}/edit")->assertForbidden();
        $this->actingAs($other)->put("/posts/{$post->id}", ['title' => '乗っ取り', 'content' => 'x', 'category' => 'x'])->assertForbidden();
        $this->actingAs($other)->delete("/posts/{$post->id}")->assertForbidden();

        $this->assertSame('タイトル', $post->fresh()->title);
    }

    public function test_other_user_cannot_delete_comment(): void
    {
        $post = $this->makePost(User::factory()->create());
        $author = User::factory()->create();
        $comment = $post->comments()->create(['user_id' => $author->id, 'body' => 'mine']);

        $this->actingAs(User::factory()->create())->delete("/comments/{$comment->id}")->assertForbidden();

        $this->assertSame(1, Comment::count());
    }

    // ---------- マスアサインメント ----------

    public function test_user_id_cannot_be_spoofed_when_posting(): void
    {
        $me = User::factory()->create();
        $victim = User::factory()->create();

        // フォームに他人の user_id を紛れ込ませても、自分の投稿として保存される
        $this->actingAs($me)->post('/posts', [
            'title' => 'a', 'content' => 'b', 'category' => 'c', 'user_id' => $victim->id,
        ]);

        $this->assertSame($me->id, Post::first()->user_id);
    }

    // ---------- パスワード ----------

    public function test_password_is_stored_hashed(): void
    {
        $this->post('/register', [
            'name'                  => 'Taro',
            'email'                 => 'taro@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $stored = User::where('email', 'taro@example.com')->value('password');

        $this->assertNotSame('password123', $stored);
        $this->assertStringStartsWith('$2y$', $stored);
        $this->assertTrue(Hash::check('password123', $stored));
    }

    // ---------- 総当たり（ブルートフォース） ----------

    public function test_login_is_throttled_after_five_failures(): void
    {
        User::factory()->create(['email' => 'victim@example.com']);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'victim@example.com', 'password' => "wrong{$i}"]);
        }

        $this->post('/login', ['email' => 'victim@example.com', 'password' => 'wrong'])
            ->assertSessionHasErrors('email');

        // 6回目は「パスワードが違う」ではなく「試行回数が多すぎる」で止められる
        $this->assertStringContainsString('Too many login attempts', session('errors')->first('email'));
    }
}
