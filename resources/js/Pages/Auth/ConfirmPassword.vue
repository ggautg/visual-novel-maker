<script setup>
import FormField from '@/Components/FormField.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import SaveButton from '@/Components/SaveButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({ password: '' });

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Confirma tu contraseña" />

        <h1 class="vn-card-title">Confirma tu contraseña</h1>
        <p class="vn-card-subtitle">
            Esta es un área protegida. Confirma tu contraseña antes de continuar.
        </p>

        <form class="mt-6 space-y-5" @submit.prevent="submit">
            <FormField id="password" label="Contraseña" :error="form.errors.password">
                <TextInput
                    v-model="form.password"
                    type="password"
                    required
                    autocomplete="current-password"
                />
            </FormField>

            <SaveButton class="w-full" :processing="form.processing" label="Confirmar" />
        </form>
    </GuestLayout>
</template>
