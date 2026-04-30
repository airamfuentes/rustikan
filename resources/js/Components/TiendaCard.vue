<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import CategoriaIcono from '@/Components/CategoriaIcono.vue';

const props = defineProps({
    tienda: {
        type: Object,
        required: true
    }
});

// Diferentes formas de ondas para cada tienda
const ondas = [
    "M43.3,-72.8C54.5,-63.8,60.3,-47.5,65.9,-32.1C71.5,-16.7,76.9,-2.2,75.4,11.5C73.9,25.2,65.5,38.1,54.8,48.7C44.1,59.3,31.1,67.6,16.5,71.8C1.9,76,-14.3,76.1,-28.9,71.5C-43.5,66.9,-56.5,57.6,-65.8,45.2C-75.1,32.8,-80.7,17.3,-81.2,1.5C-81.7,-14.3,-77.1,-30.4,-68.3,-43.3C-59.5,-56.2,-46.5,-65.9,-32.8,-73.5C-19.1,-81.1,-4.7,-86.6,8.7,-84.3C22.1,-82,32.1,-81.8,43.3,-72.8Z",
    "M39.5,-65.5C51.4,-58.2,61.3,-47.3,68.2,-34.7C75.1,-22.1,79,-7.8,78.5,6.7C78,21.2,73.1,36,64.8,48.3C56.5,60.6,44.8,70.4,31.4,75.8C18,81.2,3,82.2,-11.7,80.3C-26.4,78.4,-40.8,73.6,-52.8,65.5C-64.8,57.4,-74.4,46,-79.2,32.8C-84,19.6,-84,4.6,-80.7,-9.3C-77.4,-23.2,-70.8,-36,-60.7,-46.3C-50.6,-56.6,-37,-64.4,-22.9,-69.9C-8.8,-75.4,5.8,-78.6,19.5,-76.5C33.2,-74.4,27.6,-72.8,39.5,-65.5Z",
    "M47.1,-78.5C60.9,-70.8,71.8,-57.1,78.3,-41.5C84.8,-25.9,86.9,-8.4,84.3,7.9C81.7,24.2,74.4,39.3,64.2,51.9C54,64.5,40.9,74.6,25.8,79.8C10.7,85,-6.4,85.3,-22.3,81.5C-38.2,77.7,-53,69.8,-64.5,58.3C-76,46.8,-84.2,31.7,-86.8,15.3C-89.4,-1.1,-86.4,-18.8,-78.8,-33.8C-71.2,-48.8,-59,-61.1,-44.5,-68.2C-30,-75.3,-13.2,-77.2,2.8,-81.7C18.8,-86.2,33.3,-86.2,47.1,-78.5Z",
    "M44.7,-76.4C58.8,-69.2,71.8,-59.1,79.6,-45.8C87.4,-32.6,90,-16.3,88.5,-0.9C87,14.6,81.4,29.2,73.1,42.8C64.8,56.4,53.8,69,39.9,76.8C26,84.6,9.2,87.6,-6.3,86.9C-21.8,86.2,-36.1,81.8,-49.7,74.3C-63.3,66.8,-76.2,56.2,-82.8,42.4C-89.4,28.6,-89.7,11.6,-87.3,-4.7C-84.9,-21,-79.8,-36.6,-70.7,-49.1C-61.6,-61.6,-48.5,-71,-34.3,-77.4C-20.1,-83.8,-5,-87.2,8.4,-84.6C21.8,-82,30.6,-83.6,44.7,-76.4Z",
    "M51.2,-84.7C64.8,-76.3,74.3,-60.5,80.1,-43.8C85.9,-27.1,88,-9.5,85.2,6.8C82.4,23.1,74.7,38.1,64.3,50.8C53.9,63.5,40.8,73.9,25.9,79.5C11,85.1,-5.7,85.9,-21.5,82.4C-37.3,78.9,-52.2,71.1,-63.5,59.3C-74.8,47.5,-82.5,31.7,-84.8,14.8C-87.1,-2.1,-84,-20.1,-76.4,-35.3C-68.8,-50.5,-56.7,-62.9,-42.5,-70.9C-28.3,-78.9,-12.2,-82.5,4.3,-89.1C20.8,-95.7,37.6,-93.1,51.2,-84.7Z"
];

const ondaPath = computed(() => {
    return ondas[props.tienda.id % ondas.length];
});
</script>

<template>
    <Link 
        :href="`/tienda/${tienda.slug}`"
        class="group block overflow-hidden rounded-xl bg-white shadow-sm transition-all duration-300 hover:shadow-xl"
    >
        <!-- Imagen de la tienda -->
        <div class="relative aspect-square overflow-hidden">
            <!-- Onda decorativa de fondo -->
            <svg class="absolute -inset-6 z-0 h-full w-full scale-150 text-primary-200 opacity-50" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                <path fill="currentColor" :d="ondaPath" transform="translate(100 100)" />
            </svg>
            
            <img 
                :src="tienda.imagen_portada ? `/storage/${tienda.imagen_portada}` : tienda.logo ? `/storage/${tienda.logo}` : '/images/logo.png'" 
                :alt="tienda.nombre"
                class="relative z-10 h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
            />
            
            <!-- Badge Categoría -->
            <div class="absolute left-3 top-3 z-20 flex items-center gap-1.5 rounded-full bg-primary-500 px-3 py-1 text-xs font-bold text-white shadow-lg">
                <CategoriaIcono :slug="tienda.categoria.slug" :icono="tienda.categoria.icono" class="h-3.5 w-3.5" />
                <span>{{ tienda.categoria.nombre }}</span>
            </div>

            <!-- Botón favorito -->
            <button 
                class="absolute right-3 top-3 z-20 flex h-8 w-8 items-center justify-center rounded-full bg-white/90 backdrop-blur-sm transition-colors hover:bg-white"
                @click.prevent
            >
                <svg class="h-5 w-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
            </button>
        </div>

        <!-- Información de la tienda -->
        <div class="p-4">
            <!-- Nombre -->
            <h3 class="text-lg font-bold text-gray-900">
                {{ tienda.nombre }}
            </h3>

            <!-- Ubicación -->
            <div class="mt-1 flex items-center text-sm text-gray-600">
                <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>{{ tienda.direccion }}</span>
            </div>

            <!-- Valoración y reseñas -->
            <div class="mt-3 flex items-center justify-between">
                <!-- Estrellas -->
                <div class="flex items-center gap-1">
                    <svg 
                        v-for="i in 5" 
                        :key="i"
                        :class="[
                            'h-4 w-4',
                            i <= Math.round(tienda.valoracion) ? 'text-primary-500' : 'text-gray-300'
                        ]"
                        fill="currentColor" 
                        viewBox="0 0 20 20"
                    >
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    <span class="ml-1 text-sm font-semibold text-gray-900">{{ Number(tienda.valoracion).toFixed(1) }}</span>
                </div>

                <!-- Reseñas -->
                <div class="text-sm text-gray-500">
                    {{ tienda.total_resenas }} reseñas
                </div>
            </div>
        </div>
    </Link>
</template>
