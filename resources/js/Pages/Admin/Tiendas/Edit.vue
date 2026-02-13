<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Editar Tienda</h2>
                <Link :href="route('admin.tiendas.index')" class="rounded-lg bg-gray-600 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
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
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nombre de la Tienda *</label>
                                <input
                                    v-model="form.nombre"
                                    type="text"
                                    required
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                />
                                <p v-if="errors.nombre" class="mt-1 text-sm text-red-600">{{ errors.nombre }}</p>
                            </div>

                            <!-- Categoría -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Categoría *</label>
                                <select
                                    v-model="form.categoria_id"
                                    required
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                >
                                    <option value="">Selecciona una categoría</option>
                                    <option v-for="categoria in categorias" :key="categoria.id" :value="categoria.id">
                                        {{ categoria.icono }} {{ categoria.nombre }}
                                    </option>
                                </select>
                                <p v-if="errors.categoria_id" class="mt-1 text-sm text-red-600">{{ errors.categoria_id }}</p>
                            </div>

                            <!-- Descripción -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                                <textarea
                                    v-model="form.descripcion"
                                    rows="4"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                ></textarea>
                                <p v-if="errors.descripcion" class="mt-1 text-sm text-red-600">{{ errors.descripcion }}</p>
                            </div>

                            <!-- Teléfono -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Teléfono</label>
                                <input
                                    v-model="form.telefono"
                                    type="tel"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                />
                                <p v-if="errors.telefono" class="mt-1 text-sm text-red-600">{{ errors.telefono }}</p>
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                />
                                <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
                            </div>

                            <!-- Dirección -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Dirección</label>
                                <input
                                    v-model="form.direccion"
                                    type="text"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                />
                                <p v-if="errors.direccion" class="mt-1 text-sm text-red-600">{{ errors.direccion }}</p>
                            </div>

                            <!-- Estados -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="flex items-center gap-2">
                                        <input
                                            v-model="form.activa"
                                            type="checkbox"
                                            class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                        />
                                        <span class="text-sm font-medium text-gray-700">Tienda Activa</span>
                                    </label>
                                </div>
                                <div>
                                    <label class="flex items-center gap-2">
                                        <input
                                            v-model="form.visible"
                                            type="checkbox"
                                            class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                        />
                                        <span class="text-sm font-medium text-gray-700">Visible al Público</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="flex items-center justify-end gap-4 pt-4">
                                <Link
                                    :href="route('admin.tiendas.index')"
                                    class="rounded-lg bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300"
                                >
                                    Cancelar
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="processing"
                                    class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700 disabled:opacity-50"
                                >
                                    {{ processing ? 'Guardando...' : 'Guardar Cambios' }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    tienda: Object,
    categorias: Array,
    errors: Object,
});

const form = useForm({
    categoria_id: props.tienda.categoria_id,
    nombre: props.tienda.nombre,
    descripcion: props.tienda.descripcion,
    telefono: props.tienda.telefono,
    email: props.tienda.email,
    direccion: props.tienda.direccion,
    activa: props.tienda.activa,
    visible: props.tienda.visible,
});

const processing = form.processing;

const submit = () => {
    form.put(route('admin.tiendas.update', props.tienda.id), {
        preserveScroll: true,
        onSuccess: () => {
            // Opcional: mostrar notificación de éxito
        },
    });
};
</script>
