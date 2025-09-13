<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'role' => 'admin'
        ]);

        User::factory()->create([
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'role' => 'user'
        ]);

        User::factory(5)->create();
    }
}
