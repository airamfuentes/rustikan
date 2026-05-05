<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/LayoutPublico.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Recaptcha from '@/Components/Recaptcha.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { useI18n } from '@/Composables/useI18n';

const { t } = useI18n();
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

const paises = [
    { bandera: '🇪🇸', prefijo: '+34', nombre: 'España',           maxLen: 9 },
    { bandera: '🇵🇹', prefijo: '+351', nombre: 'Portugal',         maxLen: 9 },
    { bandera: '🇫🇷', prefijo: '+33',  nombre: 'Francia',          maxLen: 9 },
    { bandera: '🇩🇪', prefijo: '+49',  nombre: 'Alemania',         maxLen: 11 },
    { bandera: '🇮🇹', prefijo: '+39',  nombre: 'Italia',           maxLen: 10 },
    { bandera: '🇬🇧', prefijo: '+44',  nombre: 'Reino Unido',      maxLen: 10 },
    { bandera: '🇺🇸', prefijo: '+1',   nombre: 'EE. UU. / Canadá', maxLen: 10 },
    { bandera: '🇲🇽', prefijo: '+52',  nombre: 'México',           maxLen: 10 },
    { bandera: '🇦🇷', prefijo: '+54',  nombre: 'Argentina',        maxLen: 10 },
    { bandera: '🇧🇷', prefijo: '+55',  nombre: 'Brasil',           maxLen: 11 },
    { bandera: '🇨🇴', prefijo: '+57',  nombre: 'Colombia',         maxLen: 10 },
    { bandera: '🇳🇱', prefijo: '+31',  nombre: 'Países Bajos',     maxLen: 9 },
    { bandera: '🇧🇪', prefijo: '+32',  nombre: 'Bélgica',          maxLen: 9 },
    { bandera: '🇨🇭', prefijo: '+41',  nombre: 'Suiza',            maxLen: 9 },
];
const prefijo         = ref('+34');
const telefonoNumero  = ref('');
const maxLenTelefono  = computed(() => paises.find(p => p.prefijo === prefijo.value)?.maxLen ?? 15);

const onTelefonoInput = (e) => {
    telefonoNumero.value = e.target.value.replace(/\D/g, '').slice(0, maxLenTelefono.value);
    form.telefono = prefijo.value + telefonoNumero.value;
};
watch(prefijo, () => { form.telefono = prefijo.value + telefonoNumero.value; });

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head :title="t('auth.register')" />

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <p class="text-sm text-tierra-500 dark:text-tierra-400">{{ t('auth.complete_data') }}</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <InputLabel for="name" :value="t('auth.name_label')" />

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
                    <InputLabel for="apellidos" :value="t('auth.surname_label')" />

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
                    <InputLabel for="telefono" :value="t('auth.phone_label') + ' *'" />

                    <div class="mt-1 flex overflow-hidden rounded-lg border border-gray-300 dark:border-gray-600 focus-within:border-tierra-500 focus-within:ring-2 focus-within:ring-tierra-500/30">
                        <select
                            v-model="prefijo"
                            class="shrink-0 cursor-pointer appearance-none border-r border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 px-2 py-2.5 text-sm text-gray-700 dark:text-gray-300 focus:outline-none"
                        >
                            <option v-for="p in paises" :key="p.prefijo" :value="p.prefijo">{{ p.bandera }} {{ p.prefijo }}</option>
                        </select>
                        <input
                            id="telefono"
                            type="tel"
                            :value="telefonoNumero"
                            @input="onTelefonoInput"
                            required
                            autocomplete="tel-national"
                            inputmode="numeric"
                            :placeholder="prefijo === '+34' ? '612345678' : ''"
                            :maxlength="maxLenTelefono"
                            class="flex-1 bg-white dark:bg-gray-800 px-3 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none"
                        />
                    </div>

                    <InputError class="mt-2" :message="form.errors.telefono" />
                </div>

                <div>
                    <InputLabel for="fecha_nacimiento" :value="t('auth.birthdate_label') + ' *'" />

                    <TextInput
                        id="fecha_nacimiento"
                        type="date"
                        class="mt-1 block w-full"
                        v-model="form.fecha_nacimiento"
                        required
                        :max="maxFechaNacimiento"
                        :min="minFechaNacimiento"
                    />
                    <InputError class="mt-2" :message="form.errors.fecha_nacimiento" />
                </div>
            </div>

            <div>
                <InputLabel for="email" :value="t('auth.email_label') + ' *'" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                    placeholder="ejemplo@gmail.com"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="direccion" :value="t('auth.address_label') + ' *'" />

                <TextInput
                    id="direccion"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.direccion"
                    required
                    autocomplete="street-address"
                    :placeholder="t('auth.address_placeholder')"
                />

                <InputError class="mt-2" :message="form.errors.direccion" />
            </div>

            <div>
                <InputLabel for="password" :value="t('auth.password')" />

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
                    :value="t('auth.confirm_password_label')"
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
                <span class="text-sm text-tierra-700 dark:text-tierra-300">{{ t('auth.accept_terms') }}</span>
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
                    {{ t('auth.have_account_link') }}
                </Link>

                <PrimaryButton
                    class="w-full justify-center sm:w-auto"
                    :class="{ 'opacity-25': form.processing || !form.turnstile_token }"
                    :disabled="form.processing || !form.turnstile_token"
                >
                    {{ t('auth.register_btn') }}
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
