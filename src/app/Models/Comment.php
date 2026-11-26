<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    protected $fillable = [
        'post_id',
        'user_id',
        'body',
    ];

    // コメントが増えた・消えたら、投稿一覧の「コメント数」が変わるので、一覧のキャッシュを捨てる（Week 17）
    protected static function booted(): void
    {
        static::saved(fn () => Post::flushListCache());
        static::deleted(fn () => Post::flushListCache());
    }

    // コメント(多) → 投稿(1)
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    // コメント(多) → ユーザー(1)：$comment->user->name で書いた人の名前が取れる
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
