<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    /**
     * Valid actions for the action column.
     * The database CHECK constraint must stay in sync with this array.
     */
    public const ACTIONS = ['created', 'updated', 'deleted', 'status_changed'];

    /**
     * The table associated with the model.
     * By default, Eloquent would look for 'activity_logs' (pluralized).
     * Our table is named 'activity_log' (singular), so we specify it.
     */
    protected $table = 'activity_log';

    /**
     * Activity logs are append-only. There is no updated_at column.
     */
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * The user who performed this action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get the subject model (Client, Project, or Task).
     * This is a manual polymorphic lookup since we store short names
     * ('client', 'project', 'task') instead of full class names.
     */
    public function subject(): ?Model
    {
        $map = [
            'client' => Client::class,
            'project' => Project::class,
            'task' => Task::class,
            'user' => User::class,
        ];

        $modelClass = $map[$this->subject_type] ?? null;

        if (! $modelClass) {
            return null;
        }

        return $modelClass::find($this->subject_id);
    }

    /**
     * Create an activity log entry.
     *
     * Usage:
     *   ActivityLog::log(auth()->user(), 'created', $client);
     *   ActivityLog::log(auth()->user(), 'status_changed', $project, [
     *       'old_status' => 'draft',
     *       'new_status' => 'active',
     *   ]);
     */
    public static function log(?User $user, string $action, Model $subject, array $metadata = []): self
    {
        $typeMap = [
            Client::class => 'client',
            Project::class => 'project',
            Task::class => 'task',
            User::class => 'user',
        ];

        return self::create([
            'user_id' => $user?->id,
            'action' => $action,
            'subject_type' => $typeMap[get_class($subject)] ?? get_class($subject),
            'subject_id' => $subject->id,
            'metadata' => $metadata ?: null,
            'created_at' => now(),
        ]);
    }

    /**
     * Build a human-readable description of this activity.
     * Examples:
     *   "Paulino Awino created client Acme Corp"
     *   "Paulino Awino moved task Fix navbar to Done"
     */
    public function description(): string
    {
        $userName = $this->user?->name ?? 'System';
        $subjectName = $this->metadata['name'] ?? $this->metadata['title'] ?? '';

        if ($this->action === 'status_changed') {
            $oldStatus = $this->metadata['old_status'] ?? '';
            $newStatus = $this->metadata['new_status'] ?? '';
            return "{$userName} moved {$this->subject_type} {$subjectName} from {$oldStatus} to {$newStatus}";
        }

        $action = str_replace('_', ' ', $this->action);
        return trim("{$userName} {$action} {$this->subject_type} {$subjectName}");
    }
}