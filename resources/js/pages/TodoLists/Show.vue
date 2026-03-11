<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Pencil, Trash2 } from 'lucide-vue-next';
import type { PropType } from 'vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

interface User {
    id: number;
    name: string;
    email: string;
}

interface TodoList {
    id: number;
    name: string;
    color: string;
    user_id: number;
    assignee: User;
    tasks_count: number;
    created_at: string;
    updated_at: string;
}

interface Task {
    id: number;
    title: string;
    description: string;
    priority: string;
    completed: boolean;
    assignee_id: number;
    assignee: User;
    created_at: string;
}

const props = defineProps({
    todoList: {
        type: Object as PropType<TodoList>,
        required: true,
    },
    tasks: {
        type: Array as PropType<Task[]>,
        required: true,
    },
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard() },
    { title: 'Todo Lists', href: '/todo-lists' },
    { title: props.todoList.name, href: `/todo-lists/${props.todoList.id}` },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="todoList.name" />

        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <!-- Header with list info -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div
                        class="h-8 w-8 rounded-full"
                        :style="{ backgroundColor: todoList.color }"
                    />
                    <h1 class="text-2xl font-bold">{{ todoList.name }}</h1>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="`/todo-lists/${todoList.id}/edit`">
                            <Pencil class="mr-2 h-4 w-4" />
                            Edit List
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Tasks table -->
            <div class="overflow-x-auto rounded-md border">
                <table class="w-full">
                    <thead class="bg-gray-100">
                        <tr class="text-left">
                            <th class="px-4 py-2">#</th>
                            <th class="px-4 py-2">Title</th>
                            <th class="px-4 py-2">Description</th>
                            <th class="px-4 py-2">Priority</th>
                            <th class="px-4 py-2">Status</th>
                            <th class="px-4 py-2">Assignee</th>
                            <th class="px-4 py-2">Created At</th>
                            <th class="px-4 py-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(task, index) in tasks"
                            :key="task.id"
                            class="border-b border-gray-200 hover:bg-gray-50"
                        >
                            <td class="px-4 py-2">{{ index + 1 }}</td>
                            <td class="px-4 py-2 font-medium">
                                {{ task.title }}
                            </td>
                            <td class="max-w-xs truncate px-4 py-2">
                                {{ task.description }}
                            </td>
                            <td class="px-4 py-2">
                                <span
                                    class="inline-flex rounded-full px-2 py-1 text-xs font-medium"
                                    :class="{
                                        'bg-green-100 text-green-800':
                                            task.priority === 'low',
                                        'bg-yellow-100 text-yellow-800':
                                            task.priority === 'medium',
                                        'bg-red-100 text-red-800':
                                            task.priority === 'high',
                                    }"
                                >
                                    {{
                                        task.priority.charAt(0).toUpperCase() +
                                        task.priority.slice(1)
                                    }}
                                </span>
                            </td>
                            <td class="px-4 py-2">
                                <span
                                    class="inline-flex rounded-full px-2 py-1 text-xs font-medium"
                                    :class="{
                                        'bg-green-100 text-green-800':
                                            task.completed,
                                        'bg-gray-100 text-gray-800':
                                            !task.completed,
                                    }"
                                >
                                    {{
                                        task.completed ? 'Completed' : 'Pending'
                                    }}
                                </span>
                            </td>
                            <td class="px-4 py-2">
                                {{ task.assignee?.name || 'Unassigned' }}
                            </td>
                            <td class="px-4 py-2">
                                {{
                                    new Date(
                                        task.created_at,
                                    ).toLocaleDateString()
                                }}
                            </td>
                            <td class="px-4 py-2">
                                <!-- <div class="flex gap-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        as-child
                                    >
                                        <Link :href="`/tasks/${task.id}/edit`">
                                            <Pencil class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                    <Button
                                        variant="destructive"
                                        size="sm"
                                        as-child
                                    >
                                        <Link
                                            :href="`/tasks/${task.id}`"
                                            method="delete"
                                            as="button"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                </div> -->
                            </td>
                        </tr>
                        <tr v-if="tasks.length === 0">
                            <td
                                colspan="8"
                                class="px-4 py-8 text-center text-gray-500"
                            >
                                No tasks found in this list.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
