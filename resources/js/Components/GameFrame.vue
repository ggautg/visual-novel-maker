<script setup>
import { computed } from 'vue';

const props = defineProps({
    background: { type: String, default: '' },
});

const backgroundStyle = computed(() =>
    props.background ? { backgroundImage: `url("${props.background}")` } : {},
);
</script>

<template>
    <div class="vn-game">
        <div class="vn-game__scene" :style="backgroundStyle" aria-hidden="true"></div>
        <div class="vn-game__scrim" aria-hidden="true"></div>
        <slot />
    </div>
</template>
