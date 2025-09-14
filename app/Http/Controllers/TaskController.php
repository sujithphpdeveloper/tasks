<?php

namespace App\Http\Controllers;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Filters\TaskFilter;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\TaskFilterRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(TaskFilterRequest $request): JsonResponse
    {
        // Authorize here anyonce can see any task, but will add filters for the admin and user
        $this->authorize('viewAny', Task::class);

        // Adding the role based filter
        $user = Auth::user();
        if ($user->role === 'admin') {
            $tasks = Task::query();
        } else {
            $tasks = Task::where('assigned_to', $user->id);
        }

        // Implement the filters using the scope
        $tasks->filter($request);

        // Sorting based on the requested field and direction
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $tasks->orderBy($sortBy, $sortDirection);

        // Pagination methods for offset and cursor based
        $perPage = $request->input('per_page', 10);

        // If the 'cursor' parameter is present, use cursor pagination.
        if ($request->has('cursor')) {
            $tasks = $tasks->cursorPaginate($perPage);
        } else {
            // Otherwise, use standard offset-based pagination.
            $tasks = $tasks->paginate($perPage);
        }

        // return the tasks based on the filters and sorting
        return response()->json($tasks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $validateTask = $request->validated();

        $task = new Task();
        $task->title = $validateTask['title'];
        $task->description = $validateTask['description'] ?? null;
        $task->status = $validateTask['status'] ?? TaskStatus::PENDING;
        $task->priority = $validateTask['priority'] ?? TaskPriority::MEDIUM;
        $task->due_date = $validateTask['due_date'] ?? null;
        $task->assigned_to = $validateTask['assigned_to'] ?? null;
        $task->version = 1;
        $task->metadata = $validateTask['metadata'] ?? null;

        //Save the New Task
        $task->save();

        // Adding the tags if there is any
        if (isset($validateTask['tags'])) {
            $task->tags()->attach($validateTask['tags']);
        }

        //Add the details to Log
        $this->addTaskLog($task, 'create', $task->toArray());

        return response()->json(['message' => 'Task created successfully', 'task' => $task->load('tags')], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task): JsonResponse
    {
        // Admin can view any Task, user can only view their tasks
        $this->authorize('view', $task);

        return response()->json($task->load('user', 'tags'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        // Admin can update any task and user can only update their own tasks
        $this->authorize('update', $task);

        $validatedTask = $request->validated();

        // Optimistic Locking
        // The form should have the current value from the database, will check this with the new value
        if ((int)$validatedTask['version'] !== $task->version) {
            return response()->json(['message' => 'Task has been modified by another user. Please refresh and try again.'], 409);
        }

        // adding incremented version to the task
        $validatedTask['version']++;

        $task->update($validatedTask);

        // Update tags if provided in the request
        if (isset($validatedTask['tags'])) {
            $task->tags()->sync($validatedTask['tags']);
        }

        $changedValues =  $task->getChanges();
        unset($changedValues['updated_at']);
        if(!empty($changedValues)) {
            // Update the task details to log
            $this->addTaskLog($task, 'update', $task->getChanges());
        }

        return response()->json(['message' => 'Task updated successfully', 'task' => $task->load('tags')]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task): JsonResponse
    {
        // Admin can delete any task and user can only delete their own tasks
        $this->authorize('delete', $task);

        $task->delete();

        //Update the deletion into Task Log
        $this->addTaskLog($task, 'delete', ['deleted_at' => $task->deleted_at]);

        return response()->json(['message' => 'Task deleted successfully']);
    }

    /**
     * Restore the deleted task based on the request
     */
    public function restore($id): JsonResponse
    {
        $task = Task::withTrashed()->findOrFail($id);
        $task->restore();

        //Update the log when task restored
        $this->addTaskLog($task, 'update', ['deleted_at' => $task->deleted_at]);

        return response()->json(['message' => 'Task restored successfully', 'task' => $task->load('tags')]);
    }

    /**
     * Implement the Status Cycling using this function
     */
    public function toggleStatus(Task $task): JsonResponse
    {
        $oldStatus = $task->status;

        switch ($task->status) {
            case TaskStatus::PENDING->value:
                $task->status = TaskStatus::IN_PROGRESS->value;
                break;
            case TaskStatus::IN_PROGRESS->value:
                $task->status = TaskStatus::COMPLETED->value;
                break;
            case TaskStatus::COMPLETED->value:
                $task->status = TaskStatus::PENDING->value;
                break;
        }
        $newStatus = $task->status;
        $task->save();

        // Update the logs with the status change
        $this->addTaskLog($task, 'update', ['old_status' => $oldStatus, 'new_status' => $newStatus]);

        return response()->json(['message' => 'Task status updated successfully', 'task' => $task]);
    }

    // Update the logs for each action on the Tasks
    public function addTaskLog(Task $task, string $operationType, array $changes = []): void
    {
        $task->logs()->create([
            'user_id'        => auth()->id(),
            'operation_type' => $operationType,
            'changes'        => $changes,
        ]);
    }
}
