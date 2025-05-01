<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\ClassSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        $classSession = ClassSession::factory()->create();
        $checkinTime = clone $classSession->start_time;
        
        return [
            'class_session_id' => $classSession->id,
            'student_id' => User::factory()->student(),
            'status' => fake()->randomElement(['present', 'absent', 'late']),
            'check_in_time' => $checkinTime->modify('+' . rand(-10, 20) . ' minutes'),
            'check_out_time' => $checkinTime->modify('+' . rand(90, 120) . ' minutes'),
            'remarks' => fake()->optional(0.3)->sentence(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
