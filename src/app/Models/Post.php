<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    use HasFactory; // テストで Post::factory() を使えるようにする（Week 14）

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

    // 投稿(多) ⇔ ユーザー(多)：いいねした人の一覧（中間テーブル likes）
    public function likedUsers()
    {
        return $this->belongsToMany(User::class, 'likes')->withTimestamps();
    }

    // この人がいいね済みか
    public function isLikedBy(User $user): bool
    {
        return $this->likedUsers()->where('user_id', $user->id)->exists();
    }

    // いいねしていなければ付け、いいね済みなら取り消す
    public function toggleLikeBy(User $user): void
    {
        $this->likedUsers()->toggle($user->id);
    }
}
