<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TodoList;
use Inertia\Inertia;

class TodoListController extends Controller
{
    public function index()
    {
        $todoLists = TodoList::query()
            ->select(['id', 'name', 'color'])
            ->withCount('tasks')
            ->latest()
            ->get();

        return Inertia::render('TodoLists/Index', [
            'todoLists' => $todoLists,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:32'],
        ]);

        $todoList = TodoList::create($validated);

        return redirect()->route('todo-lists.index')->with('success', 'Todo list created successfully');
    }

    public function show(TodoList $todoList)
    {
        return Inertia::render('TodoLists/Show', [
            'todoList' => $todoList,
        ]);
    }

    public function update(Request $request, TodoList $todoList)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:32'],
        ]);

        $todoList->update($validated);

        return redirect()->route('todo-lists.index')->with('success', 'Todo list updated successfully');
    }

    public function destroy(TodoList $todoList)
    {
        $todoList->delete();

        return redirect()->route('todo-lists.index')->with('success', 'Todo list deleted successfully');
    }
}
