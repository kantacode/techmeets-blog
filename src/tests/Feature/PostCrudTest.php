<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * 練習課題2：投稿の CRUD・認可・バリデーションの Feature Test
 *
 * このアプリでは、一覧・詳細は誰でも見られ、作成・編集・削除はログインした人だけ（routes/web.php）
 */
class PostCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('s3'); // 画像まわりで本物の S3 につながないようにする
        $this->owner = User::factory()->create();
    }

    private function validData(array $overrides = []): array
    {
        return array_merge([
            'title'    => 'テストのタイトル',
            'content'  => 'テストの本文',
            'category' => '技術',
        ], $overrides);
    }

    // ---------- Read（一覧・詳細） ----------

    public function test_anyone_can_view_post_list(): void
    {
        Post::factory()->count(3)->create();

        $response = $this->get('/posts');

        $response->assertStatus(200);
        $response->assertViewIs('posts.index');
        $response->assertViewHas('posts');
    }

    public function test_post_list_shows_10_posts_per_page(): void
    {
        Post::factory()->count(11)->create();

        $response = $this->get('/posts');

        $this->assertCount(10, $response->viewData('posts'));
    }

    public function test_anyone_can_view_post_detail(): void
    {
        $post = Post::factory()->create(['title' => '詳細のテスト']);

        $response = $this->get("/posts/{$post->id}");

        $response->assertOk();
        $response->assertSee('詳細のテスト');
    }

    public function test_viewing_missing_post_returns_404(): void
    {
        $this->get('/posts/9999')->assertNotFound();
    }

    // ---------- Create（作成） ----------

    public function test_authenticated_user_can_view_create_form(): void
    {
        $this->actingAs($this->owner)->get('/posts/create')->assertOk();
    }

    public function test_guest_is_redirected_to_login_from_create_form(): void
    {
        $this->get('/posts/create')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_create_post(): void
    {
        $response = $this->actingAs($this->owner)->post('/posts', $this->validData());

        $post = Post::where('title', 'テストのタイトル')->first();
        $response->assertRedirect(route('posts.show', $post));
        $this->assertDatabaseHas('posts', [
            'title'   => 'テストのタイトル',
            'user_id' => $this->owner->id,
        ]);
    }

    public function test_guest_cannot_create_post(): void
    {
        $response = $this->post('/posts', $this->validData());

        $response->assertRedirect('/login');
        $this->assertDatabaseCount('posts', 0);
    }

    // ---------- バリデーション（PostRequest） ----------

    public function test_title_is_required(): void
    {
        $response = $this->actingAs($this->owner)->post('/posts', $this->validData(['title' => '']));

        $response->assertSessionHasErrors('title');
    }

    public function test_content_is_required(): void
    {
        $response = $this->actingAs($this->owner)->post('/posts', $this->validData(['content' => '']));

        $response->assertSessionHasErrors('content');
    }

    public function test_category_is_required(): void
    {
        $response = $this->actingAs($this->owner)->post('/posts', $this->validData(['category' => '']));

        $response->assertSessionHasErrors('category');
    }

    // 境界値：タイトル200文字（上限ちょうど）→ OK
    public function test_title_with_200_characters_is_accepted(): void
    {
        $response = $this->actingAs($this->owner)->post('/posts', $this->validData(['title' => str_repeat('あ', 200)]));

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseCount('posts', 1);
    }

    // 境界値：タイトル201文字（上限の1つ外）→ NG
    public function test_title_with_201_characters_is_rejected(): void
    {
        $response = $this->actingAs($this->owner)->post('/posts', $this->validData(['title' => str_repeat('あ', 201)]));

        $response->assertSessionHasErrors('title');
        $this->assertDatabaseCount('posts', 0);
    }

    // 境界値：カテゴリー50文字 → OK / 51文字 → NG
    public function test_category_with_50_characters_is_accepted(): void
    {
        $response = $this->actingAs($this->owner)->post('/posts', $this->validData(['category' => str_repeat('a', 50)]));

        $response->assertSessionHasNoErrors();
    }

    public function test_category_with_51_characters_is_rejected(): void
    {
        $response = $this->actingAs($this->owner)->post('/posts', $this->validData(['category' => str_repeat('a', 51)]));

        $response->assertSessionHasErrors('category');
    }

    // ---------- Update（更新） ----------

    public function test_owner_can_view_edit_form(): void
    {
        $post = Post::factory()->for($this->owner)->create();

        $this->actingAs($this->owner)->get("/posts/{$post->id}/edit")->assertOk();
    }

    public function test_owner_can_update_post(): void
    {
        $post = Post::factory()->for($this->owner)->create();

        $response = $this->actingAs($this->owner)->put("/posts/{$post->id}", $this->validData(['title' => '更新後のタイトル']));

        $response->assertRedirect(route('posts.show', $post));
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'title' => '更新後のタイトル']);
    }

    public function test_update_with_empty_title_keeps_old_title(): void
    {
        $post = Post::factory()->for($this->owner)->create(['title' => '元のタイトル']);

        $response = $this->actingAs($this->owner)->put("/posts/{$post->id}", $this->validData(['title' => '']));

        $response->assertSessionHasErrors('title');
        $this->assertSame('元のタイトル', $post->fresh()->title);
    }

    // ---------- Delete（削除） ----------

    public function test_owner_can_delete_post(): void
    {
        $post = Post::factory()->for($this->owner)->create();

        $response = $this->actingAs($this->owner)->delete("/posts/{$post->id}");

        $response->assertRedirect(route('posts.index'));
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    // ---------- 認可（他人の投稿は触れない：PostPolicy） ----------

    public function test_other_user_cannot_view_edit_form(): void
    {
        $post = Post::factory()->for($this->owner)->create();
        $other = User::factory()->create();

        $this->actingAs($other)->get("/posts/{$post->id}/edit")->assertForbidden();
    }

    public function test_other_user_cannot_update_post(): void
    {
        $post = Post::factory()->for($this->owner)->create(['title' => '本人のタイトル']);
        $other = User::factory()->create();

        $response = $this->actingAs($other)->put("/posts/{$post->id}", $this->validData(['title' => '乗っ取り']));

        $response->assertForbidden();
        $this->assertSame('本人のタイトル', $post->fresh()->title);
    }

    public function test_other_user_cannot_delete_post(): void
    {
        $post = Post::factory()->for($this->owner)->create();
        $other = User::factory()->create();

        $response = $this->actingAs($other)->delete("/posts/{$post->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }

    public function test_guest_cannot_delete_post(): void
    {
        $post = Post::factory()->for($this->owner)->create();

        $response = $this->delete("/posts/{$post->id}");

        $response->assertRedirect('/login');
        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }
}
