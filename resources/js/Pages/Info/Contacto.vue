<template>
    <div class="min-h-screen bg-white dark:bg-gray-900">
        <Head>
            <title>Contacto – Rustikan · Hablemos sobre productos locales de Lanzarote</title>
            <meta name="description" content="¿Tienes una duda o quieres colaborar con Rustikan? Escríbenos a info@rustikan.com o usa el formulario. Respondemos en menos de 48 horas hábiles.">
        </Head>
        <NavbarPublico />

        <!-- Los toasts se muestran via ToastContainer global -->

        <!-- Hero -->
        <section class="bg-gradient-to-br from-primary-600 via-primary-700 to-primary-900 pt-32 pb-20 text-white">
            <div class="mx-auto max-w-4xl px-4 text-center">
                <div class="mb-6 inline-flex h-16 w-16 items-center justify-center rounded-full bg-white/10 backdrop-blur-sm">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                    </svg>
                </div>
                <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">{{ t('info.contact.title') }}</h1>
                <p class="mt-4 text-lg text-primary-100 max-w-2xl mx-auto">
                    {{ t('info.contact.subtitle') }}
                </p>
            </div>
        </section>

        <!-- Contenido -->
        <section class="py-16 bg-white dark:bg-gray-900">
            <div class="mx-auto max-w-5xl px-4 sm:px-6">
                <div class="grid gap-10 lg:grid-cols-2">

                    <!-- Formulario -->
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">{{ t('info.contact.form_title') }}</h2>
                        <form @submit.prevent="enviar" class="space-y-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('info.contact.name') }}</label>
                                <input v-model="form.nombre" type="text" required minlength="2" maxlength="100" v-only-letters :placeholder="t('info.contact.name_placeholder')"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500" />
                                <p v-if="form.errors.nombre" class="mt-1 text-xs text-red-500">{{ form.errors.nombre }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('info.contact.email') }}</label>
                                <input v-model="form.email" type="email" required maxlength="150" placeholder="tu@email.com"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500" />
                                <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('info.contact.subject') }}</label>
                                <select v-model="form.asunto" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                    <option value="">{{ t('info.contact.subject_placeholder') }}</option>
                                    <option value="productor">{{ t('info.contact.subject_producer') }}</option>
                                    <option value="repartidor">{{ t('info.contact.subject_delivery') }}</option>
                                    <option value="soporte">{{ t('info.contact.subject_support') }}</option>
                                    <option value="colaboracion">{{ t('info.contact.subject_collaboration') }}</option>
                                    <option value="otro">{{ t('info.contact.subject_other') }}</option>
                                </select>
                                <p v-if="form.errors.asunto" class="mt-1 text-xs text-red-500">{{ form.errors.asunto }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('info.contact.message') }}</label>
                                <textarea v-model="form.mensaje" required minlength="10" maxlength="2000" rows="5" :placeholder="t('info.contact.message_placeholder')"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
                                <div class="mt-1 flex items-center justify-between">
                                    <p v-if="form.errors.mensaje" class="text-xs text-red-500">{{ form.errors.mensaje }}</p>
                                    <p class="ml-auto text-xs text-gray-400">{{ form.mensaje.length }}/2000</p>
                                </div>
                            </div>
                            <button type="submit" :disabled="form.processing"
                                class="w-full rounded-lg bg-primary-600 px-6 py-3 font-semibold text-white hover:bg-primary-700 disabled:opacity-50 transition-colors">
                                {{ form.processing ? t('info.contact.sending') : t('info.contact.send') }}
                            </button>
                        </form>
                    </div>

                    <!-- Info de contacto -->
                    <div class="space-y-6">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">{{ t('info.contact.info_title') }}</h2>

                        <div class="flex gap-4 items-start p-5 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900/40 text-primary-600 dark:text-primary-400 flex-shrink-0">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">Email</p>
                                <a href="mailto:info@rustikan.com" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">info@rustikan.com</a>
                            </div>
                        </div>

                        <div class="flex gap-4 items-start p-5 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900/40 text-primary-600 dark:text-primary-400 flex-shrink-0">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">{{ t('general.location', 'Ubicación') }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ t('info.contact.location') }}</p>
                            </div>
                        </div>

                        <div class="flex gap-4 items-start p-5 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900/40 text-primary-600 dark:text-primary-400 flex-shrink-0">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 0C5.374 0 0 5.373 0 12c0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23A11.509 11.509 0 0112 5.803c1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576C20.566 21.797 24 17.3 24 12c0-6.627-5.373-12-12-12z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">GitHub</p>
                                <a href="https://github.com/airamfuentes" target="_blank" rel="noopener noreferrer" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">github.com/airamfuentes</a>
                            </div>
                        </div>

                        <div class="mt-6 rounded-xl bg-orange-50 dark:bg-orange-900/20 border border-orange-100 dark:border-orange-800 p-5">
                            <p class="text-sm font-semibold text-orange-900 dark:text-orange-300 mb-1">{{ t('info.contact.rusti_title') }}</p>
                            <p class="text-sm text-orange-700 dark:text-orange-400">{{ t('info.contact.rusti_desc') }}</p>
                        </div>

                        <div class="mt-4 rounded-xl bg-primary-50 dark:bg-primary-900/20 border border-primary-100 dark:border-primary-800 p-5">
                            <p class="text-sm font-semibold text-primary-900 dark:text-primary-300 mb-1">{{ t('info.contact.response_time_title') }}</p>
                            <p class="text-sm text-primary-700 dark:text-primary-400">{{ t('info.contact.response_time_desc') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <FooterPublico />
    </div>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import NavbarPublico from '@/Components/NavbarPublico.vue';
import FooterPublico from '@/Components/FooterPublico.vue';
import { useDarkMode } from '@/Composables/useDarkMode';
import { useI18n } from '@/Composables/useI18n';
import { useToasts } from '@/Composables/useToasts';
import { CheckCircle2 } from 'lucide-vue-next';
useDarkMode();

const { t } = useI18n();
const { error: toastError } = useToasts();

const form = useForm({ nombre: '', email: '', asunto: '', mensaje: '' });

const enviar = () => {
    form.post(route('info.contacto.store'), {
        onSuccess: () => form.reset(),
        onError: () => toastError(t('general.form_errors_title'), t('general.form_errors_msg')),
    });
};
</script>
