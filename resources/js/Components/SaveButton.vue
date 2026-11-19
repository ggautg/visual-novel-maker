<script setup>
import { computed } from 'vue';

const props = defineProps({
    processing: { type: Boolean, default: false },
    success: { type: Boolean, default: false },
    label: { type: String, default: 'Guardar' },
    processingLabel: { type: String, default: 'Guardando…' },
    successLabel: { type: String, default: 'Guardado' },
});

defineEmits(['click']);

const currentLabel = computed(() => {
    if (props.processing) return props.processingLabel;
    if (props.success) return props.successLabel;
    return props.label;
});
</script>

<template>
    <button
        type="submit"
        class="vn-pill vn-pill--solid"
        :disabled="processing"
        :aria-busy="processing"
        @click="$emit('click', $event)"
    >
        <span v-if="processing" class="vn-spinner" aria-hidden="true"></span>
        <span v-else-if="success" aria-hidden="true">✓</span>
        <span>{{ currentLabel }}</span>
    </button>
</template>
