<template>
    <transition
        enter-active-class="transform ease-out duration-300 transition"
        enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
        leave-active-class="transition ease-in duration-100"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="show"
            class="pointer-events-auto fixed right-0 top-20 z-50 mx-4 w-full max-w-sm overflow-hidden rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 sm:mx-6"
        >
            <div class="p-4">
                <div class="flex items-start">
                    <div class="shrink-0">
                        <!-- Success Icon -->
                        <svg
                            v-if="type === 'success'"
                            class="h-6 w-6 text-green-400"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        <!-- Error Icon -->
                        <svg
                            v-else-if="type === 'error'"
                            class="h-6 w-6 text-red-400"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        <!-- Info Icon -->
                        <svg
                            v-else-if="type === 'info'"
                            class="h-6 w-6 text-blue-400"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        <!-- Warning Icon -->
                        <svg
                            v-else
                            class="h-6 w-6 text-yellow-400"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                            />
                        </svg>
                    </div>
                    <div class="ml-3 w-0 flex-1 pt-0.5">
                        <p class="text-sm font-medium text-gray-900">
                            {{ title }}
                        </p>
                        <p v-if="message" class="mt-1 text-sm text-gray-500">
                            {{ message }}
                        </p>
                    </div>
                    <div class="ml-4 flex shrink-0">
                        <button
                            @click="close"
                            class="inline-flex rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                        >
                            <span class="sr-only">Cerrar</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    fill-rule="evenodd"
                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            <!-- Progress Bar -->
            <div class="h-1 w-full bg-gray-200">
                <div
                    class="h-full transition-all duration-100 ease-linear"
                    :class="{
                        'bg-green-500': type === 'success',
                        'bg-red-500': type === 'error',
                        'bg-blue-500': type === 'info',
                        'bg-yellow-500': type === 'warning'
                    }"
                    :style="{ width: progress + '%' }"
                ></div>
            </div>
        </div>
    </transition>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';

const props = defineProps({
    type: {
        type: String,
        default: 'info',
        validator: (value) => ['success', 'error', 'info', 'warning'].includes(value),
    },
    title: {
        type: String,
        required: true,
    },
    message: {
        type: String,
        default: '',
    },
    duration: {
        type: Number,
        default: 5000,
    },
});

const emit = defineEmits(['close']);

const show = ref(false);
const progress = ref(100);
let timer = null;
let progressInterval = null;

const close = () => {
    show.value = false;
    if (timer) clearTimeout(timer);
    if (progressInterval) clearInterval(progressInterval);
    setTimeout(() => {
        emit('close');
    }, 300);
};

const startTimer = () => {
    show.value = true;
    progress.value = 100;

    // Iniciar barra de progreso
    const steps = props.duration / 100;
    progressInterval = setInterval(() => {
        progress.value -= 100 / (props.duration / 100);
        if (progress.value <= 0) {
            clearInterval(progressInterval);
        }
    }, steps);

    // Auto-cerrar después de la duración
    timer = setTimeout(() => {
        close();
    }, props.duration);
};

onMounted(() => {
    startTimer();
});

// Reiniciar timer si cambia el título
watch(() => props.title, () => {
    if (timer) clearTimeout(timer);
    if (progressInterval) clearInterval(progressInterval);
    startTimer();
});
</script>
