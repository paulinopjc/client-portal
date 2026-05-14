<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    /**
     * Display a list of all clients with optional search filtering.
     */
    public function index(Request $request): Response
    {
        $query = Client::with('creator')
            ->withCount('projects');

        // Apply search filter if a search term is present in the query string
        if ($search = $request->input('search')) {
            $query->search($search);
        }

        $clients = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
            'filters' => [
                'search' => $search,
            ],
            'can' => [
                'create' => auth()->user()->can('create', Client::class),
            ],
        ]);
    }

    /**
     * Show the form for creating a new client.
     */
    public function create(): Response
    {
        return Inertia::render('Clients/Create');
    }

    /**
     * Validate and store a new client in the database.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'company' => 'nullable|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|max:50',
            'notes' => 'nullable',
        ]);

        $client = Client::create([
            ...$validated,
            'created_by' => auth()->id(),
        ]);

        ActivityLog::log(auth()->user(), 'created', $client, [
            'name' => $client->name,
        ]);

        return redirect()->route('clients.show', $client)
            ->with('success', 'Client created successfully.');
    }

    /**
     * Display a single client with their projects.
     */
    public function show(Client $client): Response
    {
        $client->load(['creator', 'projects' => function ($query) {
            $query->withCount('tasks')->orderBy('created_at', 'desc');
        }]);

        return Inertia::render('Clients/Show', [
            'client' => $client,
            'can' => [
                'edit' => auth()->user()->can('update', $client),
                'delete' => auth()->user()->can('delete', $client),
            ],
        ]);
    }

    /**
     * Show the form for editing an existing client.
     */
    public function edit(Client $client): Response
    {
        $this->authorize('update', $client);

        return Inertia::render('Clients/Edit', [
            'client' => $client,
        ]);
    }

    /**
     * Validate and update the client record.
     */
    public function update(Request $request, Client $client): RedirectResponse
    {
        $this->authorize('update', $client);

        $validated = $request->validate([
            'name' => 'required|max:255',
            'company' => 'nullable|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|max:50',
            'notes' => 'nullable',
        ]);

        $client->update($validated);

        ActivityLog::log(auth()->user(), 'updated', $client, [
            'name' => $client->name,
        ]);

        return redirect()->route('clients.show', $client)
            ->with('success', 'Client updated successfully.');
    }

    /**
     * Delete a client and all their associated projects/tasks.
     */
    public function destroy(Client $client): RedirectResponse
    {
        $this->authorize('delete', $client);

        $clientName = $client->name;

        ActivityLog::log(auth()->user(), 'deleted', $client, [
            'name' => $clientName,
        ]);

        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', "Client \"{$clientName}\" deleted.");
    }
}