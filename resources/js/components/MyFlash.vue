<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

type FlashType = 'success' | 'error' | 'info';
type FlashBag = {
    message?: string | null;
    success?: string | null;
    error?: string | null;
};

const page = usePage();

const show = ref(false);
const text = ref<string | null>(null);
const type = ref<FlashType>('info');
let timer: number | null = null;

function openToast(
    message: string,
    variant: FlashType = 'info',
    duration = 3500,
) {
    text.value = message;
    type.value = variant;
    show.value = true;

    if (timer) window.clearTimeout(timer);
    timer = window.setTimeout(() => {
        show.value = false;
        text.value = null;
    }, duration);
}

// Watch the shared flash bag from Inertia page props
watch(
    () => page.props.flash as FlashBag | undefined,
    (flash) => {
        const message = flash?.success ?? flash?.error ?? flash?.message;
        if (!message) return;
        const variant: FlashType = flash?.error
            ? 'error'
            : flash?.success
              ? 'success'
              : 'info';
        openToast(message, variant);
    },
    { deep: true, immediate: true },
);
</script>

<template>
    <!-- Toast container -->
    <transition name="fade">
        <div
            v-if="show"
            class="fixed right-4 bottom-4 z-[1000] max-w-sm min-w-[240px] rounded-xl p-4 text-white shadow-lg ring-1 ring-black/10"
            :class="{
                'bg-emerald-600': type === 'success',
                'bg-rose-600': type === 'error',
                'bg-slate-800': type === 'info',
            }"
            role="status"
            aria-live="polite"
        >
            <div class="flex items-start gap-3">
                <div class="mt-0.5">
                    <svg
                        v-if="type === 'success'"
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M5 13l4 4L19 7"
                        />
                    </svg>
                    <svg
                        v-else-if="type === 'error'"
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                    <svg
                        v-else
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z"
                        />
                    </svg>
                </div>
                <div class="flex-1">{{ text }}</div>
                <button
                    class="ml-2 opacity-80 hover:opacity-100 focus:outline-none"
                    @click="show = false"
                    aria-label="Dismiss"
                >
                    ×
                </button>
            </div>
        </div>
    </transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition:
        opacity 0.2s ease,
        transform 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(6px);
}
</style>
