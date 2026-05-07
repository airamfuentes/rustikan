<template>
    <transition
        enter-active-class="transform ease-out duration-300 transition"
        enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-2"
    >
        <div
            v-if="show"
            class="pointer-events-auto w-full overflow-hidden rounded-xl bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black/5 dark:ring-white/10"
            role="alert"
        >
            <div class="p-4 pr-3">
                <div class="flex items-start gap-3">
                    <!-- Icono según tipo -->
                    <div :class="[
                        'flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full',
                        bgClass,
                    ]">
                        <svg v-if="type === 'success'" class="h-5 w-5" :class="iconClass" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        <svg v-else-if="type === 'error'" class="h-5 w-5" :class="iconClass" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <svg v-else-if="type === 'warning'" class="h-5 w-5" :class="iconClass" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <svg v-else class="h-5 w-5" :class="iconClass" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>

                    <!-- Texto -->
                    <div class="min-w-0 flex-1 pt-0.5">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white break-words">{{ title }}</p>
                        <p v-if="message" class="mt-0.5 text-sm text-gray-500 dark:text-gray-400 break-words">{{ message }}</p>
                    </div>

                    <!-- Cerrar -->
                    <button
                        @click="close"
                        class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors"
                        aria-label="Cerrar"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Progress bar inferior -->
            <div class="h-1 w-full bg-gray-100 dark:bg-gray-700/60 overflow-hidden">
                <div
                    :class="['h-full transition-[width] ease-linear', progressBgClass]"
                    :style="{ width: progress + '%', transitionDuration: progressTickMs + 'ms' }"
                ></div>
            </div>
        </div>
    </transition>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';

const props = defineProps({
    type:     { type: String, default: 'info', validator: (v) => ['success','error','info','warning'].includes(v) },
    title:    { type: String, required: true },
    message:  { type: String, default: '' },
    duration: { type: Number, default: 5000 },
    /**
     * Si false, el toast se monta pero su timer no arranca hasta que active=true.
     * Permite encolarlos en serie.
     */
    active:   { type: Boolean, default: true },
});

const emit = defineEmits(['close']);

const show = ref(false);
const progress = ref(100);
let timerId = null;
let intervalId = null;

const PROGRESS_TICK = 50; // ms entre actualizaciones de la barra

const progressTickMs = computed(() => PROGRESS_TICK);

const bgClass = computed(() => ({
    success: 'bg-green-100 dark:bg-green-900/30',
    error:   'bg-red-100   dark:bg-red-900/30',
    warning: 'bg-yellow-100 dark:bg-yellow-900/30',
    info:    'bg-blue-100  dark:bg-blue-900/30',
}[props.type]));

const iconClass = computed(() => ({
    success: 'text-green-600 dark:text-green-400',
    error:   'text-red-600   dark:text-red-400',
    warning: 'text-yellow-600 dark:text-yellow-400',
    info:    'text-blue-600  dark:text-blue-400',
}[props.type]));

const progressBgClass = computed(() => ({
    success: 'bg-green-500',
    error:   'bg-red-500',
    warning: 'bg-yellow-500',
    info:    'bg-blue-500',
}[props.type]));

const clearTimers = () => {
    if (timerId) { clearTimeout(timerId); timerId = null; }
    if (intervalId) { clearInterval(intervalId); intervalId = null; }
};

const close = () => {
    clearTimers();
    show.value = false;
    setTimeout(() => emit('close'), 200);
};

const startTimer = () => {
    clearTimers();
    show.value = true;
    progress.value = 100;

    const decrementPerTick = (PROGRESS_TICK / props.duration) * 100;

    intervalId = setInterval(() => {
        progress.value = Math.max(0, progress.value - decrementPerTick);
    }, PROGRESS_TICK);

    timerId = setTimeout(close, props.duration);
};

onMounted(() => {
    if (props.active) startTimer();
    else show.value = true; // visible pero sin timer hasta que sea activo
});

onUnmounted(clearTimers);

watch(() => props.active, (v) => {
    if (v && !timerId) startTimer();
});
</script>
