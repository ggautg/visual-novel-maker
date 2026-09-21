<script setup>
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: { type: Boolean },
    status: { type: String },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Entrar" />

    <div class="vn-page">
        <div class="vn-scanlines"></div>
        <div class="vn-star vn-star--1">✦</div>
        <div class="vn-star vn-star--2">✧</div>
        <div class="vn-star vn-star--3">✦</div>

        <div class="relative mx-auto flex min-h-screen max-w-md flex-col items-center justify-center px-6">
            <Link href="/" class="vn-wordmark mb-8">VN Maker</Link>

            <div class="vn-card w-full">
                <h1 class="vn-card-title">Bienvenido de nuevo</h1>
                <p class="vn-card-subtitle">Entrá para seguir tu historia</p>

                <div v-if="status" class="vn-status">{{ status }}</div>

                <form @submit.prevent="submit" class="mt-6 space-y-4">
                    <div>
                        <label for="email" class="vn-label">Email</label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="vn-input"
                            required
                            autofocus
                            autocomplete="username"
                        />
                        <InputError class="mt-1" :message="form.errors.email" />
                    </div>

                    <div>
                        <label for="password" class="vn-label">Contraseña</label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            class="vn-input"
                            required
                            autocomplete="current-password"
                        />
                        <InputError class="mt-1" :message="form.errors.password" />
                    </div>

                    <label class="flex items-center gap-2 text-sm text-[#D9CFFB]">
                   <input
    v-model="form.remember"
    type="checkbox"
    name="remember"
    class="vn-checkbox"
/>
                    Recordarme
                    </label>

                    <div class="flex items-center justify-between pt-2">
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="vn-link text-sm"
                        >
                            ¿Olvidaste tu contraseña?
                        </Link>
                    </div>

                    <button
                        type="submit"
                        class="vn-pill vn-pill--solid w-full"
                        :class="{ 'opacity-50': form.processing }"
                        :disabled="form.processing"
                    >
                        Entrar
                    </button>
                </form>

                <p class="mt-6 text-center text-sm text-[#9D8FD6]">
                    ¿Todavía no tenés cuenta?
                    <Link :href="route('register')" class="vn-link">Registrate</Link>
                </p>
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
}

.vn-card {
    position: relative;
    border-radius: 1.25rem;
    border: 1px solid rgba(201, 184, 255, 0.15);
    background: rgba(255, 255, 255, 0.04);
    backdrop-filter: blur(8px);
    padding: 2rem;
}

.vn-card-title {
    font-family: 'Fredoka', sans-serif;
    font-weight: 600;
    font-size: 1.5rem;
    color: #fff6fb;
}

.vn-card-subtitle {
    margin-top: 0.25rem;
    font-size: 0.9rem;
    color: #b8acdd;
}

.vn-status {
    margin-top: 1rem;
    border-radius: 0.5rem;
    background: rgba(94, 241, 230, 0.12);
    padding: 0.6rem 0.9rem;
    font-size: 0.85rem;
    color: #5ef1e6;
}

.vn-label {
    display: block;
    margin-bottom: 0.35rem;
    font-size: 0.85rem;
    color: #d9cffb;
}

.vn-input {
    width: 100%;
    border-radius: 0.65rem;
    border: 1px solid rgba(201, 184, 255, 0.25);
    background: rgba(255, 255, 255, 0.05);
    padding: 0.6rem 0.85rem;
    font-size: 0.95rem;
    color: #fff6fb;
    outline: none;
    transition: border-color 0.2s ease;
}
.vn-input:focus {
    border-color: #ff5fa2;
}
.vn-input::placeholder {
    color: #6e5fa3;
}

.vn-checkbox {
    height: 1rem;
    width: 1rem;
    border-radius: 0.3rem;
    accent-color: #ff5fa2;
}

.vn-link {
    color: #5ef1e6;
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
    font-weight: 600;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border: none;
    cursor: pointer;
}
.vn-pill--solid {
    background: linear-gradient(135deg, #ff5fa2, #ff8fc0);
    color: #1a0f36;
    box-shadow: 0 8px 24px rgba(255, 95, 162, 0.35);
}
.vn-pill--solid:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(255, 95, 162, 0.45);
}
.vn-pill--solid:disabled {
    cursor: not-allowed;
}

@media (prefers-reduced-motion: reduce) {
    .vn-star {
        animation: none;
    }
}
</style>