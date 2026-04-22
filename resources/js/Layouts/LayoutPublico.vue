<script setup>
import { ref, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Toast from '@/Components/Toast.vue';

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

    <div class="flex min-h-screen flex-col items-center bg-gray-50 pt-6 sm:justify-center sm:pt-0">
        <!-- Logo -->
        <div>
            <Link href="/" class="flex items-center">
                <img src="/images/logo.png" alt="Rustikan" class="h-16 w-auto" />
            </Link>
        </div>

        <!-- Formulario -->
        <div class="mt-8 w-full rounded-xl bg-white px-8 py-8 shadow-md sm:max-w-md">
            <slot />
        </div>

        <!-- Volver al inicio -->
        <div class="mt-6">
            <Link href="/" class="text-sm text-gray-600 hover:text-gray-900">
                ← Volver al inicio
            </Link>
        </div>
    </div>
</template>
