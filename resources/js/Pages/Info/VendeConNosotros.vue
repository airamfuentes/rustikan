<template>
    <div class="min-h-screen bg-white dark:bg-gray-900">
        <Head title="Vende con nosotros - Rustikan" />
        <NavbarPublico />

        <!-- Hero -->
        <section class="bg-gradient-to-br from-primary-600 via-primary-700 to-emerald-800 pt-32 pb-20 text-white">
            <div class="mx-auto max-w-5xl px-4 text-center">
                <div class="mb-6 inline-flex h-16 w-16 items-center justify-center rounded-full bg-white/10 backdrop-blur-sm">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z"/>
                    </svg>
                </div>
                <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">Vende con nosotros</h1>
                <p class="mt-4 text-xl text-primary-100 max-w-2xl mx-auto">
                    Abre tu tienda en Rustikan y lleva tus productos locales a más personas de Lanzarote.
                </p>
                <a href="mailto:hola@rustikan.com?subject=Quiero ser productor en Rustikan"
                    class="mt-8 inline-block rounded-full bg-white text-primary-700 px-8 py-3 font-bold text-sm hover:bg-primary-50 transition-colors shadow-lg">
                    Solicitar mi tienda →
                </a>
            </div>
        </section>

        <!-- Beneficios -->
        <section class="py-16 bg-white dark:bg-gray-900">
            <div class="mx-auto max-w-5xl px-4 sm:px-6">

                <h2 class="text-2xl font-bold text-center text-gray-900 dark:text-white mb-10">¿Por qué vender en Rustikan?</h2>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 mb-16">
                    <div v-for="b in beneficios" :key="b.titulo" class="rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-6">
                        <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-primary-100 text-primary-600 dark:bg-primary-900/30 dark:text-primary-400">
                            <component :is="b.icon" class="h-6 w-6" />
                        </div>
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-2">{{ b.titulo }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ b.desc }}</p>
                    </div>
                </div>

                <!-- Proceso -->
                <h2 class="text-2xl font-bold text-center text-gray-900 dark:text-white mb-10">¿Cómo funciona?</h2>
                <div class="relative">
                    <div class="absolute left-6 top-0 bottom-0 w-px bg-primary-200 dark:bg-primary-800 hidden sm:block"></div>
                    <div class="space-y-8">
                        <div v-for="(paso, i) in pasos" :key="i" class="flex gap-5 items-start">
                            <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-primary-600 text-white font-bold text-lg z-10">{{ i + 1 }}</div>
                            <div class="pt-2">
                                <h3 class="font-semibold text-gray-900 dark:text-white">{{ paso.titulo }}</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ paso.desc }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CTA final -->
                <div class="mt-16 rounded-2xl bg-primary-50 dark:bg-primary-900/20 border border-primary-100 dark:border-primary-800 p-8 text-center">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">¿Listo para empezar?</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">Escríbenos y te ayudamos a configurar tu tienda en menos de 24 horas.</p>
                    <a href="mailto:hola@rustikan.com?subject=Quiero ser productor en Rustikan"
                        class="inline-block rounded-full bg-primary-600 text-white px-8 py-3 font-semibold text-sm hover:bg-primary-700 transition-colors">
                        Contactar ahora
                    </a>
                </div>

            </div>
        </section>

        <FooterPublico />
    </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import NavbarPublico from '@/Components/NavbarPublico.vue';
import FooterPublico from '@/Components/FooterPublico.vue';
import { useDarkMode } from '@/Composables/useDarkMode';
import { Gift, Smartphone, CreditCard, Package, BarChart3, Globe } from 'lucide-vue-next';
useDarkMode();

const beneficios = [
    { icon: Gift,       titulo: 'Alta gratuita',              desc: 'Crear tu tienda en Rustikan no tiene ningún coste de entrada. Solo pagas cuando vendes.' },
    { icon: Smartphone, titulo: 'Panel de gestión fácil',     desc: 'Gestiona tus productos, pedidos y stock desde un panel intuitivo, sin conocimientos técnicos.' },
    { icon: CreditCard, titulo: 'Cobro rápido y seguro',      desc: 'Recibes el importe de cada venta directamente, con pagos seguros y trazables.' },
    { icon: Package,    titulo: 'Logística integrada',        desc: 'Trabajamos con repartidores locales para que tus pedidos lleguen frescos y a tiempo.' },
    { icon: BarChart3,  titulo: 'Estadísticas en tiempo real', desc: 'Consulta ventas, productos más vendidos y comportamiento de tus clientes.' },
    { icon: Globe,      titulo: 'Más visibilidad',            desc: 'Aparece en el mapa de tiendas de Rustikan y llega a clientes que buscan productos como los tuyos.' },
];

const pasos = [
    { titulo: 'Contáctanos', desc: 'Escríbenos un email o usa el formulario de contacto. Cuéntanos qué produces y dónde estás.' },
    { titulo: 'Revisamos tu solicitud', desc: 'En menos de 24 horas revisamos tu perfil y te informamos de los próximos pasos.' },
    { titulo: 'Creamos tu tienda', desc: 'Configuramos tu tienda y te damos acceso al panel de gestión para que subas tus productos.' },
    { titulo: 'Empieza a vender', desc: 'Tu tienda aparece en Rustikan y empieza a recibir pedidos de clientes de toda la isla.' },
];
</script>
