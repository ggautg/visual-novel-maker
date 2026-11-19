<script setup>
defineProps({
    value: { type: String, default: '' },
    for: { type: String, default: '' },
});
</script>

<template>
    <label class="vn-label" :for="for">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
