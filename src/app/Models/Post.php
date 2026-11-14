<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'title',
        'content',
        'category',
        'user_id',
    ];

    // 投稿(多) → ユーザー(1)：$post->user->name で投稿者名が取れる
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 投稿(1) → コメント(多)：$post->comments でコメント一覧が取れる
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
