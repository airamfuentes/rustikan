<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 text-xl font-semibold text-gray-800 dark:text-gray-200">
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
                <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6">
                        <div class="space-y-6">
                            <!-- Nombre -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre del Producto *</label>
                                <input
                                    v-model="form.nombre"
                                    type="text"
                                    required
                                    minlength="2"
                                    maxlength="255"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                />
                                <p v-if="errors.nombre" class="mt-1 text-sm text-red-600">{{ errors.nombre }}</p>
                            </div>

                            <!-- Tienda (bloqueada) -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Tienda</label>
                                <div class="flex items-center gap-3 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 px-4 py-3">
                                    <img v-if="tienda.logo" :src="`/storage/${tienda.logo}`" class="h-8 w-8 rounded-full object-cover" />
                                    <div v-else class="flex h-8 w-8 items-center justify-center rounded-full bg-primary-100 dark:bg-gray-600 text-sm font-semibold text-primary-600 dark:text-primary-400">{{ tienda.nombre.charAt(0) }}</div>
                                    <span class="font-medium text-gray-900 dark:text-white">{{ tienda.nombre }}</span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">· {{ tienda.categoria?.nombre }}</span>
                                </div>
                            </div>

                            <!-- Descripción -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Descripción</label>
                                <textarea
                                    v-model="form.descripcion"
                                    rows="4"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                ></textarea>
                                <p v-if="errors.descripcion" class="mt-1 text-sm text-red-600">{{ errors.descripcion }}</p>
                            </div>

                            <!-- Precios en Grid -->
                            <div class="grid gap-4 md:grid-cols-3">
                                <!-- Precio -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Precio (€) *</label>
                                    <input
                                        v-model.number="form.precio"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        max="99999.99"
                                        required
                                        inputmode="decimal"
                                        v-only-decimal
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    />
                                    <p v-if="errors.precio" class="mt-1 text-sm text-red-600">{{ errors.precio }}</p>
                                </div>

                                <!-- Precio Oferta -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Precio Oferta (€)</label>
                                    <input
                                        v-model.number="form.precio_oferta"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        max="99999.99"
                                        inputmode="decimal"
                                        v-only-decimal
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    />
                                    <p v-if="errors.precio_oferta" class="mt-1 text-sm text-red-600">{{ errors.precio_oferta }}</p>
                                </div>

                                <!-- Unidad -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Unidad *</label>
                                    <select
                                        v-model="form.unidad"
                                        required
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    >
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
                                    <p v-if="errors.unidad" class="mt-1 text-sm text-red-600">{{ errors.unidad }}</p>
                                </div>
                            </div>

                            <!-- Stock en Grid -->
                            <div class="grid gap-4 md:grid-cols-2">
                                <!-- Stock -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Stock Actual *</label>
                                    <input
                                        v-model.number="form.stock"
                                        type="number"
                                        min="0"
                                        max="999999"
                                        required
                                        inputmode="numeric"
                                        step="1"
                                        v-only-digits
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    />
                                    <p v-if="errors.stock" class="mt-1 text-sm text-red-600">{{ errors.stock }}</p>
                                </div>

                                <!-- Stock Mínimo -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Stock Mínimo *</label>
                                    <input
                                        v-model.number="form.stock_minimo"
                                        type="number"
                                        min="0"
                                        max="999999"
                                        required
                                        inputmode="numeric"
                                        step="1"
                                        v-only-digits
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    />
                                    <p v-if="errors.stock_minimo" class="mt-1 text-sm text-red-600">{{ errors.stock_minimo }}</p>
                                </div>
                            </div>

                            <!-- Imagen URL -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">URL de Imagen</label>
                                <input
                                    v-model="form.imagen"
                                    type="text"
                                    placeholder="https://..."
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                />
                                <p v-if="errors.imagen" class="mt-1 text-sm text-red-600">{{ errors.imagen }}</p>
                                <div v-if="form.imagen" class="mt-2">
                                    <img :src="form.imagen" alt="Vista previa" class="h-32 w-32 rounded-lg object-cover shadow">
                                </div>
                            </div>

                            <!-- Estado y Destacado -->
                            <div class="grid gap-4 md:grid-cols-3">
                                <!-- Disponible -->
                                <div class="rounded-lg border dark:border-gray-700 p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Disponible</label>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">El producto está disponible para la venta</p>
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
                                                class="flex h-6 w-11 cursor-pointer items-center rounded-full bg-gray-300 dark:bg-gray-600 transition-colors peer-checked:bg-green-500"
                                            >
                                                <span class="ml-1 h-4 w-4 rounded-full bg-white transition-transform peer-checked:translate-x-5"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Destacado -->
                                <div class="rounded-lg border dark:border-gray-700 p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Destacado</label>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">Aparecerá en la sección de destacados</p>
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
                                                class="flex h-6 w-11 cursor-pointer items-center rounded-full bg-gray-300 dark:bg-gray-600 transition-colors peer-checked:bg-yellow-500"
                                            >
                                                <span class="ml-1 h-4 w-4 rounded-full bg-white transition-transform peer-checked:translate-x-5"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Oferta activa -->
                                <div class="rounded-lg border p-4" :class="form.oferta_activa ? 'border-green-300 bg-green-50 dark:bg-green-900/20 dark:border-green-700' : 'dark:border-gray-600'">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Oferta activa
                                                <span v-if="form.oferta_activa" class="ml-1 text-green-600">✓</span>
                                            </label>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                <span v-if="form.precio_oferta">Precio: {{ Number(form.precio_oferta).toFixed(2) }}€</span>
                                                <span v-else class="text-amber-600">Introduce un precio de oferta</span>
                                            </p>
                                        </div>
                                        <button
                                            type="button"
                                            :disabled="!form.precio_oferta"
                                            @click="form.oferta_activa = !form.oferta_activa"
                                            :class="['flex h-6 w-11 cursor-pointer items-center rounded-full transition-colors disabled:opacity-40 disabled:cursor-not-allowed',
                                                form.oferta_activa ? 'bg-green-500' : 'bg-gray-300']"
                                        >
                                            <span :class="['ml-1 h-4 w-4 rounded-full bg-white transition-transform',
                                                form.oferta_activa ? 'translate-x-5' : '']"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="mt-8 flex items-center justify-end gap-3 border-t pt-6">
                            <Link
                                :href="route('admin.tiendas.productos.index', tienda.id)"
                                class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-6 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600"
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
import { Link, useForm, router } from '@inertiajs/vue3';

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
    oferta_activa: props.producto.oferta_activa ?? false,
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
