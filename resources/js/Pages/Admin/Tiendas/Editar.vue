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
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6">
                        <div class="grid gap-8 lg:grid-cols-2">

                            <!-- Columna Izquierda: Datos -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-semibold text-gray-900">Datos de la Tienda</h3>

                                <!-- Nombre -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700">Nombre *</label>
                                    <input v-model="form.nombre" type="text" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" />
                                    <p v-if="form.errors.nombre" class="mt-1 text-sm text-red-600">{{ form.errors.nombre }}</p>
                                </div>

                                <!-- Categoría -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700">Categoría *</label>
                                    <select v-model="form.categoria_id" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                        <option value="">Selecciona una categoría</option>
                                        <option v-for="categoria in categorias" :key="categoria.id" :value="categoria.id">
                                            {{ categoria.icono }} {{ categoria.nombre }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.categoria_id" class="mt-1 text-sm text-red-600">{{ form.errors.categoria_id }}</p>
                                </div>

                                <!-- Descripción -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700">Descripción</label>
                                    <textarea v-model="form.descripcion" rows="4" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
                                    <p v-if="form.errors.descripcion" class="mt-1 text-sm text-red-600">{{ form.errors.descripcion }}</p>
                                </div>

                                <!-- Teléfono -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700">Teléfono</label>
                                    <input v-model="form.telefono" type="tel" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" />
                                    <p v-if="form.errors.telefono" class="mt-1 text-sm text-red-600">{{ form.errors.telefono }}</p>
                                </div>

                                <!-- Email -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700">Email</label>
                                    <input v-model="form.email" type="email" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" />
                                    <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                                </div>

                                <!-- Dirección -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700">Dirección</label>
                                    <input v-model="form.direccion" type="text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" />
                                    <p v-if="form.errors.direccion" class="mt-1 text-sm text-red-600">{{ form.errors.direccion }}</p>
                                </div>

                                <!-- Estados -->
                                <div class="grid grid-cols-2 gap-4">
                                    <label class="flex items-center gap-2">
                                        <input v-model="form.activa" type="checkbox" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500" />
                                        <span class="text-sm font-medium text-gray-700">Tienda Activa</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input v-model="form.visible" type="checkbox" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500" />
                                        <span class="text-sm font-medium text-gray-700">Visible al Público</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Columna Derecha: Imágenes -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-semibold text-gray-900">Imágenes</h3>

                                <!-- Logo -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700">Logo</label>

                                    <!-- Imagen actual -->
                                    <div v-if="logoActual && !form.delete_logo" class="mb-3">
                                        <p class="mb-1 text-xs text-gray-500">Imagen actual:</p>
                                        <div class="relative inline-block">
                                            <img :src="logoActual" alt="Logo actual" class="h-28 w-28 rounded-lg object-cover shadow" />
                                            <button type="button" @click="eliminarLogo" class="absolute -right-2 -top-2 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-white shadow hover:bg-red-600">
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div v-else-if="form.delete_logo" class="mb-3 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-600">
                                        Logo marcado para eliminar.
                                        <button type="button" @click="restaurarLogo" class="ml-1 font-semibold underline">Deshacer</button>
                                    </div>

                                    <!-- Vista previa nueva -->
                                    <div v-if="logoPreview" class="mb-3">
                                        <p class="mb-1 text-xs text-gray-500">Nueva imagen:</p>
                                        <img :src="logoPreview" alt="Vista previa logo" class="h-28 w-28 rounded-lg object-cover shadow" />
                                    </div>

                                    <input type="file" accept="image/*" @change="handleLogoUpload" class="w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-700 hover:file:bg-primary-100" />
                                    <p class="mt-1 text-xs text-gray-400">Cuadrada recomendada · JPG/PNG/WebP · máx. 2MB</p>
                                    <p v-if="form.errors.logo" class="mt-1 text-sm text-red-600">{{ form.errors.logo }}</p>
                                </div>

                                <!-- Imagen de Portada -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700">Imagen de Portada</label>

                                    <!-- Imagen actual -->
                                    <div v-if="portadaActual && !form.delete_imagen_portada" class="mb-3">
                                        <p class="mb-1 text-xs text-gray-500">Imagen actual:</p>
                                        <div class="relative">
                                            <img :src="portadaActual" alt="Portada actual" class="h-36 w-full rounded-lg object-cover shadow" />
                                            <button type="button" @click="eliminarPortada" class="absolute -right-2 -top-2 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-white shadow hover:bg-red-600">
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div v-else-if="form.delete_imagen_portada" class="mb-3 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-600">
                                        Portada marcada para eliminar.
                                        <button type="button" @click="restaurarPortada" class="ml-1 font-semibold underline">Deshacer</button>
                                    </div>

                                    <!-- Vista previa nueva -->
                                    <div v-if="portadaPreview" class="mb-3">
                                        <p class="mb-1 text-xs text-gray-500">Nueva imagen:</p>
                                        <img :src="portadaPreview" alt="Vista previa portada" class="h-36 w-full rounded-lg object-cover shadow" />
                                    </div>

                                    <input type="file" accept="image/*" @change="handlePortadaUpload" class="w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-700 hover:file:bg-primary-100" />
                                    <p class="mt-1 text-xs text-gray-400">Horizontal recomendada · JPG/PNG/WebP · máx. 2MB</p>
                                    <p v-if="form.errors.imagen_portada" class="mt-1 text-sm text-red-600">{{ form.errors.imagen_portada }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="mt-8 flex items-center justify-end gap-3 border-t pt-6">
                            <Link :href="route('admin.tiendas.index')" class="rounded-lg border border-gray-300 bg-white px-6 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                Cancelar
                            </Link>
                            <button type="submit" :disabled="form.processing" class="rounded-lg bg-primary-600 px-6 py-2 text-sm font-semibold text-white hover:bg-primary-700 disabled:opacity-50">
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
import { ref, computed } from 'vue';

const props = defineProps({
    tienda: Object,
    categorias: Array,
    errors: Object,
});

const form = useForm({
    categoria_id:        props.tienda.categoria_id,
    nombre:              props.tienda.nombre,
    descripcion:         props.tienda.descripcion,
    telefono:            props.tienda.telefono,
    email:               props.tienda.email,
    direccion:           props.tienda.direccion,
    activa:              props.tienda.activa,
    visible:             props.tienda.visible,
    logo:                null,
    imagen_portada:      null,
    delete_logo:         false,
    delete_imagen_portada: false,
});

// Imágenes actuales (del servidor)
const logoActual    = computed(() => props.tienda.logo         ? `/storage/${props.tienda.logo}`         : null);
const portadaActual = computed(() => props.tienda.imagen_portada ? `/storage/${props.tienda.imagen_portada}` : null);

// Previews de nuevas imágenes
const logoPreview    = ref(null);
const portadaPreview = ref(null);

const handleLogoUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.logo = file;
        form.delete_logo = false;
        logoPreview.value = URL.createObjectURL(file);
    }
};

const handlePortadaUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.imagen_portada = file;
        form.delete_imagen_portada = false;
        portadaPreview.value = URL.createObjectURL(file);
    }
};

const eliminarLogo = () => {
    form.delete_logo = true;
    form.logo = null;
    logoPreview.value = null;
};

const restaurarLogo = () => {
    form.delete_logo = false;
};

const eliminarPortada = () => {
    form.delete_imagen_portada = true;
    form.imagen_portada = null;
    portadaPreview.value = null;
};

const restaurarPortada = () => {
    form.delete_imagen_portada = false;
};

const submit = () => {
    form.post(route('admin.tiendas.update', props.tienda.id), {
        forceFormData: true,
        onSuccess: () => {
            logoPreview.value = null;
            portadaPreview.value = null;
        },
    });
};
</script>

