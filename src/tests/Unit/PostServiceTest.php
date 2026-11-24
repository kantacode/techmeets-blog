<?php

namespace Tests\Unit;

use App\Models\Post;
use App\Models\User;
use App\Services\PostService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * 練習課題1：Week 9 の PostService のテスト
 *
 * PostService は Repository（Eloquent）・DB::transaction()・Storage を使うので、
 * Laravel の Tests\TestCase を継承し、RefreshDatabase でテスト用の DB を使う
 * S3 は Storage::fake('s3') で偽物に差し替え、本物の AWS にはつながない
 */
class PostServiceTest extends TestCase
{
    use RefreshDatabase;

    private PostService $service;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('s3');
        // コンストラクタの PostRepository も Laravel が自動で渡してくれる
        $this->service = app(PostService::class);
        $this->user = User::factory()->create();
    }

    private function validData(array $overrides = []): array
    {
        return array_merge([
            'title'    => 'テストのタイトル',
            'content'  => 'テストの本文',
            'category' => '技術',
            'user_id'  => $this->user->id,
        ], $overrides);
    }

    // ---------- 正常系 ----------

    public function test_create_post_saves_post_to_database(): void
    {
        $post = $this->service->createPost($this->validData());

        $this->assertDatabaseHas('posts', [
            'id'      => $post->id,
            'title'   => 'テストのタイトル',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_create_post_without_image_leaves_image_path_empty(): void
    {
        $post = $this->service->createPost($this->validData());

        $this->assertNull($post->image_path);
    }

    public function test_create_post_with_image_stores_image_in_s3(): void
    {
        $image = UploadedFile::fake()->create('photo.jpg', 100, 'image/jpeg');

        $post = $this->service->createPost($this->validData(), $image);

        Storage::disk('s3')->assertExists($post->image_path);
    }

    public function test_update_post_changes_title(): void
    {
        $post = Post::factory()->for($this->user)->create(['title' => '古いタイトル']);

        $updated = $this->service->updatePost($post, ['title' => '新しいタイトル']);

        $this->assertSame('新しいタイトル', $updated->fresh()->title);
    }

    public function test_update_post_without_new_image_keeps_old_image(): void
    {
        $oldPath = Storage::disk('s3')->put('images', UploadedFile::fake()->create('old.jpg', 100, 'image/jpeg'));
        $post = Post::factory()->for($this->user)->create(['image_path' => $oldPath]);

        $this->service->updatePost($post, ['title' => '画像はそのまま']);

        Storage::disk('s3')->assertExists($oldPath);
    }

    public function test_update_post_with_new_image_deletes_old_image(): void
    {
        $oldPath = Storage::disk('s3')->put('images', UploadedFile::fake()->create('old.jpg', 100, 'image/jpeg'));
        $post = Post::factory()->for($this->user)->create(['image_path' => $oldPath]);

        $this->service->updatePost($post, [], UploadedFile::fake()->create('new.jpg', 100, 'image/jpeg'));

        Storage::disk('s3')->assertMissing($oldPath);
    }

    public function test_delete_post_removes_post_from_database(): void
    {
        $post = Post::factory()->for($this->user)->create();

        $this->service->deletePost($post);

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_delete_post_also_deletes_image(): void
    {
        $path = Storage::disk('s3')->put('images', UploadedFile::fake()->create('photo.jpg', 100, 'image/jpeg'));
        $post = Post::factory()->for($this->user)->create(['image_path' => $path]);

        $this->service->deletePost($post);

        Storage::disk('s3')->assertMissing($path);
    }

    // ---------- 異常系 ----------

    // タイトルがないと DB の NOT NULL 制約で失敗する（Service は入力チェックをしない）
    public function test_create_post_without_title_throws_exception(): void
    {
        $this->expectException(QueryException::class);

        $this->service->createPost($this->validData(['title' => null]));
    }

    // 失敗したときは DB::transaction() で巻き戻り、投稿は1件も残らない
    public function test_failed_create_post_leaves_no_post(): void
    {
        try {
            $this->service->createPost($this->validData(['title' => null]));
        } catch (QueryException) {
            // 例外が出ることは上のテストで確認済み。ここでは DB の状態だけを見る
        }

        $this->assertDatabaseCount('posts', 0);
    }

    // ---------- 境界値 ----------

    // posts.title は VARCHAR(255)。入力チェック（PostRequest）は200文字までなので、200文字はそのまま保存できる
    public function test_create_post_saves_title_of_200_characters(): void
    {
        $title = str_repeat('あ', 200);

        $post = $this->service->createPost($this->validData(['title' => $title]));

        $this->assertSame(200, mb_strlen($post->fresh()->title));
    }

    // 空文字のタイトルは DB では NULL ではないので保存できてしまう → 空チェックは PostRequest の役目
    public function test_create_post_accepts_empty_string_title_because_validation_is_not_its_job(): void
    {
        $post = $this->service->createPost($this->validData(['title' => '']));

        $this->assertSame('', $post->fresh()->title);
    }
}
