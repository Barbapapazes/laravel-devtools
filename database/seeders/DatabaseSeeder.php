<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        if (app()->environment('local')) {
            $project = Project::factory()->create([
                'name' => 'Build the playground',
                'description' => 'Try the Laravel and Nuxt developer tools together.',
            ]);

            $project->todos()->createMany([
                ['title' => 'Explore the Project and Todo models', 'is_completed' => true],
                ['title' => 'Inspect the API routes in Nuxt DevTools'],
                ['title' => 'Add a task and mark it complete'],
            ]);
        }
    }
}
