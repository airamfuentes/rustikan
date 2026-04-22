<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import CarritoCompra from '@/Components/CarritoCompra.vue';
import MapaTiendas from '@/Components/MapaTiendas.vue';
import DarkModeToggle from '@/Components/DarkModeToggle.vue';
import Toast from '@/Components/Toast.vue';
import { useDarkMode } from '@/Composables/useDarkMode';

const page = usePage();
const user = computed(() => page.props.auth.user);

// ── Toast system ─────────────────────────────────────────────────────────────
const toasts = ref([]);
const addToast = (type, title, message = '') => {
    const id = Date.now() + Math.random();
    toasts.value.push({ id, type, title, message });
};
const removeToast = (id) => {
    toasts.value = toasts.value.filter(t => t.id !== id);
};
watch(
    () => page.props.flash,
    (flash) => {
        if (!flash) return;
        if (flash.success) addToast('success', '¡Éxito!', flash.success);
        if (flash.error)   addToast('error',   'Error',   flash.error);
        if (flash.info)    addToast('info',     'Info',    flash.info);
        if (flash.warning) addToast('warning',  'Aviso',   flash.warning);
    },
    { deep: true, immediate: true },
);

const props = defineProps({
    tiendas: {
        type: Array,
        default: () => [],
    },
    categorias: {
        type: Array,
        default: () => [],
    },
});

const scrolled = ref(false);
const busqueda           = ref('');
const showDropdown       = ref(false);
const showProfileMenu    = ref(false);
const sugerenciasTiendas = ref([]);
const searchRef          = ref(null);
const profileMenuRef     = ref(null);
let scrollTimeout        = null;
let searchTimeout        = null;

const { isDark, toggleDark } = useDarkMode();

const handleScroll = () => {
    if (scrollTimeout) return;
    scrollTimeout = setTimeout(() => {
        scrolled.value = window.scrollY > 20;
        scrollTimeout = null;
    }, 100);
};

const handleClickOutside = (event) => {
    if (searchRef.value && !searchRef.value.contains(event.target)) {
        showDropdown.value = false;
    }
    if (profileMenuRef.value && !profileMenuRef.value.contains(event.target)) {
        showProfileMenu.value = false;
    }
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
    document.addEventListener('click', handleClickOutside, true);
    // Mostrar toast si viene de ?verified=1 (por si se llega sin flash de sesión)
    const params = new URLSearchParams(window.location.search);
    if (params.get('verified') === '1' && !page.props.flash?.success) {
        const nombre = user.value?.name ?? '';
        addToast('success',
            '🌿 ¡Cuenta verificada!',
            nombre ? `Bienvenido/a a Rustikan, ${nombre}. Ya puedes empezar a comprar.` : 'Tu correo ha sido verificado. ¡Bienvenido/a!');
    }
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
    document.removeEventListener('click', handleClickOutside, true);
    if (searchTimeout) clearTimeout(searchTimeout);
    if (scrollTimeout) clearTimeout(scrollTimeout);
});

// Normaliza un texto: minúsculas + sin tildes/diacríticos
const normalizar = (str) =>
    (str ?? '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');

const buscarTiendas = () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        const q = normalizar(busqueda.value.trim());
        if (q === '') {
            showDropdown.value = false;
            sugerenciasTiendas.value = [];
        } else {
            sugerenciasTiendas.value = props.tiendas.filter(tienda =>
                normalizar(tienda.nombre).includes(q) ||
                normalizar(tienda.direccion).includes(q) ||
                normalizar(tienda.descripcion).includes(q) ||
                normalizar(tienda.categoria?.nombre).includes(q)
            );
            showDropdown.value = true;
        }
    }, 300);
};
</script>

