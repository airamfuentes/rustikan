<script setup>
import { ref, computed } from 'vue';
import GuestLayout from '@/Layouts/LayoutPublico.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    status: { type: String },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

// ── Formulario del código ─────────────────────────────────────────────────────
const digits  = ref(['', '', '', '', '', '']);
const inputs  = ref([]);
const codeErr = ref('');

const codeCompleto = computed(() => digits.value.every(d => d !== ''));

const onDigitInput = (i, e) => {
    const val = e.target.value.replace(/\D/g, '').slice(-1);
    digits.value[i] = val;
    if (val && i < 5) {
        inputs.value[i + 1]?.focus();
    }
};

const onKeydown = (i, e) => {
    if (e.key === 'Backspace' && !digits.value[i] && i > 0) {
        inputs.value[i - 1]?.focus();
    }
};

const onPaste = (e) => {
    const text = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
    if (text.length) {
        text.split('').forEach((ch, i) => { digits.value[i] = ch; });
        inputs.value[Math.min(text.length, 5)]?.focus();
    }
    e.preventDefault();
};

// ── Envío del código ─────────────────────────────────────────────────────────
const verifyForm = useForm({ code: '' });

const submitCode = () => {
    if (!codeCompleto.value) { codeErr.value = 'Introduce los 6 dígitos.'; return; }
    codeErr.value = '';
    verifyForm.code = digits.value.join('');
    verifyForm.post(route('verification.verify-code'), {
        onError: (e) => {
            codeErr.value = e.code ?? 'Código incorrecto. Inténtalo de nuevo.';
            digits.value = ['', '', '', '', '', ''];
            inputs.value[0]?.focus();
        },
    });
};

// ── Reenvío ───────────────────────────────────────────────────────────────────
const resendForm  = useForm({});
const sent        = computed(() => props.status === 'verification-link-sent');
const submitResend = () => resendForm.post(route('verification.send'));
</script>

<template>
    <GuestLayout>
        <Head title="Verifica tu correo – Rustikan" />

        <div class="flex flex-col items-center text-center">
            <!-- Icono -->
            <div class="mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900/30">
                <svg class="h-8 w-8 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                </svg>
            </div>

            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Verifica tu correo electrónico</h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-xs">
                Hemos enviado un código de 6 dígitos a
                <span class="font-semibold text-gray-700 dark:text-gray-300">{{ user?.email }}</span>.
                Introdúcelo aquí para activar tu cuenta.
            </p>
        </div>

        <!-- ── Inputs OTP ───────────────────────────────────────────────────── -->
        <div class="mt-6">
            <div class="flex justify-center gap-2" @paste="onPaste">
                <input
                    v-for="i in 6"
                    :key="i"
                    :ref="el => { if (el) inputs[i - 1] = el }"
                    v-model="digits[i - 1]"
                    type="text"
                    inputmode="numeric"
                    maxlength="1"
                    @input="onDigitInput(i - 1, $event)"
                    @keydown="onKeydown(i - 1, $event)"
                    :class="[
                        'h-14 w-11 rounded-xl border-2 text-center text-xl font-bold outline-none transition-all focus:scale-105',
                        digits[i - 1]
                            ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300'
                            : 'border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white',
                        codeErr ? 'border-red-400 dark:border-red-500' : '',
                        'focus:border-primary-400 focus:ring-2 focus:ring-primary-200 dark:focus:ring-primary-800',
                    ]"
                />
            </div>

            <!-- Error -->
            <p v-if="codeErr" class="mt-2 text-center text-xs text-red-500">{{ codeErr }}</p>
        </div>

        <!-- ── Botón verificar ──────────────────────────────────────────────── -->
        <div class="mt-5 flex flex-col gap-3">
            <button
                @click="submitCode"
                :disabled="verifyForm.processing || !codeCompleto"
                class="w-full rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-600 disabled:opacity-50 transition-colors"
            >
                {{ verifyForm.processing ? 'Verificando…' : 'Verificar cuenta' }}
            </button>

            <!-- Éxito reenvío -->
            <div v-if="sent"
                 class="rounded-xl bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 px-4 py-3 text-sm font-medium text-green-700 dark:text-green-400 flex items-center gap-2">
                <svg class="h-4 w-4 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                    <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" />
                </svg>
                Nuevo código enviado. Revisa tu bandeja de entrada.
            </div>

            <button
                @click="submitResend"
                :disabled="resendForm.processing"
                class="w-full rounded-xl border border-gray-200 dark:border-gray-600 px-4 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors disabled:opacity-50"
            >
                {{ resendForm.processing ? 'Enviando…' : 'Reenviar código' }}
            </button>

            <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="w-full rounded-xl border border-gray-200 dark:border-gray-600 px-4 py-2.5 text-sm font-medium text-gray-500 dark:text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
            >
                Cerrar sesión
            </Link>
        </div>

        <p class="mt-4 text-center text-xs text-gray-400">
            El código expira en 24 horas.
        </p>
    </GuestLayout>
</template>
