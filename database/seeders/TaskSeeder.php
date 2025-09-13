<?php

namespace Database\Seeders;

use App\Models\Tag;
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
        $users = User::all();
        $tags = Tag::all();

        if ($users->isEmpty() || $tags->isEmpty()) {
            return;
        }

        Task::factory(20)->create()->each(function (Task $task) use ($users, $tags) {
            // Assign the user to the task
            $task->assigned_to = $users->random()->id;

            $task->save();

            //Adding tags to the Task
            $selectedTags = $tags->random(rand(1, 3));
            $task->tags()->attach($selectedTags);
        });
    }
}