<template>
    <Head title="Tiendas - Rustikan" />

    <!-- Toast container -->
    <div class="pointer-events-none fixed inset-0 z-[9999] flex flex-col items-end justify-start gap-3 p-6">
        <Toast
            v-for="toast in toasts"
            :key="toast.id"
            :type="toast.type"
            :title="toast.title"
            :message="toast.message"
            @close="removeToast(toast.id)"
        />
    </div>

    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
        <!-- Navbar Transformable -->
        <nav
            :class="[
                'fixed z-50 bg-white dark:bg-gray-900 transition-all duration-300',
                scrolled
                    ? 'top-0 left-0 right-0 border-b border-gray-200 dark:border-gray-700 shadow-md'
                    : 'top-4 left-12 right-12 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm'
            ]"
        >
            <div class="mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between gap-4">
                    <!-- Logo -->
                    <div class="flex shrink-0 items-center">
                        <Link href="/" class="flex items-center">
                            <img src="/images/logo.png" alt="Rustikan" class="h-10 w-auto" />
                        </Link>
                    </div>

                    <!-- Buscador central -->
                    <div class="hidden flex-1 max-w-2xl md:flex">
                        <div ref="searchRef" class="relative w-full">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input
                                v-model="busqueda"
                                @input="buscarTiendas"
                                type="text"
                                placeholder="Buscar tiendas o ubicaciones..."
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 py-2 pl-12 pr-4 text-sm text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500"
                            />

                            <!-- Dropdown de Sugerencias -->
                            <Transition
                                enter-active-class="transition ease-out duration-200"
                                enter-from-class="opacity-0 translate-y-1"
                                enter-to-class="opacity-100 translate-y-0"
                                leave-active-class="transition ease-in duration-150"
                                leave-from-class="opacity-100 translate-y-0"
                                leave-to-class="opacity-0 translate-y-1"
                            >
                                <div
                                    v-if="showDropdown && sugerenciasTiendas.length > 0"
                                    class="absolute left-0 right-0 top-full mt-2 max-h-96 overflow-y-auto rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-xl will-change-transform"
                                >
                                    <Link
                                        v-for="tienda in sugerenciasTiendas"
                                        :key="tienda.id"
                                        :href="`/tienda/${tienda.slug}`"
                                        class="flex items-center gap-3 border-b border-gray-100 dark:border-gray-700 p-3 transition-colors hover:bg-gray-50 dark:hover:bg-gray-700 last:border-b-0"
                                    >
                                        <!-- Imagen -->
                                        <img
                                            :src="tienda.imagen_portada ? `/storage/${tienda.imagen_portada}` : tienda.logo ? `/storage/${tienda.logo}` : '/images/logo.png'"
                                            :alt="tienda.nombre"
                                            loading="lazy"
                                            class="h-16 w-16 flex-shrink-0 rounded-full object-cover"
                                        />

                                        <!-- Información -->
                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                                {{ tienda.nombre }}
                                            </h3>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1 mt-0.5">
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                {{ tienda.direccion }}
                                            </p>
                                            <div class="flex items-center gap-2 mt-1">
                                                <!-- Estrellas -->
                                                <div class="flex items-center gap-0.5">
                                                    <svg v-for="i in 5" :key="i" class="h-3 w-3" :class="i <= tienda.valoracion ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600'" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                    </svg>
                                                </div>
                                                <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ Number(tienda.valoracion).toFixed(1) }}</span>
                                                <span class="text-xs text-gray-400 dark:text-gray-500">•</span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ tienda.total_resenas }} reseñas</span>
                                            </div>
                                        </div>

                                        <!-- Flecha -->
                                        <svg class="h-5 w-5 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </Link>

                                    <!-- Sin resultados en dropdown -->
                                    <div
                                        v-if="sugerenciasTiendas.length === 0 && busqueda.trim() !== ''"
                                        class="p-4 text-center text-sm text-gray-500"
                                    >
                                        No se encontraron tiendas
                                    </div>
                                </div>
                            </Transition>
                        </div>
                    </div>

                    <!-- Acciones derecha -->
                    <div class="flex shrink-0 items-center gap-3">
                        <!-- Carrito -->
                        <CarritoCompra />

                        <!-- Selector de idioma -->
                        <LanguageSwitcher />

                        <!-- Dark mode toggle (siempre visible) -->
                        <DarkModeToggle />

                        <!-- Botón Acceder (no autenticado) -->
                        <Link
                            v-if="!user"
                            :href="route('login')"
                            class="rounded-lg bg-primary-500 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-primary-600"
                        >
                            Acceder
                        </Link>

                        <!-- Acceso rápido Admin -->
                        <Link
                            v-if="user && user.role === 'admin'"
                            :href="route('admin.dashboard')"
                            class="rounded-lg bg-gradient-to-r from-orange-500 to-red-500 px-3 py-2 text-sm font-bold text-white shadow-lg transition-all hover:shadow-xl"
                        >🛡️ Admin</Link>

                        <!-- Acceso rápido Owner -->
                        <Link
                            v-if="user && user.role === 'owner'"
                            :href="route('owner.panel')"
                            class="rounded-lg bg-green-500 px-3 py-2 text-sm font-bold text-white shadow-sm transition-colors hover:bg-green-600"
                        >🏪 {{ $page.props.auth.tienda?.nombre || 'Mi Tienda' }}</Link>

                        <!-- Avatar + Dropdown (autenticado) -->
                        <div
                            v-if="user"
                            ref="profileMenuRef"
                            class="relative"
                        >
                            <button
                                @click="showProfileMenu = !showProfileMenu"
                                class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-full bg-primary-500 text-white transition-colors hover:bg-primary-600 focus:outline-none"
                            >
                                <img v-if="user.avatar" :src="`/storage/${user.avatar}`" class="h-full w-full object-cover" alt="Avatar" />
                                <span v-else class="text-sm font-semibold">{{ user.name?.charAt(0)?.toUpperCase() }}</span>
                            </button>

                            <!-- Menú desplegable -->
                            <Transition
                                enter-active-class="transition ease-out duration-150"
                                enter-from-class="opacity-0 scale-95"
                                enter-to-class="opacity-100 scale-100"
                                leave-active-class="transition ease-in duration-100"
                                leave-from-class="opacity-100 scale-100"
                                leave-to-class="opacity-0 scale-95"
                            >
                                <div
                                    v-if="showProfileMenu"
                                    class="absolute right-0 top-12 z-50 w-56 overflow-hidden rounded-xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-lg"
                                >
                                    <div class="border-b border-gray-100 dark:border-gray-700 px-4 py-3">
                                        <p class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ user.name }}</p>
                                        <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ user.email }}</p>
                                        <p v-if="user.telefono" class="truncate text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ user.telefono }}</p>
                                    </div>

                                    <!-- Toggle dark mode -->
                                    <button
                                        @click="toggleDark"
                                        class="flex w-full items-center gap-3 border-b border-gray-100 dark:border-gray-700 px-4 py-2.5 text-sm transition-colors hover:bg-gray-50 dark:hover:bg-gray-700"
                                    >
                                        <!-- Icono animado sol/luna -->
                                        <div class="relative h-5 w-5 shrink-0">
                                            <!-- Sol -->
                                            <svg class="absolute inset-0 h-5 w-5 text-yellow-500 transition-all duration-300"
                                                 :class="isDark ? 'opacity-0 scale-50 rotate-90' : 'opacity-100 scale-100 rotate-0'"
                                                 fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 2.25a.75.75 0 01.75.75v2.25a.75.75 0 01-1.5 0V3a.75.75 0 01.75-.75zM7.5 12a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM18.894 6.166a.75.75 0 00-1.06-1.06l-1.591 1.59a.75.75 0 101.06 1.061l1.591-1.59zM21.75 12a.75.75 0 01-.75.75h-2.25a.75.75 0 010-1.5H21a.75.75 0 01.75.75zM17.834 18.894a.75.75 0 001.06-1.06l-1.59-1.591a.75.75 0 10-1.061 1.06l1.59 1.591zM12 18a.75.75 0 01.75.75V21a.75.75 0 01-1.5 0v-2.25A.75.75 0 0112 18zM7.166 17.834a.75.75 0 00-1.06 1.06l1.59 1.591a.75.75 0 101.061-1.06l-1.591-1.591zM6 12a.75.75 0 01-.75.75H3a.75.75 0 010-1.5h2.25A.75.75 0 016 12zM6.166 6.166a.75.75 0 001.061 1.06l1.59-1.59a.75.75 0 10-1.06-1.061l-1.591 1.59z" />
                                            </svg>
                                            <!-- Luna -->
                                            <svg class="absolute inset-0 h-5 w-5 text-blue-400 transition-all duration-300"
                                                 :class="isDark ? 'opacity-100 scale-100 rotate-0' : 'opacity-0 scale-50 -rotate-90'"
                                                 fill="currentColor" viewBox="0 0 24 24">
                                                <path fill-rule="evenodd" d="M9.528 1.718a.75.75 0 01.162.819A8.97 8.97 0 009 6a9 9 0 009 9 8.97 8.97 0 003.463-.69.75.75 0 01.981.98 10.503 10.503 0 01-9.694 6.46c-5.799 0-10.5-4.701-10.5-10.5 0-4.368 2.667-8.112 6.46-9.694a.75.75 0 01.818.162z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <span class="text-gray-700 dark:text-gray-300">{{ isDark ? 'Modo oscuro' : 'Modo claro' }}</span>
                                        <!-- Toggle switch visual -->
                                        <div
                                            class="relative ml-auto inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors duration-200"
                                            :class="isDark ? 'bg-primary-500' : 'bg-gray-300'"
                                        >
                                            <span
                                                class="inline-block h-5 w-5 transform rounded-full bg-white shadow-md transition-transform duration-200"
                                                :class="isDark ? 'translate-x-[22px]' : 'translate-x-0.5'"
                                            />
                                        </div>
                                    </button>

                                    <Link
                                        :href="route('carrito')"
                                        class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 transition-colors hover:bg-gray-50 dark:hover:bg-gray-700"
                                        @click="showProfileMenu = false"
                                    >
                                        <svg class="h-4 w-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        Ver carrito
                                    </Link>
                                    <Link
                                        :href="route('pedidos.index')"
                                        class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 transition-colors hover:bg-gray-50 dark:hover:bg-gray-700"
                                        @click="showProfileMenu = false"
                                    >
                                        <svg class="h-4 w-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        Mis pedidos
                                    </Link>
                                    <Link
                                        :href="route('profile.edit')"
                                        class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 transition-colors hover:bg-gray-50 dark:hover:bg-gray-700"
                                        @click="showProfileMenu = false"
                                    >
                                        <svg class="h-4 w-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        Mi perfil
                                    </Link>
                                    <Link
                                        :href="route('home')"
                                        class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 transition-colors hover:bg-gray-50 dark:hover:bg-gray-700"
                                        @click="showProfileMenu = false"
                                    >
                                        <svg class="h-4 w-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        Contáctanos
                                    </Link>
                                    <Link
                                        v-if="user.role === 'admin'"
                                        :href="route('admin.dashboard')"
                                        class="flex items-center gap-3 px-4 py-2.5 text-sm font-semibold text-orange-600 transition-colors hover:bg-orange-50 dark:hover:bg-orange-900/20"
                                        @click="showProfileMenu = false"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                        Panel de administración
                                    </Link>
                                    <Link
                                        v-if="user.role === 'owner'"
                                        :href="route('owner.panel')"
                                        class="flex items-center gap-3 px-4 py-2.5 text-sm font-semibold text-green-600 transition-colors hover:bg-green-50 dark:hover:bg-green-900/20"
                                        @click="showProfileMenu = false"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                        </svg>
                                        Mi tienda
                                    </Link>
                                    <div class="border-t border-gray-100 dark:border-gray-700">
                                        <Link
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                            class="flex w-full items-center gap-3 px-4 py-2.5 text-sm text-red-600 transition-colors hover:bg-red-50 dark:hover:bg-red-900/20"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                            </svg>
                                            Cerrar sesión
                                        </Link>
                                    </div>
                                </div>
                            </Transition>
                        </div>

                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero: imagen de portada a pantalla completa -->
        <div class="relative h-[520px] overflow-hidden">
            <div
                class="absolute inset-0 bg-cover bg-no-repeat"
                style="background-image: url('/images/fondo_1.png'); background-position: center 35%;"
            ></div>
            <div class="absolute inset-0 bg-gradient-to-b from-black/40 via-black/30 to-transparent"></div>

            <!-- Curva inferior -->
            <div class="absolute bottom-0 left-0 right-0 z-10">
                <svg viewBox="0 0 1200 50" preserveAspectRatio="none" class="h-12 w-full text-gray-50 dark:text-gray-900" fill="currentColor">
                    <path d="M0 50C300 10 900 10 1200 50V50H0Z" />
                </svg>
            </div>
        </div>

        <!-- Sección de Categorías -->
        <section id="categorias" class="py-16">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                <!-- Encabezado -->
                <div class="mb-10 text-center">
                    <h2 class="text-4xl font-extrabold tracking-tight text-gray-900 dark:text-white">¿Qué estás buscando?</h2>
                </div>

                <!-- Grid de categorías circulares -->
                <div class="flex flex-wrap justify-center gap-8">
                    <Link
                        v-for="(cat, index) in categorias"
                        :key="cat.id"
                        :href="route('categoria.tiendas', cat.slug)"
                        class="group flex flex-col items-center"
                    >
                        <!-- Círculo + etiqueta superpuesta -->
                        <div class="relative flex h-24 w-24 items-center justify-center rounded-full border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 shadow-sm transition-all duration-300 group-hover:scale-105 group-hover:border-primary-400 group-hover:shadow-xl">
                            <span class="flex select-none items-center justify-center pb-4 text-3xl leading-none">{{ cat.icono }}</span>

                            <!-- Etiqueta superpuesta abajo -->
                            <span class="absolute bottom-0 left-1/2 -translate-x-1/2 translate-y-1/2 whitespace-nowrap rounded-full bg-white dark:bg-gray-700 px-3 py-0.5 text-[10px] font-bold uppercase tracking-wide text-gray-700 dark:text-gray-200 shadow ring-1 ring-gray-200 dark:ring-gray-600 transition-colors group-hover:bg-primary-500 group-hover:text-white group-hover:ring-primary-500">
                                {{ cat.nombre }}
                            </span>
                        </div>

                        <!-- Espaciado para la etiqueta que sobresale -->
                        <div class="h-4"></div>
                    </Link>
                </div>

                <!-- Estado vacío -->
                <div v-if="categorias.length === 0" class="py-12 text-center text-gray-400">
                    Cargando categorías...
                </div>
            </div>
        </section>

        <!-- Sección Mapa de Tiendas -->
        <section id="mapa" class="relative pt-16 pb-28 bg-white dark:bg-gray-800">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-8 text-center">
                    <h2 class="text-4xl font-extrabold tracking-tight text-gray-900 dark:text-white">
                        Encuentra tiendas cerca de ti
                    </h2>
                    <p class="mt-3 text-lg text-gray-500 dark:text-gray-400">
                        Explora los productores locales en el mapa interactivo
                    </p>
                </div>

                <MapaTiendas
                    :tiendas="tiendas"
                    :categorias="categorias"
                    height="480px"
                />
            </div>

            <!-- Curva inferior: transición a sección naranja -->
            <div class="absolute bottom-0 left-0 right-0">
                <svg class="block w-full h-20 text-primary-400" viewBox="0 0 1440 80" preserveAspectRatio="none" aria-hidden="true">
                    <path d="M0,80 C480,0 960,0 1440,80 L1440,80 L0,80 Z" fill="currentColor"/>
                </svg>
            </div>
        </section>

        <!-- Sección Colabora con Rustikan -->
        <section class="relative overflow-hidden bg-primary-400 pt-4 pb-28">
            <div class="absolute inset-0 overflow-hidden opacity-15 pointer-events-none">
                <svg class="absolute left-1/2 top-1/2 h-32 w-32 -translate-x-1/2 -translate-y-1/2 text-primary-600" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                    <path fill="currentColor" d="M47.1,-78.5C60.9,-70.8,71.8,-57.1,78.3,-41.5C84.8,-25.9,86.9,-8.4,84.3,7.9C81.7,24.2,74.4,39.3,64.2,51.9C54,64.5,40.9,74.6,25.8,79.8C10.7,85,-6.4,85.3,-22.3,81.5C-38.2,77.7,-53,69.8,-64.5,58.3C-76,46.8,-84.2,31.7,-86.8,15.3C-89.4,-1.1,-86.4,-18.8,-78.8,-33.8C-71.2,-48.8,-59,-61.1,-44.5,-68.2C-30,-75.3,-13.2,-77.2,2.8,-81.7C18.8,-86.2,33.3,-86.2,47.1,-78.5Z" transform="translate(100 100)" />
                </svg>
            </div>

            <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Icono y título -->
                <div class="mb-12 text-center">
                    <div class="mb-4 flex justify-center">
                        <div class="rounded-full bg-white p-4 shadow-lg">
                            <svg class="h-12 w-12 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                    </div>
                    <h2 class="text-4xl font-extrabold tracking-tight text-white">Colabora con Rustikan</h2>
                </div>

                <!-- Grid de opciones -->
                <div class="grid gap-8 md:grid-cols-3">
                    <!-- Hazte Repartidor -->
                    <div class="flex flex-col items-center text-center">
                        <div class="relative mb-6">
                            <!-- Onda decorativa -->
                            <svg class="absolute -inset-8 h-64 w-64 text-white opacity-55" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                                <path fill="currentColor" d="M43.3,-72.8C54.5,-63.8,60.3,-47.5,65.9,-32.1C71.5,-16.7,76.9,-2.2,75.4,11.5C73.9,25.2,65.5,38.1,54.8,48.7C44.1,59.3,31.1,67.6,16.5,71.8C1.9,76,-14.3,76.1,-28.9,71.5C-43.5,66.9,-56.5,57.6,-65.8,45.2C-75.1,32.8,-80.7,17.3,-81.2,1.5C-81.7,-14.3,-77.1,-30.4,-68.3,-43.3C-59.5,-56.2,-46.5,-65.9,-32.8,-73.5C-19.1,-81.1,-4.7,-86.6,8.7,-84.3C22.1,-82,32.1,-81.8,43.3,-72.8Z" transform="translate(100 100)" />
                            </svg>
                            <div class="relative h-48 w-48 overflow-hidden rounded-full border-4 border-white shadow-xl">
                                <img
                                    src="https://images.unsplash.com/photo-1574068468668-a05a11f871da?w=400&h=400&fit=crop"
                                    alt="Repartidor"
                                    class="h-full w-full object-cover"
                                />
                            </div>
                        </div>
                        <h3 class="mb-3 text-xl font-bold text-white">Hazte repartidor</h3>
                        <p class="mb-6 min-h-[3rem] text-sm text-white/90">
                            Realiza entregas a través de Rustikan y disfruta de flexibilidad, libertad y ganancias competitivas en tu zona.
                        </p>
                        <button class="rounded-full bg-white px-8 py-3 text-sm font-semibold text-primary-600 transition-colors hover:bg-gray-100">
                            Regístrate aquí
                        </button>
                    </div>

                    <!-- Hazte Productor -->
                    <div class="flex flex-col items-center text-center">
                        <div class="relative mb-6">
                            <!-- Onda decorativa -->
                            <svg class="absolute -inset-8 h-64 w-64 text-white opacity-50" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                                <path fill="currentColor" d="M40.9,-68.9C52.5,-60.5,60.9,-47.2,67.4,-32.7C73.9,-18.2,78.5,-2.5,77.1,13C75.7,28.5,68.3,43.8,57.7,55.5C47.1,67.2,33.3,75.3,17.8,79.8C2.3,84.3,-15,85.2,-29.5,79.6C-44,74,-55.7,62,-65.2,47.7C-74.7,33.4,-82,16.7,-82.3,-0.2C-82.6,-17.1,-75.9,-34.2,-65.6,-47.5C-55.3,-60.8,-41.4,-70.3,-27,-75.2C-12.6,-80.1,2.3,-80.4,16.3,-76.1C30.3,-71.8,29.3,-77.3,40.9,-68.9Z" transform="translate(100 100)" />
                            </svg>
                            <div class="relative h-48 w-48 overflow-hidden rounded-full border-4 border-white shadow-xl">
                                <img
                                    src="https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=400&h=400&fit=crop"
                                    alt="Productor"
                                    class="h-full w-full object-cover"
                                />
                            </div>
                        </div>
                        <h3 class="mb-3 text-xl font-bold text-white">Hazte Productor</h3>
                        <p class="mb-6 min-h-[3rem] text-sm text-white/90">
                            ¡Crece con Rustikan! Aumenta tus ventas y alcanza más clientes con nuestra tecnología y red de usuarios.
                        </p>
                        <button class="rounded-full bg-white px-8 py-3 text-sm font-semibold text-primary-600 transition-colors hover:bg-gray-100">
                            Regístrate aquí
                        </button>
                    </div>

                    <!-- Trabaja con nosotros -->
                    <div class="flex flex-col items-center text-center">
                        <div class="relative mb-6">
                            <!-- Onda decorativa -->
                            <svg class="absolute -inset-8 h-64 w-64 text-white opacity-45" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                                <path fill="currentColor" d="M47.1,-78.5C60.9,-70.8,71.8,-57.1,78.3,-41.5C84.8,-25.9,86.9,-8.4,84.3,7.9C81.7,24.2,74.4,39.3,64.2,51.9C54,64.5,40.9,74.6,25.8,79.8C10.7,85,-6.4,85.3,-22.3,81.5C-38.2,77.7,-53,69.8,-64.5,58.3C-76,46.8,-84.2,31.7,-86.8,15.3C-89.4,-1.1,-86.4,-18.8,-78.8,-33.8C-71.2,-48.8,-59,-61.1,-44.5,-68.2C-30,-75.3,-13.2,-77.2,2.8,-81.7C18.8,-86.2,33.3,-86.2,47.1,-78.5Z" transform="translate(100 100)" />
                            </svg>
                            <div class="relative h-48 w-48 overflow-hidden rounded-full border-4 border-white shadow-xl">
                                <img
                                    src="/images/camiseta.png"
                                    alt="Equipo"
                                    class="h-full w-full object-cover"
                                />
                            </div>
                        </div>
                        <h3 class="mb-3 text-xl font-bold text-white">Trabaja con nosotros</h3>
                        <p class="mb-6 min-h-[3rem] text-sm text-white/90">
                            ¿Buscas un nuevo reto apasionante? Si eres exigente, humilde y te gusta trabajar en equipo, ¡queremos conocerte!
                        </p>
                        <button class="rounded-full bg-white px-8 py-3 text-sm font-semibold text-primary-600 transition-colors hover:bg-gray-100">
                            Regístrate aquí
                        </button>
                    </div>
                </div>
            </div>

            <!-- Curva inferior: transición al footer -->
            <div class="absolute left-0 right-0 z-10" style="bottom:-2px">
                <svg class="block w-full h-24 text-gray-900" viewBox="0 0 1440 96" preserveAspectRatio="none" aria-hidden="true">
                    <path d="M0,96 L0,40 C120,70 240,90 360,72 C480,54 600,10 720,4 C840,-2 960,30 1080,52 C1200,74 1320,86 1440,60 L1440,96 Z" fill="currentColor"/>
                </svg>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-900 text-gray-300">
            <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="grid gap-8 md:grid-cols-4">
                    <!-- Columna 1: Logo y descripción -->
                    <div class="md:col-span-1">
                        <img src="/images/logo.png" alt="Rustikan" class="mb-4 h-12 w-auto brightness-0 invert" />
                        <p class="text-sm">
                            Conectamos productores locales con consumidores, promoviendo el comercio justo y sostenible en Lanzarote.
                        </p>
                    </div>

                    <!-- Columna 2: Enlaces -->
                    <div>
                        <h3 class="mb-4 text-sm font-semibold text-white">Sobre Nosotros</h3>
                        <ul class="space-y-2 text-sm">
                            <li><a href="#" class="hover:text-primary-400 transition-colors">Quiénes somos</a></li>
                            <li><a href="#" class="hover:text-primary-400 transition-colors">Nuestra misión</a></li>
                            <li><a href="#" class="hover:text-primary-400 transition-colors">Blog</a></li>
                            <li><a href="#" class="hover:text-primary-400 transition-colors">Contacto</a></li>
                        </ul>
                    </div>

                    <!-- Columna 3: Para productores -->
                    <div>
                        <h3 class="mb-4 text-sm font-semibold text-white">Para Productores</h3>
                        <ul class="space-y-2 text-sm">
                            <li><a href="#" class="hover:text-primary-400 transition-colors">Vende con nosotros</a></li>
                            <li><a href="#" class="hover:text-primary-400 transition-colors">Hazte repartidor</a></li>
                            <li><a href="#" class="hover:text-primary-400 transition-colors">Centro de ayuda</a></li>
                            <li><a href="#" class="hover:text-primary-400 transition-colors">Preguntas frecuentes</a></li>
                        </ul>
                    </div>

                    <!-- Columna 4: Legal y redes -->
                    <div>
                        <h3 class="mb-4 text-sm font-semibold text-white">Legal</h3>
                        <ul class="space-y-2 text-sm">
                            <li><a href="#" class="hover:text-primary-400 transition-colors">Términos y condiciones</a></li>
                            <li><a href="#" class="hover:text-primary-400 transition-colors">Política de privacidad</a></li>
                            <li><a href="#" class="hover:text-primary-400 transition-colors">Cookies</a></li>
                        </ul>
                        
                        <!-- Redes sociales -->
                        <div class="mt-6">
                            <h4 class="mb-3 text-sm font-semibold text-white">Síguenos</h4>
                            <div class="flex gap-3">
                                <a href="#" class="rounded-full bg-gray-800 p-2 hover:bg-primary-500 transition-colors">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                    </svg>
                                </a>
                                <a href="#" class="rounded-full bg-gray-800 p-2 hover:bg-primary-500 transition-colors">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                    </svg>
                                </a>
                                <a href="#" class="rounded-full bg-gray-800 p-2 hover:bg-primary-500 transition-colors">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Copyright -->
                <div class="mt-8 border-t border-gray-800 pt-8 text-center text-sm">
                    <p>&copy; {{ new Date().getFullYear() }} Rustikan. Todos los derechos reservados.</p>
                </div>
            </div>
        </footer>

    </div>
</template>
    {
