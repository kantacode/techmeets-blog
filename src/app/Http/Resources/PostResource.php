<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 投稿をフロントに返すときの JSON の形を決める
 * モデルをそのまま返すと不要なカラムが漏れるので、返す項目をここで明示する
 */
class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'title'      => $this->title,
            'content'    => $this->content,
            'category'   => $this->category,
            'user'       => [
                'name' => $this->user->name, // 投稿者の名前だけ（メールアドレスやパスワードは返さない）
            ],
            'created_at' => $this->created_at->toDateString(),
        ];
    }
}
