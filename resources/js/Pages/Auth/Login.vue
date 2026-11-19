<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import FormField from '@/Components/FormField.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import SaveButton from '@/Components/SaveButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: { type: Boolean, default: false },
    status: { type: String, default: '' },
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
    <GuestLayout>
        <Head title="Inicia sesión" />

        <h1 class="vn-card-title">Bienvenido de nuevo</h1>
        <p class="vn-card-subtitle">Inicia sesión para continuar tu historia.</p>

        <div v-if="status" class="vn-status" role="status">{{ status }}</div>

        <form class="mt-6 space-y-5" @submit.prevent="submit">
            <FormField id="email" label="Correo electrónico" :error="form.errors.email">
                <TextInput v-model="form.email" type="email" required autocomplete="username" />
            </FormField>

            <FormField id="password" label="Contraseña" :error="form.errors.password">
                <TextInput
                    v-model="form.password"
                    type="password"
                    required
                    autocomplete="current-password"
                />
            </FormField>

            <label class="flex items-center gap-2 text-sm">
                <Checkbox v-model:checked="form.remember" />
                Recordarme
            </label>

            <div class="flex items-center justify-between gap-4 pt-2">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="vn-link text-sm"
                >
                    ¿Olvidaste tu contraseña?
                </Link>
            </div>

            <SaveButton class="w-full" :processing="form.processing" label="Inicia sesión" />
        </form>

        <p class="mt-6 text-center text-sm opacity-80">
            ¿Todavía no tienes cuenta?
            <Link :href="route('register')" class="vn-link">Regístrate</Link>
        </p>
    </GuestLayout>
</template>
