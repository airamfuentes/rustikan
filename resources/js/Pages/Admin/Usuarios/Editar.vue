<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Editar Usuario</h2>
                <Link :href="route('admin.usuarios.index')" class="rounded-lg bg-gray-600 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">
                    Volver
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6">
                        <div class="space-y-6">
                            <!-- Foto de perfil -->
                            <div class="rounded-lg bg-gray-50 p-4">
                                <h3 class="mb-3 text-sm font-semibold text-gray-700">Foto de perfil</h3>
                                <div class="flex items-center gap-5">
                                    <div class="relative shrink-0">
                                        <img
                                            v-if="avatarPreview"
                                            :src="avatarPreview"
                                            alt="Avatar"
                                            class="h-20 w-20 rounded-full object-cover ring-2 ring-gray-200"
                                        />
                                        <div
                                            v-else
                                            class="flex h-20 w-20 items-center justify-center rounded-full bg-primary-600 text-2xl font-bold text-white"
                                        >
                                            {{ usuario.name.charAt(0).toUpperCase() }}
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-2">
                                        <label class="cursor-pointer inline-flex items-center gap-2 rounded-lg border border-primary-300 bg-primary-50 px-3 py-1.5 text-sm font-medium text-primary-700 hover:bg-primary-100 transition-colors">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                            Cambiar foto
                                            <input type="file" accept="image/*" class="hidden" @change="onAvatarChange" />
                                        </label>
                                        <button
                                            v-if="avatarPreview"
                                            type="button"
                                            @click="removeAvatar"
                                            class="inline-flex items-center gap-2 rounded-lg border border-red-200 bg-white px-3 py-1.5 text-sm font-medium text-red-600 hover:bg-red-50 transition-colors"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            Eliminar foto
                                        </button>
                                        <p class="text-xs text-gray-400">JPG, PNG o GIF · Máx. 2MB</p>
                                    </div>
                                </div>
                                <p v-if="form.errors.avatar" class="mt-2 text-sm text-red-600">{{ form.errors.avatar }}</p>
                                <div class="mt-3 border-t border-gray-200 pt-3">
                                    <p class="text-xs text-gray-500">Usuario desde <span class="font-medium text-gray-700">{{ new Date(usuario.created_at).toLocaleDateString('es-ES') }}</span></p>
                                </div>
                            </div>

                            <!-- Nombre -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">Nombre Completo *</label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    required
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                />
                                <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">Email *</label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    required
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                />
                                <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                            </div>

                            <!-- Rol -->
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">Rol *</label>
                                <select
                                    v-model="form.role"
                                    required
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                >
                                    <option value="user">Usuario</option>
                                    <option value="admin">Administrador</option>
                                </select>
                                <p v-if="form.errors.role" class="mt-1 text-sm text-red-600">{{ form.errors.role }}</p>
                                <p class="mt-1 text-xs text-gray-500">Los administradores tienen acceso completo al panel de administración.</p>
                            </div>

                            <!-- Cambiar Contraseña (Opcional) -->
                            <div class="rounded-lg border-2 border-dashed border-gray-300 p-4">
                                <h3 class="mb-3 text-sm font-semibold text-gray-700">Cambiar Contraseña (Opcional)</h3>
                                <p class="mb-4 text-xs text-gray-500">Deja estos campos vacíos si no deseas cambiar la contraseña.</p>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-gray-700">Nueva Contraseña</label>
                                        <input
                                            v-model="form.password"
                                            type="password"
                                            autocomplete="new-password"
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                        />
                                        <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
                                    </div>

                                    <div>
                                        <label class="mb-2 block text-sm font-medium text-gray-700">Confirmar Contraseña</label>
                                        <input
                                            v-model="form.password_confirmation"
                                            type="password"
                                            autocomplete="new-password"
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Estadísticas del Usuario -->
                            <div class="rounded-lg bg-blue-50 p-4">
                                <h3 class="mb-2 text-sm font-semibold text-blue-900">Estadísticas</h3>
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <p class="text-blue-600">Total de Pedidos</p>
                                        <p class="text-lg font-bold text-blue-900">{{ usuario.pedidos_count || 0 }}</p>
                                    </div>
                                    <div>
                                        <p class="text-blue-600">Total Gastado</p>
                                        <p class="text-lg font-bold text-blue-900">{{ Number(usuario.pedidos_sum || 0).toFixed(2) }}€</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="mt-8 flex items-center justify-end gap-3 border-t pt-6">
                            <Link
                                :href="route('admin.usuarios.index')"
                                class="rounded-lg border border-gray-300 bg-white px-6 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                            >
                                Cancelar
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-lg bg-primary-600 px-6 py-2 text-sm font-semibold text-white hover:bg-primary-700 disabled:opacity-50"
                            >
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
import { ref } from 'vue';

const props = defineProps({
    usuario: Object,
});

const avatarPreview = ref(props.usuario.avatar ? '/storage/' + props.usuario.avatar : null);

const form = useForm({
    name: props.usuario.name,
    email: props.usuario.email,
    role: props.usuario.role,
    password: '',
    password_confirmation: '',
    avatar: null,
    delete_avatar: false,
});

const onAvatarChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    form.avatar = file;
    form.delete_avatar = false;
    avatarPreview.value = URL.createObjectURL(file);
};

const removeAvatar = () => {
    form.avatar = null;
    form.delete_avatar = true;
    avatarPreview.value = null;
};

const submit = () => {
    form.transform((data) => ({ ...data, _method: 'PUT' }))
        .post(route('admin.usuarios.update', props.usuario.id), {
            preserveScroll: true,
            forceFormData: true,
        });
};
</script>
