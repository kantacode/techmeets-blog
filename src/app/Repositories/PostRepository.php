<?php

namespace App\Repositories;

use App\Models\Post;
use Illuminate\Support\Facades\Cache;

/**
 * 投稿の「DB の読み書き」だけを担当する（倉庫係）
 */
class PostRepository
{
    // 一覧用：新しい順・投稿者も一緒に取得（N+1 対策）・コメント数といいね数も数える・10件ずつ
    // 一覧は誰でも見られて、よく開かれるページなので、結果を10分間キャッシュする（Week 17）
    // キーに「版」を入れておき、投稿・コメント・いいねが変わったら版を変える（Post::flushListCache）
    // → 古い版のキャッシュは使われなくなり、10分たつと自然に消える
    public function getPublished()
    {
        $version = Cache::get(Post::LIST_CACHE_VERSION_KEY, 'initial');
        $page = (int) request()->query('page', 1);

        return Cache::remember(
            "posts.list.{$version}.page{$page}",
            now()->addMinutes(10),
            fn () => Post::with('user')->withCount(['comments', 'likedUsers'])->latest()->paginate(10)
        );
    }

    // API の1件取得用：投稿者も一緒に取得。見つからなければ 404
    public function findById(int $id): Post
    {
        return Post::with('user')->findOrFail($id);
    }

    // 詳細用：投稿者と、コメント（＋書いた人）を古い順で一緒に取得し、いいねの数（liked_users_count）も数える
    public function loadForShow(Post $post): Post
    {
        return $post
            ->load(['user', 'comments' => fn ($query) => $query->with('user')->oldest()])
            ->loadCount('likedUsers');
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
