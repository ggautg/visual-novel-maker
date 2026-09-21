<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: { type: Boolean },
    canRegister: { type: Boolean },
    laravelVersion: { type: String, required: true },
    phpVersion: { type: String, required: true },
});
</script>

<template>
    <Head title="Visual Novel Maker" />

    <div class="vn-page">
        <div class="vn-scanlines"></div>

        <div class="vn-star vn-star--1">✦</div>
        <div class="vn-star vn-star--2">✧</div>
        <div class="vn-star vn-star--3">✦</div>
        <div class="vn-star vn-star--4">✧</div>
        <div class="vn-star vn-star--5">✦</div>

        <div class="relative mx-auto flex min-h-screen max-w-6xl flex-col px-6">
            <!-- Header -->
            <header class="flex items-center justify-between py-8">
                <span class="vn-wordmark">VN Maker</span>

                <nav v-if="canLogin" class="flex items-center gap-3 text-sm">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('novels.index')"
                        class="vn-link"
                    >
                        Mi panel
                    </Link>
                    <template v-else>
                        <Link :href="route('login')" class="vn-link">
                            Entrar
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="vn-pill vn-pill--ghost"
                        >
                            Crear cuenta
                        </Link>
                    </template>
                </nav>
            </header>

            <!-- Hero -->
            <main class="flex flex-1 flex-col items-center justify-center py-16 text-center">
                <p class="vn-eyebrow">crea · publicá · jugá</p>

                <h1 class="vn-title">
                    Tu historia,<br />tus reglas
                </h1>

                <p class="mx-auto mt-6 max-w-lg text-base leading-relaxed text-[#D9CFFB]">
                    Armá visual novels con personajes, escenas y decisiones
                    que realmente cambian el rumbo de la historia. Sin
                    escribir una línea de código.
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
                        Empezar gratis
                    </Link>
                    <Link :href="route('novels.public') ?? '#'" class="vn-pill vn-pill--ghost">
                        Explorar novelas →
                    </Link>
                </div>
            </main>

            <!-- Menú de features -->
            <section class="vn-panel mb-16">
                <div class="vn-panel-row">
                    <span class="vn-panel-marker">▸</span>
                    <div>
                        <h3 class="vn-panel-title">Personajes con expresiones</h3>
                        <p class="vn-panel-text">
                            Cada personaje puede tener múltiples sprites: feliz,
                            triste, enojado — lo que la escena necesite.
                        </p>
                    </div>
                </div>
                <div class="vn-panel-row">
                    <span class="vn-panel-marker">▸</span>
                    <div>
                        <h3 class="vn-panel-title">Diálogos que se ramifican</h3>
                        <p class="vn-panel-text">
                            Las elecciones del jugador mueven variables reales:
                            afecto, inventario, finales distintos.
                        </p>
                    </div>
                </div>
                <div class="vn-panel-row">
                    <span class="vn-panel-marker">▸</span>
                    <div>
                        <h3 class="vn-panel-title">Publicá y compartí</h3>
                        <p class="vn-panel-text">
                            Cuando tu historia está lista, publicala y cualquiera
                            puede jugarla desde el navegador.
                        </p>
                    </div>
                </div>
            </section>

            <footer class="pb-8 text-center text-xs text-[#6E5FA3]">
                Laravel v{{ laravelVersion }} · PHP v{{ phpVersion }}
            </footer>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Rubik:wght@400;500&display=swap');

