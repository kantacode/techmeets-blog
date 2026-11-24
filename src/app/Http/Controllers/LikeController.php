<?php

namespace App\Http\Controllers;

use App\Models\Post;

/**
 * 投稿への「いいね」（Week 14：TDD で追加）
 */
class LikeController extends Controller
{
    // いいね / 取り消し：POST /posts/{post}/like
    public function toggle(Post $post)
    {
        $post->toggleLikeBy(auth()->user());

        return redirect()->route('posts.show', $post);
    }
}
