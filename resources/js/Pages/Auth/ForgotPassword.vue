<script setup>
import FormField from '@/Components/FormField.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import SaveButton from '@/Components/SaveButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: { type: String, default: '' },
});

const form = useForm({ email: '' });

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Restablece tu contraseña" />

        <h1 class="vn-card-title">¿Olvidaste tu contraseña?</h1>
        <p class="vn-card-subtitle">
            No hay problema. Escribe tu correo electrónico y te enviaremos un enlace para elegir una nueva.
        </p>

        <div v-if="status" class="vn-status" role="status">{{ status }}</div>

        <form class="mt-6 space-y-5" @submit.prevent="submit">
            <FormField id="email" label="Correo electrónico" :error="form.errors.email">
                <TextInput v-model="form.email" type="email" required autocomplete="username" />
            </FormField>

            <SaveButton
                class="w-full"
                :processing="form.processing"
                label="Enviar enlace de recuperación"
                processing-label="Enviando…"
            />
        </form>

        <p class="mt-6 text-center text-sm opacity-80">
            <Link :href="route('login')" class="vn-link">Volver a iniciar sesión</Link>
        </p>
    </GuestLayout>
</template>
