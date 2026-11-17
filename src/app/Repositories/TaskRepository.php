<?php

namespace App\Repositories;

use App\Models\Task;

/**
 * タスクの「DB の読み書き」だけを担当する（倉庫係）
 */
class TaskRepository
{
    // 自分のタスクだけ取得：未完了が上、期限が近い順、10件ずつ
    public function getByUser(int $userId)
    {
        return Task::where('user_id', $userId)
            ->orderBy('is_done')
            ->orderBy('due_date')
            ->paginate(10);
    }

    public function create(array $data): Task
    {
        return Task::create($data);
    }

    public function update(Task $task, array $data): Task
    {
        $task->update($data);
        return $task;
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }
}