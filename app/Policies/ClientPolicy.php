<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    /**
     * Any authenticated user can view the client list.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Any authenticated user can view a single client.
     */
    public function view(User $user, Client $client): bool
    {
        return true;
    }

    /**
     * Any authenticated user can create clients.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Only the creator or an admin can update a client.
     */
    public function update(User $user, Client $client): bool
    {
        return $user->isAdmin() || $user->id === $client->created_by;
    }

    /**
     * Only the creator or an admin can delete a client.
     */
    public function delete(User $user, Client $client): bool
    {
        return $user->isAdmin() || $user->id === $client->created_by;
    }
}