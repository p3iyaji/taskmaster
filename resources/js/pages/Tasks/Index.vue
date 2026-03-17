<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import { Search, X, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { Plus, Pencil, Trash2, Loader2 } from 'lucide-vue-next';
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';

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
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogTrigger,
    DialogFooter,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';

import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard() },
    { title: 'Tasks', href: '/tasks' },
];

interface User {
    id: number;
    name: string;
    email: string;
}

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
    assignee_id: number | null;
    assignee: User | null;
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

interface Notification {
    id: number;
    message: string;
    task_title: string;
    task_description: string;
    task_priority: string;
    task_completed: boolean;
    task_assignee_name: string;
    task_assignee_email: string;
    created_at: string;
    read_at: string | null;
}

const props = defineProps<{
    tasks: PaginatedTasks;
    filters: {
        search: string | null;
        priority: 'low' | 'medium' | 'high' | null;
        listId: number | null;
    };
    todoLists: TodoList[];
    assignees: User[];
    notifications: Notification[];
}>();

const displayNotifications = ref(false);

const isCreateDialogOpen = ref(false);
const isEditDialogOpen = ref(false);
const deletingTask = ref<number | null>(null);
const editingTask = ref<Task | null>(null);
const search = ref(props.filters.search || '');
const priority = ref(props.filters.priority || '');
const listId = ref(props.filters.listId || '');

const form = useForm({
    id: 0,
    title: '',
    description: '',
    priority: 'medium' as 'low' | 'medium' | 'high',
    completed: false,
    todo_list_id: 0,
    assignee_id: null as number | null,
});

const resetForm = () => {
    form.reset();
    form.assignee_id = null;
    form.todo_list_id = 0;
};

const handleCreate = () => {
    form.post('/tasks', {
        onSuccess: () => {
            isCreateDialogOpen.value = false;
            resetForm();
        },
    });
};

const handleEdit = (task: Task) => {
    editingTask.value = task;
    isEditDialogOpen.value = true;
    form.id = task.id;
    form.title = task.title;
    form.description = task.description;
    form.priority = task.priority;
    form.completed = task.completed;
    form.todo_list_id = task.todo_list_id;
    form.assignee_id = task.assignee_id;
};

