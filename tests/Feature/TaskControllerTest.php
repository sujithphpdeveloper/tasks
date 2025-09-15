<?php

namespace Tests\Feature;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $apiUrl =  '/api/v1/tasks';

    /**
     * Set up the test environment for Tasks
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin and a regular user for testing
        User::factory()->create(['role' => 'admin']);
        User::factory()->create(['role' => 'user']);

        // Create tasks for the regular user and the admin
        $user = User::where('role', 'user')->first();
        $admin = User::where('role', 'admin')->first();
    }

    /**
     * Test for not allowed unauthorized users for any activity
    */
    public function test_unauthenticated_users_cannot_access_tasks()
    {
        $response = $this->getJson($this->apiUrl);
        $response->assertStatus(401);
    }

    /**
     * Admin can view all tasks
    */
    public function test_admin_can_view_all_tasks()
    {
        $admin = User::where('role', 'admin')->first();
        $user = User::where('role', 'user')->first();

        Task::factory(5)->create(['assigned_to' => $user->id]);
        Task::factory(5)->create(['assigned_to' => $admin->id]);

        $response = $this->actingAs($admin)->getJson($this->apiUrl);
        $response->assertOk()->assertJsonCount(10, 'data');
    }

    /**
     * Create a task without invalid data
    */

    public function test_creating_a_task_with_invalid_data_validation_fails()
    {
        $user = User::where('role', 'user')->first();

        $response = $this->actingAs($user)->postJson($this->apiUrl, [
            'title' => '',
            'status' => 'invalid',
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'status']);
    }

    /**
     * Updating a task with invalid data
    */
    public function test_updating_a_task_with_invalid_data_validation_fails()
    {
        $user = User::where('role', 'user')->first();
        $task = Task::factory()->create(['assigned_to' => $user->id]);
        $response = $this->actingAs($user)->putJson($this->apiUrl . "/{$task->id}", [
            'title' => '',
            'status' => 'invalid',
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'status']);
    }

    /**
     * A User can view their own tasks
    */
    public function test_user_can_only_view_their_own_tasks()
    {
        $admin = User::where('role', 'admin')->first();
        Task::factory(5)->create(['assigned_to' => $admin->id]);
        $user = User::where('role', 'user')->first();
        Task::factory(5)->create(['assigned_to' => $user->id]);


        $myTasksCount = Task::where('assigned_to', $user->id)->count();
        $response = $this->actingAs($user)->getJson($this->apiUrl);
        $response->assertOk()->assertJsonCount($myTasksCount, 'data');
    }

    /**
     * Due date validation for a task with pending and in_progress
     */
    public function test_task_due_date_cannot_be_a_past_date_for_pending_or_inprogress()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);

        // Pending
        $response = $this->postJson($this->apiUrl, [
            'title'       => 'Invalid Task',
            'status'      => TaskStatus::PENDING->value,
            'due_date'    => now()->subDay()->toDateString(), // yesterday
            'priority'    => 'medium',
            'assigned_to' => $user->id,
            'version'     => 1,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['due_date']);

        // In Progress
        $response = $this->postJson($this->apiUrl, [
            'title'       => 'Invalid Task 2',
            'status'      => TaskStatus::IN_PROGRESS->value,
            'due_date'    => now()->subDays(5)->toDateString(), // 5 days ago
            'priority'    => 'high',
            'assigned_to' => $user->id,
            'version'     => 1,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['due_date']);
    }

    /**
     * Testing the Toggle Status of the Task
     */
    public function test_can_toggle_task_status()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);

        // task with a default status pending
        $task = Task::factory()->create([
            'status'      => TaskStatus::PENDING->value,
            'assigned_to' => $user->id,
            'version'     => 1,
        ]);

        // toggle the status from pending to in_progress
        $response = $this->patchJson($this->apiUrl . "/{$task->id}/toggle-status");
        $task->refresh();
        $response->assertOk()->assertJsonPath('task.status', TaskStatus::IN_PROGRESS->value);

        $task->refresh();
        $this->assertEquals(TaskStatus::IN_PROGRESS->value, $task->status);

        // toggle the status from in_progress to completed
        $response = $this->patchJson($this->apiUrl . "/{$task->id}/toggle-status");
        $response->assertOk()->assertJsonPath('task.status', TaskStatus::COMPLETED->value);

        $task->refresh();
        $this->assertEquals(TaskStatus::COMPLETED->value, $task->status);

        // toggle the status from completed to pending
        $response = $this->patchJson($this->apiUrl . "/{$task->id}/toggle-status");
        $response->assertOk()->assertJsonPath('task.status', TaskStatus::PENDING->value);

        $task->refresh();
        $this->assertEquals(TaskStatus::PENDING->value, $task->status);
    }

    /**
     * Test for the Optimistic Lock for the Task
     */
    public function test_optimistic_locking_of_task()
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);

        // Task created with default version 1
        $task = Task::factory()->create([
            'title'       => 'Sample Title',
            'assigned_to' => $user->id,
        ]);

        $originalVersion = $task->version;

        // Update the version
        $task->update([
            'title'   => 'Sample Title Updated',
            'version' => $task->version + 1,
        ]);

        // Try to update the task with old version
        $response = $this->putJson($this->apiUrl . "/{$task->id}", [
            'title'       => 'Updating with old version',
            'version'     => $originalVersion,
        ]);

        // Expect conflict
        $response->assertStatus(409);
        $response->assertJson([
            'message' => 'Task has been modified by another user. Please refresh and try again.',
        ]);
    }

    /**
     * Task can be deleted
    */
    public function test_task_can_be_soft_deleted()
    {
        $admin = User::where('role', 'admin')->first();
        $task = Task::factory()->create(['assigned_to' => $admin->id]);
        $response = $this->actingAs($admin)->deleteJson($this->apiUrl . "/{$task->id}");
        $response->assertOk();
        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }

    /**
     * Admin can restore the Deleted Task
    */
    public function test_task_can_be_restored()
    {
        $admin = User::where('role', 'admin')->first();
        $task = Task::factory()->create(['assigned_to' => $admin->id]);
        $task->delete();
        $response = $this->actingAs($admin)->patchJson($this->apiUrl . "/{$task->id}/restore");
        $response->assertOk();
        $this->assertNull($task->fresh()->deleted_at);
    }

    // All Filter Tests

    /**
     * Tasks can filter by Status
    */
    public function test_tasks_can_be_filtered_by_status()
    {
        $user = User::where('role', 'user')->first();
        $this->actingAs($user);
        Task::factory(5)->create(['assigned_to' => $user->id, 'status' => TaskStatus::PENDING->value]);
        $response = $this->getJson($this->apiUrl . '?status=pending');
        $response->assertOk()->assertJsonCount(5, 'data');
    }

    /**
     * Tasks can be filtered by priority
    */
    public function test_tasks_can_be_filtered_by_priority()
    {
        $user = User::where('role', 'user')->first();
        $this->actingAs($user);
        Task::factory(2)->create(['assigned_to' => $user->id, 'priority' => TaskPriority::HIGH->value]);
        $response = $this->getJson($this->apiUrl .'?priority=high');
        $response->assertOk()->assertJsonCount(2, 'data');
    }

    /**
     * Tasks can be filtered by Due Date
    */
    public function test_tasks_can_be_filtered_by_date_range()
    {
        $user = User::where('role', 'user')->first();
        $this->actingAs($user);
        Task::factory()->create(['assigned_to' => $user->id, 'due_date' => Carbon::parse('2025-09-20')]);
        Task::factory()->create(['assigned_to' => $user->id, 'due_date' => Carbon::parse('2025-09-18')]);
        $response = $this->getJson($this->apiUrl.'?due_date_from=2025-09-19&due_date_to=2025-09-21');
        $response->assertOk()->assertJsonCount(1, 'data');
    }

    /**
     * Tasks can filter using the keyword, implemented full text
    */
    public function test_tasks_can_be_searched_by_keyword()
    {
        $user = User::where('role', 'user')->first();
        $this->actingAs($user);
        Task::factory()->create(['assigned_to' => $user->id, 'title' => 'Important project Deadline']);
        Task::factory()->create(['assigned_to' => $user->id, 'title' => 'Important project delivery']);
        $response = $this->getJson($this->apiUrl . '?keyword=Deadline');
        $response->assertOk()->assertJsonCount(1, 'data');
    }

    /**
     * Tasks can filter by the tag
    */
    public function test_tasks_can_be_filtered_by_tags()
    {
        $user = User::where('role', 'user')->first();
        $this->actingAs($user);
        $tagDevelopment = Tag::factory()->create(['name' => 'Development']);
        $tagDesign = Tag::factory()->create(['name' => 'Design']);
        Task::factory(2)->for($user)->hasAttached($tagDesign)->create();
        Task::factory(2)->for($user)->hasAttached($tagDevelopment)->create();
        $response = $this->getJson($this->apiUrl. "?tags={$tagDevelopment->id}");
        $response->assertOk()->assertJsonCount(2, 'data');
    }


    // Test for Sorting
    /**
     * Tasks can sort by field with specific direction
    */
    public function test_tasks_can_be_sorted_by_field_with_direction()
    {
        $user = User::where('role', 'user')->first();
        $this->actingAs($user);
        Task::factory()->create(['assigned_to' => $user->id, 'priority' => 'low']);
        Task::factory()->create(['assigned_to' => $user->id, 'priority' => 'high']);
        $response = $this->getJson($this->apiUrl.'?sort_by=priority&sort_direction=desc');
        $response->assertOk()->assertJsonPath('data.0.priority', 'high');
    }

}
