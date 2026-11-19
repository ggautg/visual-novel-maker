<script setup>
import FormField from '@/Components/FormField.vue';
import SaveButton from '@/Components/SaveButton.vue';
import SettingsCard from '@/Components/SettingsCard.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    mustVerifyEmail: { type: Boolean, default: false },
    status: { type: String, default: '' },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <SettingsCard
        title="Información del perfil"
        description="Actualiza tus datos y tu correo electrónico."
    >
        <form class="space-y-5" @submit.prevent="form.patch(route('profile.update'))">
            <FormField id="name" label="Nombre" :error="form.errors.name">
                <TextInput v-model="form.name" type="text" required autocomplete="name" />
            </FormField>

            <FormField id="email" label="Correo electrónico" :error="form.errors.email">
                <TextInput v-model="form.email" type="email" required autocomplete="username" />
            </FormField>

            <div v-if="props.mustVerifyEmail && user.email_verified_at === null">
                <p class="text-sm">
                    Tu correo electrónico todavía no está verificado.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="vn-link"
                    >
                        Reenviar el enlace de verificación.
                    </Link>
                </p>

                <div v-if="props.status === 'verification-link-sent'" class="vn-status" role="status">
                    Hemos enviado un nuevo enlace de verificación a tu correo electrónico.
                </div>
            </div>

            <SaveButton
                :processing="form.processing"
                :success="form.recentlySuccessful"
            />
        </form>
    </SettingsCard>
</template>
