<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/LayoutPublico.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Recaptcha from '@/Components/Recaptcha.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const RECAPTCHA_SITE_KEY = usePage().props.recaptchaSiteKey;

const form = useForm({
    name: '',
    apellidos: '',
    telefono: '',
    email: '',
    fecha_nacimiento: '',
    direccion: '',
    accept_terms: false,
    password: '',
    password_confirmation: '',
    turnstile_token: '',
});

// Edad mínima 14, máxima 120
const today = new Date();
const maxFechaNacimiento = computed(() => {
    const d = new Date(today.getFullYear() - 14, today.getMonth(), today.getDate());
    return d.toISOString().split('T')[0];
});
const minFechaNacimiento = computed(() => {
    const d = new Date(today.getFullYear() - 120, today.getMonth(), today.getDate());
    return d.toISOString().split('T')[0];
});

const onVerify  = (token) => { form.turnstile_token = token; };
const onExpire  = ()      => { form.turnstile_token = ''; };
const onError   = ()      => { form.turnstile_token = ''; };

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Registrarse" />

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <p class="text-sm text-tierra-500 dark:text-tierra-400">Completa los datos para continuar.</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <InputLabel for="name" value="Nombre" />

                    <TextInput
                        id="name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.name"
                        required
                        autofocus
                        autocomplete="given-name"
                    />

                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div>
                    <InputLabel for="apellidos" value="Apellidos" />

                    <TextInput
                        id="apellidos"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.apellidos"
                        required
                        autocomplete="family-name"
                    />

                    <InputError class="mt-2" :message="form.errors.apellidos" />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <InputLabel for="telefono" value="Teléfono *" />

                    <TextInput
                        id="telefono"
                        type="tel"
                        class="mt-1 block w-full"
                        v-model="form.telefono"
                        required
                        autocomplete="tel"
                        placeholder="612345678"
                        pattern="[6-9][0-9]{8}"
                        title="9 dígitos comenzando por 6, 7, 8 o 9"
                    />

                    <InputError class="mt-2" :message="form.errors.telefono" />
                </div>

                <div>
                    <InputLabel for="fecha_nacimiento" value="Fecha de nacimiento *" />

                    <TextInput
                        id="fecha_nacimiento"
                        type="date"
                        class="mt-1 block w-full"
                        v-model="form.fecha_nacimiento"
                        required
                        :max="maxFechaNacimiento"
                        :min="minFechaNacimiento"
                    />
                    <p class="mt-1 text-xs text-tierra-500 dark:text-tierra-400">Debes tener al menos 14 años</p>
                    <InputError class="mt-2" :message="form.errors.fecha_nacimiento" />
                </div>
            </div>

            <div>
                <InputLabel for="email" value="Correo Electrónico *" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                    placeholder="ejemplo@gmail.com"
                />
                <p class="mt-1 text-xs text-tierra-500 dark:text-tierra-400">Solo dominios conocidos: gmail, outlook, hotmail, yahoo, icloud, etc.</p>
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="direccion" value="Dirección *" />

                <TextInput
                    id="direccion"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.direccion"
                    required
                    autocomplete="street-address"
                    placeholder="Calle, número, ciudad, CP"
                />

                <InputError class="mt-2" :message="form.errors.direccion" />
            </div>

            <div>
                <InputLabel for="password" value="Contraseña" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div>
                <InputLabel
                    for="password_confirmation"
                    value="Confirmar Contraseña"
                />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div class="flex items-center gap-2">
                <Checkbox name="accept_terms" v-model:checked="form.accept_terms" />
                <span class="text-sm text-tierra-700 dark:text-tierra-300">
                    Acepto los términos y condiciones de uso
                </span>
            </div>
            <InputError :message="form.errors.accept_terms" />

            <!-- reCAPTCHA -->
            <div>
                <Recaptcha
                    :sitekey="RECAPTCHA_SITE_KEY"
                    @verify="onVerify"
                    @expire="onExpire"
                    @error="onError"
                />
                <InputError class="mt-1" :message="form.errors.turnstile_token" />
            </div>

            <div class="flex flex-col-reverse items-start gap-3 pt-1 sm:flex-row sm:items-center sm:justify-between">
                <Link
                    :href="route('login')"
                    class="rounded-md text-sm text-tierra-600 dark:text-tierra-400 underline hover:text-tierra-900 dark:hover:text-tierra-200 focus:outline-none focus:ring-2 focus:ring-tierra-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                >
                    ¿Ya tienes cuenta? Inicia sesión
                </Link>

                <PrimaryButton
                    class="w-full justify-center sm:w-auto"
                    :class="{ 'opacity-25': form.processing || !form.turnstile_token }"
                    :disabled="form.processing || !form.turnstile_token"
                >
                    Crear cuenta
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
