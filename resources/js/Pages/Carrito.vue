<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { useCarrito } from '@/Composables/useCarrito';
import NavbarPublico from '@/Components/NavbarPublico.vue';
import { useI18n } from '@/Composables/useI18n';
import { ChevronLeft } from 'lucide-vue-next';

const page  = usePage();
const user  = computed(() => page.props.auth?.user);
const { t } = useI18n();

const {
    items,
    totalItems,
    totalPrecio,
    itemsAgrupadosPorTienda,
    eliminarItem,
    actualizarCantidad,
    vaciarCarrito,
} = useCarrito();

const gastosEnvio = computed(() => (totalPrecio.value >= 50 ? 0 : totalPrecio.value > 0 ? 2.5 : 0));
const totalFinal  = computed(() => totalPrecio.value + gastosEnvio.value);
const handleVaciar = () => vaciarCarrito();

// ─── Checkout multi-step ──────────────────────────────────────────────────────
const mostrarCheckout = ref(false);
const step            = ref(1); // 1: envío, 2: pago, 3: procesando
const errores         = ref({});
const erroresPago     = ref({});

const envioForm = ref({
    calle:             '',
    numero:            '',
    puerta:            '',
    cp:                '',
    localidad:         '',
    telefono_contacto: '',
    notas:             '',
});

// CP auto-lookup (España)
// Códigos postales válidos de Lanzarote (35500–35599)
const isLanzaroteCP = (cp) => {
    const n = parseInt(cp, 10);
    return n >= 35500 && n <= 35599;
};

const buscandoLocalidad = ref(false);
watch(() => envioForm.value.cp, async (cp) => {
    if (!/^\d{5}$/.test(cp)) {
        // Borrar error de CP inválido mientras el usuario escribe
        if (errores.value.cp && errores.value.cp !== t('checkout.cp_required')) {
            errores.value = { ...errores.value, cp: '' };
        }
        return;
    }
    // Validar que sea de Lanzarote
    if (!isLanzaroteCP(cp)) {
        errores.value = { ...errores.value, cp: t('checkout.cp_not_lanzarote') };
        envioForm.value.localidad = '';
        return;
    }
    // CP válido de Lanzarote → limpiar error y buscar localidad
    errores.value = { ...errores.value, cp: '' };
    buscandoLocalidad.value = true;
    try {
        const res  = await fetch(`https://nominatim.openstreetmap.org/search?format=json&addressdetails=1&countrycodes=es&postalcode=${cp}&limit=5`, {
            headers: { 'Accept-Language': 'es', 'User-Agent': 'Rustikan/1.0' },
        });
        const data = await res.json();
        if (data.length > 0) {
            const addr = data[0].address ?? {};
            // Preferir el núcleo más específico antes que el municipio
            const localidad = addr.village || addr.suburb || addr.quarter || addr.neighbourhood
                || addr.hamlet || addr.city_district || addr.town
                || addr.city || addr.municipality || addr.county || addr.state_district || '';
            if (localidad) envioForm.value.localidad = localidad;
        }
    } catch { /* silently fail */ } finally {
        buscandoLocalidad.value = false;
    }
});

const pagoForm = ref({
    titular:    '',
    numero:     '',
    expiry:     '',
    cvv:        '',
    metodo:     'tarjeta',
    rcACusar:   0,
});

const abrirCheckout = () => {
    if (!user.value) { router.visit(route('login')); return; }
    // Pre-rellenar desde perfil si hay datos
    const dir = user.value?.direccion || '';
    envioForm.value = {
        calle:             dir,
        numero:            '',
        puerta:            '',
        cp:                '',
        localidad:         '',
        telefono_contacto: user.value?.telefono || '',
        notas:             '',
    };
    pagoForm.value = { titular: '', numero: '', expiry: '', cvv: '', metodo: 'tarjeta', rcACusar: 0 };
    errores.value     = {};
    erroresPago.value = {};
    step.value        = 1;
    mostrarCheckout.value = true;
};

const cerrarCheckout = () => { if (step.value !== 3) mostrarCheckout.value = false; };

// ── Step 1: validar datos de envío ───────────────────────────────────────────
const siguientePaso = () => {
    errores.value = {};
    if (!envioForm.value.calle.trim()) {
        errores.value.calle = t('checkout.street_required');
        return;
    }
    if (!envioForm.value.numero.trim()) {
        errores.value.numero = t('checkout.number_required');
        return;
    }
    if (!envioForm.value.cp.trim() || !/^\d{5}$/.test(envioForm.value.cp)) {
        errores.value.cp = t('checkout.cp_required');
        return;
    }
    if (!isLanzaroteCP(envioForm.value.cp)) {
        errores.value.cp = t('checkout.cp_not_lanzarote');
        return;
    }
    if (!envioForm.value.localidad.trim()) {
        errores.value.localidad = t('checkout.city_required');
        return;
    }
    if (!envioForm.value.telefono_contacto.trim()) {
        errores.value.telefono_contacto = t('checkout.phone_required');
        return;
    }
    step.value = 2;
};

