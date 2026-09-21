<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Registrarme" />

        <h1 class="vn-card-title">Creá tu cuenta</h1>
        <p class="vn-card-subtitle">Empezá a armar tu propia historia</p>

        <form @submit.prevent="submit" class="mt-6 space-y-4">
            <div>
                <label for="name" class="vn-label">Nombre</label>
                <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="vn-input"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError class="mt-1" :message="form.errors.name" />
            </div>

            <div>
                <label for="email" class="vn-label">Email</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="vn-input"
                    required
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
                    autocomplete="new-password"
                />
                <InputError class="mt-1" :message="form.errors.password" />
            </div>

            <div>
                <label for="password_confirmation" class="vn-label">Confirmar contraseña</label>
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="vn-input"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-1" :message="form.errors.password_confirmation" />
            </div>

            <button
                type="submit"
                class="vn-pill vn-pill--solid w-full"
                :class="{ 'opacity-50': form.processing }"
                :disabled="form.processing"
            >
                Crear cuenta
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-[#9D8FD6]">
            ¿Ya tenés cuenta?
            <Link :href="route('login')" class="vn-link">Entrá acá</Link>
        </p>
    </GuestLayout>
</template>

<style scoped>
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
</style>