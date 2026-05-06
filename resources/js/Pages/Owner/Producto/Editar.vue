<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/LayoutAutenticado.vue';
import Toast from '@/Components/Toast.vue';
import { Star } from 'lucide-vue-next';

const props = defineProps({
    producto:   { type: Object, required: true },
    tienda:     { type: Object, required: true },
});

const page = usePage();

// ── Toasts ────────────────────────────────────────────────────────────────
const toasts = ref([]);
const addToast = (type, title, msg) => {
    const id = Date.now();
    toasts.value.push({ id, type, title, message: msg });
    setTimeout(() => { toasts.value = toasts.value.filter(t => t.id !== id); }, 4000);
};
// Flash handled by LayoutAutenticado — no duplicate watcher needed

const imgUrl = (path) => {
    if (!path) return null;
    return path.startsWith('http') ? path : `/storage/${path}`;
};

// ── Modo imagen ────────────────────────────────────────────────────────────
const imagenMode = ref('file');

const form = useForm({
    _method:       'POST',
    nombre:        props.producto.nombre        ?? '',
    descripcion:   props.producto.descripcion   ?? '',
    precio:        props.producto.precio        ?? '',
    precio_oferta: props.producto.precio_oferta ?? '',
    oferta_activa: props.producto.oferta_activa  ?? false,
    unidad:        props.producto.unidad        ?? 'kg',
    stock:         props.producto.stock         ?? 0,
    stock_minimo:  props.producto.stock_minimo  ?? 3,
    disponible:    props.producto.disponible    ?? true,
    destacado:     props.producto.destacado     ?? false,
    imagen:        null,
    imagen_url:    '',
    delete_imagen: false,
});

const imagenPreview = ref(imgUrl(props.producto.imagen));

const onImagenChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    form.imagen = file;
    form.delete_imagen = false;
    imagenPreview.value = URL.createObjectURL(file);
};

const deleteImagen = () => {
    form.imagen = null;
    form.imagen_url = '';
    form.delete_imagen = true;
    imagenPreview.value = null;
};

const onUrlInput = () => {
    if (form.imagen_url) imagenPreview.value = form.imagen_url;
};

const submit = () => {
    form.post(route('solicitar.producto.editar', props.producto.id), {
        forceFormData: true,
    });
};

// Precio oferta válido → habilita el toggle de oferta activa en tiempo real
const tieneOferta = computed(() => {
    const v = parseFloat(form.precio_oferta);
    return !isNaN(v) && v > 0;
});

// Si se borra el precio oferta, desactivar la oferta automáticamente
watch(tieneOferta, (val) => {
    if (!val) form.oferta_activa = false;
});

const toggleOfertaActiva = () => {
    if (!tieneOferta.value) return;
    form.oferta_activa = !form.oferta_activa;
};
</script>

