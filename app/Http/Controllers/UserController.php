<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Display a list of all users with optional search filtering.
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', User::class);

        $query = User::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => [
                'search' => $search,
            ],
            'can' => [
                'create' => auth()->user()->can('create', User::class),
            ],
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): Response
    {
        $this->authorize('create', User::class);

        return Inertia::render('Users/Create', [
            'roles' => User::ROLES,
        ]);
    }

    /**
     * Validate and store a new user in the database.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:' . implode(',', User::ROLES),
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;

        $user = User::create($validated);

        ActivityLog::log(auth()->user(), 'created', $user, [
            'name' => $user->name,
            'role' => $user->role,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing an existing user.
     */
    public function edit(User $user): Response
    {
        $this->authorize('update', $user);

        return Inertia::render('Users/Edit', [
            'user' => $user,
            'roles' => User::ROLES,
        ]);
    }

    /**
     * Validate and update the user record.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => 'required|max:255',
            'role' => 'required|in:' . implode(',', User::ROLES),
            'is_active' => 'boolean',
        ]);

        $user->update($validated);

        ActivityLog::log(auth()->user(), 'updated', $user, [
            'name' => $user->name,
            'role' => $user->role,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Delete a user.
     */
    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        if (auth()->id() === $user->id) {
            return redirect()->back()
                ->with('error', 'You cannot delete your own account.');
        }

        $userName = $user->name;

        ActivityLog::log(auth()->user(), 'deleted', $user, [
            'name' => $userName,
        ]);

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', "User \"{$userName}\" deleted.");
    }
}