.vn-page {
    position: relative;
    min-height: 100vh;
    overflow: hidden;
    background: radial-gradient(120% 90% at 50% -10%, #2a1b54 0%, #120b26 60%);
    font-family: 'Rubik', system-ui, sans-serif;
    color: #fff6fb;
}

.vn-scanlines {
    position: absolute;
    inset: 0;
    pointer-events: none;
    background: repeating-linear-gradient(
        to bottom,
        rgba(255, 255, 255, 0.025) 0px,
        rgba(255, 255, 255, 0.025) 1px,
        transparent 1px,
        transparent 3px
    );
    mix-blend-mode: overlay;
}

.vn-star {
    position: absolute;
    color: #c9b8ff;
    opacity: 0.6;
    font-size: 1.25rem;
    animation: vn-twinkle 3.5s ease-in-out infinite;
    pointer-events: none;
}
.vn-star--1 { top: 14%; left: 10%; animation-delay: 0s; }
.vn-star--2 { top: 22%; right: 14%; font-size: 1.75rem; animation-delay: 0.6s; }
.vn-star--3 { top: 62%; left: 6%; font-size: 1rem; animation-delay: 1.2s; }
.vn-star--4 { top: 68%; right: 8%; animation-delay: 1.8s; }
.vn-star--5 { top: 40%; right: 30%; font-size: 0.9rem; animation-delay: 2.4s; }

@keyframes vn-twinkle {
    0%, 100% { opacity: 0.25; transform: scale(0.9); }
    50% { opacity: 0.85; transform: scale(1.15); }
}

.vn-wordmark {
    font-family: 'Fredoka', sans-serif;
    font-weight: 600;
    font-size: 1.1rem;
    color: #fff6fb;
}

.vn-link {
    color: #d9cffb;
    transition: color 0.2s ease;
}
.vn-link:hover {
    color: #ffffff;
}

.vn-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    padding: 0.7rem 1.75rem;
    font-size: 0.95rem;
    font-weight: 500;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.vn-pill--solid {
    background: linear-gradient(135deg, #ff5fa2, #ff8fc0);
    color: #1a0f36;
    font-weight: 600;
    box-shadow: 0 8px 24px rgba(255, 95, 162, 0.35);
}
.vn-pill--solid:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(255, 95, 162, 0.45);
}
.vn-pill--ghost {
    border: 1px solid rgba(201, 184, 255, 0.4);
    color: #e8e2ff;
}
.vn-pill--ghost:hover {
    border-color: #5ef1e6;
    color: #5ef1e6;
}

.vn-eyebrow {
    font-family: 'Rubik', sans-serif;
    font-size: 0.85rem;
    letter-spacing: 0.03em;
    color: #9d8fd6;
}

.vn-title {
    margin-top: 0.75rem;
    font-family: 'Fredoka', sans-serif;
    font-weight: 700;
    font-size: clamp(2.75rem, 7vw, 5rem);
    line-height: 1.05;
    background: linear-gradient(100deg, #ffb3dc 0%, #ff5fa2 30%, #c9b8ff 55%, #5ef1e6 80%, #ffb3dc 100%);
    background-size: 200% auto;
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    animation: vn-shine 6s ease-in-out infinite;
}

@keyframes vn-shine {
    0% { background-position: 0% center; }
    50% { background-position: 100% center; }
    100% { background-position: 0% center; }
}

.vn-panel {
    border-radius: 1.25rem;
    border: 1px solid rgba(201, 184, 255, 0.15);
    background: rgba(255, 255, 255, 0.03);
    backdrop-filter: blur(6px);
    padding: 1.5rem;
    display: grid;
    gap: 1.5rem;
}
@media (min-width: 768px) {
    .vn-panel {
        grid-template-columns: repeat(3, 1fr);
    }
}

.vn-panel-row {
    display: flex;
    gap: 0.75rem;
}

.vn-panel-marker {
    color: #ff5fa2;
    font-size: 1rem;
    line-height: 1.6;
}

.vn-panel-title {
    font-family: 'Fredoka', sans-serif;
    font-weight: 500;
    font-size: 1rem;
    color: #fff6fb;
}

.vn-panel-text {
    margin-top: 0.35rem;
    font-size: 0.85rem;
    line-height: 1.5;
    color: #b8acdd;
}

@media (prefers-reduced-motion: reduce) {
    .vn-title, .vn-star {
        animation: none;
    }
}
</style>