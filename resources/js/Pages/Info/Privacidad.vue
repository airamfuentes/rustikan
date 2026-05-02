<template>
    <div class="min-h-screen bg-white dark:bg-gray-900">
        <Head title="Política de privacidad - Rustikan" />
        <NavbarPublico />

        <!-- Hero -->
        <section class="bg-gradient-to-br from-slate-700 via-slate-800 to-gray-900 pt-32 pb-16 text-white">
            <div class="mx-auto max-w-4xl px-4 text-center">
                <div class="mb-6 inline-flex h-16 w-16 items-center justify-center rounded-full bg-white/10 backdrop-blur-sm">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                    </svg>
                </div>
                <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">Política de privacidad</h1>
                <p class="mt-3 text-sm text-gray-400">Última actualización: {{ fechaActualizacion }}</p>
            </div>
        </section>

        <!-- Contenido -->
        <section class="py-12 bg-white dark:bg-gray-900">
            <div class="mx-auto max-w-3xl px-4 sm:px-6">

                <!-- Introducción -->
                <div class="mb-8 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 p-5">
                    <p class="text-sm text-blue-800 dark:text-blue-300 leading-relaxed">
                        En Rustikan nos comprometemos a proteger tu privacidad y tratar tus datos personales con total transparencia. Esta política explica qué datos recogemos, cómo los usamos y cuáles son tus derechos conforme al Reglamento General de Protección de Datos (RGPD) y la Ley Orgánica de Protección de Datos (LOPDGDD).
                    </p>
                </div>

                <div class="space-y-10">
                    <div v-for="(seccion, i) in secciones" :key="i">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <span class="text-primary-600 dark:text-primary-400 text-sm font-mono">{{ String(i + 1).padStart(2, '0') }}</span>
                            {{ seccion.titulo }}
                        </h2>
                        <div class="space-y-3">
                            <p v-for="(p, j) in seccion.contenido" :key="j" class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">{{ p }}</p>
                            <ul v-if="seccion.lista" class="mt-2 space-y-1 list-none">
                                <li v-for="(item, k) in seccion.lista" :key="k" class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-400">
                                    <span class="text-primary-500 mt-0.5">•</span> {{ item }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Contacto DPO -->
                <div class="mt-12 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Contacto para asuntos de privacidad</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Para ejercer tus derechos o realizar cualquier consulta sobre el tratamiento de tus datos, escríbenos a
                        <a href="mailto:info@rustikan.com" class="text-primary-600 dark:text-primary-400 hover:underline">info@rustikan.com</a>
                        . Responderemos en un plazo máximo de 30 días.
                    </p>
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
        titulo: 'Responsable del tratamiento',
        contenido: [
            'El responsable del tratamiento de tus datos personales es Rustikan, con domicilio en Lanzarote, Islas Canarias. Puedes contactarnos en info@rustikan.com para cualquier cuestión relacionada con la protección de datos.',
        ],
    },
    {
        titulo: 'Datos que recogemos',
        contenido: ['Recogemos los siguientes tipos de datos personales:'],
        lista: [
            'Datos de registro: nombre, dirección de email y contraseña (almacenada de forma cifrada).',
            'Datos de perfil: foto de perfil (opcional) y preferencias de la cuenta.',
            'Datos de compra: dirección de entrega, teléfono de contacto e historial de pedidos.',
            'Datos de uso: páginas visitadas, productos consultados y comportamiento en la Plataforma (mediante cookies analíticas).',
            'Datos de pago: procesados directamente por pasarelas de pago seguras. Rustikan no almacena datos de tarjeta.',
        ],
    },
    {
        titulo: 'Finalidad y base legal del tratamiento',
        contenido: ['Tratamos tus datos para los siguientes fines:'],
        lista: [
            'Gestión de tu cuenta y autenticación (base legal: ejecución del contrato).',
            'Procesamiento y seguimiento de pedidos (base legal: ejecución del contrato).',
            'Comunicaciones sobre tu cuenta y pedidos (base legal: ejecución del contrato).',
            'Mejora de la Plataforma mediante análisis estadístico anonimizado (base legal: interés legítimo).',
            'Envío de comunicaciones comerciales, solo con tu consentimiento expreso (base legal: consentimiento).',
        ],
    },
    {
        titulo: 'Conservación de datos',
        contenido: [
            'Conservamos tus datos mientras mantengas una cuenta activa en la Plataforma. Si eliminas tu cuenta, suprimiremos tus datos personales en un plazo máximo de 30 días, salvo que la ley nos obligue a conservarlos por un período determinado (por ejemplo, datos de facturación durante 5 años conforme a la normativa fiscal).',
        ],
    },
    {
        titulo: 'Derechos del usuario',
        contenido: ['Como usuario tienes derecho a:'],
        lista: [
            'Acceder a tus datos personales y obtener una copia.',
            'Rectificar datos inexactos o incompletos.',
            'Suprimir tus datos ("derecho al olvido").',
            'Limitar u oponerte al tratamiento en determinadas circunstancias.',
            'Portabilidad: recibir tus datos en un formato estructurado y de uso común.',
            'Retirar tu consentimiento en cualquier momento, sin que ello afecte a la licitud del tratamiento previo.',
        ],
    },
    {
        titulo: 'Transferencias internacionales',
        contenido: [
            'Rustikan no realiza transferencias de datos personales fuera del Espacio Económico Europeo (EEE). En caso de que algún proveedor de servicios requiriese dicha transferencia, nos aseguraremos de que existan las garantías adecuadas conforme al RGPD.',
        ],
    },
    {
        titulo: 'Seguridad',
        contenido: [
            'Aplicamos medidas técnicas y organizativas apropiadas para proteger tus datos contra el acceso no autorizado, la pérdida o la destrucción. Entre ellas: cifrado HTTPS, contraseñas hasheadas con bcrypt y accesos con autenticación de dos factores para el equipo interno.',
        ],
    },
    {
        titulo: 'Reclamaciones',
        contenido: [
            'Si consideras que el tratamiento de tus datos vulnera la normativa de protección de datos, tienes derecho a presentar una reclamación ante la Agencia Española de Protección de Datos (AEPD) en www.aepd.es.',
        ],
    },
];
</script>
