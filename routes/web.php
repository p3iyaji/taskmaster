<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use App\Http\Controllers\TodoListController;
use App\Http\Controllers\TaskController;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::resource('todo-lists', TodoListController::class);
    Route::resource('tasks', TaskController::class);

    Route::post('/tasks/mark-as-read', [TaskController::class, 'markAsRead']);
});

require __DIR__ . '/settings.php';
