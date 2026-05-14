<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Any authenticated user can create tasks in a project.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Only the project creator, the task assignee, or an admin can update a task.
     */
    public function update(User $user, Task $task): bool
    {
        return $user->isAdmin()
            || $user->id === $task->project->created_by
            || $user->id === $task->assigned_to;
    }

    /**
     * Only the project creator or an admin can delete a task.
     */
    public function delete(User $user, Task $task): bool
    {
        return $user->isAdmin()
            || $user->id === $task->project->created_by;
    }

    /**
     * Any authenticated user can move tasks (change status).
     * This keeps the kanban board usable for the whole team.
     */
    public function updateStatus(User $user, Task $task): bool
    {
        return true;
    }
}