// ── Helpers de formateo de tarjeta ───────────────────────────────────────────
const formatCardNumber = (val) => {
    const digits = val.replace(/\D/g, '').slice(0, 16);
    return digits.replace(/(.{4})/g, '$1 ').trim();
};

const formatExpiry = (val) => {
    const digits = val.replace(/\D/g, '').slice(0, 4);
    if (digits.length >= 3) return digits.slice(0, 2) + '/' + digits.slice(2);
    return digits;
};

const onCardInput = (e) => {
    pagoForm.value.numero = formatCardNumber(e.target.value);
};

const onExpiryInput = (e) => {
    pagoForm.value.expiry = formatExpiry(e.target.value);
};

const onCvvInput = (e) => {
    pagoForm.value.cvv = e.target.value.replace(/\D/g, '').slice(0, 3);
};

// Luhn check para simular validación real
const luhnCheck = (num) => {
    const digits = num.replace(/\D/g, '');
    if (digits.length < 13) return false;
    let sum = 0;
    let alt = false;
    for (let i = digits.length - 1; i >= 0; i--) {
        let n = parseInt(digits[i]);
        if (alt) { n *= 2; if (n > 9) n -= 9; }
        sum += n;
        alt = !alt;
    }
    return sum % 10 === 0;
};

const cardBrand = computed(() => {
    const n = pagoForm.value.numero.replace(/\D/g, '');
    if (/^4/.test(n)) return 'visa';
    if (/^5[1-5]|^2[2-7]/.test(n)) return 'mastercard';
    if (/^3[47]/.test(n)) return 'amex';
    return null;
});

// ── Step 2: validar pago y procesar ──────────────────────────────────────────
const procesando = ref(false);

const rcDisponible    = computed(() => user.value?.rusticoin_balance ?? 0);
const rcMaxMixto      = computed(() => Math.min(rcDisponible.value, Math.max(0, totalFinal.value - 0.01)));
const restanteTarjeta = computed(() => {
    if (pagoForm.value.metodo !== 'mixto') return totalFinal.value;
    return Math.max(0, totalFinal.value - (pagoForm.value.rcACusar || 0));
});

const pagar = () => {
    erroresPago.value = {};

    if (pagoForm.value.metodo === 'tarjeta' || pagoForm.value.metodo === 'mixto') {
        if (pagoForm.value.metodo === 'mixto') {
            const rc = pagoForm.value.rcACusar;
            if (!rc || rc <= 0) {
                erroresPago.value.rcACusar = 'Indica cuántos RC quieres usar.';
            } else if (rc > rcDisponible.value) {
                erroresPago.value.rcACusar = 'Saldo RC insuficiente.';
            } else if (rc >= totalFinal.value) {
                erroresPago.value.rcACusar = 'Usa la opción RustiCoin para pagar todo con RC.';
            }
        }
        if (!pagoForm.value.titular.trim()) {
            erroresPago.value.titular = 'El nombre del titular es obligatorio.';
        }
        const digits = pagoForm.value.numero.replace(/\D/g, '');
        if (digits.length < 16 || !luhnCheck(digits)) {
            erroresPago.value.numero = 'Número de tarjeta no válido.';
        }
        const [mes, anio] = pagoForm.value.expiry.split('/');
        const ahora = new Date();
        const mesN = parseInt(mes); const anioN = 2000 + parseInt(anio ?? '0');
        if (!mes || !anio || mesN < 1 || mesN > 12 || anioN < ahora.getFullYear() ||
            (anioN === ahora.getFullYear() && mesN < ahora.getMonth() + 1)) {
            erroresPago.value.expiry = 'Fecha de caducidad no válida.';
        }
        if (!pagoForm.value.cvv || pagoForm.value.cvv.length < 3) {
            erroresPago.value.cvv = 'CVV no válido.';
        }
        if (Object.keys(erroresPago.value).length) return;
    }

    // Pasar a step 3: animación de procesamiento
    step.value = 3;
    procesando.value = true;

    // Simular procesamiento (~2 s) y luego enviar al backend
    setTimeout(() => {
        router.post(route('pedidos.store'), {
            items: items.value.map(i => ({
                id:            i.id,
                cantidad:      i.cantidad,
                precio:        i.precio,
                nombre:        i.nombre,
                tienda_id:     i.tienda_id,
                tienda_nombre: i.tienda_nombre,
                imagen:        i.imagen,
                unidad:        i.unidad,
            })),
            direccion_envio:   [envioForm.value.calle.trim(), envioForm.value.numero.trim(), envioForm.value.puerta.trim()].filter(Boolean).join(', ') + `, ${envioForm.value.cp} ${envioForm.value.localidad}`.trim(),
            telefono_contacto: envioForm.value.telefono_contacto.trim(),
            notas:             envioForm.value.notas.trim(),
            metodo_pago:       pagoForm.value.metodo,
            rc_a_usar:         pagoForm.value.metodo === 'mixto' ? pagoForm.value.rcACusar : null,
        }, {
            onSuccess: () => {
                vaciarCarrito();
                mostrarCheckout.value = false;
            },
            onError: (e) => {
                procesando.value = false;
                step.value = 1;
                errores.value = e;
            },
        });
    }, 2200);
};

