<script setup>
import { ref, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Toast from '@/Components/Toast.vue';
import { ArrowLeft } from 'lucide-vue-next';

const page   = usePage();
const toasts = ref([]);

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
            v-for="toast in toasts"
            :key="toast.id"
            :type="toast.type"
            :title="toast.title"
            :message="toast.message"
            @close="removeToast(toast.id)"
        />
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
