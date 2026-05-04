<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import CarritoCompra from '@/Components/CarritoCompra.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';
import DarkModeToggle from '@/Components/DarkModeToggle.vue';
import CampanaNotificaciones from '@/Components/CampanaNotificaciones.vue';
import { useDarkMode } from '@/Composables/useDarkMode';
import { ShieldCheck, Store, Menu, X, Search } from 'lucide-vue-next';

const props = defineProps({
    tiendas: { type: Array, default: () => [] },
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const { isDark, toggleDark } = useDarkMode();

const scrolled           = ref(false);
const busqueda           = ref('');
const showDropdown       = ref(false);
const showHistory        = ref(false);
const showProfileMenu    = ref(false);
const showMobileMenu     = ref(false);
const sugerenciasTiendas = ref([]);
const searchRef          = ref(null);
const profileMenuRef     = ref(null);
let   scrollTimeout      = null;
let   searchTimeout      = null;

const HISTORY_KEY = 'rustikan_busquedas';
const searchHistory = ref(
    typeof window !== 'undefined'
        ? JSON.parse(localStorage.getItem(HISTORY_KEY) || '[]')
        : []
);

const saveToHistory = (query) => {
    const q = query.trim();
    if (!q) return;
    const filtered = searchHistory.value.filter(h => h !== q);
    searchHistory.value = [q, ...filtered].slice(0, 5);
    localStorage.setItem(HISTORY_KEY, JSON.stringify(searchHistory.value));
};

const removeFromHistory = (query) => {
    searchHistory.value = searchHistory.value.filter(h => h !== query);
    localStorage.setItem(HISTORY_KEY, JSON.stringify(searchHistory.value));
};

const clearHistory = () => {
    searchHistory.value = [];
    localStorage.removeItem(HISTORY_KEY);
};

const handleScroll = () => {
    if (scrollTimeout) return;
    scrollTimeout = setTimeout(() => {
        scrolled.value = window.scrollY > 20;
        scrollTimeout  = null;
    }, 100);
};

const handleClickOutside = (event) => {
    if (searchRef.value && !searchRef.value.contains(event.target)) {
        showDropdown.value = false;
        showHistory.value = false;
    }
    if (profileMenuRef.value && !profileMenuRef.value.contains(event.target)) {
        showProfileMenu.value = false;
    }
};

const normalizar = (str) =>
    (str ?? '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');

const buscarTiendas = () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    showHistory.value = false;
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

const buscarEnHome = () => {
    const q = busqueda.value.trim();
    if (q) {
        saveToHistory(q);
        showMobileMenu.value = false;
        router.visit(`/?busqueda=${encodeURIComponent(q)}`);
    }
};

const selectHistory = (query) => {
    busqueda.value = query;
    showHistory.value = false;
    showMobileMenu.value = false;
    saveToHistory(query);
    router.visit(`/?busqueda=${encodeURIComponent(query)}`);
};

// Bloquear scroll del body cuando el menú móvil está abierto
watch(showMobileMenu, (open) => {
    if (typeof document === 'undefined') return;
    document.body.style.overflow = open ? 'hidden' : '';
});

// Cerrar menú al cambiar de página
router.on('navigate', () => {
    showMobileMenu.value = false;
    showProfileMenu.value = false;
});

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
    document.addEventListener('click', handleClickOutside, true);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
    document.removeEventListener('click', handleClickOutside, true);
    if (scrollTimeout) clearTimeout(scrollTimeout);
    if (searchTimeout) clearTimeout(searchTimeout);
    if (typeof document !== 'undefined') document.body.style.overflow = '';
});
</script>

