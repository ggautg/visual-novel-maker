<script setup>
import FormField from '@/Components/FormField.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import SaveButton from '@/Components/SaveButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: { type: String, required: true },
    token: { type: String, required: true },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Restablece tu contraseña" />

        <h1 class="vn-card-title">Elige una nueva contraseña</h1>
        <p class="vn-card-subtitle">Usa una contraseña larga y fácil de recordar para ti.</p>

        <form class="mt-6 space-y-5" @submit.prevent="submit">
            <FormField id="email" label="Correo electrónico" :error="form.errors.email">
                <TextInput v-model="form.email" type="email" required autocomplete="username" readonly />
            </FormField>

            <FormField id="password" label="Nueva contraseña" :error="form.errors.password">
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

            <SaveButton
                class="w-full"
                :processing="form.processing"
                label="Guardar contraseña"
                processing-label="Guardando…"
            />
        </form>
    </GuestLayout>
</template>
