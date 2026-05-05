<template>
    <footer class="bg-gray-900 text-gray-300">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="grid gap-8 md:grid-cols-4">

                <!-- Columna 1: Logo, descripción y GitHub -->
                <div class="md:col-span-1">
                    <Link href="/">
                        <img src="/images/logo.png" alt="Rustikan" class="mb-4 h-12 w-auto brightness-0 invert" />
                    </Link>
                    <p class="text-sm leading-relaxed">
                        {{ t('footer.tagline') }}
                    </p>
                    <a
                        href="https://github.com/airamfuentes"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="mt-4 inline-flex items-center gap-2 text-sm text-gray-400 hover:text-white transition-colors group"
                    >
                        <svg class="h-5 w-5 flex-shrink-0 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 0C5.374 0 0 5.373 0 12c0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23A11.509 11.509 0 0112 5.803c1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576C20.566 21.797 24 17.3 24 12c0-6.627-5.373-12-12-12z"/>
                        </svg>
                        <span>airamfuentes</span>
                    </a>
                </div>

                <!-- Columna 2: Sobre Nosotros -->
                <div>
                    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-white">{{ t('footer.about_title') }}</h3>
                    <ul class="space-y-2 text-sm">
                        <li><Link :href="route('info.quienes-somos')" class="hover:text-primary-400 transition-colors">{{ t('footer.about_us') }}</Link></li>
                        <li><Link :href="route('info.mision')" class="hover:text-primary-400 transition-colors">{{ t('footer.mission') }}</Link></li>
                        <li><Link :href="route('info.contacto')" class="hover:text-primary-400 transition-colors">{{ t('footer.contact') }}</Link></li>
                    </ul>
                </div>

                <!-- Columna 3: Para Productores -->
                <div>
                    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-white">{{ t('footer.producers_title') }}</h3>
                    <ul class="space-y-2 text-sm">
                        <li><Link :href="route('info.vende')" class="hover:text-primary-400 transition-colors">{{ t('footer.sell_with_us') }}</Link></li>
                        <li><Link :href="route('info.faq')" class="hover:text-primary-400 transition-colors">{{ t('footer.faq') }}</Link></li>
                    </ul>
                </div>

                <!-- Columna 4: Legal -->
                <div>
                    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-white">{{ t('footer.legal_title') }}</h3>
                    <ul class="space-y-2 text-sm">
                        <li><Link :href="route('info.terminos')" class="hover:text-primary-400 transition-colors">{{ t('footer.terms') }}</Link></li>
                        <li><Link :href="route('info.privacidad')" class="hover:text-primary-400 transition-colors">{{ t('footer.privacy') }}</Link></li>
                        <li><Link :href="route('info.cookies')" class="hover:text-primary-400 transition-colors">{{ t('footer.cookies') }}</Link></li>
                    </ul>
                </div>
            </div>

            <!-- Copyright -->
            <div class="mt-10 border-t border-gray-800 pt-8 text-center text-sm text-gray-500">
                <p>&copy; {{ new Date().getFullYear() }} Rustikan. {{ t('footer.rights') }}.</p>
            </div>
        </div>
    </footer>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { useI18n } from '@/Composables/useI18n';
const { t } = useI18n();
</script>
