<script setup>
import { ref, nextTick, watch, onMounted, computed } from 'vue';
import axios from 'axios';
import { MessageCircle, X, Send, Sparkles } from 'lucide-vue-next';
import { usePage } from '@inertiajs/vue3';

const STORAGE_KEY = 'rustikan_chat_ia';

const open       = ref(false);
const sending    = ref(false);
const userInput  = ref('');
const messagesEl = ref(null);
const inputEl    = ref(null);

const initialGreeting = {
    role: 'assistant',
    text: '¡Hola! Soy Rusti, el asistente de Rustikan. ¿En qué puedo ayudarte? Puedo contarte sobre productos locales, cómo hacer un pedido, vender en la plataforma…',
};

const messages = ref([initialGreeting]);

// Restaurar conversación previa (si existe)
onMounted(() => {
    try {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (saved) {
            const parsed = JSON.parse(saved);
            if (Array.isArray(parsed) && parsed.length > 0) {
                messages.value = parsed;
            }
        }
    } catch (_) { /* ignore */ }
});

// Persistir conversación
watch(messages, (val) => {
    try {
        // Limita a últimos 30 para no inflar localStorage
        const trimmed = val.slice(-30);
        localStorage.setItem(STORAGE_KEY, JSON.stringify(trimmed));
    } catch (_) { /* ignore */ }
}, { deep: true });

const scrollToBottom = async () => {
    await nextTick();
    if (messagesEl.value) {
        messagesEl.value.scrollTop = messagesEl.value.scrollHeight;
    }
};

const toggleOpen = async () => {
    open.value = !open.value;
    if (open.value) {
        await nextTick();
        inputEl.value?.focus();
        scrollToBottom();
    }
};

const resetChat = () => {
    messages.value = [initialGreeting];
    open.value = false;
    localStorage.removeItem(STORAGE_KEY);
};

// Reset al hacer logout (usuario pasa a null) o al logearse (usuario cambia)
const page = usePage();
watch(() => page.props.auth?.user?.id, (newId, prevId) => {
    // Logout: tenía usuario y ahora no
    if (prevId && !newId) resetChat();
    // Login: no tenía usuario y ahora sí
    if (!prevId && newId) resetChat();
});

const canSend = computed(() => userInput.value.trim().length > 0 && !sending.value);

const send = async () => {
    const text = userInput.value.trim();
    if (!text || sending.value) return;

    // Añade mensaje del usuario
    messages.value.push({ role: 'user', text });
    userInput.value = '';
    sending.value = true;
    scrollToBottom();

    // Historial reciente (sin el mensaje recién enviado, lo manda como `message`)
    const historyForApi = messages.value
        .slice(0, -1)
        .filter(m => m !== initialGreeting || messages.value.length > 2)
        .slice(-10)
        .map(m => ({ role: m.role, text: m.text }));

    try {
        const { data } = await axios.post('/api/chat-ia', {
            message: text,
            history: historyForApi,
        });
        messages.value.push({ role: 'assistant', text: data.reply || 'Sin respuesta.' });
    } catch (err) {
        const status = err?.response?.status;
        const reply  = err?.response?.data?.reply
            || (status === 429
                ? 'Has enviado demasiados mensajes. Espera un momento.'
                : 'Ha ocurrido un error. Inténtalo de nuevo.');
        messages.value.push({ role: 'assistant', text: reply });
    } finally {
        sending.value = false;
        scrollToBottom();
    }
};

const handleKeydown = (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        send();
    }
};
</script>

