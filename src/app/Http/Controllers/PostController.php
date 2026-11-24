<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostRequest;
use App\Models\Post;
use App\Repositories\PostRepository;
use App\Services\PostService;
use Illuminate\Support\Facades\Gate;

/**
 * 投稿の「受け取って、担当に渡して、画面を返す」だけを担当する（交通整理）
 *   入力チェック   → PostRequest
 *   DB の読み書き → PostRepository
 *   業務の手順     → PostService
 *   本人チェック   → PostPolicy
 */
class PostController extends Controller
{
    public function __construct(
        private PostService $postService,
        private PostRepository $postRepository
    ) {
    }

    // 一覧：GET /posts
    public function index()
    {
        $posts = $this->postRepository->getPublished();
        return view('posts.index', compact('posts'));
    }

    // 作成フォーム：GET /posts/create
    public function create()
    {
        return view('posts.create');
    }

    // 保存：POST /posts
    public function store(PostRequest $request)
    {
        // 画像ファイルそのものは DB に入れないので、入力値からは外して別に渡す
        $validated = $request->safe()->except('image');
        $validated['user_id'] = auth()->id(); // 投稿者はフォームではなく、ログイン中のユーザー

        $post = $this->postService->createPost($validated, $request->file('image'));

        return redirect()->route('posts.show', $post)->with('success', '投稿を作成しました');
    }

    // 詳細：GET /posts/{post}
    public function show(Post $post)
    {
        $post = $this->postRepository->loadForShow($post);
        return view('posts.show', compact('post'));
    }

    // 編集フォーム：GET /posts/{post}/edit
    public function edit(Post $post)
    {
        Gate::authorize('update', $post); // 本人でなければ自動で 403
        return view('posts.edit', compact('post'));
    }

    // 更新：PUT /posts/{post}
    public function update(PostRequest $request, Post $post)
    {
        Gate::authorize('update', $post);

        $post = $this->postService->updatePost(
            $post,
            $request->safe()->except('image'),
            $request->file('image')
        );

        return redirect()->route('posts.show', $post)->with('success', '投稿を更新しました');
    }

    // 削除：DELETE /posts/{post}
    public function destroy(Post $post)
    {
        Gate::authorize('delete', $post);

        $this->postService->deletePost($post);

        return redirect()->route('posts.index')->with('success', '投稿を削除しました');
    }
}