<template>
    <!-- Navbar Transformable -->
    <nav
        :class="[
            'fixed z-50 bg-white dark:bg-gray-900 transition-all duration-300',
            scrolled
                ? 'top-0 left-0 right-0 border-b border-gray-200 dark:border-gray-700 shadow-md'
                : 'top-2 left-2 right-2 sm:top-4 sm:left-6 sm:right-6 lg:left-12 lg:right-12 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm'
        ]"
    >
        <div class="mx-auto px-3 sm:px-6 lg:px-8">
            <div class="flex h-14 sm:h-16 items-center justify-between gap-2 sm:gap-4">
                <!-- Logo -->
                <div class="flex shrink-0 items-center">
                    <Link href="/" class="flex items-center">
                        <img src="/images/logo.png" alt="Rustikan" class="h-8 sm:h-10 w-auto brightness-0 dark:invert" />
                    </Link>
                </div>

                <!-- Buscador central (solo desktop) -->
                <div class="hidden flex-1 max-w-2xl md:flex">
                    <div ref="searchRef" class="relative w-full">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <Search class="h-5 w-5 text-gray-400" />
                        </div>
                        <input
                            v-model="busqueda"
                            @input="buscarTiendas"
                            @keydown.enter="buscarEnHome"
                            @focus="showHistory = busqueda.trim() === '' && searchHistory.length > 0"
                            type="text"
                            placeholder="Buscar tiendas o ubicaciones..."
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 py-2 pl-12 pr-4 text-sm text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500"
                        />

                        <!-- Historial de búsquedas -->
                        <Transition
                            enter-active-class="transition ease-out duration-200"
                            enter-from-class="opacity-0 translate-y-1"
                            enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition ease-in duration-150"
                            leave-from-class="opacity-100 translate-y-0"
                            leave-to-class="opacity-0 translate-y-1"
                        >
                            <div
                                v-if="showHistory && !showDropdown && searchHistory.length > 0"
                                class="absolute left-0 right-0 top-full mt-2 overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-xl"
                            >
                                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 px-3 py-2">
                                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Búsquedas recientes</span>
                                    <button @click.prevent="clearHistory" class="text-xs text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors">Limpiar todo</button>
                                </div>
                                <div>
                                    <button
                                        v-for="item in searchHistory"
                                        :key="item"
                                        @click.prevent="selectHistory(item)"
                                        class="flex w-full items-center gap-3 px-3 py-2.5 text-sm text-gray-700 dark:text-gray-300 transition-colors hover:bg-gray-50 dark:hover:bg-gray-700"
                                    >
                                        <svg class="h-4 w-4 shrink-0 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="flex-1 text-left truncate">{{ item }}</span>
                                        <span
                                            @click.stop="removeFromHistory(item)"
                                            class="flex h-5 w-5 items-center justify-center rounded-full text-gray-300 hover:bg-gray-200 dark:text-gray-600 dark:hover:bg-gray-600 transition-colors"
                                        >×</span>
                                    </button>
                                </div>
                            </div>
                        </Transition>

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
                                    <img
                                        :src="tienda.imagen_portada ? `/storage/${tienda.imagen_portada}` : tienda.logo ? `/storage/${tienda.logo}` : '/images/logo.png'"
                                        :alt="tienda.nombre"
                                        loading="lazy"
                                        class="h-14 w-14 sm:h-16 sm:w-16 flex-shrink-0 rounded-full object-cover"
                                    />
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ tienda.nombre }}</h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1 mt-0.5 truncate">
                                            <svg class="h-3 w-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <span class="truncate">{{ tienda.direccion }}</span>
                                        </p>
                                        <div class="flex items-center gap-2 mt-1">
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
                                    <svg class="h-5 w-5 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </Link>
                            </div>
                        </Transition>
                    </div>
                </div>

                <!-- Acciones derecha (desktop) -->
                <div class="hidden md:flex shrink-0 items-center gap-2 lg:gap-3">
                    <CarritoCompra />
                    <LanguageSwitcher />
                    <DarkModeToggle />

                    <!-- Notificaciones para admin/owner -->
                    <CampanaNotificaciones v-if="user && (user.role === 'admin' || user.role === 'owner')" />

                    <Link
                        v-if="!user"
                        :href="route('login')"
                        class="rounded-lg bg-primary-500 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-primary-600"
                    >
                        Acceder
                    </Link>

                    <Link
                        v-if="user && user.role === 'admin'"
                        :href="route('admin.dashboard')"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-orange-500 to-red-500 px-3 py-2 text-sm font-bold text-white shadow-lg transition-all hover:shadow-xl"
                    ><ShieldCheck class="h-4 w-4" /> Admin</Link>

                    <Link
                        v-if="user && user.role === 'owner'"
                        :href="route('owner.panel')"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-green-500 px-3 py-2 text-sm font-bold text-white shadow-sm transition-colors hover:bg-green-600 max-w-[180px]"
                    ><Store class="h-4 w-4 shrink-0" /> <span class="truncate">{{ $page.props.auth.tienda?.nombre || 'Mi Tienda' }}</span></Link>

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
                                <Link :href="route('monedero.index')" class="flex items-center justify-between gap-3 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 transition-colors hover:bg-gray-50 dark:hover:bg-gray-700" @click="showProfileMenu = false">
                                    <span class="flex items-center gap-3">
                                        <span class="text-base">🪙</span>
                                        Mi monedero
                                    </span>
                                    <span class="rounded-full bg-orange-100 dark:bg-orange-900/40 px-2 py-0.5 text-xs font-bold text-orange-600 dark:text-orange-300">
                                        {{ Number(user.rusticoin_balance ?? 0).toFixed(2) }} RC
                                    </span>
                                </Link>
                                <Link :href="route('carrito')" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 transition-colors hover:bg-gray-50 dark:hover:bg-gray-700" @click="showProfileMenu = false">
                                    <svg class="h-4 w-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    Ver carrito
                                </Link>
                                <Link :href="route('pedidos.index')" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 transition-colors hover:bg-gray-50 dark:hover:bg-gray-700" @click="showProfileMenu = false">
                                    <svg class="h-4 w-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    Mis pedidos
                                </Link>
                                <Link :href="route('profile.edit')" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 transition-colors hover:bg-gray-50 dark:hover:bg-gray-700" @click="showProfileMenu = false">
                                    <svg class="h-4 w-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    Mi perfil
                                </Link>
                                <Link :href="route('info.contacto')" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 transition-colors hover:bg-gray-50 dark:hover:bg-gray-700" @click="showProfileMenu = false">
                                    <svg class="h-4 w-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    Contáctanos
                                </Link>
                                <Link v-if="user.role === 'admin'" :href="route('admin.dashboard')" class="flex items-center gap-3 px-4 py-2.5 text-sm font-semibold text-orange-600 transition-colors hover:bg-orange-50 dark:hover:bg-orange-900/20" @click="showProfileMenu = false">
                                    <ShieldCheck class="h-4 w-4" />
                                    Panel de administración
                                </Link>
                                <Link v-if="user.role === 'owner'" :href="route('owner.panel')" class="flex items-center gap-3 px-4 py-2.5 text-sm font-semibold text-green-600 transition-colors hover:bg-green-50 dark:hover:bg-green-900/20" @click="showProfileMenu = false">
                                    <Store class="h-4 w-4" />
                                    Mi tienda
                                </Link>
                                <div class="border-t border-gray-100 dark:border-gray-700">
                                    <Link :href="route('logout')" method="post" as="button" class="flex w-full items-center gap-3 px-4 py-2.5 text-sm text-red-600 transition-colors hover:bg-red-50 dark:hover:bg-red-900/20">
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

                <!-- Acciones móvil: solo carrito + hamburguesa -->
                <div class="flex md:hidden items-center gap-1">
                    <CarritoCompra />
                    <button
                        @click="showMobileMenu = true"
                        aria-label="Abrir menú"
                        class="flex h-10 w-10 items-center justify-center rounded-lg text-gray-700 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                    >
                        <Menu class="h-6 w-6" />
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile menu drawer -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="showMobileMenu"
                @click="showMobileMenu = false"
                class="fixed inset-0 z-[60] bg-black/50 backdrop-blur-sm md:hidden"
            />
        </Transition>
        <Transition
            enter-active-class="transition-transform duration-300 ease-out"
            enter-from-class="translate-x-full"
            enter-to-class="translate-x-0"
            leave-active-class="transition-transform duration-200 ease-in"
            leave-from-class="translate-x-0"
            leave-to-class="translate-x-full"
        >
            <aside
                v-if="showMobileMenu"
                class="fixed inset-y-0 right-0 z-[61] flex w-[88%] max-w-sm flex-col bg-white dark:bg-gray-900 shadow-2xl md:hidden"
            >
                <!-- Header del drawer -->
                <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 px-4 py-3">
                    <img src="/images/logo.png" alt="Rustikan" class="h-9 w-auto brightness-0 dark:invert" />
                    <button
                        @click="showMobileMenu = false"
                        aria-label="Cerrar menú"
                        class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-700 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-800"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <!-- Buscador móvil -->
                <div class="border-b border-gray-100 dark:border-gray-800 px-4 py-3">
                    <form @submit.prevent="buscarEnHome" class="relative">
                        <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                        <input
                            v-model="busqueda"
                            type="text"
                            placeholder="Buscar tiendas..."
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 py-2.5 pl-9 pr-3 text-sm text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500"
                        />
                    </form>
                </div>

                <!-- Contenido scrollable -->
                <div class="flex-1 overflow-y-auto py-2">
                    <!-- Usuario autenticado -->
                    <div v-if="user" class="border-b border-gray-100 dark:border-gray-800 px-4 py-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full bg-primary-500 text-white">
                                <img v-if="user.avatar" :src="`/storage/${user.avatar}`" class="h-full w-full object-cover" alt="Avatar" />
                                <span v-else class="text-base font-semibold">{{ user.name?.charAt(0)?.toUpperCase() }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ user.name }}</p>
                                <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ user.email }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de rol destacados -->
                    <div v-if="user && (user.role === 'admin' || user.role === 'owner')" class="px-4 py-3">
                        <Link
                            v-if="user.role === 'admin'"
                            :href="route('admin.dashboard')"
                            class="flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-orange-500 to-red-500 px-4 py-2.5 text-sm font-bold text-white shadow"
                        ><ShieldCheck class="h-4 w-4" /> Panel de administración</Link>
                        <Link
                            v-if="user.role === 'owner'"
                            :href="route('owner.panel')"
                            class="flex items-center justify-center gap-2 rounded-lg bg-green-500 px-4 py-2.5 text-sm font-bold text-white shadow"
                        ><Store class="h-4 w-4" /> {{ $page.props.auth.tienda?.nombre || 'Mi Tienda' }}</Link>
                    </div>

                    <!-- Login para invitados -->
                    <div v-if="!user" class="px-4 py-3">
                        <Link
                            :href="route('login')"
                            class="flex items-center justify-center rounded-lg bg-primary-500 px-4 py-2.5 text-sm font-semibold text-white shadow transition hover:bg-primary-600"
                        >Acceder</Link>
                    </div>

                    <!-- Links principales -->
                    <nav class="px-2 py-2">
                        <Link href="/" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            Inicio
                        </Link>
                        <Link v-if="user" :href="route('carrito')" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            Mi carrito
                        </Link>
                        <Link v-if="user" :href="route('pedidos.index')" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            Mis pedidos
                        </Link>
                        <Link v-if="user" :href="route('profile.edit')" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Mi perfil
                        </Link>
                        <Link :href="route('info.contacto')" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            Contacto
                        </Link>
                        <Link :href="route('info.faq')" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093M12 17h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Preguntas frecuentes
                        </Link>
                    </nav>

                    <!-- Preferencias -->
                    <div class="border-t border-gray-100 dark:border-gray-800 px-4 py-4 space-y-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Preferencias</p>
                        <button
                            @click="toggleDark"
                            class="flex w-full items-center justify-between rounded-lg bg-gray-50 dark:bg-gray-800 px-3 py-2.5 text-sm text-gray-700 dark:text-gray-200"
                        >
                            <span class="flex items-center gap-3">
                                <div class="relative h-5 w-5">
                                    <svg class="absolute inset-0 h-5 w-5 text-yellow-500 transition-all duration-300"
                                         :class="isDark ? 'opacity-0 scale-50 rotate-90' : 'opacity-100 scale-100 rotate-0'"
                                         fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2.25a.75.75 0 01.75.75v2.25a.75.75 0 01-1.5 0V3a.75.75 0 01.75-.75zM7.5 12a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM18.894 6.166a.75.75 0 00-1.06-1.06l-1.591 1.59a.75.75 0 101.06 1.061l1.591-1.59zM21.75 12a.75.75 0 01-.75.75h-2.25a.75.75 0 010-1.5H21a.75.75 0 01.75.75zM17.834 18.894a.75.75 0 001.06-1.06l-1.59-1.591a.75.75 0 10-1.061 1.06l1.59 1.591zM12 18a.75.75 0 01.75.75V21a.75.75 0 01-1.5 0v-2.25A.75.75 0 0112 18zM7.166 17.834a.75.75 0 00-1.06 1.06l1.59 1.591a.75.75 0 101.061-1.06l-1.591-1.591zM6 12a.75.75 0 01-.75.75H3a.75.75 0 010-1.5h2.25A.75.75 0 016 12zM6.166 6.166a.75.75 0 001.061 1.06l1.59-1.59a.75.75 0 10-1.06-1.061l-1.591 1.59z" />
                                    </svg>
                                    <svg class="absolute inset-0 h-5 w-5 text-blue-400 transition-all duration-300"
                                         :class="isDark ? 'opacity-100 scale-100 rotate-0' : 'opacity-0 scale-50 -rotate-90'"
                                         fill="currentColor" viewBox="0 0 24 24">
                                        <path fill-rule="evenodd" d="M9.528 1.718a.75.75 0 01.162.819A8.97 8.97 0 009 6a9 9 0 009 9 8.97 8.97 0 003.463-.69.75.75 0 01.981.98 10.503 10.503 0 01-9.694 6.46c-5.799 0-10.5-4.701-10.5-10.5 0-4.368 2.667-8.112 6.46-9.694a.75.75 0 01.818.162z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                {{ isDark ? 'Modo oscuro' : 'Modo claro' }}
                            </span>
                            <div
                                class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors duration-200"
                                :class="isDark ? 'bg-primary-500' : 'bg-gray-300'"
                            >
                                <span
                                    class="inline-block h-5 w-5 transform rounded-full bg-white shadow-md transition-transform duration-200"
                                    :class="isDark ? 'translate-x-[22px]' : 'translate-x-0.5'"
                                />
                            </div>
                        </button>

                        <div class="flex items-center justify-between rounded-lg bg-gray-50 dark:bg-gray-800 px-3 py-2.5">
                            <span class="text-sm text-gray-700 dark:text-gray-200">Idioma</span>
                            <LanguageSwitcher />
                        </div>
                    </div>

                    <!-- Logout -->
                    <div v-if="user" class="border-t border-gray-100 dark:border-gray-800 px-2 py-3">
                        <Link
                            :href="route('logout')"
                            method="post"
                            as="button"
                            class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Cerrar sesión
                        </Link>
                    </div>
                </div>
            </aside>
        </Transition>
    </Teleport>
</template>

<style>
.dark .nav-logo {
    filter: brightness(0) invert(1);
}
</style>
