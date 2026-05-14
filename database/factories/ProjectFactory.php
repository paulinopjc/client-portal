<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'client_id' => Client::factory(),
            'description' => fake()->optional()->paragraph(),
            'status' => 'draft',
            'deadline' => fake()->optional()->dateTimeBetween('+1 week', '+3 months'),
            'created_by' => User::factory(),
        ];
    }
}