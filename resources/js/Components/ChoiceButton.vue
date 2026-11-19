<script setup>
import { computed } from 'vue';

const props = defineProps({
    label: { type: String, required: true },
    disabled: { type: Boolean, default: false },
    advancing: { type: Boolean, default: false },
});

defineEmits(['select']);

const text = computed(() => (props.advancing ? 'Avanzando…' : props.label));
</script>

<template>
    <button
        type="button"
        class="vn-choice"
        :disabled="disabled"
        :aria-busy="advancing"
        @click="$emit('select')"
    >
        <span v-if="advancing" class="vn-spinner" aria-hidden="true"></span>
        {{ text }}
    </button>
</template>
