<script setup>
import { computed } from 'vue';

const props = defineProps({
    text: { type: String, required: true },
    lines: { type: Number, default: 2 },
});

const clampStyle = computed(() => ({
    display: '-webkit-box',
    '-webkit-box-orient': 'vertical',
    '-webkit-line-clamp': String(props.lines),
    overflow: 'hidden',
}));
</script>

<template>
    <p class="vn-clamped-text" :style="clampStyle" :title="text">{{ text }}</p>
</template>
