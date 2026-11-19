<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ThemePicker from '@/Components/ThemePicker.vue';

defineProps({
    canLogin: { type: Boolean, default: false },
    canRegister: { type: Boolean, default: false },
    laravelVersion: { type: String, required: true },
    phpVersion: { type: String, required: true },
    appName: { type: String, default: 'VN Maker' },
});
</script>

<template>
    <Head title="Inicio" />

    <div class="vn-page">
        <div class="vn-scanlines"></div>

        <div class="vn-star vn-star--1">✦</div>
        <div class="vn-star vn-star--2">✧</div>
        <div class="vn-star vn-star--3">✦</div>
        <div class="vn-star vn-star--4">✧</div>
        <div class="vn-star vn-star--5">✦</div>

        <div class="relative mx-auto flex min-h-screen max-w-6xl flex-col px-6">
            <header class="flex flex-wrap items-center justify-between gap-4 py-8">
                <span class="vn-wordmark">{{ appName }}</span>

                <div class="flex items-center gap-3">
                    <ThemePicker />
                    <nav v-if="canLogin" class="flex items-center gap-3 text-sm">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('novels.index')"
                            class="vn-link"
                        >
                            Mi panel
                        </Link>
                        <template v-else>
                            <Link :href="route('login')" class="vn-link">Inicia sesión</Link>
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="vn-pill vn-pill--ghost"
                            >
                                Regístrate
                            </Link>
                        </template>
                    </nav>
                </div>
            </header>

            <main class="flex flex-1 flex-col items-center justify-center py-16 text-center">
                <p class="vn-eyebrow">crea · publica · juega</p>

                <h1 class="vn-title">Tu historia, tus reglas</h1>

                <p class="vn-narrative mx-auto mt-6 max-w-prose text-base">
                    Crea novelas visuales con personajes, escenas y decisiones que realmente cambian
                    el rumbo de la historia. Sin escribir una línea de código.
                </p>

                <div class="mt-10 flex flex-col gap-4 sm:flex-row">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('novels.index')"
                        class="vn-pill vn-pill--solid"
                    >
                        Ir a mis novelas
                    </Link>
                    <Link
                        v-else-if="canRegister"
                        :href="route('register')"
                        class="vn-pill vn-pill--solid"
                    >
                        Crea tu cuenta
                    </Link>
                    <Link :href="route('novels.public')" class="vn-pill vn-pill--ghost">
                        Explorar novelas →
                    </Link>
                </div>
            </main>

            <section class="vn-panel mb-16">
                <div class="vn-panel-row">
                    <span class="vn-panel-marker">▸</span>
                    <div>
                        <h3 class="vn-panel-title">Personajes con expresiones</h3>
                        <p class="vn-panel-text">
                            Cada personaje puede tener varias imágenes: feliz, triste, enfadado; lo que la escena necesite.
                        </p>
                    </div>
                </div>
                <div class="vn-panel-row">
                    <span class="vn-panel-marker">▸</span>
                    <div>
                        <h3 class="vn-panel-title">Diálogos que se ramifican</h3>
                        <p class="vn-panel-text">
                            Las decisiones del jugador modifican variables reales: afecto, inventario y finales distintos.
                        </p>
                    </div>
                </div>
                <div class="vn-panel-row">
                    <span class="vn-panel-marker">▸</span>
                    <div>
                        <h3 class="vn-panel-title">Publica y comparte</h3>
                        <p class="vn-panel-text">
                            Cuando tu historia esté lista, publicala y cualquiera podrá jugarla desde el navegador.
                        </p>
                    </div>
                </div>
            </section>

            <footer class="pb-8 text-center text-xs opacity-70">
                Laravel v{{ laravelVersion }} · PHP v{{ phpVersion }}
            </footer>
        </div>
    </div>
</template>
