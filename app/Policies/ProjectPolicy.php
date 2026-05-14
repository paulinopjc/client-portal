<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Any authenticated user can view the project list.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Any authenticated user can view a single project.
     */
    public function view(User $user, Project $project): bool
    {
        return true;
    }

    /**
     * Any authenticated user can create projects.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Only the creator or an admin can update a project.
     */
    public function update(User $user, Project $project): bool
    {
        return $user->isAdmin() || $user->id === $project->created_by;
    }

    /**
     * Only the creator or an admin can delete a project.
     */
    public function delete(User $user, Project $project): bool
    {
        return $user->isAdmin() || $user->id === $project->created_by;
    }

    /**
     * Only the creator or an admin can change project status.
     */
    public function updateStatus(User $user, Project $project): bool
    {
        return $user->isAdmin() || $user->id === $project->created_by;
    }
}