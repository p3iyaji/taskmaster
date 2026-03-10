<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
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
import { Plus, Pencil, Trash2, ExternalLink, Loader2 } from 'lucide-vue-next';
import { ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard() },
    { title: 'Todo Lists', href: '/todo-lists' },
];

interface List {
    id: number;
    name: string;
    color: string;
}

interface TodoLists {
    id: number;
    name: string;
    color?: string;
    tasks_count?: number;
    created_at: string;
}

const props = defineProps({
    todoLists: {
        type: Array<TodoLists>,
        required: true,
    },
});

const colors = [
    '#6366f1',
    '#ef4444',
    '#10b981',
    '#f59e0b',
    '#8b5cf6',
    '#ec4899',
];

const isCreateDialogOpen = ref(false);
const isEditDialogOpen = ref(false);

const editingList = ref<{ id: number; name: string; color: string } | null>(
    null,
);
const deletingList = ref<{ id: number } | null>(null);

const form = useForm<List>({
    id: 0,
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

const handleEdit = (todoList: TodoLists) => {
    isEditDialogOpen.value = true;
    form.id = todoList.id;
    form.name = todoList.name;
    form.color = todoList.color || '#6366f1';
};

const handleUpdate = () => {
    const id = form.id as number;
    form.put(`/todo-lists/${id}`, {
        onSuccess: () => {
            isEditDialogOpen.value = false;
            form.reset();
        },
    });
};

const handleDelete = (todoList: TodoLists) => {
    deletingList.value = { id: todoList.id };
    form.delete(`/todo-lists/${todoList.id}`, {
        onSuccess: () => {
            deletingList.value = null;
            form.reset();
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Todo Lists" />

        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
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
                            <DialogTitle>Create New List</DialogTitle>
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
                                    />
                                    <InputError :message="form.errors.name" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="color">List Color</Label>
                                    <Input
                                        id="color"
                                        v-model="form.color"
                                        type="color"
                                        class="h-10 w-full"
                                    />
                                    <InputError :message="form.errors.color" />
                                    <div class="mt-2 flex items-center gap-2">
                                        <div
                                            v-for="color in colors"
                                            :key="color"
                                            class="h-8 w-8 cursor-pointer rounded-full transition-all hover:scale-110"
                                            :class="{
                                                'ring-2 ring-primary ring-offset-2':
                                                    color === form.color,
                                            }"
                                            :style="{ backgroundColor: color }"
                                            @click="form.color = color"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                            <DialogFooter>
                                <Button
                                    type="button"
                                    variant="outline"
                                    @click="isCreateDialogOpen = false"
                                >
                                    Cancel
                                </Button>
                                <Button
                                    type="submit"
                                    variant="default"
                                    :disabled="form.processing"
                                >
                                    <Loader2
                                        v-if="form.processing"
                                        class="mr-2 h-4 w-4 animate-spin"
                                    />
                                    {{
                                        form.processing
                                            ? 'Creating...'
                                            : 'Create List'
                                    }}
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>

            <!-- Todo Lists Grid - You can add this section to display the lists -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Card v-for="todoList in todoLists" :key="todoList.id">
                    <CardHeader>
                        <CardTitle class="flex items-center justify-between">
                            <span>{{ todoList.name }}</span>

                            <div class="flex items-center gap-2">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    @click="handleEdit(todoList)"
                                >
                                    <Pencil class="h-4 w-4" />
                                </Button>

                                <AlertDialog>
                                    <AlertDialogTrigger as-child>
                                        <Button variant="outline" size="icon">
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </AlertDialogTrigger>
                                    <AlertDialogContent>
                                        <AlertDialogHeader>
                                            <AlertDialogTitle
                                                >Are you sure?</AlertDialogTitle
                                            >
                                            <AlertDialogDescription>
                                                This action cannot be undone.
                                                This will permanently delete "{{
                                                    todoList.name
                                                }}" and all its tasks.
                                            </AlertDialogDescription>
                                        </AlertDialogHeader>
                                        <AlertDialogFooter>
                                            <AlertDialogCancel
                                                >Cancel</AlertDialogCancel
                                            >
                                            <AlertDialogAction
                                                @click="handleDelete(todoList)"
                                                :disabled="
                                                    deletingList?.id ===
                                                    todoList.id
                                                "
                                            >
                                                <Loader2
                                                    v-if="
                                                        deletingList?.id ===
                                                        todoList.id
                                                    "
                                                    class="mr-2 h-4 w-4 animate-spin"
                                                />
                                                {{
                                                    deletingList?.id ===
                                                    todoList.id
                                                        ? 'Deleting...'
                                                        : 'Delete'
                                                }}
                                            </AlertDialogAction>
                                        </AlertDialogFooter>
                                    </AlertDialogContent>
                                </AlertDialog>
                            </div>
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div
                            :style="{
                                backgroundColor: todoList.color || '#6366f1',
                            }"
                            class="h-4 rounded-full"
                        ></div>
                        <p class="text-sm text-muted-foreground">
                            {{ todoList.tasks_count || 0 }} tasks
                        </p>
                    </CardContent>
                </Card>
            </div>
        </div>

        <Dialog v-model:open="isEditDialogOpen">
            <DialogTrigger as-child> </DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Edit List</DialogTitle>
                    <DialogDescription>
                        Edit the list name and color
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="handleUpdate()">
                    <div class="space-y-4 py-4">
                        <div class="space-y-2">
                            <Label for="title">List Name</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                placeholder="Enter list name"
                            />
                        </div>
                        <div class="space-y-2">
                            <Label for="color">List Color</Label>
                            <Input
                                id="color"
                                v-model="form.color"
                                type="color"
                            />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            @click="isEditDialogOpen = false"
                            >Cancel</Button
                        >
                    </DialogFooter>
                    <Button
                        type="submit"
                        variant="default"
                        :disabled="form.processing"
                    >
                        <Loader2
                            v-if="form.processing"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        {{ form.processing ? 'Updating...' : 'Update List' }}
                    </Button>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
