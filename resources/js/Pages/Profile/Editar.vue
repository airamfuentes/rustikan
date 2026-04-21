<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import Toast from '@/Components/Toast.vue';

defineProps({
    mustVerifyEmail: { type: Boolean },
    status:          { type: String  },

});

const page = usePage();
const user = computed(() => page.props.auth.user);

// ── Toast system ──────────────────────────────────────────────────────────────
const toasts = ref([]);

const addToast = (type, title, message = '') => {
    const id = Date.now();
    toasts.value.push({ id, type, title, message });
};

const removeToast = (id) => {
    toasts.value = toasts.value.filter(t => t.id !== id);
};

watch(
    () => page.props.flash,
    (flash) => {
        if (!flash) return;
        if (flash.success) addToast('success', flash.success);
        if (flash.error)   addToast('error',   flash.error);
        if (flash.info)    addToast('info',     flash.info);
        if (flash.warning) addToast('warning',  flash.warning);
    },
    { deep: true, immediate: true },
);

const tabs = [
    { id: 'perfil',    label: 'Mi perfil'   },
    { id: 'seguridad', label: 'Seguridad'   },
    { id: 'cuenta',    label: 'Cuenta'      },
];
const activeTab = ref('perfil');

// ── Avatar ────────────────────────────────────────────────────────────────────
const avatarPreview = ref(null);
const avatarInput   = ref(null);
const avatarForm    = useForm({ avatar: null });

const onAvatarChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    avatarForm.avatar   = file;
    avatarPreview.value = URL.createObjectURL(file);
};

const uploadAvatar = () => {
    avatarForm.post(route('profile.avatar'), {
        onSuccess: () => {
            avatarPreview.value = null;
        },
        onError: () => {
            addToast('error', 'Error al subir la foto', 'Comprueba el formato y el tamaño (máx. 2 MB).');
        },
    });
};

// ── Información personal (─────────────────────────────────────────────────────
const profileForm = useForm({
    name:      user.value?.name      ?? '',
    apellidos: user.value?.apellidos ?? '',
    telefono:  user.value?.telefono  ?? '',
    edad:      user.value?.edad      ?? '',
    direccion: user.value?.direccion ?? '',
});

const saveProfile = () => {
    profileForm.patch(route('profile.update'), {
        onError: () => addToast('error', 'No se pudo guardar', 'Revisa los campos marcados en rojo.'),
    });
};

// ── Contraseña ────────────────────────────────────────────────────────────────
const passwordForm = useForm({
    current_password:      '',
    password:              '',
    password_confirmation: '',
});

const updatePassword = () => {
    passwordForm.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
            addToast('success', 'Contraseña actualizada correctamente.');
        },
        onError: () => addToast('error', 'No se pudo actualizar', 'Comprueba que la contraseña actual sea correcta.'),
    });
};

// ── Eliminar cuenta ───────────────────────────────────────────────────────────
const FRASE_BORRAR       = 'Me voy de rustikan';
const deleteStep        = ref(0); // 0=oculto 1=frase 2=contraseña
const deletePhraseInput = ref('');
const deleteForm        = useForm({ password: '' });
const deletePhraseOk    = computed(() => deletePhraseInput.value === FRASE_BORRAR);

