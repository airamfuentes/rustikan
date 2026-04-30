export const vReveal = {
    mounted(el, binding) {
        if (typeof window === 'undefined') return

        const delay = binding.value?.delay ?? 0

        el.style.opacity = '0'
        el.style.transform = 'translateY(20px)'
        el.style.transition = `opacity 0.6s ease ${delay}ms, transform 0.6s ease ${delay}ms`

        const observer = new IntersectionObserver(
            (entries) => {
                if (entries[0].isIntersecting) {
                    el.style.opacity = '1'
                    el.style.transform = 'translateY(0)'
                    observer.disconnect()
                    delete el._revealObserver
                }
            },
            { threshold: 0.1 }
        )

        observer.observe(el)
        el._revealObserver = observer
    },
    unmounted(el) {
        el._revealObserver?.disconnect()
    },
}
