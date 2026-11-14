<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

/**
 * 「この User は、この Post に対してこの操作をしていいか」を判断する（警備員）
 * Week 8 で edit / update / destroy の3か所に書いていた本人チェックを、ここ1か所にまとめた
 */
class PostPolicy
{
    // 編集・更新できるのは、投稿した本人だけ
    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }

    // 削除できるのは、投稿した本人だけ
    public function delete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }
}
