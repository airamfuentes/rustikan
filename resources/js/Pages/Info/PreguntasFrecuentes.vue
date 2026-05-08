<template>
    <div class="min-h-screen bg-white dark:bg-gray-900">
        <Head :title="t('info.faq.title')" />
        <NavbarPublico />

        <!-- Hero -->
        <section class="bg-gradient-to-br from-teal-600 via-teal-700 to-cyan-800 pt-32 pb-20 text-white">
            <div class="mx-auto max-w-4xl px-4 text-center">
                <div class="mb-6 inline-flex h-16 w-16 items-center justify-center rounded-full bg-white/10 backdrop-blur-sm">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z"/>
                    </svg>
                </div>
                <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">{{ t('info.faq.title') }}</h1>
                <p class="mt-4 text-lg text-teal-100 max-w-2xl mx-auto">{{ t('info.faq.subtitle') }}</p>
            </div>
        </section>

        <!-- FAQ -->
        <section class="py-16 bg-white dark:bg-gray-900">
            <div class="mx-auto max-w-3xl px-4 sm:px-6">

                <div v-for="(seccion, si) in faqs" :key="si" class="mb-12">
                    <h2 class="text-lg font-bold text-primary-600 dark:text-primary-400 uppercase tracking-wider mb-5">{{ seccion.categoria }}</h2>
                    <div class="space-y-3">
                        <div
                            v-for="(item, i) in seccion.items"
                            :key="i"
                            class="rounded-xl border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 overflow-hidden"
                        >
                            <button
                                type="button"
                                @click="toggleFaq(si, i)"
                                class="w-full flex items-center justify-between gap-4 px-5 py-4 text-left hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                            >
                                <span class="font-medium text-gray-900 dark:text-white text-sm">{{ item.q }}</span>
                                <svg
                                    class="h-5 w-5 text-gray-400 flex-shrink-0 transition-transform duration-200"
                                    :class="isOpen(si, i) ? 'rotate-180' : ''"
                                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                                </svg>
                            </button>
                            <div v-if="isOpen(si, i)" class="px-5 pb-5 text-sm text-gray-600 dark:text-gray-400 leading-relaxed border-t border-gray-100 dark:border-gray-700 pt-4">
                                {{ item.a }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CTA -->
                <div class="mt-8 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800 p-6 text-center">
                    <p class="text-sm text-gray-700 dark:text-gray-300 mb-4">{{ t('info.faq.no_answer') }}</p>
                    <div class="flex flex-wrap items-center justify-center gap-3">
                        <Link :href="route('info.contacto')" class="inline-flex items-center gap-2 rounded-full bg-indigo-600 text-white px-6 py-2.5 font-semibold text-sm hover:bg-indigo-700 transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            {{ t('info.faq.contact_us') }}
                        </Link>
                        <button @click="abrirRusti" class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-orange-500 to-pink-500 text-white px-6 py-2.5 font-semibold text-sm hover:from-orange-600 hover:to-pink-600 transition-colors shadow">
                            <span class="text-base">🤖</span>
                            {{ t('info.faq.ask_rusti') }}
                        </button>
                    </div>
                    <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">Rusti es nuestro asistente IA disponible 24/7 para resolver tus dudas al instante.</p>
                </div>

            </div>
        </section>

        <FooterPublico />
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import NavbarPublico from '@/Components/NavbarPublico.vue';
import FooterPublico from '@/Components/FooterPublico.vue';
import { useDarkMode } from '@/Composables/useDarkMode';
import { useI18n } from '@/Composables/useI18n';
useDarkMode();

const { t } = useI18n();

const openItems = ref(new Set());

const key = (si, i) => `${si}-${i}`;
const isOpen    = (si, i) => openItems.value.has(key(si, i));
const toggleFaq = (si, i) => {
    const k = key(si, i);
    const next = new Set(openItems.value);
    next.has(k) ? next.delete(k) : next.add(k);
    openItems.value = next;
};

const abrirRusti = () => {
    window.dispatchEvent(new CustomEvent('rusti:open'));
};

const faqs = computed(() => [
    {
        categoria: t('info.faq.cat_orders'),
        items: [
            { q: t('info.faq.q1'), a: t('info.faq.a1') },
            { q: t('info.faq.q2'), a: t('info.faq.a2') },
            { q: t('info.faq.q3'), a: t('info.faq.a3') },
            { q: t('info.faq.q4'), a: t('info.faq.a4') },
            { q: t('info.faq.q5'), a: t('info.faq.a5') },
        ],
    },
    {
        categoria: t('info.faq.cat_account'),
        items: [
            { q: t('info.faq.q6'), a: t('info.faq.a6') },
            { q: t('info.faq.q7'), a: t('info.faq.a7') },
            { q: t('info.faq.q8'), a: t('info.faq.a8') },
        ],
    },
    {
        categoria: t('info.faq.cat_producers'),
        items: [
            { q: t('info.faq.q9'), a: t('info.faq.a9') },
            { q: t('info.faq.q10'), a: t('info.faq.a10') },
            { q: t('info.faq.q11'), a: t('info.faq.a11') },
        ],
    },
    {
        categoria: t('info.faq.cat_ai'),
        items: [
            { q: t('info.faq.q12'), a: t('info.faq.a12') },
            { q: t('info.faq.q13'), a: t('info.faq.a13') },
            { q: t('info.faq.q14'), a: t('info.faq.a14') },
        ],
    },
]);
</script>
