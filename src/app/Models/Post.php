<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    protected $fillable = [
        'title',
        'content',
        'category',
        'user_id',
        'image_path',
    ];

    // 画像の URL：$post->image_url で取れる（画像がなければ null）
    // バケットは非公開のままにして、30分だけ有効な URL（署名付き URL）を毎回発行する
    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return Storage::disk('s3')->temporaryUrl($this->image_path, now()->addMinutes(30));
    }

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
