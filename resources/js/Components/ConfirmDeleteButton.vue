<script setup>
import { ref } from 'vue';
import DangerButton from './DangerButton.vue';
import Modal from './Modal.vue';
import SecondaryButton from './SecondaryButton.vue';

defineProps({
    title: { type: String, required: true },
    message: { type: String, required: true },
    label: { type: String, default: 'Eliminar' },
    processing: { type: Boolean, default: false },
});

const emit = defineEmits(['confirm']);
const open = ref(false);

const confirm = () => {
    if (open.value === false) return;
    open.value = false;
    emit('confirm');
};
</script>

<template>
    <div class="inline-flex">
        <span
            class="inline-flex"
            role="button"
            tabindex="0"
            @click="open = true"
            @keydown.enter="open = true"
            @keydown.space.prevent="open = true"
        >
            <slot name="trigger">
                <slot>
                    <span class="vn-pill vn-pill--danger">{{ label }}</span>
                </slot>
            </slot>
        </span>

        <Modal :show="open" max-width="lg" @close="open = false">
            <div class="vn-modal__body">
                <h2 class="vn-modal__title">{{ title }}</h2>
                <p class="vn-card-subtitle">{{ message }}</p>

                <div class="vn-modal__actions">
                    <SecondaryButton type="button" @click="open = false">Cancelar</SecondaryButton>
                    <DangerButton type="button" :disabled="processing" @click="confirm">
                        <span v-if="processing" class="vn-spinner" aria-hidden="true"></span>
                        {{ label }}
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </div>
</template>
