<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = ['pending', 'in_progress', 'completed'];
        $priority = ['low', 'medium', 'high'];
        return [
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement($status),
            'priority' => $this->faker->randomElement($priority),
            'due_date' => $this->faker->optional()->dateTimeBetween('+1 week', '+1 month'),
            'version' => 1,
            'metadata' => ['note' => 'This is a sample note'],
        ];
    }
}
