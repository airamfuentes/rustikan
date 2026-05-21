<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Editar Tienda</h2>
                <Link :href="route('admin.tiendas.index')" class="rounded-lg bg-gray-600 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                    Volver
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6">
                        <div class="grid gap-8 lg:grid-cols-2">

                            <!-- Columna Izquierda: Datos -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Datos de la Tienda</h3>

                                <!-- Nombre -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre *</label>
                                    <input v-model="form.nombre" type="text" required minlength="2" maxlength="255" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500" />
                                    <p v-if="form.errors.nombre" class="mt-1 text-sm text-red-600">{{ form.errors.nombre }}</p>
                                </div>

                                <!-- Categoría -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Categoría *</label>
                                    <select v-model="form.categoria_id" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                        <option value="">Selecciona una categoría</option>
                                        <option v-for="categoria in categorias" :key="categoria.id" :value="categoria.id">
                                            {{ categoria.nombre }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.categoria_id" class="mt-1 text-sm text-red-600">{{ form.errors.categoria_id }}</p>
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
                                    <input v-model="form.telefono" type="tel" inputmode="tel" maxlength="20" v-only-phone class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500" />
                                    <p v-if="form.errors.telefono" class="mt-1 text-sm text-red-600">{{ form.errors.telefono }}</p>
                                </div>

                                <!-- Email -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                                    <input v-model="form.email" type="email" maxlength="255" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500" />
                                    <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                                </div>

                                <!-- Dirección -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Dirección</label>
                                    <input v-model="form.direccion" type="text" maxlength="500" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500" />
                                    <p v-if="form.errors.direccion" class="mt-1 text-sm text-red-600">{{ form.errors.direccion }}</p>
                                </div>

                                <!-- Coordenadas -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Coordenadas (para el mapa)</label>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <input
                                                v-model="form.latitud"
                                                type="number"
                                                step="0.0000001"
                                                min="-90"
                                                max="90"
                                                inputmode="decimal"
                                                v-only-signed-decimal
                                                placeholder="Latitud"
                                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                            />
                                            <p v-if="form.errors.latitud" class="mt-1 text-sm text-red-600">{{ form.errors.latitud }}</p>
                                        </div>
                                        <div>
                                            <input
                                                v-model="form.longitud"
                                                type="number"
                                                step="0.0000001"
                                                min="-180"
                                                max="180"
                                                inputmode="decimal"
                                                v-only-signed-decimal
                                                placeholder="Longitud"
                                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                            />
                                            <p v-if="form.errors.longitud" class="mt-1 text-sm text-red-600">{{ form.errors.longitud }}</p>
                                        </div>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-400">Puedes obtenerlas desde Google Maps (clic derecho ? coordenadas)</p>
                                </div>

                                <!-- Propietario -->
                                <div class="rounded-lg border border-primary-200 dark:border-primary-800 bg-primary-50 dark:bg-primary-900/10 p-4">
                                    <h3 class="mb-3 flex items-center gap-2 text-sm font-semibold text-primary-900 dark:text-primary-300">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        Propietario
                                    </h3>

                                    <!-- Usuario seleccionado -->
                                    <div v-if="usuarioSeleccionado" class="mb-3 flex items-center justify-between rounded-xl bg-white dark:bg-gray-700 border border-primary-200 dark:border-primary-700 px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <img v-if="usuarioSeleccionado.avatar" :src="'/storage/' + usuarioSeleccionado.avatar" class="h-9 w-9 rounded-full object-cover" alt="" />
                                            <div v-else class="flex h-9 w-9 items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300 font-bold text-sm">
                                                {{ usuarioSeleccionado.name.charAt(0).toUpperCase() }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ usuarioSeleccionado.name }}</p>
                                                <p class="text-xs text-gray-400">{{ usuarioSeleccionado.email }}</p>
                                            </div>
                                        </div>
                                        <button type="button" @click="form.user_id = null"
                                                class="rounded-lg p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-900/20 transition-colors">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <p v-else class="mb-3 text-xs text-gray-500 dark:text-gray-400 italic">Sin propietario asignado</p>

                                    <!-- Buscador de usuarios -->
                                    <div class="relative">
                                        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/>
                                        </svg>
                                        <input
                                            v-model="busquedaUsuario"
                                            type="text"
                                            placeholder="Buscar usuario por nombre o email..."
                                            class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 pl-9 pr-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-400"
                                        />
                                    </div>

                                    <!-- Lista de usuarios filtrados -->
                                    <div v-if="busquedaUsuario.trim()" class="mt-2 max-h-52 overflow-y-auto rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 divide-y divide-gray-100 dark:divide-gray-600">
                                        <div v-if="usuariosFiltrados.length === 0" class="px-4 py-3 text-sm text-gray-400 text-center">
                                            Sin resultados
                                        </div>
                                        <button
                                            v-for="u in usuariosFiltrados"
                                            :key="u.id"
                                            type="button"
                                            @click="seleccionarUsuario(u)"
                                            :class="['w-full flex items-center gap-3 px-4 py-2.5 text-left hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors',
                                                form.user_id === u.id ? 'bg-primary-50 dark:bg-primary-900/20' : '']"
                                        >
                                            <img v-if="u.avatar" :src="'/storage/' + u.avatar" class="h-7 w-7 rounded-full object-cover flex-shrink-0" alt="" />
                                            <div v-else class="flex h-7 w-7 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-600 text-gray-500 text-xs font-bold flex-shrink-0">
                                                {{ u.name.charAt(0).toUpperCase() }}
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ u.name }}</p>
                                                <p class="text-xs text-gray-400 truncate">{{ u.email }}</p>
                                            </div>
                                            <span :class="['text-xs px-1.5 py-0.5 rounded font-medium flex-shrink-0',
                                                u.role === 'admin' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' :
                                                u.role === 'owner' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' :
                                                'bg-gray-100 text-gray-600 dark:bg-gray-600 dark:text-gray-300']">
                                                {{ u.role }}
                                            </span>
                                            <svg v-if="form.user_id === u.id" class="h-4 w-4 text-primary-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Estados -->
                                <div class="grid grid-cols-2 gap-4">                                    <label class="flex items-center gap-2">
                                        <input v-model="form.activa" type="checkbox" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500" />
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Tienda Activa</span>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input v-model="form.visible" type="checkbox" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500" />
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Visible al Público</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Columna Derecha: Imágenes -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Imágenes</h3>

                                <!-- Logo -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Logo <span class="text-xs text-gray-400 dark:text-gray-500">(400×400px)</span></label>

                                    <!-- Imagen actual -->
                                    <div v-if="logoActual && !form.delete_logo && !logoPreview" class="mb-3">
                                        <p class="mb-1 text-xs text-gray-500">Imagen actual:</p>
                                        <div class="relative inline-block">
                                            <img :src="logoActual" alt="Logo actual" class="h-28 w-28 rounded-full object-cover shadow ring-2 ring-gray-200" />
                                            <button type="button" @click="eliminarLogo" class="absolute -right-1 -top-1 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-white shadow hover:bg-red-600">
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div v-else-if="form.delete_logo && !logoPreview" class="mb-3 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-600">
                                        Logo marcado para eliminar.
                                        <button type="button" @click="restaurarLogo" class="ml-1 font-semibold underline">Deshacer</button>
                                    </div>

                                    <!-- Vista previa recortada -->
                                    <div v-if="logoPreview" class="mb-3">
                                        <p class="mb-1 text-xs text-gray-500">Nueva imagen (recortada):</p>
                                        <div class="relative inline-block">
                                            <img :src="logoPreview" alt="Vista previa logo" class="h-28 w-28 rounded-full object-cover shadow ring-2 ring-primary-300" />
                                            <button type="button" @click="quitarLogo" class="absolute -right-1 -top-1 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-white shadow hover:bg-red-600">
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                            </button>
                                        </div>
                                    </div>

                                    <input ref="logoInput" type="file" accept="image/*" @change="onLogoSelected" class="w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-700 hover:file:bg-primary-100" />
                                    <p class="mt-1 text-xs text-gray-400">Se recortará en forma circular · JPG/PNG/WebP · máx. 2MB</p>
                                    <div class="mt-2">
                                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">O pega una URL de imagen</label>
                                        <input v-model="form.logo_url" type="url" placeholder="https://..." class="w-full rounded-lg border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm outline-none focus:border-primary-400 focus:ring-2 focus:ring-primary-200" />
                                    </div>
                                    <p v-if="form.errors.logo" class="mt-1 text-sm text-red-600">{{ form.errors.logo }}</p>
                                </div>

                                <!-- Imagen de Portada -->
                                <div>
                                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Imagen de Portada <span class="text-xs text-gray-400 dark:text-gray-500">(1200×400px)</span></label>

                                    <!-- Imagen actual -->
                                    <div v-if="portadaActual && !form.delete_imagen_portada && !portadaPreview" class="mb-3">
                                        <p class="mb-1 text-xs text-gray-500">Imagen actual:</p>
                                        <div class="relative">
                                            <img :src="portadaActual" alt="Portada actual" class="h-36 w-full rounded-lg object-cover shadow ring-2 ring-gray-200" />
                                            <button type="button" @click="eliminarPortada" class="absolute -right-2 -top-2 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-white shadow hover:bg-red-600">
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div v-else-if="form.delete_imagen_portada && !portadaPreview" class="mb-3 rounded-lg bg-red-50 px-3 py-2 text-xs text-red-600">
                                        Portada marcada para eliminar.
                                        <button type="button" @click="restaurarPortada" class="ml-1 font-semibold underline">Deshacer</button>
                                    </div>

                                    <!-- Vista previa recortada -->
                                    <div v-if="portadaPreview" class="mb-3">
                                        <p class="mb-1 text-xs text-gray-500">Nueva imagen (recortada):</p>
                                        <div class="relative">
                                            <img :src="portadaPreview" alt="Vista previa portada" class="h-36 w-full rounded-lg object-cover shadow ring-2 ring-primary-300" />
                                            <button type="button" @click="quitarPortada" class="absolute -right-2 -top-2 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-white shadow hover:bg-red-600">
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                            </button>
                                        </div>
                                    </div>

                                    <input ref="portadaInput" type="file" accept="image/*" @change="onPortadaSelected" class="w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-700 hover:file:bg-primary-100" />
                                    <p class="mt-1 text-xs text-gray-400">Se recortará en formato panorámico · JPG/PNG/WebP · máx. 2MB</p>
                                    <div class="mt-2">
                                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">O pega una URL de imagen</label>
                                        <input v-model="form.imagen_portada_url" type="url" placeholder="https://..." class="w-full rounded-lg border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white px-3 py-2 text-sm outline-none focus:border-primary-400 focus:ring-2 focus:ring-primary-200" />
                                    </div>
                                    <p v-if="form.errors.imagen_portada" class="mt-1 text-sm text-red-600">{{ form.errors.imagen_portada }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="mt-8 flex items-center justify-end gap-3 border-t pt-6">
                            <Link :href="route('admin.tiendas.index')" class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-6 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">
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
import { ref, computed } from 'vue';
import { useFileUpload } from '@/Composables/useFileUpload';

const { validate: validateFile } = useFileUpload();
const props = defineProps({
    tienda: Object,
    categorias: Array,
    usuarios: { type: Array, default: () => [] },
    errors: Object,
});

const form = useForm({
    user_id:             props.tienda.user_id ?? null,
    categoria_id:        props.tienda.categoria_id,
    nombre:              props.tienda.nombre,
    descripcion:         props.tienda.descripcion,
    telefono:            props.tienda.telefono,
    email:               props.tienda.email,
    direccion:           props.tienda.direccion,
    latitud:             props.tienda.latitud,
    longitud:            props.tienda.longitud,
    activa:              props.tienda.activa,
    visible:             props.tienda.visible,
    logo:                null,
    imagen_portada:      null,
    logo_url:            '',
    imagen_portada_url:  '',
    delete_logo:         false,
    delete_imagen_portada: false,
});

// Imágenes actuales (del servidor)
const imgUrl = (p) => p ? (p.startsWith('http') ? p : `/storage/${p}`) : null;
const logoActual    = computed(() => imgUrl(props.tienda.logo));
const portadaActual = computed(() => imgUrl(props.tienda.imagen_portada));

const busquedaUsuario = ref('');

const usuariosFiltrados = computed(() => {
    const q = busquedaUsuario.value.trim().toLowerCase();
    if (!q) return [];
    return props.usuarios
        .filter(u => u.name.toLowerCase().includes(q) || u.email.toLowerCase().includes(q))
        .slice(0, 8);
});

const usuarioSeleccionado = computed(() =>
    form.user_id ? props.usuarios.find(u => u.id === form.user_id) ?? null : null
);

const seleccionarUsuario = (u) => {
    form.user_id = u.id;
    busquedaUsuario.value = '';
};

// Previews de nuevas imágenes recortadas
const logoPreview    = ref(null);
const portadaPreview = ref(null);

// File input refs
const logoInput    = ref(null);
const portadaInput = ref(null);

// Cropper state
const showLogoCropper    = ref(false);
const showPortadaCropper = ref(false);
const cropperSrc         = ref(null);

// -- Logo handlers --------------------------------------------------------
const onLogoSelected = (event) => {
    const file = event.target.files[0];
    if (!file) return;
    if (!validateFile(file, { accept: ['jpg', 'jpeg', 'png', 'webp', 'gif'] })) {
        event.target.value = '';
        return;
    }
    cropperSrc.value = URL.createObjectURL(file);
    showLogoCropper.value = true;
};

const onLogoCropped = ({ file, preview }) => {
    form.logo = file;
    form.delete_logo = false;
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

const eliminarLogo = () => {
    form.delete_logo = true;
    form.logo = null;
    if (logoPreview.value) { URL.revokeObjectURL(logoPreview.value); logoPreview.value = null; }
};

const restaurarLogo = () => { form.delete_logo = false; };

// -- Portada handlers -----------------------------------------------------
const onPortadaSelected = (event) => {
    const file = event.target.files[0];
    if (!file) return;
    if (!validateFile(file, { accept: ['jpg', 'jpeg', 'png', 'webp', 'gif'] })) {
        event.target.value = '';
        return;
    }
    cropperSrc.value = URL.createObjectURL(file);
    showPortadaCropper.value = true;
};

const onPortadaCropped = ({ file, preview }) => {
    form.imagen_portada = file;
    form.delete_imagen_portada = false;
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

const eliminarPortada = () => {
    form.delete_imagen_portada = true;
    form.imagen_portada = null;
    if (portadaPreview.value) { URL.revokeObjectURL(portadaPreview.value); portadaPreview.value = null; }
};

const restaurarPortada = () => { form.delete_imagen_portada = false; };

// -- Submit ---------------------------------------------------------------
const submit = () => {
    form
        .transform((data) => ({ ...data, _method: 'put' }))
        .post(route('admin.tiendas.update', props.tienda.id), {
            forceFormData: true,
            onSuccess: () => {
                if (logoPreview.value) { URL.revokeObjectURL(logoPreview.value); logoPreview.value = null; }
                if (portadaPreview.value) { URL.revokeObjectURL(portadaPreview.value); portadaPreview.value = null; }
            },
        });
};
</script>

