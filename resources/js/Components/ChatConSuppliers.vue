<script setup>
import { ref, nextTick, watch, onMounted, onUnmounted, computed } from 'vue';
import axios from 'axios';
import { MessageSquare, X, Send, ChevronLeft, Users } from 'lucide-vue-next';
import { usePage } from '@inertiajs/vue3';

const page       = usePage();
const open       = ref(false);
const sending    = ref(false);
const input      = ref('');
const msgsEl     = ref(null);
const inputEl    = ref(null);
const noLeidos   = ref(0);
const vista      = ref('lista');   // 'lista' | 'chat'
const supplierActivo = ref(null);
const conversaciones = ref([]);
const messages   = ref([]);
let pollTimer    = null;

const chatNoLeidos = computed(() => page.props.chatNoLeidos ?? 0);
watch(chatNoLeidos, (v) => { noLeidos.value = v; }, { immediate: true });

const scrollBottom = async () => {
    await nextTick();
    if (msgsEl.value) msgsEl.value.scrollTop = msgsEl.value.scrollHeight;
};

const fetchConversaciones = async () => {
    try {
        const { data } = await axios.get('/api/chat-almacen/conversaciones');
        conversaciones.value = data.conversaciones;
        noLeidos.value = data.conversaciones.reduce((acc, c) => acc + (c.no_leidos ?? 0), 0);
    } catch (_) {}
};

const abrirChat = async (conv) => {
    supplierActivo.value = conv;
    messages.value = [];
    vista.value = 'chat';
    await fetchMensajes();
    await nextTick();
    inputEl.value?.focus();
};

const fetchMensajes = async () => {
    if (!supplierActivo.value) return;
    try {
        const { data } = await axios.get('/api/chat-almacen/mensajes', {
            params: { supplier_id: supplierActivo.value.supplier.id },
        });
        messages.value = data.mensajes;
        // Restar no leídos de este supplier
        const idx = conversaciones.value.findIndex(c => c.supplier.id === supplierActivo.value.supplier.id);
        if (idx >= 0) conversaciones.value[idx].no_leidos = 0;
        noLeidos.value = conversaciones.value.reduce((acc, c) => acc + (c.no_leidos ?? 0), 0);
        scrollBottom();
    } catch (_) {}
};

const toggleOpen = async () => {
    open.value = !open.value;
    if (open.value) {
        vista.value = 'lista';
        supplierActivo.value = null;
        await fetchConversaciones();
    }
};

onMounted(() => {
    fetchConversaciones();
    pollTimer = setInterval(async () => {
        if (open.value && vista.value === 'chat') {
            await fetchMensajes();
        } else if (open.value && vista.value === 'lista') {
            await fetchConversaciones();
        } else {
            try {
                const { data } = await axios.get('/api/chat-almacen/no-leidos');
                noLeidos.value = data.count;
            } catch (_) {}
        }
    }, 4000);
});

onUnmounted(() => clearInterval(pollTimer));

const canSend = computed(() => input.value.trim().length > 0 && !sending.value);

const send = async () => {
    const texto = input.value.trim();
    if (!texto || sending.value || !supplierActivo.value) return;
    sending.value = true;
    input.value   = '';
    try {
        await axios.post('/api/chat-almacen/enviar', {
            mensaje:     texto,
            supplier_id: supplierActivo.value.supplier.id,
        });
        await fetchMensajes();
    } catch (_) {}
    finally { sending.value = false; }
};

const handleKeydown = (e) => {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); send(); }
};

const user = computed(() => page.props.auth?.user);
const formatHora = (d) => new Date(d).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
const formatFecha = (d) => {
    if (!d) return '';
    const date = new Date(d);
    const hoy  = new Date();
    if (date.toDateString() === hoy.toDateString()) return formatHora(d);
    return date.toLocaleDateString('es-ES', { day: '2-digit', month: 'short' });
};
</script>

