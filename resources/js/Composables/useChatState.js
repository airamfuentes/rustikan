import { ref } from 'vue';

const suppliersOpen = ref(false);

export function useChatState() {
    return { suppliersOpen };
}
