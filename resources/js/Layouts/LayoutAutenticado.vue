<script setup>
import { ref, watch } from 'vue';
import NavbarPublico from '@/Components/NavbarPublico.vue';
import Toast from '@/Components/Toast.vue';
import ScrollToTop from '@/Components/ScrollToTop.vue';
import { usePage } from '@inertiajs/vue3';

const toasts = ref([]);

const showToast = (type, title, message) => {
    const id = Date.now();
    toasts.value.push({ id, type, title, message });
};

const removeToast = (id) => {
    toasts.value = toasts.value.filter((t) => t.id !== id);
};

const page = usePage();
watch(
    () => page.props.flash,
    (flash) => {
        if (!flash || !Object.keys(flash).length) return;
        if (flash.success) showToast('success', 'Éxito', flash.success);
        else if (flash.error) showToast('error', 'Error', flash.error);
        else if (flash.info) showToast('info', 'Información', flash.info);
        else if (flash.warning) showToast('warning', 'Advertencia', flash.warning);
    },
    { deep: true }
);
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
        <!-- Toast Notifications -->
        <div class="pointer-events-none fixed top-20 right-4 z-[9999] flex flex-col items-end gap-3 max-w-sm w-full">
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

        <!-- Navbar (same as public, already has admin/owner links + mobile drawer) -->
        <NavbarPublico />

        <!-- Page Heading -->
        <header v-if="$slots.header" class="bg-white dark:bg-gray-900 shadow dark:shadow-gray-700/30 pt-20 sm:pt-24">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <slot name="header" />
            </div>
        </header>

        <!-- Page Content -->
        <main :class="$slots.header ? '' : 'pt-20 sm:pt-24'">
            <slot />
        </main>

        <ScrollToTop />
    </div>
</template>
