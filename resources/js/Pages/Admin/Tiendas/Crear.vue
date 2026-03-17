<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Nueva Tienda</h2>
                <Link :href="route('admin.tiendas.index')" class="rounded-lg bg-gray-600 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                    Volver
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6">
                        <div class="grid gap-6 lg:grid-cols-2">
                            <!-- Columna Izquierda: Datos Básicos -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-semibold text-gray-900">Datos Básicos</h3>
                                
                                <!-- Nombre -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700">Nombre de la Tienda *</label>
                                    <input
                                        v-model="form.nombre"
                                        type="text"
                                        required
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    />
                                    <p v-if="form.errors.nombre" class="mt-1 text-sm text-red-600">{{ form.errors.nombre }}</p>
                                </div>

                                <!-- Categoría -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700">Categoría *</label>
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
                                    <p v-if="form.errors.categoria_id" class="mt-1 text-sm text-red-600">{{ form.errors.categoria_id }}</p>
                                </div>

                                <!-- Propietario -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700">Propietario *</label>
                                    <select
                                        v-model="form.user_id"
                                        required
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    >
                                        <option value="">Selecciona un usuario</option>
                                        <option v-for="usuario in usuarios" :key="usuario.id" :value="usuario.id">
                                            {{ usuario.name }} ({{ usuario.email }})
                                        </option>
                                    </select>
                                    <p v-if="form.errors.user_id" class="mt-1 text-sm text-red-600">{{ form.errors.user_id }}</p>
                                </div>

                                <!-- Descripción -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700">Descripción</label>
                                    <textarea
                                        v-model="form.descripcion"
                                        rows="4"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    ></textarea>
                                    <p v-if="form.errors.descripcion" class="mt-1 text-sm text-red-600">{{ form.errors.descripcion }}</p>
                                </div>

                                <!-- Teléfono -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700">Teléfono</label>
                                    <input
                                        v-model="form.telefono"
                                        type="tel"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    />
                                    <p v-if="form.errors.telefono" class="mt-1 text-sm text-red-600">{{ form.errors.telefono }}</p>
                                </div>

                                <!-- Email -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700">Email</label>
                                    <input
                                        v-model="form.email"
                                        type="email"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    />
                                    <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                                </div>

                                <!-- Dirección -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700">Dirección</label>
                                    <textarea
                                        v-model="form.direccion"
                                        rows="2"
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    ></textarea>
                                    <p v-if="form.errors.direccion" class="mt-1 text-sm text-red-600">{{ form.errors.direccion }}</p>
                                </div>
                            </div>

                            <!-- Columna Derecha: Imágenes -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-semibold text-gray-900">Imágenes</h3>
                                
                                <!-- Logo -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700">Logo</label>
                                    <div class="space-y-2">
                                        <input
                                            type="file"
                                            accept="image/*"
                                            @change="handleLogoUpload"
                                            class="w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-700 hover:file:bg-primary-100"
                                        />
                                        <p class="text-xs text-gray-500">Imagen cuadrada recomendada (ej. 200x200px)</p>
                                        <!-- Vista previa del logo -->
                                        <div v-if="logoPreview" class="mt-2">
                                            <img :src="logoPreview" alt="Vista previa logo" class="h-32 w-32 rounded-lg object-cover shadow">
                                        </div>
                                    </div>
                                    <p v-if="form.errors.logo" class="mt-1 text-sm text-red-600">{{ form.errors.logo }}</p>
                                </div>

                                <!-- Imagen de Portada -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700">Imagen de Portada</label>
                                    <div class="space-y-2">
                                        <input
                                            type="file"
                                            accept="image/*"
                                            @change="handlePortadaUpload"
                                            class="w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-700 hover:file:bg-primary-100"
                                        />
                                        <p class="text-xs text-gray-500">Imagen horizontal recomendada (ej. 1200x400px)</p>
                                        <!-- Vista previa de la portada -->
                                        <div v-if="portadaPreview" class="mt-2">
                                            <img :src="portadaPreview" alt="Vista previa portada" class="h-40 w-full rounded-lg object-cover shadow">
                                        </div>
                                    </div>
                                    <p v-if="form.errors.imagen_portada" class="mt-1 text-sm text-red-600">{{ form.errors.imagen_portada }}</p>
                                </div>

                                <!-- Información adicional -->
                                <div class="rounded-lg bg-blue-50 p-4">
                                    <h4 class="mb-2 text-sm font-semibold text-blue-900">Información</h4>
                                    <ul class="space-y-1 text-xs text-blue-700">
                                        <li>• El logo aparecerá en las tarjetas de tienda</li>
                                        <li>• La portada se mostrará en la página de detalle</li>
                                        <li>• Formatos aceptados: JPG, PNG, WebP</li>
                                        <li>• Tamaño máximo: 2MB por imagen</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="mt-8 flex items-center justify-end gap-3 border-t pt-6">
                            <Link
                                :href="route('admin.tiendas.index')"
                                class="rounded-lg border border-gray-300 bg-white px-6 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                            >
                                Cancelar
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-lg bg-primary-600 px-6 py-2 text-sm font-semibold text-white hover:bg-primary-700 disabled:opacity-50"
                            >
                                {{ form.processing ? 'Guardando...' : 'Crear Tienda' }}
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
import { ref } from 'vue';

const props = defineProps({
    categorias: Array,
    usuarios: Array,
});

const form = useForm({
    nombre: '',
    categoria_id: '',
    user_id: '',
    descripcion: '',
    telefono: '',
    email: '',
    direccion: '',
    logo: null,
    imagen_portada: null,
});

const logoPreview = ref(null);
const portadaPreview = ref(null);

const handleLogoUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.logo = file;
        logoPreview.value = URL.createObjectURL(file);
    }
};

const handlePortadaUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.imagen_portada = file;
        portadaPreview.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    form.post(route('admin.tiendas.store'), {
        forceFormData: true,
    });
};
</script>
