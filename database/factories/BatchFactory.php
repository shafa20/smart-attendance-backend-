<?php

namespace Database\Factories;

use App\Models\Batch;
use Illuminate\Database\Eloquent\Factories\Factory;

class BatchFactory extends Factory
{
    protected $model = Batch::class;

    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-6 months', '+1 month');
        $endDate = fake()->dateTimeBetween($startDate, '+6 months');
        
        return [
            'name' => fake()->unique()->word() . ' Batch ' . fake()->numberBetween(1, 100),
            'course_name' => fake()->randomElement(['Web Development', 'Mobile App Development', 'Data Science', 'UI/UX Design', 'Digital Marketing']),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => fake()->randomElement(['active', 'upcoming', 'completed']),
            'description' => fake()->paragraph(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
