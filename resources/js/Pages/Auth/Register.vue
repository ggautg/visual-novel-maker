<script setup>
import FormField from '@/Components/FormField.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import SaveButton from '@/Components/SaveButton.vue';
import TextInput from '@/Components/TextInput.vue';
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
        <Head title="Regístrate" />

        <h1 class="vn-card-title">Crea tu cuenta</h1>
        <p class="vn-card-subtitle">Empieza a crear tu propia historia.</p>

        <form class="mt-6 space-y-5" @submit.prevent="submit">
            <FormField id="name" label="Nombre" :error="form.errors.name">
                <TextInput v-model="form.name" type="text" required autocomplete="name" />
            </FormField>

            <FormField id="email" label="Correo electrónico" :error="form.errors.email">
                <TextInput v-model="form.email" type="email" required autocomplete="username" />
            </FormField>

            <FormField id="password" label="Contraseña" :error="form.errors.password">
                <TextInput
                    v-model="form.password"
                    type="password"
                    required
                    autocomplete="new-password"
                />
            </FormField>

            <FormField
                id="password_confirmation"
                label="Confirma la contraseña"
                :error="form.errors.password_confirmation"
            >
                <TextInput
                    v-model="form.password_confirmation"
                    type="password"
                    required
                    autocomplete="new-password"
                />
            </FormField>

            <SaveButton class="w-full" :processing="form.processing" label="Crea tu cuenta" />
        </form>

        <p class="mt-6 text-center text-sm opacity-80">
            ¿Ya tienes cuenta?
            <Link :href="route('login')" class="vn-link">Inicia sesión</Link>
        </p>
    </GuestLayout>
</template>
