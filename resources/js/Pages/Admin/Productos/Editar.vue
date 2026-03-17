<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 text-xl font-semibold text-gray-800">
                    <Link :href="route('admin.tiendas.index')" class="text-gray-400 hover:text-gray-700">Tiendas</Link>
                    <span class="text-gray-300">/</span>
                    <Link :href="route('admin.tiendas.productos.index', tienda.id)" class="text-gray-400 hover:text-gray-700">{{ tienda.nombre }}</Link>
                    <span class="text-gray-300">/</span>
                    <span>Editar Producto</span>
                </div>
                <Link :href="route('admin.tiendas.productos.index', tienda.id)" class="rounded-lg bg-gray-600 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                    Volver
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6">
                        <div class="space-y-6">
                            <!-- Nombre -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">Nombre del Producto *</label>
                                <input
                                    v-model="form.nombre"
                                    type="text"
                                    required
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                />
                                <p v-if="errors.nombre" class="mt-1 text-sm text-red-600">{{ errors.nombre }}</p>
                            </div>

                            <!-- Tienda (bloqueada) -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">Tienda</label>
                                <div class="flex items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                                    <img v-if="tienda.logo" :src="tienda.logo" class="h-8 w-8 rounded-full object-cover" />
                                    <div v-else class="flex h-8 w-8 items-center justify-center rounded-full bg-primary-100 text-sm font-semibold text-primary-600">{{ tienda.nombre.charAt(0) }}</div>
                                    <span class="font-medium text-gray-900">{{ tienda.nombre }}</span>
                                    <span class="text-sm text-gray-500">· {{ tienda.categoria?.nombre }}</span>
                                </div>
                            </div>

                            <!-- Descripción -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">Descripción</label>
                                <textarea
                                    v-model="form.descripcion"
                                    rows="4"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                ></textarea>
                                <p v-if="errors.descripcion" class="mt-1 text-sm text-red-600">{{ errors.descripcion }}</p>
                            </div>

                            <!-- Precios en Grid -->
                            <div class="grid gap-4 md:grid-cols-3">
                                <!-- Precio -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700">Precio (€) *</label>
                                    <input
                                        v-model.number="form.precio"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        required
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    />
                                    <p v-if="errors.precio" class="mt-1 text-sm text-red-600">{{ errors.precio }}</p>
                                </div>

                                <!-- Precio Oferta -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700">Precio Oferta (€)</label>
                                    <input
                                        v-model.number="form.precio_oferta"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    />
                                    <p v-if="errors.precio_oferta" class="mt-1 text-sm text-red-600">{{ errors.precio_oferta }}</p>
                                </div>

                                <!-- Unidad -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700">Unidad *</label>
                                    <input
                                        v-model="form.unidad"
                                        type="text"
                                        placeholder="kg, unidad, litro..."
                                        required
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    />
                                    <p v-if="errors.unidad" class="mt-1 text-sm text-red-600">{{ errors.unidad }}</p>
                                </div>
                            </div>

                            <!-- Stock en Grid -->
                            <div class="grid gap-4 md:grid-cols-2">
                                <!-- Stock -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700">Stock Actual *</label>
                                    <input
                                        v-model.number="form.stock"
                                        type="number"
                                        min="0"
                                        required
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    />
                                    <p v-if="errors.stock" class="mt-1 text-sm text-red-600">{{ errors.stock }}</p>
                                </div>

                                <!-- Stock Mínimo -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700">Stock Mínimo *</label>
                                    <input
                                        v-model.number="form.stock_minimo"
                                        type="number"
                                        min="0"
                                        required
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    />
                                    <p v-if="errors.stock_minimo" class="mt-1 text-sm text-red-600">{{ errors.stock_minimo }}</p>
                                </div>
                            </div>

                            <!-- Imagen URL -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">URL de Imagen</label>
                                <input
                                    v-model="form.imagen"
                                    type="text"
                                    placeholder="https://..."
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                />
                                <p v-if="errors.imagen" class="mt-1 text-sm text-red-600">{{ errors.imagen }}</p>
                                <div v-if="form.imagen" class="mt-2">
                                    <img :src="form.imagen" alt="Vista previa" class="h-32 w-32 rounded-lg object-cover shadow">
                                </div>
                            </div>

                            <!-- Estado y Destacado -->
                            <div class="grid gap-4 md:grid-cols-2">
                                <!-- Disponible -->
                                <div class="rounded-lg border p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Disponible</label>
                                            <p class="text-xs text-gray-500">El producto está disponible para la venta</p>
                                        </div>
                                        <div class="relative">
                                            <input
                                                v-model="form.disponible"
                                                type="checkbox"
                                                class="peer sr-only"
                                                :id="'disponible'"
                                            />
                                            <label
                                                :for="'disponible'"
                                                class="flex h-6 w-11 cursor-pointer items-center rounded-full bg-gray-300 transition-colors peer-checked:bg-green-500"
                                            >
                                                <span class="ml-1 h-4 w-4 rounded-full bg-white transition-transform peer-checked:translate-x-5"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Destacado -->
                                <div class="rounded-lg border p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Destacado</label>
                                            <p class="text-xs text-gray-500">Aparecerá en la sección de destacados</p>
                                        </div>
                                        <div class="relative">
                                            <input
                                                v-model="form.destacado"
                                                type="checkbox"
                                                class="peer sr-only"
                                                :id="'destacado'"
                                            />
                                            <label
                                                :for="'destacado'"
                                                class="flex h-6 w-11 cursor-pointer items-center rounded-full bg-gray-300 transition-colors peer-checked:bg-yellow-500"
                                            >
                                                <span class="ml-1 h-4 w-4 rounded-full bg-white transition-transform peer-checked:translate-x-5"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="mt-8 flex items-center justify-end gap-3 border-t pt-6">
                            <Link
                                :href="route('admin.tiendas.productos.index', tienda.id)"
                                class="rounded-lg border border-gray-300 bg-white px-6 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                            >
                                Cancelar
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-lg bg-primary-600 px-6 py-2 text-sm font-semibold text-white hover:bg-primary-700 disabled:opacity-50"
                            >
                                {{ form.processing ? 'Guardando...' : 'Guardar Cambios' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/LayoutAutenticado.vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    producto: Object,
    tienda: Object,
    errors: Object,
});

const form = useForm({
    tienda_id: props.tienda.id,
    nombre: props.producto.nombre,
    descripcion: props.producto.descripcion || '',
    precio: props.producto.precio,
    precio_oferta: props.producto.precio_oferta || '',
    unidad: props.producto.unidad,
    imagen: props.producto.imagen || '',
    stock: props.producto.stock,
    stock_minimo: props.producto.stock_minimo,
    disponible: props.producto.disponible,
    destacado: props.producto.destacado,
});

const submit = () => {
    form.put(route('admin.productos.update', props.producto.id), {
        preserveScroll: true,
    });
};
</script>
