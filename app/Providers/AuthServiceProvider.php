<?php

namespace App\Providers;

use App\Models\Tag;
use App\Models\Task;
use App\Policies\TagPolicy;
use App\Policies\TaskPolicy;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }

    // Adding the policies for the task
    protected $policies = [
        Task::class => TaskPolicy::class,
        Tag::Class => TagPolicy::class,
    ];
}
