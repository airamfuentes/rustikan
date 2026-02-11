<script setup>
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Home" />

    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <div class="container mx-auto px-4 py-16">
            <div class="max-w-7xl mx-auto">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h1 class="text-4xl font-bold mb-4">
                            🚀 Laravel + Inertia + Vue
                        </h1>
                        <p class="text-gray-600 dark:text-gray-400 mb-6">
                            Tu proyecto está listo para empezar a desarrollar
                        </p>
                        
                        <div class="mt-8 space-y-4">
                            <div class="p-4 bg-green-100 dark:bg-green-900 rounded-lg">
                                <h3 class="font-semibold text-green-800 dark:text-green-200">✅ Stack Instalado</h3>
                                <ul class="mt-2 text-sm text-green-700 dark:text-green-300 space-y-1">
                                    <li>• Laravel 12 + Inertia.js + Vue 3</li>
                                    <li>• Sistema de autenticación completo</li>
                                    <li>• Tailwind CSS con modo oscuro</li>
                                    <li>• Vite para hot reload</li>
                                </ul>
                            </div>
                            
                            <div class="p-4 bg-blue-100 dark:bg-blue-900 rounded-lg">
                                <h3 class="font-semibold text-blue-800 dark:text-blue-200">💡 Próximos pasos</h3>
                                <ul class="mt-2 text-sm text-blue-700 dark:text-blue-300 space-y-1">
                                    <li>• Edita este archivo: <code class="px-1 bg-blue-200 dark:bg-blue-800 rounded">resources/js/Pages/Home.vue</code></li>
                                    <li>• Los cambios se actualizan automáticamente</li>
                                    <li>• Crea tus componentes en <code class="px-1 bg-blue-200 dark:bg-blue-800 rounded">resources/js/Components/</code></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
