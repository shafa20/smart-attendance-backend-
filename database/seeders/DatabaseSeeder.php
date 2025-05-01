<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Batch;
use App\Models\ClassSession;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@gmail.com', 
            'password' => bcrypt('12345678'), 
        ]);

        // Create instructors with sequential emails for easy testing
        $instructors = collect();
        for ($i = 1; $i <= 20; $i++) {
            $instructors->push(
                User::factory()->instructor()->create([
                    'email' => "instructor{$i}@gmail.com",
                ])
            );
        }

        // Create students with sequential emails for easy testing
        $students = collect();
        for ($i = 1; $i <= 500; $i++) {
            $students->push(
                User::factory()->student()->create([
                    'email' => "student{$i}@gmail.com",
                ])
            );
        }

        // Create batches with relationships
        $batches = Batch::factory()
            ->count(100)
            ->create()
            ->each(function ($batch) use ($instructors, $students) {
                // Assign 1-2 instructors to each batch
                $batch->instructors()->attach(
                    $instructors->random(rand(1, 2))->pluck('id')->toArray()
                );

                // Assign 15-30 students to each batch
                $batch->students()->attach(
                    $students->random(rand(15, 30))->pluck('id')->toArray()
                );
            });

        // Create class sessions
        $batches->each(function ($batch) {
            // Create 30-50 classes for each batch
            ClassSession::factory()
                ->count(rand(30, 50))
                ->create([
                    'batch_id' => $batch->id,
                    'instructor_id' => $batch->instructors->random()->id,
                ]);
        });

        // Create attendance records in chunks
        ClassSession::with(['batch.students'])->chunk(50, function ($sessions) {
            foreach ($sessions as $session) {
                $attendanceData = [];
                foreach ($session->batch->students as $student) {
                    $checkinTime = clone $session->start_time;
                    $attendanceData[] = [
                        'class_session_id' => $session->id,
                        'student_id' => $student->id,
                        'status' => fake()->randomElement(['present', 'absent', 'late']),
                        'check_in_time' => $checkinTime->modify('+' . rand(-10, 20) . ' minutes'),
                        'check_out_time' => $checkinTime->modify('+' . rand(90, 120) . ' minutes'),
                        'remarks' => fake()->optional(0.3)->sentence(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                DB::table('attendances')->insert($attendanceData);
            }
        });
    }
}
