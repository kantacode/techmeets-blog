<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Models\Task;
use App\Repositories\TaskRepository;
use App\Services\TaskService;
use Illuminate\Support\Facades\Gate;

/**
 * タスクの「受け取って、担当に渡して、画面を返す」だけを担当する（交通整理）
 */
class TaskController extends Controller
{
    public function __construct(
        private TaskService $taskService,
        private TaskRepository $taskRepository
    ) {
    }

    // 一覧（自分のタスクだけ）：GET /tasks
    public function index()
    {
        $tasks = $this->taskRepository->getByUser(auth()->id());
        return view('tasks.index', compact('tasks'));
    }

    // 作成フォーム：GET /tasks/create
    public function create()
    {
        return view('tasks.create');
    }

    // 保存：POST /tasks
    public function store(TaskRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id'] = auth()->id();

        $this->taskService->createTask($validated);

        return redirect()->route('tasks.index')->with('success', 'タスクを追加しました');
    }

    // 編集フォーム：GET /tasks/{task}/edit
    public function edit(Task $task)
    {
        Gate::authorize('update', $task);
        return view('tasks.edit', compact('task'));
    }

    // 更新：PUT /tasks/{task}
    public function update(TaskRequest $request, Task $task)
    {
        Gate::authorize('update', $task);

        $this->taskService->updateTask($task, $request->validated());

        return redirect()->route('tasks.index')->with('success', 'タスクを更新しました');
    }

    // 削除：DELETE /tasks/{task}
    public function destroy(Task $task)
    {
        Gate::authorize('delete', $task);

        $this->taskService->deleteTask($task);

        return redirect()->route('tasks.index')->with('success', 'タスクを削除しました');
    }

    // 完了 ⇔ 未完了 の切り替え：PATCH /tasks/{task}/toggle
    public function toggle(Task $task)
    {
        Gate::authorize('update', $task);

        $this->taskService->toggleDone($task);

        return redirect()->route('tasks.index');
    }
}