// ── Helpers ───────────────────────────────────────────────────────────────────
const memberSince = computed(() => {
    if (!user.value?.created_at) return '';
    return new Date(user.value.created_at).toLocaleDateString('es-ES', {
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
    <Head title="Mi perfil" />

    <!-- Toasts -->
    <div class="pointer-events-none fixed inset-0 z-50 flex flex-col items-end justify-start gap-3 p-6">
        <Toast
            v-for="toast in toasts"
            :key="toast.id"
            :type="toast.type"
            :title="toast.title"
            :message="toast.message"
            @close="removeToast(toast.id)"
        />
    </div>

    <div class="min-h-screen bg-gray-50">

        <!-- Navbar mínimo -->
        <header class="sticky top-0 z-40 border-b border-gray-200 bg-white/90 backdrop-blur">
            <div class="mx-auto flex max-w-3xl items-center justify-between px-4 py-3 sm:px-6">
                <Link href="/" class="flex items-center">
                    <img src="/images/logo.png" alt="Rustikan" class="h-8 w-auto" />
                </Link>
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="text-sm text-gray-500 transition hover:text-red-500"
                >
                    Cerrar sesión
                </Link>
            </div>
        </header>

        <!-- Hero con gradiente de marca -->
        <div class="bg-gradient-to-br from-primary-500 to-tierra-600">
            <div class="mx-auto max-w-3xl px-4 pb-16 pt-10 sm:px-6">
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
                            title="Cambiar foto de perfil"
                        >
                            <!-- Con foto -->
                            <template v-if="avatarPreview || user.avatar">
                                <img
                                    :src="avatarPreview ?? `/storage/${user.avatar}`"
                                    class="h-full w-full object-cover"
                                    alt="Foto de perfil"
                                />
                                <!-- Overlay editar -->
                                <div class="absolute inset-0 flex flex-col items-center justify-center gap-1 bg-black/50 opacity-0 transition-opacity group-hover:opacity-100">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span class="text-xs font-medium text-white">Cambiar</span>
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
                            {{ avatarForm.processing ? 'Subiendo...' : 'Guardar foto' }}
                        </button>
                        <p v-else class="mt-2 text-xs text-white/50">Toca la foto para cambiarla</p>
                    </Transition>

                    <h1 class="mt-4 text-2xl font-bold text-white">
                        {{ user.name }}<span v-if="user.apellidos"> {{ user.apellidos }}</span>
                    </h1>
                    <p class="mt-1 text-sm text-white/70">{{ user.email }}</p>
                    <p v-if="memberSince" class="mt-1 text-xs text-white/50">Miembro desde {{ memberSince }}</p>
                </div>
            </div>
        </div>

        <!-- Panel de contenido -->
        <div class="mx-auto max-w-3xl px-4 sm:px-6">
            <div class="-mt-6 rounded-2xl bg-white shadow-md">

                <!-- Tabs -->
                <div class="flex border-b border-gray-100">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        :class="[
                            'flex-1 py-4 text-sm font-medium transition-colors',
                            activeTab === tab.id
                                ? 'border-b-2 border-primary-500 text-primary-600'
                                : 'text-gray-400 hover:text-gray-600',
                        ]"
                    >
                        {{ tab.label }}
                    </button>
                </div>

                <div class="p-6 sm:p-8">

                    <!-- ── Pestaña: Mi perfil ─────────────────────────────── -->
                    <form
                        v-if="activeTab === 'perfil'"
                        @submit.prevent="saveProfile"
                        class="space-y-5"
                    >
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="p-name" value="Nombre" />
                                <TextInput id="p-name" v-model="profileForm.name" type="text" class="mt-1 block w-full" required autocomplete="given-name" />
                                <InputError class="mt-1" :message="profileForm.errors.name" />
                            </div>
                            <div>
                                <InputLabel for="p-apellidos" value="Apellidos" />
                                <TextInput id="p-apellidos" v-model="profileForm.apellidos" type="text" class="mt-1 block w-full" autocomplete="family-name" />
                                <InputError class="mt-1" :message="profileForm.errors.apellidos" />
                            </div>
                        </div>

                        <!-- Email bloqueado -->
                        <div>
                            <InputLabel for="p-email" value="Correo electrónico" />
                            <div class="mt-1 flex items-center gap-2">
                                <div class="flex flex-1 items-center rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
                                    <span class="flex-1 truncate text-sm text-gray-700">{{ user.email }}</span>
                                    <span class="ml-2 flex items-center gap-1 text-xs font-medium text-tierra-600">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                        Bloqueado
                                    </span>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-400">Para cambiar el correo contacta con soporte.</p>
                        </div>

                        <!-- Teléfono bloqueado -->
                        <div>
                            <InputLabel for="p-telefono" value="Teléfono" />
                            <div class="mt-1 flex items-center gap-2">
                                <div class="flex flex-1 items-center rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
                                    <span class="flex-1 truncate text-sm text-gray-500">{{ user.telefono ?? '—' }}</span>
                                    <span v-if="user.telefono_verificado_at" class="ml-2 flex items-center gap-1 text-xs font-medium text-green-600">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                        </svg>
                                        Verificado
                                    </span>
                                    <span v-else class="ml-2 flex items-center gap-1 text-xs font-medium text-gray-400">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                        Sin verificar
                                    </span>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-400">El teléfono queda fijo una vez verificado. Contacta con soporte para modificarlo.</p>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <InputLabel for="p-edad" value="Edad" />
                                <TextInput id="p-edad" v-model="profileForm.edad" type="number" min="14" max="120" class="mt-1 block w-full" />
                                <InputError class="mt-1" :message="profileForm.errors.edad" />
                            </div>
                            <div>
                                <InputLabel for="p-direccion" value="Dirección" />
                                <TextInput id="p-direccion" v-model="profileForm.direccion" type="text" class="mt-1 block w-full" autocomplete="street-address" />
                                <InputError class="mt-1" :message="profileForm.errors.direccion" />
                            </div>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button
                                type="submit"
                                :disabled="profileForm.processing"
                                class="inline-flex items-center gap-2 rounded-lg bg-primary-500 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-600 disabled:opacity-50"
                            >
                                <svg v-if="profileForm.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                </svg>
                                {{ profileForm.processing ? 'Guardando...' : 'Guardar cambios' }}
                            </button>
                        </div>
                    </form>

                    <!-- ── Pestaña: Seguridad ─────────────────────────────── -->
                    <div v-if="activeTab === 'seguridad'" class="space-y-6">

                        <!-- Tarjeta de consejos -->
                        <div class="rounded-xl border border-blue-100 bg-blue-50 p-4">
                            <div class="flex items-start gap-3">
                                <div class="mt-0.5 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-blue-100">
                                    <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold text-blue-800">Consejos para una contraseña segura</h4>
                                    <ul class="mt-2 space-y-1 text-xs text-blue-700">
                                        <li class="flex items-center gap-1.5">
                                            <svg class="h-3 w-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                            Mínimo 8 caracteres, mejor 12 o más
                                        </li>
                                        <li class="flex items-center gap-1.5">
                                            <svg class="h-3 w-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                            Combina mayúsculas, minúsculas, números y símbolos
                                        </li>
                                        <li class="flex items-center gap-1.5">
                                            <svg class="h-3 w-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                            No uses la misma contraseña en otros sitios
                                        </li>
                                        <li class="flex items-center gap-1.5">
                                            <svg class="h-3 w-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                            Evita fechas de nacimiento o nombres propios
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Tarjeta de acceso reciente -->
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                                <p class="text-xs font-medium uppercase tracking-wide text-gray-400">Correo vinculado</p>
                                <p class="mt-1 truncate text-sm font-semibold text-gray-700">{{ user.email }}</p>
                            </div>
                            <div class="rounded-xl border border-green-100 bg-green-50 p-4">
                                <p class="text-xs font-medium uppercase tracking-wide text-green-500">Estado de la cuenta</p>
                                <p class="mt-1 flex items-center gap-1.5 text-sm font-semibold text-green-700">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                    </svg>
                                    Activa y protegida
                                </p>
                            </div>
                        </div>

                        <!-- Formulario cambio contraseña -->
                        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                            <h4 class="mb-4 text-sm font-semibold text-gray-700">Cambiar contraseña</h4>
                            <form @submit.prevent="updatePassword" class="space-y-4">
                                <div>
                                    <InputLabel for="s-current" value="Contraseña actual" />
                                    <TextInput id="s-current" v-model="passwordForm.current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
                                    <InputError class="mt-1" :message="passwordForm.errors.current_password" />
                                </div>
                                <div>
                                    <InputLabel for="s-new" value="Nueva contraseña" />
                                    <TextInput id="s-new" v-model="passwordForm.password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                                    <InputError class="mt-1" :message="passwordForm.errors.password" />
                                </div>
                                <div>
                                    <InputLabel for="s-confirm" value="Confirmar contraseña" />
                                    <TextInput id="s-confirm" v-model="passwordForm.password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                                    <InputError class="mt-1" :message="passwordForm.errors.password_confirmation" />
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
                                        {{ passwordForm.processing ? 'Actualizando...' : 'Actualizar contraseña' }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- ── Pestaña: Cuenta ───────────────────────────────── -->
                    <div v-if="activeTab === 'cuenta'" class="space-y-6">

                        <!-- Acceso rápido a pedidos -->
                        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-700">Mis pedidos</h4>
                                    <p class="mt-0.5 text-xs text-gray-400">Consulta el estado de tus pedidos activos e historial</p>
                                </div>
                                <Link
                                    :href="route('pedidos.index')"
                                    class="flex items-center gap-1.5 rounded-lg bg-primary-500 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-primary-600"
                                >
                                    Ver pedidos
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </Link>
                            </div>
                        </div>

                        <!-- ── Eliminar cuenta (al fondo) ─── -->
                        <div class="rounded-xl border border-red-100 bg-red-50 p-5">
                            <div class="flex items-start gap-3">
                                <div class="mt-0.5 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-red-100">
                                    <svg class="h-4 w-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm font-semibold text-red-700">Eliminar cuenta</h3>
                                    <p class="mt-1 text-sm text-red-600/80">
                                        Esta acción es permanente e irreversible. Se eliminarán todos tus datos, pedidos y cualquier información asociada.
                                    </p>

                                    <!-- Paso 0: botón inicial -->
                                    <div v-if="deleteStep === 0" class="mt-4">
                                        <button
                                            type="button"
                                            @click="deleteStep = 1"
                                            class="rounded-lg border border-red-300 px-4 py-2 text-sm font-medium text-red-600 transition hover:bg-red-100"
                                        >
                                            Quiero eliminar mi cuenta
                                        </button>
                                    </div>

                                    <!-- Paso 1: confirmar frase -->
                                    <div v-else-if="deleteStep === 1" class="mt-4 space-y-3">
                                        <p class="text-sm font-medium text-red-700">
                                            Para continuar, escribe exactamente:
                                            <span class="ml-1 rounded bg-red-100 px-1.5 py-0.5 font-mono text-red-800">{{ FRASE_BORRAR }}</span>
                                        </p>
                                        <input
                                            v-model="deletePhraseInput"
                                            type="text"
                                            placeholder="Escribe la frase aquí…"
                                            class="block w-full rounded-lg border border-red-200 bg-white px-3 py-2 text-sm focus:border-red-400 focus:outline-none focus:ring-1 focus:ring-red-400"
                                            :class="deletePhraseInput && !deletePhraseOk ? 'border-red-400 bg-red-50' : ''"
                                        />
                                        <p v-if="deletePhraseInput && !deletePhraseOk" class="text-xs text-red-500">
                                            La frase no coincide exactamente.
                                        </p>
                                        <div class="flex gap-3">
                                            <button
                                                type="button"
                                                @click="deleteStep = 0; deletePhraseInput = ''"
                                                class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-600 transition hover:bg-gray-100"
                                            >
                                                Cancelar
                                            </button>
                                            <button
                                                type="button"
                                                :disabled="!deletePhraseOk"
                                                @click="deleteStep = 2"
                                                class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700 disabled:opacity-40"
                                            >
                                                Continuar
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Paso 2: confirmar contraseña -->
                                    <div v-else-if="deleteStep === 2" class="mt-4 space-y-3">
                                        <p class="text-sm text-red-700 font-medium">Confirma tu contraseña para eliminar definitivamente la cuenta.</p>
                                        <TextInput
                                            id="del-pass"
                                            v-model="deleteForm.password"
                                            type="password"
                                            class="mt-1 block w-full"
                                            placeholder="Tu contraseña actual"
                                        />
                                        <InputError :message="deleteForm.errors.password" />
                                        <div class="flex gap-3 pt-1">
                                            <button
                                                type="button"
                                                @click="deleteStep = 0; deletePhraseInput = ''; deleteForm.reset()"
                                                class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-600 transition hover:bg-gray-100"
                                            >
                                                Cancelar
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
                                                Eliminar definitivamente
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
                <Link href="/" class="text-sm text-gray-400 transition hover:text-gray-600">← Volver al inicio</Link>
            </div>
        </div>

    </div>
</template>
