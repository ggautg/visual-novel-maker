<script setup>
import { ref } from 'vue';
import FormField from '@/Components/FormField.vue';
import SaveButton from '@/Components/SaveButton.vue';
import SettingsCard from '@/Components/SettingsCard.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value?.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value?.focus();
            }
        },
    });
};
</script>

<template>
    <SettingsCard
        title="Actualiza tu contraseña"
        description="Usa una contraseña larga y aleatoria para proteger tu cuenta."
    >
        <form class="space-y-5" @submit.prevent="updatePassword">
            <FormField
                id="current_password"
                label="Contraseña actual"
                :error="form.errors.current_password"
            >
                <TextInput
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    type="password"
                    autocomplete="current-password"
                />
            </FormField>

            <FormField id="password" label="Nueva contraseña" :error="form.errors.password">
                <TextInput
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    autocomplete="new-password"
                />
            </FormField>

            <FormField
                id="password_confirmation"
                label="Confirma la nueva contraseña"
                :error="form.errors.password_confirmation"
            >
                <TextInput
                    v-model="form.password_confirmation"
                    type="password"
                    autocomplete="new-password"
                />
            </FormField>

            <SaveButton
                :processing="form.processing"
                :success="form.recentlySuccessful"
            />
        </form>
    </SettingsCard>
</template>
