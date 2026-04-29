<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Nueva Tienda</h2>
                <Link :href="route('admin.tiendas.index')" class="rounded-lg bg-gray-600 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                    Volver
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6">
                        <div class="grid gap-6 lg:grid-cols-2">
                            <!-- Columna Izquierda: Datos Básicos -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Datos Básicos</h3>

                                <!-- Nombre -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre de la Tienda *</label>
                                    <input v-model="form.nombre" type="text" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500" />
                                    <p v-if="form.errors.nombre" class="mt-1 text-sm text-red-600">{{ form.errors.nombre }}</p>
                                </div>

                                <!-- Categoría -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Categoría *</label>
                                    <select v-model="form.categoria_id" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                        <option value="">Selecciona una categoría</option>
                                        <option v-for="categoria in categorias" :key="categoria.id" :value="categoria.id">
                                            {{ categoria.icono }} {{ categoria.nombre }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.categoria_id" class="mt-1 text-sm text-red-600">{{ form.errors.categoria_id }}</p>
                                </div>

                                <!-- Propietario -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Propietario *</label>
                                    <select v-model="form.user_id" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                        <option value="">Selecciona un usuario</option>
                                        <option v-for="usuario in usuarios" :key="usuario.id" :value="usuario.id">
                                            {{ usuario.name }} ({{ usuario.email }})
                                        </option>
                                    </select>
                                    <p v-if="form.errors.user_id" class="mt-1 text-sm text-red-600">{{ form.errors.user_id }}</p>
                                </div>

                                <!-- Descripción -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Descripción</label>
                                    <textarea v-model="form.descripcion" rows="4" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
                                    <p v-if="form.errors.descripcion" class="mt-1 text-sm text-red-600">{{ form.errors.descripcion }}</p>
                                </div>

                                <!-- Teléfono -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Teléfono</label>
                                    <input v-model="form.telefono" type="tel" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500" />
                                    <p v-if="form.errors.telefono" class="mt-1 text-sm text-red-600">{{ form.errors.telefono }}</p>
                                </div>

                                <!-- Email -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                                    <input v-model="form.email" type="email" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500" />
                                    <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                                </div>

                                <!-- Dirección -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Dirección</label>
                                    <textarea v-model="form.direccion" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
                                    <p v-if="form.errors.direccion" class="mt-1 text-sm text-red-600">{{ form.errors.direccion }}</p>
                                </div>

                                <!-- Coordenadas -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Coordenadas (para el mapa)</label>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <input v-model="form.latitud" type="number" step="0.0000001" placeholder="Latitud (ej. 29.0469)" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500" />
                                            <p v-if="form.errors.latitud" class="mt-1 text-sm text-red-600">{{ form.errors.latitud }}</p>
                                        </div>
                                        <div>
                                            <input v-model="form.longitud" type="number" step="0.0000001" placeholder="Longitud (ej. -13.6328)" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500" />
                                            <p v-if="form.errors.longitud" class="mt-1 text-sm text-red-600">{{ form.errors.longitud }}</p>
                                        </div>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-400">Puedes obtenerlas desde Google Maps (clic derecho ? coordenadas)</p>
                                </div>
                            </div>

                            <!-- Columna Derecha: Imágenes -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Imágenes</h3>

                                <!-- Logo -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Logo <span class="text-xs text-gray-400 dark:text-gray-500">(400×400px)</span></label>

                                    <div v-if="logoPreview" class="mb-3">
                                        <p class="mb-1 text-xs text-gray-500">Imagen recortada:</p>
                                        <div class="relative inline-block">
                                            <img :src="logoPreview" alt="Vista previa logo" class="h-28 w-28 rounded-full object-cover shadow ring-2 ring-primary-300" />
                                            <button type="button" @click="quitarLogo" class="absolute -right-1 -top-1 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-white shadow hover:bg-red-600">
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                            </button>
                                        </div>
                                    </div>

                                    <input ref="logoInput" type="file" accept="image/*" @change="onLogoSelected" class="w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-700 hover:file:bg-primary-100" />
                                    <p class="mt-1 text-xs text-gray-400">Se recortará en forma circular · JPG/PNG/WebP · máx. 2MB</p>
                                    <p v-if="form.errors.logo" class="mt-1 text-sm text-red-600">{{ form.errors.logo }}</p>
                                </div>

                                <!-- Imagen de Portada -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Imagen de Portada <span class="text-xs text-gray-400 dark:text-gray-500">(1200×400px)</span></label>

                                    <div v-if="portadaPreview" class="mb-3">
                                        <p class="mb-1 text-xs text-gray-500">Imagen recortada:</p>
                                        <div class="relative">
                                            <img :src="portadaPreview" alt="Vista previa portada" class="h-36 w-full rounded-lg object-cover shadow ring-2 ring-primary-300" />
                                            <button type="button" @click="quitarPortada" class="absolute -right-2 -top-2 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-white shadow hover:bg-red-600">
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                            </button>
                                        </div>
                                    </div>

                                    <input ref="portadaInput" type="file" accept="image/*" @change="onPortadaSelected" class="w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-700 hover:file:bg-primary-100" />
                                    <p class="mt-1 text-xs text-gray-400">Se recortará en formato panorámico · JPG/PNG/WebP · máx. 2MB</p>
                                    <p v-if="form.errors.imagen_portada" class="mt-1 text-sm text-red-600">{{ form.errors.imagen_portada }}</p>
                                </div>

                                <!-- Información adicional -->
                                <div class="rounded-lg bg-blue-50 dark:bg-blue-900/10 p-4">
                                    <h4 class="mb-2 text-sm font-semibold text-blue-900 dark:text-blue-300">Información</h4>
                                    <ul class="space-y-1 text-xs text-blue-700 dark:text-blue-300">
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
                            <Link :href="route('admin.tiendas.index')" class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-6 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">
                                Cancelar
                            </Link>
                            <button type="submit" :disabled="form.processing" class="rounded-lg bg-primary-600 px-6 py-2 text-sm font-semibold text-white hover:bg-primary-700 disabled:opacity-50">
                                {{ form.processing ? 'Guardando...' : 'Crear Tienda' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Cropper modals -->
        <ImageCropper
            :show="showLogoCropper"
            :image-src="cropperSrc"
            :aspect-ratio="1"
            :output-width="400"
            :output-height="400"
            :circular="true"
            title="Recortar Logo (circular)"
            @cropped="onLogoCropped"
            @cancel="cancelLogoCrop"
        />

        <ImageCropper
            :show="showPortadaCropper"
            :image-src="cropperSrc"
            :aspect-ratio="3"
            :output-width="1200"
            :output-height="400"
            title="Recortar Portada (panorámica)"
            @cropped="onPortadaCropped"
            @cancel="cancelPortadaCrop"
        />
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/LayoutAutenticado.vue';
import ImageCropper from '@/Components/ImageCropper.vue';
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
    latitud: null,
    longitud: null,
    logo: null,
    imagen_portada: null,
});

const logoPreview    = ref(null);
const portadaPreview = ref(null);
const logoInput      = ref(null);
const portadaInput   = ref(null);

// Cropper state
const showLogoCropper    = ref(false);
const showPortadaCropper = ref(false);
const cropperSrc         = ref(null);

// -- Logo handlers --------------------------------------------------------
const onLogoSelected = (event) => {
    const file = event.target.files[0];
    if (!file) return;
    cropperSrc.value = URL.createObjectURL(file);
    showLogoCropper.value = true;
};

const onLogoCropped = ({ file, preview }) => {
    form.logo = file;
    logoPreview.value = preview;
    showLogoCropper.value = false;
    if (cropperSrc.value) { URL.revokeObjectURL(cropperSrc.value); cropperSrc.value = null; }
};

const cancelLogoCrop = () => {
    showLogoCropper.value = false;
    if (cropperSrc.value) { URL.revokeObjectURL(cropperSrc.value); cropperSrc.value = null; }
    if (logoInput.value) logoInput.value.value = '';
};

const quitarLogo = () => {
    form.logo = null;
    if (logoPreview.value) { URL.revokeObjectURL(logoPreview.value); logoPreview.value = null; }
    if (logoInput.value) logoInput.value.value = '';
};

// -- Portada handlers -----------------------------------------------------
const onPortadaSelected = (event) => {
    const file = event.target.files[0];
    if (!file) return;
    cropperSrc.value = URL.createObjectURL(file);
    showPortadaCropper.value = true;
};

const onPortadaCropped = ({ file, preview }) => {
    form.imagen_portada = file;
    portadaPreview.value = preview;
    showPortadaCropper.value = false;
    if (cropperSrc.value) { URL.revokeObjectURL(cropperSrc.value); cropperSrc.value = null; }
};

const cancelPortadaCrop = () => {
    showPortadaCropper.value = false;
    if (cropperSrc.value) { URL.revokeObjectURL(cropperSrc.value); cropperSrc.value = null; }
    if (portadaInput.value) portadaInput.value.value = '';
};

const quitarPortada = () => {
    form.imagen_portada = null;
    if (portadaPreview.value) { URL.revokeObjectURL(portadaPreview.value); portadaPreview.value = null; }
    if (portadaInput.value) portadaInput.value.value = '';
};

// -- Submit ---------------------------------------------------------------
const submit = () => {
    form.post(route('admin.tiendas.store'), {
        forceFormData: true,
        onSuccess: () => {
            if (logoPreview.value) { URL.revokeObjectURL(logoPreview.value); logoPreview.value = null; }
            if (portadaPreview.value) { URL.revokeObjectURL(portadaPreview.value); portadaPreview.value = null; }
        },
    });
};
</script>
