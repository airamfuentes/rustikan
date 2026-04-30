<template>
    <div class="min-h-screen bg-white dark:bg-gray-900">
        <Head title="Preguntas frecuentes - Rustikan" />
        <NavbarPublico />

        <!-- Hero -->
        <section class="bg-gradient-to-br from-teal-600 via-teal-700 to-cyan-800 pt-32 pb-20 text-white">
            <div class="mx-auto max-w-4xl px-4 text-center">
                <div class="mb-6 inline-flex h-16 w-16 items-center justify-center rounded-full bg-white/10 backdrop-blur-sm">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z"/>
                    </svg>
                </div>
                <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">Preguntas frecuentes</h1>
                <p class="mt-4 text-lg text-teal-100 max-w-2xl mx-auto">Todo lo que necesitas saber sobre Rustikan.</p>
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
                    <p class="text-sm text-gray-700 dark:text-gray-300 mb-3">¿No encuentras lo que buscas?</p>
                    <Link :href="route('info.contacto')" class="inline-block rounded-full bg-indigo-600 text-white px-6 py-2.5 font-semibold text-sm hover:bg-indigo-700 transition-colors">
                        Escríbenos
                    </Link>
                </div>

            </div>
        </section>

        <FooterPublico />
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import NavbarPublico from '@/Components/NavbarPublico.vue';
import FooterPublico from '@/Components/FooterPublico.vue';
import { useDarkMode } from '@/Composables/useDarkMode';
useDarkMode();

const openItems = ref(new Set());

const key = (si, i) => `${si}-${i}`;
const isOpen    = (si, i) => openItems.value.has(key(si, i));
const toggleFaq = (si, i) => {
    const k = key(si, i);
    const next = new Set(openItems.value);
    next.has(k) ? next.delete(k) : next.add(k);
    openItems.value = next;
};

const faqs = [
    {
        categoria: 'Compras y pedidos',
        items: [
            { q: '¿Cómo realizo un pedido?', a: 'Navega por las tiendas o categorías, añade productos al carrito y completa el proceso de pago. Recibirás una confirmación por email con los detalles de tu pedido.' },
            { q: '¿Cuánto tarda en llegar mi pedido?', a: 'El tiempo de entrega depende de la tienda y tu ubicación dentro de Lanzarote. Normalmente entre 2 y 24 horas. Cada tienda indica sus plazos habituales.' },
            { q: '¿Puedo cancelar un pedido?', a: 'Puedes cancelar un pedido siempre que no haya sido confirmado por el productor. Una vez confirmado, contacta directamente con la tienda o con nuestro soporte.' },
            { q: '¿Cuáles son los métodos de pago?', a: 'Actualmente aceptamos pago con tarjeta de crédito/débito. Próximamente añadiremos más métodos de pago.' },
            { q: '¿Puedo pedir a varias tiendas en un mismo pedido?', a: 'Sí, puedes añadir productos de varias tiendas en el mismo carrito. Se generará un pedido separado por cada tienda.' },
        ],
    },
    {
        categoria: 'Cuenta y perfil',
        items: [
            { q: '¿Es gratuito registrarse?', a: 'Sí, crear una cuenta en Rustikan como comprador es completamente gratuito.' },
            { q: '¿Cómo cambio mi contraseña?', a: 'Desde tu perfil → Configuración → Cambiar contraseña. Si la olvidaste, usa la opción "¿Olvidaste tu contraseña?" en la pantalla de login.' },
            { q: '¿Puedo eliminar mi cuenta?', a: 'Sí. Desde tu perfil puedes solicitar la eliminación de tu cuenta y todos tus datos personales conforme al RGPD.' },
        ],
    },
    {
        categoria: 'Para productores',
        items: [
            { q: '¿Cómo puedo vender en Rustikan?', a: 'Contáctanos a través del formulario de "Vende con nosotros" o escríbenos a hola@rustikan.com. Revisaremos tu solicitud y te configuramos la tienda.' },
            { q: '¿Tiene coste abrir una tienda?', a: 'El alta es gratuita. Rustikan aplica una pequeña comisión sobre cada venta para mantener la plataforma y los servicios de reparto.' },
            { q: '¿Quién gestiona las entregas?', a: 'Rustikan cuenta con repartidores locales. También puedes gestionar tus propias entregas si lo prefieres.' },
        ],
    },
];
</script>
