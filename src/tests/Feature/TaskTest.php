<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Week 9 のタスク管理（/tasks）の Feature Test
 * タスクは「自分のものだけ」見られ、触れる（TaskPolicy）
 */
class TaskTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    private function makeTask(User $owner, array $attributes = []): Task
    {
        return Task::create(array_merge([
            'user_id' => $owner->id,
            'title'   => 'テストのタスク',
            'is_done' => false,
        ], $attributes));
    }

    // ---------- 一覧 ----------

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/tasks')->assertRedirect('/login');
    }

    public function test_user_sees_only_own_tasks(): void
    {
        $this->makeTask($this->user, ['title' => '自分のタスク']);
        $this->makeTask(User::factory()->create(), ['title' => '他人のタスク']);

        $response = $this->actingAs($this->user)->get('/tasks');

        $response->assertOk();
        $response->assertSee('自分のタスク');
        $response->assertDontSee('他人のタスク');
    }

    // 並び順：未完了が上 → 期限が近い順 → 期限なしは最後
    public function test_tasks_are_ordered_by_done_then_due_date(): void
    {
        $this->makeTask($this->user, ['title' => '完了済み', 'is_done' => true, 'due_date' => '2026-01-01']);
        $this->makeTask($this->user, ['title' => '期限なし']);
        $this->makeTask($this->user, ['title' => '期限が遠い', 'due_date' => '2026-12-31']);
        $this->makeTask($this->user, ['title' => '期限が近い', 'due_date' => '2026-12-01']);

        $response = $this->actingAs($this->user)->get('/tasks');

        $titles = $response->viewData('tasks')->pluck('title')->all();
        $this->assertSame(['期限が近い', '期限が遠い', '期限なし', '完了済み'], $titles);
    }

    // ---------- 作成 ----------

    public function test_user_can_view_create_form(): void
    {
        $this->actingAs($this->user)->get('/tasks/create')->assertOk();
    }

    public function test_user_can_create_task(): void
    {
        $response = $this->actingAs($this->user)->post('/tasks', [
            'title'    => '牛乳を買う',
            'due_date' => '2026-12-01',
        ]);

        $response->assertRedirect(route('tasks.index'));
        $this->assertDatabaseHas('tasks', ['title' => '牛乳を買う', 'user_id' => $this->user->id, 'is_done' => false]);
    }

    public function test_title_is_required(): void
    {
        $response = $this->actingAs($this->user)->post('/tasks', ['title' => '']);

        $response->assertSessionHasErrors('title');
    }

    // 境界値：タイトル100文字 → OK / 101文字 → NG
    public function test_title_with_100_characters_is_accepted(): void
    {
        $response = $this->actingAs($this->user)->post('/tasks', ['title' => str_repeat('あ', 100)]);

        $response->assertSessionHasNoErrors();
    }

    public function test_title_with_101_characters_is_rejected(): void
    {
        $response = $this->actingAs($this->user)->post('/tasks', ['title' => str_repeat('あ', 101)]);

        $response->assertSessionHasErrors('title');
    }

    public function test_invalid_due_date_is_rejected(): void
    {
        $response = $this->actingAs($this->user)->post('/tasks', ['title' => 'タスク', 'due_date' => '日付ではない']);

        $response->assertSessionHasErrors('due_date');
    }

    // ---------- 更新・完了の切り替え ----------

    public function test_owner_can_view_edit_form(): void
    {
        $task = $this->makeTask($this->user);

        $this->actingAs($this->user)->get("/tasks/{$task->id}/edit")->assertOk();
    }

    public function test_owner_can_update_task(): void
    {
        $task = $this->makeTask($this->user);

        $response = $this->actingAs($this->user)->put("/tasks/{$task->id}", ['title' => '更新後']);

        $response->assertRedirect(route('tasks.index'));
        $this->assertSame('更新後', $task->fresh()->title);
    }

    public function test_owner_can_toggle_task_done_and_back(): void
    {
        $task = $this->makeTask($this->user);

        $this->actingAs($this->user)->patch("/tasks/{$task->id}/toggle");
        $this->assertTrue($task->fresh()->is_done);

        $this->actingAs($this->user)->patch("/tasks/{$task->id}/toggle");
        $this->assertFalse($task->fresh()->is_done);
    }

    // ---------- 削除 ----------

    public function test_owner_can_delete_task(): void
    {
        $task = $this->makeTask($this->user);

        $response = $this->actingAs($this->user)->delete("/tasks/{$task->id}");

        $response->assertRedirect(route('tasks.index'));
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    // ---------- 認可（他人のタスク） ----------

    public function test_other_user_cannot_edit_update_toggle_or_delete(): void
    {
        $task = $this->makeTask(User::factory()->create(), ['title' => '他人のタスク']);

        $this->actingAs($this->user)->get("/tasks/{$task->id}/edit")->assertForbidden();
        $this->actingAs($this->user)->put("/tasks/{$task->id}", ['title' => '乗っ取り'])->assertForbidden();
        $this->actingAs($this->user)->patch("/tasks/{$task->id}/toggle")->assertForbidden();
        $this->actingAs($this->user)->delete("/tasks/{$task->id}")->assertForbidden();

        $this->assertSame('他人のタスク', $task->fresh()->title);
        $this->assertFalse($task->fresh()->is_done);
    }
}
