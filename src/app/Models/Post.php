<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory; // テストで Post::factory() を使えるようにする（Week 14）

    // 投稿一覧のキャッシュの「版」を入れておくキー（Week 17。PostRepository::getPublished を参照）
    public const LIST_CACHE_VERSION_KEY = 'posts.list.version';

    // 投稿が作られた・変わった・消えたら、一覧のキャッシュを捨てる
    protected static function booted(): void
    {
        static::saved(fn () => self::flushListCache());
        static::deleted(fn () => self::flushListCache());
    }

    // 版を新しくする。キャッシュのキーが変わるので、次に一覧を開いたときは DB から取り直す
    public static function flushListCache(): void
    {
        Cache::forever(self::LIST_CACHE_VERSION_KEY, (string) Str::uuid());
    }

    protected $fillable = [
        'title',
        'content',
        'category',
        'user_id',
        'image_path',
    ];

    // 画像の URL：$post->image_url で取れる（画像がなければ null）
    // - AWS_URL（CloudFront のドメイン）があれば、CloudFront 経由の URL を返す（Week 17）
    //   バケットは非公開のまま。CloudFront だけが OAC で S3 を読める
    // - なければ、30分だけ有効な S3 の URL（署名付き URL）を発行する（Week 11 のやり方）
    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        if (config('filesystems.disks.s3.url')) {
            return Storage::disk('s3')->url($this->image_path);
        }

        return Storage::disk('s3')->temporaryUrl($this->image_path, now()->addMinutes(30));
    }

    // 投稿(多) → ユーザー(1)：$post->user->name で投稿者名が取れる
    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // 投稿(1) → コメント(多)：$post->comments でコメント一覧が取れる
    public function comments(): HasMany          // 投稿(1) → コメント(多)
    {
        return $this->hasMany(Comment::class);
    }

    // 投稿(多) ⇔ ユーザー(多)：いいねした人の一覧（中間テーブル likes）
    public function likedUsers(): BelongsToMany  // 投稿(多) ⇔ ユーザー(多)
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

        // 一覧に出している「いいね数」が変わるので、一覧のキャッシュを捨てる
        self::flushListCache();
    }
}
