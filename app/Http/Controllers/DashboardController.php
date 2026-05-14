<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        // Count total clients in the system
        $totalClients = Client::count();

        // Count projects with status 'active' using the scope we defined on the model
        $activeProjects = Project::active()->count();

        // Count tasks that are not yet done (todo + in_progress)
        $pendingTasks = Task::where('status', '!=', 'done')->count();

        // Get the 5 most recent activity log entries with the user relationship eager-loaded.
        // Eager loading prevents the N+1 query problem: without it, each activity item
        // would trigger a separate query to load its user.
        $recentActivity = ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function (ActivityLog $log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'subject_type' => $log->subject_type,
                    'subject_id' => $log->subject_id,
                    'description' => $log->description(),
                    'metadata' => $log->metadata,
                    'created_at' => $log->created_at->diffForHumans(),
                    'user' => $log->user ? [
                        'name' => $log->user->name,
                        'avatar_url' => $log->user->avatar_url,
                    ] : null,
                ];
            });

        return Inertia::render('Dashboard/Index', [
            'stats' => [
                'totalClients' => $totalClients,
                'activeProjects' => $activeProjects,
                'pendingTasks' => $pendingTasks,
            ],
            'recentActivity' => $recentActivity,
        ]);
    }
}