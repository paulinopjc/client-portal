<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    /**
     * Valid statuses for the status column.
     * The database CHECK constraint must stay in sync with this array.
     */
    public const STATUSES = ['draft', 'active', 'completed'];

    protected $fillable = [
        'client_id',
        'name',
        'description',
        'status',
        'deadline',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'date',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * The client this project belongs to.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * The user who created this project.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * All tasks in this project.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope: only active projects (status = 'active').
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: only draft projects.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope: only completed projects.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope: projects with an upcoming deadline (within 7 days).
     */
    public function scopeDeadlineSoon($query)
    {
        return $query->whereNotNull('deadline')
                     ->where('deadline', '<=', now()->addDays(7))
                     ->where('deadline', '>=', now());
    }
}