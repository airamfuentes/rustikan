import { ref, onUnmounted } from 'vue'

export function useCountUp(target, duration = 2000) {
    const count = ref(0)
    let observer = null
    let rafId = null

    const start = () => {
        const startTime = performance.now()
        const step = (now) => {
            const progress = Math.min((now - startTime) / duration, 1)
            // ease-out cubic
            const eased = 1 - Math.pow(1 - progress, 3)
            count.value = Math.round(eased * target)
            if (progress < 1) {
                rafId = requestAnimationFrame(step)
            }
        }
        rafId = requestAnimationFrame(step)
    }

    const observe = (el) => {
        if (!el || typeof window === 'undefined') return
        observer = new IntersectionObserver(
            (entries) => {
                if (entries[0].isIntersecting) {
                    start()
                    observer.disconnect()
                    observer = null
                }
            },
            { threshold: 0.5 }
        )
        observer.observe(el)
    }

    onUnmounted(() => {
        observer?.disconnect()
        if (rafId) cancelAnimationFrame(rafId)
    })

    return { count, observe }
}
