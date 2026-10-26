<script setup>
import { Link } from '@inertiajs/vue3';
import ThemePicker from '@/Components/ThemePicker.vue';
</script>

<template>
    <div class="vn-page">
        <ThemePicker class="absolute right-4 top-4 z-20" />
        <div class="vn-scanlines"></div>
        <div class="vn-star vn-star--1">✦</div>
        <div class="vn-star vn-star--2">✧</div>
        <div class="vn-star vn-star--3">✦</div>

        <div class="relative mx-auto flex min-h-screen max-w-md flex-col items-center justify-center px-6">
            <Link href="/" class="vn-wordmark mb-8">VN Maker</Link>

            <div class="vn-card w-full">
                <slot />
            </div>
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
    opacity: 0.5;
    animation: vn-twinkle 3.5s ease-in-out infinite;
    pointer-events: none;
}
.vn-star--1 { top: 12%; left: 12%; font-size: 1.5rem; }
.vn-star--2 { top: 20%; right: 14%; font-size: 1.1rem; animation-delay: 1s; }
.vn-star--3 { bottom: 15%; left: 18%; font-size: 1rem; animation-delay: 2s; }

@keyframes vn-twinkle {
    0%, 100% { opacity: 0.2; transform: scale(0.9); }
    50% { opacity: 0.8; transform: scale(1.15); }
}

.vn-wordmark {
    font-family: 'Fredoka', sans-serif;
    font-weight: 600;
    font-size: 1.2rem;
    color: #fff6fb;
    text-decoration: none;
}

.vn-card {
    position: relative;
    border-radius: 1.25rem;
    border: 1px solid rgba(201, 184, 255, 0.15);
    background: rgba(255, 255, 255, 0.04);
    backdrop-filter: blur(8px);
    padding: 2rem;
}

@media (prefers-reduced-motion: reduce) {
    .vn-star {
        animation: none;
    }
}
</style>