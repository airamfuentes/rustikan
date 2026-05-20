<template>
    <div :class="['min-h-screen', isDark ? 'bg-gray-950 text-white' : 'bg-gray-50 text-gray-900']">
        <Head title="Mi Monedero RustiCoin" />
        <NavbarPublico />

        <main class="mx-auto max-w-5xl px-4 pt-28 pb-12 sm:px-6 lg:px-8">

            <!-- Header -->
            <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight flex items-center gap-2">
                        <Coins class="h-8 w-8 text-primary-500" /> Mi Monedero
                    </h1>
                    <p :class="['mt-1 text-sm', isDark ? 'text-gray-400' : 'text-gray-500']">
                        Usa RustiCoins para pagar tus pedidos de forma rápida.
                    </p>
                </div>
                <!-- Saldo destacado -->
                <div class="flex items-center gap-4 rounded-2xl bg-gradient-to-br from-primary-500 to-orange-600 px-6 py-4 text-white shadow-lg">
                    <Coins class="h-8 w-8" />
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider opacity-80">Saldo disponible</p>
                        <p class="text-3xl font-extrabold">{{ Number(saldo).toFixed(2) }} RC</p>
                        <p class="text-xs opacity-70">1 RC = 1 €</p>
                    </div>
                </div>
            </div>

            <!-- Recargar (ancho completo) -->
            <div :class="['rounded-2xl border p-6 shadow-sm mb-8', isDark ? 'bg-gray-800 border-gray-700' : 'bg-white border-gray-200']">
                <h2 class="mb-1 text-lg font-bold">Añadir fondos</h2>
                <p :class="['mb-5 text-sm', isDark ? 'text-gray-400' : 'text-gray-500']">Recarga tu monedero de forma segura con Stripe.</p>

                <form @submit.prevent="recargar" class="space-y-4">
                    <div>
                        <label :class="['mb-1 block text-sm font-medium', isDark ? 'text-gray-300' : 'text-gray-700']">Cantidad (€ / RC)</label>
                        <div class="flex gap-2 flex-wrap mb-3">
                            <button
                                v-for="amount in [5, 10, 20, 50]"
                                :key="amount"
                                type="button"
                                @click="recargaForm.cantidad = amount"
                                :class="['rounded-full px-4 py-1.5 text-sm font-semibold transition-colors border',
                                    recargaForm.cantidad == amount
                                        ? 'bg-primary-500 text-white border-primary-500'
                                        : isDark ? 'bg-gray-700 border-gray-600 text-gray-300 hover:bg-gray-600' : 'bg-gray-100 border-gray-200 text-gray-600 hover:bg-gray-200']"
                            >{{ amount }}€</button>
                        </div>
                        <input
                            v-model.number="recargaForm.cantidad"
                            type="number" min="1" max="500" step="1"
                            inputmode="numeric"
                            v-only-digits
                            placeholder="Otra cantidad..."
                            :class="['w-full rounded-xl border px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-400',
                                isDark ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-500' : 'bg-white border-gray-300 text-gray-900 placeholder-gray-400']"
                            required
                        />
                        <p v-if="recargaForm.errors.cantidad" class="mt-1 text-xs text-red-500">{{ recargaForm.errors.cantidad }}</p>
                        <p v-if="recargaForm.errors.stripe" class="mt-1 text-xs text-red-500">{{ recargaForm.errors.stripe }}</p>
                    </div>

                    <!-- Info Stripe -->
                    <div :class="['rounded-xl border p-3 flex items-start gap-3', isDark ? 'border-blue-800 bg-blue-900/20' : 'border-blue-100 bg-blue-50']">
                        <ShieldCheck class="h-5 w-5 shrink-0 mt-0.5" :class="isDark ? 'text-blue-400' : 'text-blue-600'" />
                        <div>
                            <p :class="['text-xs font-semibold', isDark ? 'text-blue-300' : 'text-blue-700']">Pago seguro con Stripe</p>
                            <p :class="['text-xs mt-0.5', isDark ? 'text-blue-400' : 'text-blue-600']">
                                Al hacer clic en "Añadir fondos" serás redirigido a la pasarela de pago de Stripe. Tu saldo se actualizará automáticamente tras completar el pago.
                            </p>
                        </div>
                    </div>

                    <button type="submit"
                        :disabled="recargaForm.processing || !recargaForm.cantidad || recargaForm.cantidad < 1"
                        class="w-full rounded-xl bg-primary-500 px-6 py-3 font-bold text-white shadow transition-all hover:bg-primary-600 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="recargaForm.processing">Redirigiendo a Stripe...</span>
                        <span v-else>Añadir {{ recargaForm.cantidad ? Number(recargaForm.cantidad).toFixed(2) : '0.00' }} RC</span>
                    </button>
                </form>
            </div>

            <!-- Historial -->
            <div :class="['rounded-2xl border p-6 shadow-sm', isDark ? 'bg-gray-800 border-gray-700' : 'bg-white border-gray-200']">
                <h2 class="mb-1 text-lg font-bold">Historial de movimientos</h2>
                <p :class="['mb-5 text-sm', isDark ? 'text-gray-400' : 'text-gray-500']">Últimas 50 transacciones.</p>

                <div v-if="transacciones.length === 0" class="flex flex-col items-center py-10 text-center">
                    <Coins class="h-16 w-16 text-gray-300 dark:text-gray-600 mb-3" />
                    <p :class="['text-sm', isDark ? 'text-gray-400' : 'text-gray-500']">Aún no tienes movimientos.</p>
                </div>

                <div v-else class="space-y-2 max-h-96 overflow-y-auto pr-1">
                    <div
                        v-for="tx in transacciones"
                        :key="tx.id"
                        :class="['flex items-center gap-3 rounded-xl p-3 transition-colors',
                            isDark ? 'hover:bg-gray-700/60' : 'hover:bg-gray-50']"
                    >
                        <div :class="['flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full',
                            tx.tipo === 'recarga'   ? 'bg-green-100 dark:bg-green-900/40' :
                            tx.tipo === 'compra'    ? 'bg-red-100 dark:bg-red-900/40' :
                            tx.tipo === 'retiro'    ? 'bg-orange-100 dark:bg-orange-900/40' :
                            tx.tipo === 'reembolso' ? 'bg-blue-100 dark:bg-blue-900/40' :
                                                      'bg-gray-100 dark:bg-gray-700']">
                            <Plus         v-if="tx.tipo === 'recarga'"   class="h-4 w-4 text-green-600 dark:text-green-400" />
                            <ShoppingCart v-else-if="tx.tipo === 'compra'"    class="h-4 w-4 text-red-600 dark:text-red-400" />
                            <ArrowUpRight v-else-if="tx.tipo === 'retiro'"    class="h-4 w-4 text-orange-600 dark:text-orange-400" />
                            <RotateCcw    v-else-if="tx.tipo === 'reembolso'" class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                            <Wallet       v-else                               class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p :class="['text-sm font-medium truncate', isDark ? 'text-gray-200' : 'text-gray-800']">{{ tx.descripcion }}</p>
                            <p :class="['text-xs', isDark ? 'text-gray-500' : 'text-gray-400']">
                                {{ new Date(tx.created_at).toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' }) }}
                            </p>
                        </div>
                        <div :class="['text-sm font-bold',
                            tx.cantidad > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400']">
                            {{ tx.cantidad > 0 ? '+' : '' }}{{ Number(tx.cantidad).toFixed(2) }} RC
                        </div>
                    </div>
                </div>
            </div>

        </main>

        <FooterPublico />
    </div>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import NavbarPublico from '@/Components/NavbarPublico.vue';
import FooterPublico from '@/Components/FooterPublico.vue';
import { useDarkMode } from '@/Composables/useDarkMode.js';
import { Coins, Plus, ShoppingCart, ArrowUpRight, RotateCcw, Wallet, ShieldCheck } from 'lucide-vue-next';

const props = defineProps({
    saldo:         { type: Number, default: 0 },
    transacciones: { type: Array, default: () => [] },
});

const { isDark } = useDarkMode();

const recargaForm = useForm({ cantidad: 10 });

const recargar = () => {
    recargaForm.post(route('monedero.recargar'));
};
</script>
