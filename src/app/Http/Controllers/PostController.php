<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Repositories\PostRepository;
use App\Services\PostService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * 投稿の「受け取って、担当に渡して、画面を返す」だけを担当する（交通整理）
 *   DB の読み書き → PostRepository
 *   業務の手順     → PostService
 *   本人チェック   → PostPolicy
 */
class PostController extends Controller
{
    public function __construct(
        private PostService $postService,
        private PostRepository $postRepository
    ) {}

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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'    => 'required|max:200',
            'content'  => 'required',
            'category' => 'required|max:50',
        ]);
        $validated['user_id'] = auth()->id(); // 投稿者はフォームではなく、ログイン中のユーザー

        $post = $this->postService->createPost($validated);

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
    public function update(Request $request, Post $post)
    {
        Gate::authorize('update', $post);

        $validated = $request->validate([
            'title'    => 'required|max:200',
            'content'  => 'required',
            'category' => 'required|max:50',
        ]);

        $post = $this->postService->updatePost($post, $validated);

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
