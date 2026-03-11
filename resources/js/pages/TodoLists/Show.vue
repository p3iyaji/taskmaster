<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter,
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

import InputError from '@/components/InputError.vue';
import { type BreadcrumbItem } from '@/types';
import { dashboard } from '@/routes';
import {
    Plus,
    Pencil,
    Trash2,
    ExternalLink,
    Loader2,
    Eye,
} from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps({
    todoList: {
        type: Object as PropType<List>,
        required: true,
    },
    tasks: {
        type: Array as PropType<Task[]>,
        required: true,
    },
});

interface User {
    id: number;
    name: string;
    email: string;
}

interface List {
    id: number;
    name: string;
    color: string;
    user_id: number;
    user: User;
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

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard() },
    { title: 'Todo Lists', href: '/todo-lists' },
    { title: 'Show', href: `/todo-lists/${props.todoList.id}` },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Todo Lists" />

        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">{{ todoList.name }}</h1>
            </div>

            <!-- Todo Lists Grid - You can add this section to display the lists -->
            <div class="w-full table-auto">
                <table class="w-full">
                    <thead class="bg-gray-100">
                        <tr class="text-left">
                            <th class="px-4 py-2">#</th>
                            <th class="px-4 py-2">Title</th>
                            <th class="px-4 py-2">Description</th>
                            <th class="px-4 py-2">Priority</th>
                            <th class="px-4 py-2">Completed</th>
                            <th class="px-4 py-2">Assignee</th>
                            <th class="px-4 py-2">Created At</th>
                            <th class="px-4 py-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(task, index) in tasks" :key="task.id">
                            <td class="px-4 py-2">{{ index + 1 }}</td>
                            <td class="px-4 py-2">{{ task.title }}</td>
                            <td class="px-4 py-2">{{ task.description }}</td>
                            <td class="px-4 py-2">{{ task.priority }}</td>
                            <td class="px-4 py-2">
                                {{ task.completed ? 'Yes' : 'No' }}
                            </td>
                            <td class="px-4 py-2">{{ task.created_at }}</td>
                            <td class="px-4 py-2">{{ task.assignee.name }}</td>
                            <td>
                                <Button variant="outline" size="sm">
                                    <Pencil class="h-4 w-4" />
                                </Button>
                                <Button variant="outline" size="sm">
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
