<script setup>
import { ref, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Toast from '@/Components/Toast.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import { ArrowLeft } from 'lucide-vue-next';
import { useDarkMode } from '@/Composables/useDarkMode';

const page   = usePage();
const toasts = ref([]);
const { isDark, toggleDark } = useDarkMode();

const addToast = (type, title, message = '') => {
    const id = Date.now() + Math.random();
    toasts.value.push({ id, type, title, message });
};
const removeToast = (id) => {
    toasts.value = toasts.value.filter(t => t.id !== id);
};

watch(
    () => page.props.flash,
    (flash) => {
        if (!flash) return;
        if (flash.success) addToast('success', '¡Éxito!', flash.success);
        if (flash.error)   addToast('error',   'Error',   flash.error);
        if (flash.info)    addToast('info',     'Info',    flash.info);
        if (flash.warning) addToast('warning',  'Aviso',   flash.warning);
    },
    { deep: true, immediate: true },
);
</script>

<template>
    <!-- Toast container -->
    <div class="pointer-events-none fixed inset-0 z-[9999] flex flex-col items-end justify-start gap-3 p-6">
        <Toast
            v-for="(toast, index) in toasts"
            :key="toast.id"
            :type="toast.type"
            :title="toast.title"
            :message="toast.message"
            :active="index === 0"
            @close="removeToast(toast.id)"
        />
    </div>

    <!-- Dark/Light toggle + Idiomas -->
    <div class="fixed top-4 right-4 z-50 flex flex-col items-end gap-3">
        <button
            @click="toggleDark"
            aria-label="Cambiar tema"
            class="h-11 w-11 flex items-center justify-center rounded-full border transition-all duration-300 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-offset-2"
            :class="isDark
                ? 'bg-gray-800 border-gray-600 text-amber-400 hover:bg-gray-700 focus:ring-amber-400 focus:ring-offset-gray-900'
                : 'bg-white border-gray-200 text-indigo-500 hover:bg-gray-50 focus:ring-indigo-400'"
        >
            <span class="relative h-5 w-5 overflow-hidden">
                <!-- Sol (visible en dark, se gira y escala al entrar/salir) -->
                <svg
                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="absolute inset-0 h-5 w-5 text-amber-400 transition-all duration-500 ease-in-out"
                    :class="isDark ? 'rotate-0 scale-100 opacity-100' : 'rotate-90 scale-0 opacity-0'"
                >
                    <circle cx="12" cy="12" r="4"/>
                    <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>
                </svg>
                <!-- Luna (visible en light) -->
                <svg
                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="absolute inset-0 h-5 w-5 text-indigo-500 transition-all duration-500 ease-in-out"
                    :class="isDark ? '-rotate-90 scale-0 opacity-0' : 'rotate-0 scale-100 opacity-100'"
                >
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                </svg>
            </span>
        </button>

        <LanguageSwitcher />
    </div>

    <div class="flex min-h-screen flex-col items-center bg-gray-50 dark:bg-gray-950 pt-6 sm:justify-center sm:pt-0 transition-colors duration-300">
        <!-- Logo -->
        <div>
            <Link href="/" class="flex items-center">
                <img src="/images/logo.png" alt="Rustikan" class="h-16 w-auto" />
            </Link>
        </div>

        <!-- Formulario -->
        <div class="mt-8 w-full rounded-xl bg-white dark:bg-gray-800 px-8 py-8 shadow-md sm:max-w-md dark:shadow-gray-900/50">
            <slot />
        </div>

        <!-- Volver al inicio -->
        <div class="mt-6">
            <Link href="/" class="inline-flex items-center gap-1 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
                <ArrowLeft class="h-3.5 w-3.5" /> Volver al inicio
            </Link>
        </div>
    </div>
</template>
