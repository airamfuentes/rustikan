<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Gestionar Usuarios</h2>
                <Link :href="route('admin.dashboard')" class="rounded-lg bg-gray-200 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300">
                    ← Volver
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <!-- Estadísticas -->
                <div class="mb-6 grid gap-4 sm:grid-cols-5">
                    <div class="rounded-lg bg-white p-4 shadow">
                        <p class="text-sm text-gray-600">Total Usuarios</p>
                        <p class="text-2xl font-bold text-gray-900">{{ stats.total }}</p>
                    </div>
                    <div class="rounded-lg bg-purple-100 p-4 shadow">
                        <p class="text-sm text-purple-800">Administradores</p>
                        <p class="text-2xl font-bold text-purple-900">{{ stats.admins }}</p>
                    </div>
                    <div class="rounded-lg bg-blue-100 p-4 shadow">
                        <p class="text-sm text-blue-800">Usuarios</p>
                        <p class="text-2xl font-bold text-blue-900">{{ stats.users }}</p>
                    </div>
                    <div class="rounded-lg bg-green-100 p-4 shadow">
                        <p class="text-sm text-green-800">Con Pedidos</p>
                        <p class="text-2xl font-bold text-green-900">{{ stats.con_pedidos }}</p>
                    </div>
                    <div class="rounded-lg bg-gray-100 p-4 shadow">
                        <p class="text-sm text-gray-800">Sin Pedidos</p>
                        <p class="text-2xl font-bold text-gray-900">{{ stats.sin_pedidos }}</p>
                    </div>
                </div>

                <!-- Búsqueda y Filtros -->
                <div class="mb-6 rounded-lg bg-white p-6 shadow">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Buscar</label>
                            <input
                                v-model="form.search"
                                @input="buscarConDebounce"
                                type="text"
                                placeholder="Nombre o email..."
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Rol</label>
                            <select
                                v-model="form.role"
                                @change="buscar"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            >
                                <option value="">Todos</option>
                                <option value="admin">Admin</option>
                                <option value="user">Usuario</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Fecha Desde</label>
                            <input
                                v-model="form.fecha_desde"
                                @change="buscar"
                                type="date"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-700">Fecha Hasta</label>
                            <input
                                v-model="form.fecha_hasta"
                                @change="buscar"
                                type="date"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            />
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button
                            @click="limpiarFiltros"
                            class="rounded-lg bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300"
                        >
                            Limpiar Filtros
                        </button>
                    </div>
                </div>

                <!-- Tabla de Usuarios -->
                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Usuario</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Rol</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Pedidos</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Total Gastado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Registro</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="usuario in usuarios.data" :key="usuario.id" class="hover:bg-gray-50">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-purple-500 to-purple-600 text-sm font-bold text-white">
                                            {{ usuario.name.charAt(0).toUpperCase() }}
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ usuario.name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ usuario.email }}</td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span :class="{
                                        'bg-purple-100 text-purple-800': usuario.role === 'admin',
                                        'bg-blue-100 text-blue-800': usuario.role === 'user'
                                    }" class="inline-flex rounded-full px-2 py-1 text-xs font-semibold">
                                        {{ usuario.role }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ usuario.pedidos_count || 0 }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">{{ Number(usuario.pedidos_sum || 0).toFixed(2) }}€</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ new Date(usuario.created_at).toLocaleDateString('es-ES') }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                    <div class="flex justify-end gap-3">
                                        <Link :href="route('admin.usuarios.edit', usuario.id)" class="text-blue-600 hover:text-blue-900">
                                            Editar
                                        </Link>
                                        <Link :href="route('admin.usuarios.show', usuario.id)" class="text-primary-600 hover:text-primary-900">
                                            Ver detalles
                                        </Link>
                                        <button
                                            v-if="usuario.id !== $page.props.auth.user.id"
                                            @click="eliminarUsuario(usuario)"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Paginación -->
                    <div class="border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Mostrando <span class="font-medium">{{ usuarios.from }}</span> a <span class="font-medium">{{ usuarios.to }}</span> de <span class="font-medium">{{ usuarios.total }}</span> resultados
                            </div>
                            <div class="flex gap-2">
                                <component
                                    v-for="link in usuarios.links" 
                                    :key="link.label" 
                                    :is="link.url ? Link : 'span'"
                                    :href="link.url" 
                                    :class="{
                                        'bg-primary-600 text-white': link.active,
                                        'bg-white text-gray-700 hover:bg-gray-50': !link.active && link.url,
                                        'cursor-not-allowed opacity-50': !link.url
                                    }"
                                    class="rounded-md border px-3 py-2 text-sm font-medium shadow-sm"
                                    v-html="link.label"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    usuarios: Object,
    stats: Object,
    filters: Object,
});

const form = ref({
    search: props.filters.search || '',
    role: props.filters.role || '',
    fecha_desde: props.filters.fecha_desde || '',
    fecha_hasta: props.filters.fecha_hasta || '',
});

let debounceTimer = null;

const buscar = () => {
    router.get(route('admin.usuarios.index'), form.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

const buscarConDebounce = () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        buscar();
    }, 500);
};

const limpiarFiltros = () => {
    form.value = {
        search: '',
        role: '',
        fecha_desde: '',
        fecha_hasta: '',
    };
    buscar();
};

const eliminarUsuario = (usuario) => {
    if (confirm(`¿Estás seguro de eliminar al usuario "${usuario.name}"? Esta acción no se puede deshacer.`)) {
        router.delete(route('admin.usuarios.destroy', usuario.id));
    }
};
</script>