<template>
    <Head :title="`Editar producto – ${producto.nombre}`" />

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
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">Editar producto</h2>
                    <p class="text-xs text-gray-500">{{ tienda.nombre }}</p>
                </div>
            </div>
        </template>

        <!-- Toasts -->
        <div class="pointer-events-none fixed top-20 right-4 z-[9999] flex flex-col items-end gap-3 max-w-sm w-full">
            <Toast v-for="(t, index) in toasts" :key="t.id" :type="t.type" :title="t.title" :message="t.message" :active="index === 0" @close="toasts = toasts.filter(x => x.id !== t.id)" />
        </div>

        <div class="py-8">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-6">

                    <!-- ── Info básica ──────────────────────────────────────── -->
                    <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-6 shadow-sm">
                        <h3 class="mb-5 text-base font-semibold text-gray-900 dark:text-white">Información del producto</h3>

                        <div class="space-y-4">
                            <!-- Nombre -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre <span class="text-red-500">*</span></label>
                                <input v-model="form.nombre" type="text" required minlength="2" maxlength="255"
                                       class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition" />
                                <p v-if="form.errors.nombre" class="mt-1 text-xs text-red-500">{{ form.errors.nombre }}</p>
                            </div>

                            <!-- Descripción -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Descripción</label>
                                <textarea v-model="form.descripcion" rows="3"
                                          class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition resize-none"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- ── Precio y stock ──────────────────────────────────── -->
                    <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-6 shadow-sm">
                        <h3 class="mb-5 text-base font-semibold text-gray-900 dark:text-white">Precio y stock</h3>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Precio (€) <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 text-sm">€</span>
                                    <input v-model="form.precio" type="number" min="0" max="99999.99" step="0.01" required inputmode="decimal" v-only-decimal
                                           class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 pl-7 pr-4 py-2.5 text-sm text-gray-900 dark:text-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition" />
                                </div>
                                <p v-if="form.errors.precio" class="mt-1 text-xs text-red-500">{{ form.errors.precio }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Precio oferta (€)</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 text-sm">€</span>
                                    <input v-model="form.precio_oferta" type="number" min="0" max="99999.99" step="0.01" inputmode="decimal" v-only-decimal
                                           class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 pl-7 pr-4 py-2.5 text-sm text-gray-900 dark:text-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition" />
                                </div>
                                <p class="mt-1 text-xs text-gray-400">Dejar vacío si no hay oferta</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Unidad <span class="text-red-500">*</span></label>
                                <select v-model="form.unidad"
                                        class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition">
                                    <option value="kg">kg</option>
                                    <option value="ud">ud</option>
                                    <option value="500g">500g</option>
                                    <option value="250g">250g</option>
                                    <option value="litro">litro</option>
                                    <option value="bote">bote</option>
                                    <option value="caja">caja</option>
                                    <option value="bolsa">bolsa</option>
                                    <option value="manojo">manojo</option>
                                    <option value="docena">docena</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Stock <span class="text-red-500">*</span></label>
                                <input v-model="form.stock" type="number" min="0" max="999999" required inputmode="numeric" step="1" v-only-digits
                                       class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Stock mínimo</label>
                                <input v-model="form.stock_minimo" type="number" min="0" max="999999" inputmode="numeric" step="1" v-only-digits
                                       class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition" />
                                <p class="mt-1 text-xs text-gray-400">Se mostrará "últimas unidades" al llegar a este nivel</p>
                            </div>
                        </div>

                        <!-- Toggles disponible/destacado/oferta -->
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
                                <span class="flex items-center gap-1.5 text-sm font-medium text-gray-700 dark:text-gray-300"><Star class="h-4 w-4 text-yellow-500" /> Destacar producto</span>
                            </label>

                            <button type="button"
                                    :disabled="!tieneOferta"
                                    @click="toggleOfertaActiva"
                                    class="flex cursor-pointer items-center gap-3 disabled:opacity-40 disabled:cursor-not-allowed">
                                <div class="relative pointer-events-none">
                                    <div :class="['h-6 w-11 rounded-full transition-colors', form.oferta_activa ? 'bg-orange-500' : 'bg-gray-300 dark:bg-gray-600']"></div>
                                    <div :class="['absolute top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform', form.oferta_activa ? 'translate-x-5' : 'translate-x-0.5']"></div>
                                </div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Oferta activa
                                    <span class="text-xs text-gray-400">{{ form.precio_oferta ? `(${Number(form.precio_oferta).toFixed(2)}€)` : '(pon precio oferta)' }}</span>
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- ── Imagen ──────────────────────────────────────────── -->
                    <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-6 shadow-sm">
                        <h3 class="mb-5 text-base font-semibold text-gray-900 dark:text-white">Imagen del producto</h3>

                        <!-- Toggle modo -->
                        <div class="mb-4 flex gap-1 rounded-lg bg-gray-100 dark:bg-gray-700 p-1 w-fit">
                            <button type="button" @click="imagenMode = 'file'"
                                    :class="['px-3 py-1.5 rounded-md text-xs font-medium transition-colors',
                                        imagenMode === 'file' ? 'bg-white dark:bg-gray-600 shadow text-gray-800 dark:text-white' : 'text-gray-500']">
                                Subir archivo
                            </button>
                            <button type="button" @click="imagenMode = 'url'"
                                    :class="['px-3 py-1.5 rounded-md text-xs font-medium transition-colors',
                                        imagenMode === 'url' ? 'bg-white dark:bg-gray-600 shadow text-gray-800 dark:text-white' : 'text-gray-500']">
                                URL de internet
                            </button>
                        </div>

                        <div class="flex items-start gap-5">
                            <!-- Previsualización -->
                            <div class="relative shrink-0">
                                <div class="h-28 w-28 rounded-2xl overflow-hidden bg-gray-100 dark:bg-gray-700 border-2 border-dashed border-gray-200 dark:border-gray-600 flex items-center justify-center">
                                    <img v-if="imagenPreview" :src="imagenPreview"
                                         class="h-full w-full object-cover" alt="Preview" />
                                    <svg v-else class="h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <button v-if="imagenPreview" type="button" @click="deleteImagen"
                                        class="absolute -top-2 -right-2 rounded-full bg-red-500 p-1 text-white hover:bg-red-600 transition-colors shadow">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            <div class="flex-1">
                                <!-- Archivo -->
                                <div v-if="imagenMode === 'file'">
                                    <label class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-600 p-6 hover:border-primary-400 transition-colors">
                                        <svg class="mb-2 h-8 w-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                        </svg>
                                        <p class="text-sm text-gray-500">Arrastra o haz clic para subir</p>
                                        <p class="mt-1 text-xs text-gray-400">PNG, JPG, WEBP hasta 3MB</p>
                                        <input type="file" class="sr-only" accept="image/*" @change="onImagenChange" />
                                    </label>
                                </div>

                                <!-- URL -->
                                <div v-if="imagenMode === 'url'">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">URL de la imagen</label>
                                    <input v-model="form.imagen_url" type="url" placeholder="https://ejemplo.com/imagen.jpg"
                                           @input="onUrlInput"
                                           class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none transition" />
                                    <p class="mt-1.5 text-xs text-gray-400">La imagen se descargará y guardará automáticamente.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── Acciones ──────────────────────────────────────────── -->
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
