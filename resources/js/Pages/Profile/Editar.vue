<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PasswordStrength from '@/Components/PasswordStrength.vue';
import NavbarPublico from '@/Components/NavbarPublico.vue';
import { useI18n } from '@/Composables/useI18n';
import { useToasts } from '@/Composables/useToasts';
import { evaluarPassword, validarNombre, validarCP } from '@/Composables/useValidaciones';
import { Eye, EyeOff } from 'lucide-vue-next';
import { useFileUpload } from '@/Composables/useFileUpload';

const { validate: validateFile } = useFileUpload();

defineProps({
    mustVerifyEmail: { type: Boolean },
    status:          { type: String  },

});

const { t } = useI18n();
const page = usePage();
const user = computed(() => page.props.auth.user);
const { success: toastSuccess, error: toastError } = useToasts();

// Compatibilidad con código antiguo dentro del componente
const addToast = (type, title, message = '') => {
    if (type === 'success') toastSuccess(title, message);
    else if (type === 'error') toastError(title, message);
    else if (type === 'info') useToasts().info(title, message);
    else useToasts().warning(title, message);
};

const tabs = computed(() => [
    { id: 'perfil',    label: t('profile.tab_profile')  },
    { id: 'seguridad', label: t('profile.tab_security') },
    { id: 'cuenta',    label: t('profile.tab_account')  },
]);
const activeTab = ref('perfil');

// -- Avatar --------------------------------------------------------------------
const avatarPreview = ref(null);
const avatarInput   = ref(null);
const avatarForm    = useForm({ avatar: null });

const onAvatarChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    if (!validateFile(file, { accept: ['jpg', 'jpeg', 'png', 'webp', 'gif'] })) {
        e.target.value = '';
        return;
    }
    avatarForm.avatar   = file;
    avatarPreview.value = URL.createObjectURL(file);
};

const uploadAvatar = () => {
    avatarForm.post(route('profile.avatar'), {
        onSuccess: () => {
            avatarPreview.value = null;
        },
        onError: () => {
            addToast('error', t('profile.photo_error_title'), t('profile.photo_error_msg'));
        },
    });
};

// -- Información personal ------------------------------------------------------
const today = new Date();
const maxFechaNacimiento = computed(() => {
    const now = new Date();
    const d = new Date(now.getFullYear() - 18, now.getMonth(), now.getDate());
    return d.toISOString().split('T')[0];
});
const minFechaNacimiento = computed(() => {
    const now = new Date();
    const d = new Date(now.getFullYear() - 120, now.getMonth(), now.getDate());
    return d.toISOString().split('T')[0];
});

const profileForm = useForm({
    name:             user.value?.name             ?? '',
    apellidos:        user.value?.apellidos        ?? '',
    telefono:         user.value?.telefono         ?? '',
    fecha_nacimiento: user.value?.fecha_nacimiento ? user.value.fecha_nacimiento.split('T')[0] : '',
    calle:            user.value?.calle            ?? '',
    numero:           user.value?.numero           ?? '',
    puerta:           user.value?.puerta           ?? '',
    cp:               user.value?.cp               ?? '',
    localidad:        user.value?.localidad        ?? '',
});

