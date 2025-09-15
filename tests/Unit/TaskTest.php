<?php

namespace Tests\Unit;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Soft delete test
     */
    public function test_task_can_be_soft_deleted()
    {
        $user = User::factory()->create(); // User Created
        $task = Task::factory()->create(['assigned_to' => $user->id]); // Task Created
        $task->delete(); // Task deleted
        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }

    /**
     * Exclude the deleted tasks from the queries
     */
    public function test_exclude_deleted_tasks_from_queries()
    {
        $user = User::factory()->create(); // User Created
        $task = Task::factory()->create(['assigned_to' => $user->id]); // Task Created
        $task->delete(); // Task deleted
        $tasks = Task::all(); // List all tasks
        $this->assertCount(0, $tasks);
    }

    /**
     * Deleted Task can Restore
     */
    public function test_deleted_tasks_can_be_restored()
    {
        $user = User::factory()->create(); // User Created
        $task = Task::factory()->create(['assigned_to' => $user->id]); // Task Created
        $task->delete(); // Task deleted
        $task->restore(); // Restore the Task
        $this->assertNull($task->fresh()->deleted_at); // Check the task restored based ont he deleted_at value is null
        $this->assertCount(1, Task::all());
    }

    /**
     * Checking the task is belongs to a user
    */
    public function test_task_belongs_to_assigned_user()
    {
        $user = User::factory()->create(); // User Created
        $task = Task::factory()->create(['assigned_to' => $user->id]); // Task created
        $this->assertInstanceOf(User::class, $task->user);
        $this->assertEquals($user->id, $task->user->id);
    }

    /**
     * One task may have many tags
    */
    public function test_task_has_many_tags()
    {
        $task = Task::factory()->create();
        $tags = Tag::factory(3)->create();
        $task->tags()->attach($tags);
        $this->assertCount(3, $task->tags);
        $this->assertTrue($task->tags->contains($tags->first()));
    }
}
