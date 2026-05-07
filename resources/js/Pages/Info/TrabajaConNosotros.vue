<template>
    <div class="min-h-screen bg-white dark:bg-gray-900">
        <Head title="Trabaja con nosotros" />
        <NavbarPublico />

        <!-- Toasts via ToastContainer global -->

        <!-- Hero -->
        <section class="bg-gradient-to-br from-primary-600 via-tierra-600 to-tierra-700 pt-32 pb-20 text-white">
            <div class="mx-auto max-w-4xl px-4 text-center">
                <div class="mb-6 inline-flex h-16 w-16 items-center justify-center rounded-full bg-white/10 backdrop-blur-sm">
                    <Briefcase class="h-8 w-8" />
                </div>
                <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">{{ t('info.work.title') }}</h1>
                <p class="mt-4 text-lg text-white/90 max-w-2xl mx-auto">
                    {{ t('info.work.subtitle') }}
                </p>
            </div>
        </section>

        <!-- Contenido -->
        <section class="py-16 bg-white dark:bg-gray-900">
            <div class="mx-auto max-w-5xl px-4 sm:px-6">
                <div class="grid gap-10 lg:grid-cols-5">

                    <!-- Formulario -->
                    <div class="lg:col-span-3">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">{{ t('info.work.form_title') }}</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">{{ t('info.work.form_subtitle') }}</p>

                        <form @submit.prevent="enviar" class="space-y-5">

                            <!-- Nombre + Apellidos -->
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        {{ t('info.work.name') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input v-model="form.nombre" type="text" required minlength="2" maxlength="100" v-only-letters
                                        :class="inputClass(form.errors.nombre)" />
                                    <p v-if="form.errors.nombre" class="mt-1 text-xs text-red-500">{{ form.errors.nombre }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        {{ t('info.work.surname') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input v-model="form.apellidos" type="text" required minlength="2" maxlength="100" v-only-letters
                                        :class="inputClass(form.errors.apellidos)" />
                                    <p v-if="form.errors.apellidos" class="mt-1 text-xs text-red-500">{{ form.errors.apellidos }}</p>
                                </div>
                            </div>

                            <!-- Email + Teléfono -->
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Email <span class="text-red-500">*</span>
                                    </label>
                                    <input v-model="form.email" type="email" required maxlength="180"
                                        placeholder="tu@email.com"
                                        :class="inputClass(form.errors.email)" />
                                    <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        {{ t('info.work.phone') }}
                                    </label>
                                    <input v-model="form.telefono" type="tel" inputmode="tel" maxlength="20" v-only-phone
                                        placeholder="+34 600 000 000"
                                        :class="inputClass(form.errors.telefono)" />
                                </div>
                            </div>

                            <!-- Puesto -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    {{ t('info.work.position') }} <span class="text-red-500">*</span>
                                </label>
                                <select v-model="form.puesto" required :class="inputClass(form.errors.puesto)">
                                    <option value="" disabled>{{ t('info.work.position_placeholder') }}</option>
                                    <option value="desarrollo">{{ t('info.work.pos_dev') }}</option>
                                    <option value="atencion-cliente">{{ t('info.work.pos_support') }}</option>
                                    <option value="marketing">{{ t('info.work.pos_marketing') }}</option>
                                    <option value="logistica">{{ t('info.work.pos_logistics') }}</option>
                                    <option value="reparto">{{ t('info.work.pos_courier') }}</option>
                                    <option value="otro">{{ t('info.work.pos_other') }}</option>
                                </select>
                                <p v-if="form.errors.puesto" class="mt-1 text-xs text-red-500">{{ form.errors.puesto }}</p>
                            </div>

                            <!-- Mensaje -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    {{ t('info.work.message') }} <span class="text-red-500">*</span>
                                </label>
                                <textarea v-model="form.mensaje" required rows="5" minlength="30" maxlength="2000"
                                    :placeholder="t('info.work.message_placeholder')"
                                    :class="['resize-none', inputClass(form.errors.mensaje)]"></textarea>
                                <div class="mt-1 flex justify-between">
                                    <p v-if="form.errors.mensaje" class="text-xs text-red-500">{{ form.errors.mensaje }}</p>
                                    <p class="ml-auto text-xs text-gray-400">{{ form.mensaje.length }}/2000</p>
                                </div>
                            </div>

                            <!-- CV -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    {{ t('info.work.cv') }} <span class="text-red-500">*</span>
                                </label>
                                <div :class="[
                                    'relative flex flex-col items-center justify-center rounded-xl border-2 border-dashed px-6 py-8 cursor-pointer transition-colors',
                                    form.errors.cv
                                        ? 'border-red-300 bg-red-50 dark:bg-red-900/10'
                                        : cvFile
                                            ? 'border-primary-300 bg-primary-50 dark:bg-primary-900/10'
                                            : 'border-gray-300 dark:border-gray-600 hover:border-primary-400 hover:bg-primary-50/50 dark:hover:bg-primary-900/10'
                                ]"
                                @click="$refs.cvInput.click()"
                                @dragover.prevent
                                @drop.prevent="onDrop"
                                >
                                    <input ref="cvInput" type="file" accept=".pdf,.doc,.docx" class="hidden" @change="onFileChange" />

                                    <template v-if="!cvFile">
                                        <Upload class="h-10 w-10 text-gray-400 mb-3" />
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ t('info.work.cv_drop') }}</p>
                                        <p class="text-xs text-gray-400 mt-1">{{ t('info.work.cv_hint') }}</p>
                                    </template>

                                    <template v-else>
                                        <FileText class="h-10 w-10 text-primary-500 mb-3" />
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate max-w-full">{{ cvFile.name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ formatBytes(cvFile.size) }}</p>
                                        <button type="button" @click.stop="quitarCv" class="mt-3 inline-flex items-center gap-1 text-xs text-red-600 hover:text-red-700">
                                            <X class="h-3.5 w-3.5" /> {{ t('info.work.cv_remove') }}
                                        </button>
                                    </template>
                                </div>
                                <p v-if="form.errors.cv" class="mt-1 text-xs text-red-500">{{ form.errors.cv }}</p>
                            </div>

                            <!-- Submit -->
                            <div class="flex items-center justify-end pt-2">
                                <button type="submit" :disabled="form.processing"
                                    class="flex items-center gap-2 rounded-xl bg-primary-600 px-8 py-3 text-sm font-bold text-white shadow-sm transition-all hover:bg-primary-700 hover:shadow-md disabled:opacity-60">
                                    <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                    </svg>
                                    {{ form.processing ? t('info.work.sending') : t('info.work.send') }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Sidebar: por qué Rustikan -->
                    <aside class="lg:col-span-2 space-y-5">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ t('info.work.why_title') }}</h2>

                        <div class="flex gap-4 items-start p-5 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900/40 text-primary-600 dark:text-primary-400 flex-shrink-0">
                                <Sparkles class="h-5 w-5" />
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">{{ t('info.work.why_local_title') }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ t('info.work.why_local_desc') }}</p>
                            </div>
                        </div>

                        <div class="flex gap-4 items-start p-5 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900/40 text-primary-600 dark:text-primary-400 flex-shrink-0">
                                <Users class="h-5 w-5" />
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">{{ t('info.work.why_team_title') }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ t('info.work.why_team_desc') }}</p>
                            </div>
                        </div>

                        <div class="flex gap-4 items-start p-5 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900/40 text-primary-600 dark:text-primary-400 flex-shrink-0">
                                <Rocket class="h-5 w-5" />
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">{{ t('info.work.why_growth_title') }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ t('info.work.why_growth_desc') }}</p>
                            </div>
                        </div>

                        <div class="rounded-xl bg-primary-50 dark:bg-primary-900/20 border border-primary-100 dark:border-primary-800 p-5">
                            <p class="text-sm font-semibold text-primary-900 dark:text-primary-300 mb-1">{{ t('info.work.privacy_title') }}</p>
                            <p class="text-sm text-primary-700 dark:text-primary-400">{{ t('info.work.privacy_desc') }}</p>
                        </div>
                    </aside>
                </div>
            </div>
        </section>

        <FooterPublico />

        <!-- ── Modal de confirmación tras envío ────────────────────────────── -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showConfirmacion && ultimoEnvio"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
                @click.self="cerrarConfirmacion"
            >
                <Transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                    appear
                >
                    <div class="w-full max-w-md max-h-[92vh] overflow-y-auto overscroll-contain rounded-3xl bg-white dark:bg-gray-800 shadow-2xl">

                        <!-- Cabecera con check animado -->
                        <div class="bg-gradient-to-br from-primary-500 to-tierra-600 px-6 pt-8 pb-6 text-center">
                            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-white/20 backdrop-blur-sm ring-4 ring-white/30 animate-pulse-once">
                                <CheckCircle2 class="h-9 w-9 text-white" :stroke-width="2.5" />
                            </div>
                            <h3 class="text-xl font-extrabold text-white">¡Candidatura enviada!</h3>
                            <p class="mt-1 text-sm text-white/90">Gracias por tu interés en Rustikan</p>
                        </div>

                        <!-- Cuerpo: resumen + email -->
                        <div class="p-6 space-y-5">
                            <!-- Bloque destacado de email -->
                            <div class="flex items-start gap-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 p-4">
                                <Mail class="h-5 w-5 flex-shrink-0 text-blue-600 dark:text-blue-400 mt-0.5" />
                                <div class="text-sm">
                                    <p class="font-semibold text-blue-900 dark:text-blue-200">Email de confirmación enviado</p>
                                    <p class="mt-0.5 text-blue-700 dark:text-blue-300">
                                        Te hemos escrito a <span class="font-semibold">{{ ultimoEnvio.email }}</span> con un resumen de tu candidatura.
                                    </p>
                                </div>
                            </div>

                            <!-- Resumen -->
                            <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                                <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-2.5 border-b border-gray-200 dark:border-gray-700">
                                    <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Resumen</p>
                                </div>
                                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                                    <div class="flex items-center justify-between gap-4 px-4 py-2.5 text-sm">
                                        <span class="text-gray-500 dark:text-gray-400">Candidato</span>
                                        <span class="font-semibold text-gray-900 dark:text-white truncate">{{ ultimoEnvio.nombre }} {{ ultimoEnvio.apellidos }}</span>
                                    </div>
                                    <div class="flex items-center justify-between gap-4 px-4 py-2.5 text-sm">
                                        <span class="text-gray-500 dark:text-gray-400">Puesto</span>
                                        <span class="font-semibold text-gray-900 dark:text-white truncate">{{ puestoLabel(ultimoEnvio.puesto) }}</span>
                                    </div>
                                    <div v-if="ultimoEnvio.cvNombre" class="flex items-center justify-between gap-4 px-4 py-2.5 text-sm">
                                        <span class="text-gray-500 dark:text-gray-400">CV</span>
                                        <span class="font-medium text-gray-700 dark:text-gray-300 truncate inline-flex items-center gap-1">
                                            <FileText class="h-3.5 w-3.5 flex-shrink-0" />
                                            {{ ultimoEnvio.cvNombre }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Próximos pasos -->
                            <div class="rounded-xl bg-gray-50 dark:bg-gray-700/50 p-4 text-xs text-gray-600 dark:text-gray-400 leading-relaxed">
                                <p class="font-semibold text-gray-700 dark:text-gray-300 mb-1">¿Qué pasa ahora?</p>
                                Revisaremos tu CV con calma. Si tu perfil encaja con lo que buscamos te contactaremos por email o teléfono. Si no, también te avisaremos para que no quedes en espera.
                            </div>
                        </div>

                        <!-- Footer del modal -->
                        <div class="border-t border-gray-100 dark:border-gray-700 px-6 py-4 flex flex-col-reverse sm:flex-row gap-2 sm:justify-end">
                            <button
                                type="button"
                                @click="cerrarConfirmacion"
                                class="rounded-xl bg-primary-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-primary-700 transition-colors"
                            >
                                Entendido
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
@keyframes pulse-once {
    0%   { transform: scale(0.8); opacity: 0; }
    50%  { transform: scale(1.1); opacity: 1; }
    100% { transform: scale(1);   opacity: 1; }
}
.animate-pulse-once {
    animation: pulse-once 0.5s ease-out;
}
</style>

<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import NavbarPublico from '@/Components/NavbarPublico.vue';
import FooterPublico from '@/Components/FooterPublico.vue';
import { useDarkMode } from '@/Composables/useDarkMode';
import { useI18n } from '@/Composables/useI18n';
import { useToasts } from '@/Composables/useToasts';
import { Briefcase, Upload, FileText, X, Sparkles, Users, Rocket, CheckCircle2, Mail } from 'lucide-vue-next';

const { isDark } = useDarkMode();
const { t } = useI18n();
const { error: toastError } = useToasts();

const cvFile = ref(null);
const cvInput = ref(null);

// Datos del último envío exitoso (para mostrar en el modal de confirmación)
const showConfirmacion = ref(false);
const ultimoEnvio = ref(null);

const form = useForm({
    nombre:    '',
    apellidos: '',
    email:     '',
    telefono:  '',
    puesto:    '',
    mensaje:   '',
    cv:        null,
});

const inputClass = (err) => [
    'w-full rounded-xl border px-4 py-2.5 text-sm outline-none transition focus:ring-2',
    err
        ? 'border-red-400 focus:border-red-400 focus:ring-red-200'
        : 'border-gray-200 dark:border-gray-600 focus:border-primary-400 focus:ring-primary-200',
    isDark.value
        ? 'bg-gray-800 text-white placeholder-gray-500'
        : 'bg-white text-gray-900 placeholder-gray-400',
];

const onFileChange = (e) => {
    const f = e.target.files?.[0];
    if (f) setCv(f);
};

const onDrop = (e) => {
    const f = e.dataTransfer?.files?.[0];
    if (f) setCv(f);
};

const setCv = (file) => {
    const validas = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    if (!validas.includes(file.type) && !/\.(pdf|doc|docx)$/i.test(file.name)) {
        toastError(t('info.work.cv_invalid'));
        return;
    }
    if (file.size > 5 * 1024 * 1024) {
        toastError(t('info.work.cv_too_big'));
        return;
    }
    cvFile.value = file;
    form.cv = file;
};

const quitarCv = () => {
    cvFile.value = null;
    form.cv = null;
    if (cvInput.value) cvInput.value.value = '';
};

const formatBytes = (bytes) => {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
    return `${(bytes / 1024 / 1024).toFixed(2)} MB`;
};

const enviar = () => {
    // Snapshot de los datos antes de resetear, para mostrar en el modal
    const snapshot = {
        nombre:     form.nombre,
        apellidos:  form.apellidos,
        email:      form.email,
        telefono:   form.telefono,
        puesto:     form.puesto,
        cvNombre:   cvFile.value?.name ?? null,
    };

    form.post(route('info.trabaja.store'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            ultimoEnvio.value = snapshot;
            showConfirmacion.value = true;
            form.reset();
            quitarCv();
        },
    });
};

const cerrarConfirmacion = () => {
    showConfirmacion.value = false;
    // Conservamos ultimoEnvio para no parpadear durante la animación de salida
    setTimeout(() => { ultimoEnvio.value = null; }, 250);
};

// Mapeo de slugs de puesto a etiquetas legibles (para el modal)
const puestoLabel = (key) => ({
    'desarrollo':       t('info.work.pos_dev'),
    'atencion-cliente': t('info.work.pos_support'),
    'marketing':        t('info.work.pos_marketing'),
    'logistica':        t('info.work.pos_logistics'),
    'reparto':          t('info.work.pos_courier'),
    'otro':             t('info.work.pos_other'),
}[key] || key);
</script>
