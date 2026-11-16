<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostRequest;
use App\Http\Resources\PostResource;
use App\Models\User;
use App\Repositories\PostRepository;
use App\Services\PostService;

/**
 * 投稿 API（JSON を返す）。画面用の PostController と同じく「受け取って渡すだけ」
 *   入力チェック   → PostRequest（画面用と共通）
 *   DB の読み書き → PostRepository
 *   業務の手順     → PostService
 *   JSON の形      → PostResource
 */
class PostController extends Controller
{
    public function __construct(
        private PostService $postService,
        private PostRepository $postRepository
    ) {}

    // 一覧：GET /api/posts
    public function index()
    {
        $posts = $this->postRepository->getPublished();
        return PostResource::collection($posts);
    }

    // 1件取得：GET /api/posts/{id}
    public function show(int $id)
    {
        $post = $this->postRepository->findById($id);
        return new PostResource($post);
    }

    // 新規作成：POST /api/posts
    public function store(PostRequest $request)
    {
        $validated = $request->validated();

        // 今週の API は認証なし。Seeder で作ったテスト用ユーザーの投稿として保存する
        $validated['user_id'] = User::where('email', 'test@example.com')->firstOrFail()->id;

        $post = $this->postService->createPost($validated);
        return new PostResource($post->load('user'));
    }
}
