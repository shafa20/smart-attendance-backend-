<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Create Student
        User::create([
            'name' => 'Test Student',
            'email' => 'student1@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'student'
        ]);

        // Create Instructor
        User::create([
            'name' => 'Test Instructor',
            'email' => 'instructor1@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'instructor'
        ]);

        // Create Admin
        User::create([
            'name' => 'Test Admin',
            'email' => 'admin1@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'admin'
        ]);
    }
}
