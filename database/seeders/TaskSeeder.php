<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first user for testing (assuming users exist)
        $user = User::first();
        
        if (!$user) {
            $this->command->error('No users found. Please run UserSeeder first.');
            return;
        }

        $tasks = [
            [
                'user_id' => $user->id,
                'title' => 'Morning Workout',
                'description' => 'Start the day with 30 minutes of exercise',
                'start_time' => '07:00',
                'end_time' => '07:30',
                'status' => Task::STATUS_DONE,
                'color_index' => 1, // Green
            ],
            [
                'user_id' => $user->id,
                'title' => 'Team Meeting',
                'description' => 'Weekly standup with development team',
                'start_time' => '09:00',
                'end_time' => '10:00',
                'status' => Task::STATUS_PROGRESS,
                'color_index' => 0, // Blue
            ],
            [
                'user_id' => $user->id,
                'title' => 'Code Review',
                'description' => 'Review pull requests from team members',
                'start_time' => '10:30',
                'end_time' => '11:30',
                'status' => Task::STATUS_TODO,
                'color_index' => 2, // Orange
            ],
            [
                'user_id' => $user->id,
                'title' => 'Lunch Break',
                'description' => null,
                'start_time' => '12:00',
                'end_time' => '13:00',
                'status' => Task::STATUS_TODO,
                'color_index' => 4, // Red
            ],
            [
                'user_id' => $user->id,
                'title' => 'Project Development',
                'description' => 'Continue working on the mobile app features',
                'start_time' => '14:00',
                'end_time' => '17:00',
                'status' => Task::STATUS_PROGRESS,
                'color_index' => 3, // Purple
            ],
            [
                'user_id' => $user->id,
                'title' => 'Documentation',
                'description' => 'Update API documentation and user guides',
                'start_time' => '17:30',
                'end_time' => '18:30',
                'status' => Task::STATUS_TODO,
                'color_index' => 1, // Green
            ],
        ];

        foreach ($tasks as $task) {
            Task::create($task);
        }

        $this->command->info('Sample tasks created successfully!');
    }
}
