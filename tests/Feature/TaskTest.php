<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $client = Client::factory()->create(['created_by' => $this->user->id]);
        $this->project = Project::factory()->create([
            'created_by' => $this->user->id,
            'client_id' => $client->id,
        ]);
    }

    public function test_user_can_create_task(): void
    {
        $response = $this->actingAs($this->user)
            ->post("/projects/{$this->project->id}/tasks", [
                'title' => 'Fix the navbar',
                'status' => 'todo',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tasks', [
            'project_id' => $this->project->id,
            'title' => 'Fix the navbar',
            'status' => 'todo',
        ]);
    }

    public function test_task_sort_order_increments(): void
    {
        // Create two tasks in the same column
        $this->actingAs($this->user)
            ->post("/projects/{$this->project->id}/tasks", [
                'title' => 'First task',
                'status' => 'todo',
            ]);

        $this->actingAs($this->user)
            ->post("/projects/{$this->project->id}/tasks", [
                'title' => 'Second task',
                'status' => 'todo',
            ]);

        $tasks = Task::where('project_id', $this->project->id)
            ->orderBy('sort_order')
            ->get();

        $this->assertEquals(0, $tasks[0]->sort_order);
        $this->assertEquals(1, $tasks[1]->sort_order);
    }

    public function test_task_status_change(): void
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'status' => 'todo',
        ]);

        $response = $this->actingAs($this->user)
            ->patch("/tasks/{$task->id}/status", ['status' => 'in_progress']);

        $response->assertRedirect();
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'in_progress',
        ]);
    }

    public function test_task_status_change_logs_activity(): void
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'status' => 'todo',
        ]);

        $this->actingAs($this->user)
            ->patch("/tasks/{$task->id}/status", ['status' => 'done']);

        $this->assertDatabaseHas('activity_log', [
            'action' => 'status_changed',
            'subject_type' => 'task',
            'subject_id' => $task->id,
        ]);
    }

    public function test_task_requires_title(): void
    {
        $response = $this->actingAs($this->user)
            ->post("/projects/{$this->project->id}/tasks", [
                'title' => '',
            ]);

        $response->assertSessionHasErrors('title');
    }
}