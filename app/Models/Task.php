<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    /**
     * Valid statuses for the status column.
     * The database CHECK constraint must stay in sync with this array.
     */
    public const STATUSES = ['todo', 'in_progress', 'done'];

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'status',
        'assigned_to',
        'sort_order',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * The project this task belongs to.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * The user assigned to this task (nullable).
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope: only tasks with status 'todo'.
     */
    public function scopeTodo($query)
    {
        return $query->where('status', 'todo');
    }

    /**
     * Scope: only tasks with status 'in_progress'.
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope: only tasks with status 'done'.
     */
    public function scopeDone($query)
    {
        return $query->where('status', 'done');
    }

    /**
     * Scope: tasks ordered by sort_order for kanban display.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Scope: tasks assigned to a specific user.
     */
    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope: unassigned tasks.
     */
    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to');
    }
}