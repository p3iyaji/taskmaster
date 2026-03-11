<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TodoList;
use App\Models\Task;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TodoListController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $todoLists = TodoList::with('user')
            ->select(['id', 'name', 'color', 'user_id'])
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

        /** @var User $user */
        $validated['user_id'] = $request->user()->id;

        $todoList = TodoList::create($validated);

        return redirect()->route('todo-lists.index')->with('success', 'Todo list created successfully');
    }

    public function show(TodoList $todoList)
    {
        $tasks = Task::where('todo_list_id', $todoList->id)->get();
        return Inertia::render('TodoLists/Show', [
            'todoList' => $todoList,
            'tasks' => $tasks,
        ]);
    }

    public function update(Request $request, TodoList $todoList)
    {

        $this->authorize('update', $todoList);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:32'],
        ]);

        $todoList->update($validated);

        return redirect()->route('todo-lists.index')->with('success', 'Todo list updated successfully');
    }

    public function destroy(TodoList $todoList)
    {
        $this->authorize('delete', $todoList);

        try {
            $tasksCount = $todoList->tasks()->count();

            if ($tasksCount > 0) {
                return redirect()->route('todo-lists.index')->with('error', 'Todo list has tasks. Please delete the tasks first.');
            }

            $todoList->delete();
        } catch (\Exception $e) {
            return redirect()->route('todo-lists.index')->with('error', 'Failed to delete todo list.');
        }
        return redirect()->route('todo-lists.index')->with('success', 'Todo list deleted successfully');
    }
}
