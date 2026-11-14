<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = [
        'post_id',
        'user_id',
        'body',
    ];

    // コメント(多) → 投稿(1)
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    // コメント(多) → ユーザー(1)：$comment->user->name で書いた人の名前が取れる
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
