<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    // 一覧：GET /posts
    public function index()
    {
        $posts = Post::latest()->paginate(10);
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

        $post = Post::create($validated);

        return redirect()->route('posts.show', $post)
            ->with('success', '投稿を作成しました');
    }

    // 詳細：GET /posts/{post}
    public function show(Post $post)
    {
        return view('posts.show', compact('post'));
    }

    // 編集フォーム：GET /posts/{post}/edit
    public function edit(Post $post)
    {
        return view('posts.edit', compact('post'));
    }

    // 更新：PUT /posts/{post}
    public function update(Request $request, Post $post)
    {
        $validated = $request->validate([
            'title'    => 'required|max:200',
            'content'  => 'required',
            'category' => 'required|max:50',
        ]);

        $post->update($validated);

        return redirect()->route('posts.show', $post)
            ->with('success', '投稿を更新しました');
    }

    // 削除：DELETE /posts/{post}
    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()->route('posts.index')
            ->with('success', '投稿を削除しました');
    }
}
