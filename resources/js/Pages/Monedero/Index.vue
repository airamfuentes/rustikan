<template>
    <div :class="['min-h-screen', isDark ? 'bg-gray-950 text-white' : 'bg-gray-50 text-gray-900']">
        <Head title="Mi Monedero RustiCoin" />
        <NavbarPublico />

        <main class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">

            <!-- Header -->
            <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight flex items-center gap-2">
                        <span class="text-4xl">🪙</span> Mi Monedero
                    </h1>
                    <p :class="['mt-1 text-sm', isDark ? 'text-gray-400' : 'text-gray-500']">
                        Usa RustiCoins para pagar tus pedidos de forma rápida.
                    </p>
                </div>
                <!-- Saldo destacado -->
                <div class="flex items-center gap-4 rounded-2xl bg-gradient-to-br from-primary-500 to-orange-600 px-6 py-4 text-white shadow-lg">
                    <span class="text-3xl">🪙</span>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider opacity-80">Saldo disponible</p>
                        <p class="text-3xl font-extrabold">{{ Number(saldo).toFixed(2) }} RC</p>
                        <p class="text-xs opacity-70">1 RC = 1 €</p>
                    </div>
                </div>
            </div>

            <div class="grid gap-8 lg:grid-cols-2">

                <!-- Recargar -->
                <div :class="['rounded-2xl border p-6 shadow-sm', isDark ? 'bg-gray-800 border-gray-700' : 'bg-white border-gray-200']">
                    <h2 class="mb-1 text-lg font-bold">Añadir fondos</h2>
                    <p :class="['mb-5 text-sm', isDark ? 'text-gray-400' : 'text-gray-500']">Recarga tu monedero con tarjeta (simulado).</p>

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
                                placeholder="Otra cantidad..."
                                :class="['w-full rounded-xl border px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-400',
                                    isDark ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-500' : 'bg-white border-gray-300 text-gray-900 placeholder-gray-400']"
                                required
                            />
                            <p v-if="recargaForm.errors.cantidad" class="mt-1 text-xs text-red-500">{{ recargaForm.errors.cantidad }}</p>
                        </div>

                        <!-- Datos tarjeta (simulación) -->
                        <div :class="['rounded-xl border p-4 space-y-3', isDark ? 'border-gray-600 bg-gray-700/50' : 'border-gray-200 bg-gray-50']">
                            <p :class="['text-xs font-semibold uppercase tracking-wider', isDark ? 'text-gray-400' : 'text-gray-500']">Datos de tarjeta (simulado)</p>
                            <input v-model="cardNumber" type="text" placeholder="**** **** **** ****" maxlength="19" @input="formatCard"
                                :class="['w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-400',
                                    isDark ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-500' : 'bg-white border-gray-300 text-gray-900 placeholder-gray-400']"
                            />
                            <div class="grid grid-cols-2 gap-2">
                                <input v-model="cardExpiry" type="text" placeholder="MM/AA" maxlength="5"
                                    :class="['w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-400',
                                        isDark ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-500' : 'bg-white border-gray-300 text-gray-900 placeholder-gray-400']"
                                />
                                <input v-model="cardCvv" type="text" placeholder="CVV" maxlength="3"
                                    :class="['w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-400',
                                        isDark ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-500' : 'bg-white border-gray-300 text-gray-900 placeholder-gray-400']"
                                />
                            </div>
                        </div>

                        <button type="submit"
                            :disabled="recargaForm.processing || !recargaForm.cantidad || recargaForm.cantidad < 1"
                            class="w-full rounded-xl bg-primary-500 px-6 py-3 font-bold text-white shadow transition-all hover:bg-primary-600 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="recargaForm.processing">Procesando...</span>
                            <span v-else>Añadir {{ recargaForm.cantidad ? Number(recargaForm.cantidad).toFixed(2) : '0.00' }} RC 🪙</span>
                        </button>
                    </form>
                </div>

                <!-- Retirar -->
                <div :class="['rounded-2xl border p-6 shadow-sm', isDark ? 'bg-gray-800 border-gray-700' : 'bg-white border-gray-200']">
                    <h2 class="mb-1 text-lg font-bold">Retirar fondos</h2>
                    <p :class="['mb-5 text-sm', isDark ? 'text-gray-400' : 'text-gray-500']">Transfiere RustiCoins de vuelta a tu tarjeta (simulado).</p>

                    <form @submit.prevent="retirar" class="space-y-4">
                        <div>
                            <label :class="['mb-1 block text-sm font-medium', isDark ? 'text-gray-300' : 'text-gray-700']">Cantidad a retirar (RC)</label>
                            <div class="flex gap-2 flex-wrap mb-3">
                                <button
                                    v-for="amount in [5, 10, 20, 50]"
                                    :key="amount"
                                    type="button"
                                    :disabled="amount > saldo"
                                    @click="retiradaForm.cantidad = amount"
                                    :class="['rounded-full px-4 py-1.5 text-sm font-semibold transition-colors border disabled:opacity-40 disabled:cursor-not-allowed',
                                        retiradaForm.cantidad == amount
                                            ? 'bg-orange-500 text-white border-orange-500'
                                            : isDark ? 'bg-gray-700 border-gray-600 text-gray-300 hover:bg-gray-600' : 'bg-gray-100 border-gray-200 text-gray-600 hover:bg-gray-200']"
                                >{{ amount }} RC</button>
                                <button type="button" :disabled="saldo <= 0" @click="retiradaForm.cantidad = saldo"
                                    :class="['rounded-full px-4 py-1.5 text-sm font-semibold transition-colors border disabled:opacity-40',
                                        isDark ? 'bg-gray-700 border-gray-600 text-gray-300 hover:bg-gray-600' : 'bg-gray-100 border-gray-200 text-gray-600 hover:bg-gray-200']"
                                >Todo</button>
                            </div>
                            <input
                                v-model.number="retiradaForm.cantidad"
                                type="number" :min="1" :max="saldo" step="1"
                                placeholder="Cantidad a retirar..."
                                :class="['w-full rounded-xl border px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400',
                                    isDark ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-500' : 'bg-white border-gray-300 text-gray-900 placeholder-gray-400']"
                                required
                            />
                            <p v-if="retiradaForm.errors.cantidad" class="mt-1 text-xs text-red-500">{{ retiradaForm.errors.cantidad }}</p>
                            <p v-if="retiradaForm.cantidad > saldo" class="mt-1 text-xs text-red-500">
                                No puedes retirar más de tu saldo ({{ Number(saldo).toFixed(2) }} RC)
                            </p>
                        </div>

                        <div :class="['rounded-xl border p-3 flex items-center gap-2', isDark ? 'border-gray-600 bg-gray-700/30' : 'border-orange-100 bg-orange-50']">
                            <span class="text-xl">ℹ️</span>
                            <p :class="['text-xs', isDark ? 'text-gray-400' : 'text-orange-700']">
                                El reembolso se hará a la tarjeta original. Puede tardar 5-10 días hábiles (simulado).
                            </p>
                        </div>

                        <button type="submit"
                            :disabled="retiradaForm.processing || !retiradaForm.cantidad || retiradaForm.cantidad < 1 || retiradaForm.cantidad > saldo"
                            class="w-full rounded-xl bg-orange-500 px-6 py-3 font-bold text-white shadow transition-all hover:bg-orange-600 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="retiradaForm.processing">Procesando...</span>
                            <span v-else>Retirar {{ retiradaForm.cantidad ? Number(retiradaForm.cantidad).toFixed(2) : '0.00' }} RC</span>
                        </button>
                    </form>
                </div>

            </div>

            <!-- Historial -->
            <div :class="['mt-8 rounded-2xl border p-6 shadow-sm', isDark ? 'bg-gray-800 border-gray-700' : 'bg-white border-gray-200']">
                <h2 class="mb-1 text-lg font-bold">Historial de movimientos</h2>
                <p :class="['mb-5 text-sm', isDark ? 'text-gray-400' : 'text-gray-500']">Últimas 50 transacciones.</p>

                <div v-if="transacciones.length === 0" class="flex flex-col items-center py-10 text-center">
                    <span class="text-5xl mb-3">🪙</span>
                    <p :class="['text-sm', isDark ? 'text-gray-400' : 'text-gray-500']">Aún no tienes movimientos.</p>
                </div>

                <div v-else class="space-y-2 max-h-96 overflow-y-auto pr-1">
                    <div
                        v-for="tx in transacciones"
                        :key="tx.id"
                        :class="['flex items-center gap-3 rounded-xl p-3 transition-colors',
                            isDark ? 'hover:bg-gray-700/60' : 'hover:bg-gray-50']"
                    >
                        <div :class="['flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full text-lg',
                            tx.tipo === 'recarga'   ? 'bg-green-100 dark:bg-green-900/40' :
                            tx.tipo === 'compra'    ? 'bg-red-100 dark:bg-red-900/40' :
                            tx.tipo === 'retiro'    ? 'bg-orange-100 dark:bg-orange-900/40' :
                            tx.tipo === 'reembolso' ? 'bg-blue-100 dark:bg-blue-900/40' :
                                                      'bg-gray-100 dark:bg-gray-700']">
                            {{ tx.tipo === 'recarga' ? '➕' : tx.tipo === 'compra' ? '🛒' : tx.tipo === 'retiro' ? '💸' : tx.tipo === 'reembolso' ? '↩️' : '💰' }}
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
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import NavbarPublico from '@/Components/NavbarPublico.vue';
import FooterPublico from '@/Components/FooterPublico.vue';
import { useDarkMode } from '@/Composables/useDarkMode.js';

const props = defineProps({
    saldo:         { type: Number, default: 0 },
    transacciones: { type: Array, default: () => [] },
});

const { isDark } = useDarkMode();

const recargaForm  = useForm({ cantidad: 10 });
const retiradaForm = useForm({ cantidad: null });
const cardNumber   = ref('');
const cardExpiry   = ref('');
const cardCvv      = ref('');

const formatCard = () => {
    cardNumber.value = cardNumber.value
        .replace(/\D/g, '')
        .replace(/(.{4})/g, '$1 ')
        .trim()
        .slice(0, 19);
};

const recargar = () => {
    recargaForm.post(route('monedero.recargar'), {
        onSuccess: () => {
            cardNumber.value = '';
            cardExpiry.value = '';
            cardCvv.value    = '';
        },
    });
};

const retirar = () => {
    retiradaForm.post(route('monedero.retirar'), {
        onSuccess: () => {
            retiradaForm.reset();
        },
    });
};
</script>
