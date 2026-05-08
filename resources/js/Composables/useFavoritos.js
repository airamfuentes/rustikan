import { ref, watch, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import axios from 'axios'
import { useToasts } from './useToasts'
import { useI18n } from './useI18n'

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

// Bandera para evitar dobles clics mientras está en vuelo
const enVuelo = ref(new Set())

export function useFavoritos() {
    const page = usePage()
    const { success, info, warning } = useToasts()
    const { t } = useI18n()

    // Lista efectiva: si hay user autenticado usamos los IDs servidos por
    // Inertia (compartidos vía HandleInertiaRequests). Si no, localStorage.
    const lista = computed(() => {
        const ids = page.props?.tiendasFavoritasIds
        if (Array.isArray(ids)) return ids
        return favoritos.value
    })

    const esFavorito = (tiendaId) => lista.value.includes(tiendaId)

    const toggleFavorito = async (tiendaId, tiendaNombre = '') => {
        const user = page.props?.auth?.user
        if (!user) {
            // Guests: requerir login para no perder favoritos al volver
            warning(t('favs.login_required_title'), t('favs.login_required_msg'))
            return
        }
        if (enVuelo.value.has(tiendaId)) return
        enVuelo.value.add(tiendaId)

        const eraFavorito = esFavorito(tiendaId)
        try {
            const { data } = await axios.post(`/favoritos/${tiendaId}/toggle`)
            const nombre = tiendaNombre || data.nombre || ''
            if (data.favorited) {
                success(t('favs.added'), t('favs.added_msg', { name: nombre }))
            } else {
                info(t('favs.removed'), t('favs.removed_msg', { name: nombre }))
            }
            // Refrescar las props compartidas para que `lista` se actualice
            // sin recargar la página. Solo actualizamos esa prop.
            router.reload({ only: ['tiendasFavoritasIds'] })
        } catch (e) {
            // Si falla, no cambiar nada visualmente
            warning(t('favs.login_required_title'), t('favs.login_required_msg'))
        } finally {
            enVuelo.value.delete(tiendaId)
        }
    }

    return { favoritos: lista, toggleFavorito, esFavorito }
}
