<?php

namespace App\Repositories;

use App\Models\Post;

/**
 * 投稿の「DB の読み書き」だけを担当する（倉庫係）
 */
class PostRepository
{
    // 一覧用：新しい順・投稿者も一緒に取得（N+1 対策）・10件ずつ
    public function getPublished()
    {
        return Post::with('user')->latest()->paginate(10);
    }

    // API の1件取得用：投稿者も一緒に取得。見つからなければ 404
    public function findById(int $id): Post
    {
        return Post::with('user')->findOrFail($id);
    }

    // 詳細用：投稿者と、コメント（＋書いた人）を古い順で一緒に取得
    public function loadForShow(Post $post): Post
    {
        return $post->load(['user', 'comments' => fn ($query) => $query->with('user')->oldest()]);
    }

    public function create(array $data): Post
    {
        return Post::create($data);
    }

    public function update(Post $post, array $data): Post
    {
        $post->update($data);
        return $post;
    }

    public function delete(Post $post): void
    {
        $post->delete();
    }
}
