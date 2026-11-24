<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Support extends Model
{
    protected $fillable = ['user_id', 'post_id', 'amount', 'stripe_session_id'];

    // 応援(多) → ユーザー(1)：応援した人
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // 応援(多) → 投稿(1)：応援した記事
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
