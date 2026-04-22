import { ref, watch, effectScope } from 'vue'

// Singleton — estado compartido entre todos los componentes
const isDark = ref(false)
let initialized = false

// Scope desconectado del ciclo de vida de cualquier componente.
// Esto es crítico para Inertia.js: al navegar entre páginas Vue desmonta
// el componente anterior y detiene todos sus watchers. Con effectScope(true)
// el watcher sobrevive a esas desmontajes y sigue sincronizando la clase
// "dark" en <html> sin importar qué componente esté activo.
const persistentScope = effectScope(true)

function initDarkMode() {
    if (initialized || typeof window === 'undefined') return
    initialized = true

    const saved = localStorage.getItem('theme')
    isDark.value =
        saved === 'dark' ||
        (saved === null && window.matchMedia('(prefers-color-scheme: dark)').matches)

    // Sincroniza la clase HTML con el estado reactivo
    document.documentElement.classList.toggle('dark', isDark.value)

    // Registrar el watcher en el scope persistente para que no muera
    // cuando el componente que llamó a useDarkMode() se desmonte.
    persistentScope.run(() => {
        watch(isDark, (val) => {
            document.documentElement.classList.toggle('dark', val)
            localStorage.setItem('theme', val ? 'dark' : 'light')
        })
    })
}

export function useDarkMode() {
    initDarkMode()

    const toggleDark = () => {
        isDark.value = !isDark.value
    }

    return { isDark, toggleDark }
}
