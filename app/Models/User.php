<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    /**
     * Valid roles for the role column.
     * The database CHECK constraint must stay in sync with this array.
     */
    public const ROLES = ['admin', 'member'];

    /**
     * Disable remember token — this app uses Google OAuth only, no "remember me".
     */
    protected $rememberTokenName = false;

    /**
     * The attributes that are mass assignable.
     * These are the only fields that can be set via User::create() or $user->update().
     */
    protected $fillable = [
        'name',
        'email',
        'google_sub',
        'avatar_url',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     * These won't appear when converting the model to JSON/array.
     */
    protected $hidden = [
        'google_sub',
    ];

    /**
     * Get the attributes that should be cast.
     * Casts automatically convert database values to PHP types.
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Clients created by this user.
     */
    public function clients(): HasMany
    {
        return $this->hasMany(Client::class, 'created_by');
    }

    /**
     * Tasks assigned to this user.
     */
    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    /**
     * Activity log entries for this user.
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    |
    | Scopes are reusable query constraints. Instead of writing
    | User::where('is_active', true)->get() everywhere, you write
    | User::active()->get(). Cleaner and more maintainable.
    |
    */

    /**
     * Scope: only active users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: only admin users.
     */
    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}