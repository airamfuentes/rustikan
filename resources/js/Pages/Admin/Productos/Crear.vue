<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 text-xl font-semibold text-gray-800 dark:text-gray-200">
                    <Link :href="route('admin.tiendas.index')" class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">Tiendas</Link>
                    <span class="text-gray-300">/</span>
                    <Link :href="route('admin.tiendas.productos.index', tienda.id)" class="text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">{{ tienda.nombre }}</Link>
                    <span class="text-gray-300">/</span>
                    <span>Nuevo Producto</span>
                </div>
                <Link :href="route('admin.tiendas.productos.index', tienda.id)" class="rounded-lg bg-gray-600 dark:bg-gray-700 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700 dark:hover:bg-gray-600">
                    Volver
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-6">

                    <!-- Tienda (bloqueada) -->
                    <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-6 shadow-sm">
                        <h3 class="mb-4 text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">Tienda asociada</h3>
                        <div class="flex items-center gap-3 rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 px-4 py-3">
                            <img v-if="tienda.logo" :src="`/storage/${tienda.logo}`" class="h-8 w-8 rounded-full object-cover" />
                            <div v-else class="flex h-8 w-8 items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900/40 text-sm font-semibold text-primary-600 dark:text-primary-400">{{ tienda.nombre.charAt(0) }}</div>
                            <span class="font-medium text-gray-900 dark:text-white">{{ tienda.nombre }}</span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">· {{ tienda.categoria?.nombre }}</span>
                        </div>
                    </div>

                    <!-- Información del producto -->
                    <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-6 shadow-sm">
                        <h3 class="mb-5 text-base font-semibold text-gray-900 dark:text-white">Información del producto</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre del Producto *</label>
                                <input v-model="form.nombre" type="text" required
                                    class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition" />
                                <p v-if="form.errors.nombre" class="mt-1 text-xs text-red-500">{{ form.errors.nombre }}</p>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Descripción</label>
                                <textarea v-model="form.descripcion" rows="3"
                                    class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition resize-none"></textarea>
                                <p v-if="form.errors.descripcion" class="mt-1 text-xs text-red-500">{{ form.errors.descripcion }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Precio y stock -->
                    <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-6 shadow-sm">
                        <h3 class="mb-5 text-base font-semibold text-gray-900 dark:text-white">Precio y stock</h3>
                        <div class="grid gap-4 sm:grid-cols-3">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Precio (€) *</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 text-sm">€</span>
                                    <input v-model.number="form.precio" type="number" step="0.01" min="0" required
                                        class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 pl-7 pr-4 py-2.5 text-sm text-gray-900 dark:text-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition" />
                                </div>
                                <p v-if="form.errors.precio" class="mt-1 text-xs text-red-500">{{ form.errors.precio }}</p>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Precio Oferta (€)</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 text-sm">€</span>
                                    <input v-model.number="form.precio_oferta" type="number" step="0.01" min="0"
                                        class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 pl-7 pr-4 py-2.5 text-sm text-gray-900 dark:text-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition" />
                                </div>
                                <p v-if="form.errors.precio_oferta" class="mt-1 text-xs text-red-500">{{ form.errors.precio_oferta }}</p>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Unidad *</label>
                                <select v-model="form.unidad" required
                                    class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition">
                                    <option value="">-- Seleccionar --</option>
                                    <option value="unidad">Unidad</option>
                                    <option value="kg">Kilogramo (kg)</option>
                                    <option value="g">Gramo (g)</option>
                                    <option value="litro">Litro</option>
                                    <option value="ml">Mililitro (ml)</option>
                                    <option value="docena">Docena</option>
                                    <option value="caja">Caja</option>
                                    <option value="bote">Bote</option>
                                    <option value="bolsa">Bolsa</option>
                                </select>
                                <p v-if="form.errors.unidad" class="mt-1 text-xs text-red-500">{{ form.errors.unidad }}</p>
                            </div>
                        </div>

                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Stock Inicial *</label>
                                <input v-model.number="form.stock" type="number" min="0" required
                                    class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition" />
                                <p v-if="form.errors.stock" class="mt-1 text-xs text-red-500">{{ form.errors.stock }}</p>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Stock Mínimo *</label>
                                <input v-model.number="form.stock_minimo" type="number" min="0" required
                                    class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition" />
                                <p v-if="form.errors.stock_minimo" class="mt-1 text-xs text-red-500">{{ form.errors.stock_minimo }}</p>
                            </div>
                        </div>

                        <!-- Toggles -->
                        <div class="mt-5 flex flex-wrap gap-6">
                            <label class="flex cursor-pointer items-center gap-3">
                                <div class="relative">
                                    <input type="checkbox" v-model="form.disponible" class="sr-only" />
                                    <div :class="['h-6 w-11 rounded-full transition-colors', form.disponible ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600']"></div>
                                    <div :class="['absolute top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform', form.disponible ? 'translate-x-5' : 'translate-x-0.5']"></div>
                                </div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Disponible para la venta</span>
                            </label>
                            <label class="flex cursor-pointer items-center gap-3">
                                <div class="relative">
                                    <input type="checkbox" v-model="form.destacado" class="sr-only" />
                                    <div :class="['h-6 w-11 rounded-full transition-colors', form.destacado ? 'bg-yellow-500' : 'bg-gray-300 dark:bg-gray-600']"></div>
                                    <div :class="['absolute top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform', form.destacado ? 'translate-x-5' : 'translate-x-0.5']"></div>
                                </div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Destacar producto</span>
                            </label>
                        </div>
                    </div>

                    <!-- Imagen -->
                    <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-6 shadow-sm">
                        <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">URL de Imagen</h3>
                        <input v-model="form.imagen" type="text" placeholder="https://..."
                            class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition" />
                        <p v-if="form.errors.imagen" class="mt-1 text-xs text-red-500">{{ form.errors.imagen }}</p>
                        <div v-if="form.imagen" class="mt-3">
                            <img :src="form.imagen" alt="Vista previa" class="h-32 w-32 rounded-xl object-cover shadow border border-gray-200 dark:border-gray-600">
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="flex items-center justify-between">
                        <Link :href="route('admin.tiendas.productos.index', tienda.id)"
                            class="rounded-xl border border-gray-200 dark:border-gray-600 px-5 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Cancelar
                        </Link>
                        <button type="submit" :disabled="form.processing"
                            class="rounded-xl bg-primary-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-primary-700 disabled:opacity-50 transition-colors">
                            {{ form.processing ? 'Guardando...' : 'Crear Producto' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/LayoutAutenticado.vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    tienda: Object,
});

const form = useForm({
    tienda_id: props.tienda.id,
    nombre: '',
    descripcion: '',
    precio: null,
    precio_oferta: null,
    unidad: '',
    imagen: '',
    stock: 0,
    stock_minimo: 0,
    disponible: true,
    destacado: false,
});

const submit = () => {
    form.post(route('admin.productos.store'));
};
</script>
