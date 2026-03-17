<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import TiendaCard from '@/Components/TiendaCard.vue';
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

const props = defineProps({
    categoria: {
        type: Object,
        required: true,
    },
    tiendas: {
        type: Array,
        default: () => [],
    },
});

const scrolled   = ref(false);
const busqueda   = ref('');
const searchRef  = ref(null);
const showDropdown = ref(false);
const sugerencias  = ref([]);
let scrollTimeout  = null;
let searchTimeout  = null;

const tiendasFiltradas = computed(() =>
    busqueda.value.trim()
        ? props.tiendas.filter(t =>
              t.nombre.toLowerCase().includes(busqueda.value.toLowerCase()) ||
              t.direccion?.toLowerCase().includes(busqueda.value.toLowerCase())
          )
        : props.tiendas
);

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
};

const buscarTiendas = () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        if (busqueda.value.trim() === '') {
            showDropdown.value = false;
            sugerencias.value = [];
        } else {
            sugerencias.value = props.tiendas.filter(t =>
                t.nombre.toLowerCase().includes(busqueda.value.toLowerCase())
            );
            showDropdown.value = true;
        }
    }, 300);
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
    document.removeEventListener('click', handleClickOutside);
    if (searchTimeout) clearTimeout(searchTimeout);
    if (scrollTimeout) clearTimeout(scrollTimeout);
});
</script>

<template>
    <Head :title="`${categoria.nombre} – Rustikan`" />

    <div class="min-h-screen bg-gray-50">
        <!-- Navbar -->
        <nav
            :class="[
                'sticky top-0 z-50 bg-white transition-all duration-300',
                scrolled
                    ? 'border-b border-gray-200 shadow-md'
                    : 'mx-12 mt-4 rounded-2xl border border-gray-200 shadow-sm',
            ]"
        >
            <div :class="[
                'mx-auto px-4 sm:px-6 lg:px-8 transition-all duration-300',
                scrolled ? 'max-w-full' : 'max-w-7xl',
            ]">
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
                                :placeholder="`Buscar en ${categoria.nombre}...`"
                                class="w-full rounded-lg border border-gray-300 bg-white py-2 pl-12 pr-4 text-sm text-gray-900 placeholder-gray-500 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500"
                            />

                            <Transition
                                enter-active-class="transition ease-out duration-200"
                                enter-from-class="opacity-0 translate-y-1"
                                enter-to-class="opacity-100 translate-y-0"
                                leave-active-class="transition ease-in duration-150"
                                leave-from-class="opacity-100 translate-y-0"
                                leave-to-class="opacity-0 translate-y-1"
                            >
                                <div
                                    v-if="showDropdown && sugerencias.length > 0"
                                    class="absolute left-0 right-0 top-full mt-2 max-h-72 overflow-y-auto rounded-xl border border-gray-200 bg-white shadow-xl"
                                >
                                    <Link
                                        v-for="tienda in sugerencias"
                                        :key="tienda.id"
                                        :href="route('tienda.detalle', tienda.slug)"
                                        class="flex items-center gap-3 border-b border-gray-100 p-3 transition-colors hover:bg-gray-50 last:border-b-0"
                                    >
                                        <img
                                            :src="tienda.imagen_portada || tienda.logo || '/images/logo.png'"
                                            :alt="tienda.nombre"
                                            loading="lazy"
                                            class="h-10 w-10 flex-shrink-0 rounded-lg object-cover"
                                        />
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-gray-900 truncate">{{ tienda.nombre }}</p>
                                            <p class="text-xs text-gray-500 truncate">{{ tienda.direccion }}</p>
                                        </div>
                                        <svg class="h-4 w-4 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </Link>
                                </div>
                            </Transition>
                        </div>
                    </div>

                    <!-- Acciones derecha -->
                    <div class="flex shrink-0 items-center gap-4">
                        <LanguageSwitcher />

                        <Link
                            v-if="!user"
                            :href="route('login')"
                            class="rounded-lg bg-primary-500 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-primary-600"
                        >
                            Acceder
                        </Link>

                        <Link
                            v-else
                            :href="route('dashboard')"
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-500 text-white hover:bg-primary-600 transition-colors"
                        >
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                            </svg>
                        </Link>

                        <Link
                            v-if="user && user.role === 'admin'"
                            :href="route('admin.dashboard')"
                            class="rounded-lg bg-gradient-to-r from-orange-500 to-red-500 px-4 py-2 text-sm font-bold text-white shadow-lg transition-all hover:shadow-xl hover:from-orange-600 hover:to-red-600"
                        >
                            🛡️ Admin
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Cabecera de categoría -->
        <div class="relative overflow-hidden bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 py-16">
            <!-- Patrón decorativo de fondo -->
            <div class="absolute inset-0 opacity-5">
                <div class="absolute -left-16 -top-16 h-64 w-64 rounded-full bg-primary-400 blur-3xl"></div>
                <div class="absolute -right-16 -bottom-16 h-64 w-64 rounded-full bg-primary-600 blur-3xl"></div>
            </div>

            <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Miga de pan -->
                <nav class="mb-4 flex items-center gap-2 text-sm text-gray-400">
                    <Link href="/" class="hover:text-primary-400 transition-colors">Inicio</Link>
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="text-gray-200">{{ categoria.nombre }}</span>
                </nav>

                <div class="flex items-center gap-6">
                    <!-- Icono grande -->
                    <div class="flex h-20 w-20 flex-shrink-0 items-center justify-center rounded-2xl bg-white/10 text-5xl shadow-lg backdrop-blur-sm">
                        {{ categoria.icono }}
                    </div>
                    <!-- Título -->
                    <div>
                        <h1 class="text-3xl font-extrabold text-white sm:text-4xl">
                            {{ categoria.nombre }}
                        </h1>
                        <p v-if="categoria.descripcion" class="mt-1 text-gray-300">
                            {{ categoria.descripcion }}
                        </p>
                        <p class="mt-2 text-sm text-primary-400 font-medium">
                            {{ tiendas.length }} {{ tiendas.length === 1 ? 'establecimiento' : 'establecimientos' }} disponibles
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de tiendas -->
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">

            <!-- Grid de resultados (filtrado por búsqueda si hay texto) -->
            <div v-if="tiendasFiltradas.length > 0" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <TiendaCard
                    v-for="tienda in tiendasFiltradas"
                    :key="tienda.id"
                    :tienda="tienda"
                />
            </div>

            <!-- Sin resultados -->
            <div v-else class="flex flex-col items-center justify-center py-24 text-center">
                <div class="mb-4 text-6xl">{{ categoria.icono }}</div>
                <h2 class="text-xl font-semibold text-gray-700">
                    Aún no hay establecimientos en esta categoría
                </h2>
                <p class="mt-2 text-sm text-gray-400">
                    Pronto encontrarás productores locales de {{ categoria.nombre }} aquí.
                </p>
                <Link
                    href="/"
                    class="mt-6 rounded-lg bg-primary-500 px-6 py-2.5 text-sm font-medium text-white transition-colors hover:bg-primary-600"
                >
                    ← Volver al inicio
                </Link>
            </div>
        </div>

        <!-- Footer simple -->
        <footer class="mt-16 border-t border-gray-200 bg-white py-8 text-center text-sm text-gray-500">
            <p>&copy; {{ new Date().getFullYear() }} Rustikan. Todos los derechos reservados.</p>
        </footer>
    </div>
</template>
