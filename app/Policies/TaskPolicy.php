<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Allow admin and user to view all the tasks
        // The assigned tasks for the user will be manage by the filters
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        // Admins can view any task but user can only view the task assigned to them
        return $user->role === 'admin' || $user->id === $task->assigned_to;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        //Admin or User can create new tasks
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {
        // Admins can update any task but users can only update the task assigned to them
        return $user->role === 'admin' || $user->id === $task->assigned_to;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        // Admins can delete any task but user can only delete the task assigned to them
        return $user->role === 'admin' || $user->id === $task->assigned_to;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Task $task): bool
    {
        // Admins can restore any task but user can only restore the task assigned to them
        return $user->role === 'admin' || $user->id === $task->assigned_to;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Task $task): bool
    {
        return false;
    }
}
