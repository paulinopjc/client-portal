<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $member;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        $this->member = User::factory()->create();
    }

    public function test_authenticated_user_can_view_client_list(): void
    {
        Client::factory()->count(3)->create(['created_by' => $this->admin->id]);

        $response = $this->actingAs($this->member)->get('/clients');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Clients/Index')
                ->has('clients.data', 3)
        );
    }

    public function test_user_can_create_client(): void
    {
        $response = $this->actingAs($this->member)->post('/clients', [
            'name' => 'Acme Corp',
            'company' => 'Acme',
            'email' => 'contact@acme.com',
            'phone' => '555-1234',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('clients', [
            'name' => 'Acme Corp',
            'created_by' => $this->member->id,
        ]);
    }

    public function test_client_creation_requires_name(): void
    {
        $response = $this->actingAs($this->member)->post('/clients', [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_creator_can_update_their_client(): void
    {
        $client = Client::factory()->create(['created_by' => $this->member->id]);

        $response = $this->actingAs($this->member)->put("/clients/{$client->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_non_creator_cannot_update_client(): void
    {
        $otherUser = User::factory()->create();
        $client = Client::factory()->create(['created_by' => $otherUser->id]);

        $response = $this->actingAs($this->member)->put("/clients/{$client->id}", [
            'name' => 'Hacked Name',
        ]);

        $response->assertForbidden();
    }

    public function test_admin_can_update_any_client(): void
    {
        $client = Client::factory()->create(['created_by' => $this->member->id]);

        $response = $this->actingAs($this->admin)->put("/clients/{$client->id}", [
            'name' => 'Admin Updated',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'name' => 'Admin Updated',
        ]);
    }

    public function test_non_creator_cannot_delete_client(): void
    {
        $otherUser = User::factory()->create();
        $client = Client::factory()->create(['created_by' => $otherUser->id]);

        $response = $this->actingAs($this->member)->delete("/clients/{$client->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('clients', ['id' => $client->id]);
    }

    public function test_creator_can_delete_their_client(): void
    {
        $client = Client::factory()->create(['created_by' => $this->member->id]);

        $response = $this->actingAs($this->member)->delete("/clients/{$client->id}");

        $response->assertRedirect('/clients');
        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }

    public function test_activity_is_logged_on_client_create(): void
    {
        $this->actingAs($this->member)->post('/clients', [
            'name' => 'Test Client',
        ]);

        $this->assertDatabaseHas('activity_log', [
            'user_id' => $this->member->id,
            'action' => 'created',
            'subject_type' => 'client',
        ]);
    }
}