<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import InputError from '@/components/InputError.vue';
import { Search, List, Check, X } from 'lucide-vue-next';

import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { Plus, Pencil, Trash2, ExternalLink, Loader2 } from 'lucide-vue-next';

import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogTrigger,
} from '@/components/ui/dialog';
import {
    AlertDialog,
    AlertDialogContent,
    AlertDialogHeader,
    AlertDialogTrigger,
    AlertDialogTitle,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogCancel,
    AlertDialogAction,
} from '@/components/ui/alert-dialog';
import { Checkbox } from '@/components/ui/checkbox';

import type { BreadcrumbItem } from '@/types';
import { dashboard } from '@/routes';
import { watchDebounced } from '@vueuse/core';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard() },
    { title: 'Tasks', href: '/tasks' },
];

interface Task {
    id: number;
    title: string;
    description: string;
    priority: 'low' | 'medium' | 'high';
    completed: boolean;
    created_at: string;
    todo_list: {
        id: number;
        name: string;
        color?: string;
    };
    todo_list_id: number;
}

interface TodoList {
    id: number;
    name: string;
    color?: string;
}

interface PaginationLinks {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedTasks {
    data: Task[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: PaginationLinks[];
}

const props = defineProps<{
    tasks: PaginatedTasks;
    filters: {
        search: string | null;
        priority: 'low' | 'medium' | 'high' | null;
        listId: number | null;
    };
    todoLists: TodoList[];
}>();

const isCreateDialogOpen = ref(false);
const isEditDialogOpen = ref(false);
const isDeleteDialogOpen = ref(false);
const search = ref(props.filters.search || '');
const priority = ref(props.filters.priority || '');
const listId = ref(props.filters.listId || '');

const form = useForm<Task>({
    id: 0,
    title: '',
    description: '',
    priority: 'medium',
    completed: false,
    todo_list_id: 0,
    created_at: new Date().toISOString(),
    todo_list: {
        id: 0,
        name: '',
        color: '#6366f1',
    },
});

const handleCreate = () => {
    form.post('/tasks', {
        onSuccess: () => {
            isCreateDialogOpen.value = false;
            form.reset();
        },
    });
};

const handleEdit = (task: Task) => {
    isEditDialogOpen.value = true;
    form.id = task.id;
    form.title = task.title;
    form.description = task.description;
    form.priority = task.priority;
    form.completed = task.completed;
    form.todo_list_id = task.todo_list_id;
    form.todo_list = task.todo_list;
};

const handleUpdate = () => {
    form.put(`/tasks/${form.id}`, {
        onSuccess: () => {
            isEditDialogOpen.value = false;
            form.reset();
        },
    });
};

watchDebounced(
    [search, priority, listId],
    () => {
        router.get(
            '/tasks',
            {
                search: search.value || undefined,
                priority: priority.value || undefined,
                listId: listId.value || undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    },
    { debounce: 300 },
);

const clearFilters = () => {
    search.value = '';
    priority.value = '';
    listId.value = '';
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Tasks" />

        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <CardTitle>Filters</CardTitle>
                        <Button variant="ghost" size="sm" @click="clearFilters">
                            <X class="mr-2 h-4 w-4" />
                            Clear Filters
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="space-y-2">
                            <Label for="search">Search</Label>
                            <div class="relative">
                                <Search
                                    class="absolute top-2.5 left-2 h-4 w-4 text-muted-foreground"
                                />
                                <Input
                                    id="search"
                                    v-model="search"
                                    placeholder="Search tasks..."
                                    class="pl-8"
                                />
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label for="list">List</Label>
                            <select
                                id="listId"
                                v-model="listId"
                                class="w-full rounded-md border border-gray-300 p-2"
                            >
                                <option value="">Select List</option>
                                <option
                                    v-for="todoList in todoLists"
                                    :key="todoList.id"
                                    :value="todoList.id"
                                >
                                    {{ todoList.name }}
                                </option>
                            </select>
                            <InputError :message="form.errors.todo_list_id" />
                        </div>
                        <div class="space-y-2">
                            <Label for="priority">Priority</Label>
                            <select
                                id="priority"
                                v-model="priority"
                                class="w-full rounded-md border border-gray-300 p-2"
                            >
                                <option value="">Select Priority</option>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                            <InputError :message="form.errors.priority" />
                        </div>
                    </div>
                </CardContent>
            </Card>
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Tasks</h1>
                <Dialog v-model:open="isCreateDialogOpen">
                    <DialogTrigger as-child>
                        <Button
                            ><Plus class="mr-2 h-4 w-4 text-white" /> Create
                            Task</Button
                        >
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Create Task</DialogTitle>
                            <DialogDescription>
                                Create a new task to add to your todo list
                            </DialogDescription>
                        </DialogHeader>
                        <form @submit.prevent="handleCreate()">
                            <div class="space-y-4 py-4">
                                <div class="flex-1 space-y-2">
                                    <Label for="todo_list_id">Todo List</Label>
                                    <select
                                        id="todo_list_id"
                                        v-model="form.todo_list_id"
                                        class="w-full rounded-md border border-gray-300 p-2"
                                    >
                                        <option value="">
                                            Select Todo List
                                        </option>
                                        <option
                                            v-for="todoList in todoLists"
                                            :key="todoList.id"
                                            :value="todoList.id"
                                        >
                                            {{ todoList.name }}
                                        </option>
                                    </select>
                                    <InputError
                                        :message="form.errors.todo_list_id"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <Label for="priority">Priority</Label>
                                    <select
                                        id="priority"
                                        v-model="form.priority"
                                        class="w-full rounded-md border border-gray-300 p-2"
                                    >
                                        <option value="">
                                            Select Priority
                                        </option>
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                    </select>
                                    <InputError
                                        :message="form.errors.priority"
                                    />
                                </div>

                                <div class="space-y-2">
                                    <Label for="title">Task Title</Label>
                                    <Input
                                        id="title"
                                        v-model="form.title"
                                        placeholder="Enter task title"
                                    />
                                    <InputError :message="form.errors.title" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="description">Description</Label>
                                    <Textarea
                                        id="description"
                                        v-model="form.description"
                                        placeholder="Enter task description"
                                    />
                                    <InputError
                                        :message="form.errors.description"
                                    />
                                </div>
                                <Button
                                    type="submit"
                                    :disabled="form.processing"
                                    variant="default"
                                >
                                    <Loader2
                                        v-if="form.processing"
                                        class="mr-2 h-4 w-4 animate-spin"
                                    />
                                    {{
                                        form.processing
                                            ? 'Creating...'
                                            : 'Create Task'
                                    }}
                                </Button>
                            </div>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>
            <div class="table-container">
                <table class="w-full">
                    <thead class="bg-gray-100">
                        <tr class="text-left">
                            <th class="px-4 py-2">#</th>
                            <th class="px-4 py-2">Title</th>
                            <th class="px-4 py-2">Description</th>
                            <th class="px-4 py-2">Priority</th>
                            <th class="px-4 py-2">List</th>
                            <th class="px-4 py-2">Completed</th>

                            <th class="px-4 py-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(task, index) in tasks.data"
                            :key="task.id"
                            class="border-b border-gray-200"
                        >
                            <td class="px-4 py-2">{{ index + 1 }}</td>
                            <td class="px-4 py-2">{{ task.title }}</td>
                            <td class="px-4 py-2">{{ task.description }}</td>
                            <td class="px-4 py-2">
                                <badge
                                    class="inline-flex w-20 items-center justify-center rounded-md px-2 py-1 text-center"
                                    :class="
                                        task.priority === 'low'
                                            ? 'bg-green-500 text-white'
                                            : task.priority === 'medium'
                                              ? 'bg-yellow-500 text-white'
                                              : 'bg-red-500 text-white'
                                    "
                                >
                                    {{
                                        task.priority.charAt(0).toUpperCase() +
                                        task.priority.slice(1)
                                    }}
                                </badge>
                            </td>
                            <td class="px-4 py-2">
                                {{ task.todo_list.name }}
                            </td>
                            <td class="px-4 py-2">
                                {{ task.completed ? 'Yes' : 'No' }}
                            </td>
                            <td class="px-4 py-2">
                                <Dialog v-model:open="isEditDialogOpen">
                                    <DialogTrigger as-child>
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            class="mr-2"
                                            @click="handleEdit(task)"
                                        >
                                            <Pencil class="h-4 w-4" /> Edit
                                        </Button>
                                    </DialogTrigger>
                                    <DialogContent>
                                        <DialogHeader>
                                            <DialogTitle>Edit Task</DialogTitle>
                                            <DialogDescription>
                                                Edit the task details
                                            </DialogDescription>
                                        </DialogHeader>
                                        <form
                                            @submit.prevent="handleUpdate(task)"
                                        >
                                            <div class="space-y-4 py-4">
                                                <div class="space-y-2">
                                                    <Label for="title"
                                                        >Task Title</Label
                                                    >
                                                    <Input
                                                        id="title"
                                                        v-model="form.title"
                                                        placeholder="Enter task title"
                                                    />
                                                    <InputError
                                                        :message="
                                                            form.errors.title
                                                        "
                                                    />
                                                </div>
                                                <div class="space-y-2">
                                                    <Label for="description"
                                                        >Description</Label
                                                    >
                                                    <Textarea
                                                        id="description"
                                                        v-model="
                                                            form.description
                                                        "
                                                        placeholder="Enter task description"
                                                    />
                                                    <InputError
                                                        :message="
                                                            form.errors
                                                                .description
                                                        "
                                                    />
                                                </div>
                                                <div class="space-y-2">
                                                    <Label for="priority"
                                                        >Priority</Label
                                                    >
                                                    <select
                                                        id="priority"
                                                        v-model="form.priority"
                                                        class="w-full rounded-md border border-gray-300 p-2"
                                                    >
                                                        <option value="">
                                                            Select Priority
                                                        </option>
                                                        <option value="low">
                                                            Low
                                                        </option>
                                                        <option value="medium">
                                                            Medium
                                                        </option>
                                                        <option value="high">
                                                            High
                                                        </option>
                                                    </select>
                                                    <InputError
                                                        :message="
                                                            form.errors.priority
                                                        "
                                                    />
                                                </div>
                                                <div class="space-y-2">
                                                    <Label for="completed"
                                                        >Completed</Label
                                                    >
                                                    <Checkbox
                                                        id="completed"
                                                        v-model="form.completed"
                                                    />
                                                    <InputError
                                                        :message="
                                                            form.errors
                                                                .completed
                                                        "
                                                    />
                                                </div>
                                                <Button
                                                    type="submit"
                                                    :disabled="form.processing"
                                                    variant="default"
                                                >
                                                    <Loader2
                                                        v-if="form.processing"
                                                        class="mr-2 h-4 w-4 animate-spin"
                                                    />
                                                    {{
                                                        form.processing
                                                            ? 'Updating...'
                                                            : 'Update Task'
                                                    }}
                                                </Button>
                                            </div>
                                        </form>
                                    </DialogContent>
                                </Dialog>

                                <Button variant="outline" size="sm">
                                    <Trash2 class="h-4 w-4" /> Delete
                                </Button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <!-- pagination links -->
                <div class="mt-4 flex items-center justify-between">
                    <p class="text-sm text-muted-foreground">
                        Showing {{ tasks.data.length }} of
                        {{ tasks.total }} tasks
                    </p>
                    <div class="flex items-center gap-2">
                        <template
                            v-for="(link, index) in tasks.links"
                            :key="index"
                        >
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="rounded-md px-3 py-1 text-sm font-medium"
                                :class="[
                                    link.active
                                        ? 'bg-primary text-primary-foreground'
                                        : 'bg-transparent text-muted-foreground hover:bg-accent hover:text-accent-foreground',
                                ]"
                                preserve-scroll
                            >
                                <span v-if="link.label.includes('Previous')">
                                    <ChevronLeft class="h-4 w-4" />
                                </span>
                                <span v-else-if="link.label.includes('Next')">
                                    <ChevronRight class="h-4 w-4" />
                                </span>
                                <span v-else v-html="link.label" />
                            </Link>
                            <span
                                v-else
                                class="rounded-md px-3 py-1 text-sm font-medium text-muted-foreground opacity-50"
                            >
                                <span v-if="link.label.includes('Previous')">
                                    <ChevronLeft class="h-4 w-4" />
                                </span>
                                <span v-else-if="link.label.includes('Next')">
                                    <ChevronRight class="h-4 w-4" />
                                </span>
                                <span v-else v-html="link.label" />
                            </span>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
