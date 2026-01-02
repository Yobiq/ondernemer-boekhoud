<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Document;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'document_id' => null,
            'type' => fake()->randomElement(['missing_document', 'unreadable', 'clarification']),
            'description' => fake()->sentence(),
            'status' => fake()->randomElement(['open', 'resolved']),
        ];
    }

    /**
     * Indicate that the task is open.
     */
    public function open(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'open',
        ]);
    }

    /**
     * Indicate that the task is resolved.
     */
    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'resolved',
        ]);
    }
}

