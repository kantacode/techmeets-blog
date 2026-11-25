<?php

namespace App\Services;

use App\Models\Task;
use App\Repositories\TaskRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * タスクの「業務の手順」を担当する（料理長）
 */
class TaskService
{
    public function __construct(
        private TaskRepository $taskRepository
    ) {
    }

    public function createTask(array $data): Task
    {
        return DB::transaction(function () use ($data) {
            $task = $this->taskRepository->create($data);
            Log::info('Task created', ['task_id' => $task->id]);
            return $task;
        });
    }

    public function updateTask(Task $task, array $data): Task
    {
        return DB::transaction(function () use ($task, $data) {
            $updated = $this->taskRepository->update($task, $data);
            Log::info('Task updated', ['task_id' => $updated->id]);
            return $updated;
        });
    }

    public function deleteTask(Task $task): void
    {
        DB::transaction(function () use ($task) {
            $this->taskRepository->delete($task);
            Log::info('Task deleted', ['task_id' => $task->id]);
        });
    }

    // 完了 ⇔ 未完了 を切り替える
    public function toggleDone(Task $task): Task
    {
        return $this->taskRepository->update($task, ['is_done' => ! $task->is_done]);
    }
}
