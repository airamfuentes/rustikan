<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close']);

const activeTab = ref('login'); // 'login' o 'register'

// Formulario de Login
const loginForm = useForm({
    email: '',
    password: '',
    remember: false,
});

// Formulario de Registro
const registerForm = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submitLogin = () => {
    loginForm.post(route('login'), {
        onFinish: () => loginForm.reset('password'),
        onSuccess: () => emit('close'),
    });
};

const submitRegister = () => {
    registerForm.post(route('register'), {
        onFinish: () => registerForm.reset('password', 'password_confirmation'),
        onSuccess: () => emit('close'),
    });
};

const closeModal = () => {
    emit('close');
    loginForm.reset();
    registerForm.reset();
    activeTab.value = 'login';
};
</script>

<template>
    <!-- Overlay -->
    <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="show"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm px-4"
            @click.self="closeModal"
        >
            <!-- Modal -->
            <Transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0 scale-95"
                enter-to-class="opacity-100 scale-100"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-95"
            >
                <div
                    v-if="show"
                    class="relative w-full max-w-md rounded-2xl bg-white shadow-2xl"
                >
                    <!-- Botón cerrar -->
                    <button
                        @click="closeModal"
                        class="absolute right-4 top-4 rounded-lg p-1 text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-600"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <!-- Logo -->
                    <div class="flex justify-center pt-8">
                        <img src="/images/logo.png" alt="Rustikan" class="h-16 w-auto" />
                    </div>

                    <!-- Tabs -->
                    <div class="mt-6 flex border-b border-gray-200">
                        <button
                            @click="activeTab = 'login'"
                            :class="[
                                'flex-1 py-3 text-sm font-medium transition-colors',
                                activeTab === 'login'
                                    ? 'border-b-2 border-primary-500 text-primary-600'
                                    : 'text-gray-500 hover:text-gray-700'
                            ]"
                        >
                            Iniciar Sesión
                        </button>
                        <button
                            @click="activeTab = 'register'"
                            :class="[
                                'flex-1 py-3 text-sm font-medium transition-colors',
                                activeTab === 'register'
                                    ? 'border-b-2 border-primary-500 text-primary-600'
                                    : 'text-gray-500 hover:text-gray-700'
                            ]"
                        >
                            Registrarse
                        </button>
                    </div>

                    <!-- Contenido -->
                    <div class="p-6">
                        <!-- Formulario Login -->
                        <form v-if="activeTab === 'login'" @submit.prevent="submitLogin" class="space-y-4">
                            <div>
                                <InputLabel for="login-email" value="Correo Electrónico" />
                                <TextInput
                                    id="login-email"
                                    type="email"
                                    class="mt-1 block w-full"
                                    v-model="loginForm.email"
                                    required
                                    autofocus
                                    autocomplete="username"
                                />
                                <InputError class="mt-2" :message="loginForm.errors.email" />
                            </div>

                            <div>
                                <InputLabel for="login-password" value="Contraseña" />
                                <TextInput
                                    id="login-password"
                                    type="password"
                                    class="mt-1 block w-full"
                                    v-model="loginForm.password"
                                    required
                                    autocomplete="current-password"
                                />
                                <InputError class="mt-2" :message="loginForm.errors.password" />
                            </div>

                            <div class="flex items-center">
                                <Checkbox name="remember" v-model:checked="loginForm.remember" />
                                <span class="ml-2 text-sm text-gray-700">Recuérdame</span>
                            </div>

                            <button
                                type="submit"
                                :disabled="loginForm.processing"
                                :class="{ 'opacity-25': loginForm.processing }"
                                class="w-full rounded-lg bg-primary-500 px-4 py-3 text-sm font-semibold text-white transition-colors hover:bg-primary-600 disabled:cursor-not-allowed"
                            >
                                Iniciar Sesión
                            </button>

                            <div class="text-center">
                                <a href="#" class="text-sm text-primary-600 hover:text-primary-700">
                                    ¿Olvidaste tu contraseña?
                                </a>
                            </div>
                        </form>

                        <!-- Formulario Registro -->
                        <form v-else @submit.prevent="submitRegister" class="space-y-4">
                            <div>
                                <InputLabel for="register-name" value="Nombre" />
                                <TextInput
                                    id="register-name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="registerForm.name"
                                    required
                                    autofocus
                                    autocomplete="name"
                                />
                                <InputError class="mt-2" :message="registerForm.errors.name" />
                            </div>

                            <div>
                                <InputLabel for="register-email" value="Correo Electrónico" />
                                <TextInput
                                    id="register-email"
                                    type="email"
                                    class="mt-1 block w-full"
                                    v-model="registerForm.email"
                                    required
                                    autocomplete="username"
                                />
                                <InputError class="mt-2" :message="registerForm.errors.email" />
                            </div>

                            <div>
                                <InputLabel for="register-password" value="Contraseña" />
                                <TextInput
                                    id="register-password"
                                    type="password"
                                    class="mt-1 block w-full"
                                    v-model="registerForm.password"
                                    required
                                    autocomplete="new-password"
                                />
                                <InputError class="mt-2" :message="registerForm.errors.password" />
                            </div>

                            <div>
                                <InputLabel for="register-password-confirmation" value="Confirmar Contraseña" />
                                <TextInput
                                    id="register-password-confirmation"
                                    type="password"
                                    class="mt-1 block w-full"
                                    v-model="registerForm.password_confirmation"
                                    required
                                    autocomplete="new-password"
                                />
                                <InputError class="mt-2" :message="registerForm.errors.password_confirmation" />
                            </div>

                            <button
                                type="submit"
                                :disabled="registerForm.processing"
                                :class="{ 'opacity-25': registerForm.processing }"
                                class="w-full rounded-lg bg-primary-500 px-4 py-3 text-sm font-semibold text-white transition-colors hover:bg-primary-600 disabled:cursor-not-allowed"
                            >
                                Registrarse
                            </button>
                        </form>
                    </div>
                </div>
            </Transition>
        </div>
    </Transition>
</template>
