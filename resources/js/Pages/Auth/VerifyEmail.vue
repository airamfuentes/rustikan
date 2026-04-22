<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/LayoutPublico.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: { type: String },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const sent = computed(() => props.status === 'verification-link-sent');
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
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-sm">
                Hemos enviado un enlace de verificación a tu email. Haz clic en ese enlace para activar tu cuenta.
            </p>

            <!-- Éxito: correo reenviado -->
            <div v-if="sent"
                 class="mt-4 w-full rounded-xl bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 px-4 py-3 text-sm font-medium text-green-700 dark:text-green-400 flex items-center gap-2">
                <svg class="h-4 w-4 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                    <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" />
                </svg>
                Enlace de verificación reenviado. Revisa tu bandeja de entrada.
            </div>
        </div>

        <form @submit.prevent="submit" class="mt-6 flex flex-col gap-3">
            <button
                type="submit"
                :disabled="form.processing"
                class="w-full rounded-xl bg-primary-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-600 disabled:opacity-50 transition-colors"
            >
                {{ form.processing ? 'Enviando...' : 'Reenviar enlace de verificación' }}
            </button>

            <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="w-full rounded-xl border border-gray-200 dark:border-gray-600 px-4 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
            >
                Cerrar sesión
            </Link>
        </form>
    </GuestLayout>
</template>
