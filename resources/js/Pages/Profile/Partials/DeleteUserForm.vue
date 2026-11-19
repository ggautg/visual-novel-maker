<script setup>
import { nextTick, ref } from 'vue';
import FormField from '@/Components/FormField.vue';
import Modal from '@/Components/Modal.vue';
import SaveButton from '@/Components/SaveButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import SettingsCard from '@/Components/SettingsCard.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({ password: '' });

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
    nextTick(() => passwordInput.value?.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <SettingsCard
        title="Eliminar cuenta"
        description="Esta acción no se puede deshacer. Antes de continuar, descarga los datos que quieras conservar."
    >
        <button type="button" class="vn-pill vn-pill--danger" @click="confirmUserDeletion">
            Eliminar cuenta
        </button>

        <Modal :show="confirmingUserDeletion" max-width="lg" @close="closeModal">
            <div class="vn-modal__body">
                <h2 class="vn-modal__title">¿Seguro que quieres eliminar tu cuenta?</h2>
                <p class="vn-card-subtitle">
                    Se borrarán todos tus recursos y datos de forma permanente. Escribe tu contraseña para confirmar.
                </p>

                <div class="mt-6 max-w-sm">
                    <FormField id="delete_password" label="Contraseña" :error="form.errors.password">
                        <TextInput
                            ref="passwordInput"
                            v-model="form.password"
                            type="password"
                            placeholder="Contraseña"
                            autocomplete="current-password"
                            @keyup.enter="deleteUser"
                        />
                    </FormField>
                </div>

                <div class="vn-modal__actions">
                    <SecondaryButton type="button" @click="closeModal">Cancelar</SecondaryButton>
                    <SaveButton
                        type="button"
                        class="vn-pill--danger"
                        :processing="form.processing"
                        label="Eliminar cuenta"
                        processing-label="Eliminando…"
                        @click="deleteUser"
                    />
                </div>
            </div>
        </Modal>
    </SettingsCard>
</template>
