import { ref, watch } from 'vue'

const STORAGE_KEY = 'rustikan_favoritos'

// Singleton — compartido entre todos los componentes
const favoritos = ref(
    typeof window !== 'undefined'
        ? JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]')
        : []
)

if (typeof window !== 'undefined') {
    watch(favoritos, (val) => {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(val))
    }, { deep: true })
}

export function useFavoritos() {
    const toggleFavorito = (tiendaId) => {
        const idx = favoritos.value.indexOf(tiendaId)
        if (idx === -1) {
            favoritos.value = [...favoritos.value, tiendaId]
        } else {
            favoritos.value = favoritos.value.filter(id => id !== tiendaId)
        }
    }

    const esFavorito = (tiendaId) => favoritos.value.includes(tiendaId)

    return { favoritos, toggleFavorito, esFavorito }
}