<template>
    <!-- Botón flotante -->
    <button
        @click="toggleOpen"
        :aria-label="open ? 'Cerrar chat' : 'Abrir chat con asistente IA'"
        class="fixed bottom-4 right-4 sm:bottom-6 sm:right-6 z-[9998] flex h-14 w-14 sm:h-16 sm:w-16 items-center justify-center rounded-full bg-gradient-to-br from-primary-500 to-primary-600 text-white shadow-2xl ring-2 ring-white/40 dark:ring-gray-800 transition-all duration-300 hover:scale-110 hover:shadow-primary-500/50 focus:outline-none focus:ring-4 focus:ring-primary-300"
        :class="open ? 'rotate-90 scale-95' : 'animate-pulse-slow'"
    >
        <Transition name="icon-swap" mode="out-in">
            <X v-if="open" class="h-6 w-6 sm:h-7 sm:w-7" :key="'x'" />
            <MessageCircle v-else class="h-6 w-6 sm:h-7 sm:w-7" :key="'msg'" />
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
            aria-label="Chat con asistente Rusti"
            class="fixed z-[9998] flex flex-col bg-white dark:bg-gray-900 shadow-2xl border border-gray-200 dark:border-gray-700 origin-bottom-right
                   inset-0 sm:inset-auto sm:bottom-24 sm:right-6 sm:h-[560px] sm:w-[380px] sm:max-h-[85vh] sm:rounded-2xl
                   max-h-screen overflow-hidden"
        >
            <!-- Cabecera -->
            <div class="flex items-center justify-between gap-3 bg-gradient-to-r from-primary-500 to-primary-600 px-4 py-3 text-white shrink-0">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white/20 backdrop-blur-sm">
                        <Sparkles class="h-5 w-5" />
                    </div>
                    <div class="min-w-0">
                        <p class="font-semibold text-sm truncate">Rusti — Asistente Rustikan</p>
                        <p class="text-xs text-white/80 truncate">Pregúntame lo que quieras</p>
                    </div>
                </div>
                <div class="flex items-center gap-1 shrink-0">
                    <button
                        @click="resetChat"
                        class="rounded-md px-2 py-1 text-[11px] font-medium text-white/90 transition hover:bg-white/15"
                        title="Reiniciar conversación"
                    >Reiniciar</button>
                    <button
                        @click="toggleOpen"
                        aria-label="Cerrar chat"
                        class="flex h-8 w-8 items-center justify-center rounded-full text-white/90 transition hover:bg-white/15"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>
            </div>

            <!-- Mensajes -->
            <div
                ref="messagesEl"
                class="flex-1 overflow-y-auto px-4 py-4 space-y-3 bg-gray-50 dark:bg-gray-950"
            >
                <div
                    v-for="(msg, i) in messages"
                    :key="i"
                    class="flex"
                    :class="msg.role === 'user' ? 'justify-end' : 'justify-start'"
                >
                    <div
                        class="max-w-[85%] rounded-2xl px-3.5 py-2 text-sm leading-relaxed whitespace-pre-wrap break-words shadow-sm"
                        :class="msg.role === 'user'
                            ? 'bg-primary-500 text-white rounded-br-sm'
                            : 'bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100 border border-gray-100 dark:border-gray-700 rounded-bl-sm'"
                    >{{ msg.text }}</div>
                </div>
                <!-- Indicador de escritura -->
                <div v-if="sending" class="flex justify-start">
                    <div class="flex items-center gap-1.5 rounded-2xl rounded-bl-sm bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 px-3.5 py-2.5 shadow-sm">
                        <span class="h-2 w-2 rounded-full bg-primary-400 animate-bounce" style="animation-delay: 0ms"></span>
                        <span class="h-2 w-2 rounded-full bg-primary-400 animate-bounce" style="animation-delay: 150ms"></span>
                        <span class="h-2 w-2 rounded-full bg-primary-400 animate-bounce" style="animation-delay: 300ms"></span>
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
                    v-model="userInput"
                    @keydown="handleKeydown"
                    rows="1"
                    placeholder="Escribe tu pregunta…"
                    maxlength="1000"
                    class="flex-1 resize-none rounded-xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 max-h-32"
                    :disabled="sending"
                />
                <button
                    type="submit"
                    :disabled="!canSend"
                    aria-label="Enviar mensaje"
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary-500 text-white transition hover:bg-primary-600 disabled:bg-gray-300 disabled:cursor-not-allowed dark:disabled:bg-gray-700"
                >
                    <Send class="h-4 w-4" />
                </button>
            </form>
        </div>
    </Transition>
</template>

<style scoped>
@keyframes pulse-slow {
    0%, 100% { box-shadow: 0 0 0 0 rgba(249, 115, 22, 0.6); }
    50%      { box-shadow: 0 0 0 12px rgba(249, 115, 22, 0); }
}
.animate-pulse-slow {
    animation: pulse-slow 2.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.icon-swap-enter-active,
.icon-swap-leave-active {
    transition: opacity 120ms ease, transform 120ms ease;
}
.icon-swap-enter-from {
    opacity: 0;
    transform: rotate(-90deg);
}
.icon-swap-leave-to {
    opacity: 0;
    transform: rotate(90deg);
}
</style>
