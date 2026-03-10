<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'description', 'priority', 'completed', 'todo_list_id'];

    protected $casts = [
        'completed' => 'boolean',
    ];
    public function todoList()
    {
        return $this->belongsTo(TodoList::class, 'todo_list_id');
    }
}
