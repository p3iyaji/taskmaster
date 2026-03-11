<?php

namespace App\Observers;

use App\Models\TodoList;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TodoListObserver
{
    /**
     * Handle the TodoList "created" event.
     */
    public function saved(TodoList $todoList)
    {
        Cache::tags(['todolists'])->flush();
    }

    public function deleted(TodoList $todoList)
    {
        Cache::tags(['todolists'])->flush();
    }
}
