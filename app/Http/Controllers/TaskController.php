<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Create a new task within a project.
     * New tasks are added to the bottom of their status column.
     */
    public function store(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'description' => 'nullable',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'nullable|in:todo,in_progress,done',
        ]);

        // Calculate the next sort_order for the given status column.
        // This puts the new task at the bottom of the column.
        $status = $validated['status'] ?? 'todo';
        $maxSortOrder = $project->tasks()
            ->where('status', $status)
            ->max('sort_order') ?? -1;

        $task = $project->tasks()->create([
            ...$validated,
            'status' => $status,
            'sort_order' => $maxSortOrder + 1,
        ]);

        ActivityLog::log(auth()->user(), 'created', $task, [
            'title' => $task->title,
            'project_id' => $project->id,
            'project_name' => $project->name,
        ]);

        return redirect()->back()->with('success', 'Task added.');
    }

    /**
     * Update task details (title, description, assignee).
     */
    public function update(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);
    
        $validated = $request->validate([
            'title' => 'required|max:255',
            'description' => 'nullable',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        // Convert empty string to null for the foreign key
        if (isset($validated['assigned_to']) && $validated['assigned_to'] === '') {
            $validated['assigned_to'] = null;
        }

        $task->update($validated);

        ActivityLog::log(auth()->user(), 'updated', $task, [
            'title' => $task->title,
            'project_id' => $task->project_id,
        ]);

        return redirect()->back()->with('success', 'Task updated.');
    }

    /**
     * Change a task's status (used by drag-and-drop and arrow buttons).
     * Updates the sort_order to place the task at the bottom of the target column.
     */
    public function updateStatus(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('updateStatus', $task);

        $validated = $request->validate([
            'status' => 'required|in:todo,in_progress,done',
        ]);

        $oldStatus = $task->status;
        $newStatus = $validated['status'];

        // If the status actually changed, update sort_order for the new column
        if ($oldStatus !== $newStatus) {
            $maxSortOrder = Task::where('project_id', $task->project_id)
                ->where('status', $newStatus)
                ->max('sort_order') ?? -1;

            $task->update([
                'status' => $newStatus,
                'sort_order' => $maxSortOrder + 1,
            ]);

            ActivityLog::log(auth()->user(), 'status_changed', $task, [
                'title' => $task->title,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'project_id' => $task->project_id,
            ]);
        }

        return redirect()->back();
    }

    /**
     * Delete a task.
     */
    public function destroy(Task $task): RedirectResponse
    {
        $this->authorize('delete', $task);

        $taskTitle = $task->title;
        $projectId = $task->project_id;

        ActivityLog::log(auth()->user(), 'deleted', $task, [
            'title' => $taskTitle,
            'project_id' => $projectId,
        ]);

        $task->delete();

        return redirect()->back()->with('success', "Task \"{$taskTitle}\" deleted.");
    }
}