<template>
    <!-- Botón flotante -->
    <button
        @click="toggleOpen"
        :aria-label="open ? 'Cerrar chat' : 'Chat con almacén'"
        class="fixed bottom-4 right-4 sm:bottom-6 sm:right-6 z-[9996] flex h-14 w-14 sm:h-16 sm:w-16 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-indigo-600 text-white shadow-2xl ring-2 ring-white/40 dark:ring-gray-800 transition-all duration-300 hover:scale-110 focus:outline-none"
        :class="open ? 'rotate-90 scale-95' : ''"
    >
        <span
            v-if="noLeidos > 0 && !open"
            class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white ring-2 ring-white dark:ring-gray-800"
        >{{ noLeidos > 9 ? '9+' : noLeidos }}</span>

        <Transition name="icon-swap" mode="out-in">
            <X     v-if="open" class="h-6 w-6" :key="'x'" />
            <Users v-else      class="h-6 w-6" :key="'u'" />
        </Transition>
    </button>

    <!-- Panel -->
    <Transition
        enter-active-class="transition ease-out duration-200"
        enter-from-class="opacity-0 translate-y-4 scale-95"
        enter-to-class="opacity-100 translate-y-0 scale-100"
        leave-active-class="transition ease-in duration-150"
        leave-from-class="opacity-100 translate-y-0 scale-100"
        leave-to-class="opacity-0 translate-y-4 scale-95"
    >
        <div
            v-if="open"
            role="dialog"
            aria-label="Chat con equipo de almacén"
            class="fixed z-[9996] flex flex-col bg-white dark:bg-gray-900 shadow-2xl border border-gray-200 dark:border-gray-700 origin-bottom-right
                   inset-0 sm:inset-auto sm:bottom-24 sm:right-6 sm:h-[560px] sm:w-[380px] sm:max-h-[85vh] sm:rounded-2xl overflow-hidden"
        >
            <!-- Header -->
            <div class="flex items-center gap-3 bg-gradient-to-r from-indigo-500 to-indigo-600 px-4 py-3 text-white shrink-0">
                <button
                    v-if="vista === 'chat'"
                    @click="vista = 'lista'; supplierActivo = null"
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-white/90 transition hover:bg-white/15"
                >
                    <ChevronLeft class="h-5 w-5" />
                </button>
                <div class="flex items-center gap-3 min-w-0 flex-1">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white/20">
                        <Users class="h-5 w-5" />
                    </div>
                    <div class="min-w-0">
                        <p class="font-semibold text-sm truncate">
                            {{ vista === 'chat' && supplierActivo ? supplierActivo.supplier.name : 'Chat con Almacén' }}
                        </p>
                        <p class="text-xs text-white/80">{{ vista === 'chat' ? 'Almacén / Supplier' : 'Mensajes del equipo de almacén' }}</p>
                    </div>
                </div>
                <button
                    @click="toggleOpen"
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-white/90 transition hover:bg-white/15"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>

            <!-- Vista: lista de conversaciones -->
            <div v-if="vista === 'lista'" class="flex-1 overflow-y-auto bg-gray-50 dark:bg-gray-950">
                <div v-if="conversaciones.length === 0" class="flex flex-col items-center justify-center h-full gap-2 text-center p-8">
                    <Users class="h-10 w-10 text-gray-300 dark:text-gray-600" />
                    <p class="text-sm text-gray-500 dark:text-gray-400">No hay conversaciones todavía.</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Los mensajes del equipo de almacén aparecerán aquí.</p>
                </div>
                <ul v-else class="divide-y divide-gray-200 dark:divide-gray-700">
                    <li
                        v-for="conv in conversaciones"
                        :key="conv.supplier.id"
                        @click="abrirChat(conv)"
                        class="flex items-center gap-3 px-4 py-3 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                    >
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full bg-indigo-500 text-white">
                            <img v-if="conv.supplier.avatar" :src="`/storage/${conv.supplier.avatar}`" class="h-full w-full object-cover" />
                            <span v-else class="text-sm font-bold">{{ conv.supplier.name?.charAt(0)?.toUpperCase() }}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between">
                                <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ conv.supplier.name }}</p>
                                <span class="text-[10px] text-gray-400 dark:text-gray-500 shrink-0 ml-2">{{ formatFecha(conv.ultimo_at) }}</span>
                            </div>
                            <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ conv.ultimo_mensaje ?? 'Sin mensajes' }}</p>
                        </div>
                        <span
                            v-if="conv.no_leidos > 0"
                            class="flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white shrink-0"
                        >{{ conv.no_leidos }}</span>
                    </li>
                </ul>
            </div>

            <!-- Vista: chat individual -->
            <template v-else>
                <div ref="msgsEl" class="flex-1 overflow-y-auto px-4 py-4 space-y-3 bg-gray-50 dark:bg-gray-950">
                    <div v-if="messages.length === 0" class="flex flex-col items-center justify-center h-full gap-2 text-center">
                        <MessageSquare class="h-10 w-10 text-gray-300 dark:text-gray-600" />
                        <p class="text-sm text-gray-500 dark:text-gray-400">Sin mensajes aún.</p>
                    </div>
                    <div
                        v-for="msg in messages"
                        :key="msg.id"
                        class="flex"
                        :class="msg.remitente_id !== supplierActivo?.supplier?.id ? 'justify-end' : 'justify-start'"
                    >
                        <div class="max-w-[82%] space-y-0.5">
                            <p v-if="msg.remitente_id !== supplierActivo?.supplier?.id" class="text-[10px] font-medium text-gray-400 dark:text-gray-500 px-1 text-right">
                                Tú ({{ msg.remitente?.name }})
                            </p>
                            <div
                                class="rounded-2xl px-3.5 py-2 text-sm leading-relaxed whitespace-pre-wrap break-words shadow-sm"
                                :class="msg.remitente_id !== supplierActivo?.supplier?.id
                                    ? 'bg-indigo-500 text-white rounded-br-sm'
                                    : 'bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 border border-gray-100 dark:border-gray-700 rounded-bl-sm'"
                            >{{ msg.mensaje }}</div>
                            <p class="text-[10px] text-gray-400 dark:text-gray-500 px-1" :class="msg.remitente_id !== supplierActivo?.supplier?.id ? 'text-right' : ''">
                                {{ formatHora(msg.created_at) }}
                            </p>
                        </div>
                    </div>
                    <div v-if="sending" class="flex justify-end">
                        <div class="flex items-center gap-1.5 rounded-2xl rounded-br-sm bg-indigo-500/20 px-3.5 py-2.5">
                            <span class="h-2 w-2 rounded-full bg-indigo-400 animate-bounce" style="animation-delay:0ms"></span>
                            <span class="h-2 w-2 rounded-full bg-indigo-400 animate-bounce" style="animation-delay:150ms"></span>
                            <span class="h-2 w-2 rounded-full bg-indigo-400 animate-bounce" style="animation-delay:300ms"></span>
                        </div>
                    </div>
                </div>

                <!-- Input -->
                <form
                    @submit.prevent="send"
                    class="flex items-end gap-2 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-3 shrink-0"
                >
                    <textarea
                        ref="inputEl"
                        v-model="input"
                        @keydown="handleKeydown"
                        rows="1"
                        placeholder="Escribe tu respuesta…"
                        maxlength="2000"
                        class="flex-1 resize-none rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 max-h-32"
                        :disabled="sending"
                    />
                    <button
                        type="submit"
                        :disabled="!canSend"
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-500 text-white transition hover:bg-indigo-600 disabled:bg-gray-300 disabled:cursor-not-allowed dark:disabled:bg-gray-700"
                    >
                        <Send class="h-4 w-4" />
                    </button>
                </form>
            </template>
        </div>
    </Transition>
</template>

<style scoped>
.icon-swap-enter-active, .icon-swap-leave-active { transition: opacity 120ms ease, transform 120ms ease; }
.icon-swap-enter-from { opacity: 0; transform: rotate(-90deg); }
.icon-swap-leave-to   { opacity: 0; transform: rotate(90deg); }
</style>