const buscandoLocalidad = ref(false);
const isLanzaroteCP = (cp) => { const n = parseInt(cp, 10); return n >= 35500 && n <= 35599; };
watch(() => profileForm.cp, async (cp) => {
    if (!/^\d{5}$/.test(cp) || !isLanzaroteCP(cp)) return;
    buscandoLocalidad.value = true;
    try {
        const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&addressdetails=1&countrycodes=es&postalcode=${cp}&limit=5`, {
            headers: { 'Accept-Language': 'es', 'User-Agent': 'Rustikan/1.0' },
        });
        const data = await res.json();
        if (data.length > 0) {
            const addr = data[0].address ?? {};
            const localidad = addr.village || addr.suburb || addr.quarter || addr.neighbourhood
                || addr.hamlet || addr.city_district || addr.town
                || addr.city || addr.municipality || addr.county || '';
            if (localidad) profileForm.localidad = localidad;
        }
    } catch { /* silently fail */ } finally {
        buscandoLocalidad.value = false;
    }
});

const erroresProfile = ref({});

const saveProfile = () => {
    const e = {};
    const nameErr = validarNombre(profileForm.name, { min: 2, max: 60 });
    if (nameErr) e.name = nameErr;
    if (profileForm.apellidos) {
        const apErr = validarNombre(profileForm.apellidos, { min: 2, max: 80 });
        if (apErr) e.apellidos = apErr;
    }
    if (profileForm.cp) {
        const cpErr = validarCP(profileForm.cp, { soloLanzarote: true });
        if (cpErr) e.cp = cpErr;
    }
    erroresProfile.value = e;
    if (Object.keys(e).length) {
        addToast('error', t('profile.save_error_title'), Object.values(e)[0]);
        return;
    }

    // onSuccess sin addToast: el flash del backend ya genera el toast via ToastContainer
    profileForm.patch(route('profile.update'), {
        onError: () => addToast('error', t('profile.save_error_title'), t('profile.save_error_msg')),
    });
};

// -- Contraseña ----------------------------------------------------------------
const passwordForm = useForm({
    current_password:      '',
    password:              '',
    password_confirmation: '',
});

const erroresPassword = ref({});
const showCurrentPw = ref(false);
const showNewPw = ref(false);
const showConfirmPw = ref(false);
const showDeletePw = ref(false);

const updatePassword = () => {
    const e = {};
    if (!passwordForm.current_password) e.current_password = 'Introduce tu contraseña actual.';
    const pw = evaluarPassword(passwordForm.password);
    if (!pw.valida) e.password = 'Mínimo 8 caracteres con mayúsculas, minúsculas, números y símbolos.';
    if (passwordForm.password !== passwordForm.password_confirmation) {
        e.password_confirmation = 'Las contraseñas no coinciden.';
    }
    erroresPassword.value = e;
    if (Object.keys(e).length) return;

    passwordForm.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
            erroresPassword.value = {};
            addToast('success', t('profile.password_updated'));
        },
        onError: () => addToast('error', t('profile.password_error_title'), t('profile.password_error_msg')),
    });
};

// -- Eliminar cuenta -----------------------------------------------------------
const FRASE_BORRAR       = 'Me voy de rustikan';
const deleteStep        = ref(0); // 0=oculto 1=frase 2=contraseña
const deletePhraseInput = ref('');
const deleteForm        = useForm({ password: '' });
const deletePhraseOk    = computed(() => deletePhraseInput.value === FRASE_BORRAR);

// -- Helpers -------------------------------------------------------------------
const memberSince = computed(() => {
    if (!user.value?.created_at) return '';
    const { locale } = useI18n();
    return new Date(user.value.created_at).toLocaleDateString(locale.value, {
        month: 'long',
        year:  'numeric',
    });
});

const initials = computed(() => {
    const n = user.value?.name?.[0]      ?? '';
    const a = user.value?.apellidos?.[0] ?? '';
    return (n + a).toUpperCase() || (user.value?.name?.slice(0, 2) ?? '').toUpperCase();
});
</script>

<template>
    <Head :title="t('profile.title')" />

    <!-- Los toasts se muestran via ToastContainer global montado en app.js -->

    <div class="min-h-screen bg-gray-50 dark:bg-gray-950 transition-colors duration-300">

        <NavbarPublico />

        <!-- Hero con gradiente de marca -->
        <div class="bg-gradient-to-br from-primary-500 to-tierra-600">
            <div class="mx-auto max-w-3xl px-4 pb-16 pt-24 sm:px-6">
                <div class="flex flex-col items-center text-center">

                    <!-- Avatar: zona de subida visual -->
                    <div class="relative">
                        <!-- Círculo principal -->
                        <button
                            type="button"
                            @click="avatarInput?.click()"
                            :class="[
                                'group relative flex h-28 w-28 items-center justify-center overflow-hidden rounded-full border-4 transition-all duration-200',
                                (avatarPreview || user.avatar)
                                    ? 'border-white/40 shadow-xl hover:border-white/70'
                                    : 'border-dashed border-white/50 hover:border-white bg-white/10 hover:bg-white/20'
                            ]"
                            :title="t('profile.change_photo')"
                        >
                            <!-- Con foto -->
                            <template v-if="avatarPreview || user.avatar">
                                <img
                                    :src="avatarPreview ?? (user.avatar?.startsWith('http') ? user.avatar : `/storage/${user.avatar}`)"
                                    class="h-full w-full object-cover"
                                    :alt="t('profile.photo')"
                                />
                                <!-- Overlay editar -->
                                <div class="absolute inset-0 flex flex-col items-center justify-center gap-1 bg-black/50 opacity-0 transition-opacity group-hover:opacity-100">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span class="text-xs font-medium text-white">{{ t('profile.change_photo_overlay') }}</span>
                                </div>
                            </template>

                            <!-- Sin foto: iniciales + icono invitación -->
                            <template v-else>
                                <div class="flex flex-col items-center gap-1">
                                    <span class="text-2xl font-bold text-white">{{ initials }}</span>
                                    <svg class="h-4 w-4 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                </div>
                            </template>
                        </button>

                        <!-- Badge de cámara (esquina inferior derecha) -->
                        <span class="pointer-events-none absolute bottom-0 right-0 flex h-8 w-8 items-center justify-center rounded-full bg-white shadow-md">
                            <svg class="h-4 w-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </span>

                        <input ref="avatarInput" type="file" accept="image/*" class="hidden" @change="onAvatarChange" />
                    </div>

                    <!-- Confirmar subida -->
                    <Transition
                        enter-active-class="transition-all duration-200"
                        enter-from-class="opacity-0 -translate-y-1"
                        enter-to-class="opacity-100 translate-y-0"
                    >
                        <button
                            v-if="avatarPreview"
                            type="button"
                            @click="uploadAvatar"
                            :disabled="avatarForm.processing"
                            class="mt-3 flex items-center gap-2 rounded-full bg-white px-5 py-2 text-sm font-semibold text-primary-600 shadow-lg transition hover:bg-primary-50 disabled:opacity-50"
                        >
                            <svg v-if="!avatarForm.processing" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <svg v-else class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                            </svg>
                            {{ avatarForm.processing ? t('profile.uploading') : t('profile.save_photo') }}
                        </button>
                        <p v-else class="mt-2 text-xs text-white/50">{{ t('profile.tap_to_change') }}</p>
                    </Transition>

                    <h1 class="mt-4 text-2xl font-bold text-white">
                        {{ user.name }}<span v-if="user.apellidos"> {{ user.apellidos }}</span>
                    </h1>
                    <p class="mt-1 text-sm text-white/70">{{ user.email }}</p>
                    <p v-if="memberSince" class="mt-1 text-xs text-white/50">{{ t('profile.member_since') }} {{ memberSince }}</p>
                </div>
            </div>
        </div>

        <!-- Panel de contenido -->
        <div class="mx-auto max-w-3xl px-4 sm:px-6">
            <div class="-mt-6 rounded-2xl bg-white dark:bg-gray-800 shadow-md">

                <!-- Tabs -->
                <div class="flex border-b border-gray-100 dark:border-gray-700">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        :class="[
                            'flex-1 py-4 text-sm font-medium transition-colors',
                            activeTab === tab.id
                                ? 'border-b-2 border-primary-500 text-primary-600'
                                : 'text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-400',
                        ]"
                    >
                        {{ tab.label }}
                    </button>
                </div>

                <div class="p-6 sm:p-8">

                    <!-- -- Pestaña: Mi perfil ------------------------------- -->
                    <form
                        v-if="activeTab === 'perfil'"
                        @submit.prevent="saveProfile"
                        class="space-y-5"
                    >
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="p-name" :value="t('profile.name')" />
                                <TextInput id="p-name" v-model="profileForm.name" type="text" class="mt-1 block w-full" required autocomplete="given-name" minlength="2" maxlength="60" v-only-letters />
                                <InputError class="mt-1" :message="erroresProfile.name || profileForm.errors.name" />
                            </div>
                            <div>
                                <InputLabel for="p-apellidos" :value="t('profile.surname')" />
                                <TextInput id="p-apellidos" v-model="profileForm.apellidos" type="text" class="mt-1 block w-full" autocomplete="family-name" minlength="2" maxlength="80" v-only-letters />
                                <InputError class="mt-1" :message="erroresProfile.apellidos || profileForm.errors.apellidos" />
                            </div>
                        </div>

                        <!-- Email bloqueado -->
                        <div>
                            <InputLabel for="p-email" :value="t('profile.email_label')" />
                            <div class="mt-1 flex items-center gap-2">
                                <div class="flex flex-1 items-center rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 px-3 py-2">
                                    <span class="flex-1 truncate text-sm text-gray-700 dark:text-gray-300">{{ user.email }}</span>
                                    <span v-if="user.email_verified_at" class="ml-2 flex items-center gap-1 text-xs font-medium text-green-600">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                        </svg>
                                        {{ t('profile.verified') }}
                                    </span>
                                    <span v-else class="ml-2 flex items-center gap-1 text-xs font-medium text-amber-600">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                        </svg>
                                        {{ t('profile.unverified') }}
                                    </span>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ t('profile.email_locked') }}</p>
                        </div>

                        <!-- Teléfono editable -->
                        <div>
                            <InputLabel for="p-telefono" :value="t('profile.phone_label')" />
                            <TextInput id="p-telefono" v-model="profileForm.telefono" type="tel"
                                class="mt-1 block w-full" autocomplete="off" inputmode="tel"
                                maxlength="20" v-only-phone placeholder="+34612345678" />
                            <InputError class="mt-1" :message="profileForm.errors.telefono" />
                        </div>

                        <div>
                            <InputLabel for="p-fnac" value="Fecha de nacimiento" />
                            <TextInput id="p-fnac" v-model="profileForm.fecha_nacimiento" type="date"
                                class="mt-1 block w-full" :max="maxFechaNacimiento" :min="minFechaNacimiento" />
                            <InputError class="mt-1" :message="erroresProfile.fecha_nacimiento || profileForm.errors.fecha_nacimiento" />
                        </div>

                        <!-- Dirección separada -->
                        <div class="grid gap-4 sm:grid-cols-3">
                            <div class="sm:col-span-2">
                                <InputLabel for="p-calle" value="Calle / Avenida" />
                                <TextInput id="p-calle" v-model="profileForm.calle" type="text"
                                    class="mt-1 block w-full" autocomplete="address-line1" maxlength="100" placeholder="Calle Ejemplo" />
                                <InputError class="mt-1" :message="erroresProfile.calle || profileForm.errors.calle" />
                            </div>
                            <div>
                                <InputLabel for="p-numero" value="Número" />
                                <TextInput id="p-numero" v-model="profileForm.numero" type="text"
                                    class="mt-1 block w-full" maxlength="10" placeholder="12" />
                                <InputError class="mt-1" :message="profileForm.errors.numero" />
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-3">
                            <div>
                                <InputLabel for="p-puerta" value="Piso / Puerta" />
                                <TextInput id="p-puerta" v-model="profileForm.puerta" type="text"
                                    class="mt-1 block w-full" maxlength="20" placeholder="2ºA" />
                                <InputError class="mt-1" :message="profileForm.errors.puerta" />
                            </div>
                            <div>
                                <InputLabel for="p-cp" value="Código postal" />
                                <div class="relative mt-1">
                                    <TextInput id="p-cp" v-model="profileForm.cp" type="text"
                                        class="block w-full" maxlength="5" inputmode="numeric" v-only-digits placeholder="35500" />
                                    <span v-if="buscandoLocalidad" class="absolute right-2 top-1/2 -translate-y-1/2">
                                        <svg class="animate-spin h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                        </svg>
                                    </span>
                                </div>
                                <InputError class="mt-1" :message="erroresProfile.cp || profileForm.errors.cp" />
                            </div>
                            <div>
                                <InputLabel for="p-localidad" value="Localidad" />
                                <TextInput id="p-localidad" v-model="profileForm.localidad" type="text"
                                    class="mt-1 block w-full" maxlength="100" v-only-letters placeholder="Arrecife" />
                                <InputError class="mt-1" :message="profileForm.errors.localidad" />
                            </div>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button
                                type="submit"
                                :disabled="profileForm.processing || !profileForm.isDirty"
                                :class="['inline-flex items-center gap-2 rounded-lg px-6 py-2.5 text-sm font-semibold text-white transition disabled:cursor-not-allowed disabled:opacity-40',
                                    profileForm.isDirty ? 'bg-primary-500 hover:bg-primary-600' : 'bg-gray-400']"
                            >
                                <svg v-if="profileForm.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                </svg>
                                {{ profileForm.processing ? t('profile.saving') : t('profile.save') }}
                            </button>
                        </div>
                    </form>

                    <!-- -- Pestaña: Seguridad ------------------------------- -->
                    <div v-if="activeTab === 'seguridad'" class="space-y-6">

                        <!-- Tarjeta de consejos -->
                        <div class="rounded-xl border border-blue-100 dark:border-blue-900/40 bg-blue-50 dark:bg-blue-900/20 p-4">
                            <div class="flex items-start gap-3">
                                <div class="mt-0.5 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/40">
                                    <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold text-blue-800 dark:text-blue-300">{{ t('profile.security_tips_title') }}</h4>
                                    <ul class="mt-2 space-y-1 text-xs text-blue-700 dark:text-blue-400">
                                        <li class="flex items-center gap-1.5">
                                            <svg class="h-3 w-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                            {{ t('profile.tip_length') }}
                                        </li>
                                        <li class="flex items-center gap-1.5">
                                            <svg class="h-3 w-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                            {{ t('profile.tip_mix') }}
                                        </li>
                                        <li class="flex items-center gap-1.5">
                                            <svg class="h-3 w-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                            {{ t('profile.tip_reuse') }}
                                        </li>
                                        <li class="flex items-center gap-1.5">
                                            <svg class="h-3 w-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                            {{ t('profile.tip_names') }}
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Tarjeta de acceso reciente -->
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="rounded-xl border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 p-4">
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-gray-500">{{ t('profile.linked_email') }}</p>
                                <p class="mt-1 truncate text-sm font-semibold text-gray-700 dark:text-gray-300">{{ user.email }}</p>
                            </div>
                            <div class="rounded-xl border border-green-100 bg-green-50 p-4">
                                <p class="text-xs font-medium uppercase tracking-wide text-green-500">{{ t('profile.account_status') }}</p>
                                <p class="mt-1 flex items-center gap-1.5 text-sm font-semibold text-green-700">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                    {{ t('profile.account_active') }}
                                </p>
                            </div>
                        </div>

                        <!-- Formulario cambio contraseña -->
                        <div class="rounded-xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-700/30 p-5 shadow-sm">
                            <div class="mb-4 flex items-center justify-between">
                                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ t('profile.change_password_title') }}</h4>
                                <Link
                                    :href="route('password.request')"
                                    class="text-xs text-primary-600 dark:text-primary-400 hover:underline"
                                >
                                    ¿Olvidaste tu contraseña?
                                </Link>
                            </div>
                            <form @submit.prevent="updatePassword" class="space-y-4">
                                <div>
                                    <InputLabel for="s-current" :value="t('profile.current_password')" />
                                    <div class="relative mt-1">
                                        <TextInput id="s-current" v-model="passwordForm.current_password" :type="showCurrentPw ? 'text' : 'password'" class="block w-full pr-10" autocomplete="current-password" maxlength="128" />
                                        <button type="button" @click="showCurrentPw = !showCurrentPw" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                            <EyeOff v-if="showCurrentPw" class="h-4 w-4" /><Eye v-else class="h-4 w-4" />
                                        </button>
                                    </div>
                                    <InputError class="mt-1" :message="erroresPassword.current_password || passwordForm.errors.current_password" />
                                </div>
                                <div>
                                    <InputLabel for="s-new" :value="t('profile.new_password')" />
                                    <div class="relative mt-1">
                                        <TextInput id="s-new" v-model="passwordForm.password" :type="showNewPw ? 'text' : 'password'" class="block w-full pr-10" autocomplete="new-password" minlength="8" maxlength="128" />
                                        <button type="button" @click="showNewPw = !showNewPw" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                            <EyeOff v-if="showNewPw" class="h-4 w-4" /><Eye v-else class="h-4 w-4" />
                                        </button>
                                    </div>
                                    <InputError class="mt-1" :message="erroresPassword.password || passwordForm.errors.password" />
                                    <PasswordStrength :password="passwordForm.password" />
                                </div>
                                <div>
                                    <InputLabel for="s-confirm" :value="t('profile.confirm_password')" />
                                    <div class="relative mt-1">
                                        <TextInput id="s-confirm" v-model="passwordForm.password_confirmation" :type="showConfirmPw ? 'text' : 'password'" class="block w-full pr-10" autocomplete="new-password" minlength="8" maxlength="128" />
                                        <button type="button" @click="showConfirmPw = !showConfirmPw" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                            <EyeOff v-if="showConfirmPw" class="h-4 w-4" /><Eye v-else class="h-4 w-4" />
                                        </button>
                                    </div>
                                    <InputError class="mt-1" :message="erroresPassword.password_confirmation || passwordForm.errors.password_confirmation" />
                                </div>
                                <div class="flex justify-end pt-1">
                                    <button
                                        type="submit"
                                        :disabled="passwordForm.processing"
                                        class="inline-flex items-center gap-2 rounded-lg bg-primary-500 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-600 disabled:opacity-50"
                                    >
                                        <svg v-if="passwordForm.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                        </svg>
                                        {{ passwordForm.processing ? t('profile.updating') : t('profile.update_password') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- -- Pestaña: Cuenta --------------------------------- -->
                    <div v-if="activeTab === 'cuenta'" class="space-y-6">

                        <!-- Acceso rápido a pedidos -->
                        <div class="rounded-xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-700/30 p-5 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ t('profile.my_orders_title') }}</h4>
                                    <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">{{ t('profile.my_orders_sub') }}</p>
                                </div>
                                <Link
                                    :href="route('pedidos.index')"
                                    class="flex items-center gap-1.5 rounded-lg bg-primary-500 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-primary-600"
                                >
                                    {{ t('profile.view_orders') }}
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </Link>
                            </div>
                        </div>

                        <!-- -- Eliminar cuenta (al fondo) --- -->
                        <div class="rounded-xl border border-red-200 dark:border-red-900/40 bg-red-50 dark:bg-red-900/20 p-5">
                            <div class="flex items-start gap-3">
                                <div class="mt-0.5 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-red-100">
                                    <svg class="h-4 w-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm font-semibold text-red-700">{{ t('profile.delete_account_title') }}</h3>
                                    <p class="mt-1 text-sm text-red-600/80">
                                        {{ t('profile.delete_account_desc') }}
                                    </p>

                                    <!-- Paso 0: botón inicial -->
                                    <div v-if="deleteStep === 0" class="mt-4">
                                        <button
                                            type="button"
                                            @click="deleteStep = 1"
                                            class="rounded-lg border border-red-300 px-4 py-2 text-sm font-medium text-red-600 transition hover:bg-red-100"
                                        >
                                            {{ t('profile.delete_confirm_btn') }}
                                        </button>
                                    </div>

                                    <!-- Paso 1: confirmar frase -->
                                    <div v-else-if="deleteStep === 1" class="mt-4 space-y-3">
                                        <p class="text-sm font-medium text-red-700">
                                            {{ t('profile.delete_phrase_label') }}
                                            <span class="ml-1 rounded bg-red-100 px-1.5 py-0.5 font-mono text-red-800">{{ FRASE_BORRAR }}</span>
                                        </p>
                                        <input
                                            v-model="deletePhraseInput"
                                            type="text"
                                            :placeholder="t('profile.delete_phrase_placeholder')"
                                            class="block w-full rounded-lg border border-red-200 dark:border-red-800 bg-white dark:bg-gray-700 dark:text-white px-3 py-2 text-sm focus:border-red-400 focus:outline-none focus:ring-1 focus:ring-red-400"
                                            :class="deletePhraseInput && !deletePhraseOk ? 'border-red-400 bg-red-50' : ''"
                                        />
                                        <p v-if="deletePhraseInput && !deletePhraseOk" class="text-xs text-red-500">
                                            {{ t('profile.delete_phrase_mismatch') }}
                                        </p>
                                        <div class="flex gap-3">
                                            <button
                                                type="button"
                                                @click="deleteStep = 0; deletePhraseInput = ''"
                                                class="rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm text-gray-600 dark:text-gray-400 transition hover:bg-gray-100 dark:hover:bg-gray-700"
                                            >
                                                {{ t('profile.cancel') }}
                                            </button>
                                            <button
                                                type="button"
                                                :disabled="!deletePhraseOk"
                                                @click="deleteStep = 2"
                                                class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700 disabled:opacity-40"
                                            >
                                                {{ t('profile.continue') }}
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Paso 2: confirmar contraseña -->
                                    <div v-else-if="deleteStep === 2" class="mt-4 space-y-3">
                                        <p class="text-sm text-red-700 font-medium">{{ t('profile.delete_confirm_password') }}</p>
                                        <div class="relative mt-1">
                                            <TextInput
                                                id="del-pass"
                                                v-model="deleteForm.password"
                                                :type="showDeletePw ? 'text' : 'password'"
                                                class="block w-full pr-10"
                                                :placeholder="t('profile.delete_password_placeholder')"
                                            />
                                            <button type="button" @click="showDeletePw = !showDeletePw" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                                <EyeOff v-if="showDeletePw" class="h-4 w-4" /><Eye v-else class="h-4 w-4" />
                                            </button>
                                        </div>
                                        <InputError :message="deleteForm.errors.password" />
                                        <div class="flex gap-3 pt-1">
                                            <button
                                                type="button"
                                                @click="deleteStep = 0; deletePhraseInput = ''; deleteForm.reset()"
                                                class="rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm text-gray-600 dark:text-gray-400 transition hover:bg-gray-100 dark:hover:bg-gray-700"
                                            >
                                                {{ t('profile.cancel') }}
                                            </button>
                                            <button
                                                type="button"
                                                @click="deleteForm.delete(route('profile.destroy'))"
                                                :disabled="deleteForm.processing || !deleteForm.password"
                                                class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700 disabled:opacity-50"
                                            >
                                                <svg v-if="deleteForm.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                                </svg>
                                                {{ t('profile.delete_final') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="py-8 text-center">
                <Link href="/" class="text-sm text-gray-400 transition hover:text-gray-600">{{ t('profile.back_home') }}</Link>
            </div>
        </div>

    </div>
</template>
