<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { useDarkMode } from '@/Composables/useDarkMode';
import ChatConAdmin from '@/Components/ChatConAdmin.vue';
import ScrollToTop from '@/Components/ScrollToTop.vue';
import {
    LayoutDashboard, PackageSearch, ClipboardList,
    Package, LogOut, Menu, X, ChevronRight,
    Warehouse, Moon, Sun
} from 'lucide-vue-next';

const page      = usePage();
const { isDark, toggleDark } = useDarkMode();
const sidebarOpen = ref(false);

const user = computed(() => page.props.auth?.user);

const navItems = computed(() => [
    {
        label: 'Dashboard',
        href: route('supplier.dashboard'),
        icon: LayoutDashboard,
        active: route().current('supplier.dashboard'),
        badge: null,
    },
    {
        label: 'Pedidos entrantes',
        href: route('supplier.pedidos.index'),
        icon: PackageSearch,
        active: route().current('supplier.pedidos.*'),
        badge: null,
    },
    {
        label: 'Historial',
        href: route('supplier.historial'),
        icon: ClipboardList,
        active: route().current('supplier.historial'),
        badge: null,
    },
    {
        label: 'Inventario / Stock',
        href: route('supplier.stock'),
        icon: Package,
        active: route().current('supplier.stock'),
        badge: null,
    },
]);

// Cerrar sidebar en móvil al navegar
router.on('navigate', () => { sidebarOpen.value = false; });
</script>

<template>
    <div class="flex h-screen overflow-hidden bg-gray-50 dark:bg-gray-900">

        <!-- ToastContainer está montado globalmente en app.js -->

        <!-- Overlay móvil -->
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="sidebarOpen"
                @click="sidebarOpen = false"
                class="fixed inset-0 z-20 bg-black/50 lg:hidden"
            />
        </Transition>

        <!-- Sidebar -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-30 flex w-64 flex-col bg-gray-900 transition-transform duration-300 lg:translate-x-0 lg:static lg:inset-auto',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full'
            ]"
        >
            <!-- Logo -->
            <div class="flex h-16 shrink-0 items-center gap-3 border-b border-gray-700/60 px-5">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary-500">
                    <Warehouse class="h-4 w-4 text-white" />
                </div>
                <div>
                    <p class="text-sm font-bold text-white">Rustikan</p>
                    <p class="text-[10px] font-medium uppercase tracking-widest text-primary-400">Almacén</p>
                </div>
            </div>

            <!-- Navegación -->
            <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
                <Link
                    v-for="item in navItems"
                    :key="item.label"
                    :href="item.href"
                    :class="[
                        'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-150',
                        item.active
                            ? 'bg-primary-500/20 text-primary-400'
                            : 'text-gray-400 hover:bg-gray-800 hover:text-white'
                    ]"
                >
                    <component
                        :is="item.icon"
                        class="h-5 w-5 shrink-0 transition-colors"
                        :class="item.active ? 'text-primary-400' : 'text-gray-500 group-hover:text-white'"
                    />
                    <span class="flex-1">{{ item.label }}</span>
                    <span
                        v-if="item.badge"
                        class="flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white"
                    >{{ item.badge }}</span>
                    <ChevronRight
                        v-if="item.active"
                        class="h-4 w-4 text-primary-400/60"
                    />
                </Link>

                <div class="my-3 border-t border-gray-700/60" />

                <!-- Dark mode toggle -->
                <button
                    @click="toggleDark"
                    class="group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-gray-400 transition-all hover:bg-gray-800 hover:text-white"
                >
                    <Sun v-if="isDark" class="h-5 w-5 shrink-0 text-gray-500 group-hover:text-white" />
                    <Moon v-else class="h-5 w-5 shrink-0 text-gray-500 group-hover:text-white" />
                    <span>{{ isDark ? 'Modo claro' : 'Modo oscuro' }}</span>
                </button>
            </nav>

            <!-- Usuario + Logout -->
            <div class="shrink-0 border-t border-gray-700/60 p-3">
                <div class="flex items-center gap-3 rounded-xl px-3 py-2 mb-1">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-full bg-primary-500 text-white">
                        <img
                            v-if="user?.avatar"
                            :src="`/storage/${user.avatar}`"
                            class="h-full w-full object-cover"
                            alt="Avatar"
                        />
                        <span v-else class="text-xs font-bold">{{ user?.name?.charAt(0)?.toUpperCase() }}</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-white">{{ user?.name }}</p>
                        <p class="truncate text-[10px] text-gray-500">Almacén</p>
                    </div>
                </div>
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium text-gray-500 transition-all hover:bg-red-500/10 hover:text-red-400"
                >
                    <LogOut class="h-4 w-4" />
                    Cerrar sesión
                </Link>
            </div>
        </aside>

        <!-- Main content -->
        <div class="flex flex-1 flex-col min-w-0 overflow-hidden">
            <!-- Top bar móvil -->
            <div class="flex h-14 shrink-0 items-center justify-between border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 lg:hidden">
                <button
                    @click="sidebarOpen = true"
                    class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800"
                >
                    <Menu class="h-5 w-5" />
                </button>
                <div class="flex items-center gap-2">
                    <Warehouse class="h-5 w-5 text-primary-500" />
                    <span class="text-sm font-bold text-gray-900 dark:text-white">Almacén</span>
                </div>
                <div class="w-9" />
            </div>

            <!-- Contenido -->
            <main class="flex-1 overflow-y-auto">
                <slot />
            </main>
        </div>

        <!-- Chat con Admin (flotante) -->
        <ChatConAdmin />
        <ScrollToTop />
    </div>
</template>
