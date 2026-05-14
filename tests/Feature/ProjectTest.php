<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $member;
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
        $this->member = User::factory()->create();
        $this->client = Client::factory()->create(['created_by' => $this->admin->id]);
    }

    public function test_user_can_create_project(): void
    {
        $response = $this->actingAs($this->member)->post('/projects', [
            'name' => 'Portal Redesign',
            'client_id' => $this->client->id,
            'description' => 'Redesign the client portal',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('projects', [
            'name' => 'Portal Redesign',
            'client_id' => $this->client->id,
            'status' => 'draft',
            'created_by' => $this->member->id,
        ]);
    }

    public function test_project_requires_valid_client(): void
    {
        $response = $this->actingAs($this->member)->post('/projects', [
            'name' => 'Test Project',
            'client_id' => 99999,
        ]);

        $response->assertSessionHasErrors('client_id');
    }

    public function test_project_status_transitions(): void
    {
        $project = Project::factory()->create([
            'created_by' => $this->member->id,
            'client_id' => $this->client->id,
            'status' => 'draft',
        ]);

        // Draft -> Active
        $response = $this->actingAs($this->member)
            ->patch("/projects/{$project->id}/status", ['status' => 'active']);
        $response->assertRedirect();
        $this->assertDatabaseHas('projects', ['id' => $project->id, 'status' => 'active']);

        // Active -> Completed
        $response = $this->actingAs($this->member)
            ->patch("/projects/{$project->id}/status", ['status' => 'completed']);
        $response->assertRedirect();
        $this->assertDatabaseHas('projects', ['id' => $project->id, 'status' => 'completed']);
    }

    public function test_status_change_logs_activity(): void
    {
        $project = Project::factory()->create([
            'created_by' => $this->member->id,
            'client_id' => $this->client->id,
            'status' => 'draft',
        ]);

        $this->actingAs($this->member)
            ->patch("/projects/{$project->id}/status", ['status' => 'active']);

        $this->assertDatabaseHas('activity_log', [
            'user_id' => $this->member->id,
            'action' => 'status_changed',
            'subject_type' => 'project',
            'subject_id' => $project->id,
        ]);
    }

    public function test_non_creator_cannot_delete_project(): void
    {
        $project = Project::factory()->create([
            'created_by' => $this->admin->id,
            'client_id' => $this->client->id,
        ]);

        $response = $this->actingAs($this->member)->delete("/projects/{$project->id}");

        $response->assertForbidden();
    }
}