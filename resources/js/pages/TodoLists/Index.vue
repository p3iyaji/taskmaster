<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { Pencil, Trash2, Plus } from 'lucide-vue-next';
import { ref } from 'vue';

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
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Todo Lists', href: '/todo-lists' },
];

interface TodoList {
    id: number;
    name: string;
    color: string;
    tasks_count?: number;
    created_at: string;
}

const props = defineProps<{
    todoLists: TodoList[];
}>();

const isCreateDialogOpen = ref(false);
const isEditDialogOpen = ref(false);
const deletingList = ref<number | null>(null);
const editingList = ref<TodoList | null>(null);

const form = useForm({
    name: '',
    color: '#6366f1',
});

const handleCreate = () => {
    form.post('/todo-lists', {
        onSuccess: () => {
            isCreateDialogOpen.value = false;
            form.reset();
        },
    });
};

const handleEdit = (list: TodoList) => {
    editingList.value = list;
    isEditDialogOpen.value = true;
    form.name = list.name;
    form.color = list.color;
};

const handleUpdate = () => {
    if (!editingList.value) return;

    form.put(`/todo-lists/${editingList.value.id}`, {
        onSuccess: () => {
            isEditDialogOpen.value = false;
            editingList.value = null;
            form.reset();
        },
    });
};

const handleDelete = (id: number) => {
    deletingList.value = id;
    form.delete(`/todo-lists/${id}`, {
        onSuccess: () => {
            deletingList.value = null;
        },
    });
};

const handleViewTasks = (id: number) => {
    router.get(`/todo-lists/${id}`);
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Todo Lists" />

        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Todo Lists</h1>
                <Dialog v-model:open="isCreateDialogOpen">
                    <DialogTrigger as-child>
                        <Button
                            ><Plus class="mr-2 h-4 w-4" /> Create List</Button
                        >
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Create Todo List</DialogTitle>
                            <DialogDescription>
                                Create a new todo list to organize your tasks
                            </DialogDescription>
                        </DialogHeader>
                        <form @submit.prevent="handleCreate">
                            <div class="space-y-4 py-4">
                                <div class="space-y-2">
                                    <Label for="name">List Name</Label>
                                    <Input
                                        id="name"
                                        v-model="form.name"
                                        placeholder="Enter list name"
                                        required
                                    />
                                </div>
                                <div class="space-y-2">
                                    <Label for="color">Color</Label>
                                    <div class="flex gap-2">
                                        <Input
                                            id="color"
                                            type="color"
                                            v-model="form.color"
                                            class="w-20"
                                        />
                                        <Input
                                            v-model="form.color"
                                            placeholder="#6366f1"
                                            class="flex-1"
                                        />
                                    </div>
                                </div>
                                <Button
                                    type="submit"
                                    :disabled="form.processing"
                                >
                                    {{
                                        form.processing
                                            ? 'Creating...'
                                            : 'Create List'
                                    }}
                                </Button>
                            </div>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Card v-for="list in todoLists" :key="list.id">
                    <CardHeader>
                        <CardTitle class="flex items-center justify-between">
                            <span class="flex items-center gap-2">
                                <span
                                    class="h-4 w-4 rounded-full"
                                    :style="{ backgroundColor: list.color }"
                                />
                                {{ list.name }}
                            </span>
                            <Badge>{{ list.tasks_count || 0 }} tasks</Badge>
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="flex justify-end gap-2">
                            <Button
                                variant="outline"
                                size="sm"
                                @click="handleViewTasks(list.id)"
                            >
                                <List class="h-4 w-4" />
                                View Tasks
                            </Button>
                            <Dialog v-model:open="isEditDialogOpen">
                                <DialogTrigger as-child>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        @click="handleEdit(list)"
                                    >
                                        <Pencil class="h-4 w-4" />
                                    </Button>
                                </DialogTrigger>
                                <DialogContent>
                                    <DialogHeader>
                                        <DialogTitle
                                            >Edit Todo List</DialogTitle
                                        >
                                        <DialogDescription>
                                            Edit your todo list
                                        </DialogDescription>
                                    </DialogHeader>
                                    <form @submit.prevent="handleUpdate">
                                        <div class="space-y-4 py-4">
                                            <div class="space-y-2">
                                                <Label for="edit-name"
                                                    >List Name</Label
                                                >
                                                <Input
                                                    id="edit-name"
                                                    v-model="form.name"
                                                    placeholder="Enter list name"
                                                    required
                                                />
                                            </div>
                                            <div class="space-y-2">
                                                <Label for="edit-color"
                                                    >Color</Label
                                                >
                                                <div class="flex gap-2">
                                                    <Input
                                                        id="edit-color"
                                                        type="color"
                                                        v-model="form.color"
                                                        class="w-20"
                                                    />
                                                    <Input
                                                        v-model="form.color"
                                                        placeholder="#6366f1"
                                                        class="flex-1"
                                                    />
                                                </div>
                                            </div>
                                            <Button
                                                type="submit"
                                                :disabled="form.processing"
                                            >
                                                {{
                                                    form.processing
                                                        ? 'Updating...'
                                                        : 'Update List'
                                                }}
                                            </Button>
                                        </div>
                                    </form>
                                </DialogContent>
                            </Dialog>

                            <AlertDialog>
                                <AlertDialogTrigger as-child>
                                    <Button variant="destructive" size="sm">
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </AlertDialogTrigger>
                                <AlertDialogContent>
                                    <AlertDialogHeader>
                                        <AlertDialogTitle
                                            >Are you sure?</AlertDialogTitle
                                        >
                                        <AlertDialogDescription>
                                            This action cannot be undone. This
                                            will permanently delete "{{
                                                list.name
                                            }}" and all its tasks.
                                        </AlertDialogDescription>
                                    </AlertDialogHeader>
                                    <AlertDialogFooter>
                                        <AlertDialogCancel
                                            >Cancel</AlertDialogCancel
                                        >
                                        <AlertDialogAction
                                            @click="handleDelete(list.id)"
                                            :disabled="deletingList === list.id"
                                        >
                                            {{
                                                deletingList === list.id
                                                    ? 'Deleting...'
                                                    : 'Delete'
                                            }}
                                        </AlertDialogAction>
                                    </AlertDialogFooter>
                                </AlertDialogContent>
                            </AlertDialog>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
