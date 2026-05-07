<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/LayoutAutenticado.vue';

const props = defineProps({
    tienda:     { type: Object, required: true },
    categorias: { type: Array,  default: () => [] },
});

// ── Helper imagen ─────────────────────────────────────────────────────────
const imgUrl = (path) => {
    if (!path) return null;
    return path.startsWith('http') ? path : `/storage/${path}`;
};

// ── Modo imagen: 'file' | 'url' ──────────────────────────────────────────
const logoMode    = ref('file');
const portadaMode = ref('file');

// ── Form ─────────────────────────────────────────────────────────────────
const form = useForm({
    _method:              'POST',   // tunneling via _method=POST
    nombre:               props.tienda.nombre       ?? '',
    categoria_id:         props.tienda.categoria_id ?? '',
    descripcion:          props.tienda.descripcion  ?? '',
    telefono:             props.tienda.telefono     ?? '',
    email:                props.tienda.email        ?? '',
    direccion:            props.tienda.direccion    ?? '',
    // archivo
    logo:                 null,
    imagen_portada:       null,
    // url
    logo_url:             '',
    imagen_portada_url:   '',
    // borrar
    delete_logo:          false,
    delete_imagen_portada: false,
});

// ── Previsualización local de archivos seleccionados ──────────────────────
const logoPreview    = ref(imgUrl(props.tienda.logo));
const portadaPreview = ref(imgUrl(props.tienda.imagen_portada));

const onLogoChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    form.logo = file;
    form.delete_logo = false;
    logoPreview.value = URL.createObjectURL(file);
};

const onPortadaChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    form.imagen_portada = file;
    form.delete_imagen_portada = false;
    portadaPreview.value = URL.createObjectURL(file);
};

const deleteLogo = () => {
    form.logo = null;
    form.logo_url = '';
    form.delete_logo = true;
    logoPreview.value = null;
};

const deletePortada = () => {
    form.imagen_portada = null;
    form.imagen_portada_url = '';
    form.delete_imagen_portada = true;
    portadaPreview.value = null;
};

const onLogoUrlInput = () => {
    if (form.logo_url) {
        logoPreview.value = form.logo_url;
    }
};

const onPortadaUrlInput = () => {
    if (form.imagen_portada_url) {
        portadaPreview.value = form.imagen_portada_url;
    }
};

