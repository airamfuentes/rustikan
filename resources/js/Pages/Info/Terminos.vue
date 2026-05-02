<template>
    <div class="min-h-screen bg-white dark:bg-gray-900">
        <Head title="Términos y condiciones - Rustikan" />
        <NavbarPublico />

        <!-- Hero -->
        <section class="bg-gradient-to-br from-gray-700 via-gray-800 to-gray-900 pt-32 pb-16 text-white">
            <div class="mx-auto max-w-4xl px-4 text-center">
                <div class="mb-6 inline-flex h-16 w-16 items-center justify-center rounded-full bg-white/10 backdrop-blur-sm">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                    </svg>
                </div>
                <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">Términos y condiciones</h1>
                <p class="mt-3 text-sm text-gray-400">Última actualización: {{ fechaActualizacion }}</p>
            </div>
        </section>

        <!-- Contenido legal -->
        <section class="py-12 bg-white dark:bg-gray-900">
            <div class="mx-auto max-w-3xl px-4 sm:px-6">
                <div class="prose prose-gray dark:prose-invert max-w-none">

                    <div v-for="(seccion, i) in secciones" :key="i" class="mb-10">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <span class="text-primary-600 dark:text-primary-400 text-sm font-mono">{{ String(i + 1).padStart(2, '0') }}</span>
                            {{ seccion.titulo }}
                        </h2>
                        <div class="space-y-3">
                            <p v-for="(parrafo, j) in seccion.contenido" :key="j" class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                                {{ parrafo }}
                            </p>
                        </div>
                    </div>

                    <!-- Contacto legal -->
                    <div class="mt-12 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-6">
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Contacto legal</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Para cualquier cuestión relacionada con estos términos, puedes escribirnos a
                            <a href="mailto:info@rustikan.com" class="text-primary-600 dark:text-primary-400 hover:underline">info@rustikan.com</a>
                        </p>
                    </div>

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
useDarkMode();

const fechaActualizacion = '30 de abril de 2025';

const secciones = [
    {
        titulo: 'Objeto y ámbito de aplicación',
        contenido: [
            'Los presentes Términos y Condiciones regulan el acceso y uso de la plataforma Rustikan (en adelante, "la Plataforma"), un mercado digital que conecta productores locales de Lanzarote con consumidores finales.',
            'El acceso y uso de la Plataforma atribuye la condición de usuario e implica la aceptación plena y sin reservas de todas las disposiciones incluidas en estos Términos y Condiciones.',
            'Rustikan se reserva el derecho a modificar estos términos en cualquier momento. Las modificaciones serán notificadas a los usuarios registrados con un mínimo de 15 días de antelación.',
        ],
    },
    {
        titulo: 'Registro y cuenta de usuario',
        contenido: [
            'Para acceder a determinadas funcionalidades de la Plataforma es necesario crear una cuenta de usuario. El usuario se compromete a proporcionar información veraz, exacta y completa durante el proceso de registro.',
            'El usuario es responsable de mantener la confidencialidad de sus credenciales de acceso y de todas las actividades realizadas con su cuenta.',
            'Rustikan se reserva el derecho de suspender o cancelar cuentas que incumplan estos términos, que proporcionen información falsa o que realicen actividades fraudulentas.',
        ],
    },
    {
        titulo: 'Condiciones de compra',
        contenido: [
            'Las compras realizadas a través de la Plataforma constituyen un contrato entre el comprador y el productor/vendedor. Rustikan actúa únicamente como intermediario tecnológico.',
            'Los precios mostrados incluyen el IVA aplicable. Los gastos de envío se mostrarán claramente antes de confirmar el pedido.',
            'El usuario acepta que, una vez confirmado el pedido, está obligado a efectuar el pago del importe total. La cancelación de pedidos confirmados está sujeta a la política de cada productor.',
            'Rustikan garantiza la seguridad del proceso de pago pero no es responsable de los productos ofrecidos por los vendedores ni de las transacciones que se realicen directamente entre usuarios.',
        ],
    },
    {
        titulo: 'Condiciones para productores',
        contenido: [
            'Los productores que deseen vender en la Plataforma deben solicitar el alta mediante los canales habilitados y aceptar los términos específicos para vendedores.',
            'El productor garantiza que los productos ofrecidos cumplen con toda la normativa aplicable, incluyendo la relativa a seguridad alimentaria, etiquetado y permisos de actividad.',
            'El productor es responsable de la veracidad de la información de sus productos, de la gestión de stock y del cumplimiento de los pedidos aceptados.',
            'Rustikan se reserva el derecho de eliminar productos o suspender cuentas de productores que incumplan estos términos o la normativa vigente.',
        ],
    },
    {
        titulo: 'Propiedad intelectual',
        contenido: [
            'Todos los contenidos de la Plataforma (diseño, logos, textos, código fuente) son propiedad de Rustikan o están licenciados para su uso. Queda prohibida su reproducción, distribución o modificación sin autorización expresa.',
            'Los productores conservan la propiedad intelectual de las imágenes y contenidos que suban a la Plataforma, pero otorgan a Rustikan una licencia no exclusiva para mostrarlos dentro de la Plataforma.',
        ],
    },
    {
        titulo: 'Limitación de responsabilidad',
        contenido: [
            'Rustikan no garantiza la disponibilidad continua de la Plataforma y no será responsable de los daños derivados de interrupciones del servicio por causas ajenas a su control.',
            'Rustikan no asume responsabilidad por los productos vendidos por terceros a través de la Plataforma. Las reclamaciones relativas a productos deben dirigirse al productor correspondiente.',
        ],
    },
    {
        titulo: 'Ley aplicable y jurisdicción',
        contenido: [
            'Estos Términos y Condiciones se rigen por la legislación española. Para la resolución de cualquier conflicto derivado del uso de la Plataforma, las partes se someten a la jurisdicción de los tribunales de Las Palmas de Gran Canaria, salvo que la normativa aplicable establezca otro fuero.',
        ],
    },
];
</script>
