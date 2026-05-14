<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    /**
     * Display a list of all projects with optional status filtering.
     */
    public function index(Request $request): Response
    {
        $query = Project::with(['client', 'creator'])
            ->withCount('tasks');

        // Filter by status if provided
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Search by project name
        if ($search = $request->input('search')) {
            $query->where('name', 'ILIKE', "%{$search}%");
        }

        $projects = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
            'filters' => [
                'status' => $status,
                'search' => $search,
            ],
            'statuses' => Project::STATUSES,
        ]);
    }

    /**
     * Show the form for creating a new project.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('Projects/Create', [
            'clients' => Client::orderBy('name')->get(['id', 'name', 'company']),
            'preselectedClientId' => $request->input('client_id'),
        ]);
    }

    /**
     * Validate and store a new project.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'client_id' => 'required|exists:clients,id',
            'description' => 'nullable',
            'deadline' => 'nullable|date|after:today',
        ]);

        $project = Project::create([
            ...$validated,
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);

        ActivityLog::log(auth()->user(), 'created', $project, [
            'name' => $project->name,
            'client_id' => $project->client_id,
        ]);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project created successfully.');
    }

    /**
     * Display a single project with its tasks (for the kanban board).
     */
    public function show(Project $project): Response
    {
        $project->load(['client', 'creator', 'tasks' => function ($query) {
            $query->with('assignee')->orderBy('sort_order');
        }]);

        // Get activity log entries for this project
        $activity = ActivityLog::with('user')
            ->where(function ($query) use ($project) {
                // Activity on the project itself
                $query->where('subject_type', 'project')
                    ->where('subject_id', $project->id);
            })
            ->orWhere(function ($query) use ($project) {
                // Activity on tasks belonging to this project
                $query->where('subject_type', 'task')
                    ->whereIn('subject_id', $project->tasks->pluck('id'));
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function (ActivityLog $log) {
                return [
                    'id' => $log->id,
                    'description' => $log->description(),
                    'metadata' => $log->metadata,
                    'created_at' => $log->created_at->diffForHumans(),
                    'user' => $log->user ? [
                        'name' => $log->user->name,
                        'avatar_url' => $log->user->avatar_url,
                    ] : null,
                ];
            });

        // Get all users for task assignment dropdown
        $users = User::active()
            ->orderBy('name')
            ->get(['id', 'name', 'avatar_url']);

        return Inertia::render('Projects/Show', [
            'project' => $project,
            'activity' => $activity,
            'users' => $users,
            'taskStatuses' => Task::STATUSES,
            'can' => [
                'edit' => auth()->user()->can('update', $project),
                'delete' => auth()->user()->can('delete', $project),
                'updateStatus' => auth()->user()->can('updateStatus', $project),
            ],
        ]);
    }

    /**
     * Show the form for editing an existing project.
     */
    public function edit(Project $project): Response
    {
        $this->authorize('update', $project);

        return Inertia::render('Projects/Edit', [
            'project' => $project,
            'clients' => Client::orderBy('name')->get(['id', 'name', 'company']),
        ]);
    }

    /**
     * Validate and update the project.
     */
    public function update(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'name' => 'required|max:255',
            'client_id' => 'required|exists:clients,id',
            'description' => 'nullable',
            'deadline' => 'nullable|date|after:today',
        ]);

        $project->update($validated);

        ActivityLog::log(auth()->user(), 'updated', $project, [
            'name' => $project->name,
        ]);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project updated successfully.');
    }

    /**
     * Update just the project's status.
     * Only allows valid transitions: draft -> active -> completed.
     */
    public function updateStatus(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('updateStatus', $project);

        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', Project::STATUSES),
        ]);

        $oldStatus = $project->status;
        $newStatus = $validated['status'];

        $project->update(['status' => $newStatus]);

        ActivityLog::log(auth()->user(), 'status_changed', $project, [
            'name' => $project->name,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);

        return redirect()->back()
            ->with('success', "Project status changed to \"{$newStatus}\".");
    }

    /**
     * Delete a project and all its tasks.
     */
    public function destroy(Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        $projectName = $project->name;
        $clientId = $project->client_id;

        ActivityLog::log(auth()->user(), 'deleted', $project, [
            'name' => $projectName,
        ]);

        $project->delete();

        return redirect()->route('clients.show', $clientId)
            ->with('success', "Project \"{$projectName}\" deleted.");
    }
}