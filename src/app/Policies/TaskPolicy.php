<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

/**
 * 「この User は、この Task に対してこの操作をしていいか」を判断する（警備員）
 */
class TaskPolicy
{
    // 編集・更新・完了の切り替えができるのは、本人だけ
    public function update(User $user, Task $task): bool
    {
        return $user->id === $task->user_id;
    }

    // 削除できるのは、本人だけ
    public function delete(User $user, Task $task): bool
    {
        return $user->id === $task->user_id;
    }
}
