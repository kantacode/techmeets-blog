<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    // コメントを書く：POST /posts/{post}/comments（ログイン必須）
    public function store(Request $request, Post $post)
    {
        $validated = $request->validate([
            'body' => 'required|max:1000',
        ]);

        // $post->comments()->create() は post_id を自動で入れてくれる
        $post->comments()->create([
            'body'    => $validated['body'],
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('posts.show', $post)
            ->with('success', 'コメントしました');
    }

    // コメントを消す：DELETE /comments/{comment}（ログイン必須）
    public function destroy(Comment $comment)
    {
        // 自分のコメントでなければ 403（禁止）
        if ($comment->user_id !== auth()->id()) {
            abort(403, 'この操作は許可されていません');
        }

        $post = $comment->post;
        $comment->delete();

        return redirect()->route('posts.show', $post)
            ->with('success', 'コメントを削除しました');
    }
}
