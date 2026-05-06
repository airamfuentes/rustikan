<template>
    <div :class="isDark ? 'dark' : ''">
        <Head title="Centro de ayuda - Rustikan" />
        <NavbarPublico />

        <!-- Hero -->
        <section class="bg-gradient-to-br from-teal-600 via-cyan-700 to-sky-800 pt-24 pb-20 text-white">
            <div class="mx-auto max-w-4xl px-4 text-center">
                <div class="mb-6 inline-flex h-16 w-16 items-center justify-center rounded-full bg-white/10 backdrop-blur-sm">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z"/>
                    </svg>
                </div>
                <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">{{ t('info.help.title') }}</h1>
                <p class="mt-4 text-lg text-teal-100 max-w-2xl mx-auto">{{ t('info.help.subtitle') }}</p>

                <!-- Buscador decorativo -->
                <div class="mt-8 max-w-lg mx-auto">
                    <div class="flex items-center gap-3 rounded-full bg-white/10 backdrop-blur-sm border border-white/20 px-5 py-3">
                        <svg class="h-5 w-5 text-teal-200 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/>
                        </svg>
                        <input type="text" placeholder="Busca tu duda..." class="bg-transparent text-white placeholder-teal-200 outline-none w-full text-sm" />
                    </div>
                </div>
            </div>
        </section>

        <!-- Categorías -->
        <section class="py-16 bg-white dark:bg-gray-900">
            <div class="mx-auto max-w-5xl px-4 sm:px-6">

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-8 text-center">¿Cómo podemos ayudarte?</h2>
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 mb-16">
                    <div v-for="cat in categorias" :key="cat.titulo"
                        class="group rounded-xl border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-6 cursor-pointer hover:border-primary-300 dark:hover:border-primary-600 hover:shadow-md transition-all">
                        <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-teal-100 text-teal-600 dark:bg-teal-900/30 dark:text-teal-400 group-hover:bg-teal-600 group-hover:text-white transition-colors">
                            <component :is="cat.icon" class="h-6 w-6" />
                        </div>
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-1 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">{{ cat.titulo }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ cat.desc }}</p>
                    </div>
                </div>

                <!-- Artículos populares -->
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Artículos más consultados</h2>
                <div class="divide-y divide-gray-100 dark:divide-gray-700 rounded-xl border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 mb-16">
                    <div v-for="(art, i) in articulos" :key="i" class="flex items-center justify-between p-4 hover:bg-white dark:hover:bg-gray-700 transition-colors cursor-pointer rounded-xl">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ art }}</span>
                        <svg class="h-4 w-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                        </svg>
                    </div>
                </div>

                <!-- Contacto directo -->
                <div class="rounded-2xl bg-teal-50 dark:bg-teal-900/20 border border-teal-100 dark:border-teal-800 p-8 text-center">
                    <MessageCircle class="mx-auto mb-3 h-9 w-9 text-teal-600 dark:text-teal-400" />
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">¿No encuentras lo que buscas?</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-5">Nuestro equipo está disponible para resolver cualquier duda.</p>
                    <Link :href="route('info.contacto')" class="inline-block rounded-full bg-teal-600 text-white px-8 py-3 font-semibold text-sm hover:bg-teal-700 transition-colors">
                        Contactar con soporte
                    </Link>
                </div>

            </div>
        </section>

        <FooterPublico />
    </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import NavbarPublico from '@/Components/NavbarPublico.vue';
import FooterPublico from '@/Components/FooterPublico.vue';
import { useDarkMode } from '@/Composables/useDarkMode';
import { useI18n } from '@/Composables/useI18n';
import { ShoppingCart, CreditCard, Store, UserCircle, Truck, Scale, MessageCircle } from 'lucide-vue-next';
const { isDark } = useDarkMode();

const { t } = useI18n();

const categorias = [
    { icon: ShoppingCart, titulo: 'Pedidos y entregas',     desc: 'Seguimiento de pedidos, cambios, cancelaciones y plazos de entrega.' },
    { icon: CreditCard,   titulo: 'Pagos',                  desc: 'Métodos de pago, facturas, reembolsos y problemas con cobros.' },
    { icon: Store,        titulo: 'Tiendas y productores',  desc: 'Información sobre los vendedores, sus productos y su ubicación.' },
    { icon: UserCircle,   titulo: 'Mi cuenta',              desc: 'Datos personales, contraseña, notificaciones y preferencias.' },
    { icon: Truck,        titulo: 'Repartidores',           desc: 'Cómo unirte al equipo de reparto y gestionar tus rutas.' },
    { icon: Scale,        titulo: 'Legal y privacidad',     desc: 'Términos de uso, política de privacidad y cookies.' },
];

const articulos = [
    '¿Cómo realizo un pedido?',
    '¿Cuánto tiempo tarda mi pedido en llegar?',
    '¿Puedo cancelar un pedido ya confirmado?',
    '¿Cómo puedo registrarme como productor?',
    '¿Qué métodos de pago se aceptan?',
    '¿Cómo dejo una reseña de una tienda?',
    '¿Puedo cambiar la dirección de entrega?',
];
</script>