// ── Submit ────────────────────────────────────────────────────────────────
const submit = () => {
    form.post(route('solicitar.tienda'), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Editar mi tienda" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('owner.panel')"
                      class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-600 transition-colors">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </Link>
                <div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Editar mi tienda</h2>
                    <p class="text-xs text-gray-500">{{ tienda.nombre }}</p>
                </div>
            </div>
        </template>

        <!-- Toasts via ToastContainer global -->

        <div class="py-8">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-6">

                    <!-- ── Información básica ──────────────────────────────────── -->
                    <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-6 shadow-sm">
                        <h3 class="mb-5 text-base font-semibold text-gray-900 dark:text-white">Información básica</h3>

                        <div class="space-y-4">
                            <!-- Nombre -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre de la tienda <span class="text-red-500">*</span></label>
                                <input v-model="form.nombre" type="text" required minlength="2" maxlength="255"
                                       class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition" />
                                <p v-if="form.errors.nombre" class="mt-1 text-xs text-red-500">{{ form.errors.nombre }}</p>
                            </div>

                            <!-- Categoría -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Categoría <span class="text-red-500">*</span></label>
                                <select v-model="form.categoria_id" required
                                        class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition">
                                    <option value="" disabled>Selecciona una categoría</option>
                                    <option v-for="cat in categorias" :key="cat.id" :value="cat.id">
                                        {{ cat.nombre }}
                                    </option>
                                </select>
                                <p v-if="form.errors.categoria_id" class="mt-1 text-xs text-red-500">{{ form.errors.categoria_id }}</p>
                            </div>

                            <!-- Descripción -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Descripción</label>
                                <textarea v-model="form.descripcion" rows="4" maxlength="2000"
                                          class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition resize-none"></textarea>
                                <p class="mt-1 text-xs text-right text-gray-400">{{ (form.descripcion || '').length }}/2000</p>
                            </div>

                            <!-- Contacto -->
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Teléfono</label>
                                    <input v-model="form.telefono" type="tel"
                                           inputmode="tel" maxlength="20"
                                           v-only-phone
                                           class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                                    <input v-model="form.email" type="email" maxlength="255"
                                           class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition" />
                                </div>
                            </div>

                            <!-- Dirección -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dirección</label>
                                <input v-model="form.direccion" type="text" maxlength="500"
                                       class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition" />
                            </div>
                        </div>
                    </div>

                    <!-- ── Logo ────────────────────────────────────────────────── -->
                    <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-6 shadow-sm">
                        <h3 class="mb-5 text-base font-semibold text-gray-900 dark:text-white">Logo de la tienda</h3>

                        <!-- Modo toggle -->
                        <div class="mb-4 flex gap-1 rounded-lg bg-gray-100 dark:bg-gray-700 p-1 w-fit">
                            <button type="button" @click="logoMode = 'file'"
                                    :class="['px-3 py-1.5 rounded-md text-xs font-medium transition-colors',
                                        logoMode === 'file' ? 'bg-white dark:bg-gray-600 shadow text-gray-800 dark:text-white' : 'text-gray-500']">
                                Subir archivo
                            </button>
                            <button type="button" @click="logoMode = 'url'"
                                    :class="['px-3 py-1.5 rounded-md text-xs font-medium transition-colors',
                                        logoMode === 'url' ? 'bg-white dark:bg-gray-600 shadow text-gray-800 dark:text-white' : 'text-gray-500']">
                                URL de internet
                            </button>
                        </div>

                        <div class="flex items-start gap-6">
                            <!-- Vista previa -->
                            <div class="relative shrink-0">
                                <div class="h-24 w-24 rounded-2xl overflow-hidden bg-gray-100 dark:bg-gray-700 border-2 border-dashed border-gray-200 dark:border-gray-600 flex items-center justify-center">
                                    <img v-if="logoPreview" :src="logoPreview"
                                         class="h-full w-full object-cover" alt="Logo preview" />
                                    <svg v-else class="h-8 w-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <button v-if="logoPreview" type="button" @click="deleteLogo"
                                        class="absolute -top-2 -right-2 rounded-full bg-red-500 p-1 text-white hover:bg-red-600 transition-colors shadow">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            <div class="flex-1">
                                <!-- Modo archivo -->
                                <div v-if="logoMode === 'file'">
                                    <label class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-600 p-6 hover:border-primary-400 transition-colors">
                                        <svg class="mb-2 h-8 w-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                        </svg>
                                        <p class="text-sm text-gray-500">Arrastra o haz clic para subir</p>
                                        <p class="mt-1 text-xs text-gray-400">PNG, JPG, WEBP hasta 3MB</p>
                                        <input type="file" class="sr-only" accept="image/*" @change="onLogoChange" />
                                    </label>
                                </div>

                                <!-- Modo URL -->
                                <div v-if="logoMode === 'url'">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">URL de la imagen</label>
                                    <input v-model="form.logo_url" type="url" placeholder="https://ejemplo.com/imagen.jpg"
                                           @input="onLogoUrlInput"
                                           class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition" />
                                    <p class="mt-1.5 text-xs text-gray-400">La imagen se descargará y guardará en el servidor automáticamente.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── Imagen de portada ───────────────────────────────────── -->
                    <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-6 shadow-sm">
                        <h3 class="mb-5 text-base font-semibold text-gray-900 dark:text-white">Imagen de portada</h3>

                        <!-- Modo toggle -->
                        <div class="mb-4 flex gap-1 rounded-lg bg-gray-100 dark:bg-gray-700 p-1 w-fit">
                            <button type="button" @click="portadaMode = 'file'"
                                    :class="['px-3 py-1.5 rounded-md text-xs font-medium transition-colors',
                                        portadaMode === 'file' ? 'bg-white dark:bg-gray-600 shadow text-gray-800 dark:text-white' : 'text-gray-500']">
                                Subir archivo
                            </button>
                            <button type="button" @click="portadaMode = 'url'"
                                    :class="['px-3 py-1.5 rounded-md text-xs font-medium transition-colors',
                                        portadaMode === 'url' ? 'bg-white dark:bg-gray-600 shadow text-gray-800 dark:text-white' : 'text-gray-500']">
                                URL de internet
                            </button>
                        </div>

                        <!-- Vista previa portada -->
                        <div class="mb-4 relative rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-700 h-40 flex items-center justify-center border-2 border-dashed border-gray-200 dark:border-gray-600">
                            <img v-if="portadaPreview" :src="portadaPreview"
                                 class="absolute inset-0 h-full w-full object-cover" alt="Portada preview" />
                            <div v-else class="text-center text-gray-400">
                                <svg class="mx-auto h-10 w-10 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p class="text-sm">Sin imagen de portada</p>
                            </div>
                            <button v-if="portadaPreview" type="button" @click="deletePortada"
                                    class="absolute top-2 right-2 rounded-full bg-red-500 p-1.5 text-white hover:bg-red-600 transition-colors shadow">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Modo archivo -->
                        <div v-if="portadaMode === 'file'">
                            <label class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-600 p-6 hover:border-primary-400 transition-colors">
                                <svg class="mb-2 h-8 w-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                <p class="text-sm text-gray-500">Arrastra o haz clic para subir</p>
                                <p class="mt-1 text-xs text-gray-400">PNG, JPG, WEBP hasta 3MB – Recomendado 1200×300px</p>
                                <input type="file" class="sr-only" accept="image/*" @change="onPortadaChange" />
                            </label>
                        </div>

                        <!-- Modo URL -->
                        <div v-if="portadaMode === 'url'">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">URL de la imagen</label>
                            <input v-model="form.imagen_portada_url" type="url" placeholder="https://ejemplo.com/portada.jpg"
                                   @input="onPortadaUrlInput"
                                   class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition" />
                            <p class="mt-1.5 text-xs text-gray-400">La imagen se descargará y guardará en el servidor automáticamente.</p>
                        </div>
                    </div>

                    <!-- ── Acciones ─────────────────────────────────────────────── -->
                    <div class="flex items-center justify-between">
                        <Link :href="route('owner.panel')"
                              class="rounded-xl border border-gray-200 dark:border-gray-600 px-5 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            Cancelar
                        </Link>
                        <button type="submit" :disabled="form.processing"
                                class="flex items-center gap-2 rounded-xl bg-primary-500 px-6 py-2.5 text-sm font-semibold text-white hover:bg-primary-600 disabled:opacity-60 transition-colors shadow-sm">
                            <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            {{ form.processing ? 'Guardando...' : 'Guardar cambios' }}
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
