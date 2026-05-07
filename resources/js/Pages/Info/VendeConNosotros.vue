<template>
    <div class="min-h-screen bg-white dark:bg-gray-900">
        <Head title="Vende con nosotros" />

        <!-- Toasts via ToastContainer global -->

        <NavbarPublico />

        <!-- Hero -->
        <section class="bg-gradient-to-br from-primary-600 via-primary-700 to-emerald-800 pt-32 pb-20 text-white">
            <div class="mx-auto max-w-5xl px-4 text-center">
                <div class="mb-6 inline-flex h-16 w-16 items-center justify-center rounded-full bg-white/10 backdrop-blur-sm">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z"/>
                    </svg>
                </div>
                <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">{{ t('info.sell.title') }}</h1>
                <p class="mt-4 text-xl text-primary-100 max-w-2xl mx-auto">
                    {{ t('info.sell.subtitle') }}
                </p>
                <a href="#solicitar"
                    class="mt-8 inline-block rounded-full bg-white text-primary-700 px-8 py-3 font-bold text-sm hover:bg-primary-50 transition-colors shadow-lg">
                    {{ t('info.sell.request_btn') }}
                </a>
            </div>
        </section>

        <!-- Beneficios -->
        <section class="py-16 bg-white dark:bg-gray-900">
            <div class="mx-auto max-w-5xl px-4 sm:px-6">

                <h2 class="text-2xl font-bold text-center text-gray-900 dark:text-white mb-10">{{ t('info.sell.why_title') }}</h2>
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
                <h2 class="text-2xl font-bold text-center text-gray-900 dark:text-white mb-10">{{ t('info.sell.how_title') }}</h2>
                <div class="relative mb-16">
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

                <!-- ── Formulario de solicitud ────────────────────────────── -->
                <div id="solicitar" class="scroll-mt-24">

                    <!-- Auth gate (no hay sesión) -->
                    <div v-if="!user"
                         class="rounded-2xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 p-10 text-center">
                        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-800">
                            <svg class="h-8 w-8 text-amber-600 dark:text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-amber-800 dark:text-amber-200 mb-2">{{ t('info.sell.login_required') }}</h3>
                        <p class="text-sm text-amber-700 dark:text-amber-300 mb-6">
                            {{ t('info.sell.login_desc') }}
                        </p>
                        <div class="flex flex-wrap items-center justify-center gap-3">
                            <Link :href="route('login')"
                                  class="rounded-xl bg-primary-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-primary-700">
                                {{ t('info.sell.login_btn') }}
                            </Link>
                            <Link :href="route('register')"
                                  class="rounded-xl border border-primary-300 dark:border-primary-700 bg-white dark:bg-gray-800 px-6 py-2.5 text-sm font-bold text-primary-700 dark:text-primary-300 shadow-sm transition hover:bg-primary-50 dark:hover:bg-gray-700">
                                {{ t('info.sell.register_btn') }}
                            </Link>
                        </div>
                    </div>

                    <!-- Formulario (sesión activa) -->
                    <div v-if="user" class="rounded-2xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-8">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-1">{{ t('info.sell.form_title') }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-8">{{ t('info.sell.form_subtitle') }}</p>

                        <form @submit.prevent="enviar" class="space-y-6">
                            <!-- Fila 1: Nombre tienda + categoría -->
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        {{ t('info.sell.store_name') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input v-model="form.nombre_tienda" type="text" required maxlength="120"
                                        :placeholder="t('info.sell.store_name_placeholder')"
                                        :class="inputClass(form.errors.nombre_tienda)" />
                                    <p v-if="form.errors.nombre_tienda" class="mt-1 text-xs text-red-500">{{ form.errors.nombre_tienda }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        {{ t('info.sell.category') }} <span class="text-red-500">*</span>
                                    </label>
                                    <select v-model="form.categoria" required :class="inputClass(form.errors.categoria)">
                                        <option value="" disabled>{{ t('info.sell.category_placeholder') }}</option>
                                        <option v-for="c in categorias" :key="c" :value="c">{{ c }}</option>
                                    </select>
                                    <p v-if="form.errors.categoria" class="mt-1 text-xs text-red-500">{{ form.errors.categoria }}</p>
                                </div>
                            </div>

                            <!-- Fila 2: Nombre contacto + email -->
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        {{ t('info.sell.contact_name') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input v-model="form.nombre_contacto" type="text" required maxlength="120" v-only-letters
                                        :placeholder="t('info.sell.contact_name_placeholder')"
                                        :class="inputClass(form.errors.nombre_contacto)" />
                                    <p v-if="form.errors.nombre_contacto" class="mt-1 text-xs text-red-500">{{ form.errors.nombre_contacto }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Email <span class="text-red-500">*</span>
                                    </label>
                                    <input v-model="form.email" type="email" required maxlength="180"
                                        placeholder="tu@email.com"
                                        :class="inputClass(form.errors.email)" />
                                    <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</p>
                                </div>
                            </div>

                            <!-- Fila 3: Teléfono + municipio -->
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('info.sell.phone') }}</label>
                                    <input v-model="form.telefono" type="tel" maxlength="20"
                                        inputmode="tel"
                                        v-only-phone
                                        :placeholder="t('info.sell.phone_placeholder')"
                                        :class="inputClass(form.errors.telefono)" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('info.sell.municipality') }}</label>
                                    <select v-model="form.municipio" :class="inputClass(form.errors.municipio)">
                                        <option value="">{{ t('info.sell.municipality_placeholder') }}</option>
                                        <option v-for="m in municipios" :key="m" :value="m">{{ m }}</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Dirección -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('info.sell.address') }}</label>
                                <input v-model="form.direccion" type="text" maxlength="200"
                                    :placeholder="t('info.sell.address_placeholder')"
                                    :class="inputClass(form.errors.direccion)" />
                            </div>

                            <!-- Fila: web + instagram -->
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('info.sell.website') }}</label>
                                    <input v-model="form.web" type="url" maxlength="200"
                                        placeholder="https://..."
                                        :class="inputClass(form.errors.web)" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ t('info.sell.instagram') }}</label>
                                    <div class="flex">
                                        <span :class="['flex items-center rounded-l-xl border border-r-0 px-3 text-sm text-gray-500', isDark ? 'bg-gray-700 border-gray-600' : 'bg-gray-100 border-gray-200']">@</span>
                                        <input v-model="form.instagram" type="text" maxlength="80"
                                            placeholder="tutienda"
                                            :class="['flex-1 rounded-l-none rounded-r-xl', inputClass(form.errors.instagram)]" />
                                    </div>
                                </div>
                            </div>

                            <!-- Descripción de la tienda -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    {{ t('info.sell.description') }} <span class="text-red-500">*</span>
                                </label>
                                <textarea v-model="form.descripcion" required rows="3" minlength="20" maxlength="1000"
                                    :placeholder="t('info.sell.description_placeholder')"
                                    :class="['resize-none', inputClass(form.errors.descripcion)]"></textarea>
                                <div class="flex justify-between mt-1">
                                    <p v-if="form.errors.descripcion" class="text-xs text-red-500">{{ form.errors.descripcion }}</p>
                                    <span class="ml-auto text-xs text-gray-400">{{ form.descripcion.length }}/1000</span>
                                </div>
                            </div>

                            <!-- Productos que vende -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    {{ t('info.sell.products_label') }} <span class="text-red-500">*</span>
                                </label>
                                <textarea v-model="form.productos_descripcion" required rows="3" minlength="20" maxlength="1000"
                                    :placeholder="t('info.sell.products_placeholder')"
                                    :class="['resize-none', inputClass(form.errors.productos_descripcion)]"></textarea>
                                <div class="flex justify-between mt-1">
                                    <p v-if="form.errors.productos_descripcion" class="text-xs text-red-500">{{ form.errors.productos_descripcion }}</p>
                                    <span class="ml-auto text-xs text-gray-400">{{ form.productos_descripcion.length }}/1000</span>
                                </div>
                            </div>

                            <!-- Submit -->
                            <div class="flex items-center justify-end gap-3 pt-2">
                                <p class="text-xs text-gray-400 flex-1">
                                    {{ t('info.sell.terms_note') }}
                                </p>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="flex items-center gap-2 rounded-xl bg-primary-600 px-8 py-3 text-sm font-bold text-white shadow-sm transition-all hover:bg-primary-700 hover:shadow-md disabled:opacity-60"
                                >
                                    <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                    {{ form.processing ? t('info.sell.submitting') : t('info.sell.submit') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </section>

        <FooterPublico />
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import NavbarPublico from '@/Components/NavbarPublico.vue';
import FooterPublico from '@/Components/FooterPublico.vue';
import { useDarkMode } from '@/Composables/useDarkMode';
import { useI18n } from '@/Composables/useI18n';
import { useToasts } from '@/Composables/useToasts';
import { Gift, Smartphone, CreditCard, Package, BarChart3, Globe } from 'lucide-vue-next';

const { t } = useI18n();
const { isDark } = useDarkMode();
const page = usePage();
const { success: toastSuccess, error: toastError } = useToasts();

const user = computed(() => page.props.auth?.user ?? null);

const form = useForm({
    nombre_tienda:         '',
    nombre_contacto:       page.props.auth?.user?.name  ?? '',
    email:                 page.props.auth?.user?.email ?? '',
    telefono:              '',
    categoria:             '',
    descripcion:           '',
    municipio:             '',
    direccion:             '',
    web:                   '',
    instagram:             '',
    productos_descripcion: '',
});

const enviar = () => {
    form.post(route('solicitud-tienda.store'), {
        preserveScroll: true,
        onSuccess: () => {
            // Limpiar todos los campos del formulario
            form.reset();
            // Restaurar valores del usuario autenticado
            form.nombre_contacto = page.props.auth?.user?.name  ?? '';
            form.email           = page.props.auth?.user?.email ?? '';
            toastSuccess('¡Solicitud enviada!', 'Hemos recibido tu solicitud. Nos pondremos en contacto contigo en menos de 48 horas.');
        },
        onError: () => {
            toastError('Error al enviar', 'Revisa los campos e inténtalo de nuevo.');
        },
    });
};

const inputClass = (error) => [
    'w-full rounded-xl px-4 py-2.5 text-sm outline-none transition border focus:ring-2',
    isDark.value
        ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-500 focus:border-primary-500 focus:ring-primary-500/20'
        : 'bg-white border-gray-200 text-gray-900 placeholder-gray-400 focus:border-primary-500 focus:ring-primary-500/20',
    error ? 'border-red-400' : '',
];

const categorias = [
    'Alimentación y bebidas',
    'Aceites y conservas',
    'Vinos y licores',
    'Miel y apicultura',
    'Quesos y lácteos',
    'Carnes y embutidos',
    'Frutas y verduras',
    'Panadería y repostería',
    'Artesanía',
    'Cosmética natural',
    'Plantas y flores',
    'Textil y ropa',
    'Madera y decoración',
    'Cerámica y alfarería',
    'Otra',
];

const municipios = [
    'Arrecife',
    'Teguise',
    'San Bartolomé',
    'Tías',
    'Yaiza',
    'Tinajo',
    'Haría',
];

const beneficios = [
    { icon: Gift,       titulo: 'Alta gratuita',              desc: 'Crear tu tienda en Rustikan no tiene ningún coste de entrada. Solo pagas cuando vendes.' },
    { icon: Smartphone, titulo: 'Panel de gestión fácil',     desc: 'Gestiona tus productos, pedidos y stock desde un panel intuitivo, sin conocimientos técnicos.' },
    { icon: CreditCard, titulo: 'Cobro rápido y seguro',      desc: 'Recibes el importe de cada venta directamente, con pagos seguros y trazables.' },
    { icon: Package,    titulo: 'Logística integrada',        desc: 'Trabajamos con repartidores locales para que tus pedidos lleguen frescos y a tiempo.' },
    { icon: BarChart3,  titulo: 'Estadísticas en tiempo real', desc: 'Consulta ventas, productos más vendidos y comportamiento de tus clientes.' },
    { icon: Globe,      titulo: 'Más visibilidad',            desc: 'Aparece en el mapa de tiendas de Rustikan y llega a clientes que buscan productos como los tuyos.' },
];

const pasos = [
    { titulo: 'Rellena el formulario',    desc: 'Cuéntanos qué produces, dónde estás y cómo contactarte. Solo tardas 2 minutos.' },
    { titulo: 'Revisamos tu solicitud',   desc: 'En menos de 48 horas revisamos tu perfil y te informamos de los próximos pasos.' },
    { titulo: 'Creamos tu tienda',        desc: 'Configuramos tu tienda y te damos acceso al panel de gestión para que subas tus productos.' },
    { titulo: 'Empieza a vender',         desc: 'Tu tienda aparece en Rustikan y empieza a recibir pedidos de clientes de toda la isla.' },
];
</script>
