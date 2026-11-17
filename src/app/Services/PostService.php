<?php

namespace App\Services;

use App\Models\Post;
use App\Repositories\PostRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 投稿の「業務の手順」を担当する（料理長）
 * 画像保存やメール通知などが増えても、ここに足すだけでコントローラーは変わらない
 */
class PostService
{
    // コンストラクタインジェクション：PostRepository は Laravel が自動で用意してくれる
    public function __construct(
        private PostRepository $postRepository
    ) {}

    public function createPost(array $data): Post
    {
        // 途中で失敗したら、全部なかったことにする
        return DB::transaction(function () use ($data) {
            $post = $this->postRepository->create($data);
            Log::info('Post created', ['post_id' => $post->id]);
            return $post;
        });
    }

    public function updatePost(Post $post, array $data): Post
    {
        return DB::transaction(function () use ($post, $data) {
            $updated = $this->postRepository->update($post, $data);
            Log::info('Post updated', ['post_id' => $updated->id]);
            return $updated;
        });
    }

    public function deletePost(Post $post): void
    {
        DB::transaction(function () use ($post) {
            $this->postRepository->delete($post);
            Log::info('Post deleted', ['post_id' => $post->id]);
        });
    }
}
