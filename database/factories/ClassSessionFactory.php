<?php

namespace Database\Factories;

use App\Models\ClassSession;
use App\Models\Batch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassSessionFactory extends Factory
{
    protected $model = ClassSession::class;

    public function definition(): array
    {
        $startTime = fake()->dateTimeBetween('-3 months', '+1 month');
        $endTime = (clone $startTime)->modify('+2 hours');
        
        return [
            'batch_id' => Batch::factory(),
            'instructor_id' => User::factory()->instructor(),
            'topic' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'status' => fake()->randomElement(['scheduled', 'ongoing', 'completed', 'cancelled']),
            'room_number' => fake()->numberBetween(100, 999),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
