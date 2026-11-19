<script setup>
defineProps({
    enabled: { type: Boolean, default: false },
    available: { type: Boolean, default: false },
});

defineEmits(['toggle']);
</script>

<template>
    <button
        type="button"
        class="vn-audio-toggle"
        :aria-pressed="enabled"
        :aria-label="enabled ? 'Silenciar la música' : 'Activar la música'"
        :title="available ? (enabled ? 'Silenciar la música' : 'Activar la música') : 'Esta escena no tiene música'"
        :disabled="!available"
        @click="$emit('toggle')"
    >
        <span aria-hidden="true">{{ enabled ? '🔊' : '🔇' }}</span>
    </button>
</template>
