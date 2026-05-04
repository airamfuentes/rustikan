<script setup>
import { ref, nextTick, watch, onMounted, onUnmounted, computed } from 'vue';
import axios from 'axios';
import { MessageSquare, X, Send, Headphones } from 'lucide-vue-next';
import { usePage } from '@inertiajs/vue3';

const open      = ref(false);
const sending   = ref(false);
const input     = ref('');
const messages  = ref([]);
const msgsEl    = ref(null);
const inputEl   = ref(null);
const noLeidos  = ref(0);
let   pollTimer = null;

const page = usePage();
const chatNoLeidos = computed(() => page.props.chatNoLeidos ?? 0);
watch(chatNoLeidos, (v) => { if (!open.value) noLeidos.value = v; }, { immediate: true });

const scrollBottom = async () => {
    await nextTick();
    if (msgsEl.value) msgsEl.value.scrollTop = msgsEl.value.scrollHeight;
};

const fetchMensajes = async () => {
    try {
        const { data } = await axios.get('/api/chat-almacen/mensajes');
        messages.value = data.mensajes;
        if (open.value) {
            noLeidos.value = 0;
            scrollBottom();
        }
    } catch (_) {}
};

const toggleOpen = async () => {
    open.value = !open.value;
    if (open.value) {
        noLeidos.value = 0;
        await fetchMensajes();
        await nextTick();
        inputEl.value?.focus();
        scrollBottom();
    }
};

onMounted(() => {
    fetchMensajes();
    pollTimer = setInterval(async () => {
        if (open.value) {
            await fetchMensajes();
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
    if (!texto || sending.value) return;
    sending.value = true;
    input.value   = '';
    try {
        await axios.post('/api/chat-almacen/enviar', { mensaje: texto });
        await fetchMensajes();
    } catch (_) {
        messages.value.push({
            id: Date.now(),
            remitente: null,
            mensaje: 'Error al enviar. Inténtalo de nuevo.',
            created_at: new Date().toISOString(),
        });
    } finally {
        sending.value = false;
        scrollBottom();
    }
};

const handleKeydown = (e) => {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); send(); }
};

const user = computed(() => page.props.auth?.user);

const formatHora = (d) => new Date(d).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
</script>

<template>
    <!-- Botón flotante -->
    <button
        @click="toggleOpen"
        :aria-label="open ? 'Cerrar chat' : 'Contactar con administración'"
        class="fixed bottom-4 right-4 sm:bottom-6 sm:right-6 z-[9997] flex h-14 w-14 sm:h-16 sm:w-16 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-2xl ring-2 ring-white/40 transition-all duration-300 hover:scale-110 focus:outline-none"
        :class="open ? 'rotate-90 scale-95' : 'animate-pulse-chat'"
    >
        <!-- Badge no leídos -->
        <span
            v-if="noLeidos > 0 && !open"
            class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white ring-2 ring-white"
        >{{ noLeidos > 9 ? '9+' : noLeidos }}</span>

        <Transition name="icon-swap" mode="out-in">
            <X          v-if="open"  class="h-6 w-6" :key="'x'" />
            <Headphones v-else class="h-6 w-6" :key="'h'" />
        </Transition>
    </button>

    <!-- Ventana del chat -->
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
            aria-label="Chat con administración"
            class="fixed z-[9997] flex flex-col bg-white dark:bg-gray-900 shadow-2xl border border-gray-200 dark:border-gray-700 origin-bottom-right
                   inset-0 sm:inset-auto sm:bottom-24 sm:right-6 sm:h-[540px] sm:w-[360px] sm:max-h-[85vh] sm:rounded-2xl overflow-hidden"
        >
            <!-- Cabecera -->
            <div class="flex items-center justify-between gap-3 bg-gradient-to-r from-blue-500 to-blue-600 px-4 py-3 text-white shrink-0">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white/20">
                        <Headphones class="h-5 w-5" />
                    </div>
                    <div class="min-w-0">
                        <p class="font-semibold text-sm truncate">Soporte — Administración</p>
                        <p class="text-xs text-white/80">Equipo Rustikan</p>
                    </div>
                </div>
                <button
                    @click="toggleOpen"
                    aria-label="Cerrar chat"
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-white/90 transition hover:bg-white/15"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>

            <!-- Mensajes -->
            <div ref="msgsEl" class="flex-1 overflow-y-auto px-4 py-4 space-y-3 bg-gray-50 dark:bg-gray-950">
                <div v-if="messages.length === 0" class="flex flex-col items-center justify-center h-full gap-2 text-center">
                    <Headphones class="h-10 w-10 text-gray-300 dark:text-gray-600" />
                    <p class="text-sm text-gray-500 dark:text-gray-400">Escribe un mensaje para contactar con la administración.</p>
                </div>
                <div
                    v-for="msg in messages"
                    :key="msg.id"
                    class="flex"
                    :class="msg.remitente_id === user?.id ? 'justify-end' : 'justify-start'"
                >
                    <div class="max-w-[82%] space-y-0.5">
                        <p
                            v-if="msg.remitente_id !== user?.id"
                            class="text-[10px] font-medium text-gray-400 dark:text-gray-500 px-1"
                        >{{ msg.remitente?.name ?? 'Admin' }}</p>
                        <div
                            class="rounded-2xl px-3.5 py-2 text-sm leading-relaxed whitespace-pre-wrap break-words shadow-sm"
                            :class="msg.remitente_id === user?.id
                                ? 'bg-blue-500 text-white rounded-br-sm'
                                : 'bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 border border-gray-100 dark:border-gray-700 rounded-bl-sm'"
                        >{{ msg.mensaje }}</div>
                        <p class="text-[10px] text-gray-400 dark:text-gray-500 px-1" :class="msg.remitente_id === user?.id ? 'text-right' : ''">
                            {{ formatHora(msg.created_at) }}
                        </p>
                    </div>
                </div>

                <div v-if="sending" class="flex justify-start">
                    <div class="flex items-center gap-1.5 rounded-2xl rounded-bl-sm bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 px-3.5 py-2.5 shadow-sm">
                        <span class="h-2 w-2 rounded-full bg-blue-400 animate-bounce" style="animation-delay:0ms"></span>
                        <span class="h-2 w-2 rounded-full bg-blue-400 animate-bounce" style="animation-delay:150ms"></span>
                        <span class="h-2 w-2 rounded-full bg-blue-400 animate-bounce" style="animation-delay:300ms"></span>
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
                    placeholder="Escribe tu mensaje…"
                    maxlength="2000"
                    class="flex-1 resize-none rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 max-h-32"
                    :disabled="sending"
                />
                <button
                    type="submit"
                    :disabled="!canSend"
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-500 text-white transition hover:bg-blue-600 disabled:bg-gray-300 disabled:cursor-not-allowed dark:disabled:bg-gray-700"
                >
                    <Send class="h-4 w-4" />
                </button>
            </form>
        </div>
    </Transition>
</template>

<style scoped>
@keyframes pulse-chat {
    0%, 100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.6); }
    50%       { box-shadow: 0 0 0 12px rgba(59, 130, 246, 0); }
}
.animate-pulse-chat { animation: pulse-chat 2.5s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
.icon-swap-enter-active, .icon-swap-leave-active { transition: opacity 120ms ease, transform 120ms ease; }
.icon-swap-enter-from { opacity: 0; transform: rotate(-90deg); }
.icon-swap-leave-to   { opacity: 0; transform: rotate(90deg); }
</style>
