<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            'Content Marketing',
            'Search Engine Optimization',
            'Social Media Marketing',
            'Backend Development',
            'Email Marketing',
            'Data Analytics & Reporting',
            'Frontend Development',
            'UX/UI Design',
            'Account Management',
            'Video Production'
        ];

        foreach ($tags as $tagName) {
            Tag::factory()->create(['name' => $tagName]);
        }
    }
}
