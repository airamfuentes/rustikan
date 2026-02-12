<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
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
