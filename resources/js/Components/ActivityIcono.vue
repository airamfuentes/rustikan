<script setup>
import { computed } from 'vue';
import {
    Activity, Clock, CheckCircle2, Package, Truck, Home, XCircle,
    UserPlus, UserCheck, UserX, Store, FileEdit, Trash2, Send, Bell,
    ShieldCheck, Star, AlertTriangle, FileText
} from 'lucide-vue-next';

const props = defineProps({
    icono: { type: String, default: null },
    class: { type: String, default: 'h-5 w-5' },
});

const MAP = {
    '⌛': Clock,
    '✅': CheckCircle2,
    '📦': Package,
    '🚚': Truck,
    '🏠': Home,
    '❌': XCircle,
    '📝': FileEdit,
    '📋': FileText,
    '👤': UserPlus,
    '🛡️': ShieldCheck,
    '🏪': Store,
    '⭐': Star,
    '⚠️': AlertTriangle,
    '🔔': Bell,
    '📧': Send,
    '🗑️': Trash2,
    '✏️': FileEdit,
    '👁️': Bell,
    '📊': Activity,
    '🏷️': Star,
    '✔': CheckCircle2,
    '✖': XCircle,
    'pendiente':   Clock,
    'confirmado':  CheckCircle2,
    'preparando':  Package,
    'en_camino':   Truck,
    'entregado':   Home,
    'cancelado':   XCircle,
    'aprobada':    CheckCircle2,
    'rechazada':   XCircle,
    'crear':       FileEdit,
    'editar':      FileEdit,
    'eliminar':    Trash2,
    'actualizar':  FileEdit,
    'tienda':      Store,
    'usuario':     UserPlus,
    'producto':    Package,
    'stock':       Activity,
    'oferta':      Star,
    'visibilidad': Bell,
};

const componente = computed(() => MAP[props.icono] ?? Activity);
</script>

<template>
    <component :is="componente" :class="$props.class" />
</template>