// Título del paso
const stepTitle = computed(() => ({
    1: t('checkout.step_delivery'),
    2: t('checkout.step_payment'),
    3: t('checkout.step_processing'),
}[step.value]));
</script>

<template>
    <Head title="Mi carrito" />

    <div class="min-h-screen bg-gray-50 dark:bg-gray-950 transition-colors duration-300">

        <NavbarPublico />

        <!-- ── Contenido principal ─────────────────────────────────────────── -->
        <main class="mx-auto max-w-7xl px-4 pt-24 pb-10 sm:px-6 lg:px-8">

            <!-- Título de página -->
            <div class="mb-8">
                <h1 class="text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">{{ t('cart.title') }}</h1>
                <p v-if="totalItems > 0" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ totalItems }} {{ totalItems === 1 ? 'producto' : 'productos' }} de
                    {{ itemsAgrupadosPorTienda.length }} {{ itemsAgrupadosPorTienda.length === 1 ? 'tienda' : 'tiendas' }}
                </p>
            </div>

            <!-- ── Carrito vacío ─────────────────────────────────────────── -->
            <div v-if="totalItems === 0" class="flex flex-col items-center py-24 text-center">
                <div class="mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                    <svg class="h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200">{{ t('cart.empty') }}</h2>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ t('cart.empty_sub') }}</p>
                <Link
                    href="/"
                    class="mt-6 rounded-xl bg-primary-500 px-8 py-3 text-sm font-bold text-white shadow-sm transition-colors hover:bg-primary-600"
                >
                    {{ t('cart.explore') }}
                </Link>
            </div>

            <!-- ── Layout con carrito y resumen ────────────────────────────── -->
            <div v-else class="grid gap-8 lg:grid-cols-3">

                <!-- Columna izquierda: productos -->
                <div class="space-y-6 lg:col-span-2">

                    <!-- Bloque por tienda -->
                    <div
                        v-for="grupo in itemsAgrupadosPorTienda"
                        :key="grupo.tienda_id"
                        class="overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm"
                    >
                        <!-- Cabecera de la tienda -->
                        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 px-5 py-4">
                            <Link
                                :href="`/tienda/${grupo.tienda_slug}`"
                                class="flex items-center gap-2 text-sm font-bold text-gray-800 dark:text-gray-200 hover:text-primary-600"
                            >
                                <svg class="h-4 w-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                {{ grupo.tienda_nombre }}
                            </Link>
                            <span class="text-sm font-semibold text-gray-500 dark:text-gray-400">
                                {{ t('cart.subtotal') }}: <span class="text-gray-800 dark:text-gray-200">{{ grupo.subtotal.toFixed(2) }}€</span>
                            </span>
                        </div>

                        <!-- Filas de producto -->
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            <div
                                v-for="item in grupo.items"
                                :key="item.id"
                                class="flex flex-wrap items-center gap-3 sm:gap-4 px-3 sm:px-5 py-4 transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/50"
                            >
                                <!-- Imagen -->
                                <img
                                    :src="item.imagen || '/images/logo.png'"
                                    :alt="item.nombre"
                                    loading="lazy"
                                    class="h-16 w-16 sm:h-20 sm:w-20 flex-shrink-0 rounded-xl object-cover shadow-sm"
                                />

                                <!-- Info -->
                                <div class="min-w-0 flex-1 basis-[calc(100%-5rem)] sm:basis-auto">
                                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ item.nombre }}</h3>
                                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ item.precio.toFixed(2) }}€ / {{ item.unidad }}</p>
                                </div>

                                <!-- Controles de cantidad -->
                                <div class="flex items-center gap-2 rounded-xl border border-gray-200 dark:border-gray-600 px-3 py-1.5">
                                    <button
                                        @click="actualizarCantidad(item.id, -1)"
                                        class="flex h-7 w-7 items-center justify-center rounded-lg text-gray-500 dark:text-gray-400 transition-colors hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-800 dark:hover:text-gray-200"
                                        aria-label="Reducir cantidad"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                        </svg>
                                    </button>
                                    <span class="w-8 text-center text-sm font-bold text-gray-800 dark:text-gray-200">{{ item.cantidad }}</span>
                                    <button
                                        @click="actualizarCantidad(item.id, 1)"
                                        class="flex h-7 w-7 items-center justify-center rounded-lg bg-primary-500 text-white transition-colors hover:bg-primary-600"
                                        aria-label="Aumentar cantidad"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Precio de línea -->
                                <div class="ml-auto sm:ml-0 sm:w-20 text-right">
                                    <span class="font-bold text-gray-900 dark:text-white">{{ (item.precio * item.cantidad).toFixed(2) }}€</span>
                                </div>

                                <!-- Eliminar -->
                                <button
                                    @click="eliminarItem(item.id)"
                                    class="flex h-8 w-8 items-center justify-center rounded-full text-gray-300 transition-colors hover:bg-red-50 hover:text-red-500"
                                    aria-label="Eliminar producto"
                                >
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Vaciar carrito -->
                    <div class="flex justify-end">
                        <button
                            @click="handleVaciar"
                            class="flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm text-gray-400 transition-colors hover:bg-red-50 hover:text-red-500"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            {{ t('cart.clear_cart') }}
                        </button>
                    </div>
                </div>

                <!-- Columna derecha: resumen del pedido -->
                <div class="lg:col-span-1">
                    <div class="sticky top-24 overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">

                        <div class="border-b border-gray-100 dark:border-gray-700 px-6 py-5">
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ t('cart.order_summary') }}</h2>
                        </div>

                        <div class="space-y-4 px-6 py-5">
                            <!-- Línea de subtotal -->
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">{{ t('cart.subtotal') }}</span>
                                <span class="font-medium text-gray-800 dark:text-gray-200">{{ totalPrecio.toFixed(2) }}€</span>
                            </div>

                            <!-- Gastos de envío -->
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">{{ t('cart.shipping_cost') }}</span>
                                <span class="font-medium text-gray-800 dark:text-gray-200">{{ gastosEnvio.toFixed(2) }}€</span>
                            </div>

                            <!-- Separador -->
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                                <div class="flex items-baseline justify-between">
                                    <span class="font-bold text-gray-900 dark:text-white">{{ t('cart.total') }}</span>
                                    <span class="text-2xl font-extrabold text-primary-600">{{ totalFinal.toFixed(2) }}€</span>
                                </div>
                                <p class="mt-1 text-xs text-gray-400">{{ t('cart.vat_included') }}</p>
                            </div>
                        </div>

                        <!-- CTA: Proceder al pago -->
                        <div class="px-6 pb-6">
                            <button
                                @click="abrirCheckout"
                                class="group flex w-full items-center justify-center gap-2 rounded-xl bg-primary-500 py-4 text-sm font-bold text-white shadow-sm transition-all hover:bg-primary-600 hover:shadow-md"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                {{ t('cart.checkout') }}
                            </button>

                            <!-- Garantías -->
                            <ul class="mt-4 space-y-2 text-xs text-gray-400">
                                <li class="flex items-center gap-1.5">
                                    <svg class="h-3.5 w-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    {{ t('cart.local_products') }}
                                </li>
                                <li class="flex items-center gap-1.5">
                                    <svg class="h-3.5 w-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    {{ t('cart.secure_payment') }}
                                </li>
                                <li class="flex items-center gap-1.5">
                                    <svg class="h-3.5 w-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    {{ t('cart.home_delivery') }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- ── Modal de checkout multi-step ───────────────────────────────────────── -->
    <Transition
        enter-active-class="transition duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="mostrarCheckout"
            class="fixed inset-0 z-50 flex items-end justify-center bg-black/60 px-4 pb-0 sm:items-center sm:pb-4"
            @click.self="cerrarCheckout"
        >
            <Transition
                enter-active-class="transition duration-300"
                enter-from-class="translate-y-full sm:translate-y-0 sm:scale-95 sm:opacity-0"
                enter-to-class="translate-y-0 sm:scale-100 sm:opacity-100"
                leave-active-class="transition duration-200"
                leave-from-class="translate-y-0 sm:scale-100 sm:opacity-100"
                leave-to-class="translate-y-full sm:translate-y-0 sm:scale-95 sm:opacity-0"
                appear
            >
                <div class="w-full max-w-lg overflow-hidden rounded-t-3xl bg-white dark:bg-gray-800 shadow-2xl sm:rounded-3xl">

                    <!-- ── Header ─────────────────────────────────────────── -->
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 px-6 py-5">
                        <div class="flex items-center gap-3">
                            <!-- Step indicator -->
                            <div class="flex items-center gap-1.5">
                                <div v-for="s in [1, 2, 3]" :key="s"
                                     :class="['h-2 rounded-full transition-all duration-300',
                                         s === step ? 'w-6 bg-primary-500' :
                                         s < step   ? 'w-2 bg-primary-300' : 'w-2 bg-gray-200 dark:bg-gray-600']">
                                </div>
                            </div>
                            <div>
                                <h2 class="text-lg font-extrabold text-gray-900 dark:text-white leading-tight">{{ stepTitle }}</h2>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ totalItems }} producto{{ totalItems !== 1 ? 's' : '' }} ·
                                    <span class="font-semibold text-primary-600">{{ totalFinal.toFixed(2) }}€</span>
                                </p>
                            </div>
                        </div>
                        <button v-if="step < 3"
                            @click="cerrarCheckout"
                            class="flex h-9 w-9 items-center justify-center rounded-full text-gray-400 transition-colors hover:bg-gray-100 dark:hover:bg-gray-700"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- ══════════ STEP 1: DATOS DE ENTREGA ══════════ -->
                    <Transition
                        enter-active-class="transition duration-200"
                        enter-from-class="opacity-0 translate-x-4"
                        enter-to-class="opacity-100 translate-x-0"
                        mode="out-in"
                    >
                    <div v-if="step === 1" key="step1" class="px-6 py-6 space-y-5">

                        <!-- Error de stock -->
                        <div v-if="errores.stock" class="flex items-start gap-3 rounded-xl bg-red-50 dark:bg-red-900/20 px-4 py-3 text-sm text-red-700 dark:text-red-400">
                            <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ errores.stock }}
                        </div>

                        <!-- Dirección de envío desglosada -->
                        <div class="space-y-3">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                {{ t('checkout.shipping_address') }} <span class="text-red-500">*</span>
                            </label>

                            <!-- Calle -->
                            <div>
                                <input
                                    v-model="envioForm.calle"
                                    type="text"
                                    :placeholder="t('checkout.street')"
                                    autocomplete="street-address"
                                    maxlength="100"
                                    :class="['w-full rounded-xl border px-4 py-3 text-sm outline-none transition focus:ring-2',
                                        errores.calle ? 'border-red-400 focus:ring-red-200' : 'border-gray-200 dark:border-gray-600 focus:border-primary-400 focus:ring-primary-200',
                                        'dark:bg-gray-700 dark:text-white dark:placeholder-gray-500']"
                                />
                                <p v-if="errores.calle" class="mt-1 text-xs text-red-500">{{ errores.calle }}</p>
                            </div>

                            <!-- Número + Puerta -->
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <input
                                        v-model="envioForm.numero"
                                        type="text"
                                        :placeholder="t('checkout.number')"
                                        autocomplete="address-line2"
                                        maxlength="10"
                                        :class="['w-full rounded-xl border px-4 py-3 text-sm outline-none transition focus:ring-2',
                                            errores.numero ? 'border-red-400 focus:ring-red-200' : 'border-gray-200 dark:border-gray-600 focus:border-primary-400 focus:ring-primary-200',
                                            'dark:bg-gray-700 dark:text-white dark:placeholder-gray-500']"
                                    />
                                    <p v-if="errores.numero" class="mt-1 text-xs text-red-500">{{ errores.numero }}</p>
                                </div>
                                <div>
                                    <input
                                        v-model="envioForm.puerta"
                                        type="text"
                                        :placeholder="t('checkout.door')"
                                        maxlength="20"
                                        class="w-full rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-500 px-4 py-3 text-sm outline-none transition focus:border-primary-400 focus:ring-2 focus:ring-primary-200"
                                    />
                                </div>
                            </div>

                            <!-- CP + Localidad -->
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <div class="relative">
                                        <input
                                            v-model="envioForm.cp"
                                            type="text"
                                            inputmode="numeric"
                                            maxlength="5"
                                            pattern="[0-9]{5}"
                                            @input="envioForm.cp = $event.target.value.replace(/\D/g, '').slice(0, 5)"
                                            :placeholder="t('checkout.postal_code')"
                                            autocomplete="postal-code"
                                            :class="['w-full rounded-xl border px-4 py-3 text-sm outline-none transition focus:ring-2',
                                                errores.cp ? 'border-red-400 focus:ring-red-200' : 'border-gray-200 dark:border-gray-600 focus:border-primary-400 focus:ring-primary-200',
                                                'dark:bg-gray-700 dark:text-white dark:placeholder-gray-500']"
                                        />
                                        <div v-if="buscandoLocalidad" class="absolute right-3 top-1/2 -translate-y-1/2">
                                            <svg class="h-4 w-4 animate-spin text-primary-500" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <p v-if="errores.cp" class="mt-1 text-xs text-red-500">{{ errores.cp }}</p>
                                </div>
                                <div>
                                    <input
                                        v-model="envioForm.localidad"
                                        type="text"
                                        :placeholder="t('checkout.city')"
                                        autocomplete="address-level2"
                                        maxlength="80"
                                        :class="['w-full rounded-xl border px-4 py-3 text-sm outline-none transition focus:ring-2',
                                            errores.localidad ? 'border-red-400 focus:ring-red-200' : 'border-gray-200 dark:border-gray-600 focus:border-primary-400 focus:ring-primary-200',
                                            'dark:bg-gray-700 dark:text-white dark:placeholder-gray-500']"
                                    />
                                    <p v-if="errores.localidad" class="mt-1 text-xs text-red-500">{{ errores.localidad }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Teléfono -->
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                {{ t('checkout.phone') }} <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="envioForm.telefono_contacto"
                                type="tel"
                                inputmode="tel"
                                maxlength="20"
                                placeholder="+34 600 000 000"
                                @input="envioForm.telefono_contacto = $event.target.value.replace(/[^\d\s+\-().]/g, '').slice(0, 20)"
                                :class="['w-full rounded-xl border px-4 py-3 text-sm outline-none transition focus:ring-2',
                                    errores.telefono_contacto ? 'border-red-400 focus:ring-red-200' : 'border-gray-200 dark:border-gray-600 focus:border-primary-400 focus:ring-primary-200',
                                    'dark:bg-gray-700 dark:text-white dark:placeholder-gray-500']"
                            />
                            <p v-if="errores.telefono_contacto" class="mt-1 text-xs text-red-500">{{ errores.telefono_contacto }}</p>
                        </div>

                        <!-- Notas -->
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                {{ t('checkout.notes') }}
                            </label>
                            <textarea
                                v-model="envioForm.notas"
                                rows="2"
                                maxlength="500"
                                placeholder="Instrucciones de entrega, alergias, preferencias…"
                                class="w-full resize-none rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-500 px-4 py-3 text-sm outline-none transition focus:border-primary-400 focus:ring-2 focus:ring-primary-200"
                            />
                        </div>

                        <!-- Resumen rápido -->
                        <div class="rounded-xl bg-gray-50 dark:bg-gray-700/50 px-4 py-4 text-sm">
                            <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                <span>{{ t('cart.subtotal') }}</span>
                                <span class="font-medium text-gray-800 dark:text-gray-200">{{ totalPrecio.toFixed(2) }}€</span>
                            </div>
                            <div class="mt-1 flex justify-between text-gray-600 dark:text-gray-400">
                                <span>{{ t('cart.shipping_cost') }}</span>
                                <span :class="gastosEnvio === 0 ? 'font-semibold text-green-600' : 'font-medium text-gray-800 dark:text-gray-200'">
                                    {{ gastosEnvio === 0 ? t('orders.free') : gastosEnvio.toFixed(2) + '€' }}
                                </span>
                            </div>
                            <div class="mt-3 flex justify-between border-t border-gray-200 dark:border-gray-600 pt-3">
                                <span class="font-bold text-gray-900 dark:text-white">{{ t('cart.total') }}</span>
                                <span class="text-lg font-extrabold text-primary-600">{{ totalFinal.toFixed(2) }}€</span>
                            </div>
                        </div>

                        <!-- Botón siguiente -->
                        <div class="border-t border-gray-100 dark:border-gray-700 pt-4">
                            <button
                                @click="siguientePaso"
                                class="flex w-full items-center justify-center gap-2 rounded-xl bg-primary-500 py-3.5 text-sm font-bold text-white shadow-sm transition hover:bg-primary-600 hover:shadow-md"
                            >
                                {{ t('checkout.next') }}
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    </Transition>

                    <!-- ══════════ STEP 2: PAGO ══════════ -->
                    <Transition
                        enter-active-class="transition duration-200"
                        enter-from-class="opacity-0 translate-x-4"
                        enter-to-class="opacity-100 translate-x-0"
                        mode="out-in"
                    >
                    <div v-if="step === 2" key="step2" class="px-6 py-6 space-y-5">

                        <!-- ── Selector de método de pago ── -->
                        <div class="grid grid-cols-3 gap-2">
                            <!-- Tarjeta -->
                            <button
                                type="button"
                                @click="pagoForm.metodo = 'tarjeta'"
                                :class="['flex flex-col items-center gap-1 rounded-xl border-2 p-2.5 sm:p-3 text-xs sm:text-sm font-semibold transition-all',
                                    pagoForm.metodo === 'tarjeta'
                                        ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300'
                                        : 'border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:border-gray-300']"
                            >
                                <svg class="h-4 w-4 sm:h-5 sm:w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                                <span class="text-[10px] sm:text-xs leading-tight text-center">{{ t('checkout.pay_with_card') }}</span>
                            </button>
                            <!-- RustiCoin -->
                            <button
                                type="button"
                                @click="pagoForm.metodo = 'rusticoin'"
                                :disabled="rcDisponible < totalFinal"
                                :class="['flex flex-col items-center gap-1 rounded-xl border-2 p-2.5 sm:p-3 text-xs sm:text-sm font-semibold transition-all disabled:opacity-40 disabled:cursor-not-allowed',
                                    pagoForm.metodo === 'rusticoin'
                                        ? 'border-orange-500 bg-orange-50 dark:bg-orange-900/20 text-orange-700 dark:text-orange-300'
                                        : 'border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:border-gray-300']"
                            >
                                <svg class="h-4 w-4 sm:h-5 sm:w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-[10px] sm:text-xs leading-tight text-center">RustiCoin</span>
                                <span :class="['text-[9px] sm:text-[10px] font-normal leading-none', rcDisponible >= totalFinal ? 'text-green-600 dark:text-green-400' : 'text-red-500']">
                                    {{ Number(rcDisponible).toFixed(0) }} RC
                                </span>
                            </button>
                            <!-- RC + Tarjeta (mixto) -->
                            <button
                                type="button"
                                @click="pagoForm.metodo = 'mixto'; pagoForm.rcACusar = Math.min(rcDisponible, rcMaxMixto)"
                                :disabled="rcDisponible <= 0"
                                :class="['flex flex-col items-center gap-1 rounded-xl border-2 p-2.5 sm:p-3 text-xs sm:text-sm font-semibold transition-all disabled:opacity-40 disabled:cursor-not-allowed',
                                    pagoForm.metodo === 'mixto'
                                        ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300'
                                        : 'border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:border-gray-300']"
                            >
                                <svg class="h-4 w-4 sm:h-5 sm:w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                </svg>
                                <span class="text-[10px] sm:text-xs leading-tight text-center">{{ t('checkout.pay_with_mixed') }}</span>
                            </button>
                        </div>
                        <p v-if="pagoForm.metodo === 'rusticoin' && rcDisponible < totalFinal" class="text-xs text-red-500 text-center">
                            Saldo insuficiente. <Link :href="route('monedero.index')" class="underline font-semibold">Recargar monedero</Link>
                        </p>

                        <!-- Input RC para pago mixto -->
                        <div v-if="pagoForm.metodo === 'mixto'" class="rounded-xl border border-indigo-200 dark:border-indigo-800 bg-indigo-50 dark:bg-indigo-900/20 p-4 space-y-3">
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-semibold text-indigo-700 dark:text-indigo-300">{{ t('checkout.rc_to_use') }}</span>
                                <span class="text-xs text-indigo-500 dark:text-indigo-400">{{ t('checkout.available_rc', { n: Number(rcDisponible).toFixed(2) }) }}</span>
                            </div>
                            <input
                                :value="pagoForm.rcACusar"
                                @input="pagoForm.rcACusar = Math.min(rcMaxMixto, Math.max(0, parseFloat($event.target.value) || 0))"
                                type="number"
                                :min="1"
                                :max="rcMaxMixto"
                                step="0.01"
                                class="w-full rounded-xl border border-indigo-300 dark:border-indigo-600 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-mono outline-none focus:ring-2 focus:ring-indigo-400 dark:text-white"
                                placeholder="Cantidad de RC..."
                            />
                            <p v-if="erroresPago.rcACusar" class="text-xs text-red-500">{{ erroresPago.rcACusar }}</p>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-indigo-600 dark:text-indigo-400">{{ Number(pagoForm.rcACusar || 0).toFixed(2) }} RC = {{ Number(pagoForm.rcACusar || 0).toFixed(2) }}€</span>
                                <span class="font-semibold text-gray-700 dark:text-gray-300">+ {{ Number(restanteTarjeta).toFixed(2) }}€ {{ t('checkout.rest_card') }}</span>
                            </div>
                        </div>

                        <!-- ── Formulario tarjeta ── -->
                        <div v-if="pagoForm.metodo === 'tarjeta' || pagoForm.metodo === 'mixto'" class="space-y-4">

                            <!-- Número de tarjeta -->
                            <div>
                                <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    {{ t('checkout.card_number') }} <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input
                                        :value="pagoForm.numero"
                                        @input="onCardInput"
                                        type="text"
                                        inputmode="numeric"
                                        maxlength="19"
                                        placeholder="1234 5678 9012 3456"
                                        autocomplete="cc-number"
                                        :class="['w-full rounded-xl border py-3 pl-4 pr-12 text-sm font-mono tracking-widest outline-none transition focus:ring-2',
                                            erroresPago.numero ? 'border-red-400 focus:ring-red-200' : 'border-gray-200 dark:border-gray-600 focus:border-primary-400 focus:ring-primary-200',
                                            'dark:bg-gray-700 dark:text-white dark:placeholder-gray-500']"
                                    />
                                    <!-- Card brand icon -->
                                    <div class="absolute right-3 top-1/2 -translate-y-1/2">
                                        <span v-if="cardBrand === 'visa'" class="text-xs font-black italic text-blue-700 dark:text-blue-400">VISA</span>
                                        <span v-else-if="cardBrand === 'mastercard'" class="text-xs font-bold text-orange-600 dark:text-orange-400">MC</span>
                                        <span v-else-if="cardBrand === 'amex'" class="text-xs font-bold text-blue-500 dark:text-blue-300">AMEX</span>
                                        <svg v-else class="h-5 w-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                        </svg>
                                    </div>
                                </div>
                                <p v-if="erroresPago.numero" class="mt-1 text-xs text-red-500">{{ erroresPago.numero }}</p>
                            </div>

                            <!-- Titular -->
                            <div>
                                <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    {{ t('checkout.card_holder') }} <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model="pagoForm.titular"
                                    type="text"
                                    placeholder="NOMBRE APELLIDOS"
                                    autocomplete="cc-name"
                                    :class="['w-full rounded-xl border px-4 py-3 text-sm uppercase outline-none transition focus:ring-2',
                                        erroresPago.titular ? 'border-red-400 focus:ring-red-200' : 'border-gray-200 dark:border-gray-600 focus:border-primary-400 focus:ring-primary-200',
                                        'dark:bg-gray-700 dark:text-white dark:placeholder-gray-500']"
                                />
                                <p v-if="erroresPago.titular" class="mt-1 text-xs text-red-500">{{ erroresPago.titular }}</p>
                            </div>

                            <!-- Caducidad + CVV -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        {{ t('checkout.card_expiry') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        :value="pagoForm.expiry"
                                        @input="onExpiryInput"
                                        type="text"
                                        inputmode="numeric"
                                        maxlength="5"
                                        placeholder="MM/AA"
                                        autocomplete="cc-exp"
                                        :class="['w-full rounded-xl border px-4 py-3 text-sm font-mono outline-none transition focus:ring-2',
                                            erroresPago.expiry ? 'border-red-400 focus:ring-red-200' : 'border-gray-200 dark:border-gray-600 focus:border-primary-400 focus:ring-primary-200',
                                            'dark:bg-gray-700 dark:text-white dark:placeholder-gray-500']"
                                    />
                                    <p v-if="erroresPago.expiry" class="mt-1 text-xs text-red-500">{{ erroresPago.expiry }}</p>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        {{ t('checkout.card_cvv') }} <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input
                                            :value="pagoForm.cvv"
                                            @input="onCvvInput"
                                            type="password"
                                            inputmode="numeric"
                                            maxlength="3"
                                            placeholder="•••"
                                            autocomplete="cc-csc"
                                            :class="['w-full rounded-xl border px-4 py-3 text-sm font-mono outline-none transition focus:ring-2',
                                                erroresPago.cvv ? 'border-red-400 focus:ring-red-200' : 'border-gray-200 dark:border-gray-600 focus:border-primary-400 focus:ring-primary-200',
                                                'dark:bg-gray-700 dark:text-white dark:placeholder-gray-500']"
                                        />
                                    </div>
                                    <p v-if="erroresPago.cvv" class="mt-1 text-xs text-red-500">{{ erroresPago.cvv }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Importe a pagar -->
                        <div class="rounded-xl bg-gray-50 dark:bg-gray-700/50 px-4 py-3 space-y-1">
                            <template v-if="pagoForm.metodo === 'mixto'">
                                <div class="flex items-center justify-between text-xs text-indigo-600 dark:text-indigo-400">
                                    <span>RC: {{ Number(pagoForm.rcACusar || 0).toFixed(2) }} RC</span>
                                    <span>-{{ Number(pagoForm.rcACusar || 0).toFixed(2) }}€</span>
                                </div>
                                <div class="flex items-center justify-between text-sm font-semibold border-t border-gray-200 dark:border-gray-600 pt-1 mt-1">
                                    <span class="text-gray-600 dark:text-gray-400">{{ t('checkout.rest_card') }}</span>
                                    <span class="text-xl font-extrabold text-primary-600">{{ Number(restanteTarjeta).toFixed(2) }}€</span>
                                </div>
                            </template>
                            <div v-else class="flex items-center justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ t('cart.total') }}</span>
                                <span class="text-xl font-extrabold text-primary-600">{{ totalFinal.toFixed(2) }}€</span>
                            </div>
                        </div>

                        <!-- Sellos de seguridad -->
                        <div class="flex flex-wrap items-center justify-center gap-3 text-xs text-gray-400">
                            <span class="flex items-center gap-1">
                                <svg class="h-3.5 w-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                SSL 256-bit
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="h-3.5 w-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                3D Secure
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="h-3.5 w-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Datos cifrados
                            </span>
                        </div>

                        <!-- Botones -->
                        <div class="border-t border-gray-100 dark:border-gray-700 pt-4 flex flex-col gap-3">
                            <button
                                @click="pagar"
                                class="flex w-full items-center justify-center gap-2 rounded-xl bg-green-600 py-3.5 text-sm font-bold text-white shadow-sm transition hover:bg-green-700 hover:shadow-md"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                {{ t('checkout.place_order') }} {{ pagoForm.metodo === 'mixto' ? `${Number(pagoForm.rcACusar || 0).toFixed(2)} RC + ${Number(restanteTarjeta).toFixed(2)}€` : `${totalFinal.toFixed(2)}€` }}
                            </button>
                            <button
                                @click="step = 1"
                                class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors inline-flex items-center gap-1"
                            >
                                <ChevronLeft class="h-4 w-4" /> {{ t('checkout.back') }}
                            </button>
                        </div>
                    </div>
                    </Transition>

                    <!-- ══════════ STEP 3: PROCESANDO ══════════ -->
                    <div v-if="step === 3" key="step3" class="px-6 py-16 flex flex-col items-center text-center">
                        <!-- Animación spinner + texto secuencial -->
                        <div class="relative mb-6 flex h-20 w-20 items-center justify-center">
                            <svg class="absolute inset-0 h-20 w-20 animate-spin text-primary-200" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            <div class="h-12 w-12 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                                <svg class="h-6 w-6 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">{{ t('checkout.processing') }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 max-w-xs">
                            {{ t('checkout.processing_sub') }}
                        </p>
                        <div class="mt-6 flex items-center gap-2 rounded-full bg-green-50 dark:bg-green-900/20 px-4 py-2 text-xs font-medium text-green-700 dark:text-green-400">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Conexión segura SSL
                        </div>
                    </div>

                </div>
            </Transition>
        </div>
    </Transition>
</template>
