<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Panel" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Panel Principal
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-xl bg-white shadow-md">
                    <div class="p-8 text-gray-900">
                        <div class="mb-6">
                            <h3 class="text-2xl font-bold text-gray-800">
                                ¡Bienvenido a Rustikan!
                            </h3>
                            <p class="mt-2 text-gray-600">
                                Tu panel de control está listo
                            </p>
                        </div>

                        <!-- Grid de estadísticas -->
                        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            <!-- Card 1 -->
                            <div class="rounded-xl bg-gray-50 p-6 shadow-sm">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-600">Tiendas Favoritas</p>
                                        <p class="mt-2 text-3xl font-bold text-gray-800">0</p>
                                    </div>
                                    <div class="rounded-full bg-primary-500 p-3">
                                        <svg class="h-6 w-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 2 -->
                            <div class="rounded-xl bg-gray-50 p-6 shadow-sm">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-600">Búsquedas</p>
                                        <p class="mt-2 text-3xl font-bold text-gray-800">0</p>
                                    </div>
                                    <div class="rounded-full bg-primary-500 p-3">
                                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Card 3 -->
                            <div class="rounded-xl bg-gray-50 p-6 shadow-sm">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-600">Visitas</p>
                                        <p class="mt-2 text-3xl font-bold text-gray-800">0</p>
                                    </div>
                                    <div class="rounded-full bg-primary-500 p-3">
                                        <svg class="h-6 w-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sección de acciones rápidas -->
                        <div class="mt-8">
                            <h4 class="mb-4 text-lg font-semibold text-gray-800">Acciones Rápidas</h4>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <a href="/" class="flex items-center gap-3 rounded-lg border-2 border-gray-200 p-4 transition-all hover:border-primary-500 hover:bg-gray-50">
                                    <div class="rounded-lg bg-primary-500 p-2">
                                        <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-800">Explorar Tiendas</p>
                                        <p class="text-sm text-gray-600">Encuentra nuevas tiendas</p>
                                    </div>
                                </a>

                                <a href="/profile" class="flex items-center gap-3 rounded-lg border-2 border-gray-200 p-4 transition-all hover:border-primary-500 hover:bg-gray-50">
                                    <div class="rounded-lg bg-primary-500 p-2">
                                        <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-800">Mi Perfil</p>
                                        <p class="text-sm text-gray-600">Gestionar información</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
