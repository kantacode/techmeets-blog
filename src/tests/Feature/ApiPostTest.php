<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Week 10：投稿 API のテスト
 */
class ApiPostTest extends TestCase
{
    use RefreshDatabase;

    private function createTestUser(): User
    {
        return User::factory()->create(['email' => 'test@example.com', 'name' => 'Test User']);
    }

    public function test_一覧を取得すると投稿者の名前だけが返り_メールアドレスは返らない(): void
    {
        $user = $this->createTestUser();
        Post::create(['title' => 'テスト', 'content' => '本文', 'category' => '雑記', 'user_id' => $user->id]);

        $response = $this->getJson('/api/posts');

        $response->assertOk()
            ->assertJsonPath('data.0.title', 'テスト')
            ->assertJsonPath('data.0.user.name', 'Test User')
            ->assertJsonMissingPath('data.0.user.email');
    }

    public function test_存在しない投稿は404(): void
    {
        $this->getJson('/api/posts/9999')->assertNotFound();
    }

    public function test_投稿するとテスト用ユーザーの投稿として保存される(): void
    {
        $user = $this->createTestUser();

        $response = $this->postJson('/api/posts', [
            'title'    => 'APIから投稿',
            'content'  => '本文です',
            'category' => 'お知らせ',
        ]);

        $response->assertCreated()->assertJsonPath('data.user.name', 'Test User');
        $this->assertDatabaseHas('posts', ['title' => 'APIから投稿', 'user_id' => $user->id]);
    }

    public function test_未入力だと422で入力エラーが返る(): void
    {
        $this->postJson('/api/posts', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'content', 'category']);
    }

    public function test_許可したフロントエンドからのリクエストにCORSヘッダーが付く(): void
    {
        $this->getJson('/api/posts', ['Origin' => 'http://localhost:5173'])
            ->assertHeader('Access-Control-Allow-Origin', 'http://localhost:5173');
    }
}
