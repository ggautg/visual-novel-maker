<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import SaveButton from '@/Components/SaveButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: { type: String, default: '' },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <GuestLayout>
        <Head title="Verificación de correo" />

        <h1 class="vn-card-title">Verifica tu correo electrónico</h1>
        <p class="vn-card-subtitle">
            Gracias por registrarte. Antes de empezar, confirma tu dirección con el enlace que te enviamos.
            Si no lo recibiste, podemos enviarte otro.
        </p>

        <div v-if="verificationLinkSent" class="vn-status" role="status">
            Hemos enviado un nuevo enlace de verificación a tu correo electrónico.
        </div>

        <form class="mt-6 flex flex-wrap items-center justify-between gap-4" @submit.prevent="submit">
            <SaveButton
                :processing="form.processing"
                label="Reenviar enlace de verificación"
                processing-label="Enviando…"
            />

            <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="vn-link text-sm"
            >
                Cerrar sesión
            </Link>
        </form>
    </GuestLayout>
</template>
