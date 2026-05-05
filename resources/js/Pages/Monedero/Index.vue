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

            <div class="grid gap-8 lg:grid-cols-2">

                <!-- Recargar -->
                <div :class="['rounded-2xl border p-6 shadow-sm', isDark ? 'bg-gray-800 border-gray-700' : 'bg-white border-gray-200']">
                    <h2 class="mb-1 text-lg font-bold">Añadir fondos</h2>
                    <p :class="['mb-5 text-sm', isDark ? 'text-gray-400' : 'text-gray-500']">Recarga tu monedero con tarjeta.</p>

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
                            <div class="flex items-center justify-between">
                                <p :class="['text-xs font-semibold uppercase tracking-wider', isDark ? 'text-gray-400' : 'text-gray-500']">Datos de tarjeta</p>
                                <span v-if="cardBrand" class="text-[10px] font-bold uppercase rounded px-1.5 py-0.5"
                                    :class="cardBrand === 'visa' ? 'bg-blue-100 text-blue-700' : cardBrand === 'mastercard' ? 'bg-orange-100 text-orange-700' : 'bg-gray-200 text-gray-700'">{{ cardBrand }}</span>
                            </div>

                            <div>
                                <input v-model="cardData.numero" type="text" placeholder="1234 5678 9012 3456" inputmode="numeric" autocomplete="cc-number" @input="formatCard"
                                    :class="['w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2',
                                        cardErrors.numero ? 'border-red-400 focus:ring-red-200' : 'focus:ring-primary-400',
                                        isDark ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-500' : 'bg-white border-gray-300 text-gray-900 placeholder-gray-400']"
                                />
                                <p v-if="cardErrors.numero" class="mt-1 text-xs text-red-500">{{ cardErrors.numero }}</p>
                            </div>

                            <div>
                                <input v-model="cardData.titular" type="text" placeholder="Titular de la tarjeta" autocomplete="cc-name"
                                    :class="['w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2',
                                        cardErrors.titular ? 'border-red-400 focus:ring-red-200' : 'focus:ring-primary-400',
                                        isDark ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-500' : 'bg-white border-gray-300 text-gray-900 placeholder-gray-400']"
                                />
                                <p v-if="cardErrors.titular" class="mt-1 text-xs text-red-500">{{ cardErrors.titular }}</p>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <input :value="cardData.expiry" type="text" placeholder="MM/AA" inputmode="numeric" autocomplete="cc-exp" @input="formatExpiry"
                                        :class="['w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2',
                                            cardErrors.expiry ? 'border-red-400 focus:ring-red-200' : 'focus:ring-primary-400',
                                            isDark ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-500' : 'bg-white border-gray-300 text-gray-900 placeholder-gray-400']"
                                    />
                                    <p v-if="cardErrors.expiry" class="mt-1 text-xs text-red-500">{{ cardErrors.expiry }}</p>
                                </div>
                                <div>
                                    <input :value="cardData.cvv" type="text" placeholder="CVV" inputmode="numeric" autocomplete="cc-csc" maxlength="3" @input="formatCvv"
                                        :class="['w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2',
                                            cardErrors.cvv ? 'border-red-400 focus:ring-red-200' : 'focus:ring-primary-400',
                                            isDark ? 'bg-gray-700 border-gray-600 text-white placeholder-gray-500' : 'bg-white border-gray-300 text-gray-900 placeholder-gray-400']"
                                    />
                                    <p v-if="cardErrors.cvv" class="mt-1 text-xs text-red-500">{{ cardErrors.cvv }}</p>
                                </div>
                            </div>
                        </div>

                        <button type="submit"
                            :disabled="recargaForm.processing || !recargaForm.cantidad || recargaForm.cantidad < 1"
                            class="w-full rounded-xl bg-primary-500 px-6 py-3 font-bold text-white shadow transition-all hover:bg-primary-600 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="recargaForm.processing">Procesando...</span>
                            <span v-else>Añadir {{ recargaForm.cantidad ? Number(recargaForm.cantidad).toFixed(2) : '0.00' }} RC</span>
                        </button>
                    </form>
                </div>

                <!-- Retirar -->
                <div :class="['rounded-2xl border p-6 shadow-sm', isDark ? 'bg-gray-800 border-gray-700' : 'bg-white border-gray-200']">
                    <h2 class="mb-1 text-lg font-bold">Retirar fondos</h2>
                    <p :class="['mb-5 text-sm', isDark ? 'text-gray-400' : 'text-gray-500']">Transfiere RustiCoins de vuelta a tu tarjeta.</p>

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
                            <Info class="h-5 w-5 shrink-0" :class="isDark ? 'text-gray-400' : 'text-orange-600'" />
                            <p :class="['text-xs', isDark ? 'text-gray-400' : 'text-orange-700']">
                                El reembolso se hará a la tarjeta original. Puede tardar 5-10 días hábiles.
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
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import NavbarPublico from '@/Components/NavbarPublico.vue';
import FooterPublico from '@/Components/FooterPublico.vue';
import { useDarkMode } from '@/Composables/useDarkMode.js';
import { Coins, Plus, ShoppingCart, ArrowUpRight, RotateCcw, Wallet, Info } from 'lucide-vue-next';

