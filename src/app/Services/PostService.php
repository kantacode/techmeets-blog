<?php

namespace App\Services;

use App\Models\Post;
use App\Repositories\PostRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * 投稿の「業務の手順」を担当する（料理長）
 * 画像保存やメール通知などが増えても、ここに足すだけでコントローラーは変わらない
 */
class PostService
{
    // コンストラクタインジェクション：PostRepository は Laravel が自動で用意してくれる
    public function __construct(
        private PostRepository $postRepository
    ) {
    }

    public function createPost(array $data, ?UploadedFile $image = null): Post
    {
        // 画像があれば先に S3 に保存し、保存先のパスを DB に入れる
        if ($image) {
            $data['image_path'] = $this->storeImage($image);
        }

        // 途中で失敗したら、全部なかったことにする
        return DB::transaction(function () use ($data) {
            $post = $this->postRepository->create($data);
            Log::info('Post created', ['post_id' => $post->id]);
            return $post;
        });
    }

    public function updatePost(Post $post, array $data, ?UploadedFile $image = null): Post
    {
        $oldImagePath = $post->image_path;

        // 新しい画像が選ばれたときだけ差し替える（選ばなければ今の画像のまま）
        if ($image) {
            $data['image_path'] = $this->storeImage($image);
        }

        $updated = DB::transaction(function () use ($post, $data) {
            $updated = $this->postRepository->update($post, $data);
            Log::info('Post updated', ['post_id' => $updated->id]);
            return $updated;
        });

        // DB の更新が成功してから、古い画像を S3 から消す
        if ($image && $oldImagePath) {
            $this->deleteImage($oldImagePath);
        }

        return $updated;
    }

    public function deletePost(Post $post): void
    {
        $imagePath = $post->image_path;

        DB::transaction(function () use ($post) {
            $this->postRepository->delete($post);
            Log::info('Post deleted', ['post_id' => $post->id]);
        });

        // 投稿が消えたら、画像も S3 から消す（残しておくと保存料金がかかり続ける）
        if ($imagePath) {
            $this->deleteImage($imagePath);
        }
    }

    // S3 の images/ フォルダに保存し、保存先のパス（例：images/xxxx.jpg）を返す
    private function storeImage(UploadedFile $image): string
    {
        return Storage::disk('s3')->put('images', $image);
    }

    private function deleteImage(string $path): void
    {
        Storage::disk('s3')->delete($path);
    }
}
