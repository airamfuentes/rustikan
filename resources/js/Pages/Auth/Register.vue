<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/LayoutPublico.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Recaptcha from '@/Components/Recaptcha.vue';
import PasswordStrength from '@/Components/PasswordStrength.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { useI18n } from '@/Composables/useI18n';
import {
    validarNombre, validarEmail, validarCP,
    evaluarPassword,
} from '@/Composables/useValidaciones';

const { t } = useI18n();
const RECAPTCHA_SITE_KEY = usePage().props.recaptchaSiteKey;

const form = useForm({
    name: '',
    apellidos: '',
    telefono: '',
    email: '',
    fecha_nacimiento: '',
    calle: '',
    numero: '',
    puerta: '',
    cp: '',
    localidad: '',
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
    // For Spain (+34) send only the 9 digits — backend validates without prefix
    form.telefono = prefijo.value === '+34'
        ? telefonoNumero.value
        : prefijo.value + telefonoNumero.value;
};
watch(prefijo, () => {
    form.telefono = prefijo.value === '+34'
        ? telefonoNumero.value
        : prefijo.value + telefonoNumero.value;
});

// CP auto-lookup
const buscandoLocalidad = ref(false);
const isLanzaroteCP = (cp) => { const n = parseInt(cp, 10); return n >= 35500 && n <= 35599; };
watch(() => form.cp, async (cp) => {
    if (!/^\d{5}$/.test(cp)) { form.localidad = ''; return; }
    if (!isLanzaroteCP(cp)) { form.localidad = ''; return; }
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
            if (localidad) form.localidad = localidad;
        }
    } catch { /* silently fail */ } finally {
        buscandoLocalidad.value = false;
    }
});

const erroresLocales = ref({});

const validarLocal = () => {
    const e = {};
    const nombreErr = validarNombre(form.name, { min: 2, max: 60 });
    if (nombreErr) e.name = nombreErr;
    const apellidosErr = validarNombre(form.apellidos, { min: 2, max: 80 });
    if (apellidosErr) e.apellidos = apellidosErr;
    if (!telefonoNumero.value) {
        e.telefono = 'El teléfono es obligatorio.';
    } else if (prefijo.value === '+34') {
        const digits = telefonoNumero.value.replace(/\D/g, '');
        if (!/^[6-9]\d{8}$/.test(digits)) e.telefono = 'El teléfono debe tener 9 dígitos y empezar por 6, 7, 8 o 9.';
    }
    const emailErr = validarEmail(form.email);
    if (emailErr) e.email = emailErr;
    if (!form.fecha_nacimiento) e.fecha_nacimiento = 'La fecha de nacimiento es obligatoria.';
    if (!form.calle.trim() || form.calle.trim().length < 3) e.calle = 'La calle es obligatoria (mínimo 3 caracteres).';
    if (!form.numero.trim()) e.numero = 'El número es obligatorio.';
    const cpErr = validarCP(form.cp, { soloLanzarote: true });
    if (cpErr) e.cp = cpErr;
    if (!form.localidad.trim()) e.localidad = 'La localidad es obligatoria.';
    const pw = evaluarPassword(form.password);
    if (!pw.valida) e.password = 'La contraseña debe tener 8+ caracteres, mayúsculas, minúsculas, números y símbolos.';
    if (form.password !== form.password_confirmation) e.password_confirmation = 'Las contraseñas no coinciden.';
    if (!form.accept_terms) e.accept_terms = 'Debes aceptar los términos.';
    erroresLocales.value = e;
    return Object.keys(e).length === 0;
};

const submit = () => {
    if (!validarLocal()) return;
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
                        minlength="2"
                        maxlength="60"
                        v-only-letters
                    />

                    <InputError class="mt-2" :message="erroresLocales.name || form.errors.name" />
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
                        minlength="2"
                        maxlength="80"
                        v-only-letters
                    />

                    <InputError class="mt-2" :message="erroresLocales.apellidos || form.errors.apellidos" />
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

                    <InputError class="mt-2" :message="erroresLocales.telefono || form.errors.telefono" />
                    <p v-if="prefijo === '+34'" class="mt-1 text-xs text-gray-400">9 dígitos. Empieza por 6, 7, 8 o 9.</p>
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
                    <InputError class="mt-2" :message="erroresLocales.fecha_nacimiento || form.errors.fecha_nacimiento" />
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
                    maxlength="255"
                    placeholder="ejemplo@gmail.com"
                />
                <InputError class="mt-2" :message="erroresLocales.email || form.errors.email" />
            </div>

            <!-- Dirección -->
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="sm:col-span-2">
                    <InputLabel for="calle" value="Calle / Avenida *" />
                    <TextInput id="calle" type="text" class="mt-1 block w-full" v-model="form.calle"
                        required autocomplete="address-line1" maxlength="100" placeholder="Calle Ejemplo" />
                    <InputError class="mt-2" :message="erroresLocales.calle || form.errors.calle" />
                </div>
                <div>
                    <InputLabel for="numero" value="Número *" />
                    <TextInput id="numero" type="text" class="mt-1 block w-full" v-model="form.numero"
                        required maxlength="10" placeholder="12" />
                    <InputError class="mt-2" :message="erroresLocales.numero || form.errors.numero" />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <InputLabel for="puerta" value="Piso / Puerta" />
                    <TextInput id="puerta" type="text" class="mt-1 block w-full" v-model="form.puerta"
                        maxlength="20" placeholder="2ºA" />
                    <InputError class="mt-2" :message="form.errors.puerta" />
                </div>
                <div>
                    <InputLabel for="cp" value="Código postal *" />
                    <div class="relative mt-1">
                        <TextInput id="cp" type="text" class="block w-full" v-model="form.cp"
                            required maxlength="5" inputmode="numeric" placeholder="35500" />
                        <span v-if="buscandoLocalidad" class="absolute right-2 top-1/2 -translate-y-1/2">
                            <svg class="animate-spin h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                            </svg>
                        </span>
                    </div>
                    <InputError class="mt-2" :message="erroresLocales.cp || form.errors.cp" />
                </div>
                <div>
                    <InputLabel for="localidad" value="Localidad *" />
                    <TextInput id="localidad" type="text" class="mt-1 block w-full" v-model="form.localidad"
                        required maxlength="100" placeholder="Arrecife" />
                    <InputError class="mt-2" :message="erroresLocales.localidad || form.errors.localidad" />
                </div>
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
                    minlength="8"
                    maxlength="128"
                />

                <InputError class="mt-2" :message="erroresLocales.password || form.errors.password" />
                <PasswordStrength :password="form.password" />
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
                    minlength="8"
                    maxlength="128"
                />

                <InputError
                    class="mt-2"
                    :message="erroresLocales.password_confirmation || form.errors.password_confirmation"
                />
            </div>

            <div class="flex items-center gap-2">
                <Checkbox name="accept_terms" v-model:checked="form.accept_terms" />
                <span class="text-sm text-tierra-700 dark:text-tierra-300">{{ t('auth.accept_terms') }}</span>
            </div>
            <InputError :message="erroresLocales.accept_terms || form.errors.accept_terms" />

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