const props = defineProps({
    saldo:         { type: Number, default: 0 },
    transacciones: { type: Array, default: () => [] },
});

const { isDark } = useDarkMode();

const recargaForm  = useForm({ cantidad: 10 });
const retiradaForm = useForm({ cantidad: null });

const cardData = ref({
    titular: '',
    numero:  '',
    expiry:  '',
    cvv:     '',
});
const cardErrors = ref({});

const formatCard = (e) => {
    cardData.value.numero = e.target.value
        .replace(/\D/g, '')
        .slice(0, 16)
        .replace(/(.{4})/g, '$1 ')
        .trim();
};

const formatExpiry = (e) => {
    const digits = e.target.value.replace(/\D/g, '').slice(0, 4);
    cardData.value.expiry = digits.length >= 3 ? digits.slice(0, 2) + '/' + digits.slice(2) : digits;
};

const formatCvv = (e) => {
    cardData.value.cvv = e.target.value.replace(/\D/g, '').slice(0, 3);
};

const luhnCheck = (num) => {
    const digits = num.replace(/\D/g, '');
    if (digits.length < 13) return false;
    let sum = 0, alt = false;
    for (let i = digits.length - 1; i >= 0; i--) {
        let n = parseInt(digits[i]);
        if (alt) { n *= 2; if (n > 9) n -= 9; }
        sum += n;
        alt = !alt;
    }
    return sum % 10 === 0;
};

const cardBrand = computed(() => {
    const n = cardData.value.numero.replace(/\D/g, '');
    if (/^4/.test(n)) return 'visa';
    if (/^5[1-5]|^2[2-7]/.test(n)) return 'mastercard';
    if (/^3[47]/.test(n)) return 'amex';
    return null;
});

const validarTarjeta = () => {
    cardErrors.value = {};
    if (!cardData.value.titular.trim()) {
        cardErrors.value.titular = 'El nombre del titular es obligatorio.';
    }
    const digits = cardData.value.numero.replace(/\D/g, '');
    if (digits.length < 16 || !luhnCheck(digits)) {
        cardErrors.value.numero = 'Número de tarjeta no válido.';
    }
    const [mes, anio] = cardData.value.expiry.split('/');
    const ahora = new Date();
    const mesN = parseInt(mes); const anioN = 2000 + parseInt(anio ?? '0');
    if (!mes || !anio || mesN < 1 || mesN > 12 || anioN < ahora.getFullYear() ||
        (anioN === ahora.getFullYear() && mesN < ahora.getMonth() + 1)) {
        cardErrors.value.expiry = 'Fecha de caducidad no válida.';
    }
    if (!cardData.value.cvv || cardData.value.cvv.length < 3) {
        cardErrors.value.cvv = 'CVV no válido.';
    }
    return Object.keys(cardErrors.value).length === 0;
};

const recargar = () => {
    if (!validarTarjeta()) return;
    recargaForm.post(route('monedero.recargar'), {
        onSuccess: () => {
            cardData.value = { titular: '', numero: '', expiry: '', cvv: '' };
            cardErrors.value = {};
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
