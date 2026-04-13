<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import Toast from '@/Components/Toast.vue';
import { Link, usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const scrolled = ref(false);
const toasts = ref([]);
const showProfileMenu = ref(false);
const profileMenuRef = ref(null);

// Detectar scroll
const handleScroll = () => {
    scrolled.value = window.scrollY > 20;
};

const handleClickOutside = (e) => {
    if (profileMenuRef.value && !profileMenuRef.value.contains(e.target)) {
        showProfileMenu.value = false;
    }
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
    document.removeEventListener('click', handleClickOutside);
});

// Funciones para manejar toasts
const showToast = (type, title, message) => {
    const id = Date.now();
    toasts.value.push({ id, type, title, message });
};

const removeToast = (id) => {
    toasts.value = toasts.value.filter((toast) => toast.id !== id);
};

// Escuchar mensajes flash de sesión
const page = usePage();
watch(
    () => page.props.flash,
    (flash) => {
        if (flash && Object.keys(flash).length > 0) {
            // Determinar el tipo de mensaje
            if (flash.success) {
                showToast('success', 'Éxito', flash.success);
            } else if (flash.error) {
                showToast('error', 'Error', flash.error);
            } else if (flash.info) {
                showToast('info', 'Información', flash.info);
            } else if (flash.warning) {
                showToast('warning', 'Advertencia', flash.warning);
            }
        }
    },
    { deep: true, immediate: true }
);
</script>

<template>
    <div>
        <!-- Toast Notifications -->
        <div class="pointer-events-none fixed inset-0 z-50 flex flex-col items-end justify-start space-y-4 p-6 sm:p-6">
            <Toast
                v-for="toast in toasts"
                :key="toast.id"
                :type="toast.type"
                :title="toast.title"
                :message="toast.message"
                @close="removeToast(toast.id)"
            />
        </div>

        <div class="min-h-screen bg-gray-50">
            <nav 
                :class="[
                    'sticky top-0 z-50 bg-white transition-all duration-300',
                    scrolled 
                        ? 'border-b border-gray-200 shadow-md' 
                        : 'mx-12 mt-4 rounded-2xl border border-gray-200 shadow-sm'
                ]"
            >
                <!-- Primary Navigation Menu -->
                <div :class="[
                    'mx-auto px-4 sm:px-6 lg:px-8 transition-all duration-300',
                    scrolled ? 'max-w-full' : 'max-w-7xl'
                ]">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('dashboard')" class="flex items-center">
                                    <img src="/images/logo.png" alt="Rustikan" class="h-10 w-auto" />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <NavLink :href="route('home')" :active="route().current('home')">
                                    Tiendas
                                </NavLink>
                                <!-- Admin Panel Link -->
                                <NavLink 
                                    v-if="$page.props.auth.user.role === 'admin'" 
                                    :href="route('admin.dashboard')" 
                                    :active="route().current('admin.*')"
                                    class="!text-orange-600 font-bold"
                                >
                                    🛡️ Panel Admin
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center">
                            <!-- Badge Admin -->
                            <div v-if="$page.props.auth.user.role === 'admin'" class="mr-4 rounded-full bg-gradient-to-r from-orange-500 to-red-500 px-3 py-1 text-xs font-bold text-white shadow-lg">
                                ADMIN
                            </div>
                            
                            <!-- Carrito -->
                            <button class="relative mr-4">
                                <svg class="h-6 w-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                                <span class="absolute -right-2 -top-2 flex h-5 w-5 items-center justify-center rounded-full bg-primary-500 text-xs font-bold text-white">
                                    0
                                </span>
                            </button>

                            <!-- Avatar + Dropdown -->
                            <div ref="profileMenuRef" class="relative ms-3">
                                <button
                                    @click="showProfileMenu = !showProfileMenu"
                                    class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-full bg-primary-500 text-white transition-colors hover:bg-primary-600 focus:outline-none"
                                >
                                    <img v-if="$page.props.auth.user.avatar" :src="`/storage/${$page.props.auth.user.avatar}`" class="h-full w-full object-cover" alt="Avatar" />
                                    <span v-else class="text-sm font-semibold">{{ $page.props.auth.user.name?.charAt(0)?.toUpperCase() }}</span>
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
                                        class="absolute right-0 top-12 z-50 w-52 overflow-hidden rounded-xl border border-gray-100 bg-white shadow-lg"
                                    >
                                        <div class="border-b border-gray-100 px-4 py-3">
                                            <p class="truncate text-sm font-medium text-gray-900">{{ $page.props.auth.user.name }}</p>
                                            <p class="truncate text-xs text-gray-500">{{ $page.props.auth.user.email }}</p>
                                            <p v-if="$page.props.auth.user.telefono" class="mt-0.5 truncate text-xs text-gray-400">{{ $page.props.auth.user.telefono }}</p>
                                        </div>
                                        <Link
                                            :href="route('admin.dashboard')"
                                            class="flex items-center gap-3 px-4 py-2.5 text-sm font-semibold text-orange-600 transition-colors hover:bg-orange-50"
                                            @click="showProfileMenu = false"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                            </svg>
                                            Panel de administración
                                        </Link>
                                        <Link
                                            :href="route('profile.edit')"
                                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 transition-colors hover:bg-gray-50"
                                            @click="showProfileMenu = false"
                                        >
                                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            Mi perfil
                                        </Link>
                                        <Link
                                            :href="route('home')"
                                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 transition-colors hover:bg-gray-50"
                                            @click="showProfileMenu = false"
                                        >
                                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                            Contáctanos
                                        </Link>
                                        <div class="border-t border-gray-100">
                                            <Link
                                                :href="route('logout')"
                                                method="post"
                                                as="button"
                                                class="flex w-full items-center gap-3 px-4 py-2.5 text-sm text-red-600 transition-colors hover:bg-red-50"
                                                @click="showProfileMenu = false"
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

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center rounded-md p-2 text-gray-600 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-800 focus:bg-gray-100 focus:text-gray-800 focus:outline-none"
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex': !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex': showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                >
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink :href="route('home')" :active="route().current('home')">
                            Tiendas
                        </ResponsiveNavLink>
                        <ResponsiveNavLink 
                            v-if="$page.props.auth.user.role === 'admin'" 
                            :href="route('admin.dashboard')" 
                            :active="route().current('admin.*')"
                            class="!text-orange-600 !font-bold"
                        >
                            🛡️ Panel Admin
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="border-t border-gray-200 pb-1 pt-4">
                        <div class="px-4">
                            <div class="flex items-center gap-2">
                                <div class="text-base font-medium text-gray-800">
                                    {{ $page.props.auth.user.name }}
                                </div>
                                <span v-if="$page.props.auth.user.role === 'admin'" class="rounded-full bg-gradient-to-r from-orange-500 to-red-500 px-2 py-0.5 text-xs font-bold text-white">
                                    ADMIN
                                </span>
                            </div>
                            <div class="text-sm font-medium text-gray-600">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Perfil
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Cerrar Sesión
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header class="bg-white shadow" v-if="$slots.header">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
