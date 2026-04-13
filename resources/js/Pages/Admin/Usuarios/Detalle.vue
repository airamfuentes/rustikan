<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Detalles del Usuario</h2>
                <Link :href="route('admin.usuarios.index')" class="rounded-lg bg-gray-600 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                    Volver
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="grid gap-6 lg:grid-cols-3">
                    <!-- Información del Usuario -->
                    <div class="lg:col-span-1">
                        <div class="rounded-lg bg-white p-6 shadow">
                            <div class="mb-6 flex items-center justify-center">
                                <img v-if="usuario.avatar" :src="`/storage/${usuario.avatar}`" class="h-24 w-24 rounded-full object-cover shadow-lg" :alt="usuario.name" />
                                <div v-else class="flex h-24 w-24 items-center justify-center rounded-full bg-primary-600 text-4xl font-bold text-white">
                                    {{ usuario.name.charAt(0).toUpperCase() }}
                                </div>
                            </div>
                            <h3 class="text-center text-2xl font-bold text-gray-900">{{ usuario.name }}</h3>
                            <p class="text-center text-sm text-gray-600">{{ usuario.email }}</p>
                            
                            <div class="mt-6 space-y-4">
                                <div class="flex items-center justify-between border-b pb-2">
                                    <span class="text-sm text-gray-600">Rol:</span>
                                    <span :class="{
                                        'bg-purple-100 text-purple-800': usuario.role === 'admin',
                                        'bg-blue-100 text-blue-800': usuario.role === 'user'
                                    }" class="rounded-full px-3 py-1 text-xs font-semibold">
                                        {{ usuario.role }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between border-b pb-2">
                                    <span class="text-sm text-gray-600">Registro:</span>
                                    <span class="text-sm font-medium text-gray-900">
                                        {{ new Date(usuario.created_at).toLocaleDateString('es-ES') }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between border-b pb-2">
                                    <span class="text-sm text-gray-600">Total Pedidos:</span>
                                    <span class="text-sm font-bold text-gray-900">{{ usuario.pedidos_count || 0 }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Total Gastado:</span>
                                    <span class="text-lg font-bold text-green-600">
                                        {{ Number(usuario.pedidos_sum || 0).toFixed(2) }}€
                                    </span>
                                </div>
                            </div>

                            <!-- Cambiar Rol -->
                            <div v-if="$page.props.auth.user.role === 'admin'" class="mt-6">
                                <label class="mb-2 block text-sm font-medium text-gray-700">Cambiar Rol</label>
                                <select
                                    v-model="nuevoRol"
                                    @change="cambiarRol"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                >
                                    <option value="user">Usuario</option>
                                    <option value="admin">Administrador</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Historial de Pedidos -->
                    <div class="lg:col-span-2">
                        <div class="rounded-lg bg-white p-6 shadow">
                            <h3 class="mb-4 text-xl font-bold text-gray-900">Historial de Pedidos</h3>
                            
                            <div v-if="usuario.pedidos && usuario.pedidos.length > 0" class="space-y-4">
                                <div
                                    v-for="pedido in usuario.pedidos"
                                    :key="pedido.id"
                                    class="rounded-lg border p-4 hover:shadow-md transition-shadow"
                                >
                                    <div class="flex items-center justify-between mb-3">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">Pedido #{{ pedido.numero_pedido }}</p>
                                            <p class="text-xs text-gray-500">{{ new Date(pedido.created_at).toLocaleDateString('es-ES') }}</p>
                                        </div>
                                        <span :class="{
                                            'bg-yellow-100 text-yellow-800': pedido.estado === 'pendiente',
                                            'bg-blue-100 text-blue-800': pedido.estado === 'en_proceso',
                                            'bg-green-100 text-green-800': pedido.estado === 'completado',
                                            'bg-red-100 text-red-800': pedido.estado === 'cancelado'
                                        }" class="rounded-full px-3 py-1 text-xs font-semibold">
                                            {{ pedido.estado }}
                                        </span>
                                    </div>
                                    
                                    <div v-if="pedido.items && pedido.items.length > 0" class="mb-3 space-y-2">
                                        <div
                                            v-for="item in pedido.items"
                                            :key="item.id"
                                            class="flex items-center gap-3 text-sm"
                                        >
                                            <img
                                                v-if="item.producto && item.producto.imagen"
                                                :src="item.producto.imagen"
                                                :alt="item.producto_nombre"
                                                class="h-10 w-10 rounded object-cover"
                                            />
                                            <div v-else class="flex h-10 w-10 items-center justify-center rounded bg-gray-200 text-gray-400">
                                                📦
                                            </div>
                                            <div class="flex-1">
                                                <p class="font-medium text-gray-900">{{ item.producto_nombre }}</p>
                                                <p class="text-xs text-gray-500">{{ item.tienda_nombre }}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-xs text-gray-600">{{ item.cantidad }}x {{ Number(item.precio_unitario).toFixed(2) }}€</p>
                                                <p class="font-semibold text-gray-900">{{ Number(item.subtotal).toFixed(2) }}€</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center justify-between border-t pt-3">
                                        <span class="text-sm font-medium text-gray-600">Total:</span>
                                        <span class="text-lg font-bold text-gray-900">{{ Number(pedido.total).toFixed(2) }}€</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div v-else class="py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Sin pedidos</h3>
                                <p class="mt-1 text-sm text-gray-500">Este usuario aún no ha realizado ningún pedido.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/LayoutAutenticado.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    usuario: Object,
});

const nuevoRol = ref(props.usuario.role);

const cambiarRol = () => {
    if (confirm(`¿Estás seguro de cambiar el rol de ${props.usuario.name} a ${nuevoRol.value}?`)) {
        router.put(route('admin.usuarios.update', props.usuario.id), {
            role: nuevoRol.value,
        }, {
            preserveScroll: true,
        });
    } else {
        nuevoRol.value = props.usuario.role;
    }
};
</script>