const handleUpdate = () => {
    if (!editingTask.value) return;

    form.put(`/tasks/${form.id}`, {
        onSuccess: () => {
            isEditDialogOpen.value = false;
            editingTask.value = null;
            resetForm();
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

const handleDelete = (taskId: number) => {
    deletingTask.value = taskId;
    form.delete(`/tasks/${taskId}`, {
        onSuccess: () => {
            deletingTask.value = null;
        },
    });
};

const getPriorityClass = (priority: string) => {
    switch (priority) {
        case 'low':
            return 'bg-green-500 text-white';
        case 'medium':
            return 'bg-yellow-500 text-white';
        case 'high':
            return 'bg-red-500 text-white';
        default:
            return 'bg-gray-500 text-white';
    }
};

const markAsRead = (notificationId: string) => {
    router.post('/tasks/mark-as-read', {
        notificationId,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Tasks" />
        <Button
            class="m-2 justify-end"
            variant="outline"
            @click="displayNotifications = !displayNotifications"
        >
            Show Notifications
            <Badge v-if="notifications.length">
                {{ notifications.length }}
            </Badge>
        </Button>
        <div class="space-y-2">
            <div v-if="displayNotifications">
                <Card>
                    <CardHeader>
                        <CardTitle>Notifications</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div
                            v-for="notification in notifications"
                            :key="notification.id"
                            class="flex items-center justify-between rounded-md border p-3"
                        >
                            <table
                                class="w-full"
                                cellpadding="0"
                                cellspacing="0"
                            >
                                <tr class="border-b border-border">
                                    <td
                                        class="border border-l border-border px-4 py-3"
                                    >
                                        {{ notification.task_title }}
                                    </td>

                                    <td
                                        class="border border-l border-border px-4 py-3"
                                    >
                                        {{ notification.task_description }}
                                    </td>

                                    <td
                                        class="border border-l border-border px-4 py-3"
                                    >
                                        {{ notification.task_priority }}
                                    </td>

                                    <td
                                        class="border border-l border-border px-4 py-3"
                                    >
                                        {{
                                            notification.task_completed
                                                ? 'Yes'
                                                : 'No'
                                        }}
                                    </td>

                                    <td
                                        class="border border-l border-border px-4 py-3"
                                    >
                                        {{ notification.task_assignee_name }}
                                    </td>

                                    <td
                                        class="border border-l border-border px-4 py-3"
                                    >
                                        {{ notification.task_assignee_email }}
                                    </td>

                                    <td
                                        class="border border-l border-border px-4 py-3"
                                    >
                                        {{ notification.created_at }}
                                    </td>

                                    <td
                                        class="border border-l border-border px-4 py-3"
                                    >
                                        {{ notification.read_at }}
                                    </td>

                                    <td
                                        class="border border-l border-border px-4 py-3"
                                    >
                                        <Button
                                            size="sm"
                                            variant="default"
                                            :class="
                                                notification.read_at
                                                    ? 'hidden'
                                                    : ''
                                            "
                                            @click="markAsRead(notification.id)"
                                            >Unread</Button
                                        >
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <!-- Filters Card -->
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
                                class="w-full rounded-md border border-input bg-background px-3 py-2"
                            >
                                <option value="">All Lists</option>
                                <option
                                    v-for="todoList in todoLists"
                                    :key="todoList.id"
                                    :value="todoList.id"
                                >
                                    {{ todoList.name }}
                                </option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <Label for="priority">Priority</Label>
                            <select
                                id="priority"
                                v-model="priority"
                                class="w-full rounded-md border border-input bg-background px-3 py-2"
                            >
                                <option value="">All Priorities</option>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Header with Create Button -->
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Tasks</h1>
                <Dialog v-model:open="isCreateDialogOpen">
                    <DialogTrigger as-child>
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Create Task
                        </Button>
                    </DialogTrigger>
                    <DialogContent class="sm:max-w-2xl">
                        <DialogHeader>
                            <DialogTitle>Create Task</DialogTitle>
                            <DialogDescription>
                                Create a new task to add to your todo list
                            </DialogDescription>
                        </DialogHeader>
                        <form @submit.prevent="handleCreate">
                            <div class="grid gap-4 py-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <Label for="todo_list_id"
                                            >Todo List</Label
                                        >
                                        <select
                                            id="todo_list_id"
                                            v-model="form.todo_list_id"
                                            class="w-full rounded-md border border-input bg-background px-3 py-2"
                                            required
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
                                        <Label for="assignee_id"
                                            >Assignee</Label
                                        >
                                        <select
                                            id="assignee_id"
                                            v-model="form.assignee_id"
                                            class="w-full rounded-md border border-input bg-background px-3 py-2"
                                        >
                                            <option :value="null">
                                                Unassigned
                                            </option>
                                            <option
                                                v-for="user in assignees"
                                                :key="user.id"
                                                :value="user.id"
                                            >
                                                {{ user.name }}
                                            </option>
                                        </select>
                                        <InputError
                                            :message="form.errors.assignee_id"
                                        />
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <Label for="priority">Priority</Label>
                                        <select
                                            id="priority"
                                            v-model="form.priority"
                                            class="w-full rounded-md border border-input bg-background px-3 py-2"
                                        >
                                            <option value="low">Low</option>
                                            <option value="medium">
                                                Medium
                                            </option>
                                            <option value="high">High</option>
                                        </select>
                                        <InputError
                                            :message="form.errors.priority"
                                        />
                                    </div>
                                    <div class="space-y-2">
                                        <Label for="completed">Completed</Label>
                                        <div class="flex h-10 items-center">
                                            <Checkbox
                                                id="completed"
                                                v-model="form.completed"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <Label for="title">Task Title</Label>
                                    <Input
                                        id="title"
                                        v-model="form.title"
                                        placeholder="Enter task title"
                                        required
                                    />
                                    <InputError :message="form.errors.title" />
                                </div>
                                <div class="space-y-2">
                                    <Label for="description">Description</Label>
                                    <Textarea
                                        id="description"
                                        v-model="form.description"
                                        placeholder="Enter task description"
                                        rows="3"
                                    />
                                    <InputError
                                        :message="form.errors.description"
                                    />
                                </div>
                            </div>
                            <DialogFooter>
                                <Button
                                    type="submit"
                                    :disabled="form.processing"
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
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>

            <!-- Tasks Table -->
            <div class="overflow-x-auto rounded-md border">
                <table class="w-full">
                    <thead class="bg-muted/50">
                        <tr class="text-left">
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Title</th>
                            <th class="px-4 py-3">Description</th>
                            <th class="px-4 py-3">Priority</th>
                            <th class="px-4 py-3">List</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Assignee</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(task, index) in tasks.data"
                            :key="task.id"
                            class="border-b border-border hover:bg-muted/50"
                        >
                            <td class="px-4 py-3">{{ index + 1 }}</td>
                            <td class="px-4 py-3 font-medium">
                                {{ task.title }}
                            </td>
                            <td class="max-w-xs truncate px-4 py-3">
                                {{ task.description }}
                            </td>
                            <td class="px-4 py-3">
                                <Badge :class="getPriorityClass(task.priority)">
                                    {{
                                        task.priority.charAt(0).toUpperCase() +
                                        task.priority.slice(1)
                                    }}
                                </Badge>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="h-3 w-3 rounded-full"
                                        :style="{
                                            backgroundColor:
                                                task.todo_list.color ||
                                                '#6366f1',
                                        }"
                                    />
                                    {{ task.todo_list.name }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <Badge
                                    :variant="
                                        task.completed ? 'default' : 'outline'
                                    "
                                >
                                    {{
                                        task.completed ? 'Completed' : 'Pending'
                                    }}
                                </Badge>
                            </td>
                            <td class="px-4 py-3">
                                {{ task.assignee?.name || 'Unassigned' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <!-- Edit Dialog -->
                                    <Dialog v-model:open="isEditDialogOpen">
                                        <DialogTrigger as-child>
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                @click="handleEdit(task)"
                                            >
                                                <Pencil class="h-4 w-4" />
                                            </Button>
                                        </DialogTrigger>
                                        <DialogContent class="sm:max-w-2xl">
                                            <DialogHeader>
                                                <DialogTitle
                                                    >Edit Task</DialogTitle
                                                >
                                                <DialogDescription>
                                                    Edit the task details
                                                </DialogDescription>
                                            </DialogHeader>
                                            <form
                                                @submit.prevent="handleUpdate"
                                            >
                                                <div class="grid gap-4 py-4">
                                                    <div
                                                        class="grid grid-cols-2 gap-4"
                                                    >
                                                        <div class="space-y-2">
                                                            <Label
                                                                for="edit_todo_list_id"
                                                                >Todo
                                                                List</Label
                                                            >
                                                            <select
                                                                id="edit_todo_list_id"
                                                                v-model="
                                                                    form.todo_list_id
                                                                "
                                                                class="w-full rounded-md border border-input bg-background px-3 py-2"
                                                                required
                                                            >
                                                                <option
                                                                    value=""
                                                                >
                                                                    Select Todo
                                                                    List
                                                                </option>
                                                                <option
                                                                    v-for="todoList in todoLists"
                                                                    :key="
                                                                        todoList.id
                                                                    "
                                                                    :value="
                                                                        todoList.id
                                                                    "
                                                                >
                                                                    {{
                                                                        todoList.name
                                                                    }}
                                                                </option>
                                                            </select>
                                                            <InputError
                                                                :message="
                                                                    form.errors
                                                                        .todo_list_id
                                                                "
                                                            />
                                                        </div>
                                                        <div class="space-y-2">
                                                            <Label
                                                                for="edit_assignee_id"
                                                                >Assignee</Label
                                                            >
                                                            <select
                                                                id="edit_assignee_id"
                                                                v-model="
                                                                    form.assignee_id
                                                                "
                                                                class="w-full rounded-md border border-input bg-background px-3 py-2"
                                                            >
                                                                <option
                                                                    :value="
                                                                        null
                                                                    "
                                                                >
                                                                    Unassigned
                                                                </option>
                                                                <option
                                                                    v-for="user in assignees"
                                                                    :key="
                                                                        user.id
                                                                    "
                                                                    :value="
                                                                        user.id
                                                                    "
                                                                >
                                                                    {{
                                                                        user.name
                                                                    }}
                                                                </option>
                                                            </select>
                                                            <InputError
                                                                :message="
                                                                    form.errors
                                                                        .assignee_id
                                                                "
                                                            />
                                                        </div>
                                                    </div>

                                                    <div
                                                        class="grid grid-cols-2 gap-4"
                                                    >
                                                        <div class="space-y-2">
                                                            <Label
                                                                for="edit_priority"
                                                                >Priority</Label
                                                            >
                                                            <select
                                                                id="edit_priority"
                                                                v-model="
                                                                    form.priority
                                                                "
                                                                class="w-full rounded-md border border-input bg-background px-3 py-2"
                                                            >
                                                                <option
                                                                    value="low"
                                                                >
                                                                    Low
                                                                </option>
                                                                <option
                                                                    value="medium"
                                                                >
                                                                    Medium
                                                                </option>
                                                                <option
                                                                    value="high"
                                                                >
                                                                    High
                                                                </option>
                                                            </select>
                                                            <InputError
                                                                :message="
                                                                    form.errors
                                                                        .priority
                                                                "
                                                            />
                                                        </div>
                                                        <div class="space-y-2">
                                                            <Label
                                                                for="edit_completed"
                                                                >Completed</Label
                                                            >
                                                            <div
                                                                class="flex h-10 items-center"
                                                            >
                                                                <Checkbox
                                                                    id="edit_completed"
                                                                    v-model="
                                                                        form.completed
                                                                    "
                                                                />
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="space-y-2">
                                                        <Label for="edit_title"
                                                            >Task Title</Label
                                                        >
                                                        <Input
                                                            id="edit_title"
                                                            v-model="form.title"
                                                            placeholder="Enter task title"
                                                            required
                                                        />
                                                        <InputError
                                                            :message="
                                                                form.errors
                                                                    .title
                                                            "
                                                        />
                                                    </div>
                                                    <div class="space-y-2">
                                                        <Label
                                                            for="edit_description"
                                                            >Description</Label
                                                        >
                                                        <Textarea
                                                            id="edit_description"
                                                            v-model="
                                                                form.description
                                                            "
                                                            placeholder="Enter task description"
                                                            rows="3"
                                                        />
                                                        <InputError
                                                            :message="
                                                                form.errors
                                                                    .description
                                                            "
                                                        />
                                                    </div>
                                                </div>
                                                <DialogFooter>
                                                    <Button
                                                        type="submit"
                                                        :disabled="
                                                            form.processing
                                                        "
                                                    >
                                                        <Loader2
                                                            v-if="
                                                                form.processing
                                                            "
                                                            class="mr-2 h-4 w-4 animate-spin"
                                                        />
                                                        {{
                                                            form.processing
                                                                ? 'Updating...'
                                                                : 'Update Task'
                                                        }}
                                                    </Button>
                                                </DialogFooter>
                                            </form>
                                        </DialogContent>
                                    </Dialog>

                                    <!-- Delete Alert Dialog -->
                                    <AlertDialog>
                                        <AlertDialogTrigger as-child>
                                            <Button
                                                variant="destructive"
                                                size="sm"
                                            >
                                                <Trash2 class="h-4 w-4" />
                                            </Button>
                                        </AlertDialogTrigger>
                                        <AlertDialogContent>
                                            <AlertDialogHeader>
                                                <AlertDialogTitle
                                                    >Are you
                                                    sure?</AlertDialogTitle
                                                >
                                                <AlertDialogDescription>
                                                    This action cannot be
                                                    undone. This will
                                                    permanently delete "{{
                                                        task.title
                                                    }}".
                                                </AlertDialogDescription>
                                            </AlertDialogHeader>
                                            <AlertDialogFooter>
                                                <AlertDialogCancel
                                                    >Cancel</AlertDialogCancel
                                                >
                                                <AlertDialogAction
                                                    @click="
                                                        handleDelete(task.id)
                                                    "
                                                    :disabled="
                                                        deletingTask === task.id
                                                    "
                                                >
                                                    <Loader2
                                                        v-if="
                                                            deletingTask ===
                                                            task.id
                                                        "
                                                        class="mr-2 h-4 w-4 animate-spin"
                                                    />
                                                    {{
                                                        deletingTask === task.id
                                                            ? 'Deleting...'
                                                            : 'Delete'
                                                    }}
                                                </AlertDialogAction>
                                            </AlertDialogFooter>
                                        </AlertDialogContent>
                                    </AlertDialog>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="tasks.data.length === 0">
                            <td
                                colspan="8"
                                class="px-4 py-8 text-center text-muted-foreground"
                            >
                                No tasks found. Create your first task to get
                                started!
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4 flex items-center justify-between">
                <p class="text-sm text-muted-foreground">
                    Showing {{ tasks.data.length }} of {{ tasks.total }} tasks
                </p>
                <div class="flex items-center gap-2">
                    <template v-for="(link, index) in tasks.links" :key="index">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="rounded-md px-3 py-1 text-sm font-medium"
                            :class="[
                                link.active
                                    ? 'bg-primary text-primary-foreground'
                                    : 'bg-transparent text-muted-foreground hover:bg-accent',
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
    </AppLayout>
</template>
