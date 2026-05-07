<script setup>
import NavbarPublico from '@/Components/NavbarPublico.vue';
import ScrollToTop from '@/Components/ScrollToTop.vue';
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
        <!-- ToastContainer está montado globalmente en app.js -->

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
