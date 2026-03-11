<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\TodoList;
use App\Policies\TodoListPolicy;

class AuthServiceProvider extends ServiceProvider
{

    protected $policies = [
        TodoList::class => TodoListPolicy::class,
    ];
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
}
