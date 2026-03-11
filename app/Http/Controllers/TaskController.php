<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Task;
use App\Models\TodoList;
use Inertia\Inertia;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::with('todoList', 'assignee')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('assignee', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Change this to match your frontend parameter name
        if ($request->filled('listId')) {
            $query->where('todo_list_id', $request->listId);
        }

        $tasks = $query->paginate(10)->withQueryString();
        $assignees = User::select(['id', 'name', 'email'])->get();

        $todoLists = TodoList::select(['id', 'name', 'color'])->get();
        $users = User::select(['id', 'name', 'email'])->get();

        return Inertia::render('Tasks/Index', [
            'tasks' => $tasks,
            'filters' => $request->only('search', 'priority', 'listId'), // Change this too
            'todoLists' => $todoLists,
            'assignees' => $assignees,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'todo_list_id' => ['required', 'exists:todo_lists,id'],
            'description' => ['nullable', 'string', 'max:255'],
            'priority' => ['nullable', 'string', 'max:16'],
            'completed' => ['nullable', 'boolean'],
            'assignee_id' => ['nullable', 'exists:users,id'],
        ]);

        $validated['completed'] = $request->boolean('completed', false);
        $validated['priority'] = $request->string('priority', 'normal');

        $task = Task::create($validated);

        return redirect()->route('tasks.index')->with('success', 'Task added successfully');
    }

    public function show(Task $task)
    {

        return Inertia::render('Tasks/Show', [
            'task' => $task,
        ]);
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'todo_list_id' => ['required', 'exists:todo_lists,id'],
            'description' => ['nullable', 'string', 'max:255'],
            'priority' => ['nullable', 'string', 'max:16'],
            'completed' => ['nullable', 'boolean'],
            'assignee_id' => ['nullable', 'exists:users,id'],
        ]);

        $task->update($validated);

        return redirect()->route('tasks.index')->with('success', 'Task updated successfully');
    }

    public function destroy(Task $task)
    {
        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully');
    }
